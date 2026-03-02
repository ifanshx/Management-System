<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;}
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); margin-bottom: 20px;}
    .card-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: 0.2s;}
    .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);}

    /* Journal Row Styles */
    .journal-row { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 15px; align-items: center; margin-bottom: 10px; background: rgba(0,0,0,0.01); padding: 10px; border-radius: 12px; border: 1px dashed var(--border-subtle);}
    html.dark .journal-row { background: rgba(255,255,255,0.02); }
    
    .input-money { position: relative; }
    .input-money span { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; font-weight: 800; color: var(--text-muted);}
    .input-money input { padding-left: 40px; text-align: right; font-family: 'Space Mono', monospace;}

    .btn-remove { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; width: 44px; height: 44px; border-radius: 10px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-remove:hover { background: #ef4444; color: #fff;}

    .btn-add { background: var(--bg-base); color: #0ea5e9; border: 2px dashed #0ea5e9; width: 100%; padding: 15px; border-radius: 12px; font-weight: 800; cursor: pointer; margin-bottom: 25px; transition: 0.2s; display: flex; justify-content: center; gap: 8px;}
    .btn-add:hover { background: rgba(14, 165, 233, 0.1); }

    /* Footer Balance Checker */
    .balance-checker { display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); padding: 20px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 20px;}
    .stat-box { text-align: right; }
    .stat-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;}
    .stat-val { font-size: 24px; font-weight: 900; font-family: 'Space Mono', monospace;}
    
    .status-indicator { font-size: 18px; font-weight: 900; padding: 10px 20px; border-radius: 10px; display: flex; align-items: center; gap: 10px;}
    .balanced { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);}
    .unbalanced { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);}

    .btn-save { width: 100%; background: #0ea5e9; color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3); transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px;}
    .btn-save:hover { background: #0284c7; transform: translateY(-2px);}
    .btn-save:disabled { background: var(--border-subtle); color: var(--text-muted); cursor: not-allowed; box-shadow: none; transform: none;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-books" style="color: #0ea5e9;"></i> Kemasukan Jurnal Umum</h1>
        <p>Rekod transaksi kewangan secara manual untuk mengekalkan keseimbangan Lejar Am kilang.</p>
    </div>
</div>

<div class="bento-card">
    <form action="<?= base_url('/accounting/store_journal') ?>" method="post" id="journalForm">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 25px;">
            <div class="form-group">
                <label>Tarikh Transaksi</label>
                <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Keterangan Transaksi / Rujukan</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Pembayaran utiliti elektrik kilang" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 15px; margin-bottom: 10px; padding: 0 10px;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Pilih Carta Akaun (CoA)</div>
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; text-align: right;">Debit (Rp)</div>
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; text-align: right;">Kredit (Rp)</div>
            <div></div>
        </div>

        <div id="journal-container">
            <div class="journal-row">
                <select name="account_id[]" class="form-control" required>
                    <option value="">-- Pilih Akaun --</option>
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="form-control d-input" placeholder="0" oninput="calcBalance()"></div>
                <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="form-control c-input" placeholder="0" oninput="calcBalance()"></div>
                <div style="width: 44px;"></div> </div>
            <div class="journal-row">
                <select name="account_id[]" class="form-control" required>
                    <option value="">-- Pilih Akaun --</option>
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= esc($acc['account_code']) ?> - <?= esc($acc['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="form-control d-input" placeholder="0" oninput="calcBalance()"></div>
                <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="form-control c-input" placeholder="0" oninput="calcBalance()"></div>
                <div style="width: 44px;"></div>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addRow()"><i class="ph ph-plus-circle"></i> Tambah Baris Jurnal</button>

        <div class="balance-checker">
            <div class="status-indicator unbalanced" id="balanceStatus">
                <i class="ph ph-warning"></i> Tidak Seimbang
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
            <i class="ph ph-floppy-disk"></i> Simpan Jurnal & Kemas Kini Lejar
        </button>
    </form>
</div>

<script>
    const accData = <?= json_encode($accounts) ?>;

    function addRow() {
        let container = document.getElementById('journal-container');
        let options = '<option value="">-- Pilih Akaun --</option>';
        accData.forEach(acc => { options += `<option value="${acc.id}">${acc.account_code} - ${acc.account_name}</option>`; });

        let row = document.createElement('div');
        row.className = 'journal-row';
        row.innerHTML = `
            <select name="account_id[]" class="form-control" required>${options}</select>
            <div class="input-money"><span>Rp</span><input type="number" name="debit[]" class="form-control d-input" placeholder="0" oninput="calcBalance()"></div>
            <div class="input-money"><span>Rp</span><input type="number" name="credit[]" class="form-control c-input" placeholder="0" oninput="calcBalance()"></div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calcBalance();"><i class="ph ph-trash"></i></button>
        `;
        container.appendChild(row);
    }

    function calcBalance() {
        let debits = document.querySelectorAll('.d-input');
        let credits = document.querySelectorAll('.c-input');
        
        let tDebit = 0;
        let tCredit = 0;

        debits.forEach(el => { tDebit += parseFloat(el.value) || 0; });
        credits.forEach(el => { tCredit += parseFloat(el.value) || 0; });

        document.getElementById('totalDebit').innerText = tDebit.toLocaleString('id-ID');
        document.getElementById('totalCredit').innerText = tCredit.toLocaleString('id-ID');

        let statusEl = document.getElementById('balanceStatus');
        let btnSubmit = document.getElementById('btnSubmit');

        if (tDebit === tCredit && tDebit > 0) {
            statusEl.className = 'status-indicator balanced';
            statusEl.innerHTML = '<i class="ph ph-check-circle"></i> Jurnal Seimbang (Balanced)';
            btnSubmit.disabled = false;
        } else {
            statusEl.className = 'status-indicator unbalanced';
            statusEl.innerHTML = '<i class="ph ph-warning"></i> Tidak Seimbang';
            btnSubmit.disabled = true;
        }
    }
</script>

<?= $this->endSection() ?>