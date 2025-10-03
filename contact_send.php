<?php
// contact_send.php (UPDATED FOR WASMER)

// Fix for timezone mismatch
date_default_timezone_set('UTC'); 
session_start();
require_once __DIR__ . '/db_connect.php';

// PHPMailer Imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/includes/PHPMailer/src/Exception.php';
require __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/src/SMTP.php';

function generateTicketNumber($topic) {
    $prefixes = [
        'General Inquiry'            => 'GE',
        'Sponsorship'                => 'SS',
        'Partner'                    => 'PN',
        'Apply as Content Creator'   => 'ACC'
    ];
    $prefix = $prefixes[$topic] ?? 'VX';
    $randomPart = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    return $prefix . $randomPart;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname   = trim($_POST['fname'] ?? '');
    $lname   = trim($_POST['lname'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $topic   = trim($_POST['topic'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if(!$fname || !$lname || !$email || !$message || !$topic){
        exit('Invalid input. Please fill all required fields.');
    }
    
    $ticket_number = generateTicketNumber($topic);

    try {
        // Insert inquiry
        $stmt = $pdo->prepare("INSERT INTO inquiries (ticket_number, fname, lname, email, topic, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())");
        $stmt->execute([$ticket_number, $fname, $lname, $email, $topic, $message]);
        $inquiry_id = $pdo->lastInsertId();

        // Insert first message
        $stmt2 = $pdo->prepare("INSERT INTO inquiry_messages (inquiry_id, sender, message, created_at) VALUES (?, 'client', ?, NOW())");
        $stmt2->execute([$inquiry_id, $message]);

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // --- FIXED: Using Wasmer Secrets for security ---
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS'); 

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('vxbot.auto@gmail.com', 'VX Team');
            $mail->addAddress($email, "$fname $lname");
            $mail->isHTML(true);
            $mail->Subject = "We received your VX inquiry (Ticket: $ticket_number)";

            // --- FIXED: Dynamic URL for the chat link ---
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $chat_url = "{$protocol}://{$host}/client/index.php?ticket=$ticket_number";

            $mail->Body = "
                <p>Hello <b>$fname</b>,</p>
                <p>We received your inquiry about: <b>$topic</b></p>
                <p>Your ticket number is: <b>$ticket_number</b></p>
                <p>You can continue chatting with us anytime using this link:</p>
                <p><a href='$chat_url' 
                      style='background:#900;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none;'>
                      Continue Chat
                </a></p>
                <p>– VX Team</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        // Redirect to client chat
        header("Location: client/index.php?ticket=" . urlencode($ticket_number));
        exit;

    } catch (Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        die("A database error occurred. We have been notified and will fix it shortly.");
    }
} else {
    header("Location: contact.php");
    exit;
}
?>