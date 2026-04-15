<?php

namespace App\Controllers;

use App\Models\WorkShiftModel;
use App\Models\CompanyModel;
use App\Models\LandingCatalogModel;

class Setting extends BaseController
{
    // ========================================================================
    // 1. PENGATURAN SHIFT KERJA (WORK SHIFT)
    // ========================================================================
    
    // Menampilkan daftar shift kerja
    public function workshift_index()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        $data = [
            'title'  => 'Daftar Jam Kerja',
            'shifts' => $shiftModel->findAll()
        ];
        
        return view('setting/workshift_index', $data);
    }

    // Menampilkan form tambah shift
    public function workshift_create()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $data = [
            'title' => 'Buat Jam Kerja Baru'
        ];
        return view('setting/workshift_create', $data);
    }

    // Menyimpan data shift baru
    public function workshift_store()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        $dataToSave = [
            'shift_name'            => $this->request->getPost('shift_name'),
            'shift_type'            => $this->request->getPost('shift_type'),
            'time_in'               => $this->request->getPost('time_in'),
            'scan_in_before'        => $this->request->getPost('scan_in_before'),
            'scan_in_after'         => $this->request->getPost('scan_in_after'),
            'time_out'              => $this->request->getPost('time_out'),
            'scan_out_before'       => $this->request->getPost('scan_out_before'),
            'scan_out_after'        => $this->request->getPost('scan_out_after'),
            'break_out'             => $this->request->getPost('break_out'),
            'break_in'              => $this->request->getPost('break_in'),
            'late_tolerance'        => $this->request->getPost('late_tolerance'),
            'early_leave_tolerance' => $this->request->getPost('early_leave_tolerance'),
            'late_penalty_rate'     => str_replace(['Rp', '.', ' '], '', $this->request->getPost('late_penalty_rate')),
            'full_day_duration'     => $this->request->getPost('full_day_duration'),
            'half_day_duration'     => $this->request->getPost('half_day_duration'),
            'min_overtime'          => $this->request->getPost('min_overtime'),
            'max_overtime'          => $this->request->getPost('max_overtime'),
            'overtime_deduction'    => $this->request->getPost('overtime_deduction')
        ];

        $shiftModel->insert($dataToSave);
        
        return redirect()->to('/setting/workshift_index')->with('success', 'Jam kerja baru berhasil ditambahkan.');
    }

    // Menampilkan form edit shift
    public function workshift_edit($id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        $shift = $shiftModel->find($id);

        if (!$shift) {
            return redirect()->to('/setting/workshift_index')->with('error', 'Jam kerja tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Jam Kerja',
            'shift' => $shift
        ];
        return view('setting/workshift_edit', $data);
    }

    // Menyimpan perubahan shift
    public function workshift_update($id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        $dataToUpdate = [
            'shift_name'            => $this->request->getPost('shift_name'),
            'shift_type'            => $this->request->getPost('shift_type'),
            'time_in'               => $this->request->getPost('time_in'),
            'scan_in_before'        => $this->request->getPost('scan_in_before'),
            'scan_in_after'         => $this->request->getPost('scan_in_after'),
            'time_out'              => $this->request->getPost('time_out'),
            'scan_out_before'       => $this->request->getPost('scan_out_before'),
            'scan_out_after'        => $this->request->getPost('scan_out_after'),
            'break_out'             => $this->request->getPost('break_out'),
            'break_in'              => $this->request->getPost('break_in'),
            'late_tolerance'        => $this->request->getPost('late_tolerance'),
            'early_leave_tolerance' => $this->request->getPost('early_leave_tolerance'),
            'late_penalty_rate'     => str_replace(['Rp', '.', ' '], '', $this->request->getPost('late_penalty_rate')),
            'full_day_duration'     => $this->request->getPost('full_day_duration'),
            'half_day_duration'     => $this->request->getPost('half_day_duration'),
            'min_overtime'          => $this->request->getPost('min_overtime'),
            'max_overtime'          => $this->request->getPost('max_overtime'),
            'overtime_deduction'    => $this->request->getPost('overtime_deduction')
        ];

        $shiftModel->update($id, $dataToUpdate);
        
        return redirect()->to('/setting/workshift_index')->with('success', 'Konfigurasi jam kerja berhasil diperbarui.');
    }

    // Menghapus shift
    public function workshift_delete($id)
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        // Cek apakah shift dipakai oleh karyawan sebelum dihapus
        $db = \Config\Database::connect();
        $isUsed = $db->table('employees')->where('shift_id', $id)->countAllResults();
        
        if ($isUsed > 0) {
            return redirect()->to('/setting/workshift_index')->with('error', 'Gagal dihapus. Jam kerja ini sedang digunakan oleh ' . $isUsed . ' karyawan.');
        }

        $shiftModel->delete($id);
        
        return redirect()->to('/setting/workshift_index')->with('success', 'Jam kerja berhasil dihapus.');
    }

    // ========================================================================
    // 2. PENGATURAN IDENTITAS PERUSAHAAN & KATALOG (WHITE-LABEL ENGINE)
    // ========================================================================
    
    public function company()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $catalogModel = new LandingCatalogModel();

        $data = [
            'title'    => 'Identitas Perusahaan',
            'catalogs' => $catalogModel->findAll() 
        ];
        
        return view('setting/company', $data);
    }

    public function update_company()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $companyModel = new CompanyModel();
        $dbData = $companyModel->first();
        
        $dataToUpdate = [
            'app_name'     => $this->request->getPost('app_name'),
            'company_name' => $this->request->getPost('company_name'),
            'address'      => $this->request->getPost('address'),
            'phone'        => $this->request->getPost('phone')
        ];

        $logoFile = $this->request->getFile('logo_file');
        
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            
            $validationRule = [
                'logo_file' => [
                    'rules' => 'max_size[logo_file,2048]|is_image[logo_file]|mime_in[logo_file,image/png,image/jpg,image/jpeg]',
                    'errors' => [
                        'max_size' => 'Ukuran logo terlalu besar, maksimal 2MB.',
                        'is_image' => 'File yang diunggah harus berupa gambar.',
                        'mime_in'  => 'Format gambar tidak didukung. Harap gunakan PNG atau JPG.'
                    ]
                ]
            ];

            if (!$this->validate($validationRule)) {
                return redirect()->back()->withInput()->with('error', $this->validator->getError('logo_file'));
            }

            // Gunakan FCPATH untuk kompatibilitas cPanel
            $uploadPath = FCPATH . 'uploads/logo/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($dbData && !empty($dbData['logo_path']) && $dbData['logo_path'] !== 'default-logo.png') {
                $oldPath = $uploadPath . $dbData['logo_path'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadPath, $newName);
            $dataToUpdate['logo_path'] = $newName;
        }

        if ($dbData) {
            $companyModel->update($dbData['id'], $dataToUpdate);
        } else {
            $companyModel->insert($dataToUpdate);
        }

        return redirect()->back()->with('success', 'Identitas Aplikasi berhasil diperbarui secara global!');
    }

    public function store_catalog()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $catalogModel = new LandingCatalogModel();
        
        $dataToSave = [
            'product_name'   => $this->request->getPost('product_name'),
            'category'       => $this->request->getPost('category'),
            'price'          => preg_replace('/[^0-9]/', '', $this->request->getPost('price')),
            'discount_price' => preg_replace('/[^0-9]/', '', $this->request->getPost('discount_price') ?? 0),
            'specs'          => $this->request->getPost('specs'),
            'badge_text'     => $this->request->getPost('badge_text'),
            'icon_class'     => $this->request->getPost('icon_class'),
            'shopee_link'    => $this->request->getPost('shopee_link'),
            'wa_link'        => $this->request->getPost('wa_link'),
        ];

        // LOGIKA UPLOAD GAMBAR PRODUK DENGAN FCPATH
        $imgFile = $this->request->getFile('product_image');
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/catalogs/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newName = $imgFile->getRandomName();
            $imgFile->move($uploadPath, $newName); 
            $dataToSave['product_image'] = $newName;
        }

        $catalogModel->save($dataToSave);
        return redirect()->to('/setting/company')->with('success', 'Produk katalog berhasil ditambahkan!');
    }

    public function update_catalog($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $catalogModel = new LandingCatalogModel();
        
        $dataToUpdate = [
            'product_name'   => $this->request->getPost('product_name'),
            'category'       => $this->request->getPost('category'),
            'price'          => preg_replace('/[^0-9]/', '', $this->request->getPost('price')),
            'discount_price' => preg_replace('/[^0-9]/', '', $this->request->getPost('discount_price') ?? 0),
            'specs'          => $this->request->getPost('specs'),
            'badge_text'     => $this->request->getPost('badge_text'),
            'icon_class'     => $this->request->getPost('icon_class'),
            'shopee_link'    => $this->request->getPost('shopee_link'),
            'wa_link'        => $this->request->getPost('wa_link'),
        ];

        // LOGIKA UPDATE GAMBAR PRODUK DENGAN FCPATH
        $imgFile = $this->request->getFile('product_image');
        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/catalogs/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Hapus gambar lama jika ada
            $oldData = $catalogModel->find($id);
            if ($oldData && !empty($oldData['product_image'])) {
                $oldImagePath = $uploadPath . $oldData['product_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $newName = $imgFile->getRandomName();
            $imgFile->move($uploadPath, $newName); 
            $dataToUpdate['product_image'] = $newName;
        }
        
        $catalogModel->update($id, $dataToUpdate);
        
        return redirect()->to('/setting/company')->with('success', 'Produk katalog berhasil diperbarui!');
    }

    public function delete_catalog($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') return redirect()->to('/portal');

        $catalogModel = new LandingCatalogModel();
        $oldData = $catalogModel->find($id);
        
        // Hapus gambar fisik di cPanel agar tidak menuhi storage
        if ($oldData && !empty($oldData['product_image'])) {
            $oldImagePath = FCPATH . 'uploads/catalogs/' . $oldData['product_image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $catalogModel->delete($id);
        return redirect()->to('/setting/company')->with('success', 'Katalog berhasil dihapus!');
    }
}