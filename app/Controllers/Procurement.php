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

        // Tarik data PO
        $purchaseOrders = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->orderBy('purchase_orders.id', 'DESC')
            ->get()->getResultArray();

        // FITUR BARU: Tarik data Master Supplier
        $suppliers = $this->db->table('suppliers')->orderBy('supplier_name', 'ASC')->get()->getResultArray();

        $data = [
            'title'          => 'Manajemen Pembelian (Procurement)',
            'purchaseOrders' => $purchaseOrders,
            'suppliers'      => $suppliers // Kirim ke View
        ];

        return view('procurement/index', $data);
    }

    // --- FITUR BARU: HAPUS SUPPLIER AMAN ---
    public function delete_supplier($id)
    {
        try {
            // Cek apakah supplier ini sudah pernah dipakai di PO
            $used = $this->db->table('purchase_orders')->where('supplier_id', $id)->countAllResults();
            if ($used > 0) {
                throw new \Exception("DITOLAK! Anda tidak bisa menghapus Vendor ini karena sudah memiliki riwayat transaksi Purchase Order (PO).");
            }

            $this->db->table('suppliers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data Vendor berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create_po()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $suppliers = $this->db->table('suppliers')->get()->getResultArray();
        
        // PERBAIKAN ENTERPRISE: Tarik data dari tabel raw_materials yang baru
        $rawMaterials = $this->db->table('raw_materials')
                                 ->orderBy('material_name', 'ASC')
                                 ->get()->getResultArray();

        $data = [
            'title'        => 'Buat Purchase Order Baru',
            'suppliers'    => $suppliers,
            'rawMaterials' => $rawMaterials
        ];

        return view('procurement/create_po', $data);
    }

    public function store_po()
    {
        try {
            $this->db->transStart();

            $supplierId = $this->request->getPost('supplier_id');
            $poDate     = $this->request->getPost('po_date');
            $rmSkus     = $this->request->getPost('rm_sku');
            $qtys       = $this->request->getPost('qty');
            $prices     = $this->request->getPost('price');

            if(empty($rmSkus) || count($rmSkus) === 0) {
                throw new \Exception("Anda harus memilih minimal 1 barang untuk dipesan.");
            }

            // Generate PO Number
            $dateStr = date('Ym');
            $lastPo = $this->db->table('purchase_orders')->like('po_number', "PO-$dateStr", 'after')->orderBy('id', 'DESC')->get()->getRowArray();
            $seq = 1;
            if ($lastPo) {
                $parts = explode('-', $lastPo['po_number']);
                $seq = intval(end($parts)) + 1;
            }
            $poNumber = "PO-" . $dateStr . "-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $this->db->table('purchase_orders')->insert([
                'po_number'   => $poNumber,
                'supplier_id' => $supplierId,
                'po_date'     => $poDate,
                'status'      => 'ORDERED',
                'total_amount'=> 0 
            ]);
            $poId = $this->db->insertID();

            $grandTotal = 0;
            $validItems = 0;

            for ($i = 0; $i < count($rmSkus); $i++) {
                if(isset($rmSkus[$i]) && $rmSkus[$i] !== '') {
                    $qty = isset($qtys[$i]) ? (float)$qtys[$i] : 0;
                    $price = isset($prices[$i]) ? (float)$prices[$i] : 0;

                    if ($qty > 0 && $price >= 0) {
                        $subtotal = $qty * $price;
                        $grandTotal += $subtotal;

                        $this->db->table('purchase_order_items')->insert([
                            'po_id'      => $poId,
                            'rm_sku'     => $rmSkus[$i],
                            'qty'        => $qty,
                            'unit_price' => $price,
                            'subtotal'   => $subtotal
                        ]);
                        $validItems++;
                    }
                }
            }

            if ($validItems === 0) throw new \Exception("Kuantitas barang tidak valid. PO dibatalkan.");

            $this->db->table('purchase_orders')->where('id', $poId)->update(['total_amount' => $grandTotal]);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan Purchase Order.");

            return redirect()->to('/procurement')->with('success', "Purchase Order <b>$poNumber</b> berhasil diterbitkan ke Supplier.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function receive_goods($poId)
    {
        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po || $po['status'] === 'RECEIVED') {
                throw new \Exception("PO tidak valid atau barang sudah diterima sebelumnya.");
            }

            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();

            // PERBAIKAN: Update stok fisik dan modal HPP ke tabel raw_materials (bukan warehouse_inventory)
            foreach ($poItems as $item) {
                $this->db->query("UPDATE raw_materials SET physical_stock = physical_stock + ? WHERE sku_material = ?", [$item['qty'], $item['rm_sku']]);
                $this->db->query("UPDATE raw_materials SET hpp = ? WHERE sku_material = ?", [$item['unit_price'], $item['rm_sku']]);
            }

            $this->db->table('purchase_orders')->where('id', $poId)->update(['status' => 'RECEIVED']);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal melakukan proses penerimaan barang.");

            return redirect()->back()->with('success', "Barang dari PO <b>{$po['po_number']}</b> telah diterima. Stok fisik gudang otomatis bertambah!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- FITUR BARU: REGISTRASI VENDOR / SUPPLIER ---
    public function store_supplier()
    {
        try {
            $name    = $this->request->getPost('supplier_name');
            $contact = $this->request->getPost('contact_person');
            $phone   = $this->request->getPost('phone');
            $address = $this->request->getPost('address');

            // Cek apakah supplier sudah ada (mencegah duplikat)
            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->countAllResults();
            if ($exists > 0) {
                throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar di sistem.");
            }

            $this->db->table('suppliers')->insert([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $phone,
                'address'        => $address
            ]);

            return redirect()->back()->with('success', "Vendor/Supplier <b>$name</b> berhasil didaftarkan ke sistem!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // --- FITUR BARU: LIHAT DETAIL DOKUMEN PO ---
    public function detail($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        // 1. Ambil Header PO beserta Info Supplier
        $po = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name, suppliers.contact_person, suppliers.phone, suppliers.address')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->where('purchase_orders.id', $id)
            ->get()->getRowArray();

        if (!$po) {
            return redirect()->to('/procurement')->with('error', 'Dokumen Purchase Order tidak ditemukan.');
        }

        // 2. Ambil Rincian Barang (Items) yang dipesan dari tabel raw_materials
        $items = $this->db->table('purchase_order_items')
            ->select('purchase_order_items.*, raw_materials.material_name, raw_materials.unit')
            ->join('raw_materials', 'raw_materials.sku_material = purchase_order_items.rm_sku', 'left')
            ->where('po_id', $id)
            ->get()->getResultArray();

        $data = [
            'title' => 'Detail Dokumen PO: ' . $po['po_number'],
            'po'    => $po,
            'items' => $items
        ];

        return view('procurement/detail', $data);
    }
}