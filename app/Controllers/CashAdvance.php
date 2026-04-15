<?php

namespace App\Controllers;

use App\Models\CashAdvanceModel;
use App\Models\EmployeeModel;

class CashAdvance extends BaseController
{
    // Fungsi bantuan (Helper) untuk mengecek hak akses HRD/Admin
    private function isAuthorized()
    {
        $role = session()->get('role');
        $dept = strtolower(session()->get('department_name') ?? session()->get('department') ?? '');
        $isHR = str_contains($dept, 'hrd') || str_contains($dept, 'manajemen');
        return ($role === 'admin' || $isHR);
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal');
        }

        $empModel = new \App\Models\EmployeeModel();
        $db = \Config\Database::connect();
        
        $kasbon = $db->table('cash_advances')
                     ->select('cash_advances.*, employees.name')
                     ->join('employees', 'employees.employee_id = cash_advances.employee_id')
                     ->orderBy('cash_advances.status', 'ASC')
                     ->orderBy('cash_advances.tempo_date', 'ASC')
                     ->get()->getResultArray();

        $data = [
            'title'     => 'Kelola Kasbon Karyawan',
            'kasbon'    => $kasbon,
            'employees' => $empModel->select('employees.employee_id, employees.name, positions.name as position')
                                    ->join('positions', 'positions.id = employees.position_id', 'left')
                                    ->where('employees.is_active', 1)
                                    ->orderBy('employees.name', 'ASC')
                                    ->findAll()
        ];

        return view('cash_advance/index', $data);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal');
        }

        $caModel = new CashAdvanceModel();
        $db = \Config\Database::connect();

        $employee_id   = $this->request->getPost('employee_id');
        $totalAmount   = (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount') ?? 0);
        $cicilan       = (int) $this->request->getPost('tenure');
        $firstTempo    = $this->request->getPost('first_tempo_date');
        $description   = $this->request->getPost('description');
        
        // BACA METODE PENCAIRAN DARI FORM (Cash / Transfer)
        $paymentMethod = $this->request->getPost('payment_method') ?? 'Cash';

        if ($totalAmount <= 0) return redirect()->back()->with('error', 'Nominal kasbon tidak valid (harus lebih dari 0).');
        if (empty($employee_id)) return redirect()->back()->with('error', 'Karyawan harus dipilih.');

        $emp = $db->table('employees')->where('employee_id', $employee_id)->get()->getRowArray();
        if (!$emp) return redirect()->back()->with('error', 'Data karyawan tidak ditemukan di sistem.');

        $amountPerCicilan = floor($totalAmount / $cicilan);
        $sisaPembagian    = $totalAmount - ($amountPerCicilan * $cicilan);

        $db->transStart();
        try {
            // 1. Cek Akun Akuntansi Sumber Dana
            $accCodeKas = ($paymentMethod === 'Transfer') ? '1-2000' : '1-1000';
            $akunKas = $db->table('chart_of_accounts')->where('account_code', $accCodeKas)->get()->getRowArray();
            $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray();

            if (!$akunKas) {
                $db->table('chart_of_accounts')->insert([
                    'account_code' => $accCodeKas, 
                    'account_name' => ($paymentMethod === 'Transfer' ? 'Rekening Bank' : 'Kas Tunai / Laci'), 
                    'account_type' => 'ASET', 'normal_balance' => 'DEBIT', 'is_active' => 1
                ]);
                $akunKas = $db->table('chart_of_accounts')->where('account_code', $accCodeKas)->get()->getRowArray();
            }
            if (!$akunPiutang) {
                $db->table('chart_of_accounts')->insert(['account_code' => '1-4001', 'account_name' => 'Piutang Karyawan (Kasbon)', 'account_type' => 'ASET', 'normal_balance' => 'DEBIT', 'is_active' => 1]);
                $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4001')->get()->getRowArray();
            }

            // 2. Proteksi Pengecekan Saldo
            $saldoKas = $db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
            if ($saldoKas < $totalAmount) {
                $namaMetode = ($paymentMethod === 'Transfer') ? 'Rekening Bank (ATM)' : 'Kas Laci Tunai';
                throw new \Exception("Pencairan Ditolak! Saldo {$namaMetode} Anda tidak mencukupi. (Sisa Saldo: Rp " . number_format($saldoKas, 0, ',', '.') . ")");
            }

            // 3. Simpan Data Cicilan ke Tabel Kasbon
            for ($i = 0; $i < $cicilan; $i++) {
                $tempoDate = date('Y-m-d', strtotime($firstTempo . " +{$i} months"));
                $finalAmount = ($i == $cicilan - 1) ? ($amountPerCicilan + $sisaPembagian) : $amountPerCicilan;

                $caModel->insert([
                    'employee_id' => $employee_id, 
                    'date'        => date('Y-m-d'), 
                    'amount'      => $finalAmount,
                    'tempo_date'  => $tempoDate, 
                    'description' => trim($description) . " (Cicilan " . ($i+1) . " dari {$cicilan})",
                    'status'      => 'Belum Lunas'
                ]);
            }

            // 4. Jurnal Akuntansi Keluar (Aset Kas Berkurang, Aset Piutang Bertambah)
            $db->table('journals')->insert([
                'journal_number'   => 'JRN-KSB-'.time(), 
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pencairan Kasbon: " . $emp['name'] . " via " . strtoupper($paymentMethod),
                'total_amount'     => $totalAmount, 
                'status'           => 'POSTED', 
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $jrnId = $db->insertID();

            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunPiutang['id'], 'debit' => $totalAmount, 'credit' => 0, 'line_description' => 'Piutang Kasbon Bertambah']);
            $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $totalAmount, 'line_description' => 'Kas Keluar Pencairan']);

            // 5. Catat Riwayat Kas Operasional
            $dateCode = date('Ymd');
            $lastTrx = $db->table('operational_cash')->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            
            $db->table('operational_cash')->insert([
                'transaction_code' => "TRX-$dateCode-$newNumber", 
                'transaction_date' => date('Y-m-d'),
                'type'             => 'Cash Out', 
                'metode'           => ($paymentMethod === 'Transfer' ? 'ATM' : 'Cash'), 
                'category'         => 'Kasbon Karyawan',
                'amount'           => $totalAmount, 
                'description'      => "Pencairan Kasbon: " . $emp['name'] . " - " . trim($description),
                'pic_name'         => session()->get('name') ?? 'System', 
                'journal_id'       => $jrnId,
                'status'           => 'POSTED'
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Sistem Gagal menyimpan transaksi Jurnal & Kasbon. Silakan coba lagi.");
            }

            return redirect()->to('/cash_advance')->with('success', "Kasbon Rp " . number_format($totalAmount, 0, ',', '.') . " berhasil dicairkan melalui <b>" . strtoupper($paymentMethod) . "</b>.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal');
        }

        $caModel = new CashAdvanceModel();
        $kasbon = $caModel->find($id);

        if ($kasbon['status'] === 'Lunas') {
            return redirect()->back()->with('error', 'Kasbon yang sudah dipotong (Lunas) tidak bisa dihapus. Batalkan dulu dokumen Payroll-nya.');
        }
        
        $caModel->delete($id);
        return redirect()->back()->with('success', 'Jadwal cicilan tagihan kasbon berhasil dihapus. (Note: Jangan lupa VOID transaksi pengeluaran di menu Jurnal Akuntansi/Buku Besar).');
    }
}