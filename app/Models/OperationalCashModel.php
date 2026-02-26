<?php
namespace App\Models;
use CodeIgniter\Model;

class OperationalCashModel extends Model
{
    protected $table            = 'operational_cash';
    protected $primaryKey       = 'id';
    protected $allowedFields = [
        'transaction_code', 'transaction_date', 'type', 'metode', 'category', 
        'amount', 'description', 'pic_name', 'receipt_file'
    ];
    protected $useTimestamps    = true;
}