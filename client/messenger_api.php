<?php
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? null;
if(!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified.']);
    exit;
}

$ticket = $_POST['ticket'] ?? $_GET['ticket'] ?? '';
if(!$ticket) {
    echo json_encode(['success' => false, 'message' => 'Ticket number is required.']);
    exit;
}

// Fetch inquiry details once for all actions
$stmt = $pdo->prepare("SELECT id, status FROM inquiries WHERE ticket_number = ?");
$stmt->execute([$ticket]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

// If ticket is not found (might be archived and user tries to access it)
if(!$inquiry) {
    // Check if it exists in an archived state. If so, return a specific status.
    // This part is conceptual; assuming you might want to handle direct access to archived tickets.
    // For now, we'll just treat it as not found.
    echo json_encode(['success' => true, 'data' => ['messages' => [], 'is_admin_typing' => false, 'status' => 'archived']]);
    exit;
}

$inquiry_id = $inquiry['id'];
$current_status = $inquiry['status'];

try {
    switch($action) {
        case 'set_typing':
            if ($current_status !== 'open') {
                echo json_encode(['success' => false, 'message' => 'Cannot type in a non-active ticket.']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE inquiries SET client_last_typed = NOW() WHERE id = ?");
            $stmt->execute([$inquiry_id]);
            echo json_encode(['success' => true]);
            break;

        case 'load':
            $last_id = $_POST['last_id'] ?? 0;
            $messages = [];
            $is_admin_typing = false;

            // Only load messages if the ticket is not archived
            if ($current_status !== 'archived') {
                $stmt = $pdo->prepare("SELECT * FROM inquiry_messages WHERE inquiry_id = ? AND id > ? ORDER BY created_at ASC");
                $stmt->execute([$inquiry_id, $last_id]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $typing_stmt = $pdo->prepare("SELECT (admin_last_typed IS NOT NULL AND admin_last_typed > NOW() - INTERVAL 3 SECOND) FROM inquiries WHERE id = ?");
                $typing_stmt->execute([$inquiry_id]);
                $is_admin_typing = (bool) $typing_stmt->fetchColumn();
                
                if(!empty($messages)) {
                    $pdo->prepare("UPDATE inquiry_messages SET seen_at = NOW() WHERE inquiry_id = ? AND sender = 'admin' AND seen_at IS NULL")->execute([$inquiry_id]);
                }
            }

            // Always return the current status of the ticket
            echo json_encode(['success' => true, 'data' => ['messages' => $messages, 'is_admin_typing' => $is_admin_typing, 'status' => $current_status]]);
            break;

        case 'send':
            if($current_status !== 'open') {
                echo json_encode(['success' => false, 'message' => 'This ticket is not open.']);
                exit;
            }
            $msg = trim($_POST['msg'] ?? '');
            if($msg === '') {
                echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO inquiry_messages (inquiry_id, sender, message) VALUES (?, 'client', ?)");
            $stmt->execute([$inquiry_id, $msg]);
            echo json_encode(['success' => true]);
            break;
            
        case 'upload':
            if($current_status !== 'open') {
                echo json_encode(['success' => false, 'message' => 'This ticket is not open.']);
                exit;
            }
            if(!isset($_FILES['file'])) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
                exit;
            }

            $file = $_FILES['file'];
            if($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
                exit;
            }
            
            $uploadsDir = realpath(__DIR__ . '/../uploads');
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

            $orig_name = preg_replace('/[^A-Za-z0-9_\-\. ]/', '_', basename($file['name']));
            $unique_name = time() . '_' . bin2hex(random_bytes(6)) . '_' . $orig_name;
            $target_path = $uploadsDir . '/' . $unique_name;
            
            if(!move_uploaded_file($file['tmp_name'], $target_path)) {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
                exit;
            }

            $urlPath = 'uploads/' . $unique_name;
            $message = "[file:" . $orig_name . '|' . $urlPath . "]";

            $stmt = $pdo->prepare("INSERT INTO inquiry_messages (inquiry_id, sender, message) VALUES (?, 'client', ?)");
            $stmt->execute([$inquiry_id, $message]);
            
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    // Log error properly in a real application
    error_log('Messenger API Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>

