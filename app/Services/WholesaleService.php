<?php

namespace App\Controllers;

use App\Services\WholesaleService;
use CodeIgniter\HTTP\ResponseInterface;

class Wholesale extends BaseController
{
    private \CodeIgniter\Database\BaseConnection $db;
    private WholesaleService $wholesaleService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->wholesaleService = new WholesaleService();
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', trim((string)$phone));
        if ($phone === '') return '';

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62') && str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return preg_match('/^62[0-9]{8,15}$/', $phone) ? $phone : '';
    }

    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $salesOrders = $this->db->table('b2b_sales_orders')
            ->select('b2b_sales_orders.*, b2b_customers.company_name')
            ->join('b2b_customers', 'b2b_customers.id = b2b_sales_orders.customer_id')
            ->orderBy('b2b_sales_orders.id', 'DESC')
            ->get()->getResultArray();

        foreach ($salesOrders as &$so) {
            $so['items'] = $this->db->table('b2b_sales_order_items')
                ->select('b2b_sales_order_items.*, warehouse_inventory.item_name, warehouse_inventory.hpp')
                ->join('warehouse_inventory', 'warehouse_inventory.sku = b2b_sales_order_items.fg_sku', 'left')
                ->where('so_id', $so['id'])
                ->get()->getResultArray();

            $so['returns'] = $this->db->table('b2b_sales_returns')->where('so_id', $so['id'])->orderBy('id', 'DESC')->get()->getResultArray();
        }

        $data = [
            'title'       => 'B2B Wholesale & Piutang', 
            'salesOrders' => $salesOrders, 
            'customers'   => $this->db->table('b2b_customers')->orderBy('company_name', 'ASC')->get()->getResultArray(), 
            'products'    => $this->db->table('warehouse_inventory')->select('sku, item_name, physical_stock, hpp, wholesale_price')->like('sku', 'PRD-', 'after')->get()->getResultArray(), 
            'company'     => $this->db->tableExists('company_settings') ? $this->db->table('company_settings')->get()->getRowArray() : []
        ];
        return view('wholesale/index', $data);
    }

    public function update_item_qty(int $itemId): ResponseInterface
    {
        try {
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi habis, silakan login ulang', 'csrf_token' => csrf_hash()]);
            }

            // Validasi Input
            if (!$this->validate([
                'new_qty' => 'required|is_natural_no_zero'
            ])) {
                throw new \Exception("Kuantitas baru tidak valid.");
            }

            $newQty = (int)$this->request->getPost('new_qty');
            $picName = session()->get('name') ?? 'System';

            // Eksekusi Logika Lintas Modul melalui Service
            $this->wholesaleService->updateItemQuantity($itemId, $newQty, $picName);

            return $this->response->setJSON([
                'status'     => 'success', 
                'message'    => 'Qty berhasil diperbarui. Tagihan & SPK pabrik telah disesuaikan otomatis!', 
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'     => 'error', 
                'message'    => $e->getMessage(), 
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    public function process_shipment_gabungan(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        try {
            // Validasi Input Cepat
            if (!$this->validate([
                'so_item_id.*' => 'required|is_natural_no_zero',
                'ship_qty.*'   => 'required|is_natural'
            ])) {
                throw new \Exception("Data form pengiriman tidak valid.");
            }

            $itemIds  = $this->request->getPost('so_item_id') ?? [];
            $shipQtys = $this->request->getPost('ship_qty') ?? [];
            $picName  = session()->get('name') ?? 'System';

            // Delegasi ke Service
            $soIdsToUpdate = $this->wholesaleService->processCombinedShipment($itemIds, $shipQtys, $picName);
            $soIdsString   = implode('-', $soIdsToUpdate);

            return redirect()->back()
                ->with('success', 'Pengiriman gabungan berhasil diproses dan stok telah dipotong!')
                ->with('print_gabungan', $soIdsString);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store_customer(): \CodeIgniter\HTTP\RedirectResponse
    {
        try {
            if (!$this->validate([
                'company_name' => 'required|min_length[3]',
            ])) throw new \Exception("Nama Perusahaan wajib diisi.");

            $phone = trim($this->request->getPost('phone'));
            $normalizedPhone = $this->normalizePhone($phone);

            if ($phone !== '' && $normalizedPhone === '') {
                throw new \Exception("Nomor WhatsApp tidak valid. Gunakan format 08123456789.");
            }

            $this->db->table('b2b_customers')->insert([
                'company_name' => $this->request->getPost('company_name'),
                'contact_name' => $this->request->getPost('contact_name'),
                'phone'        => $normalizedPhone,
                'address'      => $this->request->getPost('address'),
            ]);
            
            return redirect()->back()->with('success', 'Data Mitra Reseller berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah mitra: ' . $e->getMessage());
        }
    }

    public function delete_customer(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        try {
            $check = $this->db->table('b2b_sales_orders')->where('customer_id', $id)->countAllResults();
            if ($check > 0) {
                return redirect()->back()->with('error', 'Gagal Dihapus! Mitra ini memiliki riwayat transaksi.');
            }

            $this->db->table('b2b_customers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data Mitra berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus mitra: ' . $e->getMessage());
        }
    }

    public function get_pending_by_customer(?int $customerId = null): ResponseInterface
    {
        $customerId = $customerId ?? (int)$this->request->getUri()->getSegment(3);
        if (empty($customerId)) return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter tidak valid.']);

        $sql = "SELECT i.id, i.so_id, i.fg_sku, i.qty, i.shipped_qty, o.so_number, o.order_date, o.shipping_status, w.item_name 
                FROM b2b_sales_order_items i
                JOIN b2b_sales_orders o ON o.id = i.so_id
                LEFT JOIN warehouse_inventory w ON w.sku = i.fg_sku
                WHERE o.customer_id = ? AND o.shipping_status != 'SHIPPED' AND i.qty > i.shipped_qty
                ORDER BY o.order_date ASC";
        
        $pendingItems = $this->db->query($sql, [$customerId])->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $pendingItems]);
    }
}