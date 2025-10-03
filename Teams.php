<?php 
require_once 'includes/header.php';
require_once 'db_connect.php';

// Fetch all active teams
$teams_stmt = $pdo->query("SELECT * FROM teams WHERE status = 'active' ORDER BY team_name ASC");
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all members and group them by team_id for easier access
$members_stmt = $pdo->query("SELECT * FROM members ORDER BY member_name ASC");
$all_members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);
$members_by_team = [];
foreach ($all_members as $member) {
    $members_by_team[$member['team_id']][] = $member;
}
?>

<style>
/* --- BAGONG CSS PARA SA TEAMS PAGE --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0; 
    --vx-text-muted: #888;
}

.teams-page {
    background-color: var(--vx-dark);
    color: var(--vx-text);
}

.teams-hero {
    padding: 140px 1rem 4rem 1rem;
    text-align: center;
    background: linear-gradient(to top, rgba(10,10,10,1), rgba(10,10,10,0.8)), url('https://placehold.co/1920x800/000/ff2a2a?text=VX+TEAMS') no-repeat center center/cover;
    border-bottom: 2px solid var(--vx-red);
}

.teams-hero h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(2.8rem, 8vw, 4rem);
    color: var(--vx-red);
    text-shadow: 0 0 15px var(--vx-red);
}

/* Team Navigation Buttons */
.team-nav {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 3rem;
    padding: 1rem;
}

.team-nav-btn {
    font-family: 'Xirod', sans-serif;
    background-color: transparent;
    border: 2px solid #333;
    color: var(--vx-text-muted);
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.team-nav-btn:hover {
    border-color: var(--vx-red);
    color: var(--vx-red);
}

.team-nav-btn.active {
    background-color: var(--vx-red);
    border-color: var(--vx-red);
    color: #fff;
    box-shadow: 0 0 15px rgba(255, 42, 42, 0.5);
}

/* Team Roster Styling */
.team-roster {
    display: none; /* Hidden by default */
}
.team-roster.active {
    display: block; /* Shown when active */
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.player-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
}

.player-card {
    background-color: var(--vx-dark-secondary);
    border: 1px solid #222;
    border-radius: 8px;
    text-align: center;
    padding: 1.5rem 1rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.player-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.player-card .player-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    margin: 0 auto 1.5rem auto;
    border: 3px solid var(--vx-red);
}

.player-card .player-name {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.player-card .player-role {
    font-family: 'Titillium Web', sans-serif;
    color: #ccc;
    font-size: 0.9rem;
    font-weight: 600;
}
</style>

<main class="teams-page">
    <section class="teams-hero">
        <h1>Valorant Division</h1>
    </section>

    <div class="container py-5">
        <!-- Team Navigation -->
        <?php if (!empty($teams)): ?>
        <nav class="team-nav">
            <?php foreach ($teams as $index => $team): ?>
                <button class="team-nav-btn <?= $index === 0 ? 'active' : '' ?>" data-team-target="#team-<?= htmlspecialchars($team['id']) ?>">
                    <?= htmlspecialchars($team['team_name']) ?>
                </button>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <!-- Team Rosters -->
        <?php if (!empty($teams)): ?>
            <?php foreach ($teams as $index => $team): ?>
                <div class="team-roster <?= $index === 0 ? 'active' : '' ?>" id="team-<?= htmlspecialchars($team['id']) ?>">
                    <div class="player-grid">
                        <?php if (isset($members_by_team[$team['id']])): ?>
                            <?php foreach ($members_by_team[$team['id']] as $member): ?>
                                <div class="player-card">
                                    <?php
                                        // Itatama ang image path para laging tama
                                        $image_path = !empty($member['member_image']) 
                                            ? htmlspecialchars($member['member_image']) 
                                            : 'assets/images/default_player.jpg';
                                    ?>
                                    <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($member['member_name']) ?>" class="player-image">
                                    <h5 class="player-name"><?= htmlspecialchars($member['member_name']) ?></h5>
                                    <p class="player-role"><?= htmlspecialchars($member['member_role'] ?? 'Player') ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted col-12">No members found for this team.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">No teams found in the database.</p>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teamButtons = document.querySelectorAll('.team-nav-btn');
    const teamRosters = document.querySelectorAll('.team-roster');

    teamButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active state from all buttons and rosters
            teamButtons.forEach(btn => btn.classList.remove('active'));
            teamRosters.forEach(roster => roster.classList.remove('active'));

            // Add active state to the clicked button
            button.classList.add('active');

            // Show the target roster
            const targetRoster = document.querySelector(button.dataset.teamTarget);
            if (targetRoster) {
                targetRoster.classList.add('active');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
