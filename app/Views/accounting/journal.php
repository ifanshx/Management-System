<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =========================================================
       1. VARIABLES & AMBIENT GLOW (PREMIUM SAAS)
       ========================================================= */
    :root {
        --brand: #2563eb; --brand-dark: #1d4ed8; --brand-soft: rgba(37, 99, 235, 0.1);
        --success: #10b981; --success-soft: rgba(16, 185, 129, 0.1);
        --warning: #f59e0b; --warning-soft: rgba(245, 158, 11, 0.1);
        --danger: #ef4444; --danger-soft: rgba(239, 68, 68, 0.1);
        
        --bg-main: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
        
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 15px -5px rgba(0,0,0,0.05);
        --shadow-hover: 0 15px 35px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .swal2-container { z-index: 10000 !important; }
    .swal2-custom-radius { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif; }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%); }

    .page-wrapper { position: relative; z-index: 1; animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes fadeSlideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       2. PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 18px; }
    .page-title-wrap { display: flex; align-items: center; gap: 18px; }
    .page-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 12px 30px -8px rgba(37,99,235,0.5); }
    .page-title h1 { font-size: 32px; font-weight: 900; margin: 0 0 6px 0; color: var(--text-main); letter-spacing: -1px; line-height: 1;}
    .page-title p { margin: 0; font-size: 14px; color: var(--text-muted); font-weight: 600; letter-spacing: -0.2px;}

    .btn-back { background: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color); padding: 12px 18px; border-radius: 14px; font-weight: 800; text-decoration: none; font-size: 13px; transition: var(--transition-smooth); display: inline-flex; align-items: center; gap: 8px; box-shadow: var(--shadow-sm);}
    .btn-back:hover { transform: translateY(-2px); border-color: var(--brand); color: var(--brand); box-shadow: var(--shadow-md);}

    /* =========================================================
       3. MAIN BENTO GRID
       ========================================================= */
    .main-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; align-items: start; padding-bottom: 40px;}
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 28px; padding: 28px; box-shadow: var(--shadow-md); transition: var(--transition-smooth);}
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 2px dashed var(--border-color); }
    .icon-wrapper { width: 42px; height: 42px; border-radius: 14px; background: var(--brand-soft); color: var(--brand); display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .card-header h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}

    /* =========================================================
       4. FORM BUILDER
       ========================================================= */
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.8px; }
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: var(--transition-smooth); }
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); background: var(--card-bg);}
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 13px; font-weight: 700; outline: none; font-family: inherit; width: 100%; }
    .input-wrapper textarea { min-height: 90px; resize: vertical; }

    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px !important; cursor: pointer;}

    .prefix-money { padding: 14px 16px; background: rgba(0,0,0,0.03); font-size: 12px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-color); display: flex; align-items: center;}
    .input-money-field { font-family: 'Space Mono', monospace !important; text-align: right; font-size: 15px !important; font-weight: 900 !important; color: var(--text-main) !important;}
    .debit-field { color: var(--brand) !important; }
    .credit-field { color: var(--warning) !important; }

    .desktop-labels { display: grid; grid-template-columns: 2fr 1.6fr 1.2fr 1.2fr auto; gap: 14px; padding: 0 12px 10px 12px; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    @media (max-width: 768px) { .desktop-labels { display: none !important; } }

    .journal-row { display: grid; grid-template-columns: 2fr 1.6fr 1.2fr 1.2fr auto; gap: 14px; align-items: center; margin-bottom: 15px; background: var(--card-bg); padding: 16px; border-radius: 20px; border: 1px solid var(--border-color); transition: var(--transition-smooth); box-shadow: var(--shadow-sm);}
    .journal-row:hover { border-color: var(--brand); transform: translateY(-2px); box-shadow: var(--shadow-hover);}
    
    @media (max-width: 768px) { 
        .journal-row { grid-template-columns: 1fr; position: relative; padding: 30px 20px 20px 20px;} 
        .journal-row > .btn-remove { position: absolute; top: 10px; right: 10px; } 
    }

    .btn-remove { background: var(--danger-soft); color: var(--danger); border: 1px solid transparent; width: 44px; height: 44px; border-radius: 14px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;}
    .btn-remove:hover { background: var(--danger); color: #fff; transform: scale(1.05) rotate(5deg); }

    .btn-add { background: var(--bg-main); color: var(--brand); border: 2px dashed var(--border-color); width: 100%; padding: 16px; border-radius: 16px; font-weight: 900; font-size: 13px; cursor: pointer; margin-bottom: 30px; transition: var(--transition-smooth); display: flex; justify-content: center; align-items: center; gap: 8px;}
    .btn-add:hover { background: var(--brand-soft); border-color: var(--brand); transform: translateY(-2px);}

    /* =========================================================
       5. BALANCE CHECKER
       ========================================================= */
    .balance-checker { display: flex; justify-content: space-between; align-items: center; background: var(--bg-main); padding: 22px 26px; border-radius: 22px; border: 1px solid var(--border-color); margin-bottom: 24px; flex-wrap: wrap; gap: 20px;}
    
    .stat-box { text-align: left; }
    .stat-label { font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;}
    .stat-val { font-size: 26px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); letter-spacing: -1px; line-height: 1;}

    .balance-status { font-size: 14px; font-weight: 900; padding: 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 8px; border: 1px solid transparent; transition: all 0.4s; text-transform: uppercase; letter-spacing: 1px;}
    .balanced { background: var(--success-soft); color: var(--success); border-color: rgba(16, 185, 129, 0.3); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);}
    .unbalanced { background: var(--danger-soft); color: var(--danger); border-color: rgba(239, 68, 68, 0.2); animation: pulseWarn 2s infinite;}
    @keyframes pulseWarn { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    .btn-submit { width: 100%; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; padding: 22px; border: none; border-radius: 20px; font-size: 16px; font-weight: 900; cursor: pointer; transition: var(--transition-smooth); box-shadow: 0 12px 25px -10px rgba(37,99,235,0.6); display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.05); box-shadow: 0 16px 30px -10px rgba(37,99,235,0.8);}
    .btn-submit:disabled { background: var(--border-color); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none; filter: none;}

    /* =========================================================
       6. RECENT JOURNALS (RIGHT PANEL)
       ========================================================= */
    .recent-list { display: flex; flex-direction: column; gap: 16px; }
    .recent-item { padding: 18px; border-radius: 20px; border: 1px solid var(--border-color); background: var(--card-bg); transition: var(--transition-smooth); box-shadow: var(--shadow-sm); }
    .recent-item:hover { transform: translateY(-3px); border-color: var(--brand); box-shadow: var(--shadow-md); }
    
    .recent-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed var(--border-color);}
    .recent-no { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: var(--brand); background: var(--brand-soft); padding: 4px 8px; border-radius: 8px;}
    .recent-date { font-size: 11px; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 6px;}
    
    .recent-desc { font-size: 13px; font-weight: 800; color: var(--text-main); line-height: 1.5; margin-bottom: 12px;}
    .recent-amount { font-size: 18px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1px;}
    
    .status-badge { font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px;}
    .s-posted { background: var(--success-soft); color: var(--success); }
    .s-void { background: var(--danger-soft); color: var(--danger); text-decoration: line-through;}
</style>

<div class="ambient-glow"></div>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title-wrap">
            <div class="page-icon"><i class="ph-fill ph-notepad"></i></div>
            <div class="page-title">
                <div>
                    <h1>Pencatatan Jurnal Umum</h1>
                    <p>Input mutasi akuntansi dengan metode Double-Entry secara akurat.</p>
                </div>
            </div>
        </div>
        <a href="<?= base_url('/accounting') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left"></i> Dasbor Akuntansi
        </a>
    </div>

    <div class="main-grid">
        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph-fill ph-pencil-line"></i></div>
                <h3>Form Entri Jurnal Baru</h3>
            </div>

            <form action="<?= base_url('/accounting/store_journal') ?>" method="post" id="journalForm">
                <?= csrf_field() ?>

                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 2px dashed var(--border-color);">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Tanggal Transaksi</label>
                        <div class="input-wrapper">
                            <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace; font-weight: 900; color: var(--brand);">
                        </div>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Keterangan Referensi / Catatan</label>
                        <div class="input-wrapper">
                            <input type="text" name="description" placeholder="Contoh: Pembayaran beban listrik operasional bulan ini..." required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="desktop-labels">
                    <div>Pilih Kode Akun</div>
                    <div>Catatan Baris (Opsional)</div>
                    <div>Debit (+)</div>
                    <div>Kredit (-)</div>
                    <div>Aksi</div>
                </div>

                <div id="journalRows">
                    <div class="journal-row">
                        <div class="input-wrapper">
                            <select name="account_id[]" required>
                                <option value="" disabled selected>-- Pilih Akun --</option>
                                <?php foreach($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>">[<?= esc($acc['account_code']) ?>] <?= esc($acc['account_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="input-wrapper">
                            <input type="text" name="line_description[]" placeholder="Keterangan..." autocomplete="off">
                        </div>
                        <div class="input-wrapper">
                            <div class="prefix-money">Rp</div>
                            <input type="text" name="debit[]" class="input-money-field debit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
                        </div>
                        <div class="input-wrapper">
                            <div class="prefix-money">Rp</div>
                            <input type="text" name="credit[]" class="input-money-field credit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
                        </div>
                        <button type="button" class="btn-remove" onclick="removeRow(this)" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
                    </div>
                    
                    <div class="journal-row">
                        <div class="input-wrapper">
                            <select name="account_id[]" required>
                                <option value="" disabled selected>-- Pilih Akun --</option>
                                <?php foreach($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>">[<?= esc($acc['account_code']) ?>] <?= esc($acc['account_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="input-wrapper">
                            <input type="text" name="line_description[]" placeholder="Keterangan..." autocomplete="off">
                        </div>
                        <div class="input-wrapper">
                            <div class="prefix-money">Rp</div>
                            <input type="text" name="debit[]" class="input-money-field debit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
                        </div>
                        <div class="input-wrapper">
                            <div class="prefix-money">Rp</div>
                            <input type="text" name="credit[]" class="input-money-field credit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
                        </div>
                        <button type="button" class="btn-remove" onclick="removeRow(this)" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
                    </div>
                </div>

                <button type="button" class="btn-add" onclick="addRow()"><i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Baris Kosong</button>

                <div class="balance-checker">
                    <div class="stat-box">
                        <div class="stat-label">Total Debit</div>
                        <div class="stat-val" id="totalDebit" style="color: var(--brand);">0</div>
                    </div>

                    <div id="balanceStatus" class="balance-status unbalanced">
                        <i class="ph-fill ph-warning-circle" style="font-size: 20px;"></i> Belum Seimbang
                    </div>

                    <div class="stat-box" style="text-align: right;">
                        <div class="stat-label">Total Kredit</div>
                        <div class="stat-val" id="totalCredit" style="color: var(--warning);">0</div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                    <i class="ph-bold ph-lock-key" style="font-size: 22px;"></i> <span>Kunci Jurnal & Posting ke Buku Besar</span>
                </button>
            </form>
        </div>

        <div class="bento-card" style="background: transparent; border: none; box-shadow: none; padding: 0;">
            <div class="card-header" style="background: transparent; border-bottom: 2px dashed var(--border-color); padding-bottom: 20px; padding-left: 0; padding-right: 0;">
                <div class="icon-wrapper" style="background: rgba(100,116,139,.1); color: var(--text-muted);"><i class="ph-bold ph-clock-counter-clockwise"></i></div>
                <h3 style="color: var(--text-main);">Riwayat Jurnal</h3>
            </div>
            
            <div class="recent-list" style="max-height: 800px; overflow-y: auto; padding-right: 5px;">
                <?php if(empty($recent_journals)): ?>
                    <div class="recent-item" style="text-align: center; padding: 50px 20px;">
                        <i class="ph-duotone ph-ghost" style="font-size: 50px; color: var(--border-color); margin-bottom: 10px;"></i>
                        <div style="font-weight: 800; color: var(--text-muted);">Belum ada riwayat jurnal.</div>
                    </div>
                <?php else: ?>
                    <?php foreach($recent_journals as $jrn): ?>
                        <div class="recent-item">
                            <div class="recent-top">
                                <div class="recent-no"><?= esc($jrn['journal_number']) ?></div>
                                <div class="recent-date"><i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($jrn['transaction_date'])) ?></div>
                            </div>
                            <div class="recent-desc"><?= esc($jrn['description']) ?></div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                                <div>
                                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Total Mutasi</div>
                                    <div class="recent-amount">Rp <?= number_format($jrn['total_amount'], 0, ',', '.') ?></div>
                                </div>
                                <span class="status-badge <?= $jrn['status'] === 'VOID' ? 's-void' : 's-posted' ?>">
                                    <?= esc($jrn['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // --- FUNGSI FORMAT RUPIAH SAAT DIKETIK ---
    function formatRupiahText(value) {
        let number_string = value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Fungsi trigger untuk input
    function formatInput(el) {
        el.value = formatRupiahText(el.value);
        calculateBalance();
    }

    // --- FUNGSI PEMBERSIH TITIK UNTUK KALKULASI MATH ---
    function parseRupiah(val) {
        if (!val) return 0;
        let clean = val.replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(clean) || 0;
    }

    const accData = <?= json_encode($accounts) ?>;

    // --- ANIMASI TAMBAH BARIS ---
    function addRow() {
        let container = document.getElementById('journalRows');
        let options = '<option value="" disabled selected>-- Pilih Akun --</option>';
        accData.forEach(acc => { options += `<option value="${acc.id}">[${acc.account_code}] ${acc.account_name}</option>`; });

        let row = document.createElement('div');
        row.className = 'journal-row';
        row.innerHTML = `
            <div class="input-wrapper">
                <select name="account_id[]" required>${options}</select>
            </div>
            <div class="input-wrapper">
                <input type="text" name="line_description[]" placeholder="Keterangan..." autocomplete="off">
            </div>
            <div class="input-wrapper">
                <div class="prefix-money">Rp</div>
                <input type="text" name="debit[]" class="input-money-field debit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
            </div>
            <div class="input-wrapper">
                <div class="prefix-money">Rp</div>
                <input type="text" name="credit[]" class="input-money-field credit-field" placeholder="0" onkeyup="formatInput(this)" autocomplete="off">
            </div>
            <button type="button" class="btn-remove" onclick="removeRow(this)" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
        `;
        
        row.style.opacity = 0;
        row.style.transform = "translateY(20px) scale(0.98)";
        container.appendChild(row);
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0) scale(1)";
        }, 10);
    }

    // --- HAPUS BARIS ---
    function removeRow(btn) {
        const rows = document.querySelectorAll('.journal-row');
        if (rows.length <= 2) {
            Swal.fire({ icon: 'warning', title: 'Ditolak', text: 'Sistem Double-Entry mewajibkan minimal 2 baris (Debit & Kredit).', customClass: { popup: 'swal2-custom-radius' } });
            return;
        }
        btn.closest('.journal-row').remove();
        calculateBalance();
    }

    // --- KALKULATOR KESEIMBANGAN (BALANCE CHECKER) ---
    function calculateBalance() {
        let totalDebit = 0; 
        let totalCredit = 0;

        document.querySelectorAll('input[name="debit[]"]').forEach(el => { totalDebit += parseRupiah(el.value); });
        document.querySelectorAll('input[name="credit[]"]').forEach(el => { totalCredit += parseRupiah(el.value); });

        document.getElementById('totalDebit').innerText = totalDebit.toLocaleString('id-ID');
        document.getElementById('totalCredit').innerText = totalCredit.toLocaleString('id-ID');

        let statusEl = document.getElementById('balanceStatus');
        let btnSubmit = document.getElementById('btnSubmit');

        // Pengecekan Keseimbangan (Toleransi selisih 0.01 untuk koma)
        if (Math.abs(totalDebit - totalCredit) < 0.01 && totalDebit > 0) {
            statusEl.className = 'balance-status balanced';
            statusEl.innerHTML = '<i class="ph-fill ph-check-circle" style="font-size:22px;"></i> Jurnal Seimbang';
            btnSubmit.disabled = false;
        } else {
            statusEl.className = 'balance-status unbalanced';
            statusEl.innerHTML = '<i class="ph-fill ph-warning-circle" style="font-size:22px;"></i> Belum Seimbang';
            btnSubmit.disabled = true;
        }
    }

    // --- AJAX FORM SUBMIT ---
    document.getElementById('journalForm').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        let totalDebit = 0, totalCredit = 0;
        document.querySelectorAll('input[name="debit[]"]').forEach(el => totalDebit += parseRupiah(el.value));
        document.querySelectorAll('input[name="credit[]"]').forEach(el => totalCredit += parseRupiah(el.value));

        if (Math.abs(totalDebit - totalCredit) > 0.01 || totalDebit <= 0) {
            Swal.fire({ icon: 'error', title: 'Tidak Seimbang!', text: 'Total Debit dan Kredit harus sama dan lebih dari 0.', customClass: { popup: 'swal2-custom-radius' } });
            return;
        }

        // BERSIHKAN TITIK SEBELUM KIRIM KE DATABASE!
        document.querySelectorAll('input[name="debit[]"], input[name="credit[]"]').forEach(el => {
            el.dataset.oldValue = el.value; // Simpan nilai asli dengan titik untuk jaga-jaga kalau gagal
            el.value = parseRupiah(el.value); // Ubah jadi angka murni
        });

        const form = this;
        const btn = document.getElementById('btnSubmit');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        const isDark = document.documentElement.classList.contains('dark');
        
        btn.disabled = true;
        btnText.innerText = "Memposting Jurnal...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    icon: 'success', title: 'Tersimpan!', text: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000,
                    background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
                }).then(() => { window.location.reload(); });
            } else {
                // Restore format titik jika gagal
                document.querySelectorAll('input[name="debit[]"], input[name="credit[]"]').forEach(el => { el.value = el.dataset.oldValue; });
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#ef4444', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
                btn.disabled = false; btnText.innerText = "Kunci Jurnal & Posting ke Buku Besar"; btnIcon.className = "ph-bold ph-lock-key";
            }
        })
        .catch(err => {
            // Restore format titik jika error jaringan
            document.querySelectorAll('input[name="debit[]"], input[name="credit[]"]').forEach(el => { el.value = el.dataset.oldValue; });
            Swal.fire({ icon: 'error', title: 'Koneksi Terputus', text: "Gagal menghubungi server.", confirmButtonColor: '#ef4444', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
            btn.disabled = false; btnText.innerText = "Kunci Jurnal & Posting ke Buku Besar"; btnIcon.className = "ph-bold ph-lock-key";
        });
    });

</script>

<?= $this->endSection() ?>