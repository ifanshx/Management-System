<?php

namespace App\Controllers;

class OmniDashboard extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // 1. METRIK PENJUALAN & PESANAN (Hari Ini vs Total)
        $today = date('Y-m-d');
        
        // Total Pesanan Belum Diproses (Pending di Gudang)
        $pendingOrders = $this->db->table('sales_orders')
                                  ->where('order_status', 'READY_TO_SHIP')
                                  ->countAllResults();

        // Pesanan Hari Ini
        $ordersToday = $this->db->table('sales_orders')
                                ->where('DATE(order_date)', $today)
                                ->countAllResults();

        // 2. METRIK KEUANGAN
        // Omzet Kotor Bulan Ini (Berdasarkan Pesanan Selesai)
        $thisMonth = date('Y-m');
        $monthlyRevenueQuery = $this->db->table('sales_orders')
                                        ->selectSum('total_amount')
                                        ->where("DATE_FORMAT(order_date, '%Y-%m') =", $thisMonth)
                                        ->where('order_status !=', 'CANCELLED')
                                        ->get()->getRow();
        $monthlyRevenue = $monthlyRevenueQuery->total_amount ?? 0;

        // Uang Bersih Cair (Escrow) Bulan Ini
        $monthlyNetQuery = $this->db->table('shopee_finances')
                                    ->selectSum('escrow_amount')
                                    ->where("DATE_FORMAT(payout_time, '%Y-%m') =", $thisMonth)
                                    ->get()->getRow();
        $monthlyNetIncome = $monthlyNetQuery->escrow_amount ?? 0;

        // 3. TOP PRODUK TERLARIS (Best Sellers)
        // Menghitung produk mana yang qty-nya paling banyak dibeli
        $bestSellers = $this->db->table('sales_order_items')
                                ->select('item_name, variation_name, SUM(model_qty) as total_sold')
                                ->groupBy('item_id, variation_name')
                                ->orderBy('total_sold', 'DESC')
                                ->limit(5) // Ambil Top 5
                                ->get()->getResultArray();

        // 4. DAFTAR TOKO AKTIF & STATUSNYA
        $activeShops = $this->db->table('shopee_integrations')
                                ->where('status', 'Active')
                                ->get()->getResultArray();

        $data = [
            'title'            => 'Command Center',
            'pendingOrders'    => $pendingOrders,
            'ordersToday'      => $ordersToday,
            'monthlyRevenue'   => $monthlyRevenue,
            'monthlyNetIncome' => $monthlyNetIncome,
            'bestSellers'      => $bestSellers,
            'activeShops'      => $activeShops
        ];

        return view('shopee/dashboard', $data);
    }
}