<?php

namespace App\Controllers;
use App\Models\WorkShiftModel;

class Setting extends BaseController
{
    public function workshift_create()
    {
        // Pastikan hanya Manajemen/Admin yang bisa mengakses
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $data = [
            'title' => 'Konfigurasi Jam Kerja | Noric Workspace'
        ];
        return view('setting/workshift_create', $data);
    }

    public function workshift_store()
    {
        if (!session()->get('isLoggedIn') || (session()->get('role') !== 'admin')) {
            return redirect()->to('/portal');
        }

        $shiftModel = new WorkShiftModel();
        
        // Pembersihan Data Input sebelum masuk ke Database (Mencegah Error tipe data Decimal)
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
            
            // INI YANG PALING PENTING: Bersihkan titik dari angka uang
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
}