<?php

namespace App\Models;
use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    protected $allowedFields = [
        'employee_id', 'date', 'time_in', 'time_out', 'status', 'late_minutes',
        'break_out', 'break_in', 'overtime_minutes', 'work_duration_minutes' // <-- TAMBAHKAN INI
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