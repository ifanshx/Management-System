<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - <?= esc($slip['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #e5e7eb; padding: 20px; color: #111827; }
        .slip-container { background: #fff; width: 100%; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; }
        
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111827; padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; }
        .company-sub { font-size: 12px; color: #4b5563; }
        
        .title { text-align: right; }
        .title h2 { margin: 0; font-size: 20px; color: #111827; }
        .title p { margin: 5px 0 0; font-size: 12px; font-weight: 600; color: #4b5563; }

        .emp-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; font-size: 13px; }
        .emp-info div strong { display: block; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; margin-bottom: 3px;}
        .emp-info div span { font-weight: 700; font-size: 14px; }

        .section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; border-bottom: 1px dashed #d1d5db; padding-bottom: 5px; margin-bottom: 15px; margin-top: 25px; }

        .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; font-weight: 600; }
        .row.total { font-size: 15px; font-weight: 800; border-top: 1px solid #111827; padding-top: 10px; margin-top: 15px; }
        .row.grand-total { background: #f3f4f6; padding: 15px; border-radius: 8px; font-size: 18px; margin-top: 30px; align-items: center; border: 1px solid #d1d5db;}

        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }

        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #6b7280; border-top: 1px dashed #d1d5db; padding-top: 20px;}

        @media print {
            body { background: #fff; padding: 0; }
            .slip-container { box-shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="slip-container">
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #111827; color: #fff; border: none; padding: 10px 20px; font-family: inherit; font-weight: bold; border-radius: 6px; cursor: pointer;">Cetak PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="company-name">PT. NORIC RACING EXHAUST</div>
            <div class="company-sub">Manufaktur & Produksi Otomotif</div>
        </div>
        <div class="title">
            <h2>PAYSLIP (SLIP GAJI)</h2>
            <p>Periode: <?= date('d M Y', strtotime($slip['period_start'])) ?> - <?= date('d M Y', strtotime($slip['period_end'])) ?></p>
        </div>
    </div>

    <div class="emp-info">
        <div><strong>Nama Karyawan</strong><span><?= esc($slip['name']) ?></span></div>
        <div><strong>Nomor Induk (NIK)</strong><span><?= esc($slip['employee_id']) ?></span></div>
        <div><strong>Jabatan / Departemen</strong><span><?= esc($slip['position']) ?> / <?= esc($slip['department']) ?></span></div>
        <div><strong>Status Kehadiran</strong><span><?= $slip['total_present'] ?> Hari (Lembur: <?= floor($slip['total_overtime_minutes']/60) ?> Jam)</span></div>
    </div>

    <div class="section-title">Penerimaan (Earnings)</div>
    <div class="row"><span>Gaji Pokok (<?= esc($slip['salary_type']) ?>)</span> <span>Rp <?= number_format($slip['basic_salary'], 0, ',', '.') ?></span></div>
    
    <?php if($slip['position_allowance'] > 0): ?>
    <div class="row"><span>Tunjangan Jabatan</span> <span>Rp <?= number_format($slip['position_allowance'], 0, ',', '.') ?></span></div>
    <?php endif; ?>
    
    <?php if($slip['meal_allowance'] > 0): ?>
    <div class="row"><span>Uang Makan (x<?= $slip['total_present'] ?> Hari)</span> <span>Rp <?= number_format($slip['meal_allowance'], 0, ',', '.') ?></span></div>
    <?php endif; ?>
    
    <?php if($slip['transport_allowance'] > 0): ?>
    <div class="row"><span>Tunjangan Transportasi</span> <span>Rp <?= number_format($slip['transport_allowance'], 0, ',', '.') ?></span></div>
    <?php endif; ?>
    
    <?php if($slip['overtime_pay'] > 0): ?>
    <div class="row"><span>Uang Lembur (Overtime)</span> <span>Rp <?= number_format($slip['overtime_pay'], 0, ',', '.') ?></span></div>
    <?php endif; ?>

    <?php $totalEarning = $slip['basic_salary'] + $slip['position_allowance'] + $slip['meal_allowance'] + $slip['transport_allowance'] + $slip['overtime_pay']; ?>
    <div class="row total"><span>Total Penerimaan Kotor</span> <span class="text-green">Rp <?= number_format($totalEarning, 0, ',', '.') ?></span></div>

    <div class="section-title">Potongan (Deductions)</div>
    <?php if($slip['late_penalty'] > 0): ?>
    <div class="row"><span>Denda Keterlambatan (<?= $slip['total_late_minutes'] ?> Menit)</span> <span class="text-red">- Rp <?= number_format($slip['late_penalty'], 0, ',', '.') ?></span></div>
    <?php endif; ?>
    
    <?php if($slip['bpjs_deduction'] > 0): ?>
    <div class="row"><span>Iuran BPJS Kesehatan & TK</span> <span class="text-red">- Rp <?= number_format($slip['bpjs_deduction'], 0, ',', '.') ?></span></div>
    <?php endif; ?>

    <?php $totalDeduction = $slip['late_penalty'] + $slip['bpjs_deduction']; ?>
    <div class="row total"><span>Total Potongan</span> <span class="text-red">Rp <?= number_format($totalDeduction, 0, ',', '.') ?></span></div>

    <div class="row grand-total">
        <span style="text-transform: uppercase;">Take Home Pay (THP)</span> 
        <span style="font-size: 24px;">Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?></span>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; font-weight: 700; text-align: center;">
        <div>
            <div style="margin-bottom: 60px;">Penerima,</div>
            <div style="text-decoration: underline;"><?= esc($slip['name']) ?></div>
        </div>
        <div>
            <div style="margin-bottom: 60px;">Disetujui Oleh,</div>
            <div style="text-decoration: underline;">Manajemen HRD & Keuangan</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dibuat sah secara digital oleh sistem Noric Workspace. <br> Dokumen Rahasia (Confidential).
    </div>
</div>

</body>
</html>