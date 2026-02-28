<?php

namespace App\Controllers;
use App\Models\EmployeeModel;
use App\Models\PayrollModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $empModel = new EmployeeModel();
        $payrollModel = new PayrollModel();
        
        // ==========================================
        // 1. DATA ASLI (MODUL HRD & PAYROLL)
        // ==========================================
        $totalEmployees = $empModel->where('is_active', 1)->countAllResults();
        
        $currentMonth = date('m');
        $currentYear  = date('Y');
        
        // --- PERBAIKAN 1: Gunakan MONTH() dan YEAR() pada kolom period_start ---
        $payrolls = $payrollModel->where('MONTH(period_start)', $currentMonth)
                                 ->where('YEAR(period_start)', $currentYear)
                                 ->findAll();
        
        $totalPayrollCost = 0;
        foreach ($payrolls as $p) {
            // --- PERBAIKAN 2: Gunakan total_amount sesuai tabel baru kita ---
            $totalPayrollCost += $p['total_amount'];
        }

        // ==========================================
        // 2. DATA SIMULASI (MODUL PABRIK LAINNYA)
        // ==========================================
        
        // A. Modul Produksi & Manufaktur
        $productionData = [
            'active_spk' => 14,
            'completed_today' => 125, // pcs silencer
            'defect_rate' => 1.2, // persentase barang reject
            'target_achieved' => 85 // persentase target harian
        ];

        // B. Modul Gudang & Logistik
        $warehouseData = [
            'low_stock_items' => 8, // material yang mau habis
            'inbound_today' => 3, // truk masuk
            'outbound_today' => 5 // truk keluar
        ];

        // C. Modul Sales & Penjualan
        $salesData = [
            'pending_orders' => 24,
            'revenue_this_month' => 450500000, // Rp 450.5 Juta
            'top_product' => 'Noric Hexagonal Carbon'
        ];

        // D. Data Grafik (Tren Produksi vs Penjualan 6 Hari Terakhir)
        $chartData = [
            'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            'produksi' => [120, 135, 125, 140, 150, 90], // pcs
            'penjualan' => [100, 115, 140, 130, 160, 110] // pcs
        ];

        $data = [
            'title'            => 'Dashboard',
            'currentMonthName' => date('F Y'),
            'totalEmployees'   => $totalEmployees,
            'totalPayrollCost' => $totalPayrollCost,
            'production'       => $productionData,
            'warehouse'        => $warehouseData,
            'sales'            => $salesData,
            'chart'            => $chartData
        ];

        return view('dashboard/index', $data);
    }
}