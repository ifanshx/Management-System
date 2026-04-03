<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.05)); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(14, 165, 233, 0.2);}
    .page-title h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 4px 0 0 0;}
    
    .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 14px 24px; border-radius: 14px; font-size: 14px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.5); transition: all 0.3s;}
    .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(14, 165, 233, 0.6);}

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 26px; padding: 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); margin-bottom: 25px;}
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: left; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle;}
    tr:hover td { background: rgba(14, 165, 233, 0.03); }

    .acc-code { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: #0ea5e9; background: rgba(14, 165, 233, 0.1); padding: 5px 9px; border-radius: 8px;}
    .acc-type { font-size: 11px; font-weight: 900; padding: 5px 11px; border-radius: 8px; text-transform: uppercase; display: inline-block;}
    .t-ASET { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2);}
    .t-LIABILITI { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);}
    .t-EKUITI { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .t-PENDAPATAN { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);}
    .t-PERBELANJAAN { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}

    .badge-small {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .badge-debit { background: rgba(59,130,246,.1); color: #2563eb; }
    .badge-kredit { background: rgba(16,185,129,.1); color: #10b981; }
    .badge-contra { background: rgba(245,158,11,.1); color: #f59e0b; }
    .badge-active { background: rgba(16,185,129,.1); color: #10b981; }
    .badge-inactive { background: rgba(239,68,68,.1); color: #ef4444; }

    .table-wrap { overflow-x: auto; }

    .form-grid { display:grid; grid-template-columns: repeat(2,1fr); gap:16px; }
    @media(max-width:768px){ .form-grid{ grid-template-columns:1fr; } }

    .form-group { margin-bottom: 16px; }
    .form-group label { display:block; font-size:11px; font-weight:900; color:var(--text-muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px; }
    .form-control {
        width:100%;
        background:var(--bg-base);
        border:1px solid var(--border-subtle);
        color:var(--text-main);
        padding:14px 16px;
        border-radius:14px;
        font-size:13px;
        font-weight:700;
        outline:none;
        transition:.25s;
    }
    .form-control:focus {
        border-color:#0ea5e9;
        box-shadow:0 0 0 4px rgba(14,165,233,.08);
    }

    .btn-submit {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color:#fff;
        border:none;
        padding:15px 20px;
        border-radius:14px;
        font-size:14px;
        font-weight:900;
        cursor:pointer;
        transition:.3s;
        width:100%;
        margin-top:10px;
    }
    .btn-submit:hover { transform: translateY(-2px); }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-list-bullets"></i></div>
        <div>
            <h1>Chart of Accounts</h1>
            <p>Master akun akuntansi yang rapih, terstruktur, dan siap dipakai untuk laporan profesional.</p>
        </div>
    </div>
    <a href="<?= base_url('/accounting') ?>" class="btn-primary">
        <i class="ph-fill ph-arrow-left"></i> Kembali Dashboard
    </a>
</div>

<div class="bento-card">
    <h3 style="margin:0 0 20px 0;font-size:18px;font-weight:900;color:var(--text-main);">Tambah Akun Baru</h3>

    <form method="post" action="<?= base_url('/accounting/store-account') ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Kode Akun</label>
                <input type="text" name="account_code" class="form-control" placeholder="Contoh: 5-4000" required>
            </div>

            <div class="form-group">
                <label>Nama Akun</label>
                <input type="text" name="account_name" class="form-control" placeholder="Contoh: Beban Internet" required>
            </div>

            <div class="form-group">
                <label>Tipe Akun</label>
                <select name="account_type" class="form-control" required>
                    <option value="">Pilih Tipe...</option>
                    <option value="ASET">ASET</option>
                    <option value="LIABILITI">LIABILITI</option>
                    <option value="EKUITI">EKUITI</option>
                    <option value="PENDAPATAN">PENDAPATAN</option>
                    <option value="PERBELANJAAN">PERBELANJAAN</option>
                </select>
            </div>

            <div class="form-group">
                <label>Parent Account (Opsional)</label>
                <select name="parent_id" class="form-control">
                    <option value="">Tidak Ada</option>
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>">
                            <?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label>Normal Balance</label>
                <select name="normal_balance" class="form-control" required>
                    <option value="">Pilih...</option>
                    <option value="DEBIT">DEBIT</option>
                    <option value="KREDIT">KREDIT</option>
                </select>
            </div>

            <div class="form-group">
                <label>Contra Account</label>
                <select name="is_contra" class="form-control">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Catatan</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Opsional..."></textarea>
        </div>

        <button type="submit" class="btn-submit">
            <i class="ph-fill ph-plus-circle"></i> Simpan Akun
        </button>
    </form>
</div>

<div class="bento-card">
    <h3 style="margin:0 0 20px 0;font-size:18px;font-weight:900;color:var(--text-main);">Daftar Akun</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Akun</th>
                    <th>Tipe</th>
                    <th>Normal</th>
                    <th>Contra</th>
                    <th>Status</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($accounts)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px;">
                            Belum ada akun.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($accounts as $acc): ?>
                        <tr>
                            <td><span class="acc-code"><?= esc($acc['account_code']) ?></span></td>
                            <td><?= esc($acc['account_name']) ?></td>
                            <td><span class="acc-type t-<?= esc($acc['account_type']) ?>"><?= esc($acc['account_type']) ?></span></td>
                            <td>
                                <span class="badge-small <?= $acc['normal_balance'] === 'DEBIT' ? 'badge-debit' : 'badge-kredit' ?>">
                                    <?= esc($acc['normal_balance']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($acc['is_contra']): ?>
                                    <span class="badge-small badge-contra">YA</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);font-weight:800;">-</span>
                                <?php endif ?>
                            </td>
                            <td>
                                <span class="badge-small <?= $acc['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $acc['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                                </span>
                            </td>
                            <td style="font-family:'Space Mono', monospace;">Rp <?= number_format($acc['calculated_balance'] ?? 0, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>