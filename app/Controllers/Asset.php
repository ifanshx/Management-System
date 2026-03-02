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

    public function update_status($id)
    {
        $status = $this->request->getPost('status');
        $this->db->table('factory_assets')->where('id', $id)->update(['status' => $status]);
        
        $msg = ($status == 'MAINTENANCE') ? "Aset masuk masa perawatan." : (($status == 'ACTIVE') ? "Aset kembali beroperasi normal." : "Aset ditandai rusak!");
        
        return redirect()->back()->with('success', $msg);
    }
}