<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. POS CORE LAYOUT (FULLSCREEN APP FEEL)
       ========================================================= */
    /* Sembunyikan header bawaan template jika memungkinkan, atau sesuaikan margin */
    .pos-wrapper {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        height: calc(100vh - 120px); /* Menyesuaikan tinggi layar viewport */
        min-height: 650px;
        margin-top: -10px;
    }
    @media (max-width: 1024px) { 
        .pos-wrapper { grid-template-columns: 1fr; height: auto; } 
    }

    /* =========================================================
       2. LEFT PANEL: CATALOG & SEARCH
       ========================================================= */
    .pos-main {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 15px 40px -15px rgba(0,0,0,0.05);
    }

    .pos-search-bar {
        padding: 20px 25px;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border-subtle);
        z-index: 10;
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .pos-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px; white-space: nowrap;}
    
    .search-input-group { position: relative; flex: 1; }
    .search-input-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 20px;}
    .search-input-group input { width: 100%; background: var(--bg-base); border: 2px solid transparent; padding: 15px 20px 15px 50px; border-radius: 16px; font-size: 15px; font-weight: 600; color: var(--text-main); outline: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .search-input-group input:focus { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);}

    /* Product Grid */
    .pos-grid {
        padding: 25px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        overflow-y: auto;
        align-content: start;
        flex: 1;
        background: rgba(0,0,0,0.015);
    }
    html.dark .pos-grid { background: rgba(255,255,255,0.01); }

    /* Custom Scrollbar for Grid */
    .pos-grid::-webkit-scrollbar { width: 8px; }
    .pos-grid::-webkit-scrollbar-track { background: transparent; }
    .pos-grid::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
    .pos-grid::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    /* Product Card Tactile Design */
    .product-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 160px;
        user-select: none;
    }
    .product-card:hover { border-color: #3b82f6; transform: translateY(-4px); box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.15);}
    .product-card:active { transform: translateY(0) scale(0.98); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.1);}
    
    .p-sku { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 800; background: var(--bg-base); padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 12px; border: 1px solid var(--border-subtle);}
    .p-name { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    
    .p-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;}
    .p-price { font-size: 16px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}
    .p-price span { font-size: 10px; font-weight: 700; color: var(--text-muted); display: block; font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0; margin-top: 2px;}
    
    .p-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 12px; padding: 6px 12px; border-radius: 8px; font-weight: 900; border: 1px solid rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 4px;}
    .p-stock.low { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2);}

    /* =========================================================
       3. RIGHT PANEL: SMART RECEIPT & CHECKOUT
       ========================================================= */
    .pos-sidebar {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 15px 40px -15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .sidebar-header { padding: 20px 25px; border-bottom: 2px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center;}
    .sidebar-title { font-size: 18px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;}
    .sidebar-title i { color: #3b82f6; font-size: 24px; background: rgba(59, 130, 246, 0.1); padding: 6px; border-radius: 8px;}
    
    .btn-clear { background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid transparent; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 5px;}
    .btn-clear:hover { background: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);}
    
    /* Cart Items Area */
    .cart-items { flex: 1; overflow-y: auto; padding: 20px 25px; display: flex; flex-direction: column; gap: 15px; background: var(--bg-base);}
    .cart-items::-webkit-scrollbar { width: 6px; }
    .cart-items::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

    .cart-empty { text-align: center; color: var(--text-muted); margin-top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 10px;}
    
    /* Item Card inside Cart */
    .cart-item { background: var(--bg-surface); padding: 18px; border-radius: 16px; border: 1px solid var(--border-subtle); animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 10px rgba(0,0,0,0.01);}
    @keyframes popIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    
    .c-name { font-size: 14px; font-weight: 900; color: var(--text-main); line-height: 1.3; margin-bottom: 12px; padding-right: 25px; position: relative;}
    .btn-del { position: absolute; top: -2px; right: -5px; color: var(--text-muted); border: none; background: transparent; cursor: pointer; font-size: 20px; transition: 0.2s;}
    .btn-del:hover { color: #ef4444; transform: scale(1.15) rotate(5deg);}

    .c-controls { display: flex; justify-content: space-between; align-items: center; gap: 10px;}
    
    /* Elegant Qty Selector */
    .qty-controls { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden;}
    .btn-qty { border: none; background: transparent; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; cursor: pointer; color: var(--text-main); transition: 0.2s;}
    .btn-qty:hover { background: rgba(59, 130, 246, 0.1); color: #3b82f6;}
    .qty-val { font-size: 14px; font-weight: 900; width: 34px; text-align: center; border-left: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle);}
    
    /* Subtotal Display in Cart */
    .c-subtotal { font-size: 15px; color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace;}
    
    /* Edit Price Inline */
    .price-editor { margin-top: 10px; display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 8px; overflow: hidden; focus-within: border-color: #3b82f6;}
    .price-editor span { background: rgba(0,0,0,0.03); padding: 6px 10px; font-size: 11px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .price-editor input { width: 100%; border: none; padding: 6px 10px; font-size: 13px; font-weight: 700; color: #10b981; outline: none; background: transparent; font-family: 'Space Mono', monospace;}

    /* =========================================================
       4. CHECKOUT FOOTER (STICKY)
       ========================================================= */
    .checkout-footer { padding: 25px; background: var(--bg-surface); border-top: 1px solid var(--border-subtle); box-shadow: 0 -10px 20px rgba(0,0,0,0.02); z-index: 10;}
    
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 15px; border-radius: 12px; font-size: 13px; font-weight: 700; outline: none; color: var(--text-main); transition: 0.3s;}
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
    
    .total-box { display: flex; justify-content: space-between; align-items: flex-end; margin: 20px 0; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(59, 130, 246, 0.08)); padding: 20px; border-radius: 16px; border: 1px dashed rgba(16, 185, 129, 0.3);}
    .total-label { font-size: 13px; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px;}
    .total-amount { font-size: 34px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1px;}

    .btn-pay { width: 100%; background: #3b82f6; color: #fff; border: none; padding: 22px; border-radius: 16px; font-size: 18px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 12px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);}
    .btn-pay:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.6);}
    .btn-pay:active { transform: translateY(0); box-shadow: none;}
    .btn-pay:disabled { background: var(--bg-base); border: 2px dashed var(--border-subtle); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none;}
</style>

<div class="pos-wrapper">
    
    <div class="pos-main">
        <div class="pos-search-bar">
            <div class="pos-title">
                <i class="ph-fill ph-storefront" style="color: #10b981;"></i> POS Kasir
            </div>
            <div class="search-input-group">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" id="searchItem" placeholder="Ketik nama produk atau scan barcode SKU..." onkeyup="filterProducts()" autocomplete="off">
            </div>
        </div>
        
        <div class="pos-grid" id="productList">
            <?php foreach($products as $p): ?>
                <?php $isLow = $p['physical_stock'] <= 5 ? 'low' : ''; ?>
                <div class="product-card prod-item" onclick="addToCart('<?= esc($p['sku']) ?>', '<?= esc(addslashes($p['item_name'])) ?>', <?= $p['hpp'] ?>, <?= $p['physical_stock'] ?>)">
                    
                    <div>
                        <span class="p-sku prod-sku"><i class="ph-bold ph-barcode"></i> <?= esc($p['sku']) ?></span>
                        <div class="p-name prod-name"><?= esc($p['item_name']) ?></div>
                    </div>
                    
                    <div class="p-footer">
                        <div class="p-price">
                            Rp <?= number_format($p['hpp'], 0, ',', '.') ?>
                            <span>HARGA DASAR (Rp)</span>
                        </div>
                        <div class="p-stock <?= $isLow ?>">
                            <i class="ph-fill ph-package"></i> <?= $p['physical_stock'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-sidebar">
        
        <div class="sidebar-header">
            <div class="sidebar-title">
                <i class="ph-fill ph-receipt"></i> Struk Tagihan
            </div>
            <button onclick="clearCart()" class="btn-clear" title="Kosongkan Semua">
                <i class="ph-bold ph-trash"></i> Bersihkan
            </button>
        </div>
        
        <div class="cart-items" id="cartContainer">
            <div class="cart-empty">
                <div style="background: var(--bg-surface); padding: 20px; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 10px;">
                    <i class="ph-fill ph-shopping-cart-simple" style="font-size: 50px; color: var(--border-subtle);"></i>
                </div>
                Keranjang Masih Kosong
                <span style="font-size: 12px; font-weight: 500; opacity: 0.7;">Klik atau scan produk di kiri untuk menambahkan.</span>
            </div>
        </div>

        <div class="checkout-footer">
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1.5;">
                    <label>Pelanggan (Opsional)</label>
                    <input type="text" id="custName" class="form-control" placeholder="Umum / Walk-in" autocomplete="off">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Metode Bayar</label>
                    <select id="payMethod" class="form-control" style="cursor: pointer;">
                        <option value="Cash">💵 Tunai</option>
                        <option value="Transfer BCA">🏦 Trf BCA</option>
                        <option value="Transfer BRI">🏦 Trf BRI</option>
                        <option value="QRIS">📱 QRIS</option>
                    </select>
                </div>
            </div>

            <div class="total-box">
                <div>
                    <div class="total-label">Grand Total</div>
                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 800; margin-top: 5px;" id="itemCountDisplay">0 Item Terpilih</div>
                </div>
                <div class="total-amount" id="totalDisplay">Rp 0</div>
            </div>

            <button class="btn-pay" id="btnCheckout" onclick="processCheckout()" disabled>
                <i class="ph-bold ph-printer"></i> Bayar & Cetak Struk
            </button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = [];

    // Auto-focus pada Search Box saat halaman dimuat (untuk Barcode Scanner)
    window.onload = function() {
        document.getElementById('searchItem').focus();
    };

    // Filter Produk (Search)
    function filterProducts() {
        let input = document.getElementById('searchItem').value.toLowerCase();
        let items = document.getElementsByClassName('prod-item');
        for (let i = 0; i < items.length; i++) {
            let name = items[i].querySelector('.prod-name').innerText.toLowerCase();
            let sku = items[i].querySelector('.prod-sku').innerText.toLowerCase();
            if (name.includes(input) || sku.includes(input)) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    // Tambah Barang ke Keranjang
    function addToCart(sku, name, defaultPrice, maxStock) {
        if(maxStock <= 0) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Habis!', showConfirmButton: false, timer: 2000 });
            return;
        }

        let existing = cart.find(item => item.sku === sku);
        if (existing) {
            if(existing.qty < maxStock) {
                existing.qty += 1;
            } else {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Batas Stok Maksimal!', showConfirmButton: false, timer: 2000 });
            }
        } else {
            cart.unshift({ sku: sku, name: name, price: defaultPrice, qty: 1, maxStock: maxStock }); // unshift agar muncul di atas
        }
        renderCart();
    }

    // Ubah Kuantitas
    function updateQty(sku, change) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let newQty = item.qty + change;
            if(newQty > item.maxStock) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Melebihi Stok!', showConfirmButton: false, timer: 2000 });
                return;
            }
            if(newQty > 0) {
                item.qty = newQty;
            } else {
                cart = cart.filter(i => i.sku !== sku);
            }
            renderCart();
        }
    }

    // Ubah Harga Kustom
    function updatePrice(sku, newPrice) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let parsed = parseFloat(newPrice);
            item.price = isNaN(parsed) || parsed < 0 ? 0 : parsed;
        }
        renderCart(false); 
    }

    // Hapus Baris Item
    function removeItem(sku) {
        cart = cart.filter(item => item.sku !== sku);
        renderCart();
    }

    // Kosongkan Semua
    function clearCart() {
        if(cart.length === 0) return;
        Swal.fire({
            title: 'Hapus Semua?',
            text: "Keranjang tagihan akan dikosongkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Kosongkan'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
            }
        });
    }

    // Mesin Render Keranjang
    function renderCart(rebuildHTML = true) {
        const container = document.getElementById('cartContainer');
        const btnCheckout = document.getElementById('btnCheckout');
        const totalDisplay = document.getElementById('totalDisplay');
        const itemCountDisplay = document.getElementById('itemCountDisplay');
        
        if(cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <div style="background: var(--bg-surface); padding: 20px; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 10px;">
                        <i class="ph-fill ph-shopping-cart-simple" style="font-size: 50px; color: var(--border-subtle);"></i>
                    </div>
                    Keranjang Masih Kosong
                    <span style="font-size: 12px; font-weight: 500; opacity: 0.7;">Klik atau scan produk di kiri untuk menambahkan.</span>
                </div>`;
            totalDisplay.innerText = 'Rp 0';
            itemCountDisplay.innerText = '0 Item Terpilih';
            btnCheckout.disabled = true;
            btnCheckout.innerHTML = '<i class="ph-bold ph-printer"></i> Bayar & Cetak Struk';
            return;
        }

        let total = 0;
        let totalItems = 0;

        if(rebuildHTML) {
            let html = '';
            cart.forEach(item => {
                let subtotal = item.qty * item.price;
                html += `
                <div class="cart-item">
                    <div class="c-name">
                        ${item.name}
                        <button class="btn-del" onclick="removeItem('${item.sku}')"><i class="ph-fill ph-x-circle"></i></button>
                    </div>
                    
                    <div class="c-controls">
                        <div class="qty-controls">
                            <button class="btn-qty" onclick="updateQty('${item.sku}', -1)">-</button>
                            <div class="qty-val">${item.qty}</div>
                            <button class="btn-qty" onclick="updateQty('${item.sku}', 1)">+</button>
                        </div>
                        <div class="c-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                    </div>

                    <div class="price-editor">
                        <span>Rp</span>
                        <input type="number" value="${item.price}" onchange="updatePrice('${item.sku}', this.value)" min="0" onblur="renderCart(true)">
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        // Kalkulasi ulang total tanpa rebuild HTML jika false (mencegah kedip saat ngetik harga custom)
        cart.forEach(item => {
            total += (item.qty * item.price);
            totalItems += item.qty;
        });

        totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
        itemCountDisplay.innerText = `${totalItems} Item Terpilih`;
        btnCheckout.disabled = false;
        btnCheckout.innerHTML = `Bayar Rp ${total.toLocaleString('id-ID')} <i class="ph-bold ph-arrow-right"></i>`;
        
        // Scroll keranjang ke paling atas saat barang baru ditambahkan
        if(rebuildHTML) container.scrollTop = 0;
    }

    // Proses Pembayaran ke Database
    function processCheckout() {
        if(cart.length === 0) return;

        const isDark = document.documentElement.classList.contains('dark');
        let custName = document.getElementById('custName').value || 'Umum';
        let payMethod = document.getElementById('payMethod').value;
        let grandTotal = document.getElementById('totalDisplay').innerText;

        Swal.fire({
            title: 'Selesaikan Pembayaran?',
            html: `Terima dana sebesar <b style="font-family:monospace; font-size:18px; color:#10b981;">${grandTotal}</b><br>via <b>${payMethod}</b> dari <b>${custName}</b>?<br><br><span style="font-size:12px; color:var(--text-muted);">*Stok Master Gudang dan Etalase Shopee akan dipotong secara sinkron.</span>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-check-circle"></i> Ya, Proses Transaksi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                let formData = new FormData();
                formData.append('customer_name', custName);
                formData.append('payment_method', payMethod);
                formData.append('cart', JSON.stringify(cart));
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                return fetch('<?= base_url('/sales/process_offline') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal menghubungi server: ${error}`);
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    Swal.fire({
                        icon: 'success', 
                        title: 'Transaksi Sukses!', 
                        text: result.value.message,
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: '<i class="ph-bold ph-printer"></i> Selesai & Refresh'
                    }).then(() => {
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>