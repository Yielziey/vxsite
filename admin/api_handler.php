<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json');

// --- Inquiry Notification Check ---
if (isset($_POST['action']) && $_POST['action'] === 'check_new_inquiries') {
    try {
        $stmt = $pdo->query("SELECT COUNT(id) FROM inquiries WHERE status = 'open' AND id NOT IN (SELECT inquiry_id FROM inquiry_messages WHERE sender = 'admin')");
        $count = $stmt->fetchColumn();
        $stmt2 = $pdo->query("SELECT COUNT(DISTINCT im.inquiry_id) FROM inquiry_messages im JOIN inquiries i ON im.inquiry_id = i.id WHERE i.status = 'open' AND im.id > (SELECT COALESCE(MAX(id), 0) FROM inquiry_messages WHERE inquiry_id = im.inquiry_id AND sender = 'admin')");
        $count += $stmt2->fetchColumn();
        echo json_encode(['success' => true, 'data' => ['count' => $count]]);
    } catch (PDOException $e) {
        error_log("DB Error in check_new_inquiries: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'DB Error']);
    }
    exit;
}


// --- Full Authentication Check for all other actions ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action specified.'];

try {
    switch ($action) {
        // =======================================================
//                VX PODCAST ACTIONS (UPDATED)
// =======================================================
case 'update_podcast_details':
    // Logic from your snippet is correct
    $title = trim($_POST['latest_episode_title'] ?? '');
    $embed = trim($_POST['latest_episode_embed'] ?? '');
    if (empty($title) || empty($embed)) {
        throw new Exception("Title and Embed URL are required.");
    }
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute(['podcast_latest_title', $title, $title]);
    $stmt->execute(['podcast_latest_embed_src', $embed, $embed]);
    $pdo->commit();
    $response = ['success' => true, 'message' => 'VX Podcast details updated successfully!'];
    break;

// --- Podcast Platforms CRUD (NEW) ---
case 'add_podcast_platform':
    $name = trim($_POST['platform_name'] ?? '');
    $icon = trim($_POST['icon_class'] ?? '');
    $url = trim($_POST['url'] ?? '');
    if (empty($name) || empty($icon) || empty($url)) {
        throw new Exception("All fields are required.");
    }
    $sort_order = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM podcast_platforms")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO podcast_platforms (platform_name, icon_class, url, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $icon, $url, $sort_order]);
    $response = ['success' => true, 'message' => 'Podcast platform added.'];
    break;

case 'update_podcast_platform':
    $id = (int)($_POST['platform_id'] ?? 0);
    $name = trim($_POST['platform_name'] ?? '');
    $icon = trim($_POST['icon_class'] ?? '');
    $url = trim($_POST['url'] ?? '');
    if ($id <= 0 || empty($name) || empty($icon) || empty($url)) {
        throw new Exception("Invalid ID or missing fields.");
    }
    $stmt = $pdo->prepare("UPDATE podcast_platforms SET platform_name = ?, icon_class = ?, url = ? WHERE id = ?");
    $stmt->execute([$name, $icon, $url, $id]);
    $response = ['success' => true, 'message' => 'Podcast platform updated.'];
    break;

case 'delete_podcast_platform':
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("Invalid platform ID.");
    }
    $stmt = $pdo->prepare("DELETE FROM podcast_platforms WHERE id = ?");
    $stmt->execute([$id]);
    $response = ['success' => true, 'message' => 'Podcast platform deleted.'];
    break;

case 'update_platform_order':
    $order = json_decode($_POST['order'] ?? '[]');
    if (empty($order)) {
        throw new Exception("No order data received.");
    }
    $pdo->beginTransaction();
    foreach ($order as $index => $id) {
        $stmt = $pdo->prepare("UPDATE podcast_platforms SET sort_order = ? WHERE id = ?");
        $stmt->execute([$index, (int)$id]);
    }
    $pdo->commit();
    $response = ['success' => true, 'message' => 'Platform order saved!'];
    break;


// --- Podcast Topics CRUD (from your snippet, correct) ---
case 'add_podcast_topic':
    $title = trim($_POST['topic_title'] ?? '');
    $guests = trim($_POST['topic_guests'] ?? '');
    $date = trim($_POST['topic_date'] ?? '');
    if (empty($title) || empty($date)) {
        throw new Exception("Topic Title and Date are required.");
    }
    $stmt = $pdo->prepare("INSERT INTO podcast_topics (topic_title, guests, planned_date) VALUES (?, ?, ?)");
    $stmt->execute([$title, $guests, $date]);
    $response = ['success' => true, 'message' => 'Podcast topic added successfully.'];
    break;

case 'update_podcast_topic':
    $id = (int)($_POST['topic_id'] ?? 0);
    $title = trim($_POST['topic_title'] ?? '');
    $guests = trim($_POST['topic_guests'] ?? '');
    $date = trim($_POST['topic_date'] ?? '');
    if ($id <= 0 || empty($title) || empty($date)) {
        throw new Exception("Invalid topic ID or missing fields.");
    }
    $stmt = $pdo->prepare("UPDATE podcast_topics SET topic_title = ?, guests = ?, planned_date = ? WHERE id = ?");
    $stmt->execute([$title, $guests, $date, $id]);
    $response = ['success' => true, 'message' => 'Podcast topic updated successfully.'];
    break;

case 'delete_podcast_topic':
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("Invalid topic ID.");
    }
    $stmt = $pdo->prepare("DELETE FROM podcast_topics WHERE id = ?");
    $stmt->execute([$id]);
    $response = ['success' => true, 'message' => 'Podcast topic deleted successfully.'];
    break;
// =======================================================
//          VEXAURA ACTIONS (UPDATED)
// =======================================================
case 'update_vexaura_main':
    $vexaura_id = trim($_POST['vexaura_id'] ?? '');
    // Get new URLs
    $apple_url = trim($_POST['apple_music_url'] ?? '');
    $youtube_url = trim($_POST['youtube_music_url'] ?? '');
    // INAYOS: Kinuha ang bagong VexAura platform URL
    $vex_platform_url = trim($_POST['vex_platform_url'] ?? '');

    if (empty($vexaura_id)) {
        throw new Exception("Spotify Artist ID is required.");
    }

    // Prepare statement for reuse
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    // Update main VexAura Spotify ID
    $stmt->execute(['vexaura_main_spotify_id', $vexaura_id, $vexaura_id]);

    // Update Apple Music URL
    $stmt->execute(['vexaura_main_apple_music_url', $apple_url, $apple_url]);

    // Update YouTube Music URL
    $stmt->execute(['vexaura_main_youtube_music_url', $youtube_url, $youtube_url]);

    // INAYOS: Isinave ang bagong VexAura platform URL
    $stmt->execute(['vexaura_main_vex_platform_url', $vex_platform_url, $vex_platform_url]);

    $response = ['success' => true, 'message' => 'Main VexAura details updated successfully!'];
    break;

// --- Featured Artist CRUD (UPDATED) ---
case 'add_featured_artist':
    $name = trim($_POST['new_artist_name'] ?? '');
    $spotify_id = trim($_POST['new_spotify_id'] ?? '');
    // Get new URLs
    $apple_url = trim($_POST['apple_music_url'] ?? '');
    $youtube_url = trim($_POST['youtube_music_url'] ?? '');
    
    if (empty($name) || empty($spotify_id)) {
        throw new Exception("Artist Name and Spotify ID are required.");
    }
    
    // Get the next sort_order
    $sort_order = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM featured_artists")->fetchColumn();

    // Updated INSERT statement
    $stmt = $pdo->prepare("INSERT INTO featured_artists (artist_name, spotify_id, apple_music_url, youtube_music_url, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $spotify_id, $apple_url, $youtube_url, $sort_order]);

    $response = ['success' => true, 'message' => 'Featured Artist added successfully.'];
    break;

case 'update_featured_artist':
    $id = (int)($_POST['artist_id'] ?? 0);
    $name = trim($_POST['artist_name'] ?? '');
    $spotify_id = trim($_POST['spotify_id'] ?? '');
    // Get new URLs
    $apple_url = trim($_POST['apple_music_url'] ?? '');
    $youtube_url = trim($_POST['youtube_music_url'] ?? '');
    
    if ($id <= 0 || empty($name) || empty($spotify_id)) {
        throw new Exception("Invalid artist ID or missing required fields.");
    }

    // Updated UPDATE statement
    $stmt = $pdo->prepare("UPDATE featured_artists SET artist_name = ?, spotify_id = ?, apple_music_url = ?, youtube_music_url = ? WHERE id = ?");
    $stmt->execute([$name, $spotify_id, $apple_url, $youtube_url, $id]);

    $response = ['success' => true, 'message' => 'Featured Artist updated successfully.'];
    break;

case 'delete_featured_artist':
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception("Invalid artist ID.");
    }

    $stmt = $pdo->prepare("DELETE FROM featured_artists WHERE id = ?");
    $stmt->execute([$id]);

    $response = ['success' => true, 'message' => 'Featured Artist deleted successfully.'];
    break;
   
        // =======================================================
        //                 EXISTING ACTIONS (News, Xentro, etc.)
        // =======================================================
        
        // --- News Actions ---
        case 'add_news':
        case 'edit_news':
            $title = $_POST['title'];
            $content = $_POST['content'];
            $date = $_POST['date'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $upload_dir = 'uploads/news/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if ($action === 'add_news') {
                $main_image_filename = null;
                // Process Main Image
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $main_image_filename = uniqid() . '-' . basename($_FILES['image']['name']);
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $main_image_filename)) {
                        throw new Exception('Failed to move main uploaded file.');
                    }
                }

                // Insert into news table and get the new ID
                $sql = "INSERT INTO news (title, content, date, featured, image_url) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $content, $date, $featured, $main_image_filename]);
                $news_id = $pdo->lastInsertId();

            } else { // edit_news
                $news_id = $_POST['id'];
                // Process and update Main Image if a new one is uploaded
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    // Delete the old main image
                    $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                    $stmt->execute([$news_id]);
                    $old_image = $stmt->fetchColumn();
                    if ($old_image && file_exists($upload_dir . $old_image)) {
                        unlink($upload_dir . $old_image);
                    }
                    // Upload the new one
                    $main_image_filename = uniqid() . '-' . basename($_FILES['image']['name']);
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $main_image_filename)) {
                        throw new Exception('Failed to move new main uploaded file.');
                    }
                    // Update the database record with the new image
                    $sql = "UPDATE news SET title = ?, content = ?, date = ?, featured = ?, image_url = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$title, $content, $date, $featured, $main_image_filename, $news_id]);
                } else {
                    // Update only the text fields if no new main image
                    $sql = "UPDATE news SET title = ?, content = ?, date = ?, featured = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$title, $content, $date, $featured, $news_id]);
                }
            }

            // --- PROCESS ADDITIONAL IMAGES (for both add and edit) ---
            if (isset($_FILES['additional_images'])) {
                $additional_files = $_FILES['additional_images'];
                $sql_img = "INSERT INTO news_images (news_id, image_filename) VALUES (?, ?)";
                $stmt_img = $pdo->prepare($sql_img);

                foreach ($additional_files['name'] as $key => $name) {
                    if ($additional_files['error'][$key] == 0) {
                        $additional_filename = uniqid() . '-' . basename($name);
                        $target_path = $upload_dir . $additional_filename;
                        if (move_uploaded_file($additional_files['tmp_name'][$key], $target_path)) {
                            $stmt_img->execute([$news_id, $additional_filename]);
                        } else {
                            throw new Exception("Failed to move additional image: {$name}");
                        }
                    }
                }
            }
            
            $message = $action === 'add_news' ? 'News article added successfully.' : 'News article updated successfully.';
            $response = ['success' => true, 'message' => $message];
            break;

        case 'get_news_details':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT id, title, content, DATE_FORMAT(date, '%Y-%m-%dT%H:%i') as date, featured, image_url FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($news) {
                $img_stmt = $pdo->prepare("SELECT image_filename FROM news_images WHERE news_id = ? ORDER BY id ASC");
                $img_stmt->execute([$id]);
                $additional_images = $img_stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $all_images = [];
                if ($news['image_url']) {
                    $all_images[] = $news['image_url'];
                }
                $news['all_images'] = array_merge($all_images, $additional_images);
            }
            $response = ['success' => true, 'data' => ['news' => $news]];
            break;

        case 'delete_news':
            $id = $_POST['id'];
            
            // Delete main image file
            $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $main_image = $stmt->fetchColumn();
            if ($main_image && file_exists('uploads/news/' . $main_image)) {
                unlink('uploads/news/' . $main_image);
            }

            // Delete additional image files
            $img_stmt = $pdo->prepare("SELECT image_filename FROM news_images WHERE news_id = ?");
            $img_stmt->execute([$id]);
            $additional_images = $img_stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($additional_images as $img) {
                if (file_exists('uploads/news/' . $img)) {
                    unlink('uploads/news/' . $img);
                }
            }
            
            // Delete records from DB (cascading delete will handle news_images)
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'News article and all images deleted.'];
            break;

        // --- Xentro Actions ---
        case 'update_xentro_order':
            if (!isset($_POST['order'])) {
                throw new Exception('Error: Order data not provided.');
            }
            $orderData = json_decode($_POST['order']);
            if (!is_array($orderData)) {
                throw new Exception('Error: Invalid order data format.');
            }

            foreach ($orderData as $sort_order => $id) {
                $stmt = $pdo->prepare("UPDATE sentro SET sort_order = ? WHERE id = ?");
                $stmt->execute([$sort_order + 1, $id]);
            }
            $response = ['success' => true, 'message' => 'Member order updated successfully.'];
            break;

        case 'add_xentro':
        case 'edit_xentro':
            if (empty($_POST['name']) || empty($_POST['role'])) {
                throw new Exception('Name and Role are required fields.');
            }
            $name = $_POST['name'];
            $role = $_POST['role'];
            $bio = $_POST['bio'] ?? '';
            $email = $_POST['email'] ?? '';
            $tiktok = $_POST['tiktok'] ?? '';
            $twitch = $_POST['twitch'] ?? '';
            $facebook = $_POST['facebook'] ?? '';
            $kick = $_POST['kick'] ?? '';
            $portfolio_url = $_POST['portfolio_url'] ?? ''; // Kinuha ang portfolio URL
            $image_name = ($action === 'edit_xentro') ? ($_POST['existing_image'] ?? null) : null;
            $target_dir = "../assets/uploads/";

            // Handle Main Profile Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_image_name = 'sentro_' . uniqid() . '.' . $ext;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_image_name)) {
                    if ($action === 'edit_xentro' && !empty($image_name) && file_exists($target_dir . $image_name)) {
                        unlink($target_dir . $image_name);
                    }
                    $image_name = $new_image_name;
                }
            }

            // Perform DB operation
            if ($action === 'add_xentro') {
                $sql = "INSERT INTO sentro (name, role, bio, email, tiktok, twitch, facebook, kick, portfolio_url, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $role, $bio, $email, $tiktok, $twitch, $facebook, $kick, $portfolio_url, $image_name]);
                $member_id = $pdo->lastInsertId();
                $response_message = 'Xentro member added successfully.';
            } else { // edit_xentro
                $member_id = $_POST['id'];
                $sql = "UPDATE sentro SET name=?, role=?, bio=?, email=?, tiktok=?, twitch=?, facebook=?, kick=?, portfolio_url=?, image=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $role, $bio, $email, $tiktok, $twitch, $facebook, $kick, $portfolio_url, $image_name, $member_id]);
                $response_message = 'Xentro member updated successfully.';
            }

            // Handle Portfolio Images Upload
            if (isset($_FILES['portfolio_images']) && is_array($_FILES['portfolio_images']['name'])) {
                $portfolio_files = $_FILES['portfolio_images'];
                $stmt_portfolio = $pdo->prepare("INSERT INTO sentro_images (member_id, image_filename) VALUES (?, ?)");

                foreach ($portfolio_files['name'] as $key => $name) {
                    if ($portfolio_files['error'][$key] == 0) {
                        $filename = 'portfolio_' . uniqid() . '-' . basename($name);
                        if (move_uploaded_file($portfolio_files['tmp_name'][$key], $target_dir . $filename)) {
                            $stmt_portfolio->execute([$member_id, $filename]);
                        }
                    }
                }
            }
            
            $response = ['success' => true, 'message' => $response_message];
            break;
    
        case 'get_xentro_details':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM sentro WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($member) {
                // Fetch portfolio images
                $stmt_portfolio = $pdo->prepare("SELECT image_filename FROM sentro_images WHERE member_id = ? ORDER BY id ASC");
                $stmt_portfolio->execute([$id]);
                $member['portfolio_images'] = $stmt_portfolio->fetchAll(PDO::FETCH_COLUMN);
                
                $response = ['success' => true, 'data' => ['member' => $member]];
            } else {
                throw new Exception('Member not found.');
            }
            break;
    
        case 'delete_xentro':
            $id = $_POST['id'];
            
            // 1. Delete main image file
            $stmt = $pdo->prepare("SELECT image FROM sentro WHERE id = ?");
            $stmt->execute([$id]);
            $image_file = $stmt->fetchColumn();
            if ($image_file && file_exists('../assets/uploads/' . $image_file)) {
                unlink('../assets/uploads/' . $image_file);
            }

            // 2. Delete portfolio image files
            $stmt_portfolio = $pdo->prepare("SELECT image_filename FROM sentro_images WHERE member_id = ?");
            $stmt_portfolio->execute([$id]);
            $portfolio_images = $stmt_portfolio->fetchAll(PDO::FETCH_COLUMN);
            foreach ($portfolio_images as $img) {
                if (file_exists('../assets/uploads/' . $img)) {
                    unlink('../assets/uploads/' . $img);
                }
            }

            // 3. Delete from DB (ON DELETE CASCADE will handle `sentro_images` table)
            $stmt = $pdo->prepare("DELETE FROM sentro WHERE id = ?");
            $stmt->execute([$id]);
            
            $response = ['success' => true, 'message' => 'Xentro member and their portfolio have been deleted.'];
            break;
            
                // --- Teams Actions ---
        case 'add_team':
            $stmt = $pdo->prepare("INSERT INTO teams (team_name, wins, status) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['team_name'], $_POST['wins'], $_POST['status']]);
            $response = ['success' => true, 'message' => 'Team added successfully.'];
            break;
        case 'get_team_details':
            $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'data' => ['team' => $stmt->fetch()]];
            break;
        case 'edit_team':
            $stmt = $pdo->prepare("UPDATE teams SET team_name = ?, wins = ?, status = ? WHERE id = ?");
            $stmt->execute([$_POST['team_name'], $_POST['wins'], $_POST['status'], $_POST['id']]);
            $response = ['success' => true, 'message' => 'Team updated successfully.'];
            break;
        case 'delete_team':
            $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Team deleted successfully.'];
            break;

        // --- Members Actions ---
        case 'get_team_members':
            $stmt = $pdo->prepare("SELECT * FROM members WHERE team_id = ? ORDER BY id");
            $stmt->execute([$_POST['team_id']]);
            $response = ['success' => true, 'data' => ['members' => $stmt->fetchAll()]];
            break;
        case 'add_member':
            $db_path = 'assets/images/default.jpg';
            if (isset($_FILES['member_image']) && $_FILES['member_image']['error'] == 0) {
                $target_dir = "../assets/images/members/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['member_image']['name'], PATHINFO_EXTENSION);
                $filename = 'member_' . uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['member_image']['tmp_name'], $target_dir . $filename)){
                    $db_path = 'assets/images/members/' . $filename;
                }
            }
            $stmt = $pdo->prepare("INSERT INTO members (team_id, member_name, member_role, member_image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['team_id'], $_POST['member_name'], $_POST['member_role'], $db_path]);
            $response = ['success' => true, 'message' => 'Member added.'];
            break;
        case 'edit_member': // BAGONG CASE PARA SA EDIT MEMBER
            $id = $_POST['id'];
            $name = $_POST['member_name'];
            $role = $_POST['member_role'];
            $db_path = $_POST['existing_image'] ?? null;

            if (isset($_FILES['member_image']) && $_FILES['member_image']['error'] == 0) {
                $target_dir = "../assets/images/members/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['member_image']['name'], PATHINFO_EXTENSION);
                $filename = 'member_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['member_image']['tmp_name'], $target_dir . $filename)) {
                    // Delete old image if it's not the default
                    if (!empty($db_path) && $db_path !== 'assets/images/default.jpg' && file_exists('../' . $db_path)) {
                        unlink('../' . $db_path);
                    }
                    $db_path = 'assets/images/members/' . $filename;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE members SET member_name = ?, member_role = ?, member_image = ? WHERE id = ?");
            $stmt->execute([$name, $role, $db_path, $id]);
            $response = ['success' => true, 'message' => 'Member updated successfully.'];
            break;
        case 'delete_member':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT member_image FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $image_to_delete = $stmt->fetchColumn();
            if ($image_to_delete && $image_to_delete !== 'assets/images/default.jpg' && file_exists('../' . $image_to_delete)) {
                unlink('../' . $image_to_delete);
            }
            
            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'Member deleted.'];
            break;

        // --- Content Creators Actions ---
        case 'add_creator':
        case 'edit_creator':
            $name = $_POST['creator_name'];
            $status = $_POST['status'];
            $bio = $_POST['bio'];
            $social_media = json_encode(['facebook' => $_POST['facebook'], 'tiktok' => $_POST['tiktok'], 'twitch' => $_POST['twitch']]);
            
            $db_path = $_POST['existing_picture'] ?? null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $target_dir = "../assets/creators/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $filename = 'creator_' . uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_dir . $filename)){
                    $db_path = 'assets/creators/' . $filename;
                }
            }

            if ($action === 'add_creator') {
                $sql = "INSERT INTO creators (creator_name, social_media, bio, status, profile_picture) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $social_media, $bio, $status, $db_path]);
                $response = ['success' => true, 'message' => 'Creator added.'];
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE creators SET creator_name = ?, social_media = ?, bio = ?, status = ?, profile_picture = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $social_media, $bio, $status, $db_path, $id]);
                $response = ['success' => true, 'message' => 'Creator updated.'];
            }
            break;
        case 'get_creator_details':
            $stmt = $pdo->prepare("SELECT * FROM creators WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $creator = $stmt->fetch();
            $creator['social_media'] = json_decode($creator['social_media'], true);
            $response = ['success' => true, 'data' => ['creator' => $creator]];
            break;
        case 'delete_creator':
            $stmt = $pdo->prepare("DELETE FROM creators WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Creator deleted.'];
            break;
            
        // --- Sponsors Actions ---
        case 'add_sponsor':
        case 'edit_sponsor':
             $name = $_POST['sponsor_name'];
             $website = $_POST['website_url'];
             $contact = $_POST['contact_person'];
             $status = $_POST['status'];
             $db_path = $_POST['existing_logo'] ?? null;
             
            if (isset($_FILES['sponsor_logo']) && $_FILES['sponsor_logo']['error'] == 0) {
                $target_dir = "../assets/sponsors/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['sponsor_logo']['name'], PATHINFO_EXTENSION);
                $filename = 'sponsor_' . uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['sponsor_logo']['tmp_name'], $target_dir . $filename)) {
                    $db_path = 'assets/sponsors/' . $filename;
                }
            }

            if ($action === 'add_sponsor') {
                $stmt = $pdo->prepare("INSERT INTO sponsors (sponsor_name, website_url, contact_person, status, sponsor_logo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $website, $contact, $status, $db_path]);
                $response = ['success' => true, 'message' => 'Sponsor added.'];
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE sponsors SET sponsor_name = ?, website_url = ?, contact_person = ?, status = ?, sponsor_logo = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $website, $contact, $status, $db_path, $id]);
                $response = ['success' => true, 'message' => 'Sponsor updated.'];
            }
            break;
        case 'get_sponsor_details':
            $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'data' => ['sponsor' => $stmt->fetch()]];
            break;
        case 'delete_sponsor':
            $stmt = $pdo->prepare("DELETE FROM sponsors WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Sponsor deleted.'];
            break;
        
         // --- Store Items Actions ---
        case 'add_store_item':
        case 'edit_store_item':
            $name = $_POST['name'];
            $price = $_POST['price'];
            $db_path = $_POST['existing_media'] ?? null;

            if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
                $target_dir = "../assets/store-shop/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $filename = time() . '-' . uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['media']['tmp_name'], $target_dir . $filename)){
                    $db_path = 'assets/store-shop/' . $filename;
                }
            }

            if ($action === 'add_store_item') {
                 $stmt = $pdo->prepare("INSERT INTO store (name, price, media) VALUES (?, ?, ?)");
                 $stmt->execute([$name, $price, $db_path]);
                 $response = ['success' => true, 'message' => 'Store item added.'];
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE store SET name = ?, price = ?, media = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $price, $db_path, $id]);
                $response = ['success' => true, 'message' => 'Store item updated.'];
            }
            break;
        case 'get_store_item_details':
            $stmt = $pdo->prepare("SELECT * FROM store WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'data' => ['item' => $stmt->fetch()]];
            break;
        case 'delete_store_item':
            $stmt = $pdo->prepare("DELETE FROM store WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'message' => 'Store item deleted.'];
            break;

        // --- Streamer Actions ---
        case 'add_streamer':
            $name = $_POST['name'];
            $username = strtolower($_POST['username']);
            $platform = $_POST['platform'];
            
            $stmt = $pdo->prepare("INSERT INTO streamers (name, username, platform) VALUES (?, ?, ?)");
            $stmt->execute([$name, $username, $platform]);
            $response = ['success' => true, 'message' => 'Streamer added successfully.'];
            break;

        case 'delete_streamer':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM streamers WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'Streamer deleted.'];
            break;

                    // --- Store Orders Actions (UPDATED) ---
        case 'archive_all_completed_orders':
            $stmt = $pdo->prepare("UPDATE store_orders SET status = 'Archived' WHERE status = 'Completed'");
            $stmt->execute();
            $count = $stmt->rowCount();
            $response = ['success' => true, 'message' => "$count completed orders have been archived."];
            break;
            
        case 'unarchive_order':
            $id = $_POST['id'];
            // We set it back to 'Completed' as that was its likely last state
            $stmt = $pdo->prepare("UPDATE store_orders SET status = 'Completed' WHERE id = ? AND status = 'Archived'");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'Order has been restored.'];
            break;

        case 'get_order_details':
            // Your existing get_order_details case is fine
            $stmt = $pdo->prepare("SELECT * FROM store_orders WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $response = ['success' => true, 'data' => ['order' => $stmt->fetch()]];
            break;
        case 'update_order_status':
            // Your existing update_order_status case is fine
            $stmt = $pdo->prepare("UPDATE store_orders SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['id']]);
            $response = ['success' => true, 'message' => 'Order status updated.'];
            break;
        case 'delete_order':
            // Your existing delete_order case is fine
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT proof_file FROM store_orders WHERE id = ?");
            $stmt->execute([$id]);
            $proof_file = $stmt->fetchColumn();

            $delete_stmt = $pdo->prepare("DELETE FROM store_orders WHERE id = ?");
            $delete_stmt->execute([$id]);

            if ($proof_file && file_exists('../assets/payment/' . $proof_file)) {
                unlink('../assets/payment/' . $proof_file);
            }
            $response = ['success' => true, 'message' => 'Order has been deleted.'];
            break;


        // --- INQUIRY ACTIONS (ADMIN PANEL) ---
        case 'get_inquiry_messages':
            $id = $_POST['id'];
            $inquiry_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
            $inquiry_stmt->execute([$id]);
            $inquiry_details = $inquiry_stmt->fetch(PDO::FETCH_ASSOC);
            
            $messages_stmt = $pdo->prepare("SELECT * FROM inquiry_messages WHERE inquiry_id = ? ORDER BY created_at ASC");
            $messages_stmt->execute([$id]);
            $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Mark client messages as seen by admin
            $pdo->prepare("UPDATE inquiry_messages SET seen_at = NOW() WHERE inquiry_id = ? AND sender = 'client' AND seen_at IS NULL")->execute([$id]);

            $response = ['success' => true, 'data' => ['messages' => $messages, 'inquiry' => $inquiry_details]];
            break;

        case 'get_new_inquiry_messages':
            $inquiry_id = $_POST['inquiry_id'];
            $last_message_id = $_POST['last_message_id'] ?? 0;

            $messages_stmt = $pdo->prepare("SELECT * FROM inquiry_messages WHERE inquiry_id = ? AND id > ? ORDER BY created_at ASC");
            $messages_stmt->execute([$inquiry_id, $last_message_id]);
            $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Check if the client is currently typing
            $typing_stmt = $pdo->prepare("SELECT (client_last_typed IS NOT NULL AND client_last_typed > NOW() - INTERVAL 3 SECOND) FROM inquiries WHERE id = ?");
            $typing_stmt->execute([$inquiry_id]);
            $is_client_typing = (bool) $typing_stmt->fetchColumn();

            if (!empty($messages)) {
                 $pdo->prepare("UPDATE inquiry_messages SET seen_at = NOW() WHERE inquiry_id = ? AND sender = 'client' AND seen_at IS NULL AND id > ?")->execute([$inquiry_id, $last_message_id]);
            }

            $response = ['success' => true, 'data' => ['messages' => $messages, 'is_client_typing' => $is_client_typing]];
            break;

        case 'set_admin_typing':
            $inquiry_id = $_POST['inquiry_id'] ?? 0;
            if ($inquiry_id > 0) {
                $stmt = $pdo->prepare("UPDATE inquiries SET admin_last_typed = NOW() WHERE id = ?");
                $stmt->execute([$inquiry_id]);
                $response = ['success' => true];
            } else {
                $response = ['success' => false, 'message' => 'Invalid inquiry ID.'];
            }
            break;
            
        case 'send_inquiry_reply':
            $inquiry_id = $_POST['inquiry_id'];
            $message_text = trim($_POST['message'] ?? '');
            $file_message = '';

            // Handle file attachment
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                $target_dir = "../uploads/inquiries/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $original_filename = basename($_FILES['attachment']['name']);
                $safe_filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $original_filename);
                $new_filename = uniqid() . '-' . $safe_filename;
                $db_path = 'uploads/inquiries/' . $new_filename;

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_dir . $new_filename)) {
                    $file_message = "[file:{$original_filename}|{$db_path}]";
                }
            }
            
            $full_message = trim($message_text . "\n" . $file_message);

            if (!empty($full_message)) {
                $pdo->prepare("INSERT INTO inquiry_messages (inquiry_id, sender, message) VALUES (?, 'admin', ?)")->execute([$inquiry_id, $full_message]);
                
                // Re-open the ticket if a reply is sent to a closed one
                $pdo->prepare("UPDATE inquiries SET status = 'open' WHERE id = ? AND status = 'closed'")->execute([$inquiry_id]);

                $response = ['success' => true, 'message' => 'Reply sent successfully.'];
            } else {
                 $response = ['success' => false, 'message' => 'Cannot send an empty message.'];
            }
            break;

        case 'toggle_inquiry_status':
            $id = $_POST['id'];
            $current_status = $_POST['status'];
            // Can only toggle between 'open' and 'closed'
            $new_status = ($current_status === 'open') ? 'closed' : 'open';
            
            $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);

            $response = ['success' => true, 'message' => 'Status updated.', 'data' => ['newStatus' => $new_status]];
            break;

        case 'archive_all_closed':
            $stmt = $pdo->prepare("UPDATE inquiries SET status = 'archived' WHERE status = 'closed'");
            $stmt->execute();
            $count = $stmt->rowCount();
            $message = $count > 0 ? "Successfully archived {$count} closed ticket(s)." : "No closed tickets to archive.";
            $response = ['success' => true, 'message' => $message];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action specified.'];
            break;

    }


} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the error and return a generic message
    error_log("Database Error in api_handler: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'A database error occurred.'];
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Application Error in api_handler: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'An Error Occurred: ' . $e->getMessage()];
}
echo json_encode($response);
?>