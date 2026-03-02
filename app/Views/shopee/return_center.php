<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .alert-banner { background: rgba(245, 158, 11, 0.1); border: 1px dashed rgba(245, 158, 11, 0.4); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; color: #d97706; font-size: 13px; font-weight: 600;}
    .alert-banner i { font-size: 28px; }

    .return-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
    
    .return-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-top: 4px solid #f59e0b; border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); display: flex; flex-direction: column;}
    .return-card:hover { box-shadow: 0 10px 25px rgba(245, 158, 11, 0.1); }

    .r-header { display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px; margin-bottom: 12px;}
    .r-sn { font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 900; color: var(--text-main); }
    .r-status { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 6px; background: rgba(245, 158, 11, 0.1); color: #d97706; text-transform: uppercase;}

    .r-reason-box { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px; border-radius: 8px; margin-bottom: 15px;}
    .r-reason-title { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;}
    .r-reason-text { font-size: 13px; font-weight: 700; color: #ef4444;}

    .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto;}
    
    .btn-action { padding: 12px; border-radius: 10px; font-size: 12px; font-weight: 800; cursor: pointer; text-align: center; border: none; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; text-decoration: none;}
    
    .btn-dispute { background: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}
    .btn-dispute:hover { background: #dc2626; transform: translateY(-2px);}
    
    .btn-confirm { background: var(--bg-base); color: #10b981; border: 1px solid #10b981;}
    .btn-confirm:hover { background: #10b981; color: #fff; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-shield-warning" style="color: #f59e0b;"></i> Pusat Sengketa Retur</h1>
        <p>Kelola permintaan pengembalian barang dari pembeli toko <b><?= esc($shop['shop_name']) ?></b>.</p>
    </div>
</div>

<div class="alert-banner">
    <i class="ph ph-gavel"></i>
    <div>
        <b>HAKIM SHOPEE:</b> Jika produk dikembalikan dalam kondisi rusak oleh pembeli, ajukan sengketa segera. Jangan klik "Terima Retur" karena saldo Anda akan langsung dipotong.
    </div>
</div>

<?php if(empty($returns)): ?>
    <div style="background: var(--bg-surface); padding: 50px; text-align: center; border-radius: 16px; border: 1px dashed var(--border-subtle);">
        <i class="ph ph-check-circle" style="font-size: 64px; color: #10b981; margin-bottom: 15px; opacity: 0.5;"></i>
        <h3 style="color: var(--text-main); font-weight: 800;">Aset Aman Terkendali</h3>
        <p style="color: var(--text-muted); font-size: 14px;">Tidak ada permintaan retur yang perlu diproses saat ini.</p>
    </div>
<?php else: ?>
    <div class="return-grid">
        <?php foreach($returns as $r): ?>
            <div class="return-card">
                <div class="r-header">
                    <div>
                        <div class="r-sn">Retur: <?= esc($r['return_sn']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">Order: <?= esc($r['order_sn']) ?></div>
                    </div>
                    <div class="r-status"><?= esc($r['status']) ?></div>
                </div>

                <div class="r-reason-box">
                    <div class="r-reason-title">Alasan Pembeli:</div>
                    <div class="r-reason-text">"<?= esc($r['reason'] ?? 'Tidak ada deskripsi spesifik') ?>"</div>
                    <div style="font-size: 13px; font-weight: 900; color: var(--text-main); margin-top: 10px; border-top: 1px dashed var(--border-subtle); padding-top: 8px;">
                        Nilai Retur: Rp <?= number_format($r['refund_amount'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>

                <div class="btn-group">
                    <button class="btn-action btn-dispute" onclick="openDispute('<?= esc($r['return_sn']) ?>')">
                        <i class="ph ph-hand-fist"></i> Ajukan Sengketa
                    </button>
                    
                    <a href="<?= base_url('/shopee/confirm_return/'.$shop['shop_id'].'/'.$r['return_sn']) ?>" class="btn-action btn-confirm" onclick="return confirm('Yakin murni kesalahan pabrik? Saldo akan dikembalikan ke pembeli.')">
                        <i class="ph ph-check"></i> Terima Retur
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openDispute(returnSn) {
        const isDark = document.documentElement.classList.contains('dark');
        
        Swal.fire({
            title: 'Ajukan Sengketa Retur',
            html: `
                <div style="font-size:12px; color:var(--text-muted); text-align:left; margin-bottom:10px;">
                    Sebutkan alasan detail mengapa Anda menolak retur ini (Misal: Video unboxing pembeli tidak valid, atau barang rusak karena pemakaian).
                </div>
                <textarea id="dispute-text" class="swal2-textarea" placeholder="Ketik argumen Anda di sini..." style="font-size:13px; margin:0; width:100%;"></textarea>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ph ph-gavel"></i> Kirim ke Hakim Shopee',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const textVal = document.getElementById('dispute-text').value.trim();
                if (!textVal) {
                    Swal.showValidationMessage('Alasan sengketa tidak boleh kosong!');
                    return false;
                }
                
                let formData = new FormData();
                formData.append('return_sn', returnSn);
                formData.append('dispute_text', textVal);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                return fetch('<?= base_url('/shopee/dispute_return/'.$shop['shop_id']) ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText)
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request gagal: ${error}`);
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    Swal.fire({
                        icon: 'success', 
                        title: 'Sengketa Diajukan!', 
                        text: result.value.message,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>