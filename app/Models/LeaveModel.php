<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveModel extends Model
{
    protected $table = 'leave_requests';
    protected $primaryKey = 'id';
    protected $allowedFields = ['employee_id', 'leave_type', 'start_date', 'end_date', 'duration', 'reason', 'attachment', 'status', 'reviewed_by'];

    // Ambil data cuti beserta nama, departemen, dan posisi karyawan (Untuk HRD)
    public function getAllWithEmployee(): array
    {
        // PERBAIKAN: Join tabel departments dan positions, lalu alias-kan kolom 'name'
        return $this->select('leave_requests.*, employees.name, departments.name as department, positions.name as position')
                    ->join('employees', 'employees.employee_id = leave_requests.employee_id')
                    ->join('departments', 'departments.id = employees.department_id', 'left')
                    ->join('positions', 'positions.id = employees.position_id', 'left')
                    ->orderBy('leave_requests.created_at', 'DESC')
                    ->findAll();
    }
}