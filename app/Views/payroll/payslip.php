<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= esc($slip['name'] ?? 'Unknown') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700;900&display=swap" rel="stylesheet">
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

        .no-print-area { 
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
        .row span:first-child { flex: 1; padding-right: 15px; }
        .row span:last-child { color: var(--text-dark); text-align: right; font-weight: 800; white-space: nowrap;}
        
        .row-total { 
            display: flex; 
            justify-content: space-between; 
            padding: 16px 18px; 
            border-top: 1px solid var(--border-color); 
            font-weight: 900; 
            font-size: 14px; 
            background: #f8fafc;
            margin-top: auto;
        }

        .text-green { color: var(--success-dark) !important; }
        .text-red { color: var(--danger) !important; }

        /* TABLES */
        .table-wrapper { width: 100%; overflow-x: auto; padding: 0 18px 18px 18px; }
        .modern-table { width: 100%; border-collapse: collapse; font-size: 11px; text-align: left; }
        .modern-table th { background: #f8fafc; padding: 8px 12px; border: 1px solid #cbd5e1; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;}
        .modern-table td { padding: 8px 12px; border: 1px solid var(--border-color); font-weight: 600; color: var(--text-dark);}
        .modern-table.table-red th { background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .modern-table.table-red td { border-color: rgba(239, 68, 68, 0.2); }
        
        .text-right { text-align: right !important; }
        
        .borongan-title { 
            font-size: 11px; 
            font-weight: 800; 
            color: var(--text-muted); 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            padding: 0 18px 10px 18px;
            margin-top: -5px;
        }

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
            
            .signature-area { flex-direction: column; gap: 40px; align-items: center; margin-top: 40px; padding: 0;}
            .sig-title { margin-bottom: 50px; }
        }

        @media (max-width: 480px) {
            .emp-info { grid-template-columns: 1fr; text-align: center; }
            .emp-info div strong { margin-bottom: 2px; }
            .row { flex-direction: column; gap: 4px; }
            .row span:last-child { text-align: left; }
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
            
            .no-print-area { display: none !important; }
            
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

<div class="no-print-area">
    <button onclick="window.print()" class="btn-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M224,96V200a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V96a8,8,0,0,1,8-8H72V40a8,8,0,0,1,8-8H176a8,8,0,0,1,8,8V88h32A8,8,0,0,1,224,96ZM88,48V88h80V48ZM208,104H48V192H208Zm-32,48H80a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>
        Cetak Slip Gaji
    </button>
</div>

<div class="slip-container">
    <?php if(!empty($company['logo_path'])): ?>
        <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" class="watermark" alt="Watermark">
    <?php endif; ?>

    <div class="content-wrapper">
        <div class="header">
            <div class="brand-area">
                <?php if(!empty($company['logo_path'])): ?>
                    <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'])) ?>" class="brand-logo" alt="Logo">
                <?php endif; ?>
                <div>
                    <div class="company-name"><?= esc($company['company_name'] ?? 'Noric Group') ?></div>
                    <div class="company-sub"><?= esc($company['address'] ?? '') ?></div>
                </div>
            </div>
            <div class="title-area">
                <div class="confidential-badge">STRICTLY CONFIDENTIAL</div>
                <div class="title-text">PAYSLIP</div>
                <div class="period-text">PERIODE: <?= date('d M Y', strtotime($slip['period_start'] ?? 'now')) ?> - <?= date('d M Y', strtotime($slip['period_end'] ?? 'now')) ?></div>
            </div>
        </div>

        <div class="emp-info">
            <div><strong>Nama Karyawan</strong><span><?= esc($slip['name'] ?? '-') ?></span></div>
            <div><strong>ID Pekerja (NIK)</strong><span class="font-mono"><?= esc($slip['employee_id'] ?? '-') ?></span></div>
            <div><strong>Siklus / Tipe</strong><span><?= esc($slip['employee_status'] ?? '-') ?> (<?= esc($slip['salary_type'] ?? '-') ?>)</span></div>
            <div><strong>Kehadiran</strong><span><?= esc($slip['total_present'] ?? 0) ?> Hari</span></div>
        </div>

        <div class="financial-grid">
            
            <div class="section-box">
                <div class="section-title text-green">Penghasilan (Earnings)</div>
                
                <div class="row-group">
                    <?php if(($slip['basic_salary'] ?? 0) > 0): ?>
                        <div class="row">
                            <span>Gaji Pokok</span> 
                            <span class="font-mono">Rp <?= number_format((float)$slip['basic_salary'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if(($slip['borongan_pay'] ?? 0) > 0): ?>
                        <div class="row">
                            <span>
                                Upah Borongan
                                <?php if(($slip['employee_status'] ?? '') === 'Tetap'): ?>
                                    <span class="font-small" style="color: var(--success-dark);">(Mandor / Tunj. Produksi)</span>
                                <?php else: ?>
                                    <span class="font-small">(Hasil Rekap Produksi)</span>
                                <?php endif; ?>
                            </span> 
                            <span class="font-mono">Rp <?= number_format((float)$slip['borongan_pay'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if(($slip['position_allowance'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Tunjangan Jabatan</span> 
                        <span class="font-mono">Rp <?= number_format((float)$slip['position_allowance'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['meal_allowance'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Uang Makan (Total Hak)</span> 
                        <span class="font-mono">Rp <?= number_format((float)$slip['meal_allowance'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['transport_allowance'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Uang Transport</span> 
                        <span class="font-mono">Rp <?= number_format((float)$slip['transport_allowance'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['overtime_pay'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Uang Lembur (Tetap)</span> 
                        <span class="font-mono">Rp <?= number_format((float)$slip['overtime_pay'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if(!empty($boronganDetails)): ?>
                <div class="borongan-title">Rincian Pekerjaan Borongan</div>
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Nama Produk (Tipe)</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Tarif</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($boronganDetails as $bd): ?>
                            <tr>
                                <td>
                                    <b style="color: var(--text-dark);"><?= esc($bd['item_name'] ?? 'Unknown') ?></b><br>
                                    <small style="color: var(--text-muted); font-weight: 600;"><?= esc($bd['item_type'] ?? '') ?> - <?= esc($bd['operation_name'] ?? '') ?></small>
                                </td>
                                <td class="text-right"><?= esc($bd['total_qty'] ?? 0) ?></td>
                                <td class="text-right"><?= number_format((float)($bd['wage_per_piece'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right font-mono" style="color: var(--success-dark);">Rp <?= number_format((float)($bd['total_wage'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php 
                    $totalEarning = (float)($slip['basic_salary'] ?? 0) + (float)($slip['borongan_pay'] ?? 0) + (float)($slip['position_allowance'] ?? 0) + (float)($slip['meal_allowance'] ?? 0) + (float)($slip['transport_allowance'] ?? 0) + (float)($slip['overtime_pay'] ?? 0); 
                ?>
                <div class="row-total text-green">
                    <span>Total Penghasilan (A)</span> 
                    <span class="font-mono">Rp <?= number_format($totalEarning, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="section-box">
                <div class="section-title text-red">Potongan (Deductions)</div>
                
                <div class="row-group">
                    <?php if(($slip['late_penalty'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Denda Keterlambatan</span>
                        <span class="font-mono text-red">- Rp <?= number_format((float)$slip['late_penalty'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['bpjs_deduction'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>BPJS / Asuransi</span>
                        <span class="font-mono text-red">- Rp <?= number_format((float)$slip['bpjs_deduction'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['meal_taken_deduction'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>
                            Potongan Makan (Telah Diambil)
                            <span class="font-small">(Tarik Tunai via Laci)</span>
                        </span>
                        <span class="font-mono text-red">- Rp <?= number_format((float)$slip['meal_taken_deduction'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if(($slip['cash_advance'] ?? 0) > 0): ?>
                    <div class="row">
                        <span>Potongan Kasbon / Pinjaman</span>
                        <span class="font-mono text-red">- Rp <?= number_format((float)$slip['cash_advance'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if(!empty($kasbonDetails)): ?>
                <div class="borongan-title" style="color: var(--danger);">Rincian Potongan Kasbon</div>
                <div class="table-wrapper">
                    <table class="modern-table table-red">
                        <thead>
                            <tr>
                                <th>Peminjam & Keterangan</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kasbonDetails as $kd): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                                        <span style="background: rgba(239,68,68,0.15); color: #dc2626; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 900; letter-spacing: 0.5px;">
                                            <?= date('d/m/y', strtotime($kd['date'] ?? 'now')) ?>
                                        </span>
                                        <b style="color: var(--text-dark); font-size: 12px;"><?= esc($kd['emp_name'] ?? 'Tidak Diketahui') ?></b>
                                    </div>
                                    <small style="color: var(--text-muted); font-weight: 600; display: block; padding-left: 2px;">
                                        &#8627; <?= esc($kd['description'] ?? '') ?>
                                    </small>
                                </td>
                                <td class="text-right font-mono text-red" style="vertical-align: top; padding-top: 10px;">- <?= number_format((float)($kd['amount'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php 
                    $totalDeduction = (float)($slip['cash_advance'] ?? 0) + (float)($slip['late_penalty'] ?? 0) + (float)($slip['bpjs_deduction'] ?? 0) + (float)($slip['meal_taken_deduction'] ?? 0);
                ?>
                <div class="row-total text-red">
                    <span>Total Potongan (B)</span>
                    <span class="font-mono">Rp <?= number_format($totalDeduction, 0, ',', '.') ?></span>
                </div>
            </div>

        </div>

        <div class="thp-box">
            <div class="thp-label">Take Home Pay (A - B)</div>
            <div class="thp-amount">Rp <?= number_format((float)($slip['net_salary'] ?? 0), 0, ',', '.') ?></div>
        </div>

        <div class="signature-area">
            <div class="sig-box">
                <div class="sig-title">Penerima / Karyawan</div>
                <div class="sig-name"><?= esc($slip['name'] ?? '-') ?></div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Bagian Keuangan</div>
                <div class="sig-name">( ........................... )</div>
            </div>
        </div>

        <div class="system-footer">
            Dokumen sah dan dicetak otomatis dari Sistem ERP pada <?= date('d/m/Y H:i:s') ?> WIB.<br>
        </div>
    </div>
</div>

</body>
</html>