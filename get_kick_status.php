<?php
header('Content-Type: application/json');

// Securely get username from query
if (!isset($_GET['user']) || empty($_GET['user'])) {
    echo json_encode(['error' => 'No username provided']);
    exit;
}

$user = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['user']); // sanitize username
$apiUrl = "https://kick.com/api/v2/channels/" . urlencode($user);

try {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Your-App-Name/1.0');
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200 || !$response) {
        echo json_encode(['error' => "Kick API error for $user"]);
        exit;
    }

    $data = json_decode($response, true);

    // Kick API returns livestream object if live, null otherwise
    $result = [
        'username' => $user,
        'livestream' => $data['livestream'] ?? null,
        'is_live' => isset($data['livestream']) && $data['livestream'] !== null
    ];

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

?>
