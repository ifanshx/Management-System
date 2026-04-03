<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table      = 'employees';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'pin',
        'employee_id',
        'rfid',
        'finger_count',
        'face_count',
        'machine_privilege',

        'shift_id',
        'name',
        'phone',
        'address',
        'marital_status',
        'department',
        'position',

        'status',
        'payment_method',
        'join_date',
        'is_active',
        'resign_date',

        'bank_name',
        'bank_account',

        'basic_salary',
        'salary_type',
        'leave_balance',

        'position_allowance',
        'meal_allowance',
        'transport_allowance',
        'overtime_rate',

        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',

        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
}