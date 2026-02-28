<?php
namespace App\Models;
use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table            = 'company_settings';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['app_name', 'company_name', 'address', 'phone', 'logo_path'];
}