<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// --- This part MUST be before db_connect.php and any HTML output ---
$current_page = $_GET['page'] ?? 'dashboard';
if ($current_page === 'settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // TAMA: Aakyatin niya ang isang folder para hanapin ang db_connect.php
    require_once __DIR__ . '/../db_connect.php'; 
    $userId = $_SESSION['user_id'];
    $adminName = $_POST['admin_name'];
    $newPassword = $_POST['new_password'];

    // Update name
    $stmt = $pdo->prepare("UPDATE users SET admin_name = ? WHERE id = ?");
    $stmt->execute([$adminName, $userId]);
    $_SESSION['admin_name'] = $adminName;

    // Update password if provided
    if (!empty($newPassword)) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $userId]);
    }

    // Handle PFP upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "../assets/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $pfp_filename = 'pfp_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_dir . $pfp_filename)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$pfp_filename, $userId]);
            $_SESSION['profile_picture'] = $pfp_filename;
        }
    }
    header('Location: ?page=settings&update=success');
    exit();
}
// --- End of Settings Logic ---

include_once '/../db_connect.php';

// --- Admin Profile ---
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_pfp_filename = $_SESSION['profile_picture'] ?? 'default.jpg';
$admin_profile_picture = '../assets/images/' . htmlspecialchars($admin_pfp_filename);

// Idinagdag ang 'media' sa valid pages
$valid_pages = ['dashboard', 'news', 'xentro', 'division_hub', 'creators', 'store_orders', 'inquiries', 'settings', 'live_now', 'media']; 
if (!in_array($current_page, $valid_pages)) {
    $current_page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VX Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/vx-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #121212; }
        .sidebar { background-color: #181818; }
        .sidebar-item:hover, .sidebar-item.active { background-color: #e11d48; }
        .header { background-color: #1f1f1f; }
        .modal-content { background-color: #282828; }
        .modal-backdrop { background-color: rgba(0,0,0,0.7); }
        .swal2-popup { background: #1f1f1f !important; color: #e4e4e7 !important; }
        .notification-badge {
            position: absolute; top: 5px; right: 10px; min-width: 20px; height: 20px;
            border-radius: 50%; background-color: #dc2626; color: white; font-size: 12px;
            display: flex; align-items: center; justify-content: center; font-weight: bold; display: none;
        }
        /* --- LOGOUT LOADER STYLES (NEW) --- */
        #logout-loader {
            position: fixed; inset: 0; z-index: 9999;
            background-color: rgba(18, 18, 18, 0.9);
            backdrop-filter: blur(5px);
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; gap: 1rem;
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        #logout-loader.visible { opacity: 1; visibility: visible; }
        .loader-spinner {
            border: 4px solid #404040; border-top: 4px solid #ef4444;
            border-radius: 50%; width: 50px; height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-zinc-900 text-zinc-200 font-sans flex min-h-screen">

    <!-- LOGOUT LOADER (NEW) -->
    <div id="logout-loader">
        <div class="loader-spinner"></div>
        <p class="text-white text-lg font-semibold">Logging out...</p>
    </div>

    <aside class="w-64 fixed top-0 left-0 h-full bg-zinc-900 shadow-lg z-10">
        <div class="p-4 text-xl font-bold text-red-500 border-b border-gray-700">VX ADMIN</div>
        <nav class="p-2">
            <a href="?page=dashboard" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt w-6"></i><span>Dashboard</span></a>
            <a href="?page=news" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'news' ? 'active' : ''; ?>"><i class="fas fa-newspaper w-6"></i><span>News</span></a>
            <a href="?page=xentro" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'xentro' ? 'active' : ''; ?>"><i class="fas fa-users-cog w-6"></i><span>Xentro</span></a>
            <a href="?page=division_hub" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'division_hub' ? 'active' : ''; ?>"><i class="fas fa-sitemap w-6"></i><span>Management Hub</span></a>
            <a href="?page=media" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'media' ? 'active' : ''; ?>"><i class="fas fa-compact-disc w-6"></i><span>Media Hub</span></a>
            <a href="?page=live_now" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'live_now' ? 'active' : ''; ?>"><i class="fas fa-broadcast-tower w-6"></i><span>Live Now</span></a>
            <a href="?page=store_orders" class="sidebar-item flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'store_orders' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart w-6"></i><span>Store Orders</span></a>
            <a href="?page=inquiries" id="inquiries-nav" class="sidebar-item relative flex items-center p-3 rounded-lg mb-2 <?php echo $current_page == 'inquiries' ? 'active' : ''; ?>"><i class="fas fa-question-circle w-6"></i><span>Inquiries</span><span id="inquiry-badge" class="notification-badge"></span></a>
            <a href="?page=settings" class="sidebar-item flex items-center p-3 rounded-lg mb-2 mt-auto <?php echo $current_page == 'settings' ? 'active' : ''; ?>"><i class="fas fa-cog w-6"></i><span>Settings</span></a>
            <!-- UPDATED LOGOUT LINK -->
            <a href="logout.php" id="logout-link" class="sidebar-item flex items-center p-3 rounded-lg mb-2 text-red-400 hover:bg-red-800 hover:text-white"><i class="fas fa-sign-out-alt w-6"></i><span>Logout</span></a>
        </nav>
    </aside>

    <div class="ml-64 flex-1 flex flex-col">
        <header class="h-16 bg-zinc-900 shadow-md flex justify-end items-center px-6">
            <div class="flex items-center">
                <div class="text-right">
                    <span class="font-semibold"><?php echo htmlspecialchars($admin_name); ?></span>
                    <div class="h-0.5 bg-red-600 mt-1"></div>
                </div>
                <img src="<?php echo $admin_profile_picture; ?>" alt="Admin PFP" class="w-10 h-10 rounded-full object-cover ml-4">
            </div>
        </header>

        <main id="main-content" class="flex-1 p-6 lg:p-8">
            <?php include $current_page . '_content.php'; ?>
        </main>
    </div>

    <div id="genericModal" class="modal-backdrop fixed inset-0 z-40 items-center justify-center hidden">
        <div id="modalContainer" class="modal-content w-full max-w-2xl p-6 rounded-lg shadow-xl" role="dialog" aria-modal="true">
            <h3 id="modalTitle" class="text-2xl font-bold mb-4">Modal Title</h3>
            <div id="modalBody"></div>
            <div id="modalFooter" class="mt-6 text-right space-x-2"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('genericModal'), modalContainer = document.getElementById('modalContainer'),
              modalTitle = document.getElementById('modalTitle'), modalBody = document.getElementById('modalBody'),
              modalFooter = document.getElementById('modalFooter');
        function openModal(size = '2xl') {
            modalContainer.className = `modal-content w-full max-w-${size} p-6 rounded-lg shadow-xl`;
            modal.classList.remove('hidden'); modal.classList.add('flex');
        }
        function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        
        // --- LOGOUT SCRIPT (NEW) ---
        document.getElementById('logout-link').addEventListener('click', function(event) {
            event.preventDefault(); 
            const loader = document.getElementById('logout-loader');
            const href = this.href;
            loader.classList.add('visible');
            setTimeout(() => { window.location.href = href; }, 1000); 
        });

        // --- Notification System ---
        const inquiryBadge = document.getElementById('inquiry-badge');
        const originalTitle = document.title;
        let notificationSound = new Audio('../assets/sounds/notification.mp3'); 

        async function checkNewInquiries() {
            try {
                const response = await fetch('api_handler.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=check_new_inquiries' });
                const result = await response.json();
                if (result.success && result.data.count > 0) {
                    inquiryBadge.textContent = result.data.count;
                    inquiryBadge.style.display = 'flex';
                    if (document.title === originalTitle) {
                        document.title = `(${result.data.count}) New Inquiry!`;
                        notificationSound.play().catch(e => console.error("Audio play failed:", e));
                    }
                } else {
                    inquiryBadge.style.display = 'none';
                    document.title = originalTitle;
                }
            } catch (error) { console.error('Error checking for new inquiries:', error); }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            checkNewInquiries();
            setInterval(checkNewInquiries, 10000); // Check every 10 seconds
        });
    </script>
</body>
</html>

