<?php

namespace App\Libraries;

class Fingerspot
{
    protected $apiUrl;
    protected $apiToken;
    protected $cloudId;

    public function __construct()
    {
        $this->apiUrl   = getenv('FINGERSPOT_API_URL');
        $this->apiToken = getenv('FINGERSPOT_API_TOKEN');
        $this->cloudId  = getenv('FINGERSPOT_CLOUD_ID');
    }

    protected function sendRequest($endpoint, $payload = [])
    {
        $client = \Config\Services::curlrequest();
        
        $payload['cloud_id'] = $this->cloudId;
        $payload['trans_id'] = (string)time(); 

        try {
            $response = $client->post($this->apiUrl . $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ],
                'json'   => $payload,
                'verify' => false 
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
        $payload = [
            'data' => [
                'pin'       => (string)$pin,
                'name'      => $name,
                'privilege' => (string)$privilege,
                'password'  => $password,
                'rfid'      => $rfid
            ]
        ];
        return $this->sendRequest('set_userinfo', $payload);
    }

    public function deleteUserInfo($pin)
    {
        return $this->sendRequest('delete_userinfo', ['pin' => (string)$pin]);
    }

    // ---> INI ADALAH FUNGSI YANG HILANG DAN HARUS DITAMBAHKAN <---
    public function getAllPin()
    {
        return $this->sendRequest('get_all_pin');
    }

    public function getAttlog($startDate, $endDate)
    {
        return $this->sendRequest('get_attlog', ['start_date' => $startDate, 'end_date' => $endDate]);
    }

    public function setTime($timezone = "Asia/Jakarta")
    {
        return $this->sendRequest('set_time', [
            'timezone' => $timezone // Contoh: Asia/Jakarta, Asia/Makassar
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