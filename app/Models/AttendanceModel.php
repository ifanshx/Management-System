<?php

namespace App\Models;
use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    
    // HAPUS 'cash_advance' dan PASTIKAN 'is_meal_taken' ada di sini
    protected $allowedFields = [
        'employee_id', 'date', 'time_in', 'time_out', 'status', 
        'late_minutes', 'break_out', 'break_in', 'overtime_minutes', 
        'work_duration_minutes', 'is_meal_taken', 'photo_url', 'verify_method' // <--- TAMBAHKAN INI
    ];
    
    protected $useTimestamps    = true;

    // Fungsi khusus untuk mengambil rekap absen harian beserta nama dan divisinya
    public function getDailyAttendance($date)
    {
        return $this->db->table($this->table)
            ->select('attendances.*, employees.name, employees.department, employees.position')
            ->join('employees', 'employees.employee_id = attendances.employee_id', 'right') // Right join agar yang alpa tetap muncul
            ->where('attendances.date', $date)
            ->orderBy('employees.department', 'ASC')
            ->orderBy('employees.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}