<?php

namespace App\Controllers;

use App\Models\WorkShiftModel;
use App\Models\CompanyModel; // Panggil Model di sini agar lebih rapi

class Setting extends BaseController
{
    // ========================================================================
    // 1. PENGATURAN SHIFT KERJA (WORK SHIFT)
    // ========================================================================
    public function workshift_create()
    {
        // Pastikan hanya Manajemen/Admin yang bisa mengakses
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $data = [
            'title' => 'Konfigurasi Jam Kerja'
        ];
        return view('setting/workshift_create', $data);
    }

    public function workshift_store()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        // Pembersihan Data Input sebelum masuk ke Database
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
            
            // Bersihkan titik dari angka uang (Rupiah)
            'late_penalty_rate'     => str_replace(['Rp', '.', ' '], '', $this->request->getPost('late_penalty_rate')),
            
            'full_day_duration'     => $this->request->getPost('full_day_duration'),
            'half_day_duration'     => $this->request->getPost('half_day_duration'),
            'min_overtime'          => $this->request->getPost('min_overtime'),
            'max_overtime'          => $this->request->getPost('max_overtime'),
            'overtime_deduction'    => $this->request->getPost('overtime_deduction')
        ];

        $shiftModel->insert($dataToSave);
        
        return redirect()->to('/setting/workshift_create')->with('success', 'Konfigurasi aturan jam kerja dan kebijakan denda telat berhasil diaktifkan ke dalam sistem.');
    }

    // ========================================================================
    // 2. PENGATURAN IDENTITAS PERUSAHAAN (WHITE-LABEL ENGINE)
    // ========================================================================
    
    // Tampilkan Form Identitas Perusahaan
    public function company()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/portal');
        }

        $data = [
            'title' => 'Identitas Perusahaan'
        ];
        // Catatan: Variabel $company sudah otomatis terkirim ke view secara global dari BaseController!
        return view('setting/company', $data);
    }

    // Simpan Perubahan Identitas
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

        // --- SECURITY PATCH: Handle Upload Logo Baru dengan Validasi Ketat ---
        $logoFile = $this->request->getFile('logo_file');
        
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            
            // Aturan: Harus gambar, tipe spesifik, max 2MB
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

            // Hapus logo lama (agar server tidak penuh) kecuali logo default
            if ($dbData && !empty($dbData['logo_path']) && $dbData['logo_path'] !== 'default-logo.png') {
                $oldPath = ROOTPATH . 'public/uploads/logo/' . $dbData['logo_path'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Simpan gambar baru dengan nama terenkripsi acak
            $newName = $logoFile->getRandomName();
            $logoFile->move(ROOTPATH . 'public/uploads/logo/', $newName);
            $dataToUpdate['logo_path'] = $newName;
        }

        // Terapkan Perubahan ke Database
        if ($dbData) {
            $companyModel->update($dbData['id'], $dataToUpdate);
        } else {
            $companyModel->insert($dataToUpdate);
        }

        return redirect()->back()->with('success', 'Identitas Aplikasi berhasil diperbarui secara global!');
    }
}