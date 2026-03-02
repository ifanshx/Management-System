<?php

namespace App\Controllers;

class Procurement extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- 1. DASBOR PURCHASING ---
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $purchaseOrders = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->orderBy('purchase_orders.id', 'DESC')
            ->get()->getResultArray();

        $data = [
            'title'          => 'Manajemen Pembelian (Procurement)',
            'purchaseOrders' => $purchaseOrders
        ];

        return view('procurement/index', $data);
    }

    // --- 2. HALAMAN BUAT PO BARU ---
    public function create_po()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $suppliers = $this->db->table('suppliers')->get()->getResultArray();
        // Hanya ambil Bahan Baku (Raw Material)
        $rawMaterials = $this->db->table('warehouse_inventory')->like('sku', 'RM-', 'after')->get()->getResultArray();

        $data = [
            'title'        => 'Buat Purchase Order Baru',
            'suppliers'    => $suppliers,
            'rawMaterials' => $rawMaterials
        ];

        return view('procurement/create_po', $data);
    }

    // --- 3. SIMPAN PO KE DATABASE ---
    public function store_po()
    {
        try {
            $this->db->transStart();

            $supplierId = $this->request->getPost('supplier_id');
            $poDate     = $this->request->getPost('po_date');
            $rmSkus     = $this->request->getPost('rm_sku');
            $qtys       = $this->request->getPost('qty');
            $prices     = $this->request->getPost('price');

            if(empty($rmSkus)) throw new \Exception("Pilih minimal 1 bahan baku untuk dipesan.");

            // Generate PO Number
            $dateStr = date('Ym');
            $lastPo = $this->db->table('purchase_orders')->like('po_number', "PO-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastPo) {
                $parts = explode('-', $lastPo['po_number']);
                $seq = intval(end($parts)) + 1;
            }
            $poNumber = "PO-" . $dateStr . "-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Insert Header PO
            $this->db->table('purchase_orders')->insert([
                'po_number'   => $poNumber,
                'supplier_id' => $supplierId,
                'po_date'     => $poDate,
                'status'      => 'ORDERED',
                'total_amount'=> 0 // Akan diupdate nanti
            ]);
            $poId = $this->db->insertID();

            $grandTotal = 0;

            // Insert Items PO
            for ($i = 0; $i < count($rmSkus); $i++) {
                if(!empty($rmSkus[$i]) && !empty($qtys[$i]) && !empty($prices[$i])) {
                    $subtotal = (float)$qtys[$i] * (float)$prices[$i];
                    $grandTotal += $subtotal;

                    $this->db->table('purchase_order_items')->insert([
                        'po_id'      => $poId,
                        'rm_sku'     => $rmSkus[$i],
                        'qty'        => (float)$qtys[$i],
                        'unit_price' => (float)$prices[$i],
                        'subtotal'   => $subtotal
                    ]);
                }
            }

            // Update Total Amount
            $this->db->table('purchase_orders')->where('id', $poId)->update(['total_amount' => $grandTotal]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan Purchase Order.");
            }

            return redirect()->to('/procurement')->with('success', "Purchase Order <b>$poNumber</b> berhasil diterbitkan ke Supplier.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- 4. TERIMA BARANG (GOODS RECEIPT) & TAMBAH STOK ---
    public function receive_goods($poId)
    {
        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po || $po['status'] === 'RECEIVED') {
                throw new \Exception("PO tidak valid atau barang sudah diterima sebelumnya.");
            }

            // Ambil item dari PO
            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();

            // TAMBAH STOK FISIK DI GUDANG
            foreach ($poItems as $item) {
                $this->db->query("UPDATE warehouse_inventory SET physical_stock = physical_stock + ? WHERE sku = ?", [$item['qty'], $item['rm_sku']]);
                
                // Opsional: Update HPP/Modal Bahan Baku berdasarkan harga beli terbaru (Moving Average)
                $this->db->query("UPDATE warehouse_inventory SET hpp = ? WHERE sku = ?", [$item['unit_price'], $item['rm_sku']]);
            }

            // Ubah Status PO
            $this->db->table('purchase_orders')->where('id', $poId)->update(['status' => 'RECEIVED']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception("Gagal melakukan proses penerimaan barang.");
            }

            return redirect()->back()->with('success', "Barang dari PO <b>{$po['po_number']}</b> telah diterima. Stok Bahan Baku di gudang otomatis bertambah!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}