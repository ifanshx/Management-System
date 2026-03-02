<?php

namespace App\Controllers;

class Asset extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/portal');

        $assets = $this->db->table('factory_assets')->orderBy('id', 'DESC')->get()->getResultArray();

        $data = [
            'title'  => 'Manajemen Aset Mesin & Perawatan',
            'assets' => $assets
        ];

        return view('asset/index', $data);
    }

    // --- FUNGSI BARU: MENDATA MESIN BARU KE DALAM SISTEM ---
    public function store()
    {
        try {
            $assetCode = $this->request->getPost('asset_code');
            $assetName = $this->request->getPost('asset_name');
            $purchaseDate = $this->request->getPost('purchase_date');

            // Cek apakah kode aset sudah ada agar tidak duplikat
            $exists = $this->db->table('factory_assets')->where('asset_code', $assetCode)->countAllResults();
            if ($exists > 0) {
                throw new \Exception("Kode Aset $assetCode sudah terdaftar di sistem!");
            }

            $this->db->table('factory_assets')->insert([
                'asset_code'    => $assetCode,
                'asset_name'    => $assetName,
                'purchase_date' => $purchaseDate,
                'status'        => 'ACTIVE' // Secara default mesin baru berstatus Aktif
            ]);

            return redirect()->back()->with('success', "Aset mesin baru ($assetName) berhasil didaftarkan!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendaftar aset: ' . $e->getMessage());
        }
    }

    public function update_status($id)
    {
        $status = $this->request->getPost('status');
        $this->db->table('factory_assets')->where('id', $id)->update(['status' => $status]);
        
        $msg = ($status == 'MAINTENANCE') ? "Aset masuk masa perawatan." : (($status == 'ACTIVE') ? "Aset kembali beroperasi normal." : "Aset ditandai rusak / nonaktif!");
        
        return redirect()->back()->with('success', $msg);
    }

    // --- FITUR BARU: HAPUS ASET MESIN ---
    public function delete($id)
    {
        try {
            // Ambil nama aset untuk pesan notifikasi
            $asset = $this->db->table('factory_assets')->where('id', $id)->get()->getRowArray();
            if (!$asset) {
                throw new \Exception("Data mesin tidak ditemukan.");
            }

            // Hapus dari database
            $this->db->table('factory_assets')->where('id', $id)->delete();

            return redirect()->back()->with('success', "Aset mesin <b>{$asset['asset_name']}</b> berhasil dihapus secara permanen dari sistem.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }
}