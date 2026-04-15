<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    // protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();

        // -------------------------------------------------------------------
        // ENGINE WHITE-LABEL: Ambil identitas perusahaan dari Database
        // -------------------------------------------------------------------
        $db = \Config\Database::connect();
        
        try {
            $company = $db->table('company_settings')->get()->getRowArray();
        } catch (\Exception $e) {
            $company = null; // Mencegah fatal error jika tabel belum dibuat
        }

        // Jika tabel masih kosong, gunakan default
        if (!$company) {
            $company = [
                'app_name'     => 'ERP System',
                'company_name' => 'Nama Perusahaan Anda',
                'address'      => 'Alamat Perusahaan',
                'phone'        => '080000000',
                'logo_path'    => 'default-logo.png' 
            ];
        }

        // Sebarkan variabel $company ke SELURUH file View (.php)
        \Config\Services::renderer()->setVar('company', $company);
    }
    
    protected function logInventoryMovement(array $data)
{
    $itemType = strtoupper($data['item_type'] ?? 'RAW');
    $itemSku  = $data['item_sku'] ?? '';
    $qtyIn    = (float)($data['qty_in'] ?? 0);
    $qtyOut   = (float)($data['qty_out'] ?? 0);

    if (empty($itemSku)) {
        throw new \Exception("SKU mutasi stok kosong.");
    }

    $itemName = $data['item_name'] ?? null;
    $uom      = $data['uom'] ?? 'PCS';
    $unitCost = (float)($data['unit_cost'] ?? 0);
    $totalVal = (float)($data['total_value'] ?? (($qtyIn > 0 ? $qtyIn : $qtyOut) * $unitCost));

    // Ambil saldo akhir stok aktual dari tabel master
    $balanceAfter = 0;

    if ($itemType === 'RAW') {
        $row = $this->db->table('raw_materials')->where('sku_material', $itemSku)->get()->getRowArray();
        if ($row) {
            $balanceAfter = (float)($row['physical_stock'] ?? 0);
            $itemName = $itemName ?: ($row['material_name'] ?? $itemSku);
            $uom      = $uom ?: ($row['base_uom'] ?? $row['unit'] ?? 'PCS');
        }
    } else {
        $row = $this->db->table('warehouse_inventory')->where('sku', $itemSku)->get()->getRowArray();
        if ($row) {
            $balanceAfter = (float)($row['physical_stock'] ?? 0);
            $itemName = $itemName ?: ($row['item_name'] ?? $itemSku);
            $uom      = $uom ?: 'PCS';
        }
    }

    $this->db->table('inventory_movements')->insert([
        'movement_date'   => $data['movement_date'] ?? date('Y-m-d H:i:s'),
        'item_type'       => $itemType,
        'item_sku'        => $itemSku,
        'item_name'       => $itemName,
        'uom'             => $uom,
        'movement_type'   => $data['movement_type'] ?? 'ADJUSTMENT',
        'qty_in'          => $qtyIn,
        'qty_out'         => $qtyOut,
        'balance_after'   => $balanceAfter,
        'unit_cost'       => $unitCost,
        'total_value'     => $totalVal,
        'reference_no'    => $data['reference_no'] ?? null,
        'reference_table' => $data['reference_table'] ?? null,
        'reference_id'    => $data['reference_id'] ?? null,
        'notes'           => $data['notes'] ?? null,
        'created_by'      => session()->get('name') ?? 'System',
        'created_at'      => date('Y-m-d H:i:s'),
    ]);
}
}

