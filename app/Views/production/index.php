<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER (BENTO STYLE)
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.05)); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(59, 130, 246, 0.2);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 4px 0 0 0;}

    /* Tombol Header */
    .header-actions { display: flex; gap: 12px; align-items: center; }

    .btn-create-bom { background: var(--bg-surface); color: #8b5cf6; border: 1px solid var(--border-subtle); padding: 14px 20px; border-radius: 14px; font-size: 13px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-create-bom:hover { color: #fff; background: #8b5cf6; border-color: #8b5cf6; transform: translateY(-3px); box-shadow: 0 6px 15px rgba(139, 92, 246, 0.2);}

    .btn-open-spk { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 14px 20px; border-radius: 14px; font-size: 13px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5);}
    .btn-open-spk:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.6);}

    /* =========================================================
       2. BENTO CARD & TABLE (FULL WIDTH)
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden;}
    
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 8px; border-radius: 10px; font-size: 20px;}

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    
    tr:hover td { background: rgba(59, 130, 246, 0.02); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    /* Custom Badges inside Table */
    .spk-badge { font-family: 'Space Mono', monospace; font-weight: 900; font-size: 13px; color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 6px 10px; border-radius: 8px; display: inline-block; border: 1px dashed rgba(59, 130, 246, 0.3);}
    .prd-badge { font-size: 11px; color: #8b5cf6; font-family: 'Space Mono', monospace; background: rgba(139, 92, 246, 0.08); padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.2); display: inline-flex; align-items: center; gap: 6px; font-weight: 800;}

    .status-badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .s-draft { background: rgba(107, 114, 128, 0.1); color: #6b7280; border: 1px solid rgba(107, 114, 128, 0.2);}
    .s-progress { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); animation: pulseStatus 2s infinite;}
    .s-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}

    @keyframes pulseStatus { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

    .btn-complete { background: rgba(16, 185, 129, 0.05); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;}
    .btn-complete:hover { background: #10b981; color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transform: translateY(-2px);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state p { font-size: 14px; font-weight: 500; margin: 0;}

    /* =========================================================
       3. MODALS (PREMIUM)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 480px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Form within Modal */
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.3s; cursor: pointer; appearance: none;}
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}

    .input-group { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-group:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}
    .input-group input { flex: 1; border: none; background: transparent; padding: 14px 18px; font-size: 16px; font-family: 'Space Mono', monospace; font-weight: 900; color: #3b82f6; outline: none;}
    .input-group span { padding: 14px 18px; background: rgba(0,0,0,0.02); font-size: 12px; font-weight: 800; color: var(--text-muted); border-left: 1px solid var(--border-subtle);}

    .btn-submit-spk { width: 100%; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 18px; border-radius: 16px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 15px; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);}
    .btn-submit-spk:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.6);}
    .btn-submit-spk:disabled { background: var(--bg-base); color: var(--text-muted); border: 2px dashed var(--border-subtle); box-shadow: none; cursor: not-allowed; transform: none;}

    /* Modal Delete / Complete Icon */
    .icon-wrapper { width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05)); color: #10b981; border: 2px dashed rgba(16, 185, 129, 0.4); display: flex; align-items: center; justify-content: center; font-size: 44px; margin: 0 auto 25px auto; animation: pulseIcon 2s infinite;}
    @keyframes pulseIcon { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-factory"></i></div>
        <div>
            <h1>Pusat Eksekusi Manufaktur</h1>
            <p>Terbitkan Surat Perintah Kerja (SPK) dan kelola konversi material pabrik.</p>
        </div>
    </div>
    
    <div class="header-actions">
        <a href="<?= base_url('/production/bom_builder') ?>" class="btn-create-bom">
            <i class="ph-bold ph-flask" style="font-size: 18px;"></i> Buat Formulasi BoM
        </a>
        <button onclick="openModal('modalSPK')" class="btn-open-spk">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Terbitkan SPK
        </button>
    </div>
</div>

<div class="bento-card">
    <div class="card-title" style="color: var(--text-muted);">
        <i class="ph-fill ph-kanban"></i> Papan Pemantauan SPK Produksi
    </div>
    
    <div style="overflow-x: auto;">
        <table id="tableSPK">
            <thead>
                <tr>
                    <th>Dokumen SPK</th>
                    <th>Target Knalpot (PRD)</th>
                    <th style="text-align: center;">Kuantitas</th>
                    <th>Status Produksi</th>
                    <th style="text-align: center;">Aksi Pabrik</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($workOrders)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="ph-fill ph-clipboard-text"></i>
                                <h3 style="margin:0 0 8px 0; color: var(--text-main); font-weight: 900; font-size: 18px;">Papan SPK Kosong</h3>
                                <p>Belum ada perintah produksi yang berjalan hari ini. Terbitkan SPK baru di kanan atas.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($workOrders as $wo): ?>
                        <tr>
                            <td>
                                <div class="spk-badge"><?= esc($wo['spk_number']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted); font-weight: 800; margin-top: 8px; display: flex; align-items: center; gap: 4px;">
                                    <i class="ph-bold ph-calendar-blank"></i> Mulai: <?= date('d M Y', strtotime($wo['start_date'])) ?>
                                </div>
                            </td>
                            <td style="text-align: left;">
                                <div style="font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 6px;"><?= esc($wo['recipe_name']) ?></div>
                                <div class="prd-badge"><i class="ph-fill ph-target"></i> <?= esc($wo['fg_sku']) ?></div>
                            </td>
                            <td>
                                <span style="font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 900; color: var(--text-main);">
                                    <?= $wo['planned_qty'] ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    if($wo['status'] == 'IN_PROGRESS') echo '<span class="status-badge s-progress"><i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 14px;"></i> Dikerjakan</span>';
                                    elseif($wo['status'] == 'COMPLETED') echo '<span class="status-badge s-completed"><i class="ph-fill ph-check-circle" style="font-size: 14px;"></i> Selesai</span>';
                                    else echo '<span class="status-badge s-draft">Draft</span>';
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($wo['status'] == 'IN_PROGRESS'): ?>
                                    <a href="#" onclick="openConfirmModal(event, '<?= base_url('/production/complete_spk/'.$wo['id']) ?>')" class="btn-complete" title="Tandai Selesai & Potong Stok">
                                        <i class="ph-bold ph-check-square-offset" style="font-size: 16px;"></i> Selesaikan
                                    </a>
                                <?php elseif($wo['status'] == 'COMPLETED'): ?>
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 4px; background: var(--bg-base); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                                        <i class="ph-bold ph-clock"></i> <?= date('d M, H:i', strtotime($wo['completed_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalSPK">
    <div class="modal-box" style="border-top: 6px solid #3b82f6;">
        <div class="modal-header">
            <div style="font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(59, 130, 246, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                    <i class="ph-fill ph-clipboard-text" style="font-size: 24px;"></i>
                </div>
                Terbitkan SPK Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalSPK')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <form id="formSPK" action="<?= base_url('/production/create_spk') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Pilih Resep Produksi (BoM)</label>
                <select name="bom_id" class="form-control" required style="border: 2px solid rgba(59, 130, 246, 0.3);">
                    <option value="">-- Pilih Target Produk (PRD) --</option>
                    <?php foreach($boms as $b): ?>
                        <option value="<?= $b['id'] ?>">[<?= esc($b['fg_sku']) ?>] <?= esc($b['recipe_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($boms)): ?>
                    <div style="background: rgba(239, 68, 68, 0.08); border: 1px dashed rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 11px; padding: 12px 15px; border-radius: 10px; margin-top: 12px; font-weight: 700; display: flex; gap: 8px; align-items: flex-start; line-height: 1.5;">
                        <i class="ph-fill ph-warning-circle" style="font-size: 18px;"></i> 
                        Anda belum membuat Formulasi BoM. Silakan buat resep terlebih dahulu.
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Kuantitas Target Produksi</label>
                <div class="input-group">
                    <input type="number" name="planned_qty" id="plannedQtyInput" placeholder="Cth: 50" required min="1" autocomplete="off">
                    <span>Pcs</span>
                </div>
            </div>

            <button type="submit" id="btnSubmitSpk" class="btn-submit-spk" <?= empty($boms) ? 'disabled' : '' ?>>
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> <span>Kirim SPK ke Bengkel</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalConfirm">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <div class="icon-wrapper">
            <i class="ph-fill ph-check-circle"></i>
        </div>
        <h2 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 900; color: var(--text-main); letter-spacing: -0.5px;">Tandai SPK Selesai?</h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 35px; font-weight: 500;">
            Sistem akan otomatis <b>memotong stok Material (MAT)</b> dan <b>menambah stok Produk Jadi (PRD)</b> di gudang secara real-time. Lanjutkan proses ini?
        </p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button onclick="closeModal('modalConfirm')" style="background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); border-radius: 14px; font-weight: 800; font-size: 14px; padding: 14px; cursor: pointer; transition: 0.3s;">Batal</button>
            
            <button id="confirmBtnYes" onclick="executeCompleteSPK()" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 14px; border-radius: 14px; font-weight: 900; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 8px 20px -5px rgba(16, 185, 129, 0.5); cursor: pointer; transition: 0.3s;">
                <i class="ph-bold ph-check-square-offset"></i> <span>Ya, Selesaikan</span>
            </button>
        </div>
    </div>
</div>

<script>
    // --- UTILITY MODAL ---
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    window.onclick = function(e) { if (e.target.classList.contains('modal-overlay')) { e.target.classList.remove('active'); } }

    // --- 1. AJAX CREATE SPK ---
    document.getElementById('formSPK').addEventListener('submit', function(e) {
        e.preventDefault(); 

        const form = this;
        const btn = document.getElementById('btnSubmitSpk');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Memproses SPK...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                document.getElementById('plannedQtyInput').value = ''; 
                closeModal('modalSPK');
                setTimeout(() => { window.location.reload(); }, 1200); 
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false;
                btnText.innerText = "Kirim SPK ke Bengkel";
                btnIcon.className = "ph-bold ph-paper-plane-tilt";
            }
        })
        .catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi server terputus.", true);
            btn.disabled = false;
            btnText.innerText = "Kirim SPK ke Bengkel";
            btnIcon.className = "ph-bold ph-paper-plane-tilt";
        });
    });

    // --- 2. AJAX SELESAIKAN SPK ---
    let pendingCompleteUrl = '';
    
    function openConfirmModal(event, url) {
        event.preventDefault();
        pendingCompleteUrl = url; 
        openModal('modalConfirm');
    }

    function executeCompleteSPK() {
        if(!pendingCompleteUrl) return;

        const btn = document.getElementById('confirmBtnYes');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');

        btn.disabled = true;
        btnText.innerText = "Memotong Stok...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(pendingCompleteUrl, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal('modalConfirm');
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                setTimeout(() => { window.location.reload(); }, 1500); 
            } else {
                closeModal('modalConfirm');
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
            }
        })
        .catch(err => {
            closeModal('modalConfirm');
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi server terputus.", true);
        })
        .finally(() => {
            // Karena jika gagal modal tertutup, kembalikan tombol ke state awal untuk jaga-jaga
            btn.disabled = false;
            btnText.innerText = "Ya, Selesaikan";
            btnIcon.className = "ph-bold ph-check-square-offset";
        });
    }
</script>

<?= $this->endSection() ?>