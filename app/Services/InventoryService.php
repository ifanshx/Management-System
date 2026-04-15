<?php

namespace App\Services;

use Exception;

class InventoryService
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * ==============================================================================
     * 1. PENAMBAHAN MATERIAL MENTAH (PEMBELIAN / PROCUREMENT)
     * ==============================================================================
     * Menambah stok material mentah (MAT) dan menghitung ulang HPP (Moving Average).
     */
    public function receiveRawMaterial(string $sku, float $qtyIn, float $totalPrice, string $referenceNo, int $referenceId = null, string $notes = 'Penerimaan Material')
    {
        // PERBAIKAN: Gunakan Raw Query untuk mengeksekusi FOR UPDATE di CI4
        $sql = "SELECT * FROM raw_materials WHERE sku_material = ? FOR UPDATE";
        $rm = $this->db->query($sql, [$sku])->getRowArray();

        if (!$rm) {
            throw new Exception("GAGAL! Material dengan SKU {$sku} tidak ditemukan di master data.");
        }

        $oldQty = (float) $rm['physical_stock'];
        $oldHpp = (float) $rm['hpp'];
        $newQty = $oldQty + $qtyIn;

        // Cegah pembagian dengan nol
        $newHpp = 0;
        if ($newQty > 0) {
            $newHpp = (($oldQty * $oldHpp) + $totalPrice) / $newQty;
        }

        // Update Stok dan HPP Baru
        $this->db->table('raw_materials')->where('sku_material', $sku)->update([
            'physical_stock' => $newQty,
            'hpp'            => $newHpp
        ]);

        // Catat ke Log Mutasi (Inventory Movements)
        $unitCost = ($qtyIn > 0) ? ($totalPrice / $qtyIn) : 0;
        $this->logMovement('RAW', $sku, $rm['material_name'], $rm['base_uom'] ?? $rm['unit'], 'RECEIVING', $qtyIn, 0, $newQty, $unitCost, $totalPrice, $referenceNo, 'purchase_orders', $referenceId, $notes);

        return $newHpp;
    }

    /**
     * ==============================================================================
     * 2. PENGURANGAN MATERIAL MENTAH (PRODUKSI / KASBON BAHAN)
     * ==============================================================================
     */
    public function consumeRawMaterial(string $sku, float $qtyOut, string $referenceNo, int $referenceId = null, string $notes = 'Pemakaian Produksi')
    {
        // PERBAIKAN: Raw Query FOR UPDATE
        $sql = "SELECT * FROM raw_materials WHERE sku_material = ? FOR UPDATE";
        $rm = $this->db->query($sql, [$sku])->getRowArray();

        if (!$rm) {
            throw new Exception("GAGAL! Material {$sku} tidak ditemukan.");
        }

        $oldQty = (float) $rm['physical_stock'];
        $hpp    = (float) $rm['hpp'];

        if ($oldQty < $qtyOut) {
            $unit = $rm['base_uom'] ?? $rm['unit'] ?? 'PCS';
            throw new Exception("STOK KURANG! Sisa material {$sku} hanya {$oldQty} {$unit}, tapi Anda mencoba menarik {$qtyOut} {$unit}.");
        }

        $newQty = $oldQty - $qtyOut;
        $totalCost = $qtyOut * $hpp;

        $this->db->table('raw_materials')->where('sku_material', $sku)->update([
            'physical_stock' => $newQty
        ]);

        $this->logMovement('RAW', $sku, $rm['material_name'], $rm['base_uom'] ?? $rm['unit'], 'PRODUCTION_OUT', 0, $qtyOut, $newQty, $hpp, $totalCost, $referenceNo, 'work_orders', $referenceId, $notes);

        return $totalCost; // Mengembalikan total biaya bahan yang terpakai untuk dijurnal nanti
    }

    /**
     * ==============================================================================
     * 3. PENAMBAHAN BARANG JADI (HASIL PRODUKSI)
     * ==============================================================================
     */
    public function addFinishedGood(string $sku, float $qtyIn, float $totalProductionCost, string $referenceNo, int $referenceId = null, string $notes = 'Hasil Produksi')
    {
        // PERBAIKAN: Raw Query FOR UPDATE
        $sql = "SELECT * FROM warehouse_inventory WHERE sku = ? FOR UPDATE";
        $fg = $this->db->query($sql, [$sku])->getRowArray();

        if (!$fg) {
            throw new Exception("GAGAL! Produk Jadi {$sku} tidak ditemukan di gudang.");
        }

        $oldQty = (float) $fg['physical_stock'];
        $oldHpp = (float) $fg['hpp'];
        $newQty = $oldQty + $qtyIn;

        $newHpp = 0;
        if ($newQty > 0) {
            $newHpp = (($oldQty * $oldHpp) + $totalProductionCost) / $newQty;
        }

        $this->db->table('warehouse_inventory')->where('sku', $sku)->update([
            'physical_stock' => $newQty,
            'hpp'            => $newHpp
        ]);

        $unitCost = ($qtyIn > 0) ? ($totalProductionCost / $qtyIn) : 0;
        $this->logMovement('FG', $sku, $fg['item_name'], 'PCS', 'PRODUCTION_IN', $qtyIn, 0, $newQty, $unitCost, $totalProductionCost, $referenceNo, 'work_orders', $referenceId, $notes);

        return $newHpp;
    }

    /**
     * ==============================================================================
     * 4. PENGURANGAN BARANG JADI (PENJUALAN / PENGIRIMAN B2B)
     * ==============================================================================
     */
    public function shipFinishedGood(string $sku, float $qtyOut, string $referenceNo, int $referenceId = null, string $notes = 'Pengiriman Pesanan')
    {
        // PERBAIKAN: Raw Query FOR UPDATE
        $sql = "SELECT * FROM warehouse_inventory WHERE sku = ? FOR UPDATE";
        $fg = $this->db->query($sql, [$sku])->getRowArray();

        if (!$fg) {
            throw new Exception("GAGAL! Produk Jadi {$sku} tidak ditemukan.");
        }

        $oldQty = (float) $fg['physical_stock'];
        $hpp    = (float) $fg['hpp'];

        if ($oldQty < $qtyOut) {
            throw new Exception("STOK KURANG! Knalpot {$sku} sisa {$oldQty} Pcs di gudang, gagal mengirim {$qtyOut} Pcs.");
        }

        $newQty = $oldQty - $qtyOut;
        $totalCost = $qtyOut * $hpp;

        $this->db->table('warehouse_inventory')->where('sku', $sku)->update([
            'physical_stock' => $newQty
        ]);

        $this->logMovement('FG', $sku, $fg['item_name'], 'PCS', 'SALES_OUT', 0, $qtyOut, $newQty, $hpp, $totalCost, $referenceNo, 'b2b_sales_orders', $referenceId, $notes);

        return $totalCost; // HPP yang akan dibebankan di Jurnal Penjualan
    }
    
    /**
     * ==============================================================================
     * PEMBATALAN PENERIMAAN BARANG (VOID PO)
     * ==============================================================================
     */
    public function voidReceipt(string $sku, float $qtyIn, float $totalPrice, string $referenceNo, int $referenceId = null, string $notes = 'Batal Penerimaan PO')
    {
        $sql = "SELECT * FROM raw_materials WHERE sku_material = ? FOR UPDATE";
        $rm = $this->db->query($sql, [$sku])->getRowArray();

        if (!$rm) throw new Exception("Material {$sku} tidak ditemukan.");

        $oldQty = (float) $rm['physical_stock'];
        $oldHpp = (float) $rm['hpp'];

        // Proteksi: Jangan biarkan batal jika stok sudah terpakai produksi
        if ($oldQty < $qtyIn) {
            throw new Exception("STOK TIDAK BISA DITARIK! Material [{$sku}] sudah terpakai untuk produksi. Sisa stok: {$oldQty}, Batal: {$qtyIn}");
        }

        $newQty = $oldQty - $qtyIn;
        $newHpp = 0;
        
        // Kembalikan HPP ke rata-rata sebelumnya (Reverse Moving Average)
        if ($newQty > 0) {
            $calcHpp = (($oldQty * $oldHpp) - $totalPrice) / $newQty;
            $newHpp = $calcHpp > 0 ? $calcHpp : 0;
        }

        $this->db->table('raw_materials')->where('sku_material', $sku)->update([
            'physical_stock' => $newQty,
            'hpp'            => $newHpp
        ]);

        $unitCost = ($qtyIn > 0) ? ($totalPrice / $qtyIn) : 0;
        $this->logMovement('RAW', $sku, $rm['material_name'], $rm['base_uom'] ?? $rm['unit'], 'VOID_RECEIVING', 0, $qtyIn, $newQty, $unitCost, $totalPrice, $referenceNo, 'purchase_orders', $referenceId, $notes);
    }

    /**
     * ==============================================================================
     * 5. FUNGSI INTI PENCATATAN LOG MUTASI STOK
     * ==============================================================================
     */
    private function logMovement($itemType, $sku, $itemName, $uom, $movementType, $qtyIn, $qtyOut, $balanceAfter, $unitCost, $totalValue, $refNo, $refTable, $refId, $notes)
    {
        $this->db->table('inventory_movements')->insert([
            'movement_date'   => date('Y-m-d H:i:s'),
            'item_type'       => $itemType,
            'item_sku'        => $sku,
            'item_name'       => $itemName,
            'uom'             => $uom ?: 'PCS',
            'movement_type'   => $movementType,
            'qty_in'          => $qtyIn,
            'qty_out'         => $qtyOut,
            'balance_after'   => $balanceAfter,
            'unit_cost'       => $unitCost,
            'total_value'     => $totalValue,
            'reference_no'    => $refNo,
            'reference_table' => $refTable,
            'reference_id'    => $refId,
            'notes'           => $notes,
            'created_by'      => session()->get('name') ?? 'System'
        ]);
    }
    
    /**
     * Memotong stok otomatis: Cek apakah dia Bahan Baku (RAW) atau Barang Jadi / Sub-Assembly (FG)
     */
    public function consumeMaterialOrFG(string $sku, float $qtyOut, string $referenceNo, int $referenceId = null, string $notes = 'Pemakaian Produksi')
    {
        // PERBAIKAN: Raw Query FOR UPDATE
        $sqlRm = "SELECT * FROM raw_materials WHERE sku_material = ? FOR UPDATE";
        $rm = $this->db->query($sqlRm, [$sku])->getRowArray();
        
        if ($rm) {
            $oldQty = (float) $rm['physical_stock'];
            $hpp    = (float) $rm['hpp'];
            $unit   = $rm['base_uom'] ?? $rm['unit'] ?? 'PCS';

            if ($oldQty < $qtyOut) throw new \Exception("STOK KURANG! Sisa material {$sku} hanya {$oldQty} {$unit}.");

            $newQty = $oldQty - $qtyOut;
            $totalCost = $qtyOut * $hpp;

            $this->db->table('raw_materials')->where('sku_material', $sku)->update(['physical_stock' => $newQty]);
            $this->logMovement('RAW', $sku, $rm['material_name'], $unit, 'PRODUCTION_OUT', 0, $qtyOut, $newQty, $hpp, $totalCost, $referenceNo, 'work_orders', $referenceId, $notes);
            
            return ['cost' => $totalCost, 'name' => $rm['material_name'], 'unit' => $unit, 'hpp' => $hpp];
        }

        // Jika tidak ada di Raw Materials, cari di Warehouse Inventory (Barang Jadi / Sub-Assembly)
        $sqlFg = "SELECT * FROM warehouse_inventory WHERE sku = ? FOR UPDATE";
        $fg = $this->db->query($sqlFg, [$sku])->getRowArray();
        
        if ($fg) {
            $oldQty = (float) $fg['physical_stock'];
            $hpp    = (float) $fg['hpp'];

            if ($oldQty < $qtyOut) throw new \Exception("STOK KURANG! Komponen Sub-Assembly {$sku} sisa {$oldQty} PCS.");

            $newQty = $oldQty - $qtyOut;
            $totalCost = $qtyOut * $hpp;

            $this->db->table('warehouse_inventory')->where('sku', $sku)->update(['physical_stock' => $newQty]);
            $this->logMovement('FG', $sku, $fg['item_name'], 'PCS', 'PRODUCTION_OUT', 0, $qtyOut, $newQty, $hpp, $totalCost, $referenceNo, 'work_orders', $referenceId, $notes);
            
            return ['cost' => $totalCost, 'name' => $fg['item_name'], 'unit' => 'PCS', 'hpp' => $hpp];
        }

        throw new \Exception("GAGAL CRITICAL! Material / Komponen dengan SKU <b>{$sku}</b> tidak ditemukan sama sekali di Gudang.");
    }
}