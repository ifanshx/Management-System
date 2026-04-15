<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payslip - <?= esc($slip['name']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #e2e8f0; margin: 0; padding: 20px; color: #0f172a; }
        .slip-container { background: #fff; width: 100%; max-width: 800px; margin: 0 auto; padding: 35px 45px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #0f172a; padding-bottom: 15px; margin-bottom: 25px; }
        .brand-area { display: flex; align-items: center; gap: 15px;}
        .brand-logo { width: 55px; }
        .company-name { font-size: 22px; font-weight: 900; text-transform: uppercase; }
        .title-area { text-align: right; }
        .title-text { font-size: 20px; font-weight: 900; letter-spacing: 4px; }
        .period-text { font-size: 11px; font-weight: 800; background: #f1f5f9; padding: 5px 10px; border-radius: 4px; border: 1px solid #e2e8f0;}

        .emp-info { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 30px; font-size: 12px; background: #f8fafc; padding: 18px; border-radius: 6px; border: 1px solid #cbd5e1;}
        .emp-info div strong { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; margin-bottom: 4px;}
        .emp-info div span { font-weight: 800; font-size: 13px; }

        .financial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;}
        .section-box { border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; overflow: hidden; display: flex; flex-direction: column;}
        .section-title { font-size: 12px; font-weight: 900; text-transform: uppercase; background: #f1f5f9; padding: 10px 15px; border-bottom: 1px solid #cbd5e1; letter-spacing: 1px;}
        
        .row-group { padding: 15px; flex-grow: 1; }
        .row { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 12px; font-weight: 600; }
        .row-total { display: flex; justify-content: space-between; padding: 12px 15px; border-top: 1px solid #cbd5e1; font-weight: 900; font-size: 13px; background: #f8fafc; margin-top: auto;}
        
        .borongan-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10px;}
        .borongan-table th { background: #f8fafc; padding: 6px 8px; border: 1px solid #cbd5e1; text-align: left; font-weight: 800;}
        .borongan-table td { padding: 6px 8px; border: 1px solid #e2e8f0; font-weight: 600;}
        .text-right { text-align: right; }
        .borongan-title { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; border-bottom: 1px dashed #cbd5e1; padding-bottom: 5px; margin: 0 15px 10px 15px;}

        .thp-box { background: #0f172a; color: #fff; padding: 20px 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .thp-amount { font-size: 26px; font-weight: 900; color: #10b981; }

        @media print { .no-print { display: none; } body { background: #fff; padding: 0; } }
    </style>
</head>
<body>

<div class="slip-container">
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor:pointer;">Cetak Slip</button>
    </div>

    <div class="header">
        <div class="brand-area">
            <div>
                <div class="company-name"><?= esc($company['company_name']) ?></div>
                <div style="font-size: 11px;"><?= esc($company['address']) ?></div>
            </div>
        </div>
        <div class="title-area">
            <div class="title-text">PAYSLIP</div>
            <div class="period-text"><?= date('d M Y', strtotime($slip['period_start'])) ?> - <?= date('d M Y', strtotime($slip['period_end'])) ?></div>
        </div>
    </div>

    <div class="emp-info">
        <div><strong>Nama Karyawan</strong><span><?= esc($slip['name']) ?></span></div>
        <div><strong>ID Pekerja</strong><span><?= esc($slip['employee_id']) ?></span></div>
        <div><strong>Siklus</strong><span><?= esc($slip['employee_status']) ?> (<?= esc($slip['salary_type']) ?>)</span></div>
        <div><strong>Kehadiran</strong><span><?= $slip['total_present'] ?> Hari</span></div>
    </div>

    <div class="financial-grid">
        <div class="section-box">
            <div class="section-title" style="color: #16a34a;">Penghasilan</div>
            <div class="row-group">
                <?php if($slip['employee_status'] === 'Tetap' || $slip['employee_status'] === 'Magang'): ?>
                    <div class="row">
                        <span>Gaji Pokok</span> 
                        <span class="font-mono">Rp <?= number_format($slip['basic_salary'], 0, ',', '.') ?></span>
                    </div>
                <?php else: ?>
                    <div class="row" style="margin-bottom: 6px;">
                        <span>Upah Borongan (Produksi)</span> 
                        <span class="font-mono">Rp <?= number_format($slip['borongan_pay'], 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>

                <?php if($slip['position_allowance'] > 0): ?>
                <div class="row">
                    <span>Tunjangan Jabatan</span> 
                    <span class="font-mono">Rp <?= number_format($slip['position_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php if($slip['meal_allowance'] > 0): ?>
                <div class="row">
                    <span>Uang Makan (Sisa Bersih)</span> 
                    <span class="font-mono">Rp <?= number_format($slip['meal_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php if($slip['transport_allowance'] > 0): ?>
                <div class="row">
                    <span>Uang Transport</span> 
                    <span class="font-mono">Rp <?= number_format($slip['transport_allowance'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php if($slip['overtime_pay'] > 0): ?>
                <div class="row">
                    <span>Uang Lembur (Tetap)</span> 
                    <span class="font-mono">Rp <?= number_format($slip['overtime_pay'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if(!empty($boronganDetails)): ?>
            <div class="borongan-title">Rincian Pekerjaan</div>
            <div style="padding: 0 15px 15px 15px;">
                <table class="borongan-table">
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
                                <b><?= esc($bd['item_name']) ?></b><br>
                                <small style="color: #64748b;"><?= esc($bd['item_type']) ?> - <?= esc($bd['operation_name']) ?></small>
                            </td>
                            <td class="text-right"><?= $bd['total_qty'] ?></td>
                            <td class="text-right"><?= number_format($bd['wage_per_piece'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($bd['total_wage'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php 
                $totalEarning = $slip['basic_salary'] + $slip['borongan_pay'] + $slip['position_allowance'] + $slip['meal_allowance'] + $slip['transport_allowance'] + $slip['overtime_pay']; 
            ?>
            <div class="row-total" style="color: #16a34a;">
                <span>Total Penghasilan (A)</span>
                <span>Rp <?= number_format($totalEarning, 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="section-box">
            <div class="section-title" style="color: #dc2626;">Potongan</div>
            <div class="row-group">
                <div class="row">
                    <span>Denda Keterlambatan</span>
                    <span class="font-mono">- Rp <?= number_format($slip['late_penalty'], 0, ',', '.') ?></span>
                </div>

                <?php if($slip['bpjs_deduction'] > 0): ?>
                <div class="row">
                    <span>BPJS / Asuransi</span>
                    <span class="font-mono">- Rp <?= number_format($slip['bpjs_deduction'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <div class="row" style="margin-bottom: 6px;">
                    <span>Potongan Kasbon / Pinjaman</span>
                    <span class="font-mono text-red">- Rp <?= number_format($slip['cash_advance'], 0, ',', '.') ?></span>
                </div>
            </div>

            <?php if(!empty($kasbonDetails)): ?>
            <div class="borongan-title" style="color:#ef4444; border-color: rgba(239, 68, 68, 0.3);">Rincian Potongan Kasbon</div>
            <div style="padding: 0 15px 15px 15px;">
                <table class="borongan-table" style="color:#dc2626;">
                    <thead>
                        <tr>
                            <th style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2);">Tgl / Keterangan</th>
                            <th class="text-right" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2);">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($kasbonDetails as $kd): ?>
                        <tr>
                            <td style="border-color: rgba(239, 68, 68, 0.2);">
                                <b><?= date('d/m/Y', strtotime($kd['date'])) ?></b><br>
                                <small><?= esc($kd['description']) ?></small>
                            </td>
                            <td class="text-right font-mono" style="border-color: rgba(239, 68, 68, 0.2);">- <?= number_format($kd['amount'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="row-total" style="color: #dc2626;">
                <span>Total Potongan (B)</span>
                <span>Rp <?= number_format($slip['cash_advance'] + $slip['late_penalty'] + $slip['bpjs_deduction'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="thp-box">
        <div class="thp-label">Take Home Pay (A - B)</div>
        <div class="thp-amount">Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?></div>
    </div>

    <div class="signature-area" style="display:flex; justify-content: space-between; margin-top: 40px; font-size: 12px;">
        <div style="text-align: center; width: 200px;">
            <p>Penerima,</p>
            <br><br><br>
            <p><b>( <?= esc($slip['name']) ?> )</b></p>
        </div>
        <div style="text-align: center; width: 200px;">
            <p>Bagian Keuangan,</p>
            <br><br><br>
            <p><b>( ........................... )</b></p>
        </div>
    </div>
</div>

</body>
</html>