<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & BENTO GRID
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(29, 78, 216, 0.05)); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(37, 99, 235, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0;}

    .leave-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px; align-items: start; }
    @media (max-width: 1024px) { .leave-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
    .bento-header { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px; }
    
    /* =========================================================
       2. BALANCE WIDGET
       ========================================================= */
    .balance-box { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: #fff; padding: 35px 30px; border-radius: 24px; text-align: center; margin-bottom: 24px; box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.5); position: relative; overflow: hidden;}
    .balance-box::after { content: ''; position: absolute; right: -30px; top: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    .balance-box::before { content: ''; position: absolute; left: -20px; bottom: -40px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%; }
    
    .balance-num { font-size: 64px; font-weight: 900; line-height: 1; letter-spacing: -2px; font-family: 'Space Mono', monospace; position: relative; z-index: 10;}
    .balance-lbl { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-top: 8px; position: relative; z-index: 10;}

    /* =========================================================
       3. FORM ELEMENTS
       ========================================================= */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 14px 18px; border-radius: 14px; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s; cursor: pointer; appearance: none; font-family: inherit;}
    .form-control:focus { border-color: #2563eb; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
    
    .date-input { font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 800;}

    .btn-submit { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 18px; border-radius: 16px; font-weight: 900; font-size: 16px; width: 100%; cursor: pointer; transition: 0.3s; margin-top: 10px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5); display: flex; justify-content: center; align-items: center; gap: 8px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.6); }
    .btn-submit:disabled { background: var(--bg-base); color: var(--text-muted); border: 2px dashed var(--border-subtle); box-shadow: none; cursor: not-allowed; transform: none;}

    /* Custom File Upload */
    .file-upload-wrapper { background: var(--bg-base); border: 2px dashed var(--border-subtle); border-radius: 14px; padding: 10px; display: flex; align-items: center; transition: 0.3s; }
    .file-upload-wrapper:hover { border-color: #2563eb; background: var(--bg-surface); }
    .file-upload-wrapper input[type="file"] { width: 100%; font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; outline: none;}
    .file-upload-wrapper input[type="file"]::file-selector-button { background: rgba(37, 99, 235, 0.1); color: #2563eb; border: none; padding: 8px 14px; border-radius: 10px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-right: 15px;}
    .file-upload-wrapper input[type="file"]::file-selector-button:hover { background: #2563eb; color: #fff; }

    /* =========================================================
       4. HISTORY LOG ITEMS
       ========================================================= */
    .history-item { padding: 20px; border: 1px solid var(--border-subtle); border-radius: 16px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); transition: 0.3s;}
    .history-item:hover { background: var(--bg-surface); border-color: #2563eb; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transform: translateX(4px);}
    
    .h-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 4px;}
    .h-date { font-size: 12px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 6px;}
    
    .badge { padding: 6px 14px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; display: inline-block;}
    .badge.pending { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2);}
    .badge.approved { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2);}
    .badge.rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state h3 { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-calendar-plus"></i></div>
        <div>
            <h1>Form Cuti & Izin Absen</h1>
            <p>Ajukan permohonan ketidakhadiran dan unggah surat keterangan dokter (jika sakit).</p>
        </div>
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

        <div class="bento-card" style="border-top: 6px solid #2563eb;">
            <div class="bento-header">
                <div style="background: rgba(37, 99, 235, 0.1); padding: 6px; border-radius: 8px;"><i class="ph-fill ph-paper-plane-tilt" style="color: #2563eb; font-size: 20px;"></i></div> 
                Formulir Pengajuan
            </div>
            
            <form action="<?= base_url('/leave/store') ?>" method="post" enctype="multipart/form-data" onsubmit="disableSubmitBtn(this)">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Jenis Ketidakhadiran</label>
                    <select name="leave_type" class="form-control" required id="leaveType" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232563eb%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;">
                        <option value="Cuti Tahunan">Cuti Tahunan (Memotong Saldo)</option>
                        <option value="Izin Sakit">Izin Sakit (Wajib Surat Dokter)</option>
                        <option value="Izin Keperluan Pribadi">Izin Keperluan Pribadi</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Tanggal Mulai Cuti/Izin</label>
                        <input type="date" name="start_date" id="startDate" class="form-control date-input" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Masuk Kerja</label>
                        <input type="date" name="end_date" id="endDate" class="form-control date-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Durasi Cuti (Kalkulasi Otomatis)</label>
                    <div style="background: rgba(37, 99, 235, 0.05); padding: 15px; border: 1px dashed rgba(37, 99, 235, 0.3); border-radius: 14px; text-align: center; font-weight: 900; font-size: 20px; font-family: 'Space Mono', monospace; color: #2563eb;" id="durationDisplay">
                        0 Hari
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Alasan / Keterangan Detail</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Tuliskan alasan lengkap agar HRD mudah memproses..." required style="resize: none; line-height: 1.5;"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Lampiran Dokumen <span style="font-weight: 600; text-transform: none; font-size: 10px; color: var(--text-muted);">(Surat Dokter/Bukti, max 2MB)</span></label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="attachment" accept="image/*,.pdf">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="ph-bold ph-paper-plane-right"></i> Kirim Pengajuan
                </button>
            </form>
        </div>
    </div>

    <div class="bento-card">
        <div class="bento-header" style="color: var(--text-muted);">
            <i class="ph-fill ph-clock-counter-clockwise"></i> Riwayat Pengajuan Saya
        </div>
        
        <div style="max-height: 700px; overflow-y: auto; padding-right: 5px;">
            <?php if(empty($myLeaves)): ?>
                <div class="empty-state">
                    <i class="ph-fill ph-files"></i>
                    <h3>Belum Ada Riwayat</h3>
                    <p style="font-size: 13px;">Anda belum pernah mengajukan cuti atau izin sebelumnya.</p>
                </div>
            <?php else: ?>
                <?php foreach($myLeaves as $l): ?>
                <div class="history-item">
                    <div style="flex: 1; padding-right: 15px;">
                        <div class="h-title">
                            <?= esc($l['leave_type']) ?> 
                            <span style="font-family: 'Space Mono', monospace; font-size: 12px; color: #2563eb; background: rgba(37,99,235,0.1); padding: 2px 6px; border-radius: 6px; margin-left: 6px;">
                                <?= $l['duration'] ?> Hari
                            </span>
                        </div>
                        <div class="h-date">
                            <i class="ph-bold ph-calendar-blank"></i> 
                            <?= date('d M Y', strtotime($l['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($l['end_date'])) ?>
                        </div>
                        <div style="font-size: 12px; margin-top: 8px; color: var(--text-muted); line-height: 1.4; background: rgba(0,0,0,0.02); padding: 8px 12px; border-radius: 8px; border-left: 2px solid var(--border-subtle);">
                            "<?= esc($l['reason']) ?>"
                        </div>
                    </div>
                    
                    <div style="text-align: right; min-width: 100px;">
                        <div class="badge <?= strtolower($l['status']) ?>">
                            <?= esc($l['status']) ?>
                        </div>
                        <?php if($l['reviewed_by']): ?>
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); margin-top: 8px; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                <i class="ph-fill ph-user-circle"></i> <?= esc($l['reviewed_by']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // --- KALKULASI HARI OTOMATIS ---
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const display = document.getElementById('durationDisplay');
    const btnSubmit = document.getElementById('btnSubmit');

    function calcDuration() {
        if(startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            const diffTime = end - start;
            
            if(diffTime < 0) {
                display.innerHTML = "<span style='color: #ef4444;'><i class='ph-bold ph-warning-circle'></i> Tanggal Tidak Valid</span>";
                display.style.borderColor = "rgba(239, 68, 68, 0.4)";
                display.style.background = "rgba(239, 68, 68, 0.05)";
                btnSubmit.disabled = true;
            } else {
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
                display.innerText = diffDays + " Hari";
                display.style.borderColor = "rgba(37, 99, 235, 0.3)";
                display.style.background = "rgba(37, 99, 235, 0.05)";
                display.style.color = "#2563eb";
                btnSubmit.disabled = false;
            }
        }
    }
    
    startInput.addEventListener('change', calcDuration);
    endInput.addEventListener('change', calcDuration);

    // Animasi Loading saat Submit (Mencegah Double Click)
    function disableSubmitBtn(form) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size:20px;"></i> Mengirim Data...';
    }
</script>

<?= $this->endSection() ?>