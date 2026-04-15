<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand: #4f46e5; --brand-dark: #3730a3; --brand-soft: rgba(79, 70, 229, 0.1);
        --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        --card-bg: #ffffff; --bg-main: #f8fafc; --text-main: #0f172a; --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    body { background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-main); }
    
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;}
    .title-wrap { display: flex; align-items: center; gap: 15px; }
    .title-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);}
    .page-header h1 { font-size: 26px; font-weight: 900; margin: 0 0 4px; letter-spacing: -0.5px;}
    .page-header p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 0;}

    .btn-top { background: var(--card-bg); border: 1px solid var(--border-color); padding: 12px 18px; border-radius: 12px; font-weight: 800; font-size: 13px; color: var(--text-main); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; cursor: pointer;}
    .btn-top:hover { border-color: var(--brand); color: var(--brand); transform: translateY(-2px);}

    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 25px; }
    @media (max-width: 900px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card { background: var(--card-bg); padding: 22px; border-radius: 20px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); position: relative; overflow: hidden;}
    .kpi-card::before { content:''; position:absolute; top:-30px; right:-30px; width:100px; height:100px; border-radius:50%; background: var(--brand-soft); z-index: 0;}
    .kpi-label { font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; z-index: 1;}
    .kpi-val { font-size: 24px; font-weight: 900; font-family: 'Space Mono', monospace; color: var(--text-main); z-index: 1; letter-spacing: -1px;}

    /* TAB NAVIGATION STYLING */
    .tab-nav { display: inline-flex; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 16px; border: 1px solid var(--border-color); margin-bottom: 25px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .tab-btn { padding: 12px 24px; font-size: 13px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;}
    .tab-btn.active { background: var(--card-bg); color: var(--brand); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
    .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .main-grid { display: grid; grid-template-columns: 1fr 2.5fr; gap: 25px; align-items: start;}
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    .bento-box { background: var(--card-bg); border-radius: 24px; border: 1px solid var(--border-color); padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);}
    .box-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed var(--border-color); padding-bottom: 15px; margin-bottom: 20px;}
    .box-header h3 { font-size: 16px; font-weight: 900; margin: 0; display: flex; align-items: center; gap: 8px; color: var(--text-main);}

    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; }
    .form-control { width: 100%; border: 1px solid var(--border-color); background: var(--bg-main); padding: 14px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; color: var(--text-main); outline: none; transition: 0.2s;}
    .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-soft); background: var(--card-bg);}
    
    .btn-submit { width: 100%; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border: none; padding: 16px; border-radius: 14px; font-size: 14px; font-weight: 900; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);}
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.1);}

    table { width: 100%; border-collapse: collapse; white-space: nowrap;}
    th { padding: 14px; text-align: left; font-size: 10px; font-weight: 900; color: var(--text-muted); border-bottom: 1px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;}
    td { padding: 14px; font-size: 13px; color: var(--text-main); border-bottom: 1px dashed var(--border-color); font-weight: 600; vertical-align: middle;}
    .mono { font-family: 'Space Mono', monospace; font-weight: 800; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .badge { padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; text-transform: uppercase;}
    .bg-green { background: var(--success-soft); color: var(--success); }
    .bg-red { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .bg-blue { background: var(--brand-soft); color: var(--brand); }
    .bg-void { background: #f1f5f9; color: var(--text-muted); text-decoration: line-through;}

    .btn-del { color: var(--danger); background: rgba(239, 68, 68, 0.1); border: none; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: 0.2s; text-decoration: none;}
    .btn-del:hover { background: var(--danger); color: #fff; transform: scale(1.05);}
</style>

<div class="page-header">
    <div class="title-wrap">
        <div class="title-icon"><i class="ph-fill ph-bank"></i></div>
        <div class="page-title">
            <h1>Modul Pendanaan & Investor</h1>
            <p>Kelola suntikan dana dari pemilik/investor, pengembalian modal, dan pembagian dividen.</p>
        </div>
    </div>
    <a href="<?= base_url('/accounting') ?>" class="btn-top"><i class="ph-bold ph-arrow-left"></i> Dasbor Akuntansi</a>
</div>

<div class="kpi-grid">
    <div class="kpi-card" style="border-bottom: 4px solid var(--success);">
        <div class="kpi-label">Total Dana Masuk (Injeksi)</div>
        <div class="kpi-val text-green">Rp <?= number_format($totalInjection, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--danger);">
        <div class="kpi-label">Total Penarikan (Withdrawal)</div>
        <div class="kpi-val text-red">Rp <?= number_format($totalWithdrawal, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--warning);">
        <div class="kpi-label">Total Dividen Dibagi</div>
        <div class="kpi-val text-orange">Rp <?= number_format($totalDividend, 0, ',', '.') ?></div>
    </div>
    <div class="kpi-card" style="border-bottom: 4px solid var(--brand); background: var(--brand-soft);">
        <div class="kpi-label" style="color: var(--brand);">Net Capital (Modal Tertahan)</div>
        <div class="kpi-val" style="color: var(--brand-dark);">Rp <?= number_format($netCapital, 0, ',', '.') ?></div>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('transaksi')" id="btnTabTransaksi"><i class="ph-bold ph-arrows-left-right"></i> Transaksi Pendanaan</button>
    <button class="tab-btn" onclick="switchTab('investor')" id="btnTabInvestor"><i class="ph-bold ph-users-three"></i> Daftar Investor / Pemilik</button>
</div>

<div id="tab-transaksi" class="tab-content active">
    <div class="main-grid">
        <div class="bento-box">
            <div class="box-header">
                <h3><i class="ph-bold ph-plus-circle"></i> Input Transaksi Baru</h3>
            </div>
            
            <form action="<?= base_url('investor/store_transaction') ?>" method="POST" id="formTrx">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" class="form-control mono" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Pilih Investor / Pemilik</label>
                    <select name="investor_id" class="form-control" required style="cursor: pointer;">
                        <option value="" disabled selected>-- Pilih Bos / Investor --</option>
                        <?php foreach($investors as $inv): ?>
                            <option value="<?= $inv['id'] ?>"><?= esc($inv['name']) ?> (Saham: <?= floatval($inv['equity_percentage']) ?>%)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Tipe Transaksi</label>
                        <select name="type" class="form-control" required>
                            <option value="INJECTION">Suntik Dana (Masuk)</option>
                            <option value="WITHDRAWAL">Penarikan (Keluar)</option>
                            <option value="DIVIDEND">Bagi Hasil / Dividen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sifat Dana</label>
                        <select name="category" class="form-control" required>
                            <option value="DEBT">Hutang (Akan dikembalikan)</option>
                            <option value="EQUITY">Modal Saham Permanen</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="BANK">Transfer Bank / ATM</option>
                        <option value="CASH">Tunai / Laci Cash</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="text" name="amount" class="form-control mono" placeholder="0" style="font-size: 18px; color: var(--brand);" required onkeyup="formatRupiah(this)">
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan / Tujuan</label>
                    <textarea name="description" class="form-control" placeholder="Contoh: Tambahan modal untuk beli bahan baku..." required></textarea>
                </div>

                <button type="submit" id="btnSimpanTrx" class="btn-submit"><i class="ph-bold ph-floppy-disk"></i> Simpan Transaksi</button>
            </form>
        </div>

        <div class="bento-box">
            <div class="box-header">
                <h3><i class="ph-bold ph-clock-counter-clockwise"></i> Riwayat Pendanaan Terakhir</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tgl & No. Ref</th>
                            <th>Investor</th>
                            <th>Keterangan</th>
                            <th class="text-center">Status Dana</th>
                            <th class="text-right">Masuk (Injeksi)</th>
                            <th class="text-right">Keluar (Tarik/Dividen)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($transactions)): ?>
                            <tr><td colspan="7" class="text-center" style="padding: 40px; color: var(--text-muted); font-style: italic;">Belum ada riwayat transaksi pendanaan.</td></tr>
                        <?php else: ?>
                            <?php foreach($transactions as $t): ?>
                                <?php $isVoid = $t['status'] === 'VOID'; ?>
                                <tr style="<?= $isVoid ? 'opacity: 0.5;' : '' ?>">
                                    <td>
                                        <div class="mono" style="font-size: 11px; color: var(--brand);"><?= esc($t['transaction_code']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;"><?= date('d M Y', strtotime($t['transaction_date'])) ?></div>
                                    </td>
                                    <td style="font-weight: 800;"><?= esc($t['investor_name']) ?></td>
                                    <td style="white-space: normal; min-width: 180px; <?= $isVoid ? 'text-decoration: line-through;' : '' ?>">
                                        <?= esc($t['description']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($t['type'] == 'INJECTION'): ?>
                                            <span class="badge bg-green">INJEKSI (<?= $t['category']=='DEBT' ? 'HUTANG' : 'MODAL' ?>)</span>
                                        <?php elseif($t['type'] == 'WITHDRAWAL'): ?>
                                            <span class="badge bg-red">PENARIKAN (<?= $t['category']=='DEBT' ? 'HUTANG' : 'MODAL' ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-blue">DIVIDEN</span>
                                        <?php endif; ?>
                                        <div style="font-size: 9px; margin-top: 4px; color: var(--text-muted); font-weight: 800;"><i class="ph-fill ph-<?= $t['payment_method']=='BANK' ? 'credit-card' : 'wallet' ?>"></i> <?= esc($t['payment_method']) ?></div>
                                    </td>
                                    <td class="text-right mono text-green" style="<?= $isVoid ? 'text-decoration: line-through;' : '' ?>">
                                        <?= $t['type'] == 'INJECTION' ? '+ ' . number_format($t['amount'], 0, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-right mono text-red" style="<?= $isVoid ? 'text-decoration: line-through;' : '' ?>">
                                        <?= $t['type'] != 'INJECTION' ? '- ' . number_format($t['amount'], 0, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(!$isVoid): ?>
                                            <a href="<?= base_url('investor/void_transaction/'.$t['id']) ?>" onclick="return confirm('Membatalkan (VOID) transaksi ini akan menghapus pengaruhnya di jurnal akuntansi. Lanjutkan?')" class="btn-del" title="Void Transaksi"><i class="ph-bold ph-x"></i></a>
                                        <?php else: ?>
                                            <span class="badge bg-void">VOID</span>
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
</div>

<div id="tab-investor" class="tab-content">
    <div class="bento-box">
        <div class="box-header">
            <h3><i class="ph-bold ph-users-three"></i> Daftar Investor & Pemegang Saham</h3>
            <button type="button" onclick="document.getElementById('modalInvestor').classList.add('active'); document.body.style.overflow='hidden';" class="btn-top" style="padding: 8px 14px; background: var(--brand-soft); color: var(--brand); border: none;">+ Tambah Investor Baru</button>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="35%">Nama Investor / Pemilik</th>
                        <th width="25%">Kontak (No. Telp)</th>
                        <th width="20%" class="text-center">Porsi Saham (Equity)</th>
                        <th width="15%" class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($investors)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted); font-style: italic;">
                                Belum ada data pemegang saham yang terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($investors as $inv): ?>
                            <tr>
                                <td class="text-center mono"><?= $no++ ?></td>
                                <td>
                                    <div style="font-weight: 900; color: var(--text-main); font-size: 14px;">
                                        <?= esc($inv['name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: var(--text-muted); font-weight: 600; font-size: 12px;">
                                        <i class="ph-fill ph-phone"></i> <?= esc($inv['phone'] ?: 'Tidak ada data') ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span style="background: var(--brand-soft); color: var(--brand); padding: 6px 12px; border-radius: 10px; font-weight: 900; font-size: 15px; font-family: 'Space Mono', monospace; border: 1px dashed var(--brand);">
                                        <?= floatval($inv['equity_percentage']) ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if($inv['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-green"><i class="ph-bold ph-check"></i> AKTIF</span>
                                    <?php else: ?>
                                        <span class="badge bg-red"><i class="ph-bold ph-x"></i> NONAKTIF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
    <div style="display: flex; gap: 8px; justify-content: center;">
        <a href="<?= base_url('investor/toggle_status/'.$inv['id']) ?>" 
           class="btn-top" 
           style="padding: 6px 10px; font-size: 14px; background: var(--bg-main);" 
           title="<?= $inv['status'] === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' ?>">
            <i class="ph-bold ph-power"></i>
        </a>

        <a href="<?= base_url('investor/delete_investor/'.$inv['id']) ?>" 
           onclick="return confirm('Hapus data investor ini secara permanen?')" 
           class="btn-del" 
           title="Hapus Permanen">
            <i class="ph-bold ph-trash"></i>
        </a>
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

<div class="modal-overlay" id="modalInvestor" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div class="bento-box" style="width: 100%; max-width: 450px; background: #fff; transform: translateY(20px); transition: all 0.3s ease; opacity: 0;" id="modalBox">
        <div class="box-header">
            <h3>Tambah Investor / Pemilik Baru</h3>
            <button type="button" onclick="closeModal()" style="background:none; border:none; font-size:18px; cursor:pointer; color: var(--text-muted); transition: 0.2s;" onmouseover="this.style.color='var(--danger)';" onmouseout="this.style.color='var(--text-muted)';"><i class="ph-bold ph-x"></i></button>
        </div>
        <form action="<?= base_url('investor/store_investor') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Telepon (Opsional)</label>
                <input type="text" name="phone" class="form-control" autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label">Persentase Saham / Bagi Hasil (%)</label>
                <input type="number" name="equity_percentage" class="form-control" step="0.01" min="0" max="100" placeholder="Contoh: 100 atau 50.5" value="0">
            </div>
            <button type="submit" class="btn-submit" style="margin-top: 25px;">Simpan Investor</button>
        </form>
    </div>
</div>

<script>
    // Logika Pengaturan Tabs
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        document.getElementById('btnTab' + tabId.charAt(0).toUpperCase() + tabId.slice(1)).classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Modal Handlers dengan Animasi
    function closeModal() {
        let box = document.getElementById('modalBox');
        box.style.transform = 'translateY(20px)';
        box.style.opacity = '0';
        setTimeout(() => {
            document.getElementById('modalInvestor').classList.remove('active');
            document.body.style.overflow = '';
        }, 200);
    }

    // Tangkap class active untuk trigger animasi muncul
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.classList.contains('active')) {
                setTimeout(() => {
                    let box = document.getElementById('modalBox');
                    box.style.transform = 'translateY(0)';
                    box.style.opacity = '1';
                }, 50);
            }
        });
    });
    observer.observe(document.getElementById('modalInvestor'), { attributes: true, attributeFilter: ['class'] });

    // SweetAlert Handlers
    document.addEventListener("DOMContentLoaded", function() {
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    // Rupiah Formatter
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

    // Loader on Submit
    document.getElementById('formTrx').addEventListener('submit', function(e) {
        let amountInput = this.querySelector('input[name="amount"]');
        amountInput.value = amountInput.value.replace(/\./g, ''); 
        let btn = document.getElementById('btnSimpanTrx');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin"></i> Memproses...';
    });
</script>
<style>.modal-overlay.active { display: flex !important; }</style>

<?= $this->endSection() ?>