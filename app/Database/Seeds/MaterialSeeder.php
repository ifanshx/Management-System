<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run()
    {
        // Data hasil ekstraksi rapat pabrik Noric Exhaust
        $materials = [
            ['sku_material' => 'MAT-ACI-965', 'material_name' => 'ACITILIN', 'unit' => 'Pcs', 'physical_stock' => 37],
            ['sku_material' => 'MAT-AMP-508', 'material_name' => 'AMPLAS SUSUN', 'unit' => 'Pcs', 'physical_stock' => 30],
            ['sku_material' => 'MAT-AMR-100', 'material_name' => 'AMRIL', 'unit' => 'Pcs', 'physical_stock' => 109],
            ['sku_material' => 'MAT-ANG-164', 'material_name' => 'ANGIN', 'unit' => 'Pcs', 'physical_stock' => 11],
            ['sku_material' => 'MAT-ARG-276', 'material_name' => 'GAS ARGON', 'unit' => 'Pcs', 'physical_stock' => 74],
            ['sku_material' => 'MAT-ATT-147', 'material_name' => 'ATT', 'unit' => 'Pcs', 'physical_stock' => 11],
            ['sku_material' => 'MAT-BAT-486', 'material_name' => 'BATU LANCER', 'unit' => 'Pcs', 'physical_stock' => 2],
            ['sku_material' => 'MAT-BAU-659', 'material_name' => 'BAUD L 2,5', 'unit' => 'Pcs', 'physical_stock' => 1561],
            ['sku_material' => 'MAT-BAU-607', 'material_name' => 'BAUD TAMENG', 'unit' => 'Pcs', 'physical_stock' => 551],
            ['sku_material' => 'MAT-BES-223', 'material_name' => 'BESI KECIL', 'unit' => 'Pcs', 'physical_stock' => 10],
            ['sku_material' => 'MAT-BLE-243', 'material_name' => 'BLEBED', 'unit' => 'Pcs', 'physical_stock' => 36],
            ['sku_material' => 'MAT-BLE-631', 'material_name' => 'BLENDER LAS', 'unit' => 'Pcs', 'physical_stock' => 2],
            ['sku_material' => 'MAT-CO2-656', 'material_name' => 'GAS CO2', 'unit' => 'Pcs', 'physical_stock' => 1],
            ['sku_material' => 'MAT-CON-639', 'material_name' => 'CONTONGAN', 'unit' => 'Pcs', 'physical_stock' => 3],
            ['sku_material' => 'MAT-EMB-983', 'material_name' => 'EMBLEM NORIC', 'unit' => 'Pcs', 'physical_stock' => 51],
            ['sku_material' => 'MAT-GAN-970', 'material_name' => 'GANTUNGAN GESER', 'unit' => 'Pcs', 'physical_stock' => 280],
            ['sku_material' => 'MAT-GAN-880', 'material_name' => 'GANTUNGAN KAKI 3', 'unit' => 'Pcs', 'physical_stock' => 590],
            ['sku_material' => 'MAT-GAN-881', 'material_name' => 'GANTUNGAN NORIC MONEL', 'unit' => 'Pcs', 'physical_stock' => 329],
            ['sku_material' => 'MAT-GAN-542', 'material_name' => 'GANTUNGAN NORIC ST', 'unit' => 'Pcs', 'physical_stock' => 254],
            ['sku_material' => 'MAT-GAN-373', 'material_name' => 'GANTUNGAN TAMENG', 'unit' => 'Pcs', 'physical_stock' => 1120],
            ['sku_material' => 'MAT-GAS-293', 'material_name' => 'GASBUL (GLASSWOOL)', 'unit' => 'Pcs', 'physical_stock' => 9],
            ['sku_material' => 'MAT-GRE-232', 'material_name' => 'GRENDA', 'unit' => 'Pcs', 'physical_stock' => 522],
            ['sku_material' => 'MAT-KAI-495', 'material_name' => 'KAIN POLES', 'unit' => 'Pcs', 'physical_stock' => 1],
            ['sku_material' => 'MAT-KAT-410', 'material_name' => 'KARET / KATER', 'unit' => 'Pcs', 'physical_stock' => 850],
            ['sku_material' => 'MAT-KRA-867', 'material_name' => 'KERAMIK NO 4', 'unit' => 'Pcs', 'physical_stock' => 5],
            ['sku_material' => 'MAT-KRA-760', 'material_name' => 'KERAMIK NO 5', 'unit' => 'Pcs', 'physical_stock' => 5],
            ['sku_material' => 'MAT-KRA-264', 'material_name' => 'KERAMIK NO 8', 'unit' => 'Pcs', 'physical_stock' => 10],
            ['sku_material' => 'MAT-KUN-286', 'material_name' => 'KUNINGAN BESAR', 'unit' => 'Pcs', 'physical_stock' => 2015],
            ['sku_material' => 'MAT-KUP-715', 'material_name' => 'KUPINGAN KECIL', 'unit' => 'Kg', 'physical_stock' => 26],
            ['sku_material' => 'MAT-KUP-449', 'material_name' => 'KUPINGAN ORI', 'unit' => 'Pcs', 'physical_stock' => 5017],
            ['sku_material' => 'MAT-LET-626', 'material_name' => 'LETER S KOLONG', 'unit' => 'Pcs', 'physical_stock' => 23],
            ['sku_material' => 'MAT-LIS-750', 'material_name' => 'LIST 3IN HALINTAR', 'unit' => 'Pcs', 'physical_stock' => 11],
            ['sku_material' => 'MAT-LIS-649', 'material_name' => 'LIST NORIC UNGU', 'unit' => 'Pcs', 'physical_stock' => 51],
            ['sku_material' => 'MAT-NOZ-254', 'material_name' => 'NOZZLE', 'unit' => 'Pcs', 'physical_stock' => 10],
            ['sku_material' => 'MAT-PIL-946', 'material_name' => 'PILOK', 'unit' => 'Pcs', 'physical_stock' => 1],
            
            // Perbaikan SKU Pipa & Plat agar standar ERP (Ditambahkan awalan MAT-)
            ['sku_material' => 'MAT-PIP-38', 'material_name' => 'PIPA STAINLESS 38 0,8', 'unit' => 'Batang', 'physical_stock' => 171],
            ['sku_material' => 'MAT-PIP-44', 'material_name' => 'PIPA STAINLESS 44 0,8', 'unit' => 'Batang', 'physical_stock' => 142],
            ['sku_material' => 'MAT-PIP-2IN', 'material_name' => 'PIPA STAINLESS 2IN 0,8', 'unit' => 'Batang', 'physical_stock' => 51],
            ['sku_material' => 'MAT-PIP-35', 'material_name' => 'PIPA STAINLESS 35 0,8', 'unit' => 'Batang', 'physical_stock' => 42],
            ['sku_material' => 'MAT-PIP-32', 'material_name' => 'PIPA STAINLESS 32 0,8', 'unit' => 'Batang', 'physical_stock' => 117],
            ['sku_material' => 'MAT-PIP-118', 'material_name' => 'PIPA BENDING 28 90', 'unit' => 'Pcs', 'physical_stock' => 10],
            
            ['sku_material' => 'MAT-PIR-261', 'material_name' => 'PIR (PER KNALPOT)', 'unit' => 'Kg', 'physical_stock' => 34],
            ['sku_material' => 'MAT-PLA-670', 'material_name' => 'PLASTIK PACKAGING', 'unit' => 'Kg', 'physical_stock' => 43],
            ['sku_material' => 'MAT-PLA-06', 'material_name' => 'PLAT STAINLESS 06', 'unit' => 'Lembar', 'physical_stock' => 20136],
            
            ['sku_material' => 'MAT-PP-543', 'material_name' => 'PP BEAT NETRAL', 'unit' => 'Pcs', 'physical_stock' => 600],
            ['sku_material' => 'MAT-PP-993', 'material_name' => 'PP BEBEK (Besar)', 'unit' => 'Pcs', 'physical_stock' => 3600],
            ['sku_material' => 'MAT-PP-826', 'material_name' => 'PP BEBEK (Kecil)', 'unit' => 'Pcs', 'physical_stock' => 1214],
            ['sku_material' => 'MAT-PP-748', 'material_name' => 'PP MIO', 'unit' => 'Pcs', 'physical_stock' => 60],
            ['sku_material' => 'MAT-PP-816', 'material_name' => 'PP TIGER', 'unit' => 'Pcs', 'physical_stock' => 4100],
            ['sku_material' => 'MAT-PP-706', 'material_name' => 'PP VIXION', 'unit' => 'Pcs', 'physical_stock' => 2300],
            
            ['sku_material' => 'MAT-RAJ-654', 'material_name' => 'RAJANGAN PIPA (CAMPUR)', 'unit' => 'Batang', 'physical_stock' => 41561],
            ['sku_material' => 'MAT-REG-158', 'material_name' => 'REGULATOR ARGON', 'unit' => 'Pcs', 'physical_stock' => 5],
            ['sku_material' => 'MAT-REG-715', 'material_name' => 'REGULATOR ACITILIN', 'unit' => 'Pcs', 'physical_stock' => 5],
            ['sku_material' => 'MAT-SAR-526', 'material_name' => 'SARUNG TANGAN', 'unit' => 'Pcs', 'physical_stock' => 2],
            ['sku_material' => 'MAT-SUP-641', 'material_name' => 'SUPRA 125 PP (BENDING)', 'unit' => 'Pcs', 'physical_stock' => 10],
            ['sku_material' => 'MAT-TUN-375', 'material_name' => 'TUNGSTEN', 'unit' => 'Pcs', 'physical_stock' => 3],
            ['sku_material' => 'MAT-TUT-466', 'material_name' => 'TUTUP 3IN', 'unit' => 'Pcs', 'physical_stock' => 400],
            ['sku_material' => 'MAT-TUT-700', 'material_name' => 'TUTUP JENONG 3,5IN', 'unit' => 'Pcs', 'physical_stock' => 260],
            ['sku_material' => 'MAT-TUT-639', 'material_name' => 'TUTUP RATA 3,5IN', 'unit' => 'Pcs', 'physical_stock' => 256],
            ['sku_material' => 'MAT-TUT-518', 'material_name' => 'TUTUP 2,5IN RATA', 'unit' => 'Pcs', 'physical_stock' => 105],
        ];

        // Insert massal ke tabel raw_materials
        foreach ($materials as $item) {
            // Cek apakah SKU sudah ada agar tidak error duplikat
            $exists = $this->db->table('raw_materials')->where('sku_material', $item['sku_material'])->countAllResults();
            if ($exists == 0) {
                // Set default HPP 0, nanti bisa di-edit di halaman Gudang
                $item['hpp'] = 0; 
                $item['min_stock'] = 10;
                $this->db->table('raw_materials')->insert($item);
            }
        }
    }
}