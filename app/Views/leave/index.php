<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .leave-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; align-items: start; }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: var(--shadow-card); }
    .bento-header { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; }
    
    .balance-box { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: #fff; padding: 30px; border-radius: 16px; text-align: center; margin-bottom: 20px; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4); }
    .balance-num { font-size: 56px; font-weight: 800; line-height: 1; letter-spacing: -2px; }
    .balance-lbl { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-top: 5px; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 12px 16px; border-radius: 12px; font-size: 14px; font-family: inherit; outline: none; }
    .form-control:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    
    .btn-submit { background: var(--accent-main); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 700; width: 100%; cursor: pointer; transition: 0.3s; margin-top: 10px; }
    .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); }

    .history-item { padding: 15px; border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); }
    .h-title { font-size: 14px; font-weight: 800; color: var(--text-main); }
    .h-date { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
    .badge { padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; }
    .badge.pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .badge.approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge.rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    @media (max-width: 992px) { .leave-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Form Cuti & Izin</h1>
        <p>Ajukan permohonan ketidakhadiran dan unggah surat keterangan dokter jika sakit.</p>
    </div>
</div>

<div class="leave-grid">
    <div>
        <?php if(isset($employee)): ?>
        <div class="balance-box">
            <div class="balance-num"><?= esc($employee['leave_balance']) ?></div>
            <div class="balance-lbl">Hari Sisa Cuti Tahunan</div>
        </div>
        <?php endif; ?>

        <div class="bento-card">
            <div class="bento-header"><i class="ph ph-paper-plane-tilt" style="color: var(--accent-main); font-size: 20px;"></i> Ajukan Permohonan</div>
            
            <form action="<?= base_url('/leave/store') ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Jenis Ketidakhadiran</label>
                    <select name="leave_type" class="form-control" required id="leaveType">
                        <option value="Cuti Tahunan">Cuti Tahunan (Memotong Saldo)</option>
                        <option value="Izin Sakit">Izin Sakit (Wajib Surat Dokter)</option>
                        <option value="Izin Keperluan Pribadi">Izin Keperluan Pribadi</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Durasi (Otomatis)</label>
                    <div style="background: var(--bg-surface); padding: 12px; border: 1px dashed var(--border-subtle); border-radius: 12px; text-align: center; font-weight: 800; color: var(--text-main);" id="durationDisplay">
                        0 Hari
                    </div>
                </div>
                <div class="form-group">
                    <label>Alasan / Keterangan</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Tuliskan alasan lengkap..." required style="resize: none;"></textarea>
                </div>
                <div class="form-group">
                    <label>Lampiran Dokumen <span style="font-weight: 500; font-size: 11px; color: var(--text-muted);">(Opsional, max 2MB)</span></label>
                    <input type="file" name="attachment" class="form-control" accept="image/*,.pdf" style="padding: 9px 16px;">
                </div>
                
                <button type="submit" class="btn-submit">Kirim Pengajuan</button>
            </form>
        </div>
    </div>

    <div class="bento-card">
        <div class="bento-header"><i class="ph ph-clock-counter-clockwise" style="color: var(--accent-main); font-size: 20px;"></i> Riwayat Pengajuan Saya</div>
        
        <?php if(empty($myLeaves)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada riwayat pengajuan.</div>
        <?php else: ?>
            <?php foreach($myLeaves as $l): ?>
            <div class="history-item">
                <div>
                    <div class="h-title"><?= esc($l['leave_type']) ?> <span style="color: var(--accent-main);">(<?= $l['duration'] ?> Hari)</span></div>
                    <div class="h-date"><?= date('d M Y', strtotime($l['start_date'])) ?> s/d <?= date('d M Y', strtotime($l['end_date'])) ?></div>
                    <div style="font-size: 12px; margin-top: 6px; color: var(--text-muted);"><i class="ph ph-text-align-left"></i> <?= esc($l['reason']) ?></div>
                </div>
                <div style="text-align: right;">
                    <div class="badge <?= strtolower($l['status']) ?>"><?= esc($l['status']) ?></div>
                    <?php if($l['reviewed_by']): ?>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 8px;">Oleh: <?= esc($l['reviewed_by']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Kalkulasi Hari Otomatis
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const display = document.getElementById('durationDisplay');

    function calcDuration() {
        if(startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
            
            if(end < start) {
                display.innerHTML = "<span style='color: #ef4444;'>Tanggal tidak valid</span>";
            } else {
                display.innerText = diffDays + " Hari";
            }
        }
    }
    startInput.addEventListener('change', calcDuration);
    endInput.addEventListener('change', calcDuration);

    // SweetAlert
    <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8' });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>