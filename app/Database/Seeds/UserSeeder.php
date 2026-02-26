<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username'   => 'admin',
            // admin123 adalah password yang akan kamu ketik saat login
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'name'       => 'Administrator Noric',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Masukkan data ke tabel users
        $this->db->table('users')->insert($data);
    }
}