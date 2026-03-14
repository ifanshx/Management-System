<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & NAVIGATION
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; flex-direction: column; gap: 12px; }
    
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 100px; width: fit-content; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: #0ea5e9; border-color: #0ea5e9; transform: translateX(-4px); box-shadow: 0 4px 15px rgba(14, 165, 233, 0.15);}

    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.05)); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(14, 165, 233, 0.2);}
    
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); margin: 4px 0 0 0; font-weight: 500;}

    /* =========================================================
       2. BENTO CARDS & LAYOUT
       ========================================================= */
    .grid-layout { display: grid; grid-template-columns: 2.2fr 1fr; gap: 24px; align-items: start;}
    @media (max-width: 1200px) { .grid-layout { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 35px; box-shadow: 0 15px 40px -10px rgba(0,0,0,0.05);}
    @media (max-width: 768px) { .bento-card { padding: 20px; } }

    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; padding: 6px; border-radius: 8px; font-size: 20px;}

    /* =========================================================
       3. FORM & DYNAMIC ROWS
       ========================================================= */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 15px 18px; border-radius: 14px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: 0.3s;}
    .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);}

    .journal-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: center; margin-bottom: 12px; background: var(--bg-base); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-subtle); transition: all 0.3s;}
    .journal-row:hover { border-color: #0ea5e9; box-shadow: 0 8px 20px -5px rgba(14, 165, 233, 0.15); transform: translateY(-2px);}
    @media (max-width: 768px) { .journal-row { grid-template-columns: 1fr; position: relative; padding: 25px 20px;} .journal-row > .btn-remove { position: absolute; top: 15px; right: 15px;} }
    
    .input-money { display: flex; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-money:focus-within { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);}
    .input-money span { padding: 12px 15px; background: rgba(0,0,0,0.02); font-size: 12px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 12px 15px; font-size: 14px; font-weight: 800; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace; text-align: right;}

    .btn-remove { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid transparent; width: 44px; height: 44px; border-radius: 12px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;}
    .btn-remove:hover { background: #ef4444; color: #fff; transform: scale(1.05) rotate(5deg); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    .btn-add { background: var(--bg-base); color: #0ea5e9; border: 2px dashed rgba(14, 165, 233, 0.4); width: 100%; padding: 18px; border-radius: 16px; font-weight: 800; font-size: 14px; cursor: pointer; margin-bottom: 30px; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px;}
    .btn-add:hover { background: rgba(14, 165, 233, 0.05); border-color: #0ea5e9; transform: translateY(-2px);}

    /* =========================================================
       4. FOOTER BALANCE CHECKER
       ========================================================= */
    .balance-checker { display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 25px 30px; border-radius: 20px; border: 1px solid var(--border-subtle); margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    
    .stat-box { text-align: right; }
    .stat-label { font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;}
    .stat-val { font-size: 28px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); letter-spacing: -1px;}
    
    .status-indicator { font-size: 16px; font-weight: 900; padding: 12px 24px; border-radius: 14px; display: flex; align-items: center; gap: 10px; border: 2px solid transparent; transition: all 0.4s;}
    .balanced { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);}
    .unbalanced { background: rgba(239, 68, 68, 0.08); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);}

    .btn-save { width: 100%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 22px; border-radius: 18px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.5); transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 10px;}
    .btn-save:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -5px rgba(14, 165, 233, 0.6);}
    .btn-save:disabled { background: var(--bg-base); color: var(--text-muted); cursor: not-allowed; box-shadow: none; border: 2px dashed var(--border-subtle); transform: none;}

    /* =========================================================
       5. RECENT JOURNALS TABLE (RIGHT PANEL)
       ========================================================= */
    table { width: 100%; border-collapse: collapse; }
    td { padding: 15px 10px; border-bottom: 1px dashed var(--border-subtle); vertical-align: top;}
    tr:last-child td { border-bottom: none; }

    /* AJAX Toast */
    #ajaxToast { position: fixed; top: 20px; right: -400px; background: #10b981; color: #fff; padding: 16px 24px; border-radius: 12px; font-size: 14px; font-weight: 800; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4); display: flex; align-items: center; gap: 10px; z-index: 9999; transition: right 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
    #ajaxToast.show { right: 20px; }
    #ajaxToast.error { background: #ef4444; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4); }
</style>

<div id="ajaxToast"><i class="ph-bold ph-check-circle" style="font-size: 20px;"></i> <span id="toastMsg">Sukses!</span></div>

<div style="max-width: 1400px; margin: 0 auto; padding-bottom: 50px;">
    
    <div class="page-header">
        <a href="<?= base_url('/accounting') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left" style="font-size: 16px;"></i> Kembali ke Dasbor Finansial
        </a>
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-books"></i></div>
            <div>
                <h1>Pencatatan Jurnal Umum</h1>
                <p>Rekam transaksi akuntansi secara manual menggunakan metode Double-Entry.</p>
            </div>
        </div>
    </div>

    <div class="grid-layout">
        <div class="bento-card" style="border-top: 6px solid #0ea5e9;">
            <form action="<?= base_url('/accounting/store_journal') ?>" method="post" id="formJournal">
                <?= csrf_field() ?>

                <div style="display: grid; grid-template-columns: 1fr 2.5fr; gap: 20px; margin-bottom: 30px; background: rgba(14, 165, 233, 0.05); padding: 25px; border-radius: 16px; border: 1px solid rgba(14, 165, 233, 0.2);">
                    <div class="form-group" style="margin: 0;">
                        <label style="color: #0ea5e9;">Tanggal Transaksi</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace; border-color: rgba(14, 165, 233, 0.3);">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label style="color: #0ea5e9;">Keterangan / Referensi Transaksi</label>
                        <input type="text" name="description" class="form-control" placeholder="Contoh: Pembayaran tagihan listrik pabrik bulan ini..." required style="border-color: rgba(14, 165, 233, 0.3);">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; margin-bottom: 15px; padding: 0 20px; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" class="desktop-labels">
                    <div>Pilih Kode Akun (CoA)</div>
                    <div style="text-align: right;">Nilai Debit</div>
                    <div style="text-align: right;">Nilai Kredit</div>
                    <div style="width: 44px;"></div>
                </div>

                <div id="journal-container">
                    <div class="journal-row">
                        <select name="account_id[]" class="form-control" required>
                            <option value="">-- Cari Akun Buku Besar --</option>
                            <?php foreach($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="d-input" placeholder="0" oninput="calcBalance()"></div>
                        <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="c-input" placeholder="0" oninput="calcBalance()"></div>
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calcBalance();" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
                    </div>
                    <div class="journal-row">
                        <select name="account_id[]" class="form-control" required>
                            <option value="">-- Cari Akun Buku Besar --</option>
                            <?php foreach($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="d-input" placeholder="0" oninput="calcBalance()"></div>
                        <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="c-input" placeholder="0" oninput="calcBalance()"></div>
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calcBalance();" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
                    </div>
                </div>

                <button type="button" class="btn-add" onclick="addRow()"><i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Baris Jurnal Baru</button>

                <div class="balance-checker">
                    <div class="status-indicator unbalanced" id="balanceStatus">
                        <i class="ph-fill ph-warning-circle"></i> Tidak Seimbang
                    </div>
                    <div style="display: flex; gap: 40px;">
                        <div class="stat-box">
                            <div class="stat-label">Total Debit</div>
                            <div class="stat-val" id="totalDebit" style="color: var(--text-main);">0</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Total Kredit</div>
                            <div class="stat-val" id="totalCredit" style="color: var(--text-main);">0</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" id="btnSubmit" disabled>
                    <i class="ph-bold ph-lock-key" style="font-size: 22px;"></i> <span>Kunci Jurnal & Kemas Kini Buku Besar</span>
                </button>
            </form>
        </div>

        <div class="bento-card">
            <div class="card-title" style="color: var(--text-muted);">
                <i class="ph-fill ph-clock-counter-clockwise"></i> Riwayat Input Terakhir
            </div>
            <div style="overflow-x: auto; max-height: 700px; overflow-y: auto; padding-right: 10px;">
                <table>
                    <tbody>
                        <?php if(empty($recent_journals)): ?>
                            <tr><td style="text-align: center; color: var(--text-muted); padding: 40px 10px;">Belum ada riwayat jurnal manual.</td></tr>
                        <?php else: ?>
                            <?php foreach($recent_journals as $jrn): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 900; font-size: 13px; color: #0ea5e9; font-family: 'Space Mono', monospace; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 6px;"><?= esc($jrn['journal_number']) ?></div>
                                        <div style="font-size: 13px; white-space: normal; line-height: 1.4; font-weight: 800; color: var(--text-main); margin-bottom: 4px;"><?= esc($jrn['description']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                                            <span><i class="ph-bold ph-calendar-blank"></i> <?= date('d M', strtotime($jrn['transaction_date'])) ?></span>
                                            <span style="font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; color: var(--text-main);">Rp <?= number_format($jrn['total_amount'], 0, ',', '.') ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    @media (max-width: 768px) { .desktop-labels { display: none !important; } }
</style>

<script>
    const accData = <?= json_encode($accounts) ?>;

    // Animasi Muncul Melayang Baris Jurnal
    function addRow() {
        let container = document.getElementById('journal-container');
        let options = '<option value="">-- Cari Akun Buku Besar --</option>';
        accData.forEach(acc => { options += `<option value="${acc.id}">${acc.account_code} - ${acc.account_name}</option>`; });

        let row = document.createElement('div');
        row.className = 'journal-row';
        row.innerHTML = `
            <select name="account_id[]" class="form-control" required>${options}</select>
            <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="d-input" placeholder="0" oninput="calcBalance()"></div>
            <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="c-input" placeholder="0" oninput="calcBalance()"></div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calcBalance();" title="Hapus Baris"><i class="ph-bold ph-x"></i></button>
        `;
        
        row.style.opacity = 0;
        row.style.transform = "translateY(15px) scale(0.98)";
        container.appendChild(row);
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0) scale(1)";
        }, 10);
    }

    // Kalkulator Keseimbangan Akuntansi Real-Time
    function calcBalance() {
        let debits = document.querySelectorAll('.d-input');
        let credits = document.querySelectorAll('.c-input');
        
        let tDebit = 0; let tCredit = 0;

        debits.forEach(el => { tDebit += parseFloat(el.value) || 0; });
        credits.forEach(el => { tCredit += parseFloat(el.value) || 0; });

        document.getElementById('totalDebit').innerText = tDebit.toLocaleString('id-ID');
        document.getElementById('totalCredit').innerText = tCredit.toLocaleString('id-ID');

        let statusEl = document.getElementById('balanceStatus');
        let btnSubmit = document.getElementById('btnSubmit');

        if (tDebit === tCredit && tDebit > 0) {
            statusEl.className = 'status-indicator balanced';
            statusEl.innerHTML = '<i class="ph-fill ph-check-circle" style="font-size:22px;"></i> Laporan Seimbang';
            btnSubmit.disabled = false;
        } else {
            statusEl.className = 'status-indicator unbalanced';
            statusEl.innerHTML = '<i class="ph-fill ph-warning-circle" style="font-size:22px;"></i> Tidak Seimbang';
            btnSubmit.disabled = true;
        }
    }

    // AJAX Toast Notification
    function showToast(msg, isError = false) {
        const toast = document.getElementById('ajaxToast');
        document.getElementById('toastMsg').innerText = msg;
        if(isError) {
            toast.classList.add('error');
            toast.innerHTML = `<i class="ph-bold ph-warning-circle" style="font-size: 20px;"></i> <span id="toastMsg">${msg}</span>`;
        } else {
            toast.classList.remove('error');
            toast.innerHTML = `<i class="ph-bold ph-check-circle" style="font-size: 20px;"></i> <span id="toastMsg">${msg}</span>`;
        }
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3500);
    }

    // AJAX Form Submission (Tanpa Reload)
    document.getElementById('formJournal').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const form = this;
        const btn = document.getElementById('btnSubmit');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Membukukan Jurnal...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                showToast(data.message);
                
                // Animasi beres, refresh halaman perlahan untuk perbarui riwayat di panel kanan
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                showToast(data.message, true);
                btn.disabled = false;
                btnText.innerText = "Kunci Jurnal & Kemas Kini Buku Besar";
                btnIcon.className = "ph-bold ph-lock-key";
            }
        })
        .catch(err => {
            showToast("Gagal Menghubungi Server", true);
            btn.disabled = false;
            btnText.innerText = "Kunci Jurnal & Kemas Kini Buku Besar";
            btnIcon.className = "ph-bold ph-lock-key";
        });
    });
</script>

<?= $this->endSection() ?>