<?php
// get_token.php (UPDATED FOR WASMER)

// --- FIXED: Kunin ang credentials mula sa Wasmer Secrets para sa security ---
$clientId = getenv('TWITCH_CLIENT_ID');
$clientSecret = getenv('TWITCH_CLIENT_SECRET');

// Siguraduhin na nakuha ang mga variables mula sa environment
if (!$clientId || !$clientSecret) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Server configuration error: Twitch API credentials are not set.']);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'client_credentials'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// I-forward ang HTTP status code mula sa Twitch para sa mas madaling debugging
http_response_code($http_code);

header('Content-Type: application/json');
echo $response;
?>