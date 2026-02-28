<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;}
    
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden; }
    
    /* TOOLBAR MASSAL STICKY */
    .mass-toolbar { padding: 15px 25px; background: rgba(59, 130, 246, 0.05); border-bottom: 1px solid rgba(59, 130, 246, 0.2); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;}
    html.dark .mass-toolbar { background: rgba(59, 130, 246, 0.1); }
    
    .selected-count { font-size: 14px; font-weight: 800; color: #3b82f6; display: flex; align-items: center; gap: 8px;}
    .selected-count span { background: #3b82f6; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 12px;}

    .btn-group { display: flex; gap: 10px; }
    .btn-mass { padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 800; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;}
    .btn-ship { background: #10b981; color: #fff; }
    .btn-ship:hover { background: #059669; }
    .btn-print { background: var(--text-main); color: var(--bg-base); }
    .btn-print:hover { opacity: 0.8; }

    /* TABEL DATA */
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 15px 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base);}
    td { padding: 15px 20px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; color: var(--text-main); vertical-align: middle;}
    tr:hover td { background: rgba(0,0,0,0.01); }
    
    /* CUSTOM CHECKBOX */
    .custom-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;}
    .courier-badge { font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 800; border: 1px solid var(--border-subtle); background: var(--bg-base); display: inline-block;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-stack"></i> Pusat Pemrosesan Massal</h1>
        <p>Proses ratusan resi knalpot Noric sekaligus dalam satu klik. Kurir tiba, barang siap.</p>
    </div>
</div>

<div class="table-card">
    <form action="<?= base_url('/warehouse/process_mass_action') ?>" method="post" id="massForm" target="_blank">
        <?= csrf_field() ?>
        
        <div class="mass-toolbar">
            <div class="selected-count">
                <i class="ph ph-check-square"></i> Terpilih: <span id="countDisplay">0</span>
            </div>
            <div class="btn-group">
                <button type="submit" name="action_type" value="ship" class="btn-mass btn-ship" onclick="removeTarget()">
                    <i class="ph ph-truck"></i> 1. Panggil Kurir (Pickup)
                </button>
                <button type="submit" name="action_type" value="print" class="btn-mass btn-print" onclick="setTarget()">
                    <i class="ph ph-printer"></i> 2. Cetak Resi Massal PDF
                </button>
            </div>
        </div>

        <div style="overflow-x: auto; max-height: 600px; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="checkAll" class="custom-checkbox">
                        </th>
                        <th>No. Pesanan (Shopee)</th>
                        <th>Pembeli</th>
                        <th>Kurir Ekspedisi</th>
                        <th>Waktu Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px;">
                                <i class="ph ph-check-circle" style="font-size: 40px; color: #10b981; margin-bottom: 10px;"></i>
                                <div style="font-size: 15px; font-weight: 800;">Gudang Bersih!</div>
                                <div style="font-size: 13px; color: var(--text-muted);">Tidak ada antrean pesanan massal.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_orders[]" value="<?= esc($o['order_sn']) ?>" class="custom-checkbox row-check">
                            </td>
                            <td style="font-family: monospace; font-weight: 800; color: var(--accent-main);"><?= esc($o['order_sn']) ?></td>
                            <td><i class="ph ph-user" style="color: var(--text-muted);"></i> <?= esc($o['buyer_username']) ?></td>
                            <td><span class="courier-badge"><?= esc($o['shipping_carrier']) ?></span></td>
                            <td style="font-size: 12px; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($o['order_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
    // Logika Checkbox JS Murni
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const countDisplay = document.getElementById('countDisplay');

    function updateCount() {
        const checkedCount = document.querySelectorAll('.row-check:checked').length;
        countDisplay.innerText = checkedCount;
    }

    checkAll.addEventListener('change', function() {
        rowChecks.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    rowChecks.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    // Mengatur target tab baru HANYA untuk cetak PDF
    function setTarget() {
        document.getElementById('massForm').target = '_blank';
    }
    function removeTarget() {
        document.getElementById('massForm').target = '_self';
    }
</script>

<?= $this->endSection() ?>