<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ==========================================================================
       ENTERPRISE FINANCE UI/UX - KASBON MODULE
       ========================================================================== */
    :root {
        --brand: #ef4444; 
        --brand-dark: #b91c1c; 
        --brand-soft: rgba(239, 68, 68, 0.08);
        --bg-main: #f8fafc; 
        --card-bg: #ffffff; 
        --border-color: #e2e8f0;
        --text-main: #0f172a; 
        --text-muted: #64748b;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.03);
        --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08);
        --shadow-hover: 0 20px 30px -10px rgba(0,0,0,0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        --radius-lg: 20px;
    }

    html.dark {
        --bg-main: #0f172a; 
        --card-bg: #1e293b; 
        --border-color: #334155;
        --text-main: #f8fafc; 
        --text-muted: #94a3b8;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-main); color: var(--text-main); }
    .swal2-custom-radius { border-radius: var(--radius-lg) !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(239, 68, 68, 0.05) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%); }

    /* HEADER */
    .page-header { margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 60px; height: 60px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    /* GRID & CARDS */
    .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 2fr); gap: 24px; align-items: start; position: relative; z-index: 1; padding-bottom: 40px;}
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 30px; box-shadow: var(--shadow-sm); transition: var(--transition-smooth); }
    .bento-card:hover { box-shadow: var(--shadow-md); }
    .card-header { font-size: 17px; font-weight: 900; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-color); padding-bottom: 18px;}

    /* FORM STYLES */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-main); border: 2px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: 0.3s; }
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); background: var(--card-bg);}
    .input-wrapper input, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 18px; font-size: 14px; font-weight: 700; outline: none; font-family: inherit; width: 100%;}
    
    .prefix { background: var(--brand-soft); color: var(--brand-dark); font-size: 16px; font-weight: 900; padding: 0 18px; display: flex; align-items: center; border-right: 2px solid rgba(239, 68, 68, 0.1); }
    
    /* CUSTOM SELECT2 */
    .select2-container--default .select2-selection--single { background-color: var(--bg-main); border: 2px solid var(--border-color); border-radius: 14px; height: auto; padding: 12px 18px; outline: none; transition: var(--transition-smooth); }
    .select2-container--open .select2-selection--single { border-color: var(--brand); background: var(--card-bg); box-shadow: 0 0 0 4px var(--brand-soft); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); font-size: 14px; font-weight: 800; padding-left: 0; line-height: 1.5; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; right: 15px; }
    .select2-dropdown { background-color: var(--card-bg); border: 1px solid var(--brand); border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 9999; overflow: hidden; margin-top: 5px;}
    .select2-search__field { background-color: var(--bg-main); color: var(--text-main); border: 2px solid var(--border-color) !important; border-radius: 8px; padding: 10px !important; font-family: 'Plus Jakarta Sans', sans-serif; outline: none; font-weight: 600;}
    .select2-search__field:focus { border-color: var(--brand) !important; }
    .select2-results__option { color: var(--text-main); font-weight: 600; font-size: 13px; padding: 12px 18px; transition: 0.2s;}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--brand-soft); color: var(--brand-dark); font-weight: 800; }
    .select2-container--default .select2-results__option[aria-selected=true] { background-color: rgba(0,0,0,0.03); color: var(--text-muted); }

    .btn-submit { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; padding: 18px; border: none; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4); width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; text-transform: uppercase; letter-spacing: 0.5px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.5); }

    /* SEARCH BAR TABEL */
    .table-search-container { position: relative; width: 100%; max-width: 320px; }
    .table-search-container i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 18px; }
    .table-search-container input { width: 100%; padding: 12px 16px 12px 42px; border: 2px solid var(--border-color); border-radius: 99px; background: var(--bg-main); color: var(--text-main); font-size: 13px; font-weight: 700; outline: none; transition: 0.3s; font-family: inherit; }
    .table-search-container input:focus { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); background: var(--card-bg); }

    /* TABLE */
    .table-responsive { overflow-x: auto; border-radius: 16px; border: 1px solid var(--border-color); background: var(--card-bg); box-shadow: var(--shadow-sm); }
    .table-responsive::-webkit-scrollbar { height: 8px; }
    .table-responsive::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
    
    .table-kasbon { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 900px; }
    .table-kasbon th { text-align: left; padding: 18px 24px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-main); border-bottom: 2px solid var(--border-color); letter-spacing: 0.8px; position: sticky; top: 0; z-index: 10;}
    .table-kasbon td { text-align: left; padding: 16px 24px; border-bottom: 1px dashed var(--border-color); vertical-align: middle; transition: var(--transition-smooth);}
    .table-kasbon tbody tr { transition: 0.2s ease; }
    .table-kasbon tbody tr:hover td { background: var(--bg-main); }
    .table-kasbon tbody tr:last-child td { border-bottom: none; }
    
    /* Typography & Hierarchy */
    .user-info { display: flex; flex-direction: column; gap: 4px; }
    .user-name { font-weight: 800; font-size: 14px; color: var(--text-main); }
    .user-nik { font-size: 11px; font-family: 'Space Mono', monospace; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px; background: var(--bg-main); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-color); width: max-content;}
    
    .amount-text { font-family: 'Space Mono', monospace; font-weight: 900; font-size: 16px; }
    .amount-text.lunas { color: var(--text-muted); }
    .amount-text.pending { color: var(--brand-dark); }
    
    .date-badge { background: var(--bg-main); border: 1px solid var(--border-color); padding: 6px 12px; border-radius: 8px; font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 800; color: var(--text-main); display: inline-block; }
    .desc-text { font-size: 13px; color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; }
    
    /* Badges & Actions */
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px; width: max-content;}
    .status-lunas { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2);}
    .status-belum { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    html.dark .status-lunas { color: #34d399; }
    html.dark .status-belum { color: #fbbf24; }

    .btn-delete { color: var(--brand-dark); background: var(--brand-soft); font-size: 18px; transition: 0.3s; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; text-decoration: none; border: 1px solid transparent;}
    .btn-delete:hover { background: var(--brand); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239,68,68,0.3);}
</style>

<div class="ambient-glow"></div>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-hand-coins"></i></div>
        <div>
            <h1>Kasbon & Pinjaman Karyawan</h1>
            <p>Catat pengeluaran kasbon (potong uang laci/bank) dan atur skema pemotongan fleksibel.</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- FORM SECTION -->
    <div class="bento-card" style="border-top: 6px solid var(--brand);">
        <div class="card-header" style="color: var(--brand); border-bottom-color: var(--brand-soft);">
            <i class="ph-bold ph-plus-circle" style="font-size: 22px;"></i> Pencairan Kasbon Baru
        </div>

        <form action="<?= base_url('/cash_advance/store') ?>" method="post" id="formKasbon">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Pilih Karyawan Peminjam</label>
                <select name="employee_id" class="select2-search" required style="width: 100%;">
                    <option value=""></option>
                    <?php foreach($employees as $emp): ?>
                        <!-- FIX: Nullsafe Array Access using Null Coalescing Operator -->
                        <option value="<?= esc($emp['employee_id'] ?? '') ?>">
                            <?= esc($emp['name'] ?? 'Unknown') ?> 
                            (<?= esc($emp['position'] ?? 'Staff') ?>) 
                            <?= !empty($emp['emp_status']) ? '- ' . esc($emp['emp_status']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Total Pinjaman Yang Diberikan</label>
                <div class="input-wrapper" style="border-color: var(--brand); box-shadow: 0 4px 15px var(--brand-soft);">
                    <div class="prefix">Rp</div>
                    <input type="text" name="amount" placeholder="0" onkeyup="formatRupiah(this)" required style="font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 900; color: var(--brand);" autocomplete="off">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Metode Pencairan</label>
                    <div class="input-wrapper">
                        <select name="payment_method" required style="cursor: pointer;">
                            <option value="Cash">💵 Tunai (Kas Laci)</option>
                            <option value="Transfer">💳 Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan / Keperluan</label>
                    <div class="input-wrapper">
                        <input type="text" name="description" placeholder="Catatan singkat..." required autocomplete="off">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Tipe Periode</label>
                    <div class="input-wrapper">
                        <select name="tenure_type" required style="cursor: pointer;">
                            <option value="days">Harian</option>
                            <option value="weeks">Mingguan</option>
                            <option value="months" selected>Bulanan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Cicilan</label>
                    <div class="input-wrapper">
                        <input type="number" name="tenure" value="1" min="1" required style="font-family: 'Space Mono', monospace; font-size: 14px; text-align: center;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tgl Potongan Pertama</label>
                    <div class="input-wrapper">
                        <input type="date" name="first_tempo_date" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace; font-size: 13px;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> <span>Cairkan Kasbon Sekarang</span>
            </button>
        </form>
    </div>

    <!-- TABLE SECTION -->
    <div class="bento-card">
        <!-- HEADER WITH SEARCH -->
        <div class="card-header" style="justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: var(--brand-soft); color: var(--brand); padding: 8px; border-radius: 10px; display: flex;">
                    <i class="ph-bold ph-list-dashes" style="font-size: 20px;"></i>
                </div>
                <span style="font-size: 18px; font-weight: 900; color: var(--text-main);">Daftar Tagihan Aktif</span>
            </div>
            <div class="table-search-container">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" id="searchTableInput" onkeyup="filterTable()" placeholder="Cari NIK, Nama, Keterangan...">
            </div>
        </div>
        
        <div class="table-responsive" style="max-height: 550px;">
            <table class="table-kasbon" id="kasbonTable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Karyawan Peminjam</th>
                        <th style="text-align: right; width: 15%;">Besaran (Rp)</th>
                        <th style="text-align: center; width: 15%;">Jatuh Tempo</th>
                        <th style="width: 25%;">Keterangan</th>
                        <th style="text-align: center; width: 10%;">Status</th>
                        <th style="text-align: center; width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($kasbon)): ?>
                        <tr class="empty-state">
                            <td colspan="6" style="text-align: center; padding: 80px 20px;">
                                <i class="ph-fill ph-check-circle" style="font-size: 64px; color: var(--border-color); margin-bottom: 16px; display: block;"></i>
                                <b style="font-size: 16px; color: var(--text-main); display: block; margin-bottom: 4px;">Tidak ada tagihan aktif</b>
                                <span style="font-size: 13px; color: var(--text-muted);">Semua karyawan bebas dari hutang kasbon saat ini.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($kasbon as $kb): ?>
                        <tr class="kasbon-row" style="<?= ($kb['status'] ?? '') == 'Lunas' ? 'opacity: 0.5;' : '' ?>">
                            <td>
                                <div class="user-info">
                                    <span class="user-name"><?= esc($kb['name'] ?? 'Unknown') ?></span>
                                    <span class="user-nik"><i class="ph-bold ph-identification-card" style="color: var(--brand);"></i> <?= esc($kb['employee_id'] ?? '') ?></span>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <span class="amount-text <?= ($kb['status'] ?? '') == 'Lunas' ? 'lunas' : 'pending' ?>">
                                    <?= number_format((float)($kb['amount'] ?? 0), 0, ',', '.') ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="date-badge">
                                    <?= date('d M Y', strtotime((string)($kb['tempo_date'] ?? 'now'))) ?>
                                </span>
                            </td>
                            <td>
                                <div class="desc-text" title="<?= esc($kb['description'] ?? '') ?>">
                                    <?= esc($kb['description'] ?? '-') ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?php if(($kb['status'] ?? '') == 'Lunas'): ?>
                                    <span class="status-badge status-lunas"><i class="ph-bold ph-check-circle"></i> Lunas</span>
                                <?php else: ?>
                                    <span class="status-badge status-belum"><i class="ph-bold ph-hourglass-high"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if(($kb['status'] ?? '') == 'Belum Lunas'): ?>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/cash_advance/delete/' . ($kb['id'] ?? '')) ?>')" class="btn-delete" title="Batalkan & Hapus Tagihan">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="ph-bold ph-lock-key" style="color: var(--border-color); font-size: 20px;" title="Terkunci (Sudah Masuk Payroll)"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // --- FITUR PENCARIAN (LIVE SEARCH) ---
    function filterTable() {
        let input = document.getElementById("searchTableInput");
        let filter = input.value.toLowerCase();
        let table = document.getElementById("kasbonTable");
        let tr = table.getElementsByClassName("kasbon-row");

        for (let i = 0; i < tr.length; i++) {
            let nameCol = tr[i].getElementsByTagName("td")[0];
            let descCol = tr[i].getElementsByTagName("td")[3];
            
            if (nameCol || descCol) {
                let textValue = (nameCol.textContent || nameCol.innerText) + " " + (descCol.textContent || descCol.innerText);
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // --- INIT SELECT2 PENCARIAN KARYAWAN ---
    $(document).ready(function() {
        $('.select2-search').select2({
            placeholder: "-- Ketik & Pilih Nama Karyawan --",
            allowClear: true,
            width: '100%'
        });
    });

    // --- SWEETALERT INTERCEPTOR ---
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#1e293b' : '#ffffff';
        const swalText = isDark ? '#f8fafc' : '#0f172a';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success')) ?>, confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: <?= json_encode(session()->getFlashdata('error')) ?>, confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    // --- FORMAT RUPIAH ---
    function formatRupiah(angka) {
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }

    // --- SUBMIT HANDLER ---
    document.getElementById('formKasbon').addEventListener('submit', function(e) {
        let amountInput = this.querySelector('input[name="amount"]');
        let empSelect = $('.select2-search').val(); 
        
        if(!empSelect) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Anda harus memilih Karyawan Peminjam terlebih dahulu.', customClass: { popup: 'swal2-custom-radius' } });
            return;
        }

        amountInput.value = amountInput.value.replace(/\./g, '');

        let btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 22px;"></i> <span>MEMPROSES...</span>';
    });

    // --- CUSTOM DELETE DIALOG ---
    function confirmDelete(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Cicilan Ini?',
            text: 'Catatan hutang akan dihapus. PENTING: Jika uang sudah dicairkan fisik, Anda juga harus membatalkan (VOID) transaksi pengeluaran di menu Jurnal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            background: isDark ? '#1e293b' : '#ffffff', 
            color: isDark ? '#f8fafc' : '#0f172a',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#1e293b' : '#ffffff', color: isDark ? '#f8fafc' : '#0f172a', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        });
    }
</script>

<?= $this->endSection() ?>