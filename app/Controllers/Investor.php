<?php

namespace App\Controllers;

use App\Services\AccountingService;

class Investor extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Pastikan hanya role tertentu yang bisa akses (misal: admin / owner)
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal')->with('error', 'Akses ditolak.');
        }

        $investors = $this->db->table('investors')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        $transactions = $this->db->query("
            SELECT t.*, i.name as investor_name 
            FROM investor_transactions t
            JOIN investors i ON i.id = t.investor_id
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT 50
        ")->getResultArray();

        // Kalkulasi Summary
        $totalInjection = 0;
        $totalWithdrawal = 0;
        $totalDividend = 0;

        foreach ($transactions as $trx) {
            if ($trx['status'] === 'POSTED') {
                if ($trx['type'] === 'INJECTION') $totalInjection += $trx['amount'];
                if ($trx['type'] === 'WITHDRAWAL') $totalWithdrawal += $trx['amount'];
                if ($trx['type'] === 'DIVIDEND') $totalDividend += $trx['amount'];
            }
        }

        $netCapital = $totalInjection - $totalWithdrawal;

        return view('investor/index', [
            'title'           => 'Modul Pendanaan & Investor',
            'investors'       => $investors,
            'transactions'    => $transactions,
            'totalInjection'  => $totalInjection,
            'totalWithdrawal' => $totalWithdrawal,
            'totalDividend'   => $totalDividend,
            'netCapital'      => $netCapital
        ]);
    }

    public function store_investor()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $name = trim($this->request->getPost('name'));
            $phone = trim($this->request->getPost('phone'));
            $equity = (float) $this->request->getPost('equity_percentage');

            $this->db->table('investors')->insert([
                'name'              => $name,
                'phone'             => $phone,
                'equity_percentage' => $equity,
                'status'            => 'ACTIVE'
            ]);

            return redirect()->back()->with('success', "Data Investor <b>{$name}</b> berhasil ditambahkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menambah investor: ' . $e->getMessage());
        }
    }

    public function store_transaction()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $investorId = $this->request->getPost('investor_id');
            $date       = $this->request->getPost('transaction_date') ?: date('Y-m-d');
            $type       = $this->request->getPost('type'); // INJECTION, WITHDRAWAL, DIVIDEND
            $category   = $this->request->getPost('category'); // DEBT, EQUITY
            $method     = $this->request->getPost('payment_method'); // CASH, BANK
            $amountRaw  = $this->request->getPost('amount');
            $amount     = (float) preg_replace('/[^0-9]/', '', $amountRaw);
            $desc       = trim($this->request->getPost('description'));
            
            $pic_name   = session()->get('name') ?? 'System';

            if ($amount <= 0) throw new \Exception("Nominal harus lebih dari 0.");

            $investor = $this->db->table('investors')->where('id', $investorId)->get()->getRowArray();
            if (!$investor) throw new \Exception("Investor tidak valid.");

            $this->db->transStart();

            // 1. Generate Transaction Code
            $dateCode = date('Ymd', strtotime($date));
            $lastTrx = $this->db->table('investor_transactions')
                ->like('transaction_code', "INV-$dateCode-", 'after')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            $seq = $lastTrx ? (int) explode('-', $lastTrx['transaction_code'])[2] + 1 : 1;
            $trxCode = "INV-$dateCode-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // 2. Setup Accounts
            $accService = new AccountingService();
            $kasBankAcc = $this->db->table('chart_of_accounts')->where('account_code', ($method === 'CASH' ? '1-1000' : '1-2000'))->get()->getRowArray();
            
            if (!$kasBankAcc) throw new \Exception("Akun Kas/Bank tidak ditemukan.");

            $lawanAccCode = '';
            $lawanAccName = '';
            $lawanAccType = '';
            $lawanAccNormal = '';

            // Tentukan Lawan Akun berdasarkan Kategori & Tipe
            if ($type === 'DIVIDEND') {
                $lawanAccCode = '3-2000';
                $lawanAccName = 'DIVIDEN / PRIVE';
                $lawanAccType = 'EKUITI';
                $lawanAccNormal = 'DEBIT'; // Contra Equity
            } else {
                if ($category === 'DEBT') {
                    $lawanAccCode = '2-2000';
                    $lawanAccName = 'HUTANG PEMILIK / INVESTOR';
                    $lawanAccType = 'LIABILITI';
                    $lawanAccNormal = 'KREDIT';
                } else {
                    $lawanAccCode = '3-1000'; // Default Modal Pemilik yang sudah ada di sistem Anda
                    $lawanAccName = 'MODAL PEMILIK';
                    $lawanAccType = 'EKUITI';
                    $lawanAccNormal = 'KREDIT';
                }
            }

            // Auto-create akun lawan jika belum ada (Kecuali 3-1000 yang pasti sudah ada)
            $lawanAcc = $this->db->table('chart_of_accounts')->where('account_code', $lawanAccCode)->get()->getRowArray();
            if (!$lawanAcc) {
                $this->db->table('chart_of_accounts')->insert([
                    'account_code'   => $lawanAccCode,
                    'account_name'   => $lawanAccName,
                    'account_type'   => $lawanAccType,
                    'normal_balance' => $lawanAccNormal,
                    'is_contra'      => ($type === 'DIVIDEND') ? 1 : 0,
                    'is_active'      => 1
                ]);
                $lawanAcc = $this->db->table('chart_of_accounts')->where('account_code', $lawanAccCode)->get()->getRowArray();
            }

            // 3. Susun Jurnal (Debit / Kredit)
            $journalItems = [];
            $memo = "{$type} - {$investor['name']}: {$desc}";

            if ($type === 'INJECTION') {
                // Perusahaan Terima Uang
                $journalItems[] = ['account_id' => $kasBankAcc['id'], 'debit' => $amount, 'credit' => 0, 'memo' => 'Dana Masuk dari Investor'];
                $journalItems[] = ['account_id' => $lawanAcc['id'], 'debit' => 0, 'credit' => $amount, 'memo' => $memo];
            } else {
                // Perusahaan Keluar Uang (Penarikan Modal / Pengembalian Hutang / Bayar Dividen)
                
                // Cek Saldo Kas/Bank dulu
                $saldoBank = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$kasBankAcc['id']])->getRowArray()['calculated_balance'] ?? 0;
                if ($saldoBank < $amount) throw new \Exception("Saldo " . ($method === 'CASH' ? 'Kas' : 'Bank') . " tidak mencukupi untuk penarikan ini.");

                $journalItems[] = ['account_id' => $lawanAcc['id'], 'debit' => $amount, 'credit' => 0, 'memo' => $memo];
                $journalItems[] = ['account_id' => $kasBankAcc['id'], 'debit' => 0, 'credit' => $amount, 'memo' => 'Dana Keluar ke Investor'];
            }

            // 4. Create Jurnal
            $journalDesc = ($type === 'INJECTION' ? 'Suntikan Dana: ' : 'Penarikan/Dividen: ') . $investor['name'];
            $journalId = $accService->createJournal($date, $journalDesc, 'investor_funding', $trxCode, $amount, $journalItems, $pic_name);

            // 5. Insert History Transaksi Investor
            $this->db->table('investor_transactions')->insert([
                'transaction_code' => $trxCode,
                'investor_id'      => $investorId,
                'transaction_date' => $date,
                'type'             => $type,
                'category'         => $category,
                'amount'           => $amount,
                'payment_method'   => $method,
                'description'      => $desc,
                'journal_id'       => $journalId,
                'status'           => 'POSTED',
                'created_by'       => $pic_name
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Terjadi kesalahan saat memproses transaksi pendanaan.");
            }

            return redirect()->back()->with('success', "Transaksi pendanaan <b>{$trxCode}</b> berhasil dicatat ke buku besar.");

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function void_transaction($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $trx = $this->db->table('investor_transactions')->where('id', $id)->get()->getRowArray();
            if (!$trx) throw new \Exception("Transaksi tidak ditemukan.");
            if ($trx['status'] === 'VOID') throw new \Exception("Transaksi sudah dibatalkan.");

            $this->db->transStart();

            // Panggil Void Jurnal dari Service
            if ($trx['journal_id']) {
                $accService = new AccountingService();
                $accService->voidJournal($trx['journal_id'], 'Dibatalkan dari Modul Pendanaan', session()->get('name') ?? 'System');
            }

            // Update status transaksi
            $this->db->table('investor_transactions')->where('id', $id)->update(['status' => 'VOID']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal membatalkan transaksi.");
            }

            return redirect()->back()->with('success', "Transaksi pendanaan <b>{$trx['transaction_code']}</b> berhasil di-VOID.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // FUNGSI UNTUK MENGHAPUS INVESTOR DAN MENGUBAH STATUSNYA
    // Pastikan fungsi ini berada DI DALAM class Investor
    // =========================================================================

    public function delete_investor($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            // Cek apakah investor sudah punya transaksi
            $hasTransactions = $this->db->table('investor_transactions')->where('investor_id', $id)->countAllResults();
            
            if ($hasTransactions > 0) {
                throw new \Exception("Investor ini tidak bisa dihapus karena sudah memiliki riwayat transaksi pendanaan. Jika sudah tidak aktif, silakan ubah statusnya menjadi NONAKTIF.");
            }

            $this->db->table('investors')->where('id', $id)->delete();

            return redirect()->back()->with('success', "Data investor berhasil dihapus permanen.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggle_status($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        try {
            $investor = $this->db->table('investors')->where('id', $id)->get()->getRowArray();
            if (!$investor) throw new \Exception("Investor tidak ditemukan.");

            $newStatus = ($investor['status'] === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
            
            $this->db->table('investors')->where('id', $id)->update(['status' => $newStatus]);

            return redirect()->back()->with('success', "Status investor <b>{$investor['name']}</b> berhasil diubah menjadi <b>{$newStatus}</b>.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}