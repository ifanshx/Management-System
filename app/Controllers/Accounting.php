<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Accounting extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // =========================================================
    // 1. DASHBOARD ACCOUNTING
    // =========================================================
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $summary = $this->db->query("
            SELECT account_type, SUM(calculated_balance) as total_balance
            FROM v_account_balances
            WHERE is_active = 1
            GROUP BY account_type
        ")->getResultArray();

        $pendapatan = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_type = 'PENDAPATAN'
        ")->getRowArray()['total'] ?? 0;

        $hpp = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_code = '5-1000'
        ")->getRowArray()['total'] ?? 0;

        $beban_ops = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_type = 'PERBELANJAAN'
            AND account_code != '5-1000'
        ")->getRowArray()['total'] ?? 0;

        // Kalkulasi Laba
        $laba_kotor  = $pendapatan - $hpp;
        $laba_bersih = $laba_kotor - $beban_ops;

        $recent_journals = $this->db->table('journals')
            ->orderBy('id', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        return view('accounting/index', [
            'title'           => 'Dasbor Finansial Eksekutif',
            'summary'         => $summary,
            'pendapatan'      => $pendapatan,
            'hpp'             => $hpp,
            'beban_ops'       => $beban_ops,
            'laba_kotor'      => $laba_kotor,
            'laba_bersih'     => $laba_bersih,
            'recent_journals' => $recent_journals
        ]);
    }

    // =========================================================
    // 2. HALAMAN JURNAL UMUM
    // =========================================================
    public function journal()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $accounts = $this->db->table('chart_of_accounts')
            ->where('is_active', 1)
            ->orderBy('account_code', 'ASC')
            ->get()->getResultArray();

        $recent_journals = $this->db->table('journals')
            ->orderBy('id', 'DESC')
            ->limit(15)
            ->get()->getResultArray();

        return view('accounting/journal', [
            'title'           => 'Pencatatan Jurnal Umum',
            'accounts'        => $accounts,
            'recent_journals' => $recent_journals
        ]);
    }

    // =========================================================
    // 3. STORE JURNAL
    // =========================================================
    public function store_journal()
    {
        try {
            $date        = $this->request->getPost('transaction_date');
            $description = trim($this->request->getPost('description'));
            $accounts    = $this->request->getPost('account_id');
            $debits      = $this->request->getPost('debit');
            $credits     = $this->request->getPost('credit');
            $lines       = $this->request->getPost('line_description');

            if (empty($accounts) || count($accounts) < 2) {
                throw new \Exception("Minimal harus ada 2 akun dalam satu jurnal.");
            }

            $totalDebit = 0;
            $totalCredit = 0;
            $validItems = [];

            for ($i = 0; $i < count($accounts); $i++) {
                $d = (float) str_replace(',', '', $debits[$i] ?? 0);
                $c = (float) str_replace(',', '', $credits[$i] ?? 0);

                if ($d > 0 || $c > 0) {
                    $totalDebit += $d;
                    $totalCredit += $c;

                    $validItems[] = [
                        'account_id'       => $accounts[$i],
                        'line_description' => $lines[$i] ?? null,
                        'debit'            => $d,
                        'credit'           => $c
                    ];
                }
            }

            if (count($validItems) < 2) {
                throw new \Exception("Minimal harus ada 2 baris transaksi valid.");
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception("Total Debit dan Kredit harus seimbang.");
            }

            $this->db->transStart();

            $datePrefix = date('Ym', strtotime($date));
            $lastJournal = $this->db->table('journals')
                ->like('journal_number', "JRN-$datePrefix", 'after')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            $seq = 1;
            if ($lastJournal) {
                $parts = explode('-', $lastJournal['journal_number']);
                $seq = intval(end($parts)) + 1;
            }

            $journalNumber = "JRN-" . $datePrefix . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            $this->db->table('journals')->insert([
                'journal_number'   => $journalNumber,
                'transaction_date' => $date,
                'description'      => $description,
                'reference_number' => null,
                'source_module'    => 'manual_journal',
                'source_id'        => null,
                'total_amount'     => $totalDebit,
                'status'           => 'POSTED',
                'created_by'       => session()->get('name') ?? 'Sistem'
            ]);

            $journalId = $this->db->insertID();

            foreach ($validItems as $item) {
                $this->db->table('journal_items')->insert([
                    'journal_id'       => $journalId,
                    'account_id'       => $item['account_id'],
                    'line_description' => $item['line_description'],
                    'debit'            => $item['debit'],
                    'credit'           => $item['credit']
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan jurnal.");
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Jurnal {$journalNumber} berhasil disimpan."
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // =========================================================
    // 4. VOID JURNAL (SYNC PENUH KE KAS DAN PROCUREMENT)
    // =========================================================
    public function void_journal($id)
    {
        try {
            $journal = $this->db->table('journals')->where('id', $id)->get()->getRowArray();
            if (!$journal) throw new \Exception("Jurnal tidak ditemukan.");

            if ($journal['status'] === 'VOID') {
                throw new \Exception("Jurnal sudah di-void sebelumnya.");
            }

            $this->db->transStart();
            
            // Panggil Service Sentral
            $accService = new \App\Services\AccountingService();
            $accService->voidJournal($id, 'Dibatalkan oleh user dari Buku Besar', session()->get('name') ?? 'Sistem');
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Sistem Gagal membatalkan jurnal.");
            }

            return redirect()->back()->with('success', 'Jurnal berhasil di-void! Mutasi terkait di Kas Operasional telah dihapus, dan status tagihan PO (jika ada) telah di-reset.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // 5. CHART OF ACCOUNTS
    // =========================================================
    public function coa()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $accounts = $this->db->query("
            SELECT coa.*, vab.calculated_balance
            FROM chart_of_accounts coa
            LEFT JOIN v_account_balances vab ON vab.id = coa.id
            ORDER BY coa.account_code ASC
        ")->getResultArray();

        return view('accounting/coa', [
            'title'    => 'Chart of Accounts',
            'accounts' => $accounts
        ]);
    }

    // =========================================================
    // 6. STORE ACCOUNT
    // =========================================================
    public function store_account()
    {
        try {
            $this->db->table('chart_of_accounts')->insert([
                'account_code'   => trim($this->request->getPost('account_code')),
                'account_name'   => trim($this->request->getPost('account_name')),
                'account_type'   => $this->request->getPost('account_type'),
                'parent_id'      => $this->request->getPost('parent_id') ?: null,
                'normal_balance' => $this->request->getPost('normal_balance'),
                'is_contra'      => $this->request->getPost('is_contra') ? 1 : 0,
                'is_active'      => 1,
                'notes'          => trim($this->request->getPost('notes'))
            ]);

            return redirect()->back()->with('success', 'Akun berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // 7. BUKU BESAR
    // =========================================================
    public function ledger()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $accountId = $this->request->getGet('account_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $accounts = $this->db->table('chart_of_accounts')
            ->where('is_active', 1)
            ->orderBy('account_code', 'ASC')
            ->get()->getResultArray();

        $ledger = [];
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = $this->db->table('chart_of_accounts')->where('id', $accountId)->get()->getRowArray();

            $ledger = $this->db->query("
                SELECT 
                    j.transaction_date,
                    j.journal_number,
                    j.description as journal_description,
                    ji.line_description,
                    ji.debit,
                    ji.credit
                FROM journal_items ji
                JOIN journals j ON j.id = ji.journal_id
                WHERE ji.account_id = ?
                AND j.status = 'POSTED'
                AND j.transaction_date BETWEEN ? AND ?
                ORDER BY j.transaction_date ASC, ji.id ASC
            ", [$accountId, $startDate, $endDate])->getResultArray();
        }

        return view('accounting/ledger', [
            'title'           => 'Buku Besar',
            'accounts'        => $accounts,
            'ledger'          => $ledger,
            'selectedAccount' => $selectedAccount,
            'accountId'       => $accountId,
            'startDate'       => $startDate,
            'endDate'         => $endDate
        ]);
    }

    // =========================================================
    // 8. PRINT REPORT
    // =========================================================
    public function print_report()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $summary = $this->db->query("
            SELECT account_type, SUM(calculated_balance) as total_balance
            FROM v_account_balances
            WHERE is_active = 1
            GROUP BY account_type
        ")->getResultArray();

        $pendapatan = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_type = 'PENDAPATAN'
        ")->getRowArray()['total'] ?? 0;

        $hpp = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_code = '5-1000'
        ")->getRowArray()['total'] ?? 0;

        $beban_ops = $this->db->query("
            SELECT SUM(calculated_balance) as total
            FROM v_account_balances
            WHERE account_type = 'PERBELANJAAN'
            AND account_code != '5-1000'
        ")->getRowArray()['total'] ?? 0;

        $company = [
            'company_name' => 'Noric Exhaust',
            'address'      => 'Pusat Manufaktur Knalpot',
            'phone'        => '-'
        ];

        return view('accounting/print_report', [
            'title'      => 'Laporan Keuangan',
            'summary'    => $summary,
            'pendapatan' => $pendapatan,
            'hpp'        => $hpp,
            'beban_ops'  => $beban_ops,
            'company'    => $company
        ]);
    }
}