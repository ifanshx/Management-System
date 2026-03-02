<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER UTAMA --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;}
    .btn-secondary:hover { background: var(--bg-base); border-color: var(--accent-main); color: var(--accent-main);}

    /* --- BENTO CARD & CONTROLS --- */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    .table-controls { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: rgba(0,0,0,0.01); }
    html.dark .table-controls { background: rgba(255,255,255,0.02); }
    
    /* --- SEARCH BOX --- */
    .search-box { position: relative; width: 100%; max-width: 350px; }
    .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;}
    .search-box input { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 16px 12px 44px; border-radius: 12px; color: var(--text-main); outline: none; font-family: inherit; font-size: 13px; transition: 0.2s; font-weight: 500;}
    .search-box input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    
    /* --- TABEL PRODUK --- */
    .table-responsive { width: 100%; overflow-x: auto; min-height: 400px;}
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 16px 25px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500; vertical-align: middle;}
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }

    /* Info Produk */
    .product-info { display: flex; align-items: center; gap: 15px; }
    .product-icon { width: 45px; height: 45px; border-radius: 10px; background: rgba(249, 115, 22, 0.1); color: #f97316; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; border: 1px solid rgba(249, 115, 22, 0.2);}
    .prod-name { font-size: 14px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;}
    .prod-id { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); font-weight: 700; background: var(--bg-base); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-subtle); display: inline-block;}

    /* SKU Tag */
    .sku-tag { background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px dashed rgba(99, 102, 241, 0.3); padding: 4px 10px; border-radius: 6px; font-family: monospace; font-weight: 800; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;}
    .sku-missing { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }

    /* Stock Status */
    .stock-ok { color: #10b981; font-weight: 800; font-size: 15px;}
    .stock-low { color: #f59e0b; font-weight: 800; font-size: 15px;}
    .stock-empty { color: #ef4444; font-weight: 800; font-size: 15px;}

    /* Actions */
    .action-group { display: flex; gap: 8px; justify-content: flex-end; align-items: center; }
    
    .btn-map { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;}
    .btn-map:hover { background: var(--text-main); color: var(--bg-base); box-shadow: 0 4px 10px rgba(0,0,0,0.1);}
    
    .btn-var { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s;}
    .btn-var:hover { background: #3b82f6; color: #fff; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-package"></i> Master Produk Shopee</h1>
        <p>Katalog toko <b><?= esc($shop['shop_name']) ?></b>. Hubungkan SKU ini dengan Gudang Lokal untuk Sinkronisasi Stok Otomatis.</p>
    </div>
    
    <div>
        <a href="<?= base_url('/shopee') ?>" class="btn-secondary">
            <i class="ph ph-arrow-left"></i> Kembali ke Integrasi
        </a>
    </div>
</div>

<div class="table-card">
    
    <div class="table-controls">
        <div class="search-box">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari Nama Knalpot atau SKU..." onkeyup="filterTable()">
        </div>

        <div>
            <a href="<?= base_url('/shopee/create_product/'.$shop['shop_id']) ?>" class="btn-secondary" style="background: #f97316; color: white; border-color: #f97316;">
                <i class="ph ph-plus-circle"></i> Add Product
            </a>
        </div>
        
        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
            Total: <span style="color: var(--text-main);"><?= count($products) ?> Produk Aktif</span>
        </div>
    </div>

    <div class="table-responsive">
        <table id="productTable">
            <thead>
                <tr>
                    <th style="width: 40%;">Informasi Produk (Shopee)</th>
                    <th>SKU (Stock Keeping Unit)</th>
                    <th style="text-align: right;">Harga Jual</th>
                    <th style="text-align: center;">Sisa Stok</th>
                    <th style="text-align: right;">Aksi ERP</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 80px 20px;">
                            <i class="ph ph-archive-box" style="font-size: 48px; color: var(--border-subtle); display: block; margin-bottom: 10px;"></i>
                            <div style="color: var(--text-main); font-weight: 800; font-size: 15px;">Katalog Masih Kosong</div>
                            <p style="color: var(--text-muted); font-size: 13px;">Silakan klik "Sync Produk" di halaman sebelumnya untuk menyedot data dari Shopee.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $prod): ?>
                    <tr class="prod-row">
                        <td>
                            <div class="product-info">
                                <div class="product-icon"><i class="ph ph-shopping-bag"></i></div>
                                <div>
                                    <div class="prod-name" title="<?= esc($prod['item_name']) ?>"><?= esc($prod['item_name']) ?></div>
                                    <div class="prod-id"><i class="ph ph-hash"></i> <?= esc($prod['item_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <?php if(!empty($prod['item_sku'])): ?>
                                <span class="sku-tag prod-sku"><i class="ph ph-barcode"></i> <?= esc($prod['item_sku']) ?></span>
                            <?php else: ?>
                                <span class="sku-tag sku-missing prod-sku"><i class="ph ph-warning-circle"></i> Tanpa SKU</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: var(--text-main); font-size: 14px;">
                            Rp <?= number_format($prod['price'], 0, ',', '.') ?>
                        </td>

                        <td style="text-align: center;">
                            <?php 
                                $stock = $prod['stock'];
                                $stockClass = 'stock-ok';
                                if($stock <= 5 && $stock > 0) $stockClass = 'stock-low';
                                if($stock == 0) $stockClass = 'stock-empty';
                            ?>
                            <span class="<?= $stockClass ?>"><?= $stock ?></span>
                            <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pcs</div>
                        </td>

                        <td style="text-align: right;">
                            <div class="action-group">
                                <button type="button" class="btn-map" onclick="mapSku('<?= esc($prod['item_id']) ?>', '<?= esc($prod['item_name']) ?>')">
                                    <i class="ph ph-link"></i> Map SKU
                                </button>
                                
                                <a href="<?= base_url('/shopee/variation/'.$shop['shop_id'].'/'.$prod['item_id']) ?>" class="btn-var">
                                    <i class="ph ph-tree-structure"></i> Atur Varian
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Filter Pencarian
function filterTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("productTable");
    let tr = table.getElementsByClassName("prod-row");

    for (let i = 0; i < tr.length; i++) {
        let name = tr[i].querySelector(".prod-name").textContent;
        let sku = tr[i].querySelector(".prod-sku").textContent;
        
        if (name.toLowerCase().indexOf(filter) > -1 || sku.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

// Fitur Pemetaan SKU (Menyimpan data lewat AJAX tanpa reload)
function mapSku(itemId, itemName) {
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: 'Kaitkan Produk ke Gudang',
        html: `<div style="text-align:left; font-size:13px; margin-bottom:15px; color:#71717a;">Produk Shopee: <br><b style="color:#09090b;">${itemName}</b></div>
               <input id="swal-sku-input" class="swal2-input" placeholder="Ketik Kode SKU Gudang Pabrik Noric..." style="font-family:monospace; font-size:14px; margin-top:0; text-transform: uppercase;">`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="ph ph-floppy-disk"></i> Simpan Pemetaan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4f46e5',
        background: isDark ? '#18181b' : '#ffffff', 
        color: isDark ? '#f4f4f5' : '#09090b',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const skuVal = document.getElementById('swal-sku-input').value;
            if (!skuVal) {
                Swal.showValidationMessage('SKU Gudang tidak boleh kosong!');
                return false;
            }
            
            // Eksekusi AJAX Fetch ke Controller
            let formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('sku', skuVal);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>'); // Keamanan Anti-Hacking

            return fetch('<?= base_url('/shopee/map_sku') ?>', {
                method: 'POST',
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
                    title: 'Berhasil Diikat!', 
                    text: result.value.message,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    location.reload(); // Reload halaman untuk melihat perubahan badge SKU
                });
            } else {
                Swal.fire('Gagal', result.value.message, 'error');
            }
        }
    });
}
</script>

<?= $this->endSection() ?>