<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $currentMonth = date('m');
        $currentYear  = date('Y');
        $today        = date('Y-m-d');

        // ==========================================
        // 1. DATA HRD & PAYROLL
        // ==========================================
        $totalEmployees = $db->table('employees')->where('is_active', 1)->countAllResults();
        $payrolls = $db->table('payrolls')
                       ->where('MONTH(period_start)', $currentMonth)
                       ->where('YEAR(period_start)', $currentYear)
                       ->get()->getResultArray();
        
        $totalPayrollCost = array_sum(array_column($payrolls, 'total_amount'));

        // ==========================================
        // 2. DATA KEUANGAN (AKUNTANSI)
        // ==========================================
        $coa = $db->table('chart_of_accounts')->get()->getResultArray();
        $revenue = 0; $expense = 0; $liquidAssets = 0;

        foreach($coa as $acc) {
            // Amankan nilai saldo. (Coba tangkap dari kolom 'balance' atau 'saldo' jika ada)
            $bal = isset($acc['balance']) ? (float)$acc['balance'] : (isset($acc['saldo']) ? (float)$acc['saldo'] : 0);

            if($acc['account_type'] === 'PENDAPATAN') $revenue += $bal;
            if($acc['account_type'] === 'PERBELANJAAN') $expense += $bal;
            
            // Hanya hitung Kas/Bank/Piutang dari CoA sebagai Aset Likuid
            if($acc['account_type'] === 'ASET' && !in_array($acc['account_code'], ['1-3000', '1-5000', '1-5001'])) {
                $liquidAssets += $bal;
            }
        }
        $netProfit = $revenue - $expense;

        // ==========================================
        // 3. VALUASI GUDANG (INVENTORY)
        // ==========================================
        $valPRD = $db->query("SELECT SUM(physical_stock * hpp) as total FROM warehouse_inventory")->getRow()->total ?? 0;
        $valMAT = $db->query("SELECT SUM(physical_stock * hpp) as total FROM raw_materials")->getRow()->total ?? 0;
        $inventoryValue = $valPRD + $valMAT;

        // ==========================================
        // 4. VALUASI ASET TETAP (MESIN, GEDUNG)
        // ==========================================
        // Amankan Perhitungan Aset Tetap
        $totalPurchasePrice = $db->query("SELECT SUM(purchase_price) as total FROM factory_assets WHERE status = 'ACTIVE'")->getRow()->total ?? 0;
        
        // Ambil baris Akumulasi Penyusutan dengan aman, tidak peduli apakah ada kolom 'balance' atau tidak
        $accDepRow = $db->table('chart_of_accounts')->where('account_code', '1-5001')->get()->getRowArray();
        $accumulatedDepreciation = 0;
        if ($accDepRow) {
            $accumulatedDepreciation = isset($accDepRow['balance']) ? (float)$accDepRow['balance'] : (isset($accDepRow['saldo']) ? (float)$accDepRow['saldo'] : 0);
        }

        $fixedAssetValue = $totalPurchasePrice - $accumulatedDepreciation;
        $totalWealth = $liquidAssets + $inventoryValue + $fixedAssetValue;

        // ==========================================
        // 5. DATA PRODUKSI & GUDANG FISIK
        // ==========================================
        $activeSpk = $db->table('work_orders')->where('status', 'IN_PROGRESS')->countAllResults();
        $lowStockPrd = $db->table('warehouse_inventory')->where('physical_stock <= min_stock')->countAllResults();
        $lowStockMat = $db->table('raw_materials')->where('physical_stock <= min_stock')->countAllResults();
        $lowStockItems = $lowStockPrd + $lowStockMat;

        // ==========================================
        // 6. DATA PENJUALAN B2B & E-COMMERCE
        // ==========================================
        $b2bOrders = $db->table('b2b_sales_orders')
                        ->where('MONTH(order_date)', $currentMonth)
                        ->where('YEAR(order_date)', $currentYear)
                        ->get()->getResultArray();
        $b2bRevenue = array_sum(array_column($b2bOrders, 'total_amount'));
        $pendingB2b = $db->table('b2b_sales_orders')->where('status !=', 'PAID')->countAllResults();

        $activeShops = $db->table('shopee_integrations')->where('status', 'Active')->countAllResults();

        // ==========================================
        // 7. DATA GRAFIK (Tren Arus Kas 6 Bulan Real-Time)
        // ==========================================
        $labelsArr     = [];
        $pendapatanArr = [];
        $bebanArr      = [];

        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = date('m', mktime(0, 0, 0, $currentMonth - $i, 1, $currentYear));
            $targetYear  = date('Y', mktime(0, 0, 0, $currentMonth - $i, 1, $currentYear));
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
            $labelsArr[] = $monthNames[(int)$targetMonth - 1];

            $revQ = $db->table('journal_items')
                       ->selectSum('journal_items.credit', 'kredit')
                       ->selectSum('journal_items.debit', 'debit')
                       ->join('journals', 'journals.id = journal_items.journal_id')
                       ->join('chart_of_accounts', 'chart_of_accounts.id = journal_items.account_id')
                       ->where('chart_of_accounts.account_type', 'PENDAPATAN')
                       ->where('MONTH(journals.transaction_date)', $targetMonth)
                       ->where('YEAR(journals.transaction_date)', $targetYear)
                       ->get()->getRowArray();
            $totRev = ($revQ['kredit'] ?? 0) - ($revQ['debit'] ?? 0);
            $pendapatanArr[] = round($totRev / 1000000, 2);

            $expQ = $db->table('journal_items')
                       ->selectSum('journal_items.debit', 'debit')
                       ->selectSum('journal_items.credit', 'kredit')
                       ->join('journals', 'journals.id = journal_items.journal_id')
                       ->join('chart_of_accounts', 'chart_of_accounts.id = journal_items.account_id')
                       ->where('chart_of_accounts.account_type', 'PERBELANJAAN')
                       ->where('MONTH(journals.transaction_date)', $targetMonth)
                       ->where('YEAR(journals.transaction_date)', $targetYear)
                       ->get()->getRowArray();
            $totExp = ($expQ['debit'] ?? 0) - ($expQ['kredit'] ?? 0);
            $bebanArr[] = round($totExp / 1000000, 2);
        }

        // ==========================================
        // 8. DATA ABSENSI KARYAWAN HARI INI
        // ==========================================
        $recentAttendance = $db->table('attendances') 
                       ->select('attendances.*, employees.name, employees.position') 
                       ->join('employees', 'employees.employee_id = attendances.employee_id', 'left') 
                       ->where('date', $today)
                       ->orderBy('time_in', 'DESC')
                       ->limit(8)
                       ->get()->getResultArray();

        $chartData = [
            'labels'     => $labelsArr,
            'pendapatan' => $pendapatanArr, 
            'beban'      => $bebanArr  
        ];

        $data = [
            'title'            => 'Executive Command Center',
            'currentMonthName' => date('F Y'),
            'totalEmployees'   => $totalEmployees,
            'totalPayrollCost' => $totalPayrollCost,
            'recentAttendance' => $recentAttendance, // Data absensi dikirim ke Views
            'finance'          => [
                'revenue'         => $revenue, 
                'profit'          => $netProfit, 
                'liquid_assets'   => $liquidAssets,    
                'fixed_assets'    => $fixedAssetValue, 
                'inventory_value' => $inventoryValue,  
                'total_wealth'    => $totalWealth      
            ],
            'production'       => [
                'active_spk' => $activeSpk, 
                'low_stock'  => $lowStockItems
            ],
            'sales'            => [
                'b2b_revenue'  => $b2bRevenue, 
                'pending_b2b'  => $pendingB2b, 
                'active_shops' => $activeShops
            ],
            'chart'            => $chartData
        ];

        return view('dashboard/index', $data);
    }
}