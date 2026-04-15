<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table      = 'employees';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'pin', 'employee_id', 'rfid', 'finger_count', 'face_count', 
        'machine_privilege', 'name', 'nik_ktp', 'phone', 'address', 
        'marital_status', 'department_id', 'position_id', 'specialty', 'leader_id', 'shift_id', 
        'status', 'payment_method', 'is_active', 'join_date', 'resign_date',
        'bank_name', 'bank_account', 'basic_salary', 'salary_type', 
        'position_allowance', 'meal_allowance', 'transport_allowance', 
        'overtime_rate', 'bpjs_kesehatan', 'bpjs_ks_number', 
        'bpjs_ketenagakerjaan', 'bpjs_tk_number', 'leave_balance', 
        'emergency_contact_name', 'emergency_contact_phone', 'grade_level'
    ];

    public function generateEmployeeId(): string
    {
        $year = date('Y');
        $lastEmployee = $this->like('employee_id', "NIK-$year-", 'after')
                             ->orderBy('employee_id', 'DESC')
                             ->first();
                                      
        $newNumber = $lastEmployee ? ((int) substr($lastEmployee['employee_id'], -3)) + 1 : 1;
        return "NIK-$year-" . str_pad((string)$newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function generateNewPin(): int
    {
        $lastPinUser = $this->orderBy('pin', 'DESC')->first();
        return ($lastPinUser && $lastPinUser['pin']) ? (int) $lastPinUser['pin'] + 1 : 1;
    }
}