<?php

namespace App\Controllers;

use App\Models\CashAdvanceModel;
use App\Models\EmployeeModel;

class CashAdvance extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin' && session()->get('department') !== 'Manajemen & HRD')) {
            return redirect()->to('/portal');
        }

        $empModel = new EmployeeModel();
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
            'employees' => $empModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll()
        ];

        return view('cash_advance/index', $data);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $caModel = new CashAdvanceModel();
        $empModel = new EmployeeModel();
        $cashModel = new \App\Models\OperationalCashModel();
        $db = \Config\Database::connect();

        $employee_id = $this->request->getPost('employee_id');
        $totalAmount = (float) str_replace(['Rp', '.', ' '], '', $this->request->getPost('amount') ?? 0);
        $cicilan     = (int) $this->request->getPost('tenure');
        $firstTempo  = $this->request->getPost('first_tempo_date');
        $description = $this->request->getPost('description');

        if ($totalAmount <= 0) return redirect()->back()->with('error', 'Nominal kasbon tidak valid.');

        $emp = $empModel->where('employee_id', $employee_id)->first();
        if (!$emp) return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');

        $amountPerCicilan = floor($totalAmount / $cicilan);
        $sisaPembagian    = $totalAmount - ($amountPerCicilan * $cicilan);

        $db->transStart();
        try {
            // 1. Simpan Data Cicilan ke Tabel Kasbon
            for ($i = 0; $i < $cicilan; $i++) {
                $tempoDate = date('Y-m-d', strtotime($firstTempo . " +{$i} weeks"));
                $finalAmount = ($i == $cicilan - 1) ? ($amountPerCicilan + $sisaPembagian) : $amountPerCicilan;

                $caModel->insert([
                    'employee_id' => $employee_id, 'date' => date('Y-m-d'), 'amount' => $finalAmount,
                    'tempo_date'  => $tempoDate, 'description' => $description . " (Cicilan " . ($i+1) . " dari {$cicilan})",
                    'status'      => 'Belum Lunas'
                ]);
            }

            // 2. OTOMATISASI ACCOUNTING: Uang Laci Perusahaan Berkurang SEKALI sejumlah Total Kasbon
            $akunKas = $db->table('chart_of_accounts')->where('account_code', '1-1000')->get()->getRowArray();
            $akunPiutang = $db->table('chart_of_accounts')->where('account_code', '1-4000')->get()->getRowArray();

            if($akunKas && $akunPiutang) {
                // Cek Saldo
                $saldoKas = $this->db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0;
                if ($saldoKas < $totalAmount) throw new \Exception("Gagal: Saldo Kas Laci tidak cukup untuk memberikan pinjaman ini.");

                $db->table('journals')->insert([
                    'journal_number'   => 'JRN-KSB-'.time(), 'transaction_date' => date('Y-m-d'),
                    'description'      => "Pencairan Kasbon: " . $emp['name'],
                    'total_amount'     => $totalAmount, 'status' => 'POSTED', 'created_by' => session()->get('name')
                ]);
                $jrnId = $db->insertID();

                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunPiutang['id'], 'debit' => $totalAmount, 'credit' => 0]);
                $db->table('journal_items')->insert(['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $totalAmount]);

                $dateCode = date('Ymd');
                $lastTrx = $cashModel->like('transaction_code', "TRX-$dateCode-", 'after')->orderBy('id', 'DESC')->first();
                $newNumber = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
                
                $cashModel->insert([
                    'transaction_code' => "TRX-$dateCode-$newNumber", 'transaction_date' => date('Y-m-d'),
                    'type' => 'Cash Out', 'metode' => 'Cash', 'category' => 'Kasbon Karyawan',
                    'amount' => $totalAmount, 'description' => "Pencairan Kasbon: " . $emp['name'] . " - " . $description,
                    'pic_name' => session()->get('name'), 'journal_id' => $jrnId, 'status' => 'POSTED'
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) throw new \Exception("Gagal menyimpan ke database.");

            return redirect()->to('/cash_advance')->with('success', "Kasbon Rp " . number_format($totalAmount, 0, ',', '.') . " berhasil dicairkan dari Laci & dibagi jadi <b>{$cicilan} tagihan</b>.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $caModel = new CashAdvanceModel();
        $kasbon = $caModel->find($id);

        if ($kasbon['status'] === 'Lunas') {
            return redirect()->back()->with('error', 'Kasbon yang sudah dipotong (Lunas) tidak bisa dihapus. Batalkan dulu dokumen Payroll-nya.');
        }

        // Catatan: Jika dihapus manual di sini, ini hanya menghapus jadwal cicilan. 
        // Uang yang sudah keluar (Cash Out) tidak otomatis kembali karena 1 cicilan bukan berarti 1 transaksi utuh. 
        // User harus membatalkan (VOID) pengeluaran di menu Keuangan secara manual.
        
        $caModel->delete($id);
        return redirect()->back()->with('success', 'Jadwal cicilan tagihan kasbon berhasil dihapus. (Note: Jangan lupa VOID transaksi di menu Keuangan jika uang batal diberikan).');
    }
}