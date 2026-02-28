<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- POS LAYOUT --- */
    .pos-container { display: grid; grid-template-columns: 1fr 380px; gap: 20px; height: calc(100vh - 120px); min-height: 600px;}
    @media (max-width: 1024px) { .pos-container { grid-template-columns: 1fr; height: auto;} }

    /* LEFT: PRODUCT CATALOG */
    .pos-products { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-card);}
    .pos-header { padding: 20px; border-bottom: 1px solid var(--border-subtle); display: flex; gap: 15px; align-items: center; background: rgba(0,0,0,0.01);}
    
    .search-box { flex: 1; position: relative; }
    .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;}
    .search-box input { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px 12px 40px; border-radius: 10px; font-size: 14px; outline: none; font-weight: 600; color: var(--text-main);}
    .search-box input:focus { border-color: var(--accent-main); }

    .product-grid { padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; overflow-y: auto; flex: 1;}
    .product-card { background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 15px; cursor: pointer; transition: 0.2s; position: relative; user-select: none;}
    .product-card:hover { border-color: var(--accent-main); transform: translateY(-3px); box-shadow: 0 8px 15px var(--accent-light);}
    .product-card:active { transform: translateY(0);}
    
    .p-sku { font-size: 10px; color: var(--text-muted); font-family: monospace; font-weight: 800; margin-bottom: 5px; display: block;}
    .p-name { font-size: 13px; font-weight: 800; color: var(--text-main); margin-bottom: 10px; line-height: 1.3;}
    .p-price { font-size: 15px; font-weight: 900; color: #10b981; }
    .p-stock { position: absolute; top: 15px; right: 15px; background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 800;}

    /* RIGHT: CART & CHECKOUT */
    .pos-cart { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; display: flex; flex-direction: column; box-shadow: var(--shadow-card);}
    .cart-header { padding: 20px; border-bottom: 1px dashed var(--border-subtle); font-weight: 800; font-size: 16px; color: var(--text-main); display: flex; justify-content: space-between; align-items: center;}
    
    .cart-items { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px;}
    .cart-empty { text-align: center; color: var(--text-muted); margin-top: 50px; font-size: 13px;}
    .cart-item { display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 12px; border-radius: 10px; border: 1px solid var(--border-subtle);}
    .c-name { font-size: 12px; font-weight: 700; color: var(--text-main); line-height: 1.2; margin-bottom: 5px;}
    .c-price { font-size: 13px; color: #10b981; font-weight: 800; font-family: monospace;}
    
    .qty-controls { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 6px; padding: 2px;}
    .btn-qty { border: none; background: transparent; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: 800; cursor: pointer; color: var(--text-main); border-radius: 4px;}
    .btn-qty:hover { background: var(--border-subtle); }
    .btn-del { color: #ef4444; border: none; background: transparent; cursor: pointer; font-size: 18px; padding: 5px;}

    /* CHECKOUT AREA */
    .checkout-area { padding: 20px; border-top: 1px dashed var(--border-subtle); background: rgba(0,0,0,0.01);}
    html.dark .checkout-area { background: rgba(255,255,255,0.02);}
    
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 5px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 10px 15px; border-radius: 8px; font-size: 13px; font-weight: 600; outline: none; color: var(--text-main);}
    
    .total-row { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; }
    .total-label { font-size: 16px; font-weight: 800; color: var(--text-muted); }
    .total-amount { font-size: 28px; font-weight: 900; color: var(--accent-main); font-family: 'Space Mono', monospace;}

    .btn-pay { width: 100%; background: var(--accent-main); color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: 0.2s; box-shadow: 0 4px 15px var(--accent-light);}
    .btn-pay:hover { filter: brightness(1.1); transform: translateY(-2px);}
    .btn-pay:disabled { background: var(--border-subtle); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none;}
</style>

<div class="pos-container">
    
    <div class="pos-products">
        <div class="pos-header">
            <div class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchItem" placeholder="Cari Nama Barang atau SKU Gudang..." onkeyup="filterProducts()">
            </div>
        </div>
        <div class="product-grid" id="productList">
            <?php foreach($products as $p): ?>
                <div class="product-card prod-item" 
                     onclick="addToCart('<?= esc($p['sku']) ?>', '<?= esc($p['item_name']) ?>', <?= $p['hpp'] ?>, <?= $p['physical_stock'] ?>)">
                    <span class="p-stock"><?= $p['physical_stock'] ?></span>
                    <span class="p-sku prod-sku"><?= esc($p['sku']) ?></span>
                    <div class="p-name prod-name"><?= esc($p['item_name']) ?></div>
                    <div class="p-price">Rp <?= number_format($p['hpp'], 0, ',', '.') ?></div>
                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">*Harga HPP (Dapat diedit di keranjang)</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-cart">
        <div class="cart-header">
            <span><i class="ph ph-shopping-cart-simple"></i> Struk Belanja</span>
            <button onclick="clearCart()" style="background:none; border:none; color:#ef4444; font-size:12px; font-weight:700; cursor:pointer;"><i class="ph ph-trash"></i> Kosongkan</button>
        </div>
        
        <div class="cart-items" id="cartContainer">
            <div class="cart-empty"><i class="ph ph-basket" style="font-size: 40px;"></i><br>Keranjang masih kosong</div>
        </div>

        <div class="checkout-area">
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 2;">
                    <label>Nama Pelanggan (Opsional)</label>
                    <input type="text" id="custName" class="form-control" placeholder="Umum / Reseller A">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Pembayaran</label>
                    <select id="payMethod" class="form-control">
                        <option value="Cash">Tunai (Cash)</option>
                        <option value="Transfer BCA">Transfer Bank</option>
                        <option value="QRIS">QRIS / E-Wallet</option>
                    </select>
                </div>
            </div>

            <div class="total-row">
                <div class="total-label">Total Bayar</div>
                <div class="total-amount" id="totalDisplay">Rp 0</div>
            </div>

            <button class="btn-pay" id="btnCheckout" onclick="processCheckout()" disabled>
                <i class="ph ph-check-circle"></i> Proses Pembayaran
            </button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = [];

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
        // Cek apakah sudah ada di keranjang
        let existing = cart.find(item => item.sku === sku);
        if (existing) {
            if(existing.qty < maxStock) {
                existing.qty += 1;
            } else {
                Swal.fire('Stok Habis', `Stok ${name} hanya sisa ${maxStock} di gudang.`, 'warning');
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
                Swal.fire('Ups!', 'Melebihi stok gudang.', 'warning');
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
        if(item) item.price = parseFloat(newPrice) || 0;
        renderCart(false); // Render tanpa update fokus input
    }

    function removeItem(sku) {
        cart = cart.filter(item => item.sku !== sku);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart(rebuildHTML = true) {
        const container = document.getElementById('cartContainer');
        const btnCheckout = document.getElementById('btnCheckout');
        const totalDisplay = document.getElementById('totalDisplay');
        
        if(cart.length === 0) {
            container.innerHTML = '<div class="cart-empty"><i class="ph ph-basket" style="font-size: 40px; opacity:0.5;"></i><br>Pilih barang di samping</div>';
            totalDisplay.innerText = 'Rp 0';
            btnCheckout.disabled = true;
            return;
        }

        let total = 0;
        if(rebuildHTML) container.innerHTML = '';

        cart.forEach(item => {
            let subtotal = item.qty * item.price;
            total += subtotal;

            if(rebuildHTML) {
                container.innerHTML += `
                <div class="cart-item">
                    <div style="flex:1;">
                        <div class="c-name">${item.name}</div>
                        <div style="display:flex; align-items:center; gap:5px;">
                            <span style="font-size:11px; color:var(--text-muted);">Rp</span>
                            <input type="number" value="${item.price}" onchange="updatePrice('${item.sku}', this.value)" style="width:80px; padding:2px 5px; font-size:12px; border:1px solid var(--border-subtle); border-radius:4px; outline:none; background:var(--bg-surface); color:var(--text-main);">
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                        <div class="c-price">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="qty-controls">
                                <button class="btn-qty" onclick="updateQty('${item.sku}', -1)">-</button>
                                <span style="font-size:13px; font-weight:800; min-width:20px; text-align:center;">${item.qty}</span>
                                <button class="btn-qty" onclick="updateQty('${item.sku}', 1)">+</button>
                            </div>
                            <button class="btn-del" onclick="removeItem('${item.sku}')"><i class="ph ph-x-circle"></i></button>
                        </div>
                    </div>
                </div>`;
            }
        });

        totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
        btnCheckout.disabled = false;
    }

    function processCheckout() {
        if(cart.length === 0) return;

        const isDark = document.documentElement.classList.contains('dark');
        let custName = document.getElementById('custName').value;
        let payMethod = document.getElementById('payMethod').value;

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: "Sistem akan mengurangi stok gudang dan mengupdate stok Shopee. Lanjutkan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bayar Sekarang',
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
                        window.location.reload(); // Reload untuk mereset kasir
                    });
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>