<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($company['company_name'] ?? 'NORIC EXHAUST') ?> | Premium Performance Systems</title>
    
    <link rel="shortcut icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --brand-primary: #f97316; /* Racing Orange */
            --brand-dark: #ea580c;
            --brand-black: #09090b; /* Deep Zinc */
            
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            
            --border-soft: rgba(0, 0, 0, 0.06);
            --border-hard: rgba(0, 0, 0, 0.15);
            
            --shadow-float: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 30px 60px -15px rgba(249, 115, 22, 0.15);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* --- BACKGROUND EFFECTS --- */
        .bg-grid {
            position: fixed; inset: 0; z-index: -2;
            background-image: 
                linear-gradient(var(--border-soft) 1px, transparent 1px),
                linear-gradient(90deg, var(--border-soft) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at center, black, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at center, black, transparent 80%);
        }

        .bg-glow {
            position: fixed; top: -10%; left: 50%; transform: translateX(-50%);
            width: 80vw; height: 60vh;
            background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%);
            filter: blur(80px); z-index: -1; pointer-events: none;
        }

        /* --- SCROLL ANIMATIONS --- */
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

        /* --- NAVBAR GLASSMORPHISM --- */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; padding: 20px 5%; display: flex; justify-content: space-between; align-items: center;
            z-index: 100; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid transparent; transition: all 0.4s ease;
        }
        .navbar.scrolled { padding: 15px 5%; border-bottom: 1px solid var(--border-soft); background: rgba(255, 255, 255, 0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--brand-black); font-family: 'Oswald', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;}
        .nav-brand img { height: 36px; border-radius: 8px;}

        .nav-links { display: flex; gap: 40px; }
        .nav-links a { color: var(--text-main); text-decoration: none; font-weight: 700; font-size: 13px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;}
        .nav-links a:hover { color: var(--brand-primary); }

        .btn-portal {
            background: var(--brand-black); color: #fff; padding: 12px 24px; border-radius: 100px;
            text-decoration: none; font-weight: 800; font-size: 13px; transition: all 0.3s; display: flex; align-items: center; gap: 8px; border: 1px solid var(--brand-black); white-space: nowrap;
        }
        .btn-portal:hover { background: #fff; color: var(--brand-black); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);}

        /* --- HERO SECTION --- */
        .hero { 
            min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; 
            padding: 160px 5% 80px; position: relative;
        }
        
        .hero-badge {
            background: #fff; border: 1px solid var(--border-soft); padding: 8px 24px; border-radius: 100px;
            font-size: 12px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 30px; 
            display: inline-flex; align-items: center; gap: 8px; box-shadow: var(--shadow-float);
        }

        .hero-title { 
            font-family: 'Oswald', sans-serif; font-size: clamp(40px, 8vw, 100px); font-weight: 700; line-height: 1.1; 
            margin-bottom: 24px; text-transform: uppercase; letter-spacing: -2px; color: var(--brand-black);
            max-width: 1000px;
        }
        .hero-title span { background: linear-gradient(135deg, var(--brand-primary), #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}
        
        .hero-subtitle { font-size: clamp(14px, 2vw, 18px); color: var(--text-muted); max-width: 650px; margin: 0 auto 40px; font-weight: 500; line-height: 1.7;}

        .hero-actions { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; width: 100%;}
        
        .btn-shop { background: var(--brand-primary); color: #fff; padding: 18px 40px; border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 14px; transition: 0.3s; box-shadow: 0 10px 25px rgba(249, 115, 22, 0.3); display: inline-flex; align-items: center; justify-content: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
        .btn-shop:hover { background: var(--brand-dark); transform: translateY(-4px); box-shadow: 0 15px 35px rgba(249, 115, 22, 0.4); }
        
        .btn-explore { background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-hard); padding: 18px 40px; border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 14px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; text-transform: uppercase; letter-spacing: 1px; box-shadow: var(--shadow-float);}
        .btn-explore:hover { border-color: var(--brand-black); color: var(--brand-primary); transform: translateY(-4px);}

        /* --- CATALOG SECTION --- */
        .catalog { padding: 100px 5%; max-width: 1400px; margin: 0 auto; }
        
        .section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px; flex-wrap: wrap; gap: 20px;}
        .section-title h2 { font-family: 'Oswald', sans-serif; font-size: clamp(36px, 5vw, 56px); font-weight: 700; text-transform: uppercase; line-height: 1.1; letter-spacing: -1px; color: var(--brand-black);}
        .section-title p { color: var(--text-muted); font-size: 15px; font-weight: 500; margin-top: 10px; max-width: 400px;}
        
        /* Mobile Perfect Scrollable Tabs */
        .catalog-filter-wrap { width: 100%; overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none; padding-bottom: 10px;}
        .catalog-filter-wrap::-webkit-scrollbar { display: none; }
        .catalog-filter { display: inline-flex; gap: 8px; background: #fff; padding: 6px; border-radius: 100px; border: 1px solid var(--border-soft); box-shadow: var(--shadow-float);}
        
        .filter-btn { background: transparent; border: none; color: var(--text-muted); padding: 12px 24px; border-radius: 100px; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; flex-shrink: 0;}
        .filter-btn:hover { color: var(--brand-black); }
        .filter-btn.active { background: var(--brand-black); color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.2);}

        .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr)); gap: 30px;}
        
        /* Product Card */
        .product-card { 
            background: var(--bg-card); border-radius: 20px; overflow: hidden; 
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
            display: flex; flex-direction: column; position: relative;
            border: 1px solid var(--border-soft); box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .product-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-hover); border-color: rgba(249, 115, 22, 0.3);}
        .product-card.hide { display: none; }
        
        .product-img { height: 260px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;}
        .product-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 50%, rgba(255,255,255,0.95) 100%); z-index: 5; pointer-events: none;}
        
        .product-img img { width: 100%; height: 100%; object-fit: cover; z-index: 1; transition: transform 0.6s ease;}
        .product-card:hover .product-img img { transform: scale(1.05) rotate(-1deg); }
        
        .product-img i { font-size: 120px; color: #cbd5e1; z-index: 1; transition: transform 0.6s ease;}
        .product-card:hover .product-img i { transform: scale(1.1) translateX(5px); color: #94a3b8;}
        
        .badge-wrap { position: absolute; top: 15px; left: 15px; right: 15px; display: flex; justify-content: space-between; align-items: flex-start; z-index: 10;}
        .badge-hot { background: var(--brand-primary); color: #fff; padding: 4px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.3);}
        .badge-mat { background: rgba(255,255,255,0.9); color: var(--brand-black); padding: 4px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; backdrop-filter: blur(4px); border: 1px solid var(--border-soft);}
        
        .product-info { padding: 0 20px 25px; display: flex; flex-direction: column; flex: 1; position: relative; z-index: 10; margin-top: -20px;}
        
        .p-specs { display: flex; gap: 6px; margin-bottom: 12px; flex-wrap: wrap;}
        .spec-item { background: #fff; border: 1px solid var(--border-soft); padding: 4px 10px; border-radius: 100px; font-size: 9px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
        
        .product-info h3 { font-size: 18px; font-weight: 900; margin-bottom: 6px; line-height: 1.3; color: var(--text-main); letter-spacing: -0.5px;}
        .p-category { font-size: 11px; color: var(--brand-primary); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;}
        
        .price-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-top: 15px; border-top: 1px dashed var(--border-soft);}
        .product-price { font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 700; color: var(--text-main); display: flex; flex-direction: column; gap: 0; letter-spacing: -1px;}
        .product-price span { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; color: var(--text-muted); text-decoration: line-through; font-weight: 600; letter-spacing: 0;}

        .card-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: auto;}
        .btn-action { padding: 12px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 800; font-size: 12px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 6px;}
        
        .btn-shopee { background: rgba(249, 115, 22, 0.1); color: var(--brand-dark); border: 1px solid transparent;}
        .btn-shopee:hover { background: var(--brand-primary); color: #fff; box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);}
        
        .btn-wa { background: var(--bg-page); color: var(--text-main); border: 1px solid var(--border-hard); }
        .btn-wa:hover { background: var(--text-main); color: #fff; }

        /* --- THE FACTORY & QUALITY --- */
        .quality-section { padding: 80px 5%; background: var(--brand-black); color: #fff; position: relative; overflow: hidden;}
        .quality-section::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 1px; background: linear-gradient(90deg, transparent, var(--brand-primary), transparent); opacity: 0.5;}
        
        .quality-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;}
        
        .q-card { padding: 30px 25px; background: rgba(255,255,255,0.03); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); text-align: center; transition: 0.4s; backdrop-filter: blur(10px);}
        .q-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.05); border-color: rgba(249,115,22,0.3);}
        
        .q-icon { width: 60px; height: 60px; margin: 0 auto 20px; background: linear-gradient(135deg, rgba(249,115,22,0.2), transparent); color: var(--brand-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid rgba(249,115,22,0.2);}
        .q-card h4 { font-size: 18px; font-weight: 800; margin-bottom: 10px; letter-spacing: 0.5px;}
        .q-card p { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 1.6;}

        /* --- FOOTER --- */
        .footer { padding: 60px 5% 30px; text-align: center; background: #000; color: #fff;}
        .footer-logo { font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: 700; color: #fff; margin-bottom: 15px; display: inline-flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
        .footer-logo i { color: var(--brand-primary); }
        
        .footer-desc { color: rgba(255,255,255,0.5); max-width: 400px; margin: 0 auto 30px; font-size: 13px; line-height: 1.8;}
        
        .footer-social { display: flex; justify-content: center; gap: 12px; margin-bottom: 30px; }
        .footer-social a { width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.05); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s; text-decoration: none; border: 1px solid rgba(255,255,255,0.1);}
        .footer-social a:hover { background: var(--brand-primary); border-color: var(--brand-primary); transform: translateY(-5px); box-shadow: 0 10px 20px rgba(249,115,22,0.3);}
        
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; color: rgba(255,255,255,0.4); font-size: 12px; font-weight: 500;}

        /* --- MOBILE SPECIFIC FIXES --- */
        @media (max-width: 900px) { 
            .nav-links { display: none; } 
            .nav-brand span { display: none; } /* Hanya tampilkan icon di layar kecil jika text terlalu panjang */
        }
        
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .hero { padding: 120px 5% 60px; min-height: auto;}
            .btn-shop, .btn-explore { width: 100%; } /* Tombol hero penuh di HP */
            .catalog { padding: 60px 5%; }
            .card-actions { grid-template-columns: 1fr; } /* Tombol produk ditumpuk atas-bawah */
            .quality-grid { grid-template-columns: 1fr; gap: 20px;}
        }
    </style>
</head>
<body>

    <div class="bg-grid"></div>
    <div class="bg-glow"></div>

    <nav class="navbar" id="navbar">
        <a href="#" class="nav-brand">
            <?php if(!empty($company['logo_path']) && $company['logo_path'] !== 'default-logo.png'): ?>
                <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" alt="Logo">
            <?php else: ?>
                <i class="ph-fill ph-engine" style="color: var(--brand-primary);"></i>
            <?php endif; ?>
            <span><?= esc($company['app_name'] ?? 'NORIC EXHAUST') ?></span>
        </a>
        
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#catalog">Katalog</a>
            <a href="#quality">Kualitas</a>
        </div>

        <a href="<?= base_url('/login') ?>" class="btn-portal">
            <i class="ph-bold ph-lock-key"></i> Internal Portal
        </a>
    </nav>

    <section class="hero" id="home">
        <div class="hero-badge reveal">
            <i class="ph-fill ph-flag-checkered"></i> Official Factory Store
        </div>
        <h1 class="hero-title reveal delay-1">
            Mendominasi Lintasan.<br>
            <span>Memimpin Jalanan.</span>
        </h1>
        <p class="hero-subtitle reveal delay-2">
            Diproduksi langsung dari jantung "Kota Knalpot" Purbalingga. Kami merancang sistem pembuangan yang secara instan mendongkrak tenaga mesin, torsi, dan estetika motor Anda.
        </p>
        <div class="hero-actions reveal delay-3">
            <a href="#catalog" class="btn-shop">
                Lihat Etalase <i class="ph-bold ph-arrow-down"></i>
            </a>
            <a href="#quality" class="btn-explore">
                Teknologi Kami
            </a>
        </div>
    </section>

    <section class="catalog" id="catalog">
        
        <div class="section-header reveal">
            <div class="section-title">
                <h2>The Catalog</h2>
                <p>Koleksi sistem knalpot *Best Seller* yang telah diuji dan diakui oleh mekanik di seluruh Nusantara.</p>
            </div>
            
            <?php
                $uniqueCategories = [];
                if(!empty($catalogs)) {
                    $uniqueCategories = array_unique(array_column($catalogs, 'category'));
                }
            ?>
            <div class="catalog-filter-wrap">
                <div class="catalog-filter">
                    <button class="filter-btn active" data-filter="all">Semua Tipe</button>
                    <?php foreach($uniqueCategories as $cat): ?>
                        <button class="filter-btn" data-filter="<?= esc(strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat))) ?>">
                            <?= esc($cat) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="catalog-grid">
            <?php if(!empty($catalogs)): ?>
                <?php $delay = 1; foreach($catalogs as $item): ?>
                    
                    <div class="product-card reveal delay-<?= $delay ?>" data-category="<?= esc(strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $item['category']))) ?>">
                        <div class="product-img">
                            <div class="badge-wrap">
                                <?php if(!empty($item['badge_text'])): ?>
                                    <span class="badge-hot"><?= esc($item['badge_text']) ?></span>
                                <?php else: ?>
                                    <span style="opacity:0;"></span>
                                <?php endif; ?>
                                
                                <?php 
                                    $specsArr = explode(',', $item['specs']); 
                                    $materialBadge = !empty($specsArr[0]) ? trim($specsArr[0]) : 'Premium';
                                ?>
                                <span class="badge-mat"><?= esc($materialBadge) ?></span>
                            </div>
                            
                            <?php if(!empty($item['product_image'])): ?>
                                <img src="<?= base_url('uploads/catalogs/' . esc($item['product_image'])) ?>" alt="<?= esc($item['product_name']) ?>">
                            <?php else: ?>
                                <i class="ph-duotone <?= esc($item['icon_class'] ?: 'ph-motorcycle') ?>"></i> 
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <div class="p-specs">
                                <?php 
                                    // Sisa spek
                                    for($i = 1; $i < count($specsArr); $i++): 
                                        if(trim($specsArr[$i]) != ''):
                                ?>
                                    <span class="spec-item"><?= esc(trim($specsArr[$i])) ?></span>
                                <?php endif; endfor; ?>
                            </div>
                            
                            <h3><?= esc($item['product_name']) ?></h3>
                            <div class="p-category"><?= esc($item['category']) ?></div>
                            
                            <div class="price-row">
                                <div class="product-price">
                                    <?php if($item['discount_price'] > 0): ?>
                                        <span>Rp <?= number_format($item['discount_price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                    Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <a href="<?= esc($item['shopee_link']) ?>" target="_blank" class="btn-action btn-shopee"><i class="ph-bold ph-shopping-bag"></i> Beli Ecer</a>
                                <?php 
                                    $waLink = $item['wa_link'];
                                    if(strpos($waLink, 'http') !== 0) { $waLink = 'https://' . $waLink; }
                                ?>
                                <a href="<?= esc($waLink) ?>" target="_blank" class="btn-action btn-wa"><i class="ph-bold ph-whatsapp-logo"></i> Grosir B2B</a>
                            </div>
                        </div>
                    </div>

                    <?php $delay++; if($delay > 3) $delay = 1; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 80px 20px; background: #fff; border-radius: 20px; border: 1px dashed var(--border-hard);">
                    <i class="ph-duotone ph-package" style="font-size: 60px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <h3 style="font-size: 20px; font-weight: 800; color: var(--text-main);">Katalog Belum Tersedia</h3>
                    <p style="font-size: 13px;">Admin sedang mempersiapkan produk terbaik kami untuk Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="quality-section" id="quality">
        <div class="quality-grid">
            <div class="q-card reveal">
                <div class="q-icon"><i class="ph-fill ph-factory"></i></div>
                <h4>Pabrikasi Internal</h4>
                <p>Diproduksi mandiri di Purbalingga dengan pengawasan Quality Control yang ketat di setiap titik pengelasan.</p>
            </div>
            <div class="q-card reveal delay-1">
                <div class="q-icon"><i class="ph-fill ph-package"></i></div>
                <h4 style="color: var(--brand-primary);">Suplai Grosir B2B</h4>
                <p>Membuka peluang kerja sama bisnis untuk bengkel dan distributor di seluruh pelosok Indonesia dengan harga khusus.</p>
            </div>
            <div class="q-card reveal delay-2">
                <div class="q-icon"><i class="ph-fill ph-shield-check"></i></div>
                <h4>Garansi Presisi</h4>
                <p>Setiap leher knalpot didesain sangat presisi (Plug and Play), menjamin pemasangan mudah tanpa merusak rangka bawaan.</p>
            </div>
        </div>
    </section>

    <footer class="footer" id="contact">
        <div class="footer-logo reveal">
            <i class="ph-fill ph-engine"></i> <?= esc($company['app_name'] ?? 'NORIC EXHAUST') ?>
        </div>
        <p class="footer-desc reveal">
            Manufaktur Sistem Pembuangan Performa Tinggi.<br>Pilihan utama para rider yang mengerti arti sebenarnya dari sebuah tenaga kuda.
        </p>
        
        <div class="footer-social reveal">
            <a href="#" title="Instagram"><i class="ph-fill ph-instagram-logo"></i></a>
            <a href="#" title="Tiktok"><i class="ph-fill ph-tiktok-logo"></i></a>
            <a href="#" title="WhatsApp Business"><i class="ph-fill ph-whatsapp-logo"></i></a>
        </div>

        <div class="footer-bottom">
            &copy; <script>document.write(new Date().getFullYear())</script> <?= esc($company['company_name'] ?? 'PT. Noric Jaya Sentosa') ?>. All Rights Reserved.
            <div style="margin-top: 5px; font-size: 11px; opacity: 0.3; font-family: 'Space Mono', monospace;">ERP Core Engine</div>
        </div>
    </footer>

    <script>
        // 1. Navbar Shrink on Scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // 2. Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });

        // 3. Scroll Reveal Animation
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target); 
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => {
            observer.observe(el);
        });

        // 4. REAL-TIME PRODUCT FILTER LOGIC
        const filterBtns = document.querySelectorAll('.filter-btn');
        const productCards = document.querySelectorAll('.product-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Set Active State
                filterBtns.forEach(f => f.classList.remove('active'));
                btn.classList.add('active');

                const filterValue = btn.getAttribute('data-filter');

                productCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    
                    if (filterValue === 'all' || filterValue === category) {
                        card.classList.remove('hide');
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.classList.add('hide');
                        }, 400); // Sinkron dengan durasi CSS transition
                    }
                });
            });
        });
    </script>
</body>
</html>