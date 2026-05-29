<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= esc($slip['employee_id']) ?> - <?= esc($slip['name']) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Mono:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #334155;
            --success: #10b981;
            --success-dark: #059669;
            --danger: #ef4444;
            --bg-main: #f1f5f9;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0, #f8fafc); 
            min-height: 100vh;
            padding: 40px 20px; 
            color: var(--text-dark); 
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .no-print { 
            width: 100%; 
            max-width: 850px; 
            display: flex; 
            justify-content: flex-end; 
            margin-bottom: 20px; 
        }

        .btn-print { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8); 
            color: #fff; 
            border: none; 
            padding: 12px 24px; 
            font-size: 14px; 
            font-weight: 800; 
            border-radius: 10px; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 10px; 
            font-family: inherit; 
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
        }
        
        .btn-print:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.5);
        }
        
        .slip-container { 
            background: var(--bg-card); 
            width: 100%; 
            max-width: 850px; 
            padding: 45px 50px; 
            border-radius: 16px; 
            box-shadow: 0 20px 50px -10px rgba(0,0,0,0.1); 
            position: relative; 
            overflow: hidden; 
        }
        
        .watermark { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            opacity: 0.03; 
            width: 450px; 
            height: auto; 
            pointer-events: none; 
            z-index: 1;
            filter: grayscale(100%);
        }

        .content-wrapper { position: relative; z-index: 2; }

        .header { 
            display: flex; 
            justify-content: space-between; 
            border-bottom: 3px solid var(--primary); 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
            align-items: flex-end;
        }

        .brand-area { display: flex; align-items: center; gap: 18px;}
        .brand-logo { width: 65px; height: auto; border-radius: 8px;}
        .company-name { font-size: 24px; font-weight: 900; letter-spacing: -0.5px; text-transform: uppercase; margin-bottom: 4px; color: var(--primary);}
        .company-sub { font-size: 12px; color: var(--text-muted); font-weight: 600;}
        
        .title-area { text-align: right; }
        .title-text { font-size: 22px; font-weight: 900; letter-spacing: 5px; color: var(--primary); margin-bottom: 6px; text-transform: uppercase;}
        .confidential-badge { display: inline-block; border: 1px dashed var(--danger); color: var(--danger); background: rgba(239,68,68,0.05); font-size: 10px; font-weight: 900; padding: 4px 10px; border-radius: 6px; letter-spacing: 1px; margin-bottom: 8px;}
        .period-text { font-size: 12px; font-weight: 800; color: var(--primary-light); background: var(--bg-main); padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color);}

        .emp-info { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 15px; 
            margin-bottom: 35px; 
            background: #f8fafc; 
            padding: 20px; 
            border-radius: 10px; 
            border: 1px solid var(--border-color);
        }
        
        .emp-info div strong { display: block; font-size: 10px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-bottom: 6px;}
        .emp-info div span { font-weight: 800; font-size: 14px; color: var(--text-dark);}
        
        .font-mono { font-family: 'Space Mono', monospace; font-size: 13px !important; font-weight: 700; }
        .font-small { font-size: 11px; color: var(--text-muted); font-weight: 600; display: block; margin-top: 4px;}

        .financial-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
            margin-bottom: 35px;
        }

        .section-box { 
            border: 1px solid var(--border-color); 
            border-radius: 10px; 
            padding: 0; 
            background: #fff; 
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-title { 
            font-size: 13px; 
            font-weight: 900; 
            text-transform: uppercase; 
            background: var(--bg-main); 
            padding: 12px 18px; 
            border-bottom: 1px solid var(--border-color); 
            letter-spacing: 1px;
        }
        
        .row-group { padding: 20px 18px; flex-grow: 1; }
        
        .row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 16px; 
            font-size: 13px; 
            font-weight: 600; 
            color: var(--primary-light); 
            align-items: flex-start;
        }
        .row:last-child { margin-bottom: 0; }
        .row span:last-child { color: var(--text-dark); text-align: right; font-weight: 800;}
        
        .row-total { 
            display: flex; 
            justify-content: space-between; 
            padding: 16px 18px; 
            border-top: 1px solid var(--border-color); 
            font-weight: 900; 
            font-size: 14px; 
            background: #f8fafc;
        }

        .text-green { color: var(--success-dark) !important; }
        .text-red { color: var(--danger) !important; }

        .thp-box { 
            background: linear-gradient(135deg, var(--primary), #1e293b); 
            color: #fff; 
            padding: 25px 30px; 
            border-radius: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.3);
            border: 1px solid #334155;
        }
        
        .thp-label { font-size: 14px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; opacity: 0.9;}
        .thp-amount { font-size: 32px; font-weight: 900; color: var(--success); text-shadow: 0 2px 10px rgba(16, 185, 129, 0.4); font-family: 'Space Mono', monospace;}

        .signature-area { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 60px; 
            font-size: 13px; 
            font-weight: 700; 
            text-align: center; 
            color: var(--text-dark);
            padding: 0 20px;
        }
        
        .sig-box { width: 220px; }
        .sig-title { margin-bottom: 70px; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .sig-name { text-decoration: underline; text-transform: uppercase; margin-bottom: 6px; font-size: 14px; font-weight: 900;}
        .sig-role { font-size: 11px; color: var(--text-muted); font-weight: 600;}

        .system-footer { 
            margin-top: 50px; 
            text-align: center; 
            font-size: 11px; 
            color: #94a3b8; 
            border-top: 1px dashed var(--border-color); 
            padding-top: 20px; 
            font-family: 'Space Mono', monospace;
            line-height: 1.6;
        }

        /* =======================================================
           RESPONSIVE DESIGN (MOBILE & TABLET)
           ======================================================= */
        @media (max-width: 768px) {
            body { padding: 20px 10px; }
            .slip-container { padding: 30px 20px; border-radius: 12px; }
            .watermark { width: 250px; }
            
            .header { flex-direction: column; align-items: center; text-align: center; gap: 20px; border-bottom-width: 2px; padding-bottom: 25px; }
            .brand-area { flex-direction: column; gap: 10px; }
            .title-area { text-align: center; width: 100%; }
            .company-name { font-size: 20px; }
            
            .emp-info { grid-template-columns: 1fr 1fr; gap: 15px; padding: 15px;}
            .financial-grid { grid-template-columns: 1fr; gap: 20px; }
            
            .thp-box { flex-direction: column; gap: 15px; text-align: center; padding: 20px; }
            .thp-amount { font-size: 28px; }
            
            .signature-area { flex-direction: column; gap: 40px; align-items: center; margin-top: 40px; }
            .sig-title { margin-bottom: 50px; }
        }

        @media (max-width: 480px) {
            .emp-info { grid-template-columns: 1fr; text-align: center; }
            .emp-info div strong { margin-bottom: 2px; }
        }

        /* =======================================================
           PRINT MEDIA QUERIES (KERTAS A4)
           ======================================================= */
        @media print {
            @page { margin: 1.5cm; size: A4 portrait; }
            body { background: #fff; padding: 0; align-items: flex-start; }
            
            .slip-container { 
                box-shadow: none; 
                padding: 0; 
                max-width: 100%; 
                border: none; 
                border-radius: 0;
            }
            
            .no-print { display: none !important; }
            
            .thp-box { 
                background: #f8fafc !important; 
                color: #0f172a !important; 
                border: 2px solid #0f172a !important; 
                box-shadow: none !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            
            .thp-amount { color: #0f172a !important; text-shadow: none !important;}
            .thp-label { color: #0f172a !important;}
            
            .section-box { border-color: #cbd5e1 !important; break-inside: avoid; }
            .section-title { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .row-total { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            .watermark { opacity: 0.05 !important; filter: grayscale(100%) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;}
        }
    </style>
</head>
<body>

<?php
$totalHadir = (int)$slip['total_present'];

$empMealRate = (float)$slip['master_meal'];
$netMealSaved = (float)$slip['meal_allowance'];
$grossMeal = $empMealRate * $totalHadir; 
$mealDeduction = $grossMeal - $netMealSaved; 
$takenMealDays = ($empMealRate > 0) ? round($mealDeduction / $empMealRate) : 0;

$empTransportRate = (float)$slip['master_transport'];
$empOvertimeRate = (float)$slip['master_overtime'];
$overtimeHours = floor($slip['total_overtime_minutes'] / 60);

$cashAdvance = (float)$slip['cash_advance'];
$totalKasbonCombined = $cashAdvance + $mealDeduction; 

// Earning = Pokok + Borongan + Jabatan + Makan(Utuh) + Transport + SISA Lembur
$totalEarning = $slip['basic_salary'] + $slip['borongan_pay'] + $slip['position_allowance'] + $grossMeal + $slip['transport_allowance'] + $slip['overtime_pay'];
?>

<div class="no-print">
    <button onclick="window.print()" class="btn-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M224,96V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H72V40a8,8,0,0,1,8-8H176a8,8,0,0,1,8,8V88h32A8,8,0,0,1,224,96ZM88,48V88h80V48ZM208,104H48V192H208Zm-32,48H80a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>
        Cetak Slip Gaji
    </button>
</div>

<div class="slip-container">
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
                                <span class="font-small">(Hasil Rekap Produksi Tahapan)</span>
                            </span> 
                            <span class="font-mono">Rp <?= number_format($slip['borongan_pay'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <span>Tunjangan Jabatan / Posisi</span> 
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
                                <span class="font-small">(Siklus Tetap/Mingguan)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono">Rp <?= number_format($slip['transport_allowance'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="row">
                        <span>
                            Sisa Uang Lembur
                            <?php if($slip['total_overtime_minutes'] > 0): ?>
                                <span class="font-small">(Tarif Lembur: Rp <?= number_format($empOvertimeRate,0,',','.') ?> / Jam)</span>
                            <?php endif; ?>
                        </span> 
                        <span class="font-mono">Rp <?= number_format($slip['overtime_pay'], 0, ',', '.') ?></span>
                    </div>

                    <?php if(isset($slip['overtime_meal_allowance']) && $slip['overtime_meal_allowance'] > 0): ?>
                    <div class="row">
                        <span>
                            Uang Makan Lembur
                            <span class="font-small" style="color: var(--danger);">*(Telah Dibayarkan Tunai Harian)</span>
                        </span> 
                        <span class="font-mono" style="color: var(--text-muted);">Rp <?= number_format($slip['overtime_meal_allowance'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
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
                                <span class="font-small">(Pabrik/Lbr: Rp <?= number_format($cashAdvance,0,',','.') ?> | Makan: Rp <?= number_format($mealDeduction,0,',','.') ?>)</span>
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