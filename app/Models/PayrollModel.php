<?php
namespace App\Models;
use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table            = 'payrolls';
    protected $primaryKey       = 'id';
    
    // PERBAIKAN: Menambahkan 'employee_status' agar data tersimpan
    protected $allowedFields    = [
        'payroll_code', 'employee_status', 'salary_type', 'period_start', 'period_end', 
        'total_employees', 'total_amount', 'status'
    ];
    
    protected $useTimestamps    = true;
}