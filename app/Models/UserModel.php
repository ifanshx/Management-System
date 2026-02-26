<?php

namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['employee_id', 'username', 'password', 'name', 'role'];
    protected $useTimestamps    = true;
}