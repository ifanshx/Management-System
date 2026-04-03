<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= esc($slip['employee_id']) ?> - <?= esc($slip['name']) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #e2e8f0; margin: 0; padding: 20px; color: #0f172a; }
        
        .slip-container { background: #fff; width: 100%; max-width: 800px; margin: 0 auto; padding: 35px 45px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.03; width: 400px; height: auto; pointer-events: none; z-index: 1;}
        .content-wrapper { position: relative; z-index: 2; }

        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #0f172a; padding-bottom: 15px; margin-bottom: 25px; align-items: flex-end;}
        .brand-area { display: flex; align-items: center; gap: 15px;}
        .brand-logo { width: 55px; height: auto; }
        .company-name { font-size: 22px; font-weight: 900; letter-spacing: -0.5px; text-transform: uppercase; margin-bottom: 2px;}
        .company-sub { font-size: 11px; color: #475569; font-weight: 600;}
        
        .title-area { text-align: right; }
        .title-text { font-size: 20px; font-weight: 900; letter-spacing: 4px; color: #0f172a; margin-bottom: 4px; text-transform: uppercase;}
        .confidential-badge { display: inline-block; border: 1px solid #ef4444; color: #ef4444; font-size: 9px; font-weight: 900; padding: 3px 8px; border-radius: 4px; letter-spacing: 1px; margin-bottom: 6px;}
        .period-text { font-size: 11px; font-weight: 800; color: #475569; background: #f1f5f9; padding: 5px 10px; border-radius: 4px; border: 1px solid #e2e8f0;}

        .emp-info { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 30px; font-size: 12px; background: #f8fafc; padding: 18px; border-radius: 6px; border: 1px solid #cbd5e1;}
        .emp-info div strong { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;}
        .emp-info div span { font-weight: 800; font-size: 13px; color: #0f172a;}
        
        .font-mono { font-family: 'Space Mono', monospace; font-size: 12px !important; }
        .font-small { font-size: 10px; color: #64748b; font-weight: 500; display: block; margin-top: 2px;}

        .financial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;}
        .section-box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 0; background: #fff; overflow: hidden;}
        .section-title { font-size: 12px; font-weight: 900; text-transform: uppercase; background: #f1f5f9; padding: 10px 15px; border-bottom: 1px solid #cbd5e1; color: #0f172a; letter-spacing: 1px;}
        
        .row-group { padding: 15px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 12px; font-weight: 600; color: #334155; align-items: flex-start;}
        .row span:last-child { color: #0f172a; text-align: right; font-weight: 700;}
        
        .row-total { display: flex; justify-content: space-between; padding: 12px 15px; border-top: 1px solid #cbd5e1; font-weight: 900; font-size: 13px; color: #0f172a; background: #f8fafc;}
        .text-green { color: #16a34a !important; }
        .text-red { color: #dc2626 !important; }

        .thp-box { background: #0f172a; color: #fff; padding: 20px 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.15);}
        .thp-label { font-size: 13px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; opacity: 0.9;}
        .thp-amount { font-size: 26px; font-weight: 900; color: #10b981; text-shadow: 0 2px 4px rgba(0,0,0,0.3);}

        .signature-area { display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; font-weight: 700; text-align: center; color: #0f172a;}
        .sig-box { width: 200px; }
        .sig-title { margin-bottom: 60px; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;}
        .sig-name { text-decoration: underline; text-transform: uppercase; margin-bottom: 4px; font-size: 13px;}
        .sig-role { font-size: 10px; color: #64748b; font-weight: 600;}

        .system-footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 15px; font-family: 'Space Mono', monospace;}

        .no-print { text-align: right; margin-bottom: 15px; }
        .btn-print { background: #2563eb; color: #fff; border: none; padding: 10px 20px; font-size: 12px; font-weight: 800; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; transition: 0.2s;}
        .btn-print:hover { background: #1d4ed8; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);}

        @media print {
            body { background: #fff; padding: 0; }
            .slip-container { box-shadow: none; padding: 0; max-width: 100%; border: none;}
            .no-print { display: none; }
            .thp-box { background: #f1f5f9; color: #0f172a; border: 2px solid #0f172a; box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .thp-amount { color: #0f172a; text-shadow: none;}
        }
    </style>
</head>
<body>

<?php
// HITUNG LOGIKA KASBON & TUNJANGAN UNTUK STRUK
$totalHadir = (int)$slip['total_present'];

$empMealRate = (float)$slip['master_meal'];
$netMealSaved = (float)$slip['meal_allowance'];
$grossMeal = $empMealRate * $totalHadir; 
$mealDeduction = $grossMeal - $netMealSaved; 

$empTransportRate = (float)$slip['master_transport'];
$empOvertimeRate = (float)$slip['master_overtime'];
$overtimeHours = floor($slip['total_overtime_minutes'] / 60);

$cashAdvance = (float)$slip['cash_advance'];
$totalKasbonCombined = $cashAdvance + $mealDeduction; 
?>

<div class="slip-container">
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256"><path d="M224,96V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H72V40a8,8,0,0,1,8-8H176a8,8,0,0,1,8,8V88h32A8,8,0,0,1,224,96ZM88,48V88h80V48ZM208,104H48V192H208Zm-32,48H80a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>
            Cetak Struk Resmi
        </button>
    </div>

    <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')); ?>" class="watermark" alt="Watermark">

    <div class="content-wrapper">
        <div class="header">
            <div class="brand-area">
                <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')); ?>" class="brand-logo" alt="Logo">
                <div>
                    <div class="company-name"><?= esc($company['company_name'] ?? 'NAMA PERUSAHAAN') ?></div>
                    <div class="company-sub"><?= esc($company['address'] ?? 'Alamat Perusahaan') ?> | Telp: <?= esc($company['phone'] ?? '-') ?></div>
                </div>
            </div>
            <div class="title-area">
                <div class="confidential-badge">STRICTLY CONFIDENTIAL</div>
                <div class="title-text">PAYSLIP</div>
                <div class="period-text">PERIODE: <?= date('d M Y', strtotime($slip['period_start'])) ?> - <?= date('d M Y', strtotime($slip['period_end'])) ?></div>
            </div>
        </div>

        <div class="emp-info">
            <div><strong>Nama Karyawan</strong><span><?= esc($slip['name']) ?></span></div>
            <div><strong>ID Pekerja (NIK)</strong><span class="font-mono"><?= esc($slip['employee_id']) ?></span></div>
            <div><strong>Siklus / Tipe</strong><span><?= esc($slip['employee_status']) ?> (<?= esc($slip['salary_type']) ?>)</span></div>
            <div><strong>Kehadiran</strong><span><?= $totalHadir ?> Hari Kerja (HK)</span></div>
        </div>

        <div class="financial-grid">
            
            <div class="section-box">
                <div class="section-title text-green">Penerimaan Hak (Earnings)</div>
                
                <div class="row-group">
                    <?php if($slip['employee_status'] === 'Tetap' || $slip['employee_status'] === 'Magang'): ?>
                        <div class="row">
                            <span>
                                Gaji Pokok
                                <?php if($slip['salary_type'] !== 'Bulanan') echo "<span class='font-small'>(Rp ".number_format($slip['master_basic'],0,',','.')." x {$totalHadir} HK)</span>"; ?>
                            </span> 
                            <span class="font-mono">Rp <?= number_format($slip['basic_salary'], 0, ',', '.') ?></span>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <span>
                                Upah Borongan
                                <span class="font-small">(Hasil Rekapitulasi Produksi)</span>
                            </span> 
                            <span class="font-mono">Rp <?= number_format($slip['borongan_pay'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <span>Tunjangan Jabatan</span> 
                        <span class="font-mono">Rp <?= number_format($slip['position_allowance'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="row">
                        <span>
                            Uang Makan
                            <?php if($empMealRate > 0): ?>
                                <span class="font-small">(Rp <?= number_format($empMealRate,0,',','.') ?> x <?= $totalHadir ?> HK)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono">Rp <?= number_format($grossMeal, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="row">
                        <span>
                            Uang Transport
                            <?php if($empTransportRate > 0): ?>
                                <span class="font-small">(Rp <?= number_format($empTransportRate,0,',','.') ?> x <?= $totalHadir ?> HK)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono">Rp <?= number_format($slip['transport_allowance'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="row">
                        <span>
                            Uang Lembur
                            <?php if($slip['total_overtime_minutes'] > 0): ?>
                                <span class="font-small">(Tarif Lembur: Rp <?= number_format($empOvertimeRate,0,',','.') ?> / Jam)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono">Rp <?= number_format($slip['overtime_pay'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <?php $totalEarning = $slip['basic_salary'] + $slip['borongan_pay'] + $slip['position_allowance'] + $grossMeal + $slip['transport_allowance'] + $slip['overtime_pay']; ?>
                
                <div class="row-total text-green">
                    <span>Total Pendapatan (A)</span> 
                    <span class="font-mono">Rp <?= number_format($totalEarning, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="section-box">
                <div class="section-title text-red">Kewajiban Potongan (Deductions)</div>
                
                <div class="row-group">
                    <div class="row">
                        <span>
                            Kasbon (Pinjaman + Mkn)
                            <?php if($totalKasbonCombined > 0): ?>
                                <span class="font-small">(Pabrik: Rp <?= number_format($cashAdvance,0,',','.') ?> | Makan: Rp <?= number_format($mealDeduction,0,',','.') ?>)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono text-red">- Rp <?= number_format($totalKasbonCombined, 0, ',', '.') ?></span>
                    </div>

                    <div class="row">
                        <span>
                            Denda Telat
                            <?php if($slip['total_late_minutes'] > 0): ?>
                                <span class="font-small">(Total Telat: <?= $slip['total_late_minutes'] ?> Menit)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono text-red">- Rp <?= number_format($slip['late_penalty'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="row">
                        <span>
                            BPJS
                            <span class="font-small">(Kesehatan & Ketenagakerjaan)</span>
                        </span> 
                        <span class="font-mono text-red">- Rp <?= number_format($slip['bpjs_deduction'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <?php $totalDeduction = $slip['late_penalty'] + $slip['bpjs_deduction'] + $totalKasbonCombined; ?>
                
                <div style="flex-grow: 1;"></div> 

                <div class="row-total text-red">
                    <span>Total Potongan (B)</span> 
                    <span class="font-mono">Rp <?= number_format($totalDeduction, 0, ',', '.') ?></span>
                </div>
            </div>

        </div>

        <div class="thp-box">
            <div class="thp-label">Take Home Pay (A - B)</div>
            <div class="thp-amount font-mono">Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?></div>
        </div>

        <div class="signature-area">
            <div class="sig-box">
                <div class="sig-title">Penerima / Karyawan</div>
                <div class="sig-name"><?= esc($slip['name']) ?></div>
                <div class="sig-role">Metode: <b><?= strtoupper(esc($slip['payment_method'])) ?></b></div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh</div>
                <div class="sig-name">Departemen Keuangan</div>
                <div class="sig-role"><?= esc($company['company_name'] ?? 'NAMA PERUSAHAAN') ?></div>
            </div>
        </div>

        <div class="system-footer">
            Dokumen sah dan dicetak otomatis dari Sistem ERP pada <?= date('d/m/Y H:i:s') ?> WIB.<br>
            Ref: <?= esc($slip['payroll_code']) ?> | Hak Cipta &copy; <?= date('Y') ?> <?= esc($company['app_name'] ?? 'Sistem ERP') ?>
        </div>
    </div>
</div>

</body>
</html>