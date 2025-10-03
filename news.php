<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

// Fetch featured news (limit to 1)
$featured_stmt = $pdo->prepare("SELECT id, title, content, `date`, image_url FROM news WHERE featured = 1 ORDER BY `date` DESC LIMIT 1");
$featured_stmt->execute();
$featured_news = $featured_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all regular news
$news_query = "SELECT id, title, content, `date`, image_url FROM news WHERE featured = 0";
if ($featured_news) {
    $news_query .= " AND id != " . $featured_news['id'];
}
$news_query .= " ORDER BY `date` DESC"; 
$news_items = $pdo->query($news_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch ALL matches
$matches = $pdo->query("SELECT * FROM matches ORDER BY `date` DESC, `time` DESC")->fetchAll(PDO::FETCH_ASSOC); 
?>

<style>
/* --- FINAL CSS PARA SA NEWS PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
}
.news-page { background-color: var(--vx-dark); color: var(--vx-text); }
.news-hero { text-align: center; padding: 140px 1rem 4rem 1rem; background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+NEWS') no-repeat center center/cover; border-bottom: 2px solid var(--vx-red);}
.news-hero h1 { font-family: 'Xirod', sans-serif; font-size: clamp(2.8rem, 8vw, 4rem); color: var(--vx-red); text-shadow: 0 0 15px var(--vx-red); }
.news-hero p { font-family: 'Titillium Web', sans-serif; font-size: 1.2rem; max-width: 700px; margin: 1rem auto 0; color: #ccc; }
.section-title { font-family: 'Xirod', sans-serif; color: var(--vx-red); text-align: center; margin-bottom: 3rem; text-transform: uppercase; font-size: 2.2rem; }

.clickable-card { cursor: pointer; }
.featured-card { background: var(--vx-dark-secondary); border: 1px solid #222; border-left: 5px solid var(--vx-red); transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 8px; overflow: hidden; }
.featured-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
.featured-card-img-container { flex-shrink: 0; width: 100%; }
.featured-card-img { width: 100%; height: 250px; object-fit: cover; }
@media (min-width: 768px) { .featured-card-img-container { width: 45%; } .featured-card-img { height: 100%; } }
.featured-card-body { padding: 2rem; }
.featured-card-body h3 { font-family: 'Xirod', sans-serif; color: var(--vx-red); font-size: clamp(1.5rem, 4vw, 2rem);}
.featured-card-body .content-text { font-family: 'Titillium Web', sans-serif; color: #bbb; font-size: 1.1rem; line-height: 1.8; }
.featured-card-body .date-text { font-family: 'Titillium Web', sans-serif; font-size: 0.9rem; font-weight: 600; color: var(--vx-text-muted); }

.news-grid .card { background: var(--vx-dark-secondary); border: 1px solid #222; transition: transform 0.3s ease, border-color 0.3s ease; border-radius: 8px; height: 100%; }
.news-grid .card:hover { transform: translateY(-5px); border-color: var(--vx-red); }
.news-grid .card-img-top { height: 200px; object-fit: cover; }
.news-grid .card-title { font-family: 'Xirod', sans-serif; color: var(--vx-red); font-size: 1.2rem; margin-bottom: 0.5rem; }
.news-grid .card-text { font-family: 'Titillium Web', sans-serif; color: #bbb; font-size: 0.95rem; line-height: 1.6; }
.news-grid .card-date { font-family: 'Titillium Web', sans-serif; font-size: 0.8rem; font-weight: 600; color: var(--vx-text-muted); }

/* --- News Modal & Carousel Styles (UPDATED) --- */
.news-modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1050; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s ease; }
.news-modal-backdrop.show { opacity: 1; }
.news-modal-content { background: var(--vx-dark-secondary); border: 1px solid #333; border-radius: 10px; width: 90%; max-width: 1100px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; }
.news-modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #333; }
.news-modal-title { font-family: 'Xirod', sans-serif; color: var(--vx-red); font-size: 1.5rem; margin: 0; }
.news-modal-close { background: none; border: none; color: #fff; font-size: 2rem; cursor: pointer; line-height: 1; opacity: 0.7; transition: opacity 0.2s; }
.news-modal-close:hover { opacity: 1; }
.news-modal-body { display: flex; flex-direction: column; overflow: hidden; flex-grow: 1; }
@media (min-width: 768px) { .news-modal-body { flex-direction: row; } }

.news-modal-carousel { position: relative; background: #000; display: flex; align-items: center; justify-content: center; min-height: 300px; width: 100%; }
.news-modal-carousel:hover .carousel-nav-btn { opacity: 1; } /* Lalabas lang kapag naka-hover */
@media (min-width: 768px) { .news-modal-carousel { width: 65%; } }
.carousel-image { width: 100%; height: 100%; max-height: 80vh; object-fit: contain; display: none; animation: fadeIn 0.5s; }
.carousel-image.active { display: block; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.carousel-nav-btn { 
    position: absolute; 
    top: 50%; 
    transform: translateY(-50%); 
    background: rgba(0,0,0,0.4); 
    color: #fff; 
    border: 1px solid #555;
    border-radius: 50%; 
    width: 45px; 
    height: 45px; 
    font-size: 1.5rem; 
    cursor: pointer; 
    z-index: 10; 
    display: none; 
    opacity: 0; /* Nakatago by default */
    transition: opacity 0.3s ease;
}
.carousel-nav-btn.prev { left: 15px; }
.carousel-nav-btn.next { right: 15px; }

.news-modal-text { padding: 1.5rem 2rem; overflow-y: auto; width: 100%; }
@media (min-width: 768px) { .news-modal-text { width: 35%; } }
.news-modal-text .date { font-size: 0.9rem; color: var(--vx-text-muted); margin-bottom: 1rem; }
.news-modal-text .content { line-height: 1.7; color: #ccc; }

/* ... iba pang styles ... */
.matches-table-container { background-color: var(--vx-dark-secondary); border-radius: 8px; padding: 1rem; border: 1px solid #222; }
.matches-table thead th { background-color: var(--vx-red) !important; color: var(--vx-dark) !important; font-family: 'Xirod', sans-serif; border: 0 !important; text-align: center; padding: 1rem 0.5rem; }
.matches-table tbody td { font-family: 'Titillium Web', sans-serif; font-weight: 700; vertical-align: middle; text-align: center; border-color: #333 !important; font-size: 0.95rem; text-transform: uppercase; padding: 1.2rem 0.5rem; }
.matches-table .btn-watch { background-color: var(--vx-red); color: #fff; font-weight: bold; padding: 0.5rem 1.2rem; border-radius: 20px; text-decoration: none; transition: background-color 0.2s, color 0.2s; border: 2px solid var(--vx-red); font-size: 0.8rem; }
.matches-table .btn-watch:hover { background-color: transparent; color: var(--vx-red); }
</style>

<main class="news-page">
    <section class="news-hero">
        <h1>News & Updates</h1>
        <p>The official source for all Vexillum announcements, match schedules, content drops, and community events.</p>
    </section>

    <div class="container py-5">
        <!-- Featured News -->
        <?php if ($featured_news): ?>
        <section class="mb-5">
            <h2 class="section-title">FEATURED</h2>
            <div class="featured-card d-flex flex-column flex-md-row clickable-card" onclick="openNewsModal(<?= $featured_news['id'] ?>)">
                <?php if (!empty($featured_news['image_url'])): ?>
                    <div class="featured-card-img-container">
                        <img src="admin/uploads/news/<?= htmlspecialchars($featured_news['image_url']) ?>" class="featured-card-img" alt="<?= htmlspecialchars($featured_news['title']) ?>">
                    </div>
                <?php endif; ?>
                <div class="featured-card-body">
                    <h3><?= htmlspecialchars($featured_news['title']) ?></h3>
                    <p class="date-text mb-3">Posted on <?= date('F j, Y', strtotime($featured_news['date'])) ?></p>
                    <p class="content-text"><?= nl2br(htmlspecialchars(substr($featured_news['content'], 0, 200))) . (strlen($featured_news['content']) > 200 ? '...' : '') ?></p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Latest News Section -->
        <section class="mb-5">
            <h2 class="section-title">LATEST NEWS</h2>
            <div class="row g-4 news-grid">
                <?php if (empty($news_items)): ?>
                    <p class="text-center text-muted">No other news articles found.</p>
                <?php else: ?>
                    <?php foreach ($news_items as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 d-flex flex-column clickable-card" onclick="openNewsModal(<?= $item['id'] ?>)">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="admin/uploads/news/<?= htmlspecialchars($item['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/600x400/141414/888?text=No+Image" class="card-img-top" alt="No Image Available">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-date mb-2"><?= date('F j, Y', strtotime($item['date'])) ?></p>
                                <p class="card-text flex-grow-1"><?= htmlspecialchars(substr($item['content'], 0, 120)) . (strlen($item['content']) > 120 ? '...' : '') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Match Schedule Section -->
        <section>
            <h2 class="section-title">MATCH SCHEDULE</h2>
            <div class="matches-table-container">
                <table class="table table-dark table-borderless matches-table">
                    <thead><tr><th>Date</th><th>Opponent</th><th>Time</th><th>Platform</th><th>Watch</th></tr></thead>
                    <tbody>
                        <?php if (empty($matches)): ?>
                            <tr><td colspan="5" class="text-muted p-4">No matches found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($match['date'])) ?></td>
                                <td>VS <?= htmlspecialchars($match['opponent']) ?></td>
                                <td><?= date('g:i A', strtotime($match['time'])) ?></td>
                                <td><?= htmlspecialchars($match['platform']) ?></td>
                                <td>
                                    <?php if($match['watch_link']): ?>
                                    <a href="<?= htmlspecialchars($match['watch_link']) ?>" target="_blank" class="btn-watch">WATCH</a>
                                    <?php else: echo 'N/A'; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- News Modal HTML -->
<div id="newsModal" class="news-modal-backdrop">
    <div class="news-modal-content">
        <div class="news-modal-header d-flex justify-content-between align-items-center">
            <h2 id="newsModalTitle" class="news-modal-title"></h2>
            <button onclick="closeNewsModal()" class="news-modal-close">&times;</button>
        </div>
        <div class="news-modal-body">
            <div id="newsModalCarousel" class="news-modal-carousel">
                <!-- Images will be injected here by JS -->
            </div>
            <div class="news-modal-text">
                <p id="newsModalDate" class="date"></p>
                <div id="newsModalContent" class="content"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
let currentImageIndex = 0;
let newsImages = [];

async function openNewsModal(newsId) {
    const modal = document.getElementById('newsModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);

    document.getElementById('newsModalTitle').innerText = 'Loading...';
    document.getElementById('newsModalContent').innerHTML = '';
    document.getElementById('newsModalCarousel').innerHTML = '<p class="text-muted p-4">Loading images...</p>';

    const formData = new FormData();
    formData.append('action', 'get_news_details');
    formData.append('id', newsId);

    try {
        const response = await fetch('admin/api_handler.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.success && result.data.news) {
            const news = result.data.news;
            newsImages = news.all_images || [];
            currentImageIndex = 0;
            
            document.getElementById('newsModalTitle').innerText = news.title;
            const date = new Date(news.date.replace(' ', 'T'));
            document.getElementById('newsModalDate').innerText = `Posted on ${date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
            document.getElementById('newsModalContent').innerHTML = news.content.replace(/\n/g, '<br>');

            const carousel = document.getElementById('newsModalCarousel');
            carousel.innerHTML = ''; // Clear loading message

            if (newsImages.length > 0) {
                newsImages.forEach((img, index) => {
                    const imgElement = document.createElement('img');
                    imgElement.src = `admin/uploads/news/${img}`;
                    imgElement.classList.add('carousel-image');
                    if (index === 0) imgElement.classList.add('active');
                    carousel.appendChild(imgElement);
                });

                if (newsImages.length > 1) {
                    const prevBtn = document.createElement('button');
                    prevBtn.id = 'prevBtn';
                    prevBtn.className = 'carousel-nav-btn prev';
                    prevBtn.innerHTML = '&lt;';
                    carousel.appendChild(prevBtn);

                    const nextBtn = document.createElement('button');
                    nextBtn.id = 'nextBtn';
                    nextBtn.className = 'carousel-nav-btn next';
                    nextBtn.innerHTML = '&gt;';
                    carousel.appendChild(nextBtn);
                }
            } else {
                carousel.innerHTML = '<p class="text-muted p-4">No Image Available</p>';
            }
            updateCarousel();
        } else {
            document.getElementById('newsModalContent').innerText = 'Failed to load article content.';
        }
    } catch (error) {
        document.getElementById('newsModalContent').innerText = 'An error occurred while fetching the article.';
        console.error('Error fetching news:', error);
    }
}

function closeNewsModal() {
    const modal = document.getElementById('newsModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

function updateCarousel() {
    const images = document.querySelectorAll('#newsModalCarousel .carousel-image');
    if (images.length === 0) return;

    images.forEach((img, index) => {
        img.classList.toggle('active', index === currentImageIndex);
    });

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if(prevBtn && nextBtn) {
        prevBtn.style.display = newsImages.length > 1 ? 'block' : 'none';
        nextBtn.style.display = newsImages.length > 1 ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'nextBtn') {
        currentImageIndex = (currentImageIndex + 1) % newsImages.length;
        updateCarousel();
    }
    if (e.target && e.target.id === 'prevBtn') {
        currentImageIndex = (currentImageIndex - 1 + newsImages.length) % newsImages.length;
        updateCarousel();
    }
    if (e.target.matches('.news-modal-backdrop.show')) {
        closeNewsModal();
    }
});
</script>

