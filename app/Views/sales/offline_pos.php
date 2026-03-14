<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. POS CORE LAYOUT (FULLSCREEN APP FEEL)
       ========================================================= */
    .workspace { padding: 15px 20px 0 20px !important; }
    .header { display: none !important; } 
    .saas-footer { display: none !important; } 
    
    .pos-wrapper {
        display: grid;
        grid-template-columns: 1fr 450px; 
        gap: 20px;
        height: calc(100vh - 30px); 
        min-height: 650px;
    }
    @media (max-width: 1024px) { 
        .pos-wrapper { grid-template-columns: 1fr; height: auto; display: flex; flex-direction: column;} 
        .pos-sidebar { height: 600px; }
    }

    /* =========================================================
       2. LEFT PANEL: CATALOG & SEARCH
       ========================================================= */
    .pos-main { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }

    .pos-search-bar { padding: 25px 30px; background: rgba(0,0,0,0.01); border-bottom: 2px dashed var(--border-subtle); z-index: 10; display: flex; gap: 20px; align-items: center; }
    html.dark .pos-search-bar { background: rgba(255,255,255,0.01); }

    .pos-title { font-size: 24px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; white-space: nowrap; letter-spacing: -0.5px;}
    .pos-title i { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px; border-radius: 12px; font-size: 24px;}
    
    .search-input-group { position: relative; flex: 1; }
    .search-input-group i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 20px;}
    .search-input-group input { width: 100%; background: var(--bg-base); border: 2px solid transparent; padding: 16px 25px 16px 50px; border-radius: 16px; font-size: 15px; font-weight: 600; color: var(--text-main); outline: none; transition: 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .search-input-group input:focus { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);}

    .btn-history { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); padding: 14px 20px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; text-decoration: none;}
    .btn-history:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);}

    /* Product Grid */
    .pos-grid { padding: 30px; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; overflow-y: auto; align-content: start; flex: 1; background: var(--bg-base); }
    .pos-grid::-webkit-scrollbar { width: 8px; }
    .pos-grid::-webkit-scrollbar-track { background: transparent; }
    .pos-grid::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

    /* Product Card */
    .product-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; justify-content: space-between; min-height: 170px; user-select: none; box-shadow: 0 4px 15px rgba(0,0,0,0.02); position: relative; overflow: hidden;}
    .product-card:hover { border-color: #3b82f6; transform: translateY(-4px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.15);}
    .product-card:active { transform: translateY(0) scale(0.98); }
    
    .p-sku { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 900; background: var(--bg-base); padding: 6px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 15px; border: 1px dashed var(--border-subtle); letter-spacing: -0.5px;}
    .p-name { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    
    .p-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;}
    /* PERBAIKAN WARNA HARGA JUAL */
    .p-price { font-size: 18px; font-weight: 900; color: #3b82f6; font-family: 'Space Mono', monospace; letter-spacing: -1px;}
    .p-price span { font-size: 10px; font-weight: 800; color: var(--text-muted); display: block; font-family: 'Plus Jakarta Sans', sans-serif; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .p-stock { background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 13px; font-weight: 900; padding: 6px 12px; border-radius: 10px; display: flex; align-items: center; gap: 6px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .p-stock.low { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); animation: pulseDanger 2s infinite;}

    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    /* =========================================================
       3. RIGHT PANEL: CART & PAYMENT
       ========================================================= */
    .pos-sidebar { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; display: flex; flex-direction: column; box-shadow: -5px 0 30px rgba(0,0,0,0.03); overflow: hidden; z-index: 10;}
    
    .sidebar-header { padding: 25px 30px; border-bottom: 2px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);}
    .sidebar-title { font-size: 18px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;}
    .sidebar-title i { color: #8b5cf6; font-size: 20px; background: rgba(139, 92, 246, 0.1); padding: 8px; border-radius: 10px;}
    
    .btn-clear { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 6px;}
    .btn-clear:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    .cart-items { flex: 1; overflow-y: auto; padding: 20px 25px; display: flex; flex-direction: column; gap: 15px; background: var(--bg-base);}
    .cart-items::-webkit-scrollbar { width: 5px; }
    .cart-items::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

    .cart-empty { text-align: center; color: var(--text-muted); margin-top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 10px;}
    
    .cart-item { background: var(--bg-surface); padding: 20px; border-radius: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .cart-item-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px;}
    .c-name { font-size: 14px; font-weight: 900; color: var(--text-main); line-height: 1.4;}
    .btn-del { color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid transparent; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;}
    .btn-del:hover { background: #ef4444; color: #fff; transform: scale(1.1);}

    .cart-item-body { display: flex; justify-content: space-between; align-items: center; }
    .qty-controls { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 4px;}
    .btn-qty { border: none; background: var(--bg-surface); width: 30px; height: 30px; border-radius: 8px; font-weight: 900; font-size: 14px; cursor: pointer; color: var(--text-main); transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);}
    .btn-qty:hover { background: #3b82f6; color: #fff;}
    .qty-val { font-size: 15px; font-weight: 900; width: 40px; text-align: center; font-family: 'Space Mono', monospace; color: var(--text-main);}
    
    .cart-pricing { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .c-subtotal { font-size: 18px; color: #3b82f6; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}
    
    .price-editor { display: flex; align-items: center; background: var(--bg-base); border: 1px solid transparent; border-radius: 8px; padding: 4px 8px; transition: 0.3s;}
    .price-editor:focus-within { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
    .price-editor span, .price-editor i { font-size: 11px; font-weight: 800; color: var(--text-muted); }
    .price-editor input { width: 75px; border: none; padding: 2px 4px; font-size: 13px; font-weight: 800; color: var(--text-main); text-align: right; outline: none; background: transparent; font-family: 'Space Mono', monospace;}

    /* CHECKOUT FOOTER */
    .checkout-footer { padding: 25px; background: var(--bg-surface); border-top: 1px solid var(--border-subtle); box-shadow: 0 -10px 20px rgba(0,0,0,0.03);}
    
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 16px; border-radius: 12px; font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: 0.3s; font-family: inherit;}
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);}
    select.form-control { appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2371717a%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;}
    
    /* Area Uang Diterima & Kembalian */
    .cash-calculator { background: rgba(16, 185, 129, 0.05); border: 2px dashed rgba(16, 185, 129, 0.3); border-radius: 16px; padding: 18px; margin-bottom: 20px; display: none; transition: 0.3s;}
    .cash-calculator.show { display: block; animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    
    .change-display { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 14px; font-weight: 800; color: var(--text-muted); padding-top: 15px; border-top: 1px dashed rgba(16, 185, 129, 0.3);}
    .change-amount { font-size: 24px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -1px;}
    .text-green { color: #10b981; }
    .text-red { color: #ef4444; }

    .total-box { display: flex; justify-content: space-between; align-items: flex-end; margin: 15px 0 25px 0; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1)); padding: 25px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .total-label { font-size: 13px; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px;}
    .total-amount { font-size: 38px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1.5px;}

    .btn-pay { width: 100%; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 22px; border-radius: 16px; font-size: 18px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);}
    .btn-pay:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.6);}
    .btn-pay:disabled { background: var(--bg-base); border: 2px dashed var(--border-subtle); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none;}
</style>

<div class="pos-wrapper">
    
    <div class="pos-main">
        <div class="pos-search-bar">
            <div class="pos-title">
                <i class="ph-fill ph-storefront"></i> Point of Sale
            </div>
            <div class="search-input-group">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" id="searchItem" placeholder="Ketik nama produk atau scan barcode SKU..." onkeyup="filterProducts()" autocomplete="off">
            </div>
            <a href="<?= base_url('/sales/offline_history') ?>" class="btn-history">
                <i class="ph-bold ph-clock-counter-clockwise" style="font-size: 20px;"></i> Riwayat Transaksi
            </a>
        </div>
        
        <div class="pos-grid" id="productList">
            <?php foreach($products as $p): ?>
                <?php 
                    $isLow = $p['physical_stock'] <= 5 ? 'low' : ''; 
                    // PERBAIKAN: Memanggil Harga Jual ($p['sell_price'] / $p['price']) bukan HPP
                    // NOTE: Sesuaikan 'price' dengan nama kolom harga jual Anda di database (misal 'sell_price' atau 'retail_price')
                    $sellPrice = isset($p['price']) ? $p['price'] : (isset($p['sell_price']) ? $p['sell_price'] : $p['hpp']); 
                ?>
                
                <div class="product-card prod-item" onclick="addToCart('<?= esc($p['sku']) ?>', '<?= esc(addslashes($p['item_name'])) ?>', <?= $sellPrice ?>, <?= $p['physical_stock'] ?>)">
                    <div>
                        <span class="p-sku prod-sku"><i class="ph-bold ph-barcode"></i> <?= esc($p['sku']) ?></span>
                        <div class="p-name prod-name"><?= esc($p['item_name']) ?></div>
                    </div>
                    <div class="p-footer">
                        <div class="p-price">Rp <?= number_format($sellPrice, 0, ',', '.') ?></div>
                        <div class="p-stock <?= $isLow ?>" title="Stok Gudang"><i class="ph-fill ph-package"></i> <?= $p['physical_stock'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title"><i class="ph-fill ph-receipt"></i> Struk Tagihan</div>
            <button onclick="clearCart()" class="btn-clear"><i class="ph-bold ph-trash"></i> Bersihkan</button>
        </div>
        
        <div class="cart-items" id="cartContainer">
            <div class="cart-empty">
                <div style="background: var(--bg-surface); padding: 25px; border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 15px; border: 1px dashed var(--border-subtle);">
                    <i class="ph-fill ph-shopping-cart-simple" style="font-size: 56px; color: var(--border-subtle);"></i>
                </div>
                Keranjang Masih Kosong
                <span style="font-size: 12px; font-weight: 500; opacity: 0.7; margin-top: 5px;">Klik produk atau gunakan scanner barcode.</span>
            </div>
        </div>

        <div class="checkout-footer">
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1.5;">
                    <label>Nama Pelanggan</label>
                    <input type="text" id="custName" class="form-control" placeholder="Umum / Walk-in" autocomplete="off">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Metode Pembayaran</label>
                    <select id="payMethod" class="form-control" onchange="toggleCashCalculator()">
                        <option value="Cash">💵 Tunai (Cash)</option>
                        <option value="Transfer BCA">🏦 Transfer BCA</option>
                        <option value="Transfer BRI">🏦 Transfer BRI</option>
                        <option value="QRIS">📱 QRIS Digital</option>
                    </select>
                </div>
            </div>

            <div class="cash-calculator" id="cashInputArea">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: #10b981; font-size: 12px;">Nominal Uang Diterima (Rp)</label>
                    <input type="text" id="amountPaid" class="form-control" style="font-family: 'Space Mono', monospace; font-size: 24px; font-weight: 900; color: #10b981; border-color: rgba(16, 185, 129, 0.5); padding: 16px;" placeholder="0" onkeyup="formatRupiah(this); calculateChange();" autocomplete="off">
                </div>
                <div class="change-display">
                    <span>Uang Kembalian:</span>
                    <span id="changeDisplay" class="change-amount text-red">Rp 0</span>
                </div>
            </div>

            <div class="total-box">
                <div>
                    <div class="total-label">Grand Total</div>
                    <div style="font-size: 13px; color: var(--text-muted); font-weight: 800; margin-top: 6px;" id="itemCountDisplay">0 Item Terpilih</div>
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
    let globalTotal = 0;
    let globalChange = 0;

    window.onload = function() {
        document.getElementById('searchItem').focus();
        toggleCashCalculator();
    };

    // Auto Focus Scanner Barcode
    document.addEventListener('click', function(e) {
        if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'SELECT' && !e.target.closest('.price-editor') && !e.target.closest('.btn-qty')) {
            document.getElementById('searchItem').focus();
        }
    });

    // Scanner & Filter
    function filterProducts() {
        let input = document.getElementById('searchItem').value.toLowerCase();
        let items = document.getElementsByClassName('prod-item');
        let exactMatchFound = false;
        let matchBtn = null;

        for (let i = 0; i < items.length; i++) {
            let name = items[i].querySelector('.prod-name').innerText.toLowerCase();
            let sku = items[i].querySelector('.prod-sku').innerText.toLowerCase();
            if (name.includes(input) || sku.includes(input)) {
                items[i].style.display = "";
                if(sku === input.trim()) { exactMatchFound = true; matchBtn = items[i]; }
            } else {
                items[i].style.display = "none";
            }
        }
        
        // Auto add jika barcode match sempurna
        if(exactMatchFound && input.length >= 8) { 
            matchBtn.click();
            document.getElementById('searchItem').value = '';
            filterProducts(); 
        }
    }

    const beepSound = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');

    // Cart Logic
    function addToCart(sku, name, defaultPrice, maxStock) {
        if(maxStock <= 0) { 
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Stok produk ini habis!", true);
            else Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Habis!', showConfirmButton: false, timer: 2000 }); 
            return; 
        }
        beepSound.currentTime = 0; beepSound.play().catch(e => {});

        let existing = cart.find(item => item.sku === sku);
        if (existing) {
            if(existing.qty < maxStock) existing.qty += 1;
            else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Melebihi batas stok gudang!", true);
            }
        } else {
            cart.unshift({ sku: sku, name: name, price: defaultPrice, qty: 1, maxStock: maxStock });
        }
        renderCart();
    }

    function updateQty(sku, change) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let newQty = item.qty + change;
            if(newQty > item.maxStock) { 
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Melebihi batas stok gudang!", true);
                return; 
            }
            if(newQty > 0) item.qty = newQty;
            else cart = cart.filter(i => i.sku !== sku);
            renderCart();
        }
    }

    function updatePrice(sku, newPrice) {
        let item = cart.find(i => i.sku === sku);
        if(item) {
            let parsed = parseFloat(newPrice);
            item.price = isNaN(parsed) || parsed < 0 ? 0 : parsed;
        }
        renderCart(false); 
    }

    function removeItem(sku) { cart = cart.filter(item => item.sku !== sku); renderCart(); }

    function clearCart() {
        if(cart.length === 0) return;
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({ 
            title: 'Hapus Semua?', 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: 'Ya, Bersihkan',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b'
        }).then((result) => { if (result.isConfirmed) { cart = []; renderCart(); } });
    }

    // Render HTML Keranjang
    function renderCart(rebuildHTML = true) {
        const container = document.getElementById('cartContainer');
        const btnCheckout = document.getElementById('btnCheckout');
        const totalDisplay = document.getElementById('totalDisplay');
        const itemCountDisplay = document.getElementById('itemCountDisplay');
        
        if(cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <div style="background: var(--bg-surface); padding: 25px; border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 15px; border: 1px dashed var(--border-subtle);">
                        <i class="ph-fill ph-shopping-cart-simple" style="font-size: 56px; color: var(--border-subtle);"></i>
                    </div>
                    Keranjang Masih Kosong
                    <span style="font-size: 12px; font-weight: 500; opacity: 0.7; margin-top: 5px;">Klik produk atau gunakan scanner barcode.</span>
                </div>`;
            totalDisplay.innerText = 'Rp 0';
            itemCountDisplay.innerText = '0 Item Terpilih';
            btnCheckout.disabled = true;
            globalTotal = 0;
            toggleCashCalculator(); 
            return;
        }

        globalTotal = 0; let totalItems = 0;

        if(rebuildHTML) {
            let html = '';
            cart.forEach(item => {
                let subtotal = item.qty * item.price;
                html += `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <div class="c-name">${item.name}</div>
                        <button class="btn-del" onclick="removeItem('${item.sku}')" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                    </div>
                    <div class="cart-item-body">
                        <div class="qty-controls">
                            <button class="btn-qty" onclick="updateQty('${item.sku}', -1)"><i class="ph-bold ph-minus"></i></button>
                            <div class="qty-val">${item.qty}</div>
                            <button class="btn-qty" onclick="updateQty('${item.sku}', 1)"><i class="ph-bold ph-plus"></i></button>
                        </div>
                        <div class="cart-pricing">
                            <div class="c-subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                            <div class="price-editor" title="Edit Harga Satuan (Diskon Nego)">
                                <span>@ Rp</span>
                                <input type="number" value="${item.price}" onchange="updatePrice('${item.sku}', this.value)" min="0" onblur="renderCart(true)">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        cart.forEach(item => {
            globalTotal += (item.qty * item.price);
            totalItems += item.qty;
        });

        totalDisplay.innerText = 'Rp ' + globalTotal.toLocaleString('id-ID');
        itemCountDisplay.innerText = `${totalItems} Item Terpilih`;
        
        if(rebuildHTML) container.scrollTop = 0;
        calculateChange(); 
    }

    // --- LOGIKA KEMBALIAN ---
    function formatRupiah(angka) {
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function toggleCashCalculator() {
        const method = document.getElementById('payMethod').value;
        const area = document.getElementById('cashInputArea');
        
        if(method === 'Cash' && cart.length > 0) {
            area.classList.add('show');
            setTimeout(() => { document.getElementById('amountPaid').focus(); }, 100);
        } else {
            area.classList.remove('show');
            document.getElementById('amountPaid').value = '';
        }
        calculateChange();
    }

    function calculateChange() {
        const method = document.getElementById('payMethod').value;
        const btnCheckout = document.getElementById('btnCheckout');
        const changeDisplay = document.getElementById('changeDisplay');
        
        if(cart.length === 0) {
            btnCheckout.disabled = true;
            return;
        }

        if(method === 'Cash') {
            let amountPaidStr = document.getElementById('amountPaid').value.replace(/[^,\d]/g, '');
            let amountPaid = parseInt(amountPaidStr) || 0;
            globalChange = amountPaid - globalTotal;

            if(globalChange < 0) {
                changeDisplay.className = 'change-amount text-red';
                changeDisplay.innerText = 'Kurang: Rp ' + Math.abs(globalChange).toLocaleString('id-ID');
                btnCheckout.disabled = true;
                btnCheckout.innerHTML = `<i class="ph-bold ph-warning-circle"></i> Uang Tidak Cukup`;
            } else {
                changeDisplay.className = 'change-amount text-green';
                changeDisplay.innerText = 'Rp ' + globalChange.toLocaleString('id-ID');
                btnCheckout.disabled = false;
                btnCheckout.innerHTML = `<i class="ph-bold ph-printer"></i> Selesai & Cetak`;
            }
        } else {
            globalChange = 0;
            btnCheckout.disabled = false;
            btnCheckout.innerHTML = `<i class="ph-bold ph-printer"></i> Verifikasi & Cetak`;
        }
    }

    // --- CHECKOUT FINAL ---
    function processCheckout() {
        if(cart.length === 0) return;

        let custName = document.getElementById('custName').value || 'Pelanggan Umum';
        let payMethod = document.getElementById('payMethod').value;
        let amountPaidStr = document.getElementById('amountPaid').value;

        const isDark = document.documentElement.classList.contains('dark');

        let confirmationHtml = `Terima tagihan sebesar <b style="font-family:monospace; font-size:24px; color:#3b82f6; display:block; margin: 10px 0;">Rp ${globalTotal.toLocaleString('id-ID')}</b>via <b>${payMethod}</b> dari <b>${custName}</b>?<br><br>`;
        
        if(payMethod === 'Cash') {
            confirmationHtml += `<div style="background: rgba(16, 185, 129, 0.1); border: 2px dashed #10b981; padding: 20px; border-radius: 16px; margin-top: 15px;">
                                 <div style="font-size: 13px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px;">KEMBALIAN PELANGGAN:</div>
                                 <div style="font-size: 32px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; line-height: 1;">Rp ${globalChange.toLocaleString('id-ID')}</div>
                                 </div>`;
        }

        Swal.fire({
            title: 'Proses Pembayaran?',
            html: confirmationHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-check-square-offset"></i> Konfirmasi Transaksi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                let formData = new FormData();
                formData.append('customer_name', custName);
                formData.append('payment_method', payMethod);
                formData.append('amount_paid', amountPaidStr); 
                formData.append('change_amount', globalChange); 
                formData.append('cart', JSON.stringify(cart));
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                try {
                    let response = await fetch('<?= base_url('/sales/process_offline') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });
                    if (!response.ok) throw new Error("Server bermasalah.");
                    return await response.json();
                } catch (error) {
                    Swal.showValidationMessage(`Koneksi Gagal: ${error.message}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    let successText = result.value.message;
                    if(payMethod === 'Cash' && globalChange > 0) {
                        successText = `<div style="background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 12px; border: 1px dashed #ef4444; margin-bottom: 20px;"><b style="font-size: 16px; color: #ef4444; display:block; margin-bottom:4px;">JANGAN LUPA KEMBALIAN</b><span style="font-size: 28px; font-weight: 900; font-family: monospace; color: #ef4444;">Rp ${globalChange.toLocaleString('id-ID')}</span></div>` + successText;
                    }

                    Swal.fire({
                        icon: 'success', 
                        title: 'Transaksi Sukses!', 
                        html: successText, 
                        confirmButtonColor: '#10b981', 
                        confirmButtonText: '<i class="ph-bold ph-arrow-counter-clockwise"></i> Layani Transaksi Baru',
                        background: isDark ? '#18181b' : '#ffffff', 
                        color: isDark ? '#f4f4f5' : '#09090b',
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        title: 'Transaksi Gagal', 
                        text: result.value.message, 
                        icon: 'error',
                        background: isDark ? '#18181b' : '#ffffff', 
                        color: isDark ? '#f4f4f5' : '#09090b',
                    });
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>