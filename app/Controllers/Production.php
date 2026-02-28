<?php

namespace App\Controllers;
use App\Controllers\Shopee; // Memanggil Controller Shopee yang sudah sakti

class Production extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- HALAMAN INPUT PRODUKSI PABRIK ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // Ambil data stok gudang fisik saat ini
        $inventory = $this->db->table('warehouse_inventory')->orderBy('item_name', 'ASC')->get()->getResultArray();
        
        // Ambil riwayat produksi hari ini
        $todayLogs = $this->db->table('production_logs')
                              ->select('production_logs.*, warehouse_inventory.item_name')
                              ->join('warehouse_inventory', 'warehouse_inventory.sku = production_logs.sku')
                              ->where('DATE(production_date)', date('Y-m-d'))
                              ->orderBy('production_date', 'DESC')
                              ->get()->getResultArray();

        $data = [
            'title'     => 'Log Produksi Pabrik',
            'inventory' => $inventory,
            'todayLogs' => $todayLogs
        ];

        return view('production/index', $data);
    }

    // --- PROSES SIMPAN HASIL PRODUKSI & AUTO-SYNC SHOPEE ---
    public function store_production()
    {
        try {
            $sku = $this->request->getPost('sku');
            $qty = (int)$this->request->getPost('qty_produced');

            if (empty($sku) || $qty <= 0) {
                throw new \Exception("SKU tidak valid atau jumlah produksi harus lebih dari 0.");
            }

            $this->db->transStart();

            // 1. Catat ke Log Produksi
            $this->db->table('production_logs')->insert([
                'sku'          => $sku,
                'qty_produced' => $qty,
                'operator_name'=> session()->get('username') ?? 'Admin Gudang'
            ]);

            // 2. Tambahkan Stok Fisik di Gudang Lokal
            $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock + ? WHERE sku = ?", [$qty, $sku]);

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan data ke database lokal.");
            }

            // 3. AMBIL TOTAL STOK TERBARU
            $latestItem = $this->db->table('warehouse_inventory')->where('sku', $sku)->get()->getRowArray();
            $newTotalStock = $latestItem['physical_stock'];

            // 4. THE MAGIC: AUTO-PUSH KE SHOPEE!
            // Memanggil mesin Shopee yang sudah kita buat di Pilar 3
            $shopeeController = new Shopee();
            $syncCount = $shopeeController->push_stock_to_shopee($sku, $newTotalStock);

            $msg = "Hasil produksi berhasil disimpan! Stok Gudang bertambah.";
            if ($syncCount > 0) {
                $msg .= " <b>Sistem juga otomatis mengupdate stok di {$syncCount} etalase Shopee!</b>";
            } else {
                $msg .= " (Peringatan: SKU ini belum dipetakan ke Shopee).";
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses produksi: ' . $e->getMessage());
        }
    }
}