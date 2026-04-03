<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'POS Kasir Offline' ?></title>
    
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* =========================================================
           1. CORE RESET & VARIABLES
           ========================================================= */
        :root {
            --bg-base: #f4f4f5; 
            --bg-surface: #ffffff; 
            --border-subtle: #e4e4e7;
            --text-main: #09090b; 
            --text-muted: #71717a;
            --brand-green: #10b981;
            --brand-blue: #3b82f6;
            --brand-red: #ef4444;
            --transition-smooth: all 0.2s ease-in-out;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-base: #09090b; 
                --bg-surface: #121214; 
                --border-subtle: rgba(255, 255, 255, 0.08);
                --text-main: #f4f4f5; 
                --text-muted: #a1a1aa;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
        
        body { 
            background-color: var(--bg-base); color: var(--text-main); 
            height: 100vh; overflow: hidden; display: flex;
        }

        /* =========================================================
           2. POS CORE LAYOUT (FULLSCREEN SPLIT)
           ========================================================= */
        .pos-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px; 
            width: 100vw;
            height: 100vh;
        }

        @media (max-width: 1024px) { 
            .pos-wrapper { grid-template-columns: 1fr; overflow-y: auto; height: auto;} 
            body { overflow: auto; }
            .pos-sidebar { height: auto; border-left: none; border-top: 1px solid var(--border-subtle);}
        }

        /* =========================================================
           3. LEFT PANEL: CATALOG & SEARCH
           ========================================================= */
        .pos-main { 
            background: var(--bg-base); display: flex; flex-direction: column; overflow: hidden; border-right: 1px solid var(--border-subtle);
        }

        .pos-header { 
            padding: 12px 20px; background: var(--bg-surface); border-bottom: 1px solid var(--border-subtle); 
            display: flex; gap: 15px; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02); z-index: 10;
        }

        .brand-box { font-size: 16px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; white-space: nowrap; letter-spacing: -0.5px;}
        .brand-box i { background: rgba(16, 185, 129, 0.1); color: var(--brand-green); padding: 6px; border-radius: 8px; font-size: 20px;}
        
        .search-input-group { position: relative; flex: 1; max-width: 500px;}
        .search-input-group i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px;}
        .search-input-group input { width: 100%; background: var(--bg-base); border: 2px solid transparent; padding: 10px 15px 10px 40px; border-radius: 10px; font-size: 13px; font-weight: 600; color: var(--text-main); outline: none; transition: var(--transition-smooth);}
        .search-input-group input:focus { border-color: var(--brand-blue); background: var(--bg-surface); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);}

        .btn-history { background: rgba(59, 130, 246, 0.1); color: var(--brand-blue); border: 1px solid rgba(59, 130, 246, 0.2); padding: 8px 12px; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px; text-decoration: none;}
        .btn-history:hover { background: var(--brand-blue); color: #fff; transform: translateY(-2px);}

        .btn-exit { background: rgba(239, 68, 68, 0.1); color: var(--brand-red); border: 1px solid rgba(239, 68, 68, 0.2); padding: 8px 12px; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px; text-decoration: none;}
        .btn-exit:hover { background: var(--brand-red); color: #fff; transform: translateY(-2px);}

        /* Product Grid */
        .pos-grid { padding: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; overflow-y: auto; align-content: start; flex: 1; }
        .pos-grid::-webkit-scrollbar { width: 6px; }
        .pos-grid::-webkit-scrollbar-track { background: transparent; }
        .pos-grid::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

        /* Product Card */
        .product-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 14px; padding: 12px; cursor: pointer; transition: var(--transition-smooth); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; user-select: none; box-shadow: 0 2px 8px rgba(0,0,0,0.02);}
        .product-card:hover { border-color: var(--brand-blue); transform: translateY(-2px); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.15);}
        .product-card:active { transform: translateY(0) scale(0.98); }
        
        .p-sku { font-size: 10px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 900; background: var(--bg-base); padding: 4px 6px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px; border: 1px dashed var(--border-subtle); letter-spacing: -0.5px;}
        .p-name { font-size: 13px; font-weight: 900; color: var(--text-main); margin-bottom: 10px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
        
        .p-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;}
        .p-price { font-size: 14px; font-weight: 900; color: var(--brand-blue); font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}
        .p-price span { font-size: 9px; font-weight: 800; color: var(--text-muted); display: block; font-family: 'Plus Jakarta Sans', sans-serif; text-transform: uppercase; letter-spacing: 0.5px;}
        
        .p-stock { background: rgba(16, 185, 129, 0.1); color: var(--brand-green); font-size: 11px; font-weight: 900; padding: 4px 8px; border-radius: 6px; display: flex; align-items: center; gap: 4px; border: 1px solid rgba(16, 185, 129, 0.2);}
        .p-stock.low { background: rgba(239, 68, 68, 0.1); color: var(--brand-red); border-color: rgba(239, 68, 68, 0.2); animation: pulseDanger 2s infinite;}

        @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        /* =========================================================
           4. RIGHT PANEL: CART & PAYMENT (COMPACT MODE)
           ========================================================= */
        .pos-sidebar { background: var(--bg-surface); display: flex; flex-direction: column; box-shadow: -5px 0 20px rgba(0,0,0,0.03); z-index: 10;}
        
        .sidebar-header { padding: 15px 20px; border-bottom: 1px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);}
        .sidebar-title { font-size: 14px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px;}
        .sidebar-title i { color: #8b5cf6; font-size: 18px; background: rgba(139, 92, 246, 0.1); padding: 6px; border-radius: 8px;}
        
        .btn-clear { background: transparent; color: var(--brand-red); border: none; font-size: 11px; font-weight: 800; cursor: pointer; text-decoration: underline; opacity: 0.7;}
        .btn-clear:hover { opacity: 1; }

        .cart-items { flex: 1; overflow-y: auto; padding: 12px 15px; display: flex; flex-direction: column; gap: 10px; background: var(--bg-base);}
        .cart-items::-webkit-scrollbar { width: 4px; }
        .cart-items::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }

        .cart-empty { text-align: center; color: var(--text-muted); margin-top: 40%; transform: translateY(-50%); font-size: 13px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 8px;}
        
        /* Item Keranjang */
        .cart-item { background: var(--bg-surface); padding: 12px; border-radius: 12px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
        .cart-item-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 8px;}
        .c-name { font-size: 12px; font-weight: 900; color: var(--text-main); line-height: 1.3;}
        
        .btn-del { color: var(--brand-red); background: rgba(239, 68, 68, 0.1); border: 1px solid transparent; width: 26px; height: 26px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;}
        .btn-del:hover { background: var(--brand-red); color: #fff; transform: scale(1.1);}

        .cart-item-body { display: flex; justify-content: space-between; align-items: center; }
        
        .qty-controls { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 2px;}
        .btn-qty { border: none; background: var(--bg-surface); width: 24px; height: 24px; border-radius: 6px; font-weight: 900; font-size: 12px; cursor: pointer; color: var(--text-main); transition: 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);}
        .btn-qty:hover { background: var(--brand-blue); color: #fff;}
        .qty-val { font-size: 13px; font-weight: 900; width: 30px; text-align: center; font-family: 'Space Mono', monospace; color: var(--text-main);}
        
        .cart-pricing { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .c-subtotal { font-size: 15px; color: var(--brand-blue); font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -0.5px;}

        /* CHECKOUT FOOTER (CASH ONLY) */
        .checkout-footer { padding: 15px 20px; background: var(--bg-surface); border-top: 1px solid var(--border-subtle); box-shadow: 0 -5px 15px rgba(0,0,0,0.03);}
        
        .form-mini { margin-bottom: 12px; }
        .form-mini label { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;}
        .form-mini input { width: 100%; background: var(--bg-base); border: 1.5px solid var(--border-subtle); padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; outline: none; color: var(--text-main); transition: 0.2s; font-family: inherit;}
        .form-mini input:focus { border-color: var(--brand-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);}
        
        .cash-panel { background: rgba(16, 185, 129, 0.05); border: 1.5px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 12px; margin-bottom: 12px;}
        .cash-input-wrap { position: relative; margin-bottom: 8px;}
        .cash-input-wrap span { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 900; color: var(--brand-green); font-size: 16px;}
        .cash-input-wrap input { width: 100%; border: 1.5px solid var(--border-subtle); background: var(--bg-surface); padding: 8px 12px 8px 40px; border-radius: 8px; font-size: 20px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--brand-green); outline: none;}
        
        .change-display { display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px dashed rgba(16, 185, 129, 0.3);}
        .change-display span { font-size: 11px; font-weight: 800; color: var(--text-muted);}
        .change-val { font-size: 18px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--brand-green);}
        .text-red { color: var(--brand-red) !important; }

        .total-panel { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; background: #0f172a; padding: 15px; border-radius: 10px; }
        .total-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;}
        .total-val { font-size: 24px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace; letter-spacing: -1px;}

        .btn-pay { width: 100%; background: linear-gradient(135deg, var(--brand-green), #059669); color: #fff; border: none; padding: 16px; border-radius: 12px; font-size: 15px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: var(--transition-smooth); box-shadow: 0 5px 15px -3px rgba(16, 185, 129, 0.4);}
        .btn-pay:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .btn-pay:disabled { background: var(--bg-base); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none; border: 1px solid var(--border-subtle);}
        
        .history-btn { display: block; text-align: center; margin-top: 12px; font-size: 11px; font-weight: 800; color: var(--brand-blue); text-decoration: none; opacity: 0.6; }
        .history-btn:hover { opacity: 1; text-decoration: underline; }
    </style>
</head>
<body>

<div class="pos-wrapper">
    
    <div class="pos-main">
        <div class="pos-header">
            <div class="brand-box">
                <i class="ph-fill ph-lightning"></i> <?= esc($company['app_name'] ?? 'ERP System') ?>
            </div>
            <div class="search-input-group">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" id="searchItem" placeholder="Cari knalpot atau scan barcode (F2)..." onkeyup="filterProducts()" autocomplete="off">
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?= base_url('/sales/offline_history') ?>" class="btn-history" title="Riwayat Penjualan">
                    <i class="ph-bold ph-clock-counter-clockwise" style="font-size: 16px;"></i> Riwayat
                </a>
                <a href="<?= base_url('/dashboard') ?>" class="btn-exit" title="Kembali ke Dashboard Utama">
                    <i class="ph-bold ph-sign-out" style="font-size: 16px;"></i> Tutup
                </a>
            </div>
        </div>
        
        <div class="pos-grid" id="productList">
            <?php foreach($products as $p): ?>
                <?php 
                    $isLow = $p['physical_stock'] <= 5 ? 'low' : ''; 
                    
                    $retail = isset($p['retail_price']) ? (float)$p['retail_price'] : 0;
                    $wholesale = isset($p['wholesale_price']) ? (float)$p['wholesale_price'] : 0;
                    $hpp = isset($p['hpp']) ? (float)$p['hpp'] : 0;
                    
                    if ($retail > 0) {
                        $sellPrice = $retail;
                    } elseif ($wholesale > 0) {
                        $sellPrice = $wholesale;
                    } else {
                        $sellPrice = $hpp;
                    }
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
            <div class="sidebar-title"><i class="ph-fill ph-shopping-cart-simple"></i> Keranjang</div>
            <button onclick="clearCart()" class="btn-clear"><i class="ph-bold ph-trash"></i> Bersihkan</button>
        </div>
        
        <div class="cart-items" id="cartContainer">
            <div class="cart-empty">
                <div style="background: var(--bg-surface); padding: 20px; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 10px; border: 1px dashed var(--border-subtle);">
                    <i class="ph-fill ph-shopping-cart-simple" style="font-size: 40px; color: var(--border-subtle);"></i>
                </div>
                Keranjang Kosong
                <span style="font-size: 11px; font-weight: 500; opacity: 0.7; margin-top: 5px;">Scan barcode / klik produk.</span>
            </div>
        </div>

        <div class="checkout-footer">
            <div class="form-mini">
                <label>Nama Pelanggan (Opsional)</label>
                <input type="text" id="custName" placeholder="Umum / Walk-in" autocomplete="off">
            </div>

            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;">Pembayaran: <b style="color: var(--brand-green);">CASH TUNAI</b></div>
            
            <div class="cash-panel">
                <div class="cash-input-wrap">
                    <span>Rp</span>
                    <input type="text" inputmode="numeric" id="amountPaid" placeholder="0" onkeyup="formatRupiah(this); calculateChange();" autocomplete="off">
                </div>
                <div class="change-display">
                    <span>Kembalian:</span>
                    <span id="changeDisplay" class="change-val">Rp 0</span>
                </div>
            </div>

            <div class="total-panel">
                <div>
                    <div class="total-label">Total Bayar</div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 800; margin-top: 4px;" id="itemCountDisplay">0 Item</div>
                </div>
                <div class="total-val" id="totalDisplay">Rp 0</div>
            </div>

            <button class="btn-pay" id="btnCheckout" onclick="processCheckout()" disabled>
                <i class="ph-bold ph-check-circle"></i> Selesaikan Transaksi
            </button>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let globalTotal = 0;
    let globalChange = 0;

    window.onload = function() {
        document.getElementById('searchItem').focus();
    };

    // Auto Focus Scanner & F2 shortcut
    document.addEventListener('keydown', function(e) {
        if(e.key === 'F2') {
            e.preventDefault();
            document.getElementById('searchItem').focus();
        }
    });

    document.addEventListener('click', function(e) {
        if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && !e.target.closest('.btn-qty')) {
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
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Habis!', showConfirmButton: false, timer: 1500 }); 
            return; 
        }
        beepSound.currentTime = 0; beepSound.play().catch(e => {});

        let existing = cart.find(item => item.sku === sku);
        if (existing) {
            if(existing.qty < maxStock) existing.qty += 1;
            else Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Gudang Tidak Cukup!', showConfirmButton: false, timer: 1500 }); 
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
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Stok Gudang Tidak Cukup!', showConfirmButton: false, timer: 1500 });
                return; 
            }
            if(newQty > 0) item.qty = newQty;
            else cart = cart.filter(i => i.sku !== sku);
            renderCart();
        }
    }

    function removeItem(sku) { 
        cart = cart.filter(item => item.sku !== sku); 
        renderCart(); 
    }

    function clearCart() {
        if(cart.length === 0) return;
        const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
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
    function renderCart() {
        const container = document.getElementById('cartContainer');
        const btnCheckout = document.getElementById('btnCheckout');
        const totalDisplay = document.getElementById('totalDisplay');
        const itemCountDisplay = document.getElementById('itemCountDisplay');
        
        if(cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty">
                    <div style="background: var(--bg-surface); padding: 20px; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 10px; border: 1px dashed var(--border-subtle);">
                        <i class="ph-fill ph-shopping-cart-simple" style="font-size: 40px; color: var(--border-subtle);"></i>
                    </div>
                    Keranjang Kosong
                    <span style="font-size: 11px; font-weight: 500; opacity: 0.7; margin-top: 5px;">Scan barcode / klik produk.</span>
                </div>`;
            totalDisplay.innerText = 'Rp 0';
            itemCountDisplay.innerText = '0 Item';
            btnCheckout.disabled = true;
            globalTotal = 0;
            document.getElementById('amountPaid').value = '';
            calculateChange(); 
            return;
        }

        globalTotal = 0; let totalItems = 0;
        let html = '';
        
        cart.forEach(item => {
            let subtotal = item.qty * item.price;
            globalTotal += subtotal;
            totalItems += item.qty;
            
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
                    </div>
                </div>
            </div>`;
        });
        
        container.innerHTML = html;
        totalDisplay.innerText = 'Rp ' + globalTotal.toLocaleString('id-ID');
        itemCountDisplay.innerText = `${totalItems} Item`;
        
        container.scrollTop = 0;
        calculateChange(); 
    }

    // --- LOGIKA KEMBALIAN ---
    function formatRupiah(angka) {
        let val = angka.value.replace(/[^,\d]/g, '').toString();
        let split = val.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let sep = sisa ? '.' : ''; rupiah += sep + ribuan.join('.'); }
        angka.value = rupiah;
    }

    function calculateChange() {
        let paid = parseInt(document.getElementById('amountPaid').value.replace(/\./g, '')) || 0;
        globalChange = paid - globalTotal;
        const display = document.getElementById('changeDisplay');
        const btn = document.getElementById('btnCheckout');

        if(cart.length === 0) {
            btn.disabled = true;
            return;
        }

        if(globalChange < 0) {
            display.className = 'change-val text-red';
            display.innerText = 'Kurang Rp ' + Math.abs(globalChange).toLocaleString('id-ID');
            btn.disabled = true;
        } else {
            display.className = 'change-val';
            display.innerText = 'Rp ' + globalChange.toLocaleString('id-ID');
            btn.disabled = false;
        }
    }

    // --- CHECKOUT FINAL ---
    async function processCheckout() {
        if(cart.length === 0) return;

        let custName = document.getElementById('custName').value || 'Pelanggan Umum';
        let amountPaidStr = document.getElementById('amountPaid').value.replace(/[^,\d]/g, '');

        const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        let confirmationHtml = `
            <div style="font-size: 14px; color: var(--text-main); margin-bottom: 15px;">Terima uang tunai dari <b>${custName}</b>?</div>
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(0,0,0,0.03); padding:12px 15px; border-radius:10px; margin-bottom:10px; border:1px solid var(--border-subtle);">
                <span style="font-weight:700; font-size:13px; color:var(--text-muted);">Total Belanja:</span>
                <span style="font-weight:900; font-family:monospace; font-size:16px;">Rp ${globalTotal.toLocaleString('id-ID')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(16, 185, 129, 0.1); padding:15px; border-radius:12px; border:1px dashed #10b981;">
                <span style="font-weight:800; font-size:13px; color:#10b981;">KEMBALIAN:</span>
                <span style="font-weight:900; font-family:monospace; font-size:24px; color:#10b981;">Rp ${globalChange.toLocaleString('id-ID')}</span>
            </div>
        `;

        Swal.fire({
            title: 'Selesaikan Transaksi?',
            html: confirmationHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-printer"></i> Konfirmasi & Cetak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                let formData = new FormData();
                formData.append('customer_name', custName);
                formData.append('payment_method', 'Cash');
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
                    if (!response.ok) throw new Error("Koneksi bermasalah.");
                    return await response.json();
                } catch (error) {
                    Swal.showValidationMessage(`Koneksi Gagal: ${error.message}`);
                }
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
                        confirmButtonText: '<i class="ph-bold ph-arrow-counter-clockwise"></i> Layani Berikutnya',
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

</body>
</html>