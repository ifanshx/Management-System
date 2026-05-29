<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
$db = \Config\Database::connect();

// LOGIKA MENGELOMPOKKAN SETORAN BERDASARKAN SPK
$groupedLogs = [];
if (!empty($pendingLogs)) {
    foreach ($pendingLogs as $log) {
        $spk = $log['spk_number'];
        if (!isset($groupedLogs[$spk])) {
            $groupedLogs[$spk] = [];
        }
        $groupedLogs[$spk][] = $log;
    }
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =======================================================================
       PREMIUM ERP UI / UX DESIGN SYSTEM (CONFIRM LOGS)
       ======================================================================= */
    :root {
        --prod-primary: #2563eb; --prod-primary-dark: #1d4ed8; --prod-primary-soft: rgba(37, 99, 235, 0.08);
        --prod-success: #10b981; --prod-success-dark: #059669; --prod-success-soft: rgba(16, 185, 129, 0.08);
        --prod-warning: #f59e0b; --prod-warning-dark: #d97706; --prod-warning-soft: rgba(245, 158, 11, 0.08);
        --prod-danger: #ef4444; --prod-danger-dark: #dc2626; --prod-danger-soft: rgba(239, 68, 68, 0.08);
        --prod-info: #0ea5e9; --prod-info-dark: #0284c7; --prod-info-soft: rgba(14, 165, 233, 0.08);
        --prod-accent: #8b5cf6; --prod-accent-dark: #7c3aed; --prod-accent-soft: rgba(139, 92, 246, 0.08);

        --bg-body: #f8fafc;
        --bg-surface: #ffffff;
        --bg-input: #f1f5f9;
        
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-subtle: #e2e8f0;
        
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
        --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.1);
        
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
        
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    html.dark { 
        --bg-body: #0f172a; --bg-surface: #1e293b; --bg-input: #0f172a;
        --text-main: #f8fafc; --text-muted: #94a3b8; --border-subtle: #334155;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); }
    
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @keyframes slideInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* HEADER & TITLE SECTION */
    .prod-page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; width: 100%; flex-wrap: wrap; gap: 20px;} 
    .prod-page-title { display: flex; align-items: center; gap: 18px; animation: slideInUp 0.5s ease-out forwards; flex-wrap: wrap;}
    .prod-title-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.4); flex-shrink: 0; border: 1px solid rgba(255,255,255,0.2);}
    .prod-title-text { display: flex; flex-direction: column; gap: 4px; }
    .prod-title-text h1 { font-size: 32px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.2;}
    .prod-title-text p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 0; line-height: 1.5;}

    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 14px 20px; border-radius: var(--radius-md); font-size: 14px; font-weight: 800; text-decoration: none; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-sm);}
    .btn-back:hover { border-color: var(--prod-primary); color: var(--prod-primary); transform: translateY(-2px); box-shadow: var(--shadow-md);}

    /* GROUPED CARD STYLE */
    .spk-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 30px; box-shadow: var(--shadow-md); animation: slideInUp 0.6s ease-out forwards; transition: var(--transition-smooth); }
    .spk-card:hover { border-color: var(--prod-warning-soft); box-shadow: var(--shadow-lg); transform: translateY(-3px);}
    
    .spk-card-header { background: var(--bg-surface); border-bottom: 2px dashed var(--border-subtle); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-top: 6px solid var(--prod-warning); }
    
    .prod-table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .prod-table { width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 800px; }
    .prod-table th { text-align: left; padding: 16px 30px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-input); border-bottom: 1px solid var(--border-subtle); letter-spacing: 0.5px;}
    .prod-table td { padding: 20px 30px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 700; vertical-align: middle; transition: var(--transition-smooth);}
    .prod-table tr:hover td { background: var(--bg-input); }
    .prod-table tr:last-child td { border-bottom: none; }
    
    .btn-action-sm { padding: 10px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: var(--transition-smooth); border: none; letter-spacing: 0.5px; text-transform: uppercase;}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-acc { background: linear-gradient(135deg, var(--prod-success), var(--prod-success-dark)); color: #fff; box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.5); }
    .btn-acc:hover { box-shadow: 0 12px 25px -6px rgba(16, 185, 129, 0.6); }
    .btn-rej { background: var(--prod-danger-soft); color: var(--prod-danger-dark); border: 1px solid rgba(239, 68, 68, 0.2); }
    .btn-rej:hover { background: var(--prod-danger); color: #fff; }
    .btn-edit-price { background: var(--prod-info-soft); color: var(--prod-info-dark); border: 1px solid rgba(59, 130, 246, 0.2); }
    .btn-edit-price:hover { background: var(--prod-info); color: #fff; }

    .empty-state { text-align: center; padding: 80px 20px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); box-shadow: var(--shadow-md); animation: slideInUp 0.6s ease-out forwards;}
    .empty-state i { font-size: 72px; color: var(--prod-success); opacity: 0.5; margin-bottom: 20px; display: block; }
    .empty-state h3 { margin: 0 0 10px 0; font-size: 20px; font-weight: 900; color: var(--text-main); letter-spacing: -0.5px;}
    .empty-state p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 0;}

    @media (max-width: 768px) {
        .prod-page-header { flex-direction: column; align-items: flex-start; gap: 15px;}
        .btn-back { width: 100%; }
        .spk-card-header { flex-direction: column; align-items: flex-start; padding: 20px;}
        .prod-table td, .prod-table th { padding: 15px 20px; }
        .btn-action-sm { padding: 8px 10px; font-size: 10px; }
    }
</style>

<form id="csrf-form" style="display:none;">
    <?= csrf_field() ?>
</form>

<div class="page-wrapper">
    <div class="prod-page-header">
        <div class="prod-page-title">
            <div class="prod-title-icon"><i class="ph-bold ph-bell-ringing"></i></div>
            <div class="prod-title-text">
                <h1>Antrean Konfirmasi Setoran</h1>
                <p>Terima setoran untuk memotong stok bahan dan mencatat hutang upah pekerja.</p>
            </div>
        </div>
        <div class="prod-actions">
            <a href="<?= base_url('/production') ?>" class="btn-back"><i class="ph-bold ph-arrow-left" style="font-size: 18px;"></i> Kembali ke Dashboard Produksi</a>
        </div>
    </div>

    <?php if(empty($groupedLogs)): ?>
        <div class="empty-state">
            <i class="ph-duotone ph-check-circle"></i>
            <h3>Tidak ada setoran yang menunggu</h3>
            <p>Kerja bagus! Semua setoran pekerja telah dikonfirmasi dan disinkronisasi.</p>
        </div>
    <?php else: ?>

        <?php foreach($groupedLogs as $spkNumber => $logs): 
            // CEK NAMA CUSTOMER / PEMESAN DAN CATATAN EMBLEM/B2B
            $spkData = $db->table('work_orders')
                ->select('b2b_customers.company_name, work_orders.production_notes')
                ->join('b2b_sales_orders', 'b2b_sales_orders.id = work_orders.so_id', 'left')
                ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id', 'left')
                ->where('work_orders.spk_number', $spkNumber)
                ->get()->getRowArray();
                
            $buyerName = !empty($spkData['company_name']) ? $spkData['company_name'] : 'Stok Gudang (Reguler)';
            $isB2B = !empty($spkData['company_name']);
            
            // Mengambil production_notes (Catatan Emblem seperti CTS, DSM, RMS, dll)
            $emblemNote = $spkData['production_notes'] ?? '';
        ?>

        <div class="spk-card">
            <div class="spk-card-header">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <span style="font-family:'Space Mono', monospace; font-size:18px; font-weight:900; color:var(--prod-warning-dark); display:flex; align-items:center; gap:8px;">
                        <i class="ph-fill ph-folder-open" style="font-size: 24px;"></i> <?= esc($spkNumber) ?>
                    </span>
                    
                    <?php if($isB2B): ?>
                        <span style="background:var(--prod-accent-soft); color:var(--prod-accent-dark); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:900; border:1px dashed rgba(139,92,246,0.3); display:flex; align-items:center; gap:6px;">
                            <i class="ph-fill ph-storefront"></i> Pemesan: <?= esc($buyerName) ?>
                        </span>
                    <?php else: ?>
                        <span style="background:var(--prod-info-soft); color:var(--prod-info-dark); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:900; border:1px dashed rgba(14,165,233,0.3); display:flex; align-items:center; gap:6px;">
                            <i class="ph-fill ph-warehouse"></i> <?= esc($buyerName) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div style="font-size:13px; font-weight:800; color:var(--text-muted); background:var(--bg-input); padding:8px 16px; border-radius:10px; border:1px solid var(--border-subtle);">
                    Total Antrean: <span style="color:var(--text-main); font-family: 'Space Mono', monospace; font-size: 15px; margin-left: 4px;"><?= count($logs) ?> Setoran</span>
                </div>
            </div>

            <div class="prod-table-responsive" style="border: none; border-radius: 0; margin: 0;">
                <table class="prod-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Target Produksi</th>
                            <th style="width: 20%;">Waktu & Pekerja</th>
                            <th style="width: 20%;">Tahapan & Catatan</th>
                            <th style="text-align:right; width: 15%;">Qty & Harga/Pcs</th>
                            <th style="text-align:center; width: 20%;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $pl): ?>
                        <tr>
                            <td>
                                <div style="font-size:14px; font-weight:900; color: var(--text-main); margin-bottom: 6px; white-space: normal; line-height: 1.4; display:flex; align-items:flex-start; gap:8px;">
                                    <i class="ph-fill ph-cube" style="color: var(--prod-primary); font-size: 18px; margin-top:1px;"></i> 
                                    <?= esc($pl['item_name'] ?? $pl['sku']) ?>
                                </div>
                                <div style="font-size:11px; font-weight:800; font-family:'Space Mono', monospace; color:var(--text-muted); margin-left:26px; margin-bottom:4px;">Target: <?= esc($pl['sku']) ?></div>
                                <div style="font-size:11px; font-weight:800; font-family:'Space Mono', monospace; color:var(--text-muted); margin-left:26px; margin-bottom:8px;"><?= esc($pl['spk_number']) ?></div>
                                
                                <?php 
                                // MENAMPILKAN CATATAN EMBLEM DARI WORK ORDER TEPAT DI BAWAH NAMA & SPK
                                if(!empty($emblemNote)): 
                                ?>
                                    <div style="font-size: 11px; color: var(--prod-accent-dark); font-weight: 900; background: var(--prod-accent-soft); padding: 4px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; margin-left: 26px; border: 1px dashed rgba(139, 92, 246, 0.3);">
                                        <i class="ph-bold ph-tag"></i> <?= esc($emblemNote) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 900; font-size:14px; color: var(--text-main); margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                                    <i class="ph-fill ph-user-circle" style="color: var(--text-muted); font-size:20px;"></i> <?= esc($pl['employee_name']) ?>
                                </div>
                                <div style="font-size:12px; font-weight:700; color: var(--text-muted); margin-left:28px;">
                                    <i class="ph-bold ph-clock" style="color: var(--prod-info);"></i> <?= date('d M Y, H:i', strtotime($pl['production_date'])) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 900; color: var(--prod-primary-dark); font-size:13px; margin-bottom: 8px; white-space:normal; line-height:1.5;">
                                    <?= esc($pl['operation_name']) ?>
                                </div>
                                
                                <?php if(!empty($pl['notes'])): ?>
                                    <div style="font-size: 11px; color: var(--prod-warning-dark); font-weight: 800; background:var(--prod-warning-soft); padding:6px 10px; border-radius:8px; display:inline-flex; align-items: center; gap: 6px; border: 1px dashed rgba(245, 158, 11, 0.3); margin-bottom: 4px;">
                                        <i class="ph-fill ph-warning-circle" style="font-size: 14px;"></i> <?= esc($pl['notes']) ?>
                                    </div><br>
                                <?php endif; ?>

                                <?php 
                                // TAMPILKAN MATERIAL EXTRA DARI LOG SETORAN HARIAN JIKA ADA
                                if(!empty($pl['extra_materials'])): 
                                    $extras = json_decode($pl['extra_materials'], true);
                                    if(is_array($extras) && count($extras) > 0):
                                        foreach($extras as $ex):
                                            $skuRm = $ex['rm_sku'] ?? '';
                                            $qtyRm = $ex['qty'] ?? $ex['qty_used'] ?? 1;
                                            $namaBahan = $ex['material_name'] ?? '';
                                            
                                            // Jika controller hanya menyimpan SKU, cari namanya
                                            if(empty($namaBahan) && !empty($skuRm)){
                                                $rmData = $db->table('raw_materials')->select('material_name')->where('sku_material', $skuRm)->get()->getRowArray();
                                                if($rmData) {
                                                    $namaBahan = $rmData['material_name'];
                                                } else {
                                                    $namaBahan = $skuRm;
                                                }
                                            }
                                ?>
                                    <div style="font-size: 11px; color: var(--prod-info-dark); font-weight: 800; background:var(--prod-info-soft); padding:6px 10px; border-radius:8px; display:inline-flex; align-items: center; gap: 6px; border: 1px dashed rgba(14, 165, 233, 0.3); margin-bottom: 4px;">
                                        <i class="ph-bold ph-plus-circle" style="font-size: 14px;"></i> 
                                        Bahan Extra: <?= esc($namaBahan) ?> (<?= $qtyRm ?> Pcs)
                                    </div><br>
                                <?php 
                                        endforeach;
                                    endif;
                                endif; 
                                ?>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-family: 'Space Mono', monospace; font-weight: 900; font-size:20px; color: var(--prod-success-dark); text-shadow: 0 2px 10px rgba(16,185,129,0.2); margin-bottom: 4px;">
                                    <?= $pl['qty_produced'] ?> <span style="font-size:12px; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--text-muted); text-shadow: none;">Pcs</span>
                                </div>
                                <div id="display_wage_<?= $pl['id'] ?>" style="font-size:12px; font-weight:800; color: var(--prod-danger-dark); background: var(--prod-danger-soft); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                    Rp <?= number_format($pl['wage_per_piece'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                    <a href="<?= base_url('production/approve_log/'.$pl['id']) ?>" class="btn-action-sm btn-acc" onclick="konfirmasiAksi(event, this.href, 'ACC')"><i class="ph-bold ph-check" style="font-size: 16px;"></i> ACC</a>
                                    
                                    <button type="button" class="btn-action-sm btn-edit-price" onclick="revisiUpah(<?= $pl['id'] ?>, <?= $pl['wage_per_piece'] ?>, '<?= esc($pl['employee_name']) ?>')">
                                        <i class="ph-bold ph-pencil-simple"></i> Edit Rp
                                    </button>

                                    <a href="<?= base_url('production/reject_log/'.$pl['id']) ?>" class="btn-action-sm btn-rej" onclick="konfirmasiAksi(event, this.href, 'TOLAK')"><i class="ph-bold ph-x" style="font-size: 16px;"></i> Tolak</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#1e293b' : '#ffffff';
        const swalText = isDark ? '#f8fafc' : '#0f172a';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>, confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    // FUNGSI UNTUK MENGGANTIKAN ALERT BAWAAN BROWSER
    function konfirmasiAksi(event, url, tipe) {
        event.preventDefault(); // Mencegah link langsung berpindah halaman

        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#1e293b' : '#ffffff';
        const swalText = isDark ? '#f8fafc' : '#0f172a';

        let judul = '';
        let htmlPesan = '';
        let warnaTombol = '';
        let teksTombol = '';

        if (tipe === 'ACC') {
            judul = 'PENTING!';
            htmlPesan = `
                <div style="text-align: left; font-size: 14px; line-height: 1.6;">
                    Menerima setoran ini akan:<br><br>
                    <b>1.</b> Memotong bahan baku dari gudang.<br>
                    <b>2.</b> Mencatat upah ke gaji karyawan.<br>
                    <b>3.</b> Menambah stok produk (jika tahap final).
                </div>
            `;
            warnaTombol = '#10b981'; // prod-success
            teksTombol = '<i class="ph-bold ph-check"></i> Ya, Lanjutkan';
        } else {
            judul = 'Tolak Setoran?';
            htmlPesan = '<div style="text-align: left; font-size: 14px;">Setoran akan dibatalkan dan tidak dihitung ke gaji pekerja.</div>';
            warnaTombol = '#ef4444'; // prod-danger
            teksTombol = '<i class="ph-bold ph-x"></i> Ya, Tolak';
        }

        Swal.fire({
            title: judul,
            html: htmlPesan,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: warnaTombol,
            cancelButtonColor: '#64748b',
            confirmButtonText: teksTombol,
            cancelButtonText: 'Batal',
            background: swalBg,
            color: swalText,
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading agar user tidak klik berkali-kali
                Swal.fire({ 
                    title: 'Memproses...', 
                    allowOutsideClick: false, 
                    background: swalBg, 
                    color: swalText,
                    didOpen: () => { Swal.showLoading() } 
                });
                // Arahkan ke URL eksekusi ACC/Tolak
                window.location.href = url;
            }
        });
    }

    // FUNGSI UNTUK REVISI HARGA UPAH SEBELUM ACC
    function revisiUpah(logId, currentWage, empName) {
        Swal.fire({
            title: `Revisi Upah (${empName})`,
            html: `
                <div style="text-align: left; font-size: 13px; color: var(--text-muted); margin-bottom: 10px;">
                    Ubah harga upah untuk setoran ini.
                </div>
                <div style="display:flex; align-items:center; border:2px solid var(--border-subtle); border-radius:12px; overflow:hidden; margin-bottom: 15px;">
                    <span style="background:var(--bg-input); padding:12px 16px; font-weight:bold; color:var(--text-muted); border-right:2px solid var(--border-subtle);">Rp</span>
                    <input id="swal-input-wage" type="text" inputmode="numeric" class="swal2-input" value="${currentWage}" 
                           style="margin:0; border:none; box-shadow:none; font-family:'Space Mono', monospace; font-size:18px; font-weight:bold; color:var(--prod-info-dark);">
                </div>
                <div style="text-align: left; background: rgba(245, 158, 11, 0.1); padding: 12px; border-radius: 8px; border: 1px dashed rgba(245, 158, 11, 0.4);">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--prod-warning-dark); cursor: pointer;">
                        <input type="checkbox" id="swal-update-master" style="width: 18px; height: 18px; accent-color: var(--prod-warning-dark);">
                        Sekaligus Ubah Harga Standar di Master Resep (BOM)
                    </label>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-floppy-disk"></i> Simpan Revisi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            customClass: { popup: 'swal2-custom-radius' },
            preConfirm: () => {
                let val = document.getElementById('swal-input-wage').value.replace(/[^0-9]/g, '');
                let updateMaster = document.getElementById('swal-update-master').checked;
                if (!val) { Swal.showValidationMessage('Upah tidak boleh kosong'); }
                return { newWage: val, updateMaster: updateMaster };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let data = result.value;
                
                let csrfInput = document.querySelector('#csrf-form input[type="hidden"]');
                let csrfName = csrfInput.getAttribute('name');
                let csrfHash = csrfInput.value;

                let formData = new FormData();
                formData.append('log_id', logId);
                formData.append('new_wage', data.newWage);
                formData.append('update_master', data.updateMaster);
                formData.append(csrfName, csrfHash); 

                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

                fetch('<?= base_url("production/update_log_wage") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' 
                    }
                })
                .then(async (res) => {
                    if (res.status === 403) throw new Error("CSRF_EXPIRED");
                    const responseText = await res.text();
                    try {
                        return JSON.parse(responseText);
                    } catch (e) {
                        throw new Error("SERVER_ERROR");
                    }
                })
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            customClass: { popup: 'swal2-custom-radius' }
                        }).then(() => {
                            let displayEl = document.getElementById('display_wage_' + logId);
                            if(displayEl) displayEl.innerHTML = 'Rp ' + parseInt(data.newWage).toLocaleString('id-ID');
                        });
                    } else {
                        Swal.fire('Error!', res.message || 'Gagal mengubah upah', 'error');
                    }
                }).catch(err => { 
                    if(err.message === "CSRF_EXPIRED") {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sesi Berakhir',
                            text: 'Sistem keamanan mendeteksi sesi kedaluwarsa. Halaman akan dimuat ulang...',
                            showConfirmButton: false,
                            timer: 2000,
                            customClass: { popup: 'swal2-custom-radius' }
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error!', 'Terjadi kesalahan pada server. Pastikan input valid.', 'error'); 
                    }
                });
            }
        });

        const inputW = document.getElementById('swal-input-wage');
        if(inputW) {
            inputW.addEventListener('keyup', function(e) {
                let val = this.value.replace(/[^,\d]/g, '');
                if(!val) return;
                let sisa = val.length % 3;
                let rupiah = val.substr(0, sisa);
                let ribuan = val.substr(sisa).match(/\d{3}/gi);
                if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
                this.value = rupiah;
            });
        }
    }
</script>

<?= $this->endSection() ?>