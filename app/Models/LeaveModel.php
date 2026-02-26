<?php

namespace App\Models;
use CodeIgniter\Model;

class LeaveModel extends Model
{
    protected $table            = 'leave_requests';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'employee_id', 'leave_type', 'start_date', 'end_date', 
        'duration', 'reason', 'attachment', 'status', 'reviewed_by'
    ];

    // Ambil data cuti beserta nama karyawannya (Untuk HRD)
    public function getAllWithEmployee()
    {
        return $this->select('leave_requests.*, employees.name, employees.department, employees.position')
                    ->join('employees', 'employees.employee_id = leave_requests.employee_id')
                    ->orderBy('leave_requests.created_at', 'DESC')
                    ->findAll();
    }
}