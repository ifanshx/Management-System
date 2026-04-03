<?php

namespace App\Controllers;

use App\Models\OperationalCashModel;

class Finance extends BaseController
{
    protected $db;
    protected $cashModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cashModel = new OperationalCashModel();
    }

    // =========================================================
    // 1. KAS OPERASIONAL
    // =========================================================
    public function cash_index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $tgl_filter = $this->request->getGet('tgl') ?? date('Y-m-d');

        // Ambil riwayat kas sesuai tanggal
        $transactions = $this->cashModel
            ->where('transaction_date', $tgl_filter)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Ambil saldo REAL-TIME dari Database View
        $akunKas = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE account_code = '1-1000'")->getRowArray();
        $akunBank = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE account_code = '1-2000'")->getRowArray();

        $sisa_cash = $akunKas['calculated_balance'] ?? 0;
        $sisa_atm  = $akunBank['calculated_balance'] ?? 0;
        $saldo_akhir = $sisa_cash + $sisa_atm;

        $masuk_hari_ini = 0;
        $keluar_hari_ini = 0;

        foreach ($transactions as $trx) {
            // Hanya hitung yang POSTED
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

    // =========================================================
    // 2. STORE KAS OPERASIONAL + AUTO JURNAL
    // =========================================================
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
                throw new \Exception("Nominal harus lebih dari 0.");
            }

            // Generate Kode TRX
            $dateCode = date('Ymd', strtotime($tgl));
            $lastTrx = $this->cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $trxCode = "TRX-$dateCode-$newNumber";

            $receiptFile = null;
            $file = $this->request->getFile('receipt_file');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/receipts/', $newName);
                $receiptFile = $newName;
            }

            $this->db->transStart();

            if ($mode_input === 'transaksi') {
                $type   = $this->request->getPost('type');
                $metode = $this->request->getPost('metode');
                $desc   = strtoupper(trim($this->request->getPost('description')));

                $accCode = ($metode === 'Cash') ? '1-1000' : '1-2000';
                $assetAcc = $this->db->table('chart_of_accounts')->where('account_code', $accCode)->get()->getRowArray();

                $lawanCode = ($type === 'Cash In') ? '4-1000' : '5-1000';
                $lawanAcc = $this->db->table('chart_of_accounts')->where('account_code', $lawanCode)->get()->getRowArray();

                if (!$assetAcc || !$lawanAcc) {
                    throw new \Exception("Akun jurnal tidak ditemukan.");
                }

                // Cek Saldo untuk Cash Out
                if ($type === 'Cash Out') {
                    $saldoAkun = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$assetAcc['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoAkun < $nominal) throw new \Exception("Saldo {$metode} tidak mencukupi.");
                }

                // 1. Simpan Header Jurnal
                $journalNumber = 'JRN-' . date('Ym', strtotime($tgl)) . '-' . time();

                $this->db->table('journals')->insert([
                    'journal_number'   => $journalNumber,
                    'transaction_date' => $tgl,
                    'description'      => "Kas Operasional: {$desc}",
                    'reference_number' => $trxCode,
                    'source_module'    => 'operational_cash',
                    'total_amount'     => $nominal,
                    'status'           => 'POSTED',
                    'created_by'       => $pic_name
                ]);
                $journalId = $this->db->insertID();

                // 2. Detail Jurnal
                if ($type === 'Cash In') {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $assetAcc['id'], 'line_description' => "Kas masuk {$metode}", 'debit' => $nominal, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $lawanAcc['id'], 'line_description' => "Pendapatan", 'debit' => 0, 'credit' => $nominal]);
                } else {
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $lawanAcc['id'], 'line_description' => "Beban operasional", 'debit' => $nominal, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $assetAcc['id'], 'line_description' => "Kas keluar {$metode}", 'debit' => 0, 'credit' => $nominal]);
                }

                // 3. Simpan operational cash
                $this->cashModel->insert([
                    'transaction_code' => $trxCode, 'transaction_date' => $tgl, 'type' => $type, 'metode' => $metode,
                    'category' => 'Operasional', 'amount' => $nominal, 'description' => $desc,
                    'pic_name' => $pic_name, 'receipt_file' => $receiptFile, 'journal_id' => $journalId, 'status' => 'POSTED'
                ]);

                $this->db->table('journals')->where('id', $journalId)->update(['source_id' => $this->db->insertID()]);
            } 
            elseif ($mode_input === 'mutasi') {
                $arah = $this->request->getPost('arah_mutasi');
                $akunKas = $this->db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
                $akunBank = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();
                
                if(!$akunKas || !$akunBank) throw new \Exception("Akun Kas/Bank tidak ditemukan di sistem.");

                $journalNumber = 'JRN-' . date('Ym', strtotime($tgl)) . '-' . time();

                $this->db->table('journals')->insert([
                    'journal_number'   => $journalNumber, 'transaction_date' => $tgl,
                    'description'      => ($arah === 'atm_to_cash') ? 'Tarik Tunai (ATM ke Laci)' : 'Setor Tunai (Laci ke ATM)',
                    'total_amount'     => $nominal, 'status' => 'POSTED', 'created_by' => $pic_name
                ]);
                $jrnId = $this->db->insertID();

                if ($arah === 'atm_to_cash') {
                    $saldoBank = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunBank['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoBank < $nominal) throw new \Exception("Saldo Bank tidak cukup untuk ditarik.");

                    $this->cashModel->insert(['transaction_code' => "TRX-$dateCode-" . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT), 'transaction_date' => $tgl, 'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => 'TARIK TUNAI (DARI ATM)', 'pic_name' => $pic_name, 'journal_id' => $jrnId]);
                    $this->cashModel->insert(['transaction_code' => "TRX-$dateCode-" . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT), 'transaction_date' => $tgl, 'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => 'TERIMA TUNAI (KE LACI)', 'pic_name' => $pic_name, 'journal_id' => $jrnId]);
                    
                    $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'debit' => $nominal, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBank['id'], 'debit' => 0, 'credit' => $nominal]);
                } else {
                    $saldoKas = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
                    if ($saldoKas < $nominal) throw new \Exception("Saldo Kas Laci tidak cukup disetor.");

                    $this->cashModel->insert(['transaction_code' => "TRX-$dateCode-" . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT), 'transaction_date' => $tgl, 'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => 'SETOR TUNAI (DARI LACI)', 'pic_name' => $pic_name, 'journal_id' => $jrnId]);
                    $this->cashModel->insert(['transaction_code' => "TRX-$dateCode-" . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT), 'transaction_date' => $tgl, 'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Mutasi', 'amount' => $nominal, 'description' => 'DANA MASUK (KE ATM)', 'pic_name' => $pic_name, 'journal_id' => $jrnId]);
                    
                    $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunBank['id'], 'debit' => $nominal, 'credit' => 0]);
                    $this->db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $nominal]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan transaksi kas.");

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi kas berhasil disimpan di tanggal ' . date('d M Y', strtotime($tgl)) . '.']);
            }
            return redirect()->back()->with('success', 'Transaksi kas berhasil disimpan.');
        } catch (\Throwable $e) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // 3. CANCEL TRANSAKSI KAS (DENGAN ZERO-OUT REVERSAL)
    // =========================================================
    public function cash_delete($id)
    {
        try {
            $trx = $this->cashModel->find($id);
            if (!$trx) throw new \Exception("Transaksi tidak ditemukan.");

            $this->db->transStart();

            if (!empty($trx['journal_id'])) {
                
                // 1. VOID JURNAL & NOL-KAN TOTAL
                $this->db->table('journals')->where('id', $trx['journal_id'])->update([
                    'status'       => 'VOID',
                    'total_amount' => 0,
                    'void_reason'  => 'Dibatalkan dari kas operasional',
                    'voided_at'    => date('Y-m-d H:i:s'),
                    'voided_by'    => session()->get('name') ?? 'Sistem'
                ]);

                // 2. NOL-KAN RINCIAN JURNAL AGAR v_account_balances KEMBALI NORMAL (REVERSAL)
                $this->db->table('journal_items')->where('journal_id', $trx['journal_id'])->update([
                    'debit'  => 0,
                    'credit' => 0
                ]);

                // 3. CANCEL SEMUA KAS YANG TERKAIT JURNAL INI (Berguna untuk Mutasi yang punya 2 baris)
                $this->db->table('operational_cash')
                         ->where('journal_id', $trx['journal_id'])
                         ->update(['status' => 'CANCELLED']);
                         
            } else {
                $this->cashModel->update($id, ['status' => 'CANCELLED']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal membatalkan transaksi pada sistem Database.");
            }

            return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan dan saldo dikembalikan.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}