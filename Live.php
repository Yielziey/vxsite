<?php 
// This is your database connection configuration.
// Replace with your actual database credentials.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'vxsite_db'; // <-- CHANGE THIS TO YOUR DATABASE NAME

$conn = null;
$dbError = '';
$allStreamers = [];

try {
  $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // Fetch all streamers from the database only if connection is successful
  $stmt = $conn->prepare("SELECT * FROM streamers");
  $stmt->execute();
  $allStreamers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
  // Store the error message
  $dbError = "Database connection failed: " . $e->getMessage();
} finally {
  // Close the connection
  $conn = null;
}

// Convert the PHP array to a JSON string for use in JavaScript
$streamersJson = json_encode($allStreamers);

?>

<?php include 'includes/header.php'; ?>

<!-- Kailangan mong i-include ang Font Awesome para sa icons at ang 'db.php' file para sa database connection. -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Add the Tailwind CSS CDN to your header.php file if it's not already there. -->
<!-- <script src="https://cdn.tailwindcss.com"></script> -->

<style>
  /* Base styles from the About Us page */
  :root { 
    --vx-red: #ff2a2a; 
    --vx-green: #25a507;
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
  }
  
  body {
    background-color: var(--vx-dark);
    color: var(--vx-text);
  }

  .vx-page {
    min-height: calc(100vh - 100px); /* Adjust based on header and footer height */
    background-color: var(--vx-dark);
    color: var(--vx-text);
  }

  .vx-hero {
    padding: 140px 1rem 4rem 1rem;
    text-align: center;
    background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+STREAMERS') no-repeat center center/cover;
    border-bottom: 2px solid var(--vx-red);
  }

  .vx-title {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
    text-align: center;
    margin-bottom: 3rem;
  }
  
  .platform-title {
    font-family: 'Xirod', sans-serif;
    font-size: 2rem;
    color: var(--vx-red);
    text-align: center;
    margin-top: 2rem;
    margin-bottom: 1rem;
    text-shadow: 0 0 10px rgba(255, 42, 42, 0.4);
  }

  /* Creator Card Design from creators.php */
  .creators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 2rem;
    padding: 2rem;
    margin: 0 auto;
    max-width: 1200px;
  }
  .creator-card {
    background-color: var(--vx-dark-secondary);
    border: 2px solid;
    border-radius: 10px;
    text-align: center;
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    cursor: pointer;
  }
  .creator-card:hover {
    transform: translateY(-8px);
  }
  .creator-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    background-color: var(--vx-dark);
    border: 4px solid;
    object-fit: cover;
    object-position: center;
  }
  .creator-name {
    font-size: 1.2rem;
    margin-bottom: 0.3rem;
    font-family: 'Xirod', sans-serif;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .platform-info {
    font-size: 0.9rem;
    text-transform: capitalize;
  }
  .live-dot {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
  }
  /* Updated styling to remove the underline */
  .creator-card a {
    text-decoration: none !important;
  }
  
  /* Status Colors */
  .is-live {
    border-color: var(--vx-green);
    box-shadow: 0 0 15px rgba(37, 165, 7, 0.6);
  }
  .is-live:hover {
    box-shadow: 0 0 20px rgba(37, 165, 7, 0.8);
  }
  .is-live .live-dot { background-color: var(--vx-green); }
  .is-live .creator-image { border-color: var(--vx-green); }
  .is-live .creator-image span { color: var(--vx-green); }
  .is-live .creator-name { color: var(--vx-green); }
  .is-live .platform-info { color: var(--vx-green); }

  .is-offline {
    border-color: var(--vx-red);
    box-shadow: 0 0 15px rgba(255, 42, 42, 0.6);
  }
  .is-offline:hover {
    box-shadow: 0 0 20px rgba(255, 42, 42, 0.8);
  }
  .is-offline .live-dot { background-color: var(--vx-red); }
  .is-offline .creator-image { border-color: var(--vx-red); }
  .is-offline .creator-image span { color: var(--vx-red); }
  .is-offline .creator-name { color: var(--vx-red); }
  .is-offline .platform-info { color: var(--vx-red); }

</style>

<main class="vx-page">
  <section class="vx-hero">
    <h1 class="vx-title">VX STREAMERS – LIVE NOW</h1>
    <p class="text-white text-lg max-w-2xl mx-auto mt-4 font-bold">
      Watch our official streamers as they go live.
    </p>
  </section>

  <div id="loading-spinner" class="text-center py-10">
    <div class="inline-block h-10 w-10 loading-spinner-static rounded-full border-4 border-solid border-current border-r-transparent text-red-500"></div>
  </div>

  <div id="streamers-container" class="opacity-0 transition-opacity duration-500">
    <!-- Streamer cards will be inserted here -->
  </div>

  <p id="error-message" class="hidden text-center text-red-500 mt-8"></p>
</main>

<script>
  const allStreamers = <?php echo $streamersJson; ?>;
  const dbError = "<?php echo $dbError ?? ''; ?>";

  if (dbError) {
    document.getElementById('loading-spinner').classList.add('hidden');
    document.getElementById('error-message').textContent = dbError;
    document.getElementById('error-message').classList.remove('hidden');
  }

  const getAccessToken = async () => {
    try {
      const res = await fetch('get_token.php');
      const data = await res.json();
      return data.access_token;
    } catch (err) {
      console.error('Failed to get Twitch access token:', err);
      return null;
    }
  };

  const fetchStreamerStatus = async () => {
    const spinner = document.getElementById('loading-spinner');
    const container = document.getElementById('streamers-container');
    const errorMessage = document.getElementById('error-message');

    if (!dbError) {
      spinner.classList.remove('hidden');
      container.classList.add('opacity-0');
      errorMessage.classList.add('hidden');

      try {
        const twitchUsers = allStreamers.filter(s => s.platform === 'twitch').map(s => s.username);
        const kickUsers = allStreamers.filter(s => s.platform === 'kick').map(s => s.username);
        const tiktokUsers = allStreamers.filter(s => s.platform === 'tiktok').map(s => s.username);

        const [twitchStatus, kickStatus] = await Promise.all([
          fetchTwitchData(twitchUsers),
          fetchKickData(kickUsers)
        ]);

        const merged = allStreamers.map(s => {
          const isLive = s.platform === 'twitch'
            ? twitchStatus.live.includes(s.username)
            : kickStatus.live.includes(s.username);
          return { ...s, isLive };
        });

        merged.sort((a, b) => b.isLive - a.isLive);
        displayStreamers(merged);

      } catch (err) {
        errorMessage.textContent = 'Error loading streamer data.';
        errorMessage.classList.remove('hidden');
        console.error(err);
      } finally {
        spinner.classList.add('hidden');
        container.classList.remove('opacity-0');
      }
    }
  };

  const fetchTwitchData = async (users) => {
    if (users.length === 0) return { live: [] };
    const token = await getAccessToken();
    if (!token) return { live: [] };

    const res = await fetch(`https://api.twitch.tv/helix/streams?user_login=${users.join('&user_login=')}`, {
      headers: { 'Client-ID': 'x1vajyu6nhake660l6zn8o3hnsytc8', 'Authorization': `Bearer ${token}` }
    });

    if (!res.ok) throw new Error(`Twitch API error: ${res.statusText}`);
    const data = await res.json();
    return { live: data.data.map(s => s.user_login.toLowerCase()) };
  };

  const fetchKickData = async (users) => {
    if (users.length === 0) return { live: [] };
    const liveUsers = [];
    await Promise.all(users.map(async u => {
      try {
        const res = await fetch(`get_kick_status.php?user=${u}`);
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (data.livestream && data.livestream.is_live) liveUsers.push(u);
      } catch (e) { console.error('Kick error for ${u}', e); }
    }));
    return { live: liveUsers };
  };

  const displayStreamers = (streamers) => {
    const container = document.getElementById('streamers-container');
    container.innerHTML = '';
    
    // Group streamers by platform
    const platforms = {
      'twitch': [],
      'kick': [],
      'tiktok': []
    };
    streamers.forEach(s => platforms[s.platform].push(s));
    
    for (const platform in platforms) {
        if (platforms[platform].length > 0) {
            container.innerHTML += `<h2 class="platform-title">${platform}</h2>`;
            container.innerHTML += `<div class="creators-grid" id="${platform}-grid"></div>`;
            
            const platformGrid = document.getElementById(`${platform}-grid`);
            platforms[platform].forEach(s => {
                const isLive = s.isLive;
                const statusClass = isLive ? 'is-live' : 'is-offline';
                const streamUrl = isLive ? `https://${s.platform}.tv/${s.username}` : '#';
                
                const card = `
                    <div class="creator-card ${statusClass}">
                        <div class="live-dot"></div>
                        <a href="${streamUrl}" target="_blank">
                            <div class="creator-image">
                                <span class="font-bold">${s.name ? s.name.charAt(0) : '?'}</span>
                            </div>
                        </a>
                        <h5 class="creator-name">${s.name}</h5>
                        <p class="platform-info">${s.platform} - <span class="status-text">${isLive ? 'LIVE' : 'OFFLINE'}</span></p>
                    </div>
                `;
                platformGrid.innerHTML += card;
            });
        }
    }
  };

  fetchStreamerStatus();
  setInterval(fetchStreamerStatus, 60000);
</script>

<?php include 'includes/footer.php'; ?>
