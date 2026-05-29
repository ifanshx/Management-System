<?php

namespace App\Controllers;

use App\Models\CashAdvanceModel;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\RedirectResponse;

class CashAdvance extends BaseController
{
    /**
     * Memeriksa otorisasi khusus HRD/Manajemen/Admin
     */
    private function isAuthorized(): bool
    {
        $role = session()->get('role');
        $dept = strtolower((string)(session()->get('department_name') ?? session()->get('department') ?? ''));
        $isHR = str_contains($dept, 'hrd') || str_contains($dept, 'manajemen');
        
        return ($role === 'admin' || $isHR);
    }

    /**
     * Menampilkan Dashboard Kasbon
     */
    public function index(): string|RedirectResponse
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal')->with('error', 'Akses Ditolak.');
        }

        $empModel = new EmployeeModel();
        $db = \Config\Database::connect();
        
        $kasbon = $db->table('cash_advances')
            ->select('cash_advances.*, employees.name')
            ->join('employees', 'employees.employee_id = cash_advances.employee_id')
            ->orderBy('cash_advances.status', 'ASC')
            ->orderBy('cash_advances.tempo_date', 'ASC')
            ->get()->getResultArray();

        // FIX: SELECT employees.status ALIAS emp_status
        $employees = $empModel->select('employees.employee_id, employees.name, employees.status as emp_status, positions.name as position')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('employees.is_active', 1)
            ->orderBy('employees.name', 'ASC')
            ->findAll();

        $data = [
            'title'     => 'Kelola Kasbon Karyawan',
            'kasbon'    => $kasbon,
            'employees' => $employees
        ];

        return view('cash_advance/index', $data);
    }

    /**
     * Memproses Pencairan Kasbon Baru (Support Multi-Tenure Type)
     */
    public function store(): RedirectResponse
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal');
        }

        $rules = [
            'employee_id'      => 'required',
            'amount'           => 'required',
            'tenure'           => 'required|is_natural_no_zero',
            'tenure_type'      => 'required|in_list[days,weeks,months]',
            'first_tempo_date' => 'required|valid_date',
            'payment_method'   => 'required|in_list[Cash,Transfer]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Validasi gagal. Pastikan form diisi dengan lengkap dan benar.');
        }

        $db            = \Config\Database::connect();
        $caModel       = new CashAdvanceModel();
        
        $employee_id   = $this->request->getPost('employee_id');
        $totalAmount   = (float) str_replace(['Rp', '.', ' '], '', (string)$this->request->getPost('amount'));
        $cicilan       = (int) $this->request->getPost('tenure');
        $tenureType    = (string) $this->request->getPost('tenure_type');
        $firstTempo    = (string) $this->request->getPost('first_tempo_date');
        $description   = (string) $this->request->getPost('description');
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($totalAmount <= 0) {
            return redirect()->back()->with('error', 'Nominal kasbon tidak valid.');
        }

        $emp = $db->table('employees')->where('employee_id', $employee_id)->get()->getRowArray();
        if (!$emp) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');
        }

        $amountPerCicilan = floor($totalAmount / $cicilan);
        $sisaPembagian    = $totalAmount - ($amountPerCicilan * $cicilan);
        $typeIndo         = match($tenureType) { 'days' => 'Harian', 'weeks' => 'Mingguan', 'months' => 'Bulanan', default => 'Bulan' };

        $db->transStart();
        try {
            $accCodeKas  = ($paymentMethod === 'Transfer') ? '1-2000' : '1-1000';
            $akunKas     = $this->getOrCreateAccount($db, $accCodeKas, ($paymentMethod === 'Transfer' ? 'Rekening Bank' : 'Kas Tunai / Laci'));
            $akunPiutang = $this->getOrCreateAccount($db, '1-4001', 'Piutang Karyawan (Kasbon)');

            $saldoKas = (float) ($db->query("SELECT calculated_balance FROM v_account_balances WHERE id = ?", [$akunKas['id']])->getRowArray()['calculated_balance'] ?? 0);
            if ($saldoKas < $totalAmount) {
                $metode = ($paymentMethod === 'Transfer') ? 'Rekening Bank' : 'Kas Laci';
                throw new \RuntimeException("Saldo {$metode} tidak cukup (Sisa: Rp " . number_format($saldoKas, 0, ',', '.') . ").");
            }

            for ($i = 0; $i < $cicilan; $i++) {
                $tempoDate = date('Y-m-d', strtotime($firstTempo . " +{$i} {$tenureType}"));
                $finalAmount = ($i === $cicilan - 1) ? ($amountPerCicilan + $sisaPembagian) : $amountPerCicilan;

                $caModel->insert([
                    'employee_id' => $employee_id, 
                    'date'        => date('Y-m-d'), 
                    'amount'      => $finalAmount,
                    'tempo_date'  => $tempoDate, 
                    'description' => trim($description) . " (Cicilan " . ($i+1) . " dari {$cicilan} - {$typeIndo})",
                    'status'      => 'Belum Lunas'
                ]);
            }

            $db->table('journals')->insert([
                'journal_number'   => 'JRN-KSB-' . time(), 
                'transaction_date' => date('Y-m-d'),
                'description'      => "Pencairan Kasbon: {$emp['name']} via " . strtoupper($paymentMethod),
                'total_amount'     => $totalAmount, 
                'status'           => 'POSTED', 
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $jrnId = $db->insertID();

            $db->table('journal_items')->insertBatch([
                ['journal_id' => $jrnId, 'account_id' => $akunPiutang['id'], 'debit' => $totalAmount, 'credit' => 0, 'line_description' => 'Piutang Bertambah'],
                ['journal_id' => $jrnId, 'account_id' => $akunKas['id'], 'debit' => 0, 'credit' => $totalAmount, 'line_description' => 'Kas Keluar']
            ]);

            $dateCode = date('Ymd');
            $lastTrx  = $db->table('operational_cash')->like('transaction_code', "TRX-{$dateCode}-", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $newSeq   = $lastTrx ? str_pad((int) substr($lastTrx['transaction_code'], -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            
            $db->table('operational_cash')->insert([
                'transaction_code' => "TRX-{$dateCode}-{$newSeq}", 
                'transaction_date' => date('Y-m-d'),
                'type'             => 'Cash Out', 
                'metode'           => ($paymentMethod === 'Transfer' ? 'ATM' : 'Cash'), 
                'category'         => 'Kasbon Karyawan',
                'amount'           => $totalAmount, 
                'description'      => "Pencairan Kasbon: {$emp['name']} - " . trim($description),
                'pic_name'         => session()->get('name') ?? 'System', 
                'journal_id'       => $jrnId,
                'status'           => 'POSTED'
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException("Transaksi Database Gagal.");
            }

            return redirect()->to('/cash_advance')->with('success', "Kasbon Rp " . number_format($totalAmount, 0, ',', '.') . " berhasil dicairkan.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete(string $id): RedirectResponse
    {
        if (!session()->get('isLoggedIn') || !$this->isAuthorized()) {
            return redirect()->to('/portal');
        }

        $caModel = new CashAdvanceModel();
        $kasbon  = $caModel->find($id);

        if (!$kasbon) {
            return redirect()->back()->with('error', 'Data kasbon tidak ditemukan.');
        }

        if ($kasbon['status'] === 'Lunas') {
            return redirect()->back()->with('error', 'Ditolak: Kasbon berstatus Lunas tidak bisa dihapus.');
        }
        
        $caModel->delete($id);
        return redirect()->back()->with('success', 'Jadwal cicilan tagihan kasbon berhasil dihapus. (Jangan lupa VOID transaksi di Buku Besar jika dana fisik sudah keluar).');
    }

    private function getOrCreateAccount($db, string $code, string $name): array
    {
        $account = $db->table('chart_of_accounts')->where('account_code', $code)->get()->getRowArray();
        if (!$account) {
            $db->table('chart_of_accounts')->insert([
                'account_code' => $code, 'account_name' => $name, 
                'account_type' => 'ASET', 'normal_balance' => 'DEBIT', 'is_active' => 1
            ]);
            $account = $db->table('chart_of_accounts')->where('account_code', $code)->get()->getRowArray();
        }
        return $account;
    }
}