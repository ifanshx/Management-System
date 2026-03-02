<?php

namespace App\Controllers;

class Accounting extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

   // --- 1. DASBOR AKUNTANSI ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Ringkasan Saldo (Balance) berdasarkan Tipe Akun
        $summary = $this->db->query("
            SELECT account_type, SUM(balance) as total_balance 
            FROM chart_of_accounts 
            GROUP BY account_type
        ")->getResultArray();

        // Ambil 5 Jurnal Terakhir untuk Histori
        $recent_journals = $this->db->table('journals')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $data = [
            'title'           => 'Dasbor Finansial & Buku Besar',
            'summary'         => $summary,
            'recent_journals' => $recent_journals
        ];

        return view('accounting/index', $data);
    }
    // --- 2. HALAMAN KEMASUKAN JURNAL UMUM ---
    public function journal()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $accounts = $this->db->table('chart_of_accounts')->orderBy('account_code', 'ASC')->get()->getResultArray();
        
        $recent_journals = $this->db->table('journals')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();

        $data = [
            'title'           => 'Kemasukan Jurnal Umum (Double-Entry)',
            'accounts'        => $accounts,
            'recent_journals' => $recent_journals
        ];

        return view('accounting/journal', $data);
    }

    // --- 3. PROSES SIMPAN JURNAL (WAJIB SEIMBANG DEBIT & KREDIT) ---
    public function store_journal()
    {
        try {
            $date        = $this->request->getPost('transaction_date');
            $description = $this->request->getPost('description');
            $accounts    = $this->request->getPost('account_id'); // Array
            $debits      = $this->request->getPost('debit'); // Array
            $credits     = $this->request->getPost('credit'); // Array

            if (empty($accounts) || count($accounts) < 2) {
                throw new \Exception("Satu jurnal memerlukan sekurang-kurangnya 2 akaun (Debit dan Kredit).");
            }

            $totalDebit = 0;
            $totalCredit = 0;
            $validItems = [];

            // Kira jumlah Debit dan Kredit
            for ($i = 0; $i < count($accounts); $i++) {
                $d = (float)($debits[$i] ?? 0);
                $c = (float)($credits[$i] ?? 0);

                if ($d > 0 || $c > 0) {
                    $totalDebit += $d;
                    $totalCredit += $c;
                    $validItems[] = [
                        'account_id' => $accounts[$i],
                        'debit'      => $d,
                        'credit'     => $c
                    ];
                }
            }

            // HUKUM MUTLAK AKUNTANSI: DEBIT MESTI SAMA DENGAN KREDIT
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception("Transaksi DITOLAK! Jumlah Debit (Rp " . number_format($totalDebit, 0, ',', '.') . ") tidak sama dengan Kredit (Rp " . number_format($totalCredit, 0, ',', '.') . ").");
            }

            $this->db->transStart();

            // Jana Nombor Jurnal Auto (Contoh: JRN-202603-001)
            $datePrefix = date('Ym', strtotime($date));
            $lastJournal = $this->db->table('journals')->like('journal_number', "JRN-$datePrefix", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastJournal) {
                $parts = explode('-', $lastJournal['journal_number']);
                $seq = intval(end($parts)) + 1;
            }
            $journalNumber = "JRN-" . $datePrefix . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // Simpan Pengepala Jurnal
            $this->db->table('journals')->insert([
                'journal_number'   => $journalNumber,
                'transaction_date' => $date,
                'description'      => $description,
                'total_amount'     => $totalDebit,
                'created_by'       => session()->get('name') ?? 'Sistem'
            ]);
            $journalId = $this->db->insertID();

            // Simpan Butiran Jurnal & Kemas Kini Baki Akaun (Balance)
            foreach ($validItems as $item) {
                $this->db->table('journal_items')->insert([
                    'journal_id' => $journalId,
                    'account_id' => $item['account_id'],
                    'debit'      => $item['debit'],
                    'credit'     => $item['credit']
                ]);

                // Update Baki CoA (Logik Asas: Debit tambah Aset/Belanja, Kredit tambah Liabiliti/Ekuiti/Pendapatan)
                $acc = $this->db->table('chart_of_accounts')->where('id', $item['account_id'])->get()->getRowArray();
                $movement = 0;
                
                if (in_array($acc['account_type'], ['ASET', 'PERBELANJAAN'])) {
                    $movement = $item['debit'] - $item['credit'];
                } else {
                    $movement = $item['credit'] - $item['debit'];
                }

                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$movement, $item['account_id']]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan data jurnal ke pangkalan data.");
            }

            return redirect()->back()->with('success', "Berjaya! Jurnal <b>$journalNumber</b> telah direkodkan dan lejar am dikemas kini.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}