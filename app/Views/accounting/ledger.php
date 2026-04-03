<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; }
    .page-title { display: flex; align-items: center; gap: 16px; }
    .title-icon {
        width: 58px; height: 58px; border-radius: 18px;
        background: linear-gradient(135deg, rgba(139,92,246,.18), rgba(99,102,241,.08));
        color: #8b5cf6;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        border: 1px solid rgba(139,92,246,.15);
        box-shadow: 0 14px 35px -18px rgba(139,92,246,.45);
    }
    .page-title h1 { font-size: 30px; font-weight: 900; margin: 0; color: var(--text-main); letter-spacing: -.7px; }
    .page-title p { margin: 5px 0 0 0; font-size: 13px; color: var(--text-muted); font-weight: 600; }

    .btn-back {
        background: var(--bg-surface);
        color: var(--text-main);
        border: 1px solid var(--border-subtle);
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 800;
        text-decoration: none;
        font-size: 13px;
        transition: .25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-back:hover { transform: translateY(-2px); border-color: #8b5cf6; color: #8b5cf6; }

    .bento-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 28px;
        padding: 26px;
        box-shadow: 0 18px 45px -28px rgba(0,0,0,.2);
        margin-bottom: 24px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--text-main);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-title i { color: #8b5cf6; }

    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 16px;
        align-items: end;
    }
    @media(max-width: 900px) {
        .filter-grid { grid-template-columns: 1fr; }
    }

    .form-group { display: flex; flex-direction: column; }
    .form-label {
        font-size: 11px;
        font-weight: 900;
        color: var(--text-muted);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .form-control {
        width: 100%;
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        color: var(--text-main);
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: .25s ease;
        font-family: inherit;
    }
    .form-control:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139,92,246,.08);
    }

    .btn-filter {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #fff;
        border: none;
        padding: 14px 22px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: .3s;
        box-shadow: 0 12px 25px -10px rgba(139,92,246,.45);
    }
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px -12px rgba(139,92,246,.5);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4,1fr);
        gap: 18px;
        margin-top: 24px;
    }
    @media(max-width: 900px) {
        .summary-grid { grid-template-columns: repeat(2,1fr); }
    }
    @media(max-width: 600px) {
        .summary-grid { grid-template-columns: 1fr; }
    }

    .summary-box {
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 20px;
        transition: .3s;
    }
    .summary-box:hover { transform: translateY(-3px); }
    .summary-label {
        font-size: 11px;
        font-weight: 900;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: .5px;
    }
    .summary-value {
        font-size: 24px;
        font-weight: 900;
        color: var(--text-main);
        font-family: 'Space Mono', monospace;
        line-height: 1;
    }

    .table-wrap { overflow-x: auto; }
    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }
    .ledger-table th {
        text-align: left;
        padding: 16px 18px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-subtle);
        background: var(--bg-base);
        letter-spacing: .5px;
    }
    .ledger-table td {
        padding: 18px;
        border-bottom: 1px dashed var(--border-subtle);
        color: var(--text-main);
        font-size: 14px;
        font-weight: 700;
        vertical-align: middle;
    }
    .ledger-table tr:hover td {
        background: rgba(139,92,246,.03);
    }

    .mono { font-family: 'Space Mono', monospace; }
    .debit-text { color: #10b981; font-weight: 900; }
    .credit-text { color: #ef4444; font-weight: 900; }
    .balance-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(139,92,246,.1);
        color: #8b5cf6;
        font-size: 12px;
        font-weight: 900;
    }
</style>

<?php
$totalDebit = 0;
$totalCredit = 0;
$runningBalance = 0;

if (!empty($ledger) && !empty($selectedAccount)) {
    foreach ($ledger as $row) {
        $totalDebit += (float)$row['debit'];
        $totalCredit += (float)$row['credit'];

        if ($selectedAccount['normal_balance'] === 'DEBIT') {
            $runningBalance += (float)$row['debit'] - (float)$row['credit'];
        } else {
            $runningBalance += (float)$row['credit'] - (float)$row['debit'];
        }
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-book-open-text"></i></div>
        <div>
            <h1>Buku Besar</h1>
            <p>Telusuri mutasi debit, kredit, dan saldo akun secara detail dan profesional.</p>
        </div>
    </div>

    <a href="<?= base_url('/accounting') ?>" class="btn-back">
        <i class="ph-fill ph-arrow-left"></i> Kembali Dashboard
    </a>
</div>

<div class="bento-card">
    <div class="card-title">
        <i class="ph-fill ph-funnel"></i> Filter Buku Besar
    </div>

    <form method="get" action="<?= base_url('/accounting/ledger') ?>">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">Pilih Akun</label>
                <select name="account_id" class="form-control" required>
                    <option value="">-- Pilih Akun --</option>
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>" <?= ($accountId == $acc['id']) ? 'selected' : '' ?>>
                            <?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc($startDate) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="<?= esc($endDate) ?>" required>
            </div>

            <button type="submit" class="btn-filter">
                <i class="ph-fill ph-magnifying-glass"></i> Tampilkan
            </button>
        </div>
    </form>

    <?php if(!empty($selectedAccount)): ?>
        <div class="summary-grid">
            <div class="summary-box">
                <div class="summary-label">Akun Dipilih</div>
                <div class="summary-value" style="font-size:16px;font-family:inherit;line-height:1.4;">
                    <?= esc($selectedAccount['account_code']) ?><br>
                    <?= esc($selectedAccount['account_name']) ?>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-label">Total Debit</div>
                <div class="summary-value">Rp <?= number_format($totalDebit, 0, ',', '.') ?></div>
            </div>

            <div class="summary-box">
                <div class="summary-label">Total Kredit</div>
                <div class="summary-value">Rp <?= number_format($totalCredit, 0, ',', '.') ?></div>
            </div>

            <div class="summary-box">
                <div class="summary-label">Saldo Akhir</div>
                <div class="summary-value" style="color:#8b5cf6;">Rp <?= number_format($runningBalance, 0, ',', '.') ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="bento-card">
    <div class="card-title">
        <i class="ph-fill ph-scroll"></i> Detail Mutasi Buku Besar
    </div>

    <?php if(empty($ledger)): ?>
        <div style="text-align:center;padding:40px 20px;color:var(--text-muted);font-weight:700;">
            Belum ada data buku besar untuk filter yang dipilih.
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Jurnal</th>
                        <th>Deskripsi</th>
                        <th>Keterangan Baris</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ledger as $row): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['transaction_date'])) ?></td>
                            <td class="mono"><?= esc($row['journal_number']) ?></td>
                            <td><?= esc($row['journal_description']) ?></td>
                            <td><?= esc($row['line_description'] ?? '-') ?></td>
                            <td class="mono debit-text">
                                <?= ((float)$row['debit'] > 0) ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' ?>
                            </td>
                            <td class="mono credit-text">
                                <?= ((float)$row['credit'] > 0) ? 'Rp ' . number_format($row['credit'], 0, ',', '.') : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>