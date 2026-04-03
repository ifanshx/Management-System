<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationalCashModel extends Model
{
    protected $table      = 'operational_cash';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'transaction_code',
        'transaction_date',
        'type',
        'metode',
        'category',
        'amount',
        'description',
        'pic_name',
        'receipt_file',
        'journal_id',
        'status',
        'approved_by',
        'approved_at',
        'created_by_user_id'
    ];

    protected $useTimestamps = false;
}