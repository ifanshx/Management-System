<?php

namespace App\Controllers;

use App\Models\OperationalCashModel;
use App\Services\AccountingService;

class Finance extends BaseController
{
    protected $db;
    protected $cashModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cashModel = new OperationalCashModel();
    }

    public function cash_index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $tgl_filter = $this->request->getGet('tgl') ?? date('Y-m-d');

        $transactions = $this->cashModel
            ->where('transaction_date', $tgl_filter)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $akunKas = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE account_code = '1-1000'")->getRowArray();
        $akunBank = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE account_code = '1-2000'")->getRowArray();

        $sisa_cash = $akunKas['calculated_balance'] ?? 0;
        $sisa_atm  = $akunBank['calculated_balance'] ?? 0;
        $saldo_akhir = $sisa_cash + $sisa_atm;

        $masuk_hari_ini = 0;
        $keluar_hari_ini = 0;

        foreach ($transactions as $trx) {
            if (($trx['status'] ?? 'POSTED') === 'POSTED') {
                if ($trx['type'] === 'Cash In') $masuk_hari_ini += $trx['amount'];
                if ($trx['type'] === 'Cash Out') $keluar_hari_ini += $trx['amount'];
            }
        }

        $saldo_awal = $saldo_akhir - $masuk_hari_ini + $keluar_hari_ini;

        return view('finance/cash_index', [
            'title'           => 'Kas Operasional Harian',
            'tgl_filter'      => $tgl_filter,
            'transactions'    => $transactions,
            'saldo_awal'      => $saldo_awal,
            'masuk_hari_ini'  => $masuk_hari_ini,
            'keluar_hari_ini' => $keluar_hari_ini,
            'saldo_akhir'     => $saldo_akhir,
            'sisa_cash'       => $sisa_cash,
            'sisa_atm'        => $sisa_atm
        ]);
    }

    public function cash_store()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        try {
            $mode_input = $this->request->getPost('mode_input');
            $tgl        = $this->request->getPost('tanggal_input') ?: date('Y-m-d');
            $nominal    = (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount'));
            $pic_name   = session()->get('name') ?? 'Sistem';

            if ($nominal <= 0) {
                throw new \Exception("Nominal transaksi harus lebih dari 0.");
            }

            $dateCode = date('Ymd', strtotime($tgl));
            
            $lastTrx = $this->db->table('operational_cash')
                ->like('transaction_code', "TRX-$dateCode-", 'after')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            $newNumber = 0;
            if ($lastTrx) {
                $parts = explode('-', $lastTrx['transaction_code']);
                $newNumber = (int) end($parts);
            }

            $receiptFile = null;
            $file = $this->request->getFile('receipt_file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/receipts/', $newName);
                $receiptFile = $newName;
            }

            $this->db->transStart();
            
            $accService = new \App\Services\AccountingService();

            if ($mode_input === 'transaksi') {
                $type   = $this->request->getPost('type');
                $metode = $this->request->getPost('metode');
                $desc   = strtoupper(trim($this->request->getPost('description')));

                $accCode = ($metode === 'Cash') ? '1-1000' : '1-2000';
                $assetAcc = $this->db->table('chart_of_accounts')->where('account_code', $accCode)->get()->getRowArray();

                $lawanCode = ($type === 'Cash In') ? '4-1000' : '5-1000';
                $lawanAcc = $this->db->table('chart_of_accounts')->where('account_code', $lawanCode)->get()->getRowArray();

                if (!$assetAcc || !$lawanAcc) {
                    throw new \Exception("Akun jurnal tidak ditemukan di Chart of Accounts.");
                }

                if ($type === 'Cash Out') {
                    $saldoAkun = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$assetAcc['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoAkun < $nominal) throw new \Exception("Pencatatan ditolak! Saldo {$metode} Anda tidak mencukupi. (Sisa: Rp " . number_format($saldoAkun,0,',','.') . ")");
                }

                $trxCode = "TRX-$dateCode-" . str_pad($newNumber + 1, 3, '0', STR_PAD_LEFT);

                $journalItems = [];
                if ($type === 'Cash In') {
                    $journalItems[] = ['account_id' => $assetAcc['id'], 'debit' => $nominal, 'credit' => 0, 'memo' => "Kas masuk {$metode}"];
                    $journalItems[] = ['account_id' => $lawanAcc['id'], 'debit' => 0, 'credit' => $nominal, 'memo' => "Pendapatan Operasional"];
                } else {
                    $journalItems[] = ['account_id' => $lawanAcc['id'], 'debit' => $nominal, 'credit' => 0, 'memo' => "Beban operasional"];
                    $journalItems[] = ['account_id' => $assetAcc['id'], 'debit' => 0, 'credit' => $nominal, 'memo' => "Kas keluar {$metode}"];
                }

                $journalId = $accService->createJournal($tgl, "Kas Operasional: {$desc}", 'operational_cash', $trxCode, $nominal, $journalItems, $pic_name);

                $this->db->table('operational_cash')->insert([
                    'transaction_code' => $trxCode, 'transaction_date' => $tgl, 'type' => $type, 'metode' => $metode,
                    'category' => 'Operasional', 'amount' => $nominal, 'description' => $desc,
                    'pic_name' => $pic_name, 'receipt_file' => $receiptFile, 'journal_id' => $journalId, 'status' => 'POSTED'
                ]);

                $cashId = $this->db->insertID();
                $this->db->table('journals')->where('id', $journalId)->update(['source_id' => $cashId]);

            } elseif ($mode_input === 'mutasi') {
                $arah = $this->request->getPost('arah_mutasi');
                $desc = strtoupper(trim($this->request->getPost('description')));
                
                $akunKas = $this->db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
                $akunBank = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();
                
                if(!$akunKas || !$akunBank) throw new \Exception("Sistem Gagal: Akun Kas (1-1000) atau Bank (1-2000) tidak ditemukan.");

                $trxCode1 = "TRX-$dateCode-" . str_pad($newNumber + 1, 3, '0', STR_PAD_LEFT);
                $trxCode2 = "TRX-$dateCode-" . str_pad($newNumber + 2, 3, '0', STR_PAD_LEFT);

                $journalItems = [];

                if ($arah === 'atm_to_cash') {
                    $saldoBank = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunBank['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoBank < $nominal) throw new \Exception("Pencatatan Ditolak! Saldo Rekening Bank Anda tidak mencukupi untuk ditarik.");

                    $journalItems[] = ['account_id' => $akunKas['id'], 'debit' => $nominal, 'credit' => 0, 'memo' => 'Masuk Kas Laci'];
                    $journalItems[] = ['account_id' => $akunBank['id'], 'debit' => 0, 'credit' => $nominal, 'memo' => 'Keluar dari ATM'];

                    $journalId = $accService->createJournal($tgl, "Tarik Tunai: $desc", 'operational_cash', $trxCode1, $nominal, $journalItems, $pic_name);

                    $this->db->table('operational_cash')->insert(['transaction_code' => $trxCode1, 'transaction_date' => $tgl, 'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => "TARIK TUNAI (DARI ATM): $desc", 'pic_name' => $pic_name, 'journal_id' => $journalId, 'status' => 'POSTED']);
                    $this->db->table('operational_cash')->insert(['transaction_code' => $trxCode2, 'transaction_date' => $tgl, 'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => "TERIMA TUNAI (KE LACI): $desc", 'pic_name' => $pic_name, 'journal_id' => $journalId, 'status' => 'POSTED']);
                } else {
                    $saldoKas = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoKas < $nominal) throw new \Exception("Pencatatan Ditolak! Saldo Kas Laci Tunai tidak mencukupi untuk disetorkan.");

                    $journalItems[] = ['account_id' => $akunBank['id'], 'debit' => $nominal, 'credit' => 0, 'memo' => 'Masuk Rekening Bank'];
                    $journalItems[] = ['account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $nominal, 'memo' => 'Keluar dari Kas Laci'];

                    $journalId = $accService->createJournal($tgl, "Setor Tunai: $desc", 'operational_cash', $trxCode1, $nominal, $journalItems, $pic_name);

                    $this->db->table('operational_cash')->insert(['transaction_code' => $trxCode1, 'transaction_date' => $tgl, 'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => "SETOR TUNAI (DARI LACI): $desc", 'pic_name' => $pic_name, 'journal_id' => $journalId, 'status' => 'POSTED']);
                    $this->db->table('operational_cash')->insert(['transaction_code' => $trxCode2, 'transaction_date' => $tgl, 'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => "DANA MASUK (KE ATM): $desc", 'pic_name' => $pic_name, 'journal_id' => $journalId, 'status' => 'POSTED']);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Perintah penyimpanan dibatalkan di tingkat database.");
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi kas berhasil disimpan di tanggal ' . date('d M Y', strtotime($tgl)) . '.']);
            }
            return redirect()->back()->with('success', 'Transaksi kas berhasil disimpan.');
            
        } catch (\Throwable $e) {
            if ($this->db->transStatus() !== false) {
                $this->db->transRollback();
            }
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cash_delete($id)
    {
        try {
            if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
                throw new \Exception("Akses Ditolak.");
            }

            $trx = $this->db->table('operational_cash')->where('id', $id)->get()->getRowArray();
            if (!$trx) throw new \Exception("Data Kas Operasional ini sudah tidak ditemukan di database.");

            $this->db->transStart();

            if (!empty($trx['journal_id'])) {
                // Panggil Void Jurnal yang otomatis akan meng-CANCEL transaksi kas ini
                $accService = new AccountingService();
                $accService->voidJournal($trx['journal_id'], 'Dibatalkan langsung dari Kas Operasional', session()->get('name') ?? 'Sistem');
            } else {
                // Fallback jika transaksi tersebut tidak memiliki jurnal
                $this->db->table('operational_cash')->where('id', $id)->update(['status' => 'CANCELLED']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Sistem menolak untuk membatalkan karena terdapat data yang terkunci.");
            }

            return redirect()->back()->with('success', 'Transaksi Kas berhasil di-VOID. Saldo Anda telah dikembalikan!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }
}