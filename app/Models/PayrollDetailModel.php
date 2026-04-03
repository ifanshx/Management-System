<?php

namespace App\Models;
use CodeIgniter\Model;

class PayrollDetailModel extends Model
{
    protected $table      = 'payroll_details';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'payroll_id',
        'employee_id',
        'total_present',
        'total_late_minutes',
        'total_overtime_minutes',
        'basic_salary',
        'borongan_pay',
        'position_allowance',
        'meal_allowance',
        'transport_allowance',
        'overtime_pay',
        'late_penalty',
        'bpjs_deduction',
        'cash_advance',
        'net_salary'
    ];

    // MATIKAN INI AGAR TIDAK ERROR (Karena tabel payroll_details tidak punya kolom created_at)
    protected $useTimestamps = false; 
}