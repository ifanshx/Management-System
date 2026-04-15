<?php
$url = 'https://developer.fingerspot.io/api/get_attlog';
$data = '{"trans_id":"1", "cloud_id":"FZ1096818", "start_date":"2026-02-05", "end_date":"2026-02-05"}';
$authorization = "Authorization: Bearer BATIXAM74RGEG2XS";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
curl_close($ch);
print_r ($result);
?>