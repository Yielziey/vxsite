<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

// Fetch all active creators from the database
$creators_stmt = $pdo->query("SELECT * FROM creators WHERE status = 'active' ORDER BY creator_name ASC");
$creators = $creators_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- BAGONG CSS PARA SA CREATORS PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
}

.creators-page {
    background-color: var(--vx-dark);
    color: var(--vx-text);
}

.creators-hero {
    padding: 140px 1rem 4rem 1rem;
    text-align: center;
    background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+CREATORS') no-repeat center center/cover;
    border-bottom: 2px solid var(--vx-red);
}

.creators-hero h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
}

.creators-hero p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.2rem;
    max-width: 700px;
    margin: 1rem auto 0;
    color: #ccc;
}

/* Search Bar Styling */
.search-container {
    padding: 2rem 1rem;
    text-align: center;
}
#searchInput {
    font-family: 'Titillium Web', sans-serif;
    background-color: var(--vx-dark-secondary);
    border: 2px solid #333;
    color: var(--vx-text);
    padding: 0.8rem 1.5rem;
    border-radius: 50px; /* Pill shape */
    width: 100%;
    max-width: 400px;
    font-size: 1rem;
    transition: all 0.3s ease;
}
#searchInput::placeholder {
    color: var(--vx-text-muted);
}
#searchInput:focus {
    outline: none;
    border-color: var(--vx-red);
    box-shadow: 0 0 15px rgba(255, 42, 42, 0.3);
}

/* Creator Cards */
.creators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.creator-card {
    background-color: var(--vx-dark-secondary);
    border: 1px solid #222;
    border-radius: 8px;
    text-align: center;
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.creator-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.5), 0 0 15px rgba(255, 42, 42, 0.2);
}

.creator-card .creator-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    margin: 0 auto 1.5rem auto;
    border: 4px solid var(--vx-red);
}

.creator-card .creator-name {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    font-size: 1.3rem;
    margin-bottom: 1rem;
}

.creator-socials {
    margin-top: auto; /* Pushes socials to the bottom */
    display: flex;
    justify-content: center;
    gap: 1.2rem;
}

.creator-socials a {
    color: var(--vx-text-muted);
    font-size: 1.5rem;
    transition: color 0.3s ease, transform 0.3s ease;
}

.creator-socials a:hover {
    color: var(--vx-red);
    transform: scale(1.2);
}

.no-results {
    font-family: 'Titillium Web', sans-serif;
    text-align: center;
    color: var(--vx-text-muted);
    padding: 3rem;
    grid-column: 1 / -1; /* Span full width */
}
</style>

<main class="creators-page">
    <section class="creators-hero">
        <h1>VX Creators</h1>
        <p>Meet the talented individuals representing the Vexillum standard across multiple platforms.</p>
    </section>
    
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search for a creator..." onkeyup="filterCreators()">
    </div>

    <div class="container pb-5">
        <section class="creators-grid" id="creatorsGrid">
            <?php if (empty($creators)): ?>
                <p class="no-results">No creators found in the database.</p>
            <?php else: ?>
                <?php foreach ($creators as $creator): ?>
                    <?php
                        // Decode the social media JSON string
                        $socials = json_decode($creator['social_media'], true);
                    ?>
                    <div class="creator-card" data-name="<?= strtolower(htmlspecialchars($creator['creator_name'])) ?>">
                        <img src="<?= htmlspecialchars($creator['profile_picture'] ? $creator['profile_picture'] : 'assets/images/default_player.jpg') ?>" alt="<?= htmlspecialchars($creator['creator_name']) ?>" class="creator-image">
                        <h5 class="creator-name"><?= htmlspecialchars($creator['creator_name']) ?></h5>
                        
                        <div class="creator-socials">
                            <?php if (!empty($socials['facebook'])): ?><a href="<?= htmlspecialchars($socials['facebook']) ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if (!empty($socials['tiktok'])): ?><a href="<?= htmlspecialchars($socials['tiktok']) ?>" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                            <?php if (!empty($socials['twitch'])): ?><a href="<?= htmlspecialchars($socials['twitch']) ?>" target="_blank" title="Twitch"><i class="fab fa-twitch"></i></a><?php endif; ?>
                            <?php if (!empty($socials['kick'])): ?><a href="<?= htmlspecialchars($socials['kick']) ?>" target="_blank" title="Kick"><i class="fa-brands fa-kickstarter-k"></i></a><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <h3>No Creator Found</h3>
                <p>Try checking the spelling or searching for another name.</p>
            </div>
        </section>
    </div>
</main>

<!-- Kailangan mo ng Font Awesome para sa social icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
function filterCreators() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const creatorCards = document.querySelectorAll('.creator-card');
    const noResultsMessage = document.getElementById('noResultsMessage');
    let visibleCount = 0;

    creatorCards.forEach(card => {
        const name = card.dataset.name;
        if (name.includes(query)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (visibleCount === 0) {
        noResultsMessage.style.display = 'block';
    } else {
        noResultsMessage.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
