<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
if (!function_exists('fmt_compact_cash')) {
    function fmt_compact_cash($angka) {
        $angka = (float)$angka;
        if ($angka >= 1000000000) return number_format($angka / 1000000000, 2, ',', '.') . ' M';
        if ($angka >= 1000000) return number_format($angka / 1000000, 2, ',', '.') . ' Jt';
        return number_format($angka, 0, ',', '.');
    }
}
?>

<style>
    :root {
        --bg-main: #f4f6f9; --card-bg: #ffffff; --border-color: #e2e8f0;
        --text-dark: #1e293b; --text-muted: #64748b;
        --blue: #3b82f6; --green: #10b981; --red: #ef4444; --orange: #ea580c; --purple: #8b5cf6; --dark-navy: #1e293b;
    }

    body { background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-dark); }
    .swal2-container { z-index: 10000 !important; }

    .header-actions { display: flex; justify-content: flex-end; margin-bottom: 20px; }
    .btn-top { background: var(--card-bg); border: 1px solid var(--border-color); padding: 10px 16px; border-radius: 6px; font-weight: 700; font-size: 13px; color: var(--text-dark); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;}
    .btn-top:hover { background: var(--bg-main); }

    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px; }
    @media (max-width: 1024px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-row { grid-template-columns: 1fr; } }

    .kpi-box { padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .kpi-info { display: flex; flex-direction: column; gap: 6px; }
    .kpi-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
    .kpi-val { font-size: 20px; font-weight: 900; font-family: 'Space Mono', monospace; }

    .k-dark { background: var(--dark-navy); color: #fff; }
    .k-green { background: #fff; color: var(--green); border-left: 4px solid var(--green); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
    .k-red { background: #fff; color: var(--red); border-left: 4px solid var(--red); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
    .k-blue { background: var(--blue); color: #fff; }

    .wallet-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    @media (max-width: 640px) { .wallet-row { grid-template-columns: 1fr; } }

    .w-box { padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .w-orange { background: var(--orange); }
    .w-purple { background: var(--purple); }
    .w-icon { font-size: 32px; opacity: 0.3; }

    .main-grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
    .card-header { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-weight: 800; font-size: 14px; color: var(--text-dark); display: flex; align-items: center; gap: 8px; background: #fafafa; }
    .card-body { padding: 20px; }

    .tab-container { display: flex; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; }
    .tab-btn { flex: 1; padding: 10px; text-align: center; font-size: 12px; font-weight: 700; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; }
    .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); background: #eff6ff; }

    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; }
    .form-control { width: 100%; border: 1px solid var(--border-color); padding: 10px 12px; border-radius: 6px; font-size: 13px; font-family: inherit; color: var(--text-dark); outline: none; }
    .form-control:focus { border-color: var(--blue); }
    textarea.form-control { resize: vertical; min-height: 80px; }

    .radio-group { display: flex; gap: 10px; }
    .radio-label { flex: 1; position: relative; cursor: pointer; }
    .radio-label input { position: absolute; opacity: 0; }
    .radio-box { display: block; text-align: center; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 11px; font-weight: 800; color: var(--text-muted); transition: 0.2s; }
    
    .radio-label input:checked + .radio-box.in { border-color: var(--green); color: var(--green); background: #f0fdf4; }
    .radio-label input:checked + .radio-box.out { border-color: var(--text-muted); color: var(--text-dark); background: #f8fafc; }
    .radio-label input:checked + .radio-box.cash { border-color: var(--orange); color: var(--orange); background: #fff7ed; }
    .radio-label input:checked + .radio-box.atm { border-color: var(--text-muted); color: var(--text-dark); background: #f8fafc; }
    .radio-label input:checked + .radio-box.mutasi { border-color: var(--blue); color: var(--blue); background: #eff6ff; }

    .btn-submit { width: 100%; background: var(--blue); color: #fff; border: none; padding: 12px; border-radius: 6px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; }
    .btn-submit:hover { background: #2563eb; }
    .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

    .form-content { display: none; }
    .form-content.active { display: block; }

    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th { padding: 12px 15px; text-align: left; font-size: 10px; font-weight: 800; color: var(--text-muted); border-bottom: 1px solid var(--border-color); text-transform: uppercase; }
    td { padding: 12px 15px; font-size: 12px; color: var(--text-dark); border-bottom: 1px solid var(--border-color); vertical-align: middle; font-weight: 600; }
    
    .row-awal { background: #f8fafc; }
    .row-awal td { font-weight: 800; }
    .row-total { background: #f8fafc; font-weight: 800; }
    .row-total td { border-top: 2px solid var(--border-color); border-bottom: none; padding: 15px; }

    .badge-sys { background: #e2e8f0; color: var(--text-muted); padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .mono { font-family: 'Space Mono', monospace; font-weight: 800; }

    .btn-del { color: var(--red); background: transparent; border: none; font-size: 16px; cursor: pointer; padding: 4px; transition: 0.2s; }
    .btn-del:hover { transform: scale(1.1); }
    .text-green { color: var(--green); }
    .text-red { color: var(--red); }
    .flex-center { display: flex; align-items: center; gap: 8px; }
</style>

<div class="header-actions">
    <a href="<?= base_url('/accounting') ?>" class="btn-top">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Accounting
    </a>
</div>

<div class="kpi-row">
    <div class="kpi-box k-dark">
        <div class="kpi-info">
            <span class="kpi-label">Saldo Awal Hari Ini</span>
            <span class="kpi-val">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></span>
        </div>
        <i class="ph-bold ph-clock-counter-clockwise" style="font-size: 32px; opacity: 0.2;"></i>
    </div>
    <div class="kpi-box k-green">
        <div class="kpi-info">
            <span class="kpi-label">Pemasukan Global</span>
            <span class="kpi-val">+ <?= number_format($masuk_hari_ini, 0, ',', '.') ?></span>
        </div>
        <i class="ph-bold ph-arrow-down" style="font-size: 28px; opacity: 0.2;"></i>
    </div>
    <div class="kpi-box k-red">
        <div class="kpi-info">
            <span class="kpi-label">Pengeluaran Global</span>
            <span class="kpi-val">- <?= number_format($keluar_hari_ini, 0, ',', '.') ?></span>
        </div>
        <i class="ph-bold ph-arrow-up" style="font-size: 28px; opacity: 0.2;"></i>
    </div>
    <div class="kpi-box k-blue">
        <div class="kpi-info">
            <span class="kpi-label">Total Saldo Akhir</span>
            <span class="kpi-val">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></span>
        </div>
        <i class="ph-fill ph-wallet" style="font-size: 32px; opacity: 0.3;"></i>
    </div>
</div>

<div class="wallet-row">
    <div class="w-box w-orange">
        <div class="kpi-info">
            <span class="kpi-label">Dompet Tunai (Cash)</span>
            <span class="kpi-val">Rp <?= number_format($sisa_cash, 0, ',', '.') ?></span>
        </div>
        <i class="ph-fill ph-wallet w-icon"></i>
    </div>
    <div class="w-box w-purple">
        <div class="kpi-info">
            <span class="kpi-label">Rekening Bank (ATM)</span>
            <span class="kpi-val">Rp <?= number_format($sisa_atm, 0, ',', '.') ?></span>
        </div>
        <i class="ph-fill ph-credit-card w-icon"></i>
    </div>
</div>

<div class="main-grid">
    <div class="card">
        <div class="card-header">
            <i class="ph-bold ph-plus-circle"></i> Input Transaksi
        </div>
        <div class="card-body" style="padding-top: 0;">
            <div class="tab-container">
                <div class="tab-btn active" id="tabBtn-biasa" onclick="switchTab('biasa')">Transaksi Biasa</div>
                <div class="tab-btn" id="tabBtn-mutasi" onclick="switchTab('mutasi')">Pindah Dana</div>
            </div>

            <form id="formKas" action="<?= base_url('/finance/cash-store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="mode_input" id="mode_input_val" value="transaksi">

                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <input type="date" id="tglFilterInput" name="tanggal_input" class="form-control" value="<?= esc($tgl_filter) ?>" onchange="updateDateFilter(this.value)">
                </div>

                <div id="form-biasa" class="form-content active">
                    <div class="form-group">
                        <label class="form-label">Arus Dana</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="type" value="Cash In">
                                <span class="radio-box in"><i class="ph-bold ph-arrow-down"></i> MASUK</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="type" value="Cash Out" checked>
                                <span class="radio-box out"><i class="ph-bold ph-arrow-up"></i> KELUAR</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sumber Dana</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="metode" value="Cash" checked>
                                <span class="radio-box cash">CASH</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="metode" value="ATM">
                                <span class="radio-box atm"><i class="ph-bold ph-credit-card"></i> ATM</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="form-mutasi" class="form-content">
                    <div class="form-group">
                        <label class="form-label">Arah Pindah Dana</label>
                        <div class="radio-group" style="flex-direction: column;">
                            <label class="radio-label">
                                <input type="radio" name="arah_mutasi" value="atm_to_cash" checked disabled class="mutasi-input">
                                <span class="radio-box mutasi" style="text-align: left;">
                                    <i class="ph-bold ph-bank"></i> ATM &nbsp;&rarr;&nbsp; <i class="ph-bold ph-money"></i> LACI CASH
                                </span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="arah_mutasi" value="cash_to_atm" disabled class="mutasi-input">
                                <span class="radio-box mutasi" style="text-align: left;">
                                    <i class="ph-bold ph-money"></i> LACI CASH &nbsp;&rarr;&nbsp; <i class="ph-bold ph-bank"></i> ATM
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="text" name="amount" class="form-control" placeholder="0" required onkeyup="formatRupiah(this)" autocomplete="off" style="font-size: 16px; font-weight: 900; color: var(--blue);">
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control" placeholder="Keterangan transaksi..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Bukti (Opsional)</label>
                    <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" class="form-control" style="padding: 6px;">
                </div>

                <button type="submit" id="btnSubmitKas" class="btn-submit">
                    <i class="ph-bold ph-floppy-disk"></i> SIMPAN TRANSAKSI
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="ph-fill ph-list"></i> Mutasi: <?= date('d F Y', strtotime($tgl_filter)) ?>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th class="text-center">Metode</th>
                        <th class="text-right">Nominal</th>
                        <th class="text-right">Saldo Global</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-awal">
                        <td>
                            <div class="flex-center"><div style="width:6px;height:6px;background:#1e293b;border-radius:50%;"></div> SALDO AWAL</div>
                        </td>
                        <td class="text-center"><span class="badge-sys">SYSTEM</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right mono"><?= number_format($saldo_awal, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>

                    <?php if(empty($transactions)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                                Belum ada transaksi hari ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $running = $saldo_awal; ?>
                        <?php foreach($transactions as $trx): ?>
                            <?php 
                                $isVoid = ($trx['status'] ?? 'POSTED') !== 'POSTED';
                                
                                if (!$isVoid) {
                                    if($trx['type'] == 'Cash In') {
                                        $running += $trx['amount'];
                                        $colorClass = 'text-green'; $sign = '+';
                                    } else {
                                        $running -= $trx['amount'];
                                        $colorClass = 'text-red'; $sign = '-';
                                    }
                                } else {
                                    // Tentukan warna tapi JANGAN hitung $running karena sudah di-VOID
                                    if($trx['type'] == 'Cash In') {
                                        $colorClass = 'text-green'; $sign = '+';
                                    } else {
                                        $colorClass = 'text-red'; $sign = '-';
                                    }
                                }
                            ?>
                            <tr>
                                <td style="white-space: normal; min-width: 200px;">
                                    <div style="<?= $isVoid ? 'text-decoration: line-through; opacity: 0.5;' : '' ?>">
                                        <?= esc($trx['description']) ?>
                                    </div>
                                    <div style="font-size: 9px; color: var(--text-muted); margin-top: 4px; text-transform: uppercase;">Oleh: <?= esc($trx['pic_name']) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge-sys" style="background: <?= $trx['metode'] == 'Cash' ? 'var(--orange)' : 'var(--purple)' ?>; color: #fff; <?= $isVoid ? 'opacity: 0.5;' : '' ?>">
                                        <?= esc($trx['metode']) ?>
                                    </span>
                                </td>
                                <td class="text-right mono <?= $colorClass ?>" style="<?= $isVoid ? 'text-decoration: line-through; opacity: 0.5;' : '' ?>">
                                    <?= $sign ?> <?= number_format($trx['amount'], 0, ',', '.') ?>
                                </td>
                                <td class="text-right mono" style="<?= $isVoid ? 'opacity: 0.5;' : '' ?>">
                                    <?= number_format($running, 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if(!$isVoid): ?>
                                        <button class="btn-del" onclick="confirmCancel('<?= base_url('/finance/cash-delete/'.$trx['id']) ?>')" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                                    <?php else: ?>
                                        <span style="font-size: 10px; font-weight: 800; color: var(--text-muted); background: #f1f5f9; padding: 4px 6px; border-radius: 4px;">VOID</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <tr class="row-total">
                        <td colspan="3" class="text-right">TOTAL SALDO (CASH + ATM)</td>
                        <td class="text-right mono" style="font-size: 16px;">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // System Initialization & SweetAlert Handler
    const swalBg = '#ffffff';
    const swalText = '#1e293b';

    document.addEventListener("DOMContentLoaded", function() {
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    // Form Tab Switcher (Transaksi vs Pindah Dana)
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.form-content').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tabBtn-' + tabName).classList.add('active');
        document.getElementById('form-' + tabName).classList.add('active');
        
        document.getElementById('mode_input_val').value = (tabName === 'biasa') ? 'transaksi' : 'mutasi';

        if(tabName === 'biasa') {
            document.querySelectorAll('.mutasi-input').forEach(el => el.disabled = true);
            document.querySelectorAll('input[name="type"]').forEach(el => el.disabled = false);
            document.querySelectorAll('input[name="metode"]').forEach(el => el.disabled = false);
        } else {
            document.querySelectorAll('.mutasi-input').forEach(el => el.disabled = false);
            document.querySelectorAll('input[name="type"]').forEach(el => el.disabled = true);
            document.querySelectorAll('input[name="metode"]').forEach(el => el.disabled = true);
        }
    }

    // Auto Format Rupiah Input
    function formatRupiah(angka) {
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }

    // Form Submit Handler (AJAX)
    document.getElementById('formKas').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        let amountInput = this.querySelector('input[name="amount"]');
        let rawValue = amountInput.value;
        amountInput.value = rawValue.replace(/\./g, ''); // Hapus titik sblm kirim db

        const form = this;
        const btn = document.getElementById('btnSubmitKas');
        const oriHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin"></i> MENYIMPAN...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            amountInput.value = rawValue; // Kembalikan ke UI jika gagal
            if(data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500, background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' }});
                form.reset();
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                Swal.fire({ icon: 'error', title: 'Ditolak!', text: data.message, confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' }});
                btn.disabled = false;
                btn.innerHTML = oriHtml;
            }
        })
        .catch(err => {
            amountInput.value = rawValue;
            Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: "Periksa koneksi internet Anda.", confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' }});
            btn.disabled = false;
            btn.innerHTML = oriHtml;
        });
    });

    // Delete / Void Transaksi
    function confirmCancel(url) {
        Swal.fire({
            title: 'Batalkan Transaksi?', 
            text: 'Jurnal akuntansi terkait juga akan di-void secara otomatis.', 
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#e2e8f0',
            confirmButtonText: 'Ya, Hapus Data', 
            cancelButtonText: 'Batal',
            background: swalBg, color: swalText,
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        })
    }

    // Refresh Halaman Berdasarkan Filter Tanggal
    function updateDateFilter(dateVal) {
        window.location.href = "<?= base_url('/finance/cash_index') ?>?tgl=" + dateVal;
    }
</script>

<?= $this->endSection() ?>