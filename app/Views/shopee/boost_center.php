<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .info-banner { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1)); border: 1px dashed rgba(59, 130, 246, 0.3); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; color: var(--text-main); font-size: 13px; font-weight: 600;}
    .info-banner i { font-size: 28px; color: #3b82f6; }

    /* --- SLOT GRID (5 KOTAK BOOST) --- */
    .slot-container { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 30px; }
    @media (max-width: 1200px) { .slot-container { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .slot-container { grid-template-columns: 1fr; } }

    .boost-slot { background: var(--bg-surface); border: 2px dashed var(--border-subtle); border-radius: 16px; height: 160px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 15px; position: relative; overflow: hidden; transition: 0.3s;}
    
    /* Kondisi Kosong */
    .slot-empty { color: var(--text-muted); cursor: pointer; }
    .slot-empty:hover { border-color: #3b82f6; background: rgba(59, 130, 246, 0.02); }
    .slot-empty i { font-size: 32px; opacity: 0.5; margin-bottom: 10px; transition: 0.3s;}
    .slot-empty:hover i { transform: scale(1.1); color: #3b82f6; opacity: 1;}

    /* Kondisi Sedang Jalan (Aktif) */
    .slot-active { border-style: solid; border-color: #f59e0b; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.1); background: linear-gradient(to bottom, var(--bg-surface), rgba(245, 158, 11, 0.05));}
    .active-badge { position: absolute; top: 10px; right: 10px; background: #f59e0b; color: #fff; font-size: 9px; font-weight: 800; padding: 3px 8px; border-radius: 10px; text-transform: uppercase; animation: pulse 2s infinite;}
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    
    .item-id-txt { font-family: monospace; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 5px; }
    .timer-txt { font-size: 24px; font-weight: 900; color: #f59e0b; font-family: 'Space Mono', monospace; margin-top: auto;}

    /* --- TABEL PILIH PRODUK --- */
    .selection-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden; }
    .card-header { padding: 20px; border-bottom: 1px solid var(--border-subtle); font-weight: 800; display: flex; justify-content: space-between; align-items: center;}
    
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 15px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 12px 20px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; vertical-align: middle;}
    
    .custom-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;}
    .btn-rocket { background: #3b82f6; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: 0.2s;}
    .btn-rocket:hover { background: #2563eb; transform: translateY(-2px); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-rocket-launch" style="color: #3b82f6;"></i> Pusat Trafik & Boost</h1>
        <p>Naikkan visibilitas knalpot Anda ke urutan pertama pencarian Shopee secara gratis setiap 4 Jam.</p>
    </div>
</div>

<div class="info-banner">
    <i class="ph ph-info"></i>
    <div>
        Shopee memberikan <b>5 Slot Gratis</b> untuk mempromosikan produk Anda. Saat produk dinaikkan, ia akan muncul di puncak daftar pencarian pembeli, yang akan meningkatkan konversi penjualan Anda secara drastis.
    </div>
</div>

<div class="slot-container">
    <?php 
    $totalSlots = 5;
    $usedSlots = count($boostedItems);
    
    // Tampilkan slot yang sedang terisi (Aktif)
    foreach($boostedItems as $item): 
    ?>
        <div class="boost-slot slot-active">
            <div class="active-badge"><i class="ph ph-lightning"></i> Di-Boost</div>
            <div class="item-id-txt">ID: <?= esc($item['item_id']) ?></div>
            <div style="font-size: 12px; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                Sedang dipromosikan...
            </div>
            <div class="timer-txt countdown-timer" data-seconds="<?= esc($item['cooldown_second']) ?>">--:--:--</div>
        </div>
    <?php endforeach; ?>

    <?php for($i = 0; $i < ($totalSlots - $usedSlots); $i++): ?>
        <div class="boost-slot slot-empty" onclick="document.getElementById('searchInput').focus();">
            <i class="ph ph-plus-circle"></i>
            <div style="font-size: 13px; font-weight: 700;">Slot Kosong</div>
            <div style="font-size: 11px; margin-top: 4px;">Pilih produk di bawah</div>
        </div>
    <?php endfor; ?>
</div>

<div class="selection-card">
<form action="<?= base_url('/shopee/boost_action/' . $shop['shop_id']) ?>" method="post" id="boostForm">        <?= csrf_field() ?>
        <input type="hidden" name="shop_id" value="<?= esc($shop['shop_id']) ?>">
        
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari Knalpot..." style="border: none; outline: none; background: transparent; font-size: 14px; font-weight: 600; color: var(--text-main); width: 250px;">
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 12px; color: var(--text-muted); font-weight: 700;">Terpilih: <span id="countCheck" style="color: #3b82f6; font-size: 14px;">0</span> / <?= $totalSlots - $usedSlots ?></span>
                <button type="button" class="btn-rocket" onclick="submitBoost()">
                    <i class="ph ph-paper-plane-tilt"></i> Naikkan Sekarang!
                </button>
            </div>
        </div>

        <div style="overflow-y: auto; max-height: 400px;">
            <table id="productTable">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="width: 50px; text-align: center;">Pilih</th>
                        <th style="width: 100px;">Gambar</th>
                        <th>Informasi Produk</th>
                        <th>Harga</th>
                        <th>Stok Etalase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($allProducts)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px;">Tidak ada produk sinkron.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($allProducts as $p): ?>
                        <?php 
                            // Cek apakah produk ini sedang di-boost? Jika ya, disable checkbox-nya
                            $isBoosted = false;
                            foreach($boostedItems as $bi) {
                                if($bi['item_id'] == $p['item_id']) { $isBoosted = true; break; }
                            }
                        ?>
                        <tr class="search-row">
                            <td style="text-align: center;">
                                <?php if($isBoosted): ?>
                                    <i class="ph ph-check-circle" style="color: #10b981; font-size: 20px;" title="Sedang Aktif"></i>
                                <?php else: ?>
                                    <input type="checkbox" name="item_ids[]" value="<?= esc($p['item_id']) ?>" class="custom-checkbox cb-boost" onchange="limitSelection(this)">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($p['image_url'])): ?>
                                    <img src="<?= esc($p['image_url']) ?>" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-subtle);">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: var(--bg-base); border-radius: 8px; display: flex; align-items:center; justify-content:center;"><i class="ph ph-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="prod-name" style="font-weight: 800; font-size: 13px; margin-bottom: 4px;"><?= esc($p['item_name']) ?></div>
                                <div style="font-size: 10px; color: var(--text-muted); font-family: monospace;">SKU: <?= esc($p['item_sku']) ?></div>
                            </td>
                            <td style="color: #10b981; font-weight: 800;">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td><?= $p['stock'] ?> Pcs</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const maxAllowed = <?= $totalSlots - $usedSlots ?>;

    // Filter Pencarian Tabel
    function filterTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let rows = document.getElementsByClassName("search-row");
        for (let i = 0; i < rows.length; i++) {
            let name = rows[i].querySelector(".prod-name").innerText.toLowerCase();
            rows[i].style.display = name.includes(input) ? "" : "none";
        }
    }

    // Membatasi pilihan checkbox sesuai sisa slot kosong
    function limitSelection(checkbox) {
        let checkedBoxes = document.querySelectorAll('.cb-boost:checked');
        document.getElementById('countCheck').innerText = checkedBoxes.length;

        if (checkedBoxes.length > maxAllowed) {
            checkbox.checked = false; // Batalkan centang
            document.getElementById('countCheck').innerText = maxAllowed;
            Swal.fire({
                icon: 'warning',
                title: 'Slot Penuh',
                text: `Anda hanya memiliki sisa ${maxAllowed} slot kosong saat ini.`,
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    // Konfirmasi Submit
    function submitBoost() {
        let checkedBoxes = document.querySelectorAll('.cb-boost:checked');
        if(checkedBoxes.length === 0) {
            Swal.fire('Pilih Produk', 'Centang minimal 1 knalpot yang ingin dipromosikan.', 'info');
            return;
        }

        Swal.fire({
            title: 'Mulai Promosi?',
            text: `Sistem akan menaikkan ${checkedBoxes.length} produk ini ke pencarian teratas Shopee selama 4 Jam ke depan.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#ef4444',
            confirmButtonText: '<i class="ph ph-rocket-launch"></i> Ya, Tembak!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('boostForm').submit();
            }
        });
    }

    // ==========================================
    // MESIN HITUNG MUNDUR (COUNTDOWN TIMER REAL-TIME)
    // ==========================================
    function startTimers() {
        let timers = document.querySelectorAll('.countdown-timer');
        
        setInterval(() => {
            timers.forEach(timer => {
                let seconds = parseInt(timer.getAttribute('data-seconds'));
                if (seconds > 0) {
                    seconds--;
                    timer.setAttribute('data-seconds', seconds);
                    
                    // Konversi ke format HH:MM:SS
                    let h = Math.floor(seconds / 3600);
                    let m = Math.floor((seconds % 3600) / 60);
                    let s = Math.floor(seconds % 60);
                    
                    // Tambahkan angka 0 di depan jika < 10
                    h = h < 10 ? '0' + h : h;
                    m = m < 10 ? '0' + m : m;
                    s = s < 10 ? '0' + s : s;
                    
                    timer.innerText = `${h}:${m}:${s}`;
                } else if (seconds === 0) {
                    timer.innerText = "SELESAI";
                    timer.style.color = "#10b981";
                }
            });
        }, 1000);
    }

    // Jalankan timer saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', startTimers);
</script>

<?= $this->endSection() ?>