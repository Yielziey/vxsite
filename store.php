<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

// Fetch all items from the store, ordered by ID
$items_stmt = $pdo->query("SELECT * FROM store ORDER BY id DESC");
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Swiper.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>

<style>
/* --- BAGONG CSS PARA SA STORE PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
}

.store-page {
    background-color: var(--vx-dark);
    color: var(--vx-text);
}

.store-hero {
    padding: 140px 1rem 4rem 1rem;
    text-align: center;
    background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+STORE') no-repeat center center/cover;
    border-bottom: 2px solid var(--vx-red);
}

.store-hero h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
}

.store-hero p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.2rem;
    color: #ccc;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Store Grid & Cards */
.store-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Laging 3 items per slide */
    gap: 2rem;
    padding: 2rem;
}

.store-card {
    background-color: var(--vx-dark-secondary);
    border: 1px solid #222;
    border-radius: 8px;
    text-align: center;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.store-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.5), 0 0 15px rgba(255, 42, 42, 0.2);
}

.media-wrapper {
    width: 100%;
    height: 400px; /* Fixed height for consistency */
    background-color: #000;
}

.store-card video, .store-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center; /* --- ITO ANG SOLUSYON --- */
}

.info-wrapper {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.store-card h3 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    font-size: 1.3rem;
    margin: 0 0 0.5rem 0;
}

.store-card .price {
    font-family: 'Titillium Web', sans-serif;
    font-weight: 700;
    color: #fff;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.order-btn {
    font-family: 'Xirod', sans-serif;
    background-color: var(--vx-red);
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    text-transform: uppercase;
    margin-top: auto; /* Pushes button to the bottom */
    transition: background-color 0.3s ease;
}

.order-btn:hover {
    background-color: #fff;
    color: var(--vx-red);
}

/* Swiper Navigation Styling */
.swiper-button-next, .swiper-button-prev {
    color: var(--vx-red) !important;
}
.swiper-pagination-bullet-active {
    background: var(--vx-red) !important;
}

/* Responsive */
@media (max-width: 992px) {
    .store-grid { grid-template-columns: 1fr; } /* 1 item per slide on smaller screens */
    .media-wrapper { height: 50vh; }
}
</style>

<main class="store-page">
    <section class="store-hero">
        <h1>VX Store</h1>
        <p>Official Vexillum Merchandise – Limited Drops</p>
    </section>

    <div class="container py-5">
        <div class="swiper store-swiper">
            <div class="swiper-wrapper">
                <?php if (empty($items)): ?>
                    <div class="swiper-slide" style="text-align: center; padding: 5rem 0;">
                        <p class="text-muted">No items available in the store right now.</p>
                    </div>
                <?php else: ?>
                    <?php 
                        // Group items into slides. 3 items per slide for large screens.
                        $slides = array_chunk($items, 3); 
                    ?>
                    <?php foreach ($slides as $slide_items): ?>
                        <div class="swiper-slide">
                            <section class="store-grid">
                                <?php foreach ($slide_items as $item): ?>
                                    <div class="store-card">
                                        <div class="media-wrapper">
                                            <?php 
                                                // Check if media is a video or image
                                                $media_extension = pathinfo($item['media'], PATHINFO_EXTENSION);
                                                $is_video = in_array(strtolower($media_extension), ['mp4', 'webm', 'mov']);
                                            ?>
                                            <?php if ($is_video): ?>
                                                <video src="<?= htmlspecialchars($item['media']) ?>" autoplay muted loop playsinline></video>
                                            <?php else: ?>
                                                <img src="<?= htmlspecialchars($item['media']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-wrapper">
                                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                                            <p class="price">₱<?= number_format($item['price'], 2) ?></p>
                                            <a href="vx_order_form.php?item=<?= urlencode($item['name']) ?>" class="order-btn">Order Now</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Swiper Controls -->
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</main>

<!-- Swiper.js JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  const swiper = new Swiper('.store-swiper', {
    loop: <?= count($items) > 3 ? 'true' : 'false' ?>, // Loop only if more than 1 slide
    autoplay: { 
        delay: 5000,
        disableOnInteraction: false,
    },
    pagination: { 
        el: '.swiper-pagination', 
        clickable: true 
    },
    navigation: { 
        nextEl: '.swiper-button-next', 
        prevEl: '.swiper-button-prev' 
    },
    // Responsive breakpoints
    breakpoints: {
        // when window width is >= 992px
        992: {
            slidesPerView: 1, // 1 slide (containing 3 items)
        },
        // when window width is < 992px
        0: {
            slidesPerView: 1, // 1 slide (containing 1 item)
             grid: {
                rows: 1,
            },
        }
    }
  });
</script>

<?php require_once 'includes/footer.php'; ?>

