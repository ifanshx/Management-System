<?php

namespace App\Controllers;

class Procurement extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $purchaseOrders = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->orderBy('purchase_orders.id', 'DESC')
            ->get()->getResultArray();

        $suppliers = $this->db->table('suppliers')->orderBy('supplier_name', 'ASC')->get()->getResultArray();

        $data = [
            'title'          => 'Manajemen Pembelian (Procurement)',
            'purchaseOrders' => $purchaseOrders,
            'suppliers'      => $suppliers
        ];

        return view('procurement/index', $data);
    }

    public function delete_supplier($id)
    {
        try {
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
        
        $rawMaterials = $this->db->table('raw_materials')
                                 ->like('sku_material', 'MAT-', 'after')
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
                throw new \Exception("Anda harus memilih minimal 1 material untuk dipesan.");
            }

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

            if ($validItems === 0) throw new \Exception("Kuantitas material tidak valid. PO dibatalkan.");

            $this->db->table('purchase_orders')->where('id', $poId)->update(['total_amount' => $grandTotal]);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal menyimpan Purchase Order.");

            // Dukungan AJAX Response
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Purchase Order $poNumber berhasil diterbitkan ke Vendor."
                ]);
            }

            return redirect()->to('/procurement')->with('success', "Purchase Order <b>$poNumber</b> berhasil diterbitkan ke Vendor.");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function receive_goods($poId)
    {
        try {
            $this->db->transStart();

            $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
            if (!$po || $po['status'] === 'RECEIVED') {
                throw new \Exception("PO tidak valid atau material sudah diterima sebelumnya.");
            }

            $poItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();

            foreach ($poItems as $item) {
                $this->db->query("UPDATE raw_materials SET physical_stock = physical_stock + ? WHERE sku_material = ?", [$item['qty'], $item['rm_sku']]);
                $this->db->query("UPDATE raw_materials SET hpp = ? WHERE sku_material = ?", [$item['unit_price'], $item['rm_sku']]);
            }

            $this->db->table('purchase_orders')->where('id', $poId)->update(['status' => 'RECEIVED']);

            $this->db->table('journals')->insert([
                'journal_number'   => 'JRN-PO-'.time(),
                'transaction_date' => date('Y-m-d'),
                'description'      => "Penerimaan Material PO: " . $po['po_number'],
                'total_amount'     => $po['total_amount'],
                'created_by'       => session()->get('name') ?? 'System'
            ]);
            $journalId = $this->db->insertID();

            $invAccount = $this->db->table('chart_of_accounts')->where('account_code', '1-3000')->get()->getRowArray(); 
            $apAccount  = $this->db->table('chart_of_accounts')->where('account_code', '2-1000')->get()->getRowArray(); 

            if($invAccount && $apAccount) {
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $invAccount['id'], 'debit' => $po['total_amount'], 'credit' => 0]);
                $this->db->table('journal_items')->insert(['journal_id' => $journalId, 'account_id' => $apAccount['id'], 'debit' => 0, 'credit' => $po['total_amount']]);
                
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$po['total_amount'], $invAccount['id']]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$po['total_amount'], $apAccount['id']]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) throw new \Exception("Gagal melakukan proses penerimaan material.");

            return redirect()->back()->with('success', "Material dari PO {$po['po_number']} telah diterima. Stok fisik dan Buku Besar Otomatis terupdate!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_supplier()
    {
        try {
            $name    = $this->request->getPost('supplier_name');
            $contact = $this->request->getPost('contact_person');
            $phone   = $this->request->getPost('phone');
            $address = $this->request->getPost('address');

            $exists = $this->db->table('suppliers')->where('supplier_name', $name)->countAllResults();
            if ($exists > 0) {
                throw new \Exception("Vendor dengan nama <b>$name</b> sudah terdaftar.");
            }

            $this->db->table('suppliers')->insert([
                'supplier_name'  => $name,
                'contact_person' => $contact,
                'phone'          => $phone,
                'address'        => $address
            ]);

            // Dukungan AJAX Response
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Vendor $name berhasil didaftarkan!"
                ]);
            }

            return redirect()->back()->with('success', "Vendor/Supplier <b>$name</b> berhasil didaftarkan ke sistem!");
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        if (!session()->getLoggedIn()) return redirect()->to('/portal');

        $po = $this->db->table('purchase_orders')
            ->select('purchase_orders.*, suppliers.supplier_name, suppliers.contact_person, suppliers.phone, suppliers.address')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->where('purchase_orders.id', $id)
            ->get()->getRowArray();

        if (!$po) return redirect()->to('/procurement')->with('error', 'Dokumen Purchase Order tidak ditemukan.');

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