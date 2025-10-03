<?php
// check_live.php
session_start();
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json');

// Ang iyong tamang Twitch API credentials
$clientId = 'x1vajyu6nhake660l6zn8o3hnsytc8';
$clientSecret = 'fmemhv7las1txf4zpwintlye3snnmv';

// Safety check para sa mga hindi pa napapalitan na placeholders
if ($clientId === 'PALITAN_ITO_NG_IYONG_CLIENT_ID' || $clientSecret === 'PALITAN_ITO_NG_IYONG_CLIENT_SECRET') {
    echo json_encode(['error' => 'Twitch API credentials are not set correctly in check_live.php. Please replace the placeholder values.']);
    exit;
}

// Function to get an App Access Token from Twitch
function getTwitchAccessToken($clientId, $clientSecret) {
    $url = 'https://id.twitch.tv/oauth2/token';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("Twitch Token Error: HTTP {$http_code} - " . $curl_error . " - Response: " . $response);
        return null;
    }
    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

$accessToken = getTwitchAccessToken($clientId, $clientSecret);

if (!$accessToken) {
    echo json_encode(['error' => 'Failed to get Twitch Access Token. Check your credentials and server connectivity.']);
    exit;
}

// Get all twitch streamers from the database
$stmt = $pdo->prepare("SELECT username FROM streamers WHERE platform = 'twitch'");
$stmt->execute();
$streamers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($streamers)) {
    echo json_encode([]); // Return empty JSON if no streamers
    exit;
}

$streamers = array_map('strtolower', $streamers);
$statuses = [];
foreach ($streamers as $streamer) {
    $statuses[trim($streamer)] = ['status' => 'offline'];
}

// Check statuses in batches of 100 (Twitch API limit)
$chunks = array_chunk($streamers, 100);

foreach ($chunks as $chunk) {
    if (empty($chunk)) continue;
    $query_params = '?' . http_build_query(['user_login' => $chunk], '', '&');
    $url = 'https://api.twitch.tv/helix/streams' . $query_params;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Client-ID: ' . $clientId,
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $liveData = json_decode($response, true);
        if (isset($liveData['data'])) {
            foreach ($liveData['data'] as $stream) {
                $username_lower = strtolower($stream['user_login']);
                if (isset($statuses[$username_lower])) {
                    $statuses[$username_lower]['status'] = 'live';
                }
            }
        }
    } else {
        error_log("Twitch Streams API Error: HTTP {$http_code} - Response: " . $response);
    }
}

echo json_encode($statuses);
?>

