<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    
    // PERBAIKAN: Masukkan is_overtime_taken dan overtime_meal_amount ke sini
    protected $allowedFields    = [
        'employee_id', 
        'date', 
        'time_in', 
        'break_out', 
        'break_in', 
        'time_out', 
        'status', 
        'is_meal_taken', 
        'is_overtime_taken',      // Ditambahkan
        'overtime_meal_amount',   // Ditambahkan
        'photo_url', 
        'verify_method', 
        'late_minutes', 
        'overtime_minutes', 
        'work_duration_minutes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}