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
        // 2. DATA KEUANGAN (AKUNTANSI & ASET FISIK)
        // ==========================================
        $coa = $db->table('chart_of_accounts')->get()->getResultArray();
        $revenue = 0; $expense = 0; $assets = 0;

        foreach($coa as $acc) {
            if($acc['account_type'] === 'PENDAPATAN') $revenue += $acc['balance'];
            if($acc['account_type'] === 'PERBELANJAAN') $expense += $acc['balance'];
            if($acc['account_type'] === 'ASET') $assets += $acc['balance'];
        }
        $netProfit = $revenue - $expense;

        // PERBAIKAN: Hitung Aset Gudang Fisik Real-time (Produk Jadi PRD + Material MAT)
        $valPRD = $db->query("SELECT SUM(physical_stock * hpp) as total FROM warehouse_inventory")->getRow()->total ?? 0;
        $valMAT = $db->query("SELECT SUM(physical_stock * hpp) as total FROM raw_materials")->getRow()->total ?? 0;
        $inventoryValue = $valPRD + $valMAT;

        // ==========================================
        // 3. DATA PRODUKSI & GUDANG
        // ==========================================
        $activeSpk = $db->table('work_orders')->where('status', 'IN_PROGRESS')->countAllResults();
        
        // PERBAIKAN: Cek item gudang (PRD dan MAT) yang stoknya menipis berdasarkan kolom 'min_stock' (Bukan hardcode angka 10)
        $lowStockPrd = $db->table('warehouse_inventory')->where('physical_stock <= min_stock')->countAllResults();
        $lowStockMat = $db->table('raw_materials')->where('physical_stock <= min_stock')->countAllResults();
        $lowStockItems = $lowStockPrd + $lowStockMat;

        // ==========================================
        // 4. DATA PENJUALAN B2B & E-COMMERCE
        // ==========================================
        $b2bOrders = $db->table('b2b_sales_orders')
                        ->where('MONTH(order_date)', $currentMonth)
                        ->where('YEAR(order_date)', $currentYear)
                        ->get()->getResultArray();
        $b2bRevenue = array_sum(array_column($b2bOrders, 'total_amount'));
        $pendingB2b = $db->table('b2b_sales_orders')->where('status !=', 'PAID')->countAllResults();

        $activeShops = $db->table('shopee_integrations')->where('status', 'Active')->countAllResults();

        // ==========================================
        // 5. DATA GRAFIK (Simulasi 6 Bulan Terakhir untuk UI)
        // ==========================================
        $chartData = [
            'labels' => ['Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar'],
            'pendapatan' => [120, 135, 125, 140, 150, ($revenue/1000000)], // Dalam Juta
            'beban' => [100, 115, 140, 130, 110, ($expense/1000000)] // Dalam Juta
        ];

        $data = [
            'title'            => 'Executive Command Center',
            'currentMonthName' => date('F Y'),
            'totalEmployees'   => $totalEmployees,
            'totalPayrollCost' => $totalPayrollCost,
            'finance'          => [
                'revenue'         => $revenue, 
                'profit'          => $netProfit, 
                'assets'          => $assets,
                'inventory_value' => $inventoryValue // Tambahan aset fisik real-time
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