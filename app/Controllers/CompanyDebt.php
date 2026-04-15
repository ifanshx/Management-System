<?php

namespace App\Controllers;

class CompanyDebt extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        // Auto-patch: Akun Hutang Lain-lain (2-2000)
        $checkCoa = $this->db->table('chart_of_accounts')->where('account_code', '2-2000')->countAllResults();
        if ($checkCoa == 0) {
            $this->db->table('chart_of_accounts')->insert([
                'account_code'   => '2-2000',
                'account_name'   => 'Hutang Lain-lain (Non-Operasional)',
                'account_type'   => 'LIABILITI',
                'normal_balance' => 'KREDIT',
                'is_active'      => 1,
                'notes'          => 'Hutang non-operasional (Kasus, Pinjaman Eksternal, dll)'
            ]);
        }
    }

    private function cleanRupiah(?string $string): float
    {
        if (empty($string)) return 0.0;
        $cleanString = str_replace('.', '', $string);
        $cleanString = str_replace(',', '.', $cleanString);
        return (float) $cleanString;
    }

    public function index()
    {
        // Pastikan user sudah login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }

        $debts = $this->db->table('company_debts')->orderBy('status', 'ASC')->orderBy('id', 'DESC')->get()->getResultArray();
        
        $totalDebt = 0; 
        $totalPaid = 0;
        $activeCases = 0;

        foreach ($debts as &$d) {
            $d['payments'] = $this->db->table('company_debt_payments')->where('debt_id', $d['id'])->orderBy('payment_date', 'DESC')->get()->getResultArray();
            $totalDebt += $d['total_debt'];
            $totalPaid += $d['paid_amount'];
            
            // Jika status belum lunas, hitung sebagai dokumen aktif
            if ($d['status'] !== 'LUNAS') {
                $activeCases++;
            }
        }

        $overallProgress = ($totalDebt > 0) ? ($totalPaid / $totalDebt) * 100 : 0;

        $data = [
            'title'           => 'Account Payable: Non-Operasional',
            'debts'           => $debts,
            'totalDebt'       => $totalDebt,
            'totalPaid'       => $totalPaid,
            'totalSisa'       => $totalDebt - $totalPaid,
            'activeCases'     => $activeCases,
            'overallProgress' => $overallProgress
        ];

        return view('company_debt/index', $data);
    }

    public function store()
    {
        try {
            $category = $this->request->getPost('category');
            $name     = $this->request->getPost('creditor_name');
            $desc     = $this->request->getPost('description');
            $amount   = $this->cleanRupiah($this->request->getPost('total_debt'));

            if (empty($name) || $amount <= 0) throw new \Exception("Data tidak valid. Periksa Nama dan Nominal.");

            // Generate Document Number: DBT-YYYYMM-001
            $dateCode  = date('Ym');
            $lastDoc   = $this->db->table('company_debts')->like('debt_number', "DBT-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastDoc ? str_pad((int) substr($lastDoc['debt_number'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $debtNumber = "DBT-$dateCode-$newNumber";

            $this->db->table('company_debts')->insert([
                'debt_number'   => $debtNumber,
                'category'      => $category,
                'creditor_name' => strtoupper($name),
                'description'   => $desc,
                'total_debt'    => $amount,
                'status'        => 'BELUM LUNAS',
                'created_by'    => session()->get('name') ?? 'Admin'
            ]);

            return redirect()->back()->with('success', "Dokumen <b>$debtNumber</b> berhasil diterbitkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function pay($id)
    {
        try {
            $this->db->transStart();

            $amount = $this->cleanRupiah($this->request->getPost('amount'));
            $method = $this->request->getPost('payment_method');
            $notes  = $this->request->getPost('notes');
            $date   = date('Y-m-d');

            if ($amount <= 0) throw new \Exception("Nominal pembayaran tidak valid.");

            $debt = $this->db->table('company_debts')->where('id', $id)->get()->getRowArray();
            if (!$debt) throw new \Exception("Dokumen hutang tidak ditemukan.");

            $sisaHutang = $debt['total_debt'] - $debt['paid_amount'];
            if ($amount > $sisaHutang) throw new \Exception("Pembayaran melebihi sisa hutang.");

            // 1. Insert Payment
            $this->db->table('company_debt_payments')->insert([
                'debt_id'        => $id,
                'payment_date'   => $date,
                'amount'         => $amount,
                'payment_method' => $method,
                'notes'          => $notes,
                'created_by'     => session()->get('name') ?? 'Admin'
            ]);

            // 2. Update Master
            $newPaid = $debt['paid_amount'] + $amount;
            $status  = ($newPaid >= $debt['total_debt']) ? 'LUNAS' : 'SEBAGIAN';
            $this->db->table('company_debts')->where('id', $id)->update([
                'paid_amount' => $newPaid,
                'status'      => $status
            ]);

            // 3. Jurnal Akuntansi (Debit Hutang Lain-lain, Kredit Kas)
            $hutangAcc = $this->db->table('chart_of_accounts')->where('account_code', '2-2000')->get()->getRowArray();
            $kasCode   = ($method === 'CASH') ? '1-1000' : '1-2000';
            $kasAcc    = $this->db->table('chart_of_accounts')->where('account_code', $kasCode)->get()->getRowArray();

            $journalId = null;
            if ($hutangAcc && $kasAcc) {
                $this->db->table('journals')->insert([
                    'journal_number'   => 'JRN-DBT-PAY-' . time(),
                    'transaction_date' => $date,
                    'description'      => "Pembayaran Hutang Non-Op [{$debt['debt_number']}] ke {$debt['creditor_name']}",
                    'total_amount'     => $amount,
                    'created_by'       => session()->get('name') ?? 'Admin'
                ]);
                $journalId = $this->db->insertID();

                $this->db->table('journal_items')->insert([
                    'journal_id' => $journalId, 'account_id' => $hutangAcc['id'], 
                    'line_description' => "Penurunan Liabilitas [{$debt['debt_number']}]", 'debit' => $amount, 'credit' => 0
                ]);
                $this->db->table('journal_items')->insert([
                    'journal_id' => $journalId, 'account_id' => $kasAcc['id'], 
                    'line_description' => "Pengeluaran Kas ({$method})", 'debit' => 0, 'credit' => $amount
                ]);
            }

            // 4. Catat di Arus Kas Operasional
            $dateCode  = date('Ymd');
            $lastTrx   = $this->db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';

            $this->db->table('operational_cash')->insert([
                'transaction_code' => "TRX-$dateCode-$newNumber",
                'transaction_date' => $date,
                'type'             => 'Cash Out',
                'metode'           => ($method === 'CASH') ? 'Cash' : 'ATM',
                'category'         => 'Pembayaran Hutang Non-Operasional',
                'amount'           => $amount,
                'description'      => "Pembayaran ke {$debt['creditor_name']} Dokumen {$debt['debt_number']}" . ($notes ? " ($notes)" : ""),
                'pic_name'         => session()->get('name') ?? 'Sistem',
                'journal_id'       => $journalId
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal memproses pembayaran (Rollback).");

            $msg = ($status === 'LUNAS') ? "Dokumen <b>{$debt['debt_number']}</b> telah LUNAS 100%!" : "Pembayaran berhasil dialokasikan ke Dokumen <b>{$debt['debt_number']}</b>.";
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // FUNGSI UNTUK EDIT/UPDATE DATA
    public function update($id)
    {
        try {
            $category = $this->request->getPost('category');
            $name     = $this->request->getPost('creditor_name');
            $desc     = $this->request->getPost('description');
            $amount   = $this->cleanRupiah($this->request->getPost('total_debt'));

            if (empty($name) || $amount <= 0) throw new \Exception("Data tidak valid. Periksa Nama dan Nominal.");

            $debt = $this->db->table('company_debts')->where('id', $id)->get()->getRowArray();
            if (!$debt) throw new \Exception("Dokumen hutang tidak ditemukan.");

            // Validasi: Total hutang baru tidak boleh lebih kecil dari yang sudah dibayar
            if ($amount < $debt['paid_amount']) {
                throw new \Exception("Total kewajiban baru (Rp " . number_format($amount,0,',','.') . ") tidak boleh lebih kecil dari jumlah yang sudah dibayar (Rp " . number_format($debt['paid_amount'],0,',','.') . ").");
            }

            // Hitung ulang status
            $status = ($debt['paid_amount'] >= $amount) ? 'LUNAS' : (($debt['paid_amount'] > 0) ? 'SEBAGIAN' : 'BELUM LUNAS');

            $this->db->table('company_debts')->where('id', $id)->update([
                'category'      => $category,
                'creditor_name' => strtoupper($name),
                'description'   => $desc,
                'total_debt'    => $amount,
                'status'        => $status
            ]);

            return redirect()->back()->with('success', "Dokumen <b>{$debt['debt_number']}</b> berhasil diperbarui.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // FUNGSI UNTUK MENGHAPUS DATA
    public function delete($id)
    {
        try {
            $debt = $this->db->table('company_debts')->where('id', $id)->get()->getRowArray();
            if (!$debt) throw new \Exception("Dokumen hutang tidak ditemukan.");

            // Karena di tabel menggunakan ON DELETE CASCADE, riwayat pembayaran otomatis terhapus
            $this->db->table('company_debts')->where('id', $id)->delete();

            return redirect()->back()->with('success', "Dokumen <b>{$debt['debt_number']}</b> beserta riwayatnya berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}