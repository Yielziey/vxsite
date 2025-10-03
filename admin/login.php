<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $response = ['success' => false, 'message' => 'Incorrect username or password.'];

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, admin_name, password_hash, profile_picture FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['admin_name'];
                $_SESSION['profile_picture'] = $user['profile_picture']; 
                $response = ['success' => true, 'admin_name' => $user['admin_name']];
            }
        } catch (PDOException $e) {
            $response['message'] = 'A database error occurred.';
        }
    } else {
        $response['message'] = 'Please fill in all fields.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/images/vx-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #18181b; }
        .login-card { background-color: #27272a; }
        .form-input { background-color: #3f3f46; border-color: #52525b; }
        .form-input:focus { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5); }
        .welcome-overlay { opacity: 0; visibility: hidden; transition: opacity 0.5s, visibility 0.5s; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(5px); }
        .welcome-overlay.visible { opacity: 1; visibility: visible; }
        .welcome-box { transform: scale(0.7); transition: transform 0.5s; }
        .welcome-overlay.visible .welcome-box { transform: scale(1); }
    </style>
</head>
<body class="flex items-center justify-center h-screen px-4">

    <!-- Login Form -->
    <div id="login-container" class="w-full max-w-sm">
        <div class="login-card p-8 rounded-2xl shadow-2xl border border-zinc-700">
            <div class="flex justify-center mb-4">
                 <!-- UPDATED IMAGE -->
                 <img src="../assets/images/vx-logo.jpg" alt="Vexillum Logo" class="w-24 h-24 rounded-full object-cover border-2 border-zinc-700">
            </div>
            <h1 class="text-2xl font-bold text-white text-center tracking-wider">VX ADMIN</h1>
            <p class="text-center text-gray-400 mb-6 text-sm">Welcome back</p>
            
            <div id="error-message" class="bg-red-500/20 border border-red-500/30 text-red-300 p-3 rounded-md mb-4 text-center text-sm hidden"></div>

            <form id="login-form">
                <div class="mb-4">
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-input w-full rounded-lg pl-11 pr-4 py-2.5 text-white focus:outline-none transition-all" placeholder="Username" required>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-input w-full rounded-lg pl-11 pr-4 py-2.5 text-white focus:outline-none transition-all" placeholder="Password" required>
                    </div>
                </div>
                <div class="text-right mb-6">
                    <a href="forgot_password.php" class="text-xs text-zinc-400 hover:text-red-400 transition-colors">Forgot Password?</a>
                </div>
                <button type="submit" id="login-button" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <span id="login-text">Login</span>
                    <i id="login-spinner" class="fas fa-spinner fa-spin hidden ml-2"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Welcome Overlay -->
    <div id="welcome-overlay" class="welcome-overlay fixed inset-0 flex items-center justify-center z-50">
        <div class="welcome-box bg-zinc-800 p-10 rounded-lg shadow-xl text-center border border-zinc-700">
            <div class="w-20 h-20 mx-auto bg-green-500/20 rounded-full flex items-center justify-center border-2 border-green-500 mb-4">
                 <i class="fas fa-check text-4xl text-green-400"></i>
            </div>
            <h2 class="text-3xl font-bold text-white">Welcome!</h2>
            <p id="welcome-name" class="text-zinc-300 text-lg mt-1"></p>
        </div>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        const loginButton = document.getElementById('login-button'), loginText = document.getElementById('login-text'),
              loginSpinner = document.getElementById('login-spinner'), errorMessageDiv = document.getElementById('error-message');
        loginText.textContent = 'Logging In...';
        loginSpinner.classList.remove('hidden');
        loginButton.disabled = true;
        errorMessageDiv.classList.add('hidden');
        try {
            const formData = new FormData(this);
            const response = await fetch('login.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                document.getElementById('welcome-name').textContent = result.admin_name;
                document.getElementById('welcome-overlay').classList.add('visible');
                setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
            } else {
                errorMessageDiv.textContent = result.message;
                errorMessageDiv.classList.remove('hidden');
                loginText.textContent = 'Login';
                loginSpinner.classList.add('hidden');
                loginButton.disabled = false;
            }
        } catch (error) {
            errorMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
            errorMessageDiv.classList.remove('hidden');
            loginText.textContent = 'Login';
            loginSpinner.classList.add('hidden');
            loginButton.disabled = false;
        }
    });
    </script>
</body>
</html>

