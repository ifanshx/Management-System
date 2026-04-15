<?php

namespace App\Controllers;

class StockCard extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $itemType = $this->request->getGet('item_type') ?? '';
        $itemSku  = $this->request->getGet('item_sku') ?? '';
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-01');
        $dateTo   = $this->request->getGet('date_to') ?? date('Y-m-d');

        $builder = $this->db->table('inventory_movements')
            ->where('DATE(movement_date) >=', $dateFrom)
            ->where('DATE(movement_date) <=', $dateTo);

        if (!empty($itemType)) {
            $builder->where('item_type', strtoupper($itemType));
        }

        if (!empty($itemSku)) {
            $builder->where('item_sku', $itemSku);
        }

        $movements = $builder->orderBy('movement_date', 'ASC')->get()->getResultArray();

        $rawMaterials = $this->db->table('raw_materials')
            ->select('sku_material as sku, material_name as item_name')
            ->orderBy('material_name', 'ASC')
            ->get()->getResultArray();

        $fgItems = $this->db->table('warehouse_inventory')
            ->select('sku, item_name')
            ->orderBy('item_name', 'ASC')
            ->get()->getResultArray();

        return view('inventory/stock_card', [
            'title'        => 'Kartu Stok',
            'movements'    => $movements,
            'rawMaterials' => $rawMaterials,
            'fgItems'      => $fgItems,
            'itemType'     => $itemType,
            'itemSku'      => $itemSku,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }
}