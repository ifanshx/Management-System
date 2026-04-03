<?php

namespace App\Models;
use CodeIgniter\Model;

class CashAdvanceModel extends Model
{
    protected $table            = 'cash_advances';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'employee_id', 'date', 'amount', 'tempo_date', 
        'description', 'status', 'payroll_id'
    ];
    protected $useTimestamps    = true;
}