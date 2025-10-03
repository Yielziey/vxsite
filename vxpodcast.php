<?php 
require_once 'includes/header.php';

// Tiyakin na mayroon tayong DB connection
$pdo = null;
if (file_exists('db_connect.php')) {
    require_once 'db_connect.php';
}

// --- DB READ LOGIC ---
function getSetting($pdo, $key, $default = '') {
    if (!$pdo) return $default;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    } catch (PDOException $e) {
        error_log("DB Read Error for setting {$key}: " . $e->getMessage());
        return $default;
    }
}

// 1. Latest Episode Details
$latest_title = getSetting($pdo, 'podcast_latest_title', 'The Conversation Hub of Vexillum');
$latest_embed_src = getSetting($pdo, 'podcast_latest_embed_src', 'https://open.spotify.com/embed/episode/2s2L5tB2sX4gC8g5pFcG2H?utm_source=generator');

// 2. Podcast Platforms
$platforms = [];
if ($pdo) {
    try {
        $platforms_stmt = $pdo->query("SELECT platform_name, icon_class, url FROM podcast_platforms ORDER BY sort_order");
        $platforms = $platforms_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching podcast platforms: " . $e->getMessage());
    }
}
// Fallback kung walang makuha sa DB
if (empty($platforms)) {
    $platforms = [
        ['platform_name' => 'Spotify', 'icon_class' => 'fab fa-spotify', 'url' => '#'],
        ['platform_name' => 'Apple Podcasts', 'icon_class' => 'fab fa-apple', 'url' => '#'],
        ['platform_name' => 'Google Podcasts', 'icon_class' => 'fab fa-google', 'url' => '#'],
        ['platform_name' => 'YouTube', 'icon_class' => 'fab fa-youtube', 'url' => '#']
    ];
}


// 3. Upcoming Topics
$upcoming_topics = [];
if ($pdo) {
    try {
        $topics_stmt = $pdo->query("SELECT topic_title, guests, planned_date FROM podcast_topics WHERE planned_date >= CURDATE() ORDER BY planned_date ASC LIMIT 5");
        $upcoming_topics = $topics_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching upcoming podcast topics: " . $e->getMessage());
    }
}
// --- END DB READ LOGIC ---

?>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Titillium+Web:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<style>
:root {
    --podcast-bg: #0d0d0d;
    --podcast-surface: #1a1a1a;
    --podcast-violet: #a94dff;
    --podcast-violet-glow: rgba(169, 77, 255, 0.6);
    --text-light: #f5f5f5;
    --text-muted: #8e8e8e;
}

.vxpodcast-page {
    background-color: var(--podcast-bg);
    color: var(--text-light);
    font-family: 'Titillium Web', sans-serif;
    padding-top: 80px;
}

/* Hero Section */
.podcast-hero {
    text-align: center;
    padding: 6rem 1rem;
    background: radial-gradient(circle at 50% 0%, rgba(169, 77, 255, 0.15), transparent 40%), var(--podcast-bg);
    position: relative;
    overflow: hidden;
}

.podcast-hero h1 {
    font-family: 'Orbitron', sans-serif;
    font-size: clamp(3rem, 10vw, 5.5rem);
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 
        0 0 5px var(--podcast-violet),
        0 0 10px var(--podcast-violet),
        0 0 20px var(--podcast-violet),
        0 0 40px var(--podcast-violet-glow);
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.podcast-hero h1::after {
    content: 'VXPodcast';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: -1;
    font-size: clamp(4rem, 14vw, 10rem);
    font-weight: 700;
    color: rgba(255, 255, 255, 0.04);
    text-shadow: none;
    pointer-events: none;
}

.podcast-hero p.subtitle {
    font-size: 1.25rem;
    color: var(--text-muted);
    max-width: 700px;
    margin: 0 auto 2.5rem auto;
}

/* Listen On Section */
.listen-on-title {
    color: var(--text-muted);
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
    margin-bottom: 1.5rem;
}

.platform-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.platform-btn {
    display: inline-flex;
    align-items: center;
    background-color: var(--podcast-surface);
    color: var(--text-light);
    border: 1px solid #333;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.platform-btn:hover {
    background-color: var(--podcast-violet);
    color: #fff;
    border-color: var(--podcast-violet);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.platform-btn i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
}

/* Section Styling */
.podcast-section {
    padding: 4rem 0;
    border-bottom: 1px solid #222;
}
.podcast-section:last-child {
    border-bottom: none;
}
.section-title {
    font-family: 'Orbitron', sans-serif;
    font-weight: 700;
    color: #fff;
    text-align: center;
    margin-bottom: 3rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ======================================= */
/* LATEST EPISODE - REDESIGNED        */
/* ======================================= */
.latest-episode-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: center;
    background: linear-gradient(to right, #1a1a1a, #111);
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #222;
}

@media (min-width: 992px) {
    .latest-episode-layout {
        grid-template-columns: 45% 55%;
    }
}

.latest-episode-player iframe {
    border-radius: 12px;
    width: 100%;
    height: 232px; /* Standard Spotify player height */
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.spotify-player-glow {
    box-shadow: 0 0 25px 5px var(--podcast-violet-glow);
}

.latest-episode-info .episode-tag {
    display: inline-block;
    background-color: var(--podcast-violet);
    color: #fff;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
}

.latest-episode-info h3 {
    font-family: 'Titillium Web', sans-serif;
    font-weight: 700;
    font-size: clamp(1.5rem, 4vw, 2rem);
    color: #fff;
    margin-bottom: 1rem;
}

.latest-episode-info p {
    color: var(--text-muted);
    line-height: 1.8;
    margin-bottom: 2rem;
}

.latest-episode-info .listen-now-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: transparent;
    border: 2px solid var(--podcast-violet);
    color: var(--podcast-violet);
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.latest-episode-info .listen-now-btn:hover {
    background-color: var(--podcast-violet);
    color: #fff;
    transform: scale(1.05);
}
/* ======================================= */
/* END LATEST EPISODE              */
/* ======================================= */

/* Upcoming Topics List */
.topic-list-item {
    background-color: var(--podcast-surface);
    border: 1px solid #222;
    border-left: 4px solid var(--podcast-violet);
    padding: 1.25rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: background-color 0.3s ease;
}
.topic-list-item:hover {
    background-color: #252525;
}
.topic-list-item .topic-date {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.topic-list-item .topic-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-light);
    margin-bottom: 0.5rem;
}
.topic-list-item .topic-guests {
    font-size: 0.9rem;
    color: var(--podcast-violet);
}
</style>

<main class="vxpodcast-page">

    <!-- Hero Section -->
    <section class="podcast-hero">
        <div class="container">
            <h1>VXPodcast</h1>
            <p class="subtitle">The official conversation hub of Vexillum. Hear from our core members, creators, and special guests from across the gaming and creative industries.</p>
            
            <div class="listen-on-platforms">
                <h2 class="listen-on-title">Listen & Subscribe On</h2>
                <div class="platform-buttons">
                    <?php foreach ($platforms as $platform): ?>
                    <a href="<?php echo htmlspecialchars($platform['url']); ?>" target="_blank" class="platform-btn">
                        <i class="<?php echo htmlspecialchars($platform['icon_class']); ?>"></i>
                        <span><?php echo htmlspecialchars($platform['platform_name']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Episode Section -->
    <section class="podcast-section">
        <div class="container">
            <h2 class="section-title">Latest Episode</h2>
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="latest-episode-layout">
                        <div class="latest-episode-player">
                            <?php
                                $is_spotify = strpos($latest_embed_src, 'spotify') !== false;
                                $player_class = $is_spotify ? 'spotify-player-glow' : '';
                            ?>
                            <div class="<?php echo $player_class; ?>">
                                <iframe src="<?php echo htmlspecialchars($latest_embed_src); ?>" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                            </div>
                        </div>
                        <div class="latest-episode-info">
                            <span class="episode-tag">New Episode</span>
                            <h3><?php echo htmlspecialchars($latest_title); ?></h3>
                            <p>In this episode, we sit down with industry experts to discuss the rising trends, challenges, and opportunities within the Philippine esports scene. A must-watch for aspiring pros and enthusiasts.</p>
                            <a href="<?php echo htmlspecialchars($latest_embed_src); ?>" target="_blank" class="listen-now-btn">
                                <i class="fas fa-play"></i>
                                Listen Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Topics Section -->
    <?php if (!empty($upcoming_topics)): ?>
    <section class="podcast-section">
        <div class="container">
            <h2 class="section-title">Coming Up Next</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php foreach ($upcoming_topics as $topic): ?>
                    <div class="topic-list-item">
                        <div class="topic-date"><i class="fas fa-calendar-alt fa-fw me-2"></i><?php echo date('F d, Y', strtotime($topic['planned_date'])); ?></div>
                        <div class="topic-title"><?php echo htmlspecialchars($topic['topic_title']); ?></div>
                        <?php if (!empty($topic['guests'])): ?>
                        <div class="topic-guests"><i class="fas fa-users fa-fw me-2"></i>With: <?php echo htmlspecialchars($topic['guests']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Placeholder for Past Episodes -->
    <section class="podcast-section">
         <div class="container">
            <h2 class="section-title">Browse Past Episodes</h2>
            <p class="text-center text-muted">Our episode archive is coming soon. Stay tuned!</p>
        </div>
    </section>

</main>

<?php require_once 'includes/footer.php'; ?>

