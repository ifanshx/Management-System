<?php

namespace App\Controllers;
use App\Models\OperationalCashModel;

class Finance extends BaseController
{
    // --- 1. HALAMAN UTAMA BUKU KAS ---
    public function cash_index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $cashModel = new OperationalCashModel();
        $db = \Config\Database::connect();

        $tgl_filter = $this->request->getGet('tgl') ?? date('Y-m-d');

        $transactions = $cashModel->where('transaction_date', $tgl_filter)
                                  ->orderBy('created_at', 'ASC')
                                  ->findAll();

        // Saldo Awal Global
        $qAwal = $db->query("SELECT 
            SUM(CASE WHEN type='Cash In' THEN amount ELSE 0 END) as tot_masuk,
            SUM(CASE WHEN type='Cash Out' THEN amount ELSE 0 END) as tot_keluar
            FROM operational_cash WHERE transaction_date < ?", [$tgl_filter])->getRowArray();
        $saldo_awal = $qAwal['tot_masuk'] - $qAwal['tot_keluar'];

        // Transaksi Hari Ini
        $masuk_hari_ini = 0;
        $keluar_hari_ini = 0;
        foreach ($transactions as $trx) {
            if ($trx['type'] == 'Cash In') $masuk_hari_ini += $trx['amount'];
            if ($trx['type'] == 'Cash Out') $keluar_hari_ini += $trx['amount'];
        }

        $saldo_akhir = $saldo_awal + $masuk_hari_ini - $keluar_hari_ini;

        // Rincian Aset
        $qRincian = $db->query("SELECT 
            (SUM(CASE WHEN type='Cash In' AND metode='Cash' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type='Cash Out' AND metode='Cash' THEN amount ELSE 0 END)) as sisa_cash,
            (SUM(CASE WHEN type='Cash In' AND metode='ATM' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type='Cash Out' AND metode='ATM' THEN amount ELSE 0 END)) as sisa_atm
            FROM operational_cash WHERE transaction_date <= ?", [$tgl_filter])->getRowArray();
            
        $sisa_cash = $qRincian['sisa_cash'] ?? 0;
        $sisa_atm = $qRincian['sisa_atm'] ?? 0;

        $data = [
            'title'           => 'Buku Kas Harian',
            'tgl_filter'      => $tgl_filter,
            'transactions'    => $transactions,
            'saldo_awal'      => $saldo_awal,
            'masuk_hari_ini'  => $masuk_hari_ini,
            'keluar_hari_ini' => $keluar_hari_ini,
            'saldo_akhir'     => $saldo_akhir,
            'sisa_cash'       => $sisa_cash,
            'sisa_atm'        => $sisa_atm
        ];

        return view('finance/cash_index', $data);
    }

    // --- 2. PROSES SIMPAN TRANSAKSI & AUTO JURNAL (AJAX) ---
    public function cash_store()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $cashModel = new OperationalCashModel();
        $db = \Config\Database::connect();

        $mode_input = $this->request->getPost('mode_input');
        $tgl        = date('Y-m-d');
        $nominal    = str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount'));
        $pic_name   = session()->get('name');
        
        $dateCode = date('Ymd');
        
        $generateTrxCode = function() use ($cashModel, $dateCode) {
            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            return "TRX-$dateCode-$newNumber";
        };

        // File Upload
        $receiptFile = null;
        $file = $this->request->getFile('receipt_file');
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // (Skip validasi kompleks di backend saat AJAX demi kecepatan, kita asumsikan FE aman)
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/receipts/', $newName);
            $receiptFile = $newName;
        }

        if ($nominal > 0) {
            $db->transStart();
            
            try {
                if ($mode_input === 'transaksi') {
                    $type = $this->request->getPost('type');
                    $metode = $this->request->getPost('metode');
                    $desc = strtoupper($this->request->getPost('description'));
                    $trxCode = $generateTrxCode();

                    // 1. Simpan Kas
                    $dataToSave = [
                        'transaction_code' => $trxCode,
                        'transaction_date' => $tgl,
                        'type'             => $type,
                        'metode'           => $metode,
                        'category'         => 'Operasional',
                        'amount'           => $nominal,
                        'description'      => $desc,
                        'pic_name'         => $pic_name,
                        'receipt_file'     => $receiptFile
                    ];
                    $cashModel->insert($dataToSave);

                    // 2. AUTO JURNAL AKUNTANSI (ENTERPRISE MAGIC)
                    // Cari ID akun berdasarkan metode (Cash = 1-1000, ATM = 1-2000)
                    $accCode = ($metode === 'Cash') ? '1-1000' : '1-2000';
                    $assetAcc = $db->table('chart_of_accounts')->where('account_code', $accCode)->get()->getRowArray();
                    
                    // Untuk jurnal tandingannya: Jika uang masuk, anggap Pendapatan(4-1000). Jika keluar, anggap Beban Operasional(5-1000)
                    $lawanCode = ($type === 'Cash In') ? '4-1000' : '5-1000';
                    $lawanAcc = $db->table('chart_of_accounts')->where('account_code', $lawanCode)->get()->getRowArray();

                    if($assetAcc && $lawanAcc) {
                        $db->table('journals')->insert([
                            'journal_number'   => 'JRN-'.time(),
                            'transaction_date' => $tgl,
                            'description'      => "Auto-Jurnal Kas Operasional: $desc",
                            'total_amount'     => $nominal,
                            'created_by'       => $pic_name
                        ]);
                        $jrnId = $db->insertID();

                        if($type === 'Cash In') {
                            // Kas Bertambah (Debit Kas, Kredit Pendapatan)
                            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $assetAcc['id'], 'debit' => $nominal, 'credit' => 0]);
                            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $lawanAcc['id'], 'debit' => 0, 'credit' => $nominal]);
                            $db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$nominal, $assetAcc['id']]);
                            $db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$nominal, $lawanAcc['id']]);
                        } else {
                            // Kas Berkurang (Debit Beban, Kredit Kas)
                            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $lawanAcc['id'], 'debit' => $nominal, 'credit' => 0]);
                            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $assetAcc['id'], 'debit' => 0, 'credit' => $nominal]);
                            $db->query("UPDATE chart_of_accounts SET balance = balance - ? WHERE id = ?", [$nominal, $assetAcc['id']]);
                            $db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$nominal, $lawanAcc['id']]);
                        }
                    }
                    $msg = "Transaksi berhasil dicatat dan dibukukan ke Jurnal.";

                } elseif ($mode_input === 'mutasi') {
                    // MUTASI (PINDAH DANA ATM KE CASH / SEBALIKNYA)
                    $arah = $this->request->getPost('arah_mutasi');
                    
                    if ($arah === 'atm_to_cash') {
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Mutasi',
                            'amount' => $nominal, 'description' => 'TARIK TUNAI (DARI ATM KE CASH)', 'pic_name' => $pic_name
                        ]);
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Mutasi',
                            'amount' => $nominal, 'description' => 'TERIMA TUNAI (DARI ATM)', 'pic_name' => $pic_name
                        ]);
                        $msg = "Tarik Tunai berhasil.";
                    } else {
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Mutasi',
                            'amount' => $nominal, 'description' => 'SETOR TUNAI (DARI CASH KE ATM)', 'pic_name' => $pic_name
                        ]);
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Mutasi',
                            'amount' => $nominal, 'description' => 'DANA MASUK (SETORAN CASH)', 'pic_name' => $pic_name
                        ]);
                        $msg = "Setor Tunai berhasil.";
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    if($this->request->isAJAX()) return $this->response->setJSON(['status'=>'error', 'message'=>'Gagal memproses transaksi']);
                    return redirect()->back()->with('error', 'Gagal memproses transaksi.');
                }

                if($this->request->isAJAX()) return $this->response->setJSON(['status'=>'success', 'message'=>$msg]);
                return redirect()->to("/finance/cash_index")->with('success', $msg);

            } catch (\Exception $e) {
                $db->transRollback();
                if($this->request->isAJAX()) return $this->response->setJSON(['status'=>'error', 'message'=>$e->getMessage()]);
                return redirect()->back()->with('error', 'Sistem Error: ' . $e->getMessage());
            }
        }
    }

    // --- 3. PROSES HAPUS TRANSAKSI ---
    public function cash_delete($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $cashModel = new OperationalCashModel();
        $trx = $cashModel->find($id);
        
        if ($trx) {
            $tgl = $trx['transaction_date'];
            if ($trx['receipt_file'] && file_exists(ROOTPATH . 'public/uploads/receipts/' . $trx['receipt_file'])) {
                unlink(ROOTPATH . 'public/uploads/receipts/' . $trx['receipt_file']);
            }
            $cashModel->delete($id);
            return redirect()->to("/finance/cash_index?tgl=$tgl")->with('success', 'Transaksi berhasil dihapus. Saldo telah disesuaikan.');
        }
        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}