<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .action-panel { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1)); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 16px; padding: 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;}
    
    .panel-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 15px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);}
    .panel-box label { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;}
    
    .calc-input { background: var(--bg-base); border: 2px solid var(--border-subtle); padding: 10px 15px; border-radius: 8px; font-size: 16px; font-weight: 800; width: 120px; outline: none; transition: 0.2s;}
    .calc-input:focus { border-color: #10b981; }

    .btn-calc { background: var(--text-main); color: var(--bg-base); border: none; padding: 12px 20px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px;}
    .btn-calc:hover { opacity: 0.8; transform: translateY(-2px);}

    /* TABEL EXCEL STYLE */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 15px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); position: sticky; top: 0; z-index: 10;}
    td { padding: 12px 20px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; color: var(--text-main);}
    tr:hover td { background: rgba(0,0,0,0.01); }

    .prod-info { display: flex; align-items: center; gap: 12px; }
    .prod-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-subtle);}
    .prod-name { font-size: 13px; font-weight: 800; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    .prod-sku { font-size: 10px; color: var(--text-muted); font-family: monospace; background: var(--bg-base); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-subtle); margin-top: 4px; display: inline-block;}

    .price-old { font-size: 14px; font-weight: 600; color: var(--text-muted); text-decoration: line-through;}
    
    .input-price-wrapper { position: relative; display: inline-block;}
    .input-price-wrapper span { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-main); font-weight: 800; font-size: 14px;}
    .input-new-price { width: 160px; background: rgba(16, 185, 129, 0.05); border: 2px solid rgba(16, 185, 129, 0.3); padding: 12px 15px 12px 40px; border-radius: 8px; font-size: 15px; font-weight: 900; color: #10b981; outline: none; transition: 0.2s;}
    .input-new-price:focus { border-color: #10b981; background: #fff; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);}
    html.dark .input-new-price:focus { background: var(--bg-surface); }

    .btn-submit-all { background: #10b981; color: #fff; border: none; padding: 15px 30px; border-radius: 12px; font-size: 15px; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);}
    .btn-submit-all:hover { background: #059669; transform: translateY(-3px);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-currency-circle-dollar" style="color: #10b981;"></i> Update Harga Massal</h1>
        <p>Sesuaikan harga jual etalase <b><?= esc($shop['shop_name']) ?></b> dengan cepat merespon kenaikan HPP bahan baku.</p>
    </div>
</div>

<form action="<?= base_url('/shopee/update_price_action/'.$shop['shop_id']) ?>" method="post" id="massPriceForm">
    <?= csrf_field() ?>

    <div class="action-panel">
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="panel-box">
                <div>
                    <label>Kalkulator Kenaikan (%)</label>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                        <input type="number" id="percentCalc" class="calc-input" placeholder="Cth: 10" step="0.1">
                        <span style="font-weight: 900; font-size: 18px; color: var(--text-muted);">%</span>
                    </div>
                </div>
                <button type="button" class="btn-calc" onclick="applyPercentage()">
                    <i class="ph ph-calculator"></i> Terapkan ke Bawah
                </button>
            </div>
        </div>

        <button type="button" class="btn-submit-all" onclick="confirmSubmit()">
            <i class="ph ph-cloud-arrow-up"></i> Simpan Semua ke Shopee
        </button>
    </div>

    <div class="table-card">
        <div style="max-height: 600px; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Produk Etalase (Shopee)</th>
                        <th>Harga Saat Ini</th>
                        <th style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Harga Baru (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 40px;">Tidak ada produk untuk diupdate.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($products as $p): ?>
                        <tr>
                            <td>
                                <div class="prod-info">
                                    <?php if(!empty($p['image_url'])): ?>
                                        <img src="<?= esc($p['image_url']) ?>" class="prod-img">
                                    <?php else: ?>
                                        <div class="prod-img" style="display:flex; align-items:center; justify-content:center; background:var(--bg-base);"><i class="ph ph-image"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="prod-name"><?= esc($p['item_name']) ?></div>
                                        <div class="prod-sku">SKU: <?= esc($p['item_sku']) ?: 'Tanpa SKU' ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-old" data-oldprice="<?= $p['price'] ?>">
                                    Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td>
                                <div class="input-price-wrapper">
                                    <span>Rp</span>
                                    <input type="number" name="new_prices[<?= $p['item_id'] ?>]" class="input-new-price price-field" value="<?= $p['price'] ?>" required min="100">
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FUNGSI KALKULATOR PERSENTASE
    function applyPercentage() {
        let percent = parseFloat(document.getElementById('percentCalc').value);
        
        if(isNaN(percent)) {
            Swal.fire('Ups!', 'Masukkan angka persentase yang valid.', 'warning');
            return;
        }

        let multiplier = 1 + (percent / 100);
        let priceInputs = document.querySelectorAll('.price-field');
        
        priceInputs.forEach(input => {
            // Ambil harga lama dari atribut data elemen sebelumnya
            let oldPriceElem = input.closest('tr').querySelector('.price-old');
            let oldPrice = parseFloat(oldPriceElem.getAttribute('data-oldprice'));
            
            // Hitung harga baru
            let newPrice = Math.round(oldPrice * multiplier);
            
            // Update value di input form
            input.value = newPrice;
            
            // Berikan efek highlight sementara agar user sadar angkanya berubah
            input.style.backgroundColor = '#fcd34d';
            setTimeout(() => {
                input.style.backgroundColor = '';
            }, 500);
        });

        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', 
            title: `Kenaikan ${percent}% berhasil diaplikasikan ke tabel!`, 
            showConfirmButton: false, timer: 2000
        });
    }

    // FUNGSI KONFIRMASI SUBMIT KE API
    function confirmSubmit() {
        const isDark = document.documentElement.classList.contains('dark');
        
        Swal.fire({
            title: 'Tembak ke Shopee?',
            text: "Perubahan harga ini akan langsung tayang di aplikasi pembeli. Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#ef4444',
            confirmButtonText: '<i class="ph ph-cloud-arrow-up"></i> Ya, Simpan!',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading UI saat menembak API massal
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghubungi server Shopee. Mohon tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('massPriceForm').submit();
            }
        });
    }
</script>

<?= $this->endSection() ?>