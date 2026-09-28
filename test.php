<?php
echo "check server ";exit;
$url = "https://www.google.com";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo "CURL Error: " . curl_error($ch);
} else {
    echo "CURL working successfully.<br>";
    echo "Response length: " . strlen($response);
}

curl_close($ch);

?>