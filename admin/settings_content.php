<?php
// Note: db_connect.php is already included by dashboard.php
$update_message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_profile') {
            $admin_name = $_POST['admin_name'];
            $email = $_POST['email'];

            $sql = "UPDATE users SET admin_name = ?, email = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$admin_name, $email, $user_id]);

            $_SESSION['admin_name'] = $admin_name; // Update session
            $update_message = 'Profile details updated successfully!';
            $message_type = 'success';
        }
        elseif ($action === 'update_password') {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                 if (strlen($new_password) >= 8) {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$password_hash, $user_id]);
                    $update_message = 'Password changed successfully!';
                    $message_type = 'success';
                 } else {
                    $update_message = 'Password must be at least 8 characters long.';
                    $message_type = 'error';
                 }
            } else {
                $update_message = 'Passwords do not match.';
                $message_type = 'error';
            }
        }
        elseif ($action === 'update_pfp') {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $target_dir = "../assets/images/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $new_filename = 'pfp_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_dir . $new_filename)) {
                    // Delete old picture if it's not the default one
                    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $old_pic = $stmt->fetchColumn();
                    if ($old_pic && $old_pic !== 'default.jpg' && file_exists($target_dir . $old_pic)) {
                        unlink($target_dir . $old_pic);
                    }
                    
                    // Update database with new filename
                    $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$new_filename, $user_id]);

                    $_SESSION['profile_picture'] = $new_filename; // Update session

                    // Redirect to show changes immediately and prevent form resubmission
                    header('Location: dashboard.php?page=settings&status=pfp_success');
                    exit();
                } else {
                    $update_message = 'Failed to upload new profile picture.';
                    $message_type = 'error';
                }
            } else {
                $update_message = 'Please select a file to upload.';
                $message_type = 'error';
            }
        }
    } catch(PDOException $e) {
        $update_message = "Database error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Check for status from redirect
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'pfp_success') {
        $update_message = 'Profile picture updated successfully!';
        $message_type = 'success';
    }
}


$stmt = $pdo->prepare("SELECT admin_name, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$current_pfp = '../assets/images/' . ($user['profile_picture'] ?? 'default.jpg');
?>

<div class="content-area p-0">
    <div class="mb-4 border-b-2 border-red-600 pb-2">
        <h2 class="text-3xl font-bold text-white">Admin Settings</h2>
    </div>

    <?php if ($update_message): ?>
    <div class="p-4 rounded-lg mb-6 <?php echo $message_type === 'success' ? 'bg-green-800 text-green-200' : 'bg-red-800 text-red-200'; ?>">
        <?php echo $update_message; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Picture -->
        <div class="lg:col-span-1 bg-zinc-800 p-6 rounded-lg">
            <h3 class="text-xl font-semibold mb-4">Profile Picture</h3>
            <img src="<?php echo htmlspecialchars($current_pfp); ?>" alt="Current Profile Picture" class="w-40 h-40 rounded-full mx-auto object-cover mb-4">
            <form action="dashboard.php?page=settings" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_pfp">
                <input type="file" name="profile_picture" class="w-full bg-zinc-700 p-2 rounded mb-4 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" required>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Upload New Picture</button>
            </form>
        </div>

        <!-- Profile Details & Password -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-zinc-800 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Profile Details</h3>
                <form action="dashboard.php?page=settings" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-4">
                        <label for="admin_name" class="block mb-1">Admin Name</label>
                        <input type="text" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($user['admin_name']); ?>" class="w-full bg-zinc-700 p-2 rounded">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full bg-zinc-700 p-2 rounded">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Details</button>
                </form>
            </div>

            <div class="bg-zinc-800 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Change Password</h3>
                <form action="dashboard.php?page=settings" method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <div class="mb-4">
                        <label for="new_password" class="block mb-1">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="w-full bg-zinc-700 p-2 rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="block mb-1">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full bg-zinc-700 p-2 rounded" required>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

