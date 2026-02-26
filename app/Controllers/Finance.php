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

        // Filter Tanggal (Default: Hari ini)
        $tgl_filter = $this->request->getGet('tgl') ?? date('Y-m-d');

        // Tarik data tabel berdasarkan filter tanggal
        $transactions = $cashModel->where('transaction_date', $tgl_filter)
                                  ->orderBy('created_at', 'ASC')
                                  ->findAll();

        // LOGIKA PERHITUNGAN SALDO (Diadaptasi dari kode Native Anda)
        $db = \Config\Database::connect();
        
        // A. Saldo Awal Global (Sebelum tanggal filter)
        $qAwal = $db->query("SELECT 
            SUM(CASE WHEN type='Cash In' THEN amount ELSE 0 END) as tot_masuk,
            SUM(CASE WHEN type='Cash Out' THEN amount ELSE 0 END) as tot_keluar
            FROM operational_cash WHERE transaction_date < ?", [$tgl_filter])->getRowArray();
        $saldo_awal = $qAwal['tot_masuk'] - $qAwal['tot_keluar'];

        // B. Transaksi Hari Ini
        $masuk_hari_ini = 0;
        $keluar_hari_ini = 0;
        foreach ($transactions as $trx) {
            if ($trx['type'] == 'Cash In') $masuk_hari_ini += $trx['amount'];
            if ($trx['type'] == 'Cash Out') $keluar_hari_ini += $trx['amount'];
        }

        // C. Saldo Akhir Global
        $saldo_akhir = $saldo_awal + $masuk_hari_ini - $keluar_hari_ini;

        // D. Rincian Aset (Cash vs ATM) s/d Hari Ini
        $qRincian = $db->query("SELECT 
            (SUM(CASE WHEN type='Cash In' AND metode='Cash' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type='Cash Out' AND metode='Cash' THEN amount ELSE 0 END)) as sisa_cash,
            (SUM(CASE WHEN type='Cash In' AND metode='ATM' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type='Cash Out' AND metode='ATM' THEN amount ELSE 0 END)) as sisa_atm
            FROM operational_cash WHERE transaction_date <= ?", [$tgl_filter])->getRowArray();
            
        $sisa_cash = $qRincian['sisa_cash'] ?? 0;
        $sisa_atm = $qRincian['sisa_atm'] ?? 0;

        $data = [
            'title'           => 'Buku Kas Harian | Noric Workspace',
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

    // --- 2. PROSES SIMPAN TRANSAKSI & MUTASI ---
    public function cash_store()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $cashModel = new OperationalCashModel();
        $db = \Config\Database::connect();

        $mode_input = $this->request->getPost('mode_input');
        $tgl        = $this->request->getPost('transaction_date');
        $nominal    = str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount'));
        $pic_name   = session()->get('name'); // PIC Otomatis dari User Login
        
        $dateCode = date('Ymd');
        
        // Helper Bikin Kode TRX
        $generateTrxCode = function() use ($cashModel, $dateCode) {
            $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            return "TRX-$dateCode-$newNumber";
        };

        if ($nominal > 0) {
            $db->transStart();
            
            try {
                if ($mode_input === 'transaksi') {
                    // --- LOGIKA TRANSAKSI BIASA ---
                    $dataToSave = [
                        'transaction_code' => $generateTrxCode(),
                        'transaction_date' => $tgl,
                        'type'             => $this->request->getPost('type'), 
                        'metode'           => $this->request->getPost('metode'),
                        'category'         => 'Operasional', // <-- Kategori Otomatis Default
                        'amount'           => $nominal,
                        'description'      => strtoupper($this->request->getPost('description')),
                        'pic_name'         => $pic_name
                    ];
                    $cashModel->insert($dataToSave);
                    $msg = "Transaksi berhasil dicatat.";

                } elseif ($mode_input === 'mutasi') {
                    // --- LOGIKA PINDAH DANA (MUTASI) ---
                    $arah = $this->request->getPost('arah_mutasi');
                    
                    if ($arah === 'atm_to_cash') {
                        // 1. Tarik dari ATM
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash Out', 'metode' => 'ATM', 'category' => 'Mutasi Dana',
                            'amount' => $nominal, 'description' => 'TARIK TUNAI (DARI ATM KE CASH)', 'pic_name' => $pic_name
                        ]);
                        // 2. Masuk ke Cash
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash In', 'metode' => 'Cash', 'category' => 'Mutasi Dana',
                            'amount' => $nominal, 'description' => 'TERIMA TUNAI (DARI ATM)', 'pic_name' => $pic_name
                        ]);
                        $msg = "Tarik Tunai berhasil (Dana pindah ke Dompet Cash).";
                    } else {
                        // 1. Setor dari Cash
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Mutasi Dana',
                            'amount' => $nominal, 'description' => 'SETOR TUNAI (DARI CASH KE ATM)', 'pic_name' => $pic_name
                        ]);
                        // 2. Masuk ke ATM
                        $cashModel->insert([
                            'transaction_code' => $generateTrxCode(), 'transaction_date' => $tgl,
                            'type' => 'Cash In', 'metode' => 'ATM', 'category' => 'Mutasi Dana',
                            'amount' => $nominal, 'description' => 'DANA MASUK (SETORAN CASH)', 'pic_name' => $pic_name
                        ]);
                        $msg = "Setor Tunai berhasil (Dana pindah ke Rekening ATM).";
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return redirect()->back()->with('error', 'Gagal memproses transaksi.');
                }
                return redirect()->to("/finance/cash_index?tgl=$tgl")->with('success', $msg);

            } catch (\Exception $e) {
                $db->transRollback();
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
            $cashModel->delete($id);
            return redirect()->to("/finance/cash_index?tgl=$tgl")->with('success', 'Transaksi berhasil dihapus. Saldo telah disesuaikan.');
        }
        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}