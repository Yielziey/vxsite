<?php
// Fix for timezone mismatch between PHP and Database
date_default_timezone_set('UTC'); 
session_start();
require_once __DIR__ . '/../db_connect.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$show_form = false; 
$user_id = null;

if (empty($token) || !ctype_xdigit($token)) {
    $message = 'Invalid or missing password reset token.';
    $message_type = 'danger';
} else {
    $hashed_token = hash('sha256', $token);
    
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > ? LIMIT 1");
    $stmt->execute([$hashed_token, $now]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $show_form = true; 
        $user_id = $user['id']; 
    } else {
        $message = 'This password reset link is invalid or has already expired.';
        $message_type = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password) || empty($password_confirm)) {
        $message = 'Please enter and confirm your new password.';
        $message_type = 'danger';
    } elseif ($password !== $password_confirm) {
        $message = 'The passwords you entered do not match.';
        $message_type = 'danger';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
        $message_type = 'danger';
    } else {
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update_stmt->execute([$new_password_hash, $user_id]);

        $message = 'Your password has been updated successfully! You can now log in.';
        $message_type = 'success';
        $show_form = false; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            <h1 class="text-2xl font-bold text-white text-center tracking-wider">Set New Password</h1>
            <p class="text-center text-gray-400 mb-6 text-sm">Enter your new secure password</p>
            
            <?php if ($message): ?>
                <div class="<?php echo $message_type === 'success' ? 'bg-green-500/20 text-green-300 border-green-500/30' : 'bg-red-500/20 text-red-300 border-red-500/30'; ?> border p-3 rounded-md mb-4 text-center text-sm">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="password" class="sr-only">New Password</label>
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" class="form-input w-full rounded-lg pl-11 pr-4 py-2.5 text-white focus:outline-none transition-all" placeholder="New Password (min. 8 chars)" required>
                    </div>
                </div>
                 <div class="mb-6">
                    <label for="password_confirm" class="sr-only">Confirm New Password</label>
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-input w-full rounded-lg pl-11 pr-4 py-2.5 text-white focus:outline-none transition-all" placeholder="Confirm New Password" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                   Update Password
                </button>
            </form>
            <?php else: ?>
                 <div class="text-center mt-6">
                    <a href="login.php" class="text-sm text-zinc-400 hover:text-red-400 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

