<?php

namespace App\Models;
use CodeIgniter\Model;

class WorkShiftModel extends Model
{
    protected $table            = 'work_shifts';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'shift_name', 'shift_type', 'time_in', 'scan_in_before', 'scan_in_after',
        'time_out', 'scan_out_before', 'scan_out_after', 'break_out', 'break_in',
        'late_tolerance', 'early_leave_tolerance', 'half_day_duration', 'full_day_duration',
        'min_overtime', 'max_overtime', 'overtime_deduction', 
        'late_penalty_rate'
    ];
    protected $useTimestamps    = true;
}