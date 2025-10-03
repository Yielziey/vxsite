<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

// Fetch all active sponsors from the database
$sponsors_stmt = $pdo->query("SELECT * FROM sponsors WHERE status = 'active' ORDER BY sponsor_name ASC");
$sponsors = $sponsors_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- BAGONG CSS PARA SA SPONSORS PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
}

.sponsors-page {
    background-color: var(--vx-dark);
    color: var(--vx-text);
}

.sponsors-hero {
    padding: 140px 1rem 4rem 1rem;
    text-align: center;
    background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+PARTNERS') no-repeat center center/cover;
    border-bottom: 2px solid var(--vx-red);
}

.sponsors-hero h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
}

.sponsors-hero p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.2rem;
    max-width: 700px;
    margin: 1rem auto 0;
    color: #ccc;
}

.section-title {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    text-align: center;
    margin-bottom: 3rem;
    text-transform: uppercase;
    font-size: 2.2rem;
}

/* Sponsor Grid */
.sponsors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.sponsor-card {
    background-color: var(--vx-dark-secondary);
    border: 1px solid #222;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sponsor-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.5), 0 0 15px rgba(255, 42, 42, 0.2);
}

.sponsor-card .logo-container {
    background-color: #fff; /* White background for logos */
    padding: 2rem;
    height: 180px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.sponsor-card .sponsor-logo {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.sponsor-card .info-container {
    padding: 1.5rem;
    text-align: center;
}

.sponsor-card .sponsor-name {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    font-size: 1.4rem;
    margin-bottom: 1rem;
}

.sponsor-card .visit-btn {
    font-family: 'Titillium Web', sans-serif;
    background-color: var(--vx-red);
    color: #fff;
    font-weight: 700;
    padding: 0.6rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    text-transform: uppercase;
    display: inline-block;
    transition: background-color 0.3s ease;
}

.sponsor-card .visit-btn:hover {
    background-color: var(--vx-red-dark);
}
</style>

<main class="sponsors-page">
    <section class="sponsors-hero">
        <h1>Partners & Sponsors</h1>
        <p>We are proud to be supported by passionate partners and sponsors that believe in our mission, our players, and our community.</p>
    </section>

    <div class="container py-5">
        <section>
            <h2 class="section-title">OUR PARTNERS</h2>
            <div class="sponsors-grid">
                <?php if (empty($sponsors)): ?>
                    <p class="text-center text-muted col-12">No sponsors to display at the moment.</p>
                <?php else: ?>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <div class="sponsor-card">
                            <div class="logo-container">
                                <img src="<?= htmlspecialchars($sponsor['sponsor_logo'] ? $sponsor['sponsor_logo'] : 'assets/sponsors/default.png') ?>" alt="<?= htmlspecialchars($sponsor['sponsor_name']) ?> Logo" class="sponsor-logo">
                            </div>
                            <div class="info-container">
                                <h3 class="sponsor-name"><?= htmlspecialchars($sponsor['sponsor_name']) ?></h3>
                                <a href="<?= htmlspecialchars($sponsor['website_url']) ?>" class="visit-btn" target="_blank">Visit Website</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
