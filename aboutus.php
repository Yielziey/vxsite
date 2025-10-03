<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

$members = $pdo->query("SELECT * FROM sentro ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- FINAL CSS PARA SA ABOUT US PAGE --- */
:root { --vx-red: #ff2a2a; --vx-dark: #0a0a0a; --vx-dark-secondary: #141414; --vx-text: #e0e0e0; --vx-text-muted: #888; }
.about-page { background-color: var(--vx-dark); color: var(--vx-text); }
.about-hero { padding: 140px 1rem 4rem 1rem; text-align: center; background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VEXILLUM') no-repeat center center/cover; border-bottom: 2px solid var(--vx-red); }
.about-hero h1 { font-family: 'Xirod', sans-serif; font-size: clamp(2.8rem, 8vw, 4rem); color: var(--vx-red); text-shadow: 0 0 15px var(--vx-red); }
.about-hero p { font-family: 'Titillium Web', sans-serif; font-size: 1.2rem; max-width: 800px; margin: 1rem auto 0; color: #ccc; line-height: 1.7; }
.section-title { font-family: 'Xirod', sans-serif; color: var(--vx-red); text-align: center; margin-bottom: 3rem; text-transform: uppercase; font-size: 2.2rem; }

/* SENTRO Member Cards */
.sentro-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; }
.member-card { background-color: var(--vx-dark-secondary); border: 1px solid #222; border-radius: 8px; text-align: center; padding: 1.5rem 1rem; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; height: 100%; cursor: pointer; }
.member-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.5), 0 0 15px rgba(255, 42, 42, 0.2); }
.member-card .member-image { width: 149px; height: 155px; border-radius: 50%; object-fit: cover; object-position: center; margin: 0 auto 1.5rem auto; border: 4px solid var(--vx-red); background-color: #000; }
.member-card .member-name { font-family: 'Xirod', sans-serif; color: var(--vx-red); font-size: 1.3rem; margin-bottom: 0.5rem; }
.member-card .member-role { font-family: 'Titillium Web', sans-serif; color: #ccc; font-size: 0.9rem; font-weight: 600; min-height: 40px; margin-bottom: 1.5rem; }
.member-socials { margin-top: auto; display: flex; justify-content: center; gap: 1rem; }
.member-socials a { color: var(--vx-text-muted); font-size: 1.3rem; transition: color 0.3s ease, transform 0.3s ease; }
.member-socials a:hover { color: var(--vx-red); transform: scale(1.2); }

/* --- BOOK-STYLE MODAL --- */
.member-modal-content { background-color: var(--vx-dark-secondary); border: 1px solid #333; height: 650px; max-height: 90vh; }
.member-modal-body { padding: 0 !important; height: calc(100% - 57px); }
.member-modal-body .row { height: 100%; }
.member-modal-carousel { position: relative; background-color: #000; height: 100%; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.member-carousel-image-container { width: 100%; padding-top: 100%; position: relative; }
.member-carousel-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; display: none; }
.member-carousel-image.active { display: block; }
.member-carousel-pagination { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10; }
.pagination-dot { width: 10px; height: 10px; background-color: rgba(255,255,255,0.5); border-radius: 50%; transition: background-color 0.3s; cursor: pointer; }
.pagination-dot.active { background-color: #fff; }

.member-modal-info { padding: 2rem; display: flex; flex-direction: column; height: 100%; }
.member-modal-info .member-name { font-family: 'Xirod', sans-serif; color: var(--vx-red); }
.member-modal-info-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.member-modal-info .member-role { font-family: 'Titillium Web', sans-serif; color: #ccc; margin-bottom: 0; }
.member-modal-info .member-bio { flex-grow: 1; overflow: hidden; font-size: 0.95rem; line-height: 1.7; color: var(--vx-text); text-align: justify; }
.bio-pagination-controls { text-align: right; }
.bio-pagination-controls button { background: none; border: none; color: #888; padding: 5px; cursor: pointer; transition: color 0.2s; font-size: 1.2rem; }
.bio-pagination-controls button:hover:not(:disabled) { color: #fff; }
.bio-pagination-controls button:disabled { opacity: 0.3; cursor: not-allowed; }
.bio-pagination-controls span { margin: 0 10px; font-size: 0.9rem; user-select: none; }

</style>

<main class="about-page">
    <div class="container py-5">
        <section class="about-hero">
            <h1>About Vexillum</h1>
            <p>VX, short for Vexillum, is a premier esports division focused on dominance, discipline, and loyalty. Our mission is to inspire the next generation of champions through integrity, growth, and community.</p>
        </section>

        <section class="container py-5">
            <h2 class="section-title">SENTRO - THE CORE</h2>
            <div class="sentro-grid">
                <?php foreach ($members as $member): ?>
                    <?php $image_path = !empty($member['image']) ? 'assets/uploads/' . htmlspecialchars($member['image']) : 'assets/uploads/default_member.jpg'; ?>
                    <div class="member-card" onclick="openMemberModal(<?= $member['id'] ?>)">
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="member-image">
                        <h5 class="member-name"><?= htmlspecialchars($member['name']) ?></h5>
                        <p class="member-role"><?= htmlspecialchars($member['role']) ?></p>
                        <div class="member-socials">
                            <?php if (!empty($member['facebook'])): ?><a href="<?= htmlspecialchars($member['facebook']) ?>" target="_blank" title="Facebook" onclick="event.stopPropagation()"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if (!empty($member['tiktok'])): ?><a href="<?= htmlspecialchars($member['tiktok']) ?>" target="_blank" title="TikTok" onclick="event.stopPropagation()"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                            <?php if (!empty($member['twitch'])): ?><a href="<?= htmlspecialchars($member['twitch']) ?>" target="_blank" title="Twitch" onclick="event.stopPropagation()"><i class="fab fa-twitch"></i></a><?php endif; ?>
                            <!-- DINAGDAG: Portfolio Link sa Card -->
                            <?php if (!empty($member['portfolio_url'])): ?><a href="<?= htmlspecialchars($member['portfolio_url']) ?>" target="_blank" title="My Portfolio" onclick="event.stopPropagation()"><i class="fas fa-briefcase"></i></a><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<!-- Member Bio Modal -->
<div class="modal fade" id="memberBioModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content member-modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMemberNameHeader"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body member-modal-body">
        <div class="row g-0">
          <div class="col-lg-6">
            <div id="memberModalCarousel" class="member-modal-carousel"></div>
          </div>
          <div class="col-lg-6">
            <div class="member-modal-info h-100">
              <div>
                <h3 id="modalMemberName" class="member-name"></h3>
                <div class="member-modal-info-header">
                    <p id="modalMemberRole" class="member-role"></p>
                    <div id="bioPaginationControls" class="bio-pagination-controls"></div>
                </div>
              </div>
              <div id="modalMemberBio" class="member-bio"></div>
              <div id="modalMemberSocials" class="mt-auto pt-3 border-top border-secondary"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
// ... (Walang pagbabago sa JavaScript)
let memberImages = [];
let currentMemberImageIndex = 0;
let carouselInterval;
let bioPages = [];
let currentBioPageIndex = 0;

async function openMemberModal(memberId) {
    const modal = new bootstrap.Modal(document.getElementById('memberBioModal'));
    
    const formData = new FormData();
    formData.append('action', 'get_xentro_details');
    formData.append('id', memberId);
    const response = await fetch('admin/api_handler.php', { method: 'POST', body: formData });
    const result = await response.json();

    if (result.success) {
        const member = result.data.member;
        
        document.getElementById('modalMemberNameHeader').textContent = member.name;
        document.getElementById('modalMemberName').textContent = member.name;
        document.getElementById('modalMemberRole').textContent = member.role;

        setupBioPagination(member.bio || 'No biography available.');
        updateBioPage();

        const socialsContainer = document.getElementById('modalMemberSocials');
        
        // Prepare an array for all social link buttons
        const socialButtons = [];
        if (member.portfolio_url) socialButtons.push(`<a href="${member.portfolio_url}" target="_blank" class="btn btn-outline-light w-100"><i class="fas fa-briefcase me-2"></i>My Portfolio</a>`);
        if (member.facebook) socialButtons.push(`<a href="${member.facebook}" target="_blank" class="btn btn-outline-light w-100"><i class="fab fa-facebook-f me-2"></i>Facebook</a>`);
        if (member.tiktok) socialButtons.push(`<a href="${member.tiktok}" target="_blank" class="btn btn-outline-light w-100"><i class="fab fa-tiktok me-2"></i>TikTok</a>`);
        if (member.twitch) socialButtons.push(`<a href="${member.twitch}" target="_blank" class="btn btn-outline-light w-100"><i class="fab fa-twitch me-2"></i>Twitch</a>`);
        if (member.kick) socialButtons.push(`<a href="${member.kick}" target="_blank" class="btn btn-outline-light w-100"><i class="fa-brands fa-kickstarter-k me-2"></i>Kick</a>`);
        if (member.email) socialButtons.push(`<a href="mailto:${member.email}" target="_blank" class="btn btn-outline-light w-100"><i class="fas fa-envelope me-2"></i>Email</a>`);

        // Build the grid HTML using Bootstrap's grid system
        let buttonsGridHTML = '<div class="row row-cols-3 g-2">';
        socialButtons.forEach(button => {
            buttonsGridHTML += `<div class="col">${button}</div>`;
        });
        buttonsGridHTML += '</div>';

        socialsContainer.innerHTML = buttonsGridHTML;

        memberImages = [];
        if (member.image) memberImages.push(member.image);
        if (member.portfolio_images) memberImages = memberImages.concat(member.portfolio_images);
        
        currentMemberImageIndex = 0;
        updateMemberCarousel();
        startCarousel();
        
        modal.show();
        document.getElementById('memberBioModal').addEventListener('hidden.bs.modal', stopCarousel, { once: true });
    }
}

function updateMemberCarousel() {
    const carouselContainer = document.getElementById('memberModalCarousel');
    carouselContainer.innerHTML = '';

    if (memberImages.length > 0) {
        const imgContainer = document.createElement('div');
        imgContainer.className = 'member-carousel-image-container';
        const img = document.createElement('img');
        img.src = `assets/uploads/${memberImages[currentMemberImageIndex]}`;
        img.className = 'member-carousel-image active';
        imgContainer.appendChild(img);
        carouselContainer.appendChild(imgContainer);

        if (memberImages.length > 1) {
            const pagination = document.createElement('div');
            pagination.className = 'member-carousel-pagination';
            memberImages.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.className = 'pagination-dot' + (index === currentMemberImageIndex ? ' active' : '');
                dot.onclick = () => {
                    currentMemberImageIndex = index;
                    updateMemberCarousel();
                    resetCarouselInterval();
                };
                pagination.appendChild(dot);
            });
            carouselContainer.appendChild(pagination);
        }
    } else {
        carouselContainer.innerHTML = '<div class="d-flex w-100 h-100 align-items-center justify-content-center"><p class="text-muted">No images</p></div>';
    }
}

function setupBioPagination(fullBio) {
    bioPages = [];
    currentBioPageIndex = 0;
    const charsPerPage = 650; 
    if (fullBio.length <= charsPerPage) {
        bioPages.push(fullBio.replace(/\n/g, '<br>'));
    } else {
        let text = fullBio.replace(/\n/g, '<br>');
        let paragraphs = text.split('<br>').filter(p => p.trim() !== '');
        let currentPage = '';

        for(const p of paragraphs) {
            if((currentPage.length + p.length + 4) > charsPerPage && currentPage.length > 0) {
                bioPages.push(currentPage);
                currentPage = p + '<br>';
            } else {
                currentPage += p + '<br>';
            }
        }
        if (currentPage.trim() !== '') {
            bioPages.push(currentPage);
        }
    }
}

function updateBioPage() {
    document.getElementById('modalMemberBio').innerHTML = bioPages[currentBioPageIndex];
    const controlsContainer = document.getElementById('bioPaginationControls');
    if (bioPages.length > 1) {
        controlsContainer.innerHTML = `
            <button onclick="prevBioPage()" ${currentBioPageIndex === 0 ? 'disabled' : ''}><i class="fas fa-arrow-left"></i></button>
            <span class="mx-2">${currentBioPageIndex + 1} / ${bioPages.length}</span>
            <button onclick="nextBioPage()" ${currentBioPageIndex === bioPages.length - 1 ? 'disabled' : ''}><i class="fas fa-arrow-right"></i></button>
        `;
    } else {
        controlsContainer.innerHTML = '';
    }
}

function nextBioPage() {
    if (currentBioPageIndex < bioPages.length - 1) {
        currentBioPageIndex++;
        updateBioPage();
    }
}

function prevBioPage() {
    if (currentBioPageIndex > 0) {
        currentBioPageIndex--;
        updateBioPage();
    }
}

function nextMemberImage() {
    if (memberImages.length > 1) {
        currentMemberImageIndex = (currentMemberImageIndex + 1) % memberImages.length;
        updateMemberCarousel();
    }
}

function startCarousel() {
    stopCarousel();
    if (memberImages.length > 1) {
        carouselInterval = setInterval(nextMemberImage, 3000);
    }
}

function stopCarousel() {
    clearInterval(carouselInterval);
}

function resetCarouselInterval() {
    stopCarousel();
    startCarousel();
}
</script>
<?php require_once 'includes/footer.php'; ?>

