<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { color: var(--text-muted); font-size: 13px; }

    .btn-generate { background: var(--accent-main); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px var(--accent-light); transition: 0.3s; }
    .btn-generate:hover { transform: translateY(-2px); filter: brightness(1.1); }

    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); padding: 25px; margin-bottom: 30px;}
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end; margin-bottom: 10px;}
    .form-control { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 10px 15px; border-radius: 10px; width: 100%; color: var(--text-main); font-family: inherit;}
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; margin-top: 20px;}
    th { text-align: left; padding: 15px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px dashed var(--border-subtle); }
    td { padding: 15px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; }
    
    .status-badge { padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Finance & Payroll Engine</h1>
        <p>Kalkulasi otomatis gaji, tunjangan, uang makan, lembur, dan denda IoT terpusat.</p>
    </div>
</div>

<div class="table-card" style="background: rgba(37, 99, 235, 0.02); border-color: rgba(37, 99, 235, 0.2);">
    <h3 style="margin-top:0; margin-bottom: 15px; font-size: 16px; color: var(--accent-main);"><i class="ph ph-magic-wand"></i> Auto-Generate Penggajian</h3>
    <form action="<?= base_url('/payroll/generate') ?>" method="post" class="form-grid">
        <?= csrf_field() ?>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; display: block;">Tipe Karyawan</label>
            <select name="salary_type" class="form-control" required>
                <option value="Harian">Harian (Siklus Pendek)</option>
                <option value="Mingguan">Mingguan (Tiap Sabtu)</option>
                <option value="Bulanan" selected>Bulanan (Akhir Bulan)</option>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; display: block;">Periode Mulai</label>
            <input type="date" name="period_start" class="form-control" required>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; display: block;">Periode Selesai</label>
            <input type="date" name="period_end" class="form-control" required>
        </div>
        <div>
            <button type="submit" class="btn-generate" onclick="return confirm('Sistem akan menyedot data absensi mesin pada tanggal tersebut dan menghitung gaji otomatis. Lanjutkan?')">
                <i class="ph ph-gear-six"></i> Proses Gaji
            </button>
        </div>
    </form>
</div>

<div class="table-card">
    <h3 style="margin-top:0; font-size: 16px;">Riwayat Dokumen Penggajian</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Kode Dokumen</th>
                    <th>Tipe Gaji</th>
                    <th>Periode Tanggal</th>
                    <th>Total Pekerja</th>
                    <th>Total Pembayaran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payrolls as $pay): ?>
                <tr>
                    <td style="font-family: monospace; color: var(--accent-main);"><?= esc($pay['payroll_code']) ?></td>
                    <td><?= esc($pay['salary_type']) ?></td>
                    <td><i class="ph ph-calendar-blank"></i> <?= date('d M Y', strtotime($pay['period_start'])) ?> - <?= date('d M Y', strtotime($pay['period_end'])) ?></td>
                    <td><?= $pay['total_employees'] ?> Orang</td>
                    <td>Rp <?= number_format($pay['total_amount'], 0, ',', '.') ?></td>
                    <td><span class="status-badge"><?= esc($pay['status']) ?></span></td>
                    <td>
                        <a href="<?= base_url('/payroll/detail/' . $pay['id']) ?>" style="background: var(--bg-base); padding: 6px 12px; border-radius: 8px; text-decoration: none; color: var(--text-main); font-size: 12px; border: 1px solid var(--border-subtle);">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8' });
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444' });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>