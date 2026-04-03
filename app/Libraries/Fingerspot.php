<?php

namespace App\Libraries;

class Fingerspot
{
    protected $apiUrl;
    protected $apiToken;
    protected $cloudId;

    public function __construct()
    {
        // --- MENGAMBIL DATA LANGSUNG DARI DATABASE ---
        $db = \Config\Database::connect();
        
        // Kita panggil tabel fingerspot_api yang baru saja kamu buat di phpMyAdmin
        $fsConfig = $db->table('fingerspot_api')->where('id', 1)->get()->getRowArray();

        // Masukkan data ke variabel library (Jika di DB kosong, dia akan otomatis pakai default)
        $this->apiUrl   = $fsConfig['api_url'] ?? 'https://developer.fingerspot.io/api/';
        $this->apiToken = $fsConfig['api_token'] ?? '';
        $this->cloudId  = $fsConfig['cloud_id'] ?? '';
    }

    protected function sendRequest($endpoint, $payload = [])
    {
        $client = \Config\Services::curlrequest();
        
        // Sesuai Dokumentasi: trans_id dan cloud_id diletakkan di root JSON
        $payload['cloud_id'] = (string)$this->cloudId;
        $payload['trans_id'] = (string)time(); 

        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ],
                'json'        => $payload,
                'verify'      => false,
                'http_errors' => false 
            ]);

            return json_decode($response->getBody(), true);
            
        } catch (\Exception $e) {
            log_message('error', '[Fingerspot API Error] ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // --- FUNGSI-FUNGSI API ---

    public function getUserInfo($pin)
    {
        return $this->sendRequest('get_userinfo', ['pin' => (string)$pin]);
    }

    public function setUserInfo($pin, $name, $privilege = "1", $password = "", $rfid = "")
    {
        // 1. Potong nama maksimal 24 karakter agar memori mesin tidak error
        $safeName = substr(trim($name), 0, 24);

        // 2. Terjemahkan Privilege: 0/14 (Web) menjadi 1/2 (Mesin)
        $mappedPrivilege = "1"; 
        if ($privilege == "14" || $privilege == "2") {
            $mappedPrivilege = "2"; // Admin
        } elseif ($privilege == "0" || $privilege == "1") {
            $mappedPrivilege = "1"; // User
        }

        // 3. SUSUNAN JSON WAJIB (Semua harus di-cast ke (string) sesuai format API)
        $payload = [
            'data' => [
                'pin'       => (string)$pin,
                'name'      => (string)$safeName,
                'privilege' => (string)$mappedPrivilege,
                'password'  => (string)$password,
                'rfid'      => (string)$rfid,
                'template'  => "" // <--- INI DIA BIANG KEROKNYA! WAJIB ADA MESKIPUN KOSONG
            ]
        ];
        
        return $this->sendRequest('set_userinfo', $payload);
    }

    public function deleteUserInfo($pin)
    {
        return $this->sendRequest('delete_userinfo', ['pin' => (string)$pin]);
    }

    public function getAllPin()
    {
        return $this->sendRequest('get_all_pin');
    }

    public function getAttlog($startDate, $endDate)
    {
        return $this->sendRequest('get_attlog', [
            'start_date' => (string)$startDate, 
            'end_date'   => (string)$endDate
        ]);
    }

    public function setTime($timezone = "Asia/Jakarta")
    {
        return $this->sendRequest('set_time', [
            'timezone' => (string)$timezone
        ]);
    }

    public function registerOnline($pin, $verificationType = "0")
    {
        return $this->sendRequest('reg_online', [
            'pin'          => (string)$pin,
            'verification' => (string)$verificationType
        ]);
    }

    public function getDeviceInfo()
    {
        return $this->sendRequest('get_device');
    }

    public function restartDevice()
    {
        return $this->sendRequest('restart_device');
    }
}