<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HIDE DEFAULT HEADER FOR FULLSCREEN POS FEEL --- */
    .pos-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .pos-page-title h1 { font-size: 24px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px; margin: 0;}
    
    /* --- MAIN POS LAYOUT --- */
    .pos-container { display: grid; grid-template-columns: 1fr 420px; gap: 24px; height: calc(100vh - 140px); min-height: 650px;}
    @media (max-width: 1024px) { .pos-container { grid-template-columns: 1fr; height: auto;} }

    /* =========================================================
       LEFT PANEL: PRODUCT CATALOG 
       ========================================================= */
    .pos-products { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03);}
    
    /* Search Bar Area */
    .pos-header { padding: 20px 25px; border-bottom: 1px solid var(--border-subtle); background: var(--bg-surface); z-index: 10;}
    .search-wrapper { position: relative; display: flex; align-items: center; }
    .search-wrapper i { position: absolute; left: 18px; color: var(--text-muted); font-size: 20px;}
    .search-wrapper input { width: 100%; background: var(--bg-base); border: 2px solid transparent; padding: 16px 20px 16px 50px; border-radius: 14px; font-size: 15px; font-weight: 600; color: var(--text-main); outline: none; transition: 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .search-wrapper input:focus { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);}

    /* Product Grid */
    .product-grid { padding: 25px; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; overflow-y: auto; flex: 1; align-content: start; background: rgba(0,0,0,0.01);}
    html.dark .product-grid { background: rgba(255,255,255,0.01); }

    .product-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); position: relative; user-select: none; display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;}
    .product-card:hover { border-color: #3b82f6; transform: translateY(-5px); box-shadow: 0 12px 24px rgba(59, 130, 246, 0.12);}
    .product-card:active { transform: translateY(0); box-shadow: none;}
    
    .p-sku { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 800; background: var(--bg-base); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 12px; border: 1px solid var(--border-subtle);}
    .p-name { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 15px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    
    .p-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;}
    .p-price { font-size: 16px; font-weight: 900; color: #10b981; }
    .p-price small { font-size: 10px; font-weight: 600; color: var(--text-muted); display: block; margin-top: 2px;}
    
    .p-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 12px; padding: 6px 12px; border-radius: 8px; font-weight: 900; border: 1px solid rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 4px;}
    .p-stock i { font-size: 14px;}
    .p-stock.low { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2);}

    /* =========================================================
       RIGHT PANEL: CART & CHECKOUT (RECEIPT STYLE)
       ========================================================= */
    .pos-cart { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative;}
    
    .cart-header { padding: 20px 25px; border-bottom: 2px dashed var(--border-subtle); font-weight: 900; font-size: 18px; color: var(--text-main); display: flex; justify-content: space-between; align-items: center;}
    .btn-clear { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 5px;}
    .btn-clear:hover { background: #ef4444; color: #fff;}
    
    /* Cart Items Area */
    .cart-items { flex: 1; overflow-y: auto; padding: 20px 25px; display: flex; flex-direction: column; gap: 15px;}
    .cart-empty { text-align: center; color: var(--text-muted); margin-top: 60px; font-size: 14px; font-weight: 600;}
    
    .cart-item { display: flex; flex-direction: column; gap: 12px; background: var(--bg-base); padding: 16px; border-radius: 14px; border: 1px solid var(--border-subtle); animation: slideIn 0.3s ease;}
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .c-top { display: flex; justify-content: space-between; align-items: flex-start;}
    .c-name { font-size: 14px; font-weight: 800; color: var(--text-main); line-height: 1.3;}
    .c-subtotal { font-size: 15px; color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace;}
    
    .c-bottom { display: flex; justify-content: space-between; align-items: center;}
    
    /* Sleek Price Input */
    .price-editor { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; overflow: hidden; focus-within: border-color: #3b82f6;}
    .price-editor span { background: rgba(0,0,0,0.03); padding: 6px 10px; font-size: 12px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    html.dark .price-editor span { background: rgba(255,255,255,0.05); }
    .price-editor input { width: 90px; border: none; padding: 6px 10px; font-size: 13px; font-weight: 700; color: #10b981; outline: none; background: transparent;}

    /* Ergonomic QTY Controls */
    .qty-controls { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; overflow: hidden;}
    .btn-qty { border: none; background: transparent; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; cursor: pointer; color: var(--text-main); transition: 0.2s;}
    .btn-qty:hover { background: rgba(59, 130, 246, 0.1); color: #3b82f6;}
    .qty-val { font-size: 14px; font-weight: 900; width: 36px; text-align: center; border-left: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle);}
    
    .btn-del { color: var(--text-muted); border: none; background: transparent; cursor: pointer; font-size: 22px; transition: 0.2s;}
    .btn-del:hover { color: #ef4444; transform: scale(1.1);}

    /* Checkout Area (Bottom Fix) */
    .checkout-area { padding: 25px; border-top: 2px dashed var(--border-subtle); background: var(--bg-base); border-radius: 0 0 20px 20px;}
    
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 14px 15px; border-radius: 10px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: 0.2s;}
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
    
    .total-row { display: flex; justify-content: space-between; align-items: flex-end; margin: 25px 0; background: rgba(16, 185, 129, 0.05); padding: 15px 20px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .total-label { font-size: 15px; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin-bottom: 4px;}
    .total-amount { font-size: 32px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1;}

    .btn-pay { width: 100%; background: #3b82f6; color: #fff; border: none; padding: 20px; border-radius: 14px; font-size: 18px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 12px; transition: 0.3s; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);}
    .btn-pay:hover { background: #2563eb; transform: translateY(-2px);}
    .btn-pay:disabled { background: var(--bg-base); border: 2px dashed var(--border-subtle); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none;}
</style>

<div class="pos-page-header">
    <div class="pos-page-title">
        <div>
            <h1>Point of Sale (POS)</h1>
            <p>Kasir Cepat Pabrik Noric Exhaust</p>
        </div>
    </div>
</div>

<div class="pos-container">
    
    <div class="pos-products">
        <div class="pos-header">
            <div class="search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchItem" placeholder="Ketik nama produk atau scan barcode SKU..." onkeyup="filterProducts()" autocomplete="off">
            </div>
        </div>
        
        <div class="product-grid" id="productList">
            <?php foreach($products as $p): ?>
                <?php $isLow = $p['physical_stock'] <= 5 ? 'low' : ''; ?>
                <div class="product-card prod-item" 
                     onclick="addToCart('<?= esc($p['sku']) ?>', '<?= esc(addslashes($p['item_name'])) ?>', <?= $p['hpp'] ?>, <?= $p['physical_stock'] ?>)">
                    
                    <div>
                        <span class="p-sku prod-sku"><i class="ph ph-barcode"></i> <?= esc($p['sku']) ?></span>
                        <div class="p-name prod-name"><?= esc($p['item_name']) ?></div>
                    </div>
                    
                    <div class="p-footer">
                        <div class="p-price">
                            Rp <?= number_format($p['hpp'], 0, ',', '.') ?>
                            <small>Harga Dasar (HPP)</small>
                        </div>
                        <div class="p-stock <?= $isLow ?>">
                            <i class="ph ph-package"></i> <?= $p['physical_stock'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-cart">
        <div class="cart-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="ph ph-receipt" style="font-size:24px; color:#3b82f6;"></i> Struk Tagihan
            </div>
            <button onclick="clearCart()" class="btn-clear" title="Hapus semua pesanan">
                <i class="ph ph-trash"></i> Bersihkan
            </button>
        </div>
        
        <div class="cart-items" id="cartContainer">
            <div class="cart-empty">
                <i class="ph ph-shopping-cart-simple" style="font-size: 64px; color: var(--border-subtle); display:block; margin-bottom:15px;"></i>
                Belum ada barang dipilih.<br>
                <span style="font-size: 12px; font-weight: 500;">Klik produk di sebelah kiri untuk menambahkan.</span>
            </div>
        </div>

        <div class="checkout-area">
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 2;">
                    <label>Pelanggan (Opsional)</label>
                    <input type="text" id="custName" class="form-control" placeholder="Nama Reseller / Umum" autocomplete="off">
                </div>
                <div class="form-group" style="flex: 1.5;">
                    <label>Metode Bayar</label>
                    <select id="payMethod" class="form-control">
                        <option value="Cash">💵 Tunai</option>
                        <option value="Transfer BCA">🏦 Trf BCA</option>
                        <option value="Transfer BRI">🏦 Trf BRI</option>
                        <option value="QRIS">📱 QRIS</option>
                    </select>
                </div>
            </div>

            <div class="total-row">
                <div>
                    <div class="total-label">Total Pembayaran</div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;" id="itemCountDisplay">0 Barang</div>
                </div>
                <div class="total-amount" id="totalDisplay">Rp 0</div>
            </div>

            <button class="btn-pay" id="btnCheckout" onclick="processCheckout()" disabled>
                <i class="ph ph-printer"></i> Bayar & Cetak
            </button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = [];

    // Focus on search input when page loads (ideal for barcode scanners)
    window.onload = function() {
        document.getElementById('searchItem').focus();
    };

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

    function addToCart(sku, name, defaultPrice, maxStock) {
        if(maxStock <= 0) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Kosong!', showConfirmButton: false, timer: 2000 });
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
            cart.push({ sku: sku, name: name, price: defaultPrice, qty: 1, maxStock: maxStock });
        }
        renderCart();
    }

    function updateQty(sku, change) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let newQty = item.qty + change;
            if(newQty > item.maxStock) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Stok tidak mencukupi!', showConfirmButton: false, timer: 2000 });
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

    function updatePrice(sku, newPrice) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let parsed = parseFloat(newPrice);
            item.price = isNaN(parsed) || parsed < 0 ? 0 : parsed;
        }
        renderCart(false); // Render without rebuilding HTML so input doesn't lose focus
    }

    function removeItem(sku) {
        cart = cart.filter(item => item.sku !== sku);
        renderCart();
    }

    function clearCart() {
        if(cart.length === 0) return;
        Swal.fire({
            title: 'Hapus Semua?',
            text: "Keranjang akan dikosongkan.",
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

    function renderCart(rebuildHTML = true) {
        const container = document.getElementById('cartContainer');
        const btnCheckout = document.getElementById('btnCheckout');
        const totalDisplay = document.getElementById('totalDisplay');
        const itemCountDisplay = document.getElementById('itemCountDisplay');
        
        if(cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <i class="ph ph-shopping-cart-simple" style="font-size: 64px; color: var(--border-subtle); display:block; margin-bottom:15px;"></i>
                    Belum ada barang dipilih.<br>
                    <span style="font-size: 12px; font-weight: 500;">Klik produk di sebelah kiri untuk menambahkan.</span>
                </div>`;
            totalDisplay.innerText = 'Rp 0';
            itemCountDisplay.innerText = '0 Barang';
            btnCheckout.disabled = true;
            btnCheckout.innerHTML = '<i class="ph ph-printer"></i> Bayar & Cetak';
            return;
        }

        let total = 0;
        let totalItems = 0;

        if(rebuildHTML) container.innerHTML = '';

        cart.forEach(item => {
            let subtotal = item.qty * item.price;
            total += subtotal;
            totalItems += item.qty;

            if(rebuildHTML) {
                container.innerHTML += `
                <div class="cart-item">
                    <div class="c-top">
                        <div style="flex:1; padding-right:15px;">
                            <div class="c-name">${item.name}</div>
                            <div style="font-size:10px; color:var(--text-muted); font-family:monospace; margin-bottom:8px;">${item.sku}</div>
                            
                            <div class="price-editor">
                                <span>Rp</span>
                                <input type="number" value="${item.price}" onchange="updatePrice('${item.sku}', this.value)" min="0">
                            </div>
                        </div>
                        <button class="btn-del" onclick="removeItem('${item.sku}')" title="Hapus Item"><i class="ph ph-x-circle"></i></button>
                    </div>
                    
                    <div class="c-bottom">
                        <div class="qty-controls">
                            <button class="btn-qty" onclick="updateQty('${item.sku}', -1)">-</button>
                            <div class="qty-val">${item.qty}</div>
                            <button class="btn-qty" onclick="updateQty('${item.sku}', 1)">+</button>
                        </div>
                        <div class="c-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                    </div>
                </div>`;
            } else {
                // If not rebuilding HTML, just update the subtotal displays
                // (requires assigning IDs to the subtotal divs if we want to be fully dynamic without rebuild,
                // but since this is a simple POS, rebuilding HTML is usually fast enough. 
                // We keep it as is, but recalculate the main total).
            }
        });

        // Re-render HTML fully if false was passed but we need to update subtotals.
        // Actually, to make the price input work perfectly without losing focus:
        if(!rebuildHTML) {
             let html = '';
             cart.forEach(item => {
                 let subtotal = item.qty * item.price;
                 html += `
                <div class="cart-item">
                    <div class="c-top">
                        <div style="flex:1; padding-right:15px;">
                            <div class="c-name">${item.name}</div>
                            <div style="font-size:10px; color:var(--text-muted); font-family:monospace; margin-bottom:8px;">${item.sku}</div>
                            
                            <div class="price-editor">
                                <span>Rp</span>
                                <input type="number" value="${item.price}" onchange="updatePrice('${item.sku}', this.value)" min="0" onblur="renderCart(true)">
                            </div>
                        </div>
                        <button class="btn-del" onclick="removeItem('${item.sku}')" title="Hapus Item"><i class="ph ph-x-circle"></i></button>
                    </div>
                    
                    <div class="c-bottom">
                        <div class="qty-controls">
                            <button class="btn-qty" onclick="updateQty('${item.sku}', -1)">-</button>
                            <div class="qty-val">${item.qty}</div>
                            <button class="btn-qty" onclick="updateQty('${item.sku}', 1)">+</button>
                        </div>
                        <div class="c-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                    </div>
                </div>`;
             });
             container.innerHTML = html;
        }

        totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
        itemCountDisplay.innerText = `${totalItems} Barang`;
        btnCheckout.disabled = false;
        btnCheckout.innerHTML = `Bayar Rp ${total.toLocaleString('id-ID')} <i class="ph ph-arrow-right"></i>`;
    }

    function processCheckout() {
        if(cart.length === 0) return;

        const isDark = document.documentElement.classList.contains('dark');
        let custName = document.getElementById('custName').value || 'Umum';
        let payMethod = document.getElementById('payMethod').value;
        let grandTotal = document.getElementById('totalDisplay').innerText;

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `Terima pembayaran sebesar <b>${grandTotal}</b> via <b>${payMethod}</b> dari <b>${custName}</b>?<br><br><span style="font-size:12px; color:#ef4444;">*Stok Gudang dan Shopee akan dipotong secara otomatis.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ph ph-check-circle"></i> Ya, Proses',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Wajib ditambahkan agar lolos validasi isAJAX() CI4
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Terjadi Kesalahan Jaringan: ${error}`);
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
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location.reload(); // Reload untuk mereset kasir & menarik sisa stok terbaru
                    });
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>