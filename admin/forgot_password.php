<?php
// forgot_password.php (UPDATED FOR WASMER)

// Fix for timezone mismatch
date_default_timezone_set('UTC'); 
session_start();
require_once __DIR__ . '/../db_connect.php';

// PHPMailer Imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/src/Exception.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';

$message = '';
$message_type = ''; // 'success' or 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } else {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $hashed_token = hash('sha256', $token); 
            $expires = date('Y-m-d H:i:s', time() + 3600); 

            $update_stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $update_stmt->execute([$hashed_token, $expires, $user['id']]);
            
            // --- FIXED #1: Dynamic Reset URL ---
            // Awtomatikong kinukuha ang tamang domain (hal. 'vxsite.wasmer.app')
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $reset_url = "{$protocol}://{$host}/admin/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;

                // --- FIXED #2: Using Wasmer Secrets for Security ---
                $mail->Username   = getenv('SMTP_USER');
                $mail->Password   = getenv('SMTP_PASS'); 

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('vxbot.auto@gmail.com', 'Vexillum Panel');
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request for Vexillum Panel';
                $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>You requested a password reset. Click the link below to set a new password. This link is valid for 1 hour.</p>
                    <p><a href='$reset_url'>Click here to reset your password</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Log the error instead of showing it to the user
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
        } 
        
        // This message is shown whether the user exists or not for security reasons.
        $message = 'If an account with that email exists, a password reset link has been sent.';
        $message_type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" type="image/png" href="assets/images/vx-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #18181b; /* zinc-900 */
        }
        .login-card {
            background-color: #27272a; /* zinc-800 */
        }
        .form-input {
            background-color: #3f3f46; /* zinc-700 */
            border-color: #52525b; /* zinc-600 */
        }
        .form-input:focus {
            border-color: #ef4444; /* red-500 */
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen px-4">
    <div class="w-full max-w-sm">
        <div class="login-card p-8 rounded-2xl shadow-2xl border border-zinc-700">
            <div class="flex justify-center mb-4">
                 <img src="../assets/images/WHITE_DIAMOND.jpg" alt="Vexillum Logo" class="w-24 h-auto">
            </div>
            <h1 class="text-2xl font-bold text-white text-center tracking-wider">Forgot Password</h1>
            <p class="text-center text-gray-400 mb-6 text-sm">Enter email for recovery link</p>
            
            <?php if ($message): ?>
                <div class="<?php echo $message_type === 'success' ? 'bg-green-500/20 text-green-300 border-green-500/30' : 'bg-red-500/20 text-red-300 border-red-500/30'; ?> border p-3 rounded-md mb-4 text-center text-sm">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-6">
                    <label for="email" class="sr-only">Email</label>
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" class="form-input w-full rounded-lg pl-11 pr-4 py-2.5 text-white focus:outline-none transition-all" placeholder="Email Address" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                   Send Recovery Link
                </button>
                 <div class="text-center mt-6">
                    <a href="login.php" class="text-sm text-zinc-400 hover:text-red-400 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

