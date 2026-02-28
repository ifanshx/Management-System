<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payslip - <?= esc($slip['employee_id']) ?> - <?= esc($slip['name']) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #e2e8f0; margin: 0; padding: 40px; color: #0f172a; }
        
        .slip-container { background: #fff; width: 100%; max-width: 850px; margin: 0 auto; padding: 40px 50px; border-radius: 8px; box-shadow: 0 15px 30px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        
        /* Watermark Logo */
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.03; width: 400px; height: auto; pointer-events: none; z-index: 1;}
        .content-wrapper { position: relative; z-index: 2; }

        /* HEADER */
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 25px; align-items: flex-end;}
        .brand-area { display: flex; align-items: center; gap: 15px;}
        .brand-logo { width: 50px; height: auto; }
        .company-name { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase; margin-bottom: 2px;}
        .company-sub { font-size: 11px; color: #475569; font-weight: 600; line-height: 1.4;}
        
        .title-area { text-align: right; }
        .title-text { font-size: 22px; font-weight: 800; letter-spacing: 4px; color: #0f172a; margin-bottom: 5px; text-transform: uppercase;}
        .confidential-badge { display: inline-block; border: 1px solid #ef4444; color: #ef4444; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; letter-spacing: 1px; margin-bottom: 8px;}
        .period-text { font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 4px 10px; border-radius: 4px; border: 1px solid #e2e8f0;}

        /* EMPLOYEE INFO */
        .emp-info { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; font-size: 12px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1;}
        .emp-info div strong { display: block; font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;}
        .emp-info div span { font-weight: 800; font-size: 13px; color: #0f172a;}
        .font-mono { font-family: 'Space Mono', monospace; font-size: 12px !important; }

        /* GRID DUA KOLOM PENDAPATAN & POTONGAN */
        .financial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 25px;}
        
        .section-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #fff;}
        .section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px; color: #1e293b; letter-spacing: 1px;}
        
        .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 12px; font-weight: 600; color: #475569; align-items: center;}
        .row span:last-child { color: #0f172a; text-align: right;}
        
        /* Highlight Totals */
        .row-total { display: flex; justify-content: space-between; padding-top: 10px; margin-top: 15px; border-top: 1px dashed #cbd5e1; font-weight: 800; font-size: 13px; color: #0f172a;}
        .text-green { color: #16a34a !important; }
        .text-red { color: #dc2626 !important; }

        /* TAKE HOME PAY */
        .thp-box { background: #0f172a; color: #fff; padding: 20px 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);}
        .thp-label { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; opacity: 0.9;}
        .thp-amount { font-size: 28px; font-weight: 800; color: #10b981; text-shadow: 0 2px 4px rgba(0,0,0,0.3);}

        /* FOOTER & TTD */
        .signature-area { display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px; font-weight: 700; text-align: center; color: #0f172a;}
        .sig-box { width: 200px; }
        .sig-title { margin-bottom: 70px; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .sig-name { text-decoration: underline; text-transform: uppercase; margin-bottom: 4px;}
        .sig-role { font-size: 10px; color: #64748b; font-weight: 600;}

        .system-footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 15px; font-family: 'Space Mono', monospace;}

        /* PRINT STYLES */
        .no-print { text-align: right; margin-bottom: 20px; }
        .btn-print { background: #2563eb; color: #fff; border: none; padding: 10px 20px; font-weight: 700; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit;}
        .btn-print:hover { background: #1d4ed8; }

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

<div class="slip-container">
    
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256"><path d="M224,96V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H72V40a8,8,0,0,1,8-8H176a8,8,0,0,1,8,8V88h32A8,8,0,0,1,224,96ZM88,48V88h80V48ZM208,104H48V192H208Zm-32,48H80a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>
            Cetak Dokumen
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
            <div><strong>Departemen / Jabatan</strong><span><?= esc($slip['department']) ?> / <?= esc($slip['position']) ?></span></div>
            <div><strong>Kehadiran (Absensi)</strong><span><?= $slip['total_present'] ?> Hari Masuk</span></div>
        </div>

        <div class="financial-grid">
            
            <div class="section-box">
                <div class="section-title">Pendapatan (Earnings)</div>
                
                <div class="row">
                    <span>Gaji Pokok (<?= esc($slip['salary_type']) ?>)</span> 
                    <span class="font-mono">Rp <?= number_format($slip['basic_salary'], 0, ',', '.') ?></span>
                </div>
                
                <?php if($slip['position_allowance'] > 0): ?>
                <div class="row">
                    <span>Tunjangan Jabatan</span> 
                    <span class="font-mono">Rp <?= number_format($slip['position_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($slip['meal_allowance'] > 0): ?>
                <div class="row">
                    <span>Uang Makan (x<?= $slip['total_present'] ?> Kehadiran)</span> 
                    <span class="font-mono">Rp <?= number_format($slip['meal_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($slip['transport_allowance'] > 0): ?>
                <div class="row">
                    <span>Tunjangan Transportasi</span> 
                    <span class="font-mono">Rp <?= number_format($slip['transport_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($slip['overtime_pay'] > 0): ?>
                <div class="row">
                    <span>Lembur (<?= floor($slip['total_overtime_minutes']/60) ?> Jam)</span> 
                    <span class="font-mono">Rp <?= number_format($slip['overtime_pay'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php $totalEarning = $slip['basic_salary'] + $slip['position_allowance'] + $slip['meal_allowance'] + $slip['transport_allowance'] + $slip['overtime_pay']; ?>
                
                <div class="row-total text-green">
                    <span>Total Pendapatan (A)</span> 
                    <span class="font-mono">Rp <?= number_format($totalEarning, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="section-box">
                <div class="section-title">Potongan (Deductions)</div>
                
                <?php if($slip['late_penalty'] > 0): ?>
                <div class="row">
                    <span>Denda Telat (<?= $slip['total_late_minutes'] ?> Menit)</span> 
                    <span class="font-mono text-red">- Rp <?= number_format($slip['late_penalty'], 0, ',', '.') ?></span>
                </div>
                <?php else: ?>
                <div class="row">
                    <span>Denda Telat</span> 
                    <span class="font-mono">- Rp 0</span>
                </div>
                <?php endif; ?>
                
                <?php if($slip['bpjs_deduction'] > 0): ?>
                <div class="row">
                    <span>Asuransi (BPJS Kes & TK)</span> 
                    <span class="font-mono text-red">- Rp <?= number_format($slip['bpjs_deduction'], 0, ',', '.') ?></span>
                </div>
                <?php else: ?>
                <div class="row">
                    <span>Asuransi (BPJS Kes & TK)</span> 
                    <span class="font-mono">- Rp 0</span>
                </div>
                <?php endif; ?>

                <div class="row">
                    <span>Potongan Lain-lain</span> 
                    <span class="font-mono">- Rp 0</span>
                </div>

                <?php $totalDeduction = $slip['late_penalty'] + $slip['bpjs_deduction']; ?>
                
                <div style="flex-grow: 1;"></div> 

                <div class="row-total text-red" style="margin-top: 40px;">
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
                <div class="sig-role">NIK: <?= esc($slip['employee_id']) ?></div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh</div>
                <div class="sig-name">Departemen HRD</div>
                <div class="sig-role"><?= esc($company['company_name'] ?? 'NAMA PERUSAHAAN') ?></div>
            </div>
        </div>

        <div class="system-footer">
            Dokumen ini dicetak otomatis dari Sistem Manajemen ERP pada <?= date('d/m/Y H:i:s') ?>.<br>
            Ref Code: <?= esc($slip['payroll_code']) ?> | Hak Cipta &copy; <?= date('Y') ?> <?= esc($company['app_name'] ?? 'Sistem ERP') ?>
        </div>
    </div>
</div>

</body>
</html>