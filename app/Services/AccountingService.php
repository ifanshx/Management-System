<?php

namespace App\Services;

use Exception;

class AccountingService
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Menerbitkan Jurnal Umum (Double-Entry)
     */
    public function createJournal(string $date, string $description, string $module, ?string $refNo, float $totalAmount, array $items, string $createdBy = 'System', ?int $sourceId = null): int
    {
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($items as $item) {
            $totalDebit += (float)$item['debit'];
            $totalCredit += (float)$item['credit'];
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception("Sistem Menolak: Jurnal tidak seimbang! Total Debit (Rp " . number_format($totalDebit,0,',','.') . ") vs Total Kredit (Rp " . number_format($totalCredit,0,',','.') . ").");
        }

        if ($totalDebit <= 0) {
            throw new Exception("Sistem Menolak: Total nilai jurnal tidak boleh 0.");
        }

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
            'reference_number' => $refNo,
            'source_module'    => $module,
            'source_id'        => $sourceId,
            'total_amount'     => $totalAmount,
            'status'           => 'POSTED',
            'created_by'       => $createdBy
        ]);
        
        $journalId = $this->db->insertID();

        $insertItems = [];
        foreach ($items as $item) {
            $insertItems[] = [
                'journal_id'       => $journalId,
                'account_id'       => $item['account_id'],
                'line_description' => $item['memo'] ?? '',
                'debit'            => $item['debit'],
                'credit'           => $item['credit']
            ];
        }
        $this->db->table('journal_items')->insertBatch($insertItems);

        return $journalId;
    }

    /**
     * Membatalkan Jurnal (Void / Reversal) Terpusat
     * Akan otomatis membatalkan kas dan mereset tagihan PO.
     */
    public function voidJournal(int $journalId, string $reason, string $voidedBy = 'System'): void
    {
        $journal = $this->db->table('journals')->where('id', $journalId)->get()->getRowArray();
        if (!$journal) throw new Exception("Jurnal tidak ditemukan.");
        if ($journal['status'] === 'VOID') throw new Exception("Jurnal sudah pernah dibatalkan (VOID).");

        // 1. Set status VOID di Header Jurnal
        $this->db->table('journals')->where('id', $journalId)->update([
            'status'       => 'VOID',
            'total_amount' => 0,
            'void_reason'  => $reason,
            'voided_at'    => date('Y-m-d H:i:s'),
            'voided_by'    => $voidedBy
        ]);

        // 2. Nol-kan item jurnal agar Balance kembali utuh
        $this->db->table('journal_items')->where('journal_id', $journalId)->update([
            'debit'  => 0,
            'credit' => 0
        ]);

        // 3. SINKRONISASI: Ubah Status di Kas Operasional agar TAMPIL CORETAN (Bukan dihapus)
        if ($this->db->tableExists('operational_cash')) {
            $this->db->table('operational_cash')
                     ->where('journal_id', $journalId)
                     ->update(['status' => 'CANCELLED']); // UPDATE STATUS
        }

        // 4. SINKRONISASI: Tarik kembali Status Pembayaran di PO menjadi Hutang
        // KUNCI PERBAIKAN: Gunakan reference_number (PO Number) untuk mencari PO, bukan source_id.
        if ($journal['source_module'] === 'PAYMENT' && !empty($journal['reference_number'])) {
            $poNumber = $journal['reference_number'];
            $po = $this->db->table('purchase_orders')->where('po_number', $poNumber)->get()->getRowArray();
            
            if ($po) {
                $amountToReverse = (float) $journal['total_amount'];
                $newPaid = max(0, (float)$po['paid_amount'] - $amountToReverse);
                $newStatus = ($newPaid > 0) ? 'PARTIAL' : 'UNPAID';
                
                $this->db->table('purchase_orders')->where('id', $po['id'])->update([
                    'paid_amount'    => $newPaid,
                    'payment_status' => $newStatus
                ]);

                // Hapus Log History Pembayaran PO
                if ($this->db->tableExists('purchase_order_payments')) {
                    $this->db->query("DELETE FROM purchase_order_payments WHERE po_id = ? AND amount = ? ORDER BY id DESC LIMIT 1", [$po['id'], $amountToReverse]);
                }
            }
        }
    }
}