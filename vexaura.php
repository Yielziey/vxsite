<?php 
require_once 'includes/header.php';

// Tiyakin na mayroon tayong DB at Spotify config
$pdo = null;
if (file_exists('db_connect.php')) {
    require_once 'db_connect.php';
}
if (file_exists('spotify_config.php')) {
    require_once 'spotify_config.php';
} else {
    // Fallback if Spotify config is missing
    define('SPOTIFY_CLIENT_ID', 'fallback_id');
    define('SPOTIFY_CLIENT_SECRET', 'fallback_secret');
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

// Kumuha ng Main VexAura Details mula sa DB
$main_vexaura_id = getSetting($pdo, 'vexaura_main_spotify_id', '37QynDch02SFAB3ojVl6r2'); 
$main_vexaura_apple_url = getSetting($pdo, 'vexaura_main_apple_music_url', '');
$main_vexaura_youtube_url = getSetting($pdo, 'vexaura_main_youtube_music_url', '');
$main_vexaura_vex_platform_url = getSetting($pdo, 'vexaura_main_vex_platform_url', '');

// Kumuha ng Featured Artists mula sa DB
$featured_artists_from_db = [];
if ($pdo) {
    try {
        // INAYOS: Kinuha na rin ang other platform URLs
        $artists_stmt = $pdo->query("SELECT spotify_id, apple_music_url, youtube_music_url FROM featured_artists ORDER BY sort_order");
        $featured_artists_from_db = $artists_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching featured artists: " . $e->getMessage());
        $featured_artists_from_db = []; // Fallback to empty array on error
    }
}
// --- END DB READ LOGIC ---

// --- Spotify API Logic ---
function getArtistSpotifyData($clientId, $clientSecret, $artistId) {
    if (empty($artistId)) return null;
    $tokenUrl = 'https://accounts.spotify.com/api/token';
    $artistApiUrl = 'https://api.spotify.com/v1/artists/' . $artistId;
    $topTracksApiUrl = $artistApiUrl . '/top-tracks?market=PH';

    $returnData = [
        'name' => 'Artist',
        'followers' => 'N/A', 
        'top_tracks' => [], 
        'image_url' => 'https://placehold.co/600x600/0a0a0a/fff?text=Artist'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret)));
    $tokenResult = curl_exec($ch);
    $tokenData = json_decode($tokenResult, true);
    
    if (!isset($tokenData['access_token'])) {
        curl_close($ch);
        return $returnData;
    }
    $accessToken = $tokenData['access_token'];
    $authHeader = 'Authorization: Bearer ' . $accessToken;

    curl_setopt($ch, CURLOPT_URL, $artistApiUrl);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader));
    $artistResult = curl_exec($ch);
    $artistData = json_decode($artistResult, true);

    if (isset($artistData['name'])) $returnData['name'] = $artistData['name'];
    if (isset($artistData['followers']['total'])) $returnData['followers'] = number_format($artistData['followers']['total']);
    if (isset($artistData['images'][0]['url'])) $returnData['image_url'] = $artistData['images'][0]['url'];

    curl_setopt($ch, CURLOPT_URL, $topTracksApiUrl);
    $topTracksResult = curl_exec($ch);
    $topTracksData = json_decode($topTracksResult, true);

    if (isset($topTracksData['tracks'])) $returnData['top_tracks'] = $topTracksData['tracks'];
    
    curl_close($ch);
    return $returnData;
}

// Main artist (VexAura) 
$vexauraData = getArtistSpotifyData(SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET, $main_vexaura_id);

// Featured artists 
$other_artists_data = [];
foreach ($featured_artists_from_db as $artist_db_info) {
    $spotify_data = getArtistSpotifyData(SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET, $artist_db_info['spotify_id']);
    if ($spotify_data) {
        // Pinagsama ang data mula Spotify at data mula sa DB (para makuha URLs)
        $other_artists_data[] = array_merge($spotify_data, $artist_db_info);
    }
}
?>

<style>
/* --- ESTILO PARA SA VEXAURA PAGE --- */
:root { --vx-red: #ff2a2a; --vx-dark: #0a0a0a; --vx-dark-secondary: #141414; --vx-text: #e0e0e0; --vx-text-muted: #888; --spotify-green: #1DB954; }
.vexaura-page { background-color: var(--vx-dark); color: var(--vx-text); padding-top: 100px; }
.page-hero { padding: 4rem 1rem; text-align: center; background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x600/0a0a0a/1DB954?text=VexAura') no-repeat center center/cover; border-bottom: 2px solid var(--vx-red); }
.page-hero h1 { font-family: 'Xirod', sans-serif; font-size: clamp(2.8rem, 8vw, 4rem); color: #fff; text-shadow: 0 0 15px var(--spotify-green); }
.page-hero p { font-family: 'Titillium Web', sans-serif; font-size: 1.2rem; max-width: 800px; margin: 1rem auto 0; color: #ccc; line-height: 1.7; }
.content-subtitle { font-family: 'Xirod', sans-serif; color: #fff; border-bottom: 1px solid #444; padding-bottom: 0.5rem; margin-bottom: 1.5rem; }

/* Artist Profile Card */
.artist-profile-card { position: relative; background-color: var(--vx-dark-secondary); border-radius: 12px; overflow: hidden; height: 400px; display: flex; align-items: center; justify-content: center; text-align: center; }
.artist-cover { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; filter: blur(15px) brightness(0.4); transform: scale(1.1); }
.artist-profile-content { position: relative; z-index: 2; padding: 2rem; display: flex; flex-direction: column; align-items: center; }
.artist-profile-pic { width: 140px; height: 140px; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.5); object-fit: cover; margin-bottom: 1rem; }
.artist-name { font-family: 'Xirod', sans-serif; color: #fff; font-size: 2.5rem; margin: 0; }
.artist-profile-content .stat-item p { font-size: 1.2rem; font-weight: bold; color: #fff; margin: 0; }
.artist-profile-content .stat-item h5 { font-size: 0.9rem; color: var(--vx-text-muted); margin-bottom: 1rem; }
.btn-spotify-follow { background-color: var(--spotify-green); color: #fff; border: none; border-radius: 50px; padding: 0.75rem 2rem; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
.btn-spotify-follow:hover { background-color: #1ed760; color: #fff; }

/* Top Tracks Player */
.track-list-container { background-color: var(--vx-dark-secondary); border: 1px solid #222; border-radius: 8px; padding: 1rem; max-height: 352px; overflow-y: auto; }
.track-list-item { display: flex; align-items: center; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: background-color 0.2s ease-in-out; border: 1px solid transparent; }
.track-list-item:hover { background-color: #2a2a2a; }
.track-list-item.active { background-color: var(--spotify-green); border-color: var(--spotify-green); }
.track-list-item img { width: 50px; height: 50px; border-radius: 4px; margin-right: 15px; }
.track-list-item .track-details h5 { font-size: 0.9rem; font-weight: bold; color: #fff; margin: 0; }
.track-list-item .track-details p { font-size: 0.8rem; color: var(--vx-text-muted); margin: 0; }
.track-list-item.active .track-details p { color: #fff; }
.main-player-container iframe { height: 352px; }
.famous-song-label { font-family: 'Titillium Web', sans-serif; font-weight: 700; color: var(--vx-text-muted); text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; margin-bottom: 0.75rem; }

/* Featured Artists */
.featured-artist-card { background-color: var(--vx-dark-secondary); border-radius: 8px; padding: 1.5rem; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.featured-artist-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.4); }
.featured-artist-card img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem auto; border: 3px solid #333; }
.featured-artist-card h5 { font-family: 'Xirod', sans-serif; color: #fff; margin-bottom: 0.25rem; }
.featured-artist-card p { color: var(--vx-text-muted); font-size: 0.9rem; margin-bottom: 1rem; }
.featured-artist-card .btn-spotify-follow { padding: 0.5rem 1.5rem; font-size: 0.9rem; }

/* Platform Icons */
.artist-links { display: flex; justify-content: center; gap: 1.25rem; margin-top: 1rem; }
.artist-link-icon { color: #a0a0a0; font-size: 1.75rem; transition: all 0.3s ease; text-decoration: none; }
.artist-link-icon:hover { transform: scale(1.15); }
.artist-link-icon.spotify:hover { color: #1DB954; }
.artist-link-icon.apple-music:hover { color: #FA57C1; } 
.artist-link-icon.youtube:hover { color: #FF0000; }
.artist-link-icon.vex:hover { color: var(--vx-red); }
</style>

<main class="vexaura-page">
    <div class="container py-5">
        <section class="page-hero">
            <h1>VexAura</h1>
            <p>The official soundscape of Vexillum. The music that fuels our victories and defines our journey.</p>
        </section>

        <!-- Main VexAura Section -->
        <section class="container py-5">
            <div class="row align-items-stretch g-4">
                <div class="col-lg-5">
                    <div class="artist-profile-card">
                        <div class="artist-cover" style="background-image: url('<?php echo htmlspecialchars($vexauraData['image_url']); ?>');"></div>
                        <div class="artist-profile-content">
                            <img src="<?php echo htmlspecialchars($vexauraData['image_url']); ?>" alt="VexAura Profile Picture" class="artist-profile-pic">
                            <h2 class="artist-name"><?php echo htmlspecialchars($vexauraData['name']); ?></h2>
                            <div class="stat-item">
                                <p><?php echo htmlspecialchars($vexauraData['followers']); ?></p>
                                <h5>Followers on Spotify</h5>
                            </div>
                            <a href="https://open.spotify.com/artist/<?php echo htmlspecialchars($main_vexaura_id); ?>" target="_blank" class="btn-spotify-follow">
                                <i class="fab fa-spotify me-2"></i>Follow on Spotify
                            </a>
                            <!-- DINAGDAG: Mga icons para sa main artist -->
                            <div class="artist-links">
                                <?php if (!empty($main_vexaura_vex_platform_url)): ?>
                                    <a href="<?php echo htmlspecialchars($main_vexaura_vex_platform_url); ?>" target="_blank" class="artist-link-icon vex" title="Listen on VexAura Music"><i class="fas fa-record-vinyl"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($main_vexaura_apple_url)): ?>
                                    <a href="<?php echo htmlspecialchars($main_vexaura_apple_url); ?>" target="_blank" class="artist-link-icon apple-music" title="Listen on Apple Music"><i class="fab fa-apple"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($main_vexaura_youtube_url)): ?>
                                    <a href="<?php echo htmlspecialchars($main_vexaura_youtube_url); ?>" target="_blank" class="artist-link-icon youtube" title="Listen on YouTube Music"><i class="fab fa-youtube"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <h5 class="famous-song-label">Famous Song</h5>
                    <?php if (!empty($vexauraData['top_tracks'])): ?>
                        <iframe style="border-radius:12px" src="https://open.spotify.com/embed/track/<?php echo htmlspecialchars($vexauraData['top_tracks'][0]['id']); ?>?utm_source=generator&theme=0" width="100%" height="315" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-dark-secondary rounded-3"><p>Player not available.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- VexAura Top Tracks Interactive Player -->
        <section class="container pt-4">
            <div class="top-tracks-player">
                <h4 class="content-subtitle">VexAura Top Tracks</h4>
                <?php if (!empty($vexauraData['top_tracks'])): ?>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="track-list-container">
                                <?php foreach ($vexauraData['top_tracks'] as $index => $track): ?>
                                    <div class="track-list-item <?php echo $index === 0 ? 'active' : ''; ?>" data-track-id="<?php echo htmlspecialchars($track['id']); ?>">
                                        <img src="<?php echo htmlspecialchars($track['album']['images'][2]['url']); ?>" alt="<?php echo htmlspecialchars($track['name']); ?>">
                                        <div class="track-details">
                                            <h5><?php echo htmlspecialchars($track['name']); ?></h5>
                                            <p><?php echo htmlspecialchars($track['artists'][0]['name']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="main-player-container">
                                <iframe id="main-spotify-player" style="border-radius:12px" src="https://open.spotify.com/embed/track/<?php echo htmlspecialchars($vexauraData['top_tracks'][0]['id']); ?>?utm_source=generator&theme=0" width="100%" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Artists -->
        <section class="container pt-5 mt-5">
            <h4 class="content-subtitle">Featured Artists</h4>
            <div class="row g-4">
                <?php foreach ($other_artists_data as $artist): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="featured-artist-card">
                            <img src="<?php echo htmlspecialchars($artist['image_url']); ?>" alt="<?php echo htmlspecialchars($artist['name']); ?>">
                            <h5><?php echo htmlspecialchars($artist['name']); ?></h5>
                            <p><?php echo htmlspecialchars($artist['followers']); ?> Followers</p>
                            <!-- BINAGO: Pinalitan ang "View Profile" button ng mga platform icons -->
                             <div class="artist-links">
                                <a href="https://open.spotify.com/artist/<?php echo htmlspecialchars($artist['spotify_id']); ?>" target="_blank" class="artist-link-icon spotify" title="Listen on Spotify"><i class="fab fa-spotify"></i></a>
                                <?php if (!empty($artist['apple_music_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($artist['apple_music_url']); ?>" target="_blank" class="artist-link-icon apple-music" title="Listen on Apple Music"><i class="fab fa-apple"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($artist['youtube_music_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($artist['youtube_music_url']); ?>" target="_blank" class="artist-link-icon youtube" title="Listen on YouTube Music"><i class="fab fa-youtube"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackItems = document.querySelectorAll('.track-list-item');
    const mainPlayer = document.getElementById('main-spotify-player');

    if (trackItems.length > 0 && mainPlayer) {
        trackItems.forEach(item => {
            item.addEventListener('click', function() {
                trackItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const trackId = this.dataset.trackId;
                mainPlayer.src = `https://open.spotify.com/embed/track/${trackId}?utm_source=generator&theme=0`;
            });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

