<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 1px solid var(--border-subtle); }
    td { padding: 15px 20px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500; }
    
    .badge { padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; }
    .badge.pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .badge.approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge.rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    .btn-act { border: none; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; text-decoration: none; }
    .btn-approve { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .btn-approve:hover { background: #10b981; color: #fff; }
    .btn-reject { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-reject:hover { background: #ef4444; color: #fff; }
    .btn-view { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Approval Cuti & Izin</h1>
        <p>Kelola permohonan ketidakhadiran karyawan. Menyetujui Cuti Tahunan akan otomatis memotong saldo cuti karyawan.</p>
    </div>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jenis</th>
                    <th>Tanggal (Durasi)</th>
                    <th>Keterangan</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($leaves)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Tidak ada data pengajuan.</td></tr>
                <?php else: ?>
                    <?php foreach($leaves as $row): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 800;"><?= esc($row['name']) ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= esc($row['position']) ?></div>
                        </td>
                        <td><span style="font-weight: 700; color: var(--accent-main);"><?= esc($row['leave_type']) ?></span></td>
                        <td>
                            <?= date('d M Y', strtotime($row['start_date'])) ?> s/d <?= date('d M Y', strtotime($row['end_date'])) ?><br>
                            <span style="font-size: 11px; color: var(--text-muted);"><i class="ph ph-clock"></i> <?= $row['duration'] ?> Hari</span>
                        </td>
                        <td style="white-space: normal; min-width: 200px;"><?= esc($row['reason']) ?></td>
                        <td>
                            <?php if($row['attachment']): ?>
                                <a href="<?= base_url('uploads/leaves/' . $row['attachment']) ?>" target="_blank" class="btn-act btn-view"><i class="ph ph-file-pdf"></i> Lihat File</a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 11px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= strtolower($row['status']) ?>"><?= esc($row['status']) ?></span></td>
                        <td>
                            <?php if($row['status'] === 'Pending'): ?>
                                <div style="display: flex; gap: 5px;">
                                    <a href="<?= base_url('/leave/process_action/' . $row['id'] . '/approve') ?>" class="btn-act btn-approve" onclick="return confirm('Setujui pengajuan ini?')"><i class="ph ph-check"></i> Setujui</a>
                                    <a href="<?= base_url('/leave/process_action/' . $row['id'] . '/reject') ?>" class="btn-act btn-reject" onclick="return confirm('Tolak pengajuan ini?')"><i class="ph ph-x"></i> Tolak</a>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-muted);">Diulas oleh: <?= esc($row['reviewed_by']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>