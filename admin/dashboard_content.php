<?php
require_once __DIR__ . '/../db_connect.php';

// --- Helper Functions for DB and Spotify ---
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

// Function to get Spotify Data (Copied from vexaura.php)
function getArtistSpotifyData($clientId, $clientSecret, $artistId) {
    $tokenUrl = 'https://accounts.spotify.com/api/token';
    $artistApiUrl = 'https://api.spotify.com/v1/artists/' . $artistId;

    $returnData = [
        'followers' => 'N/A', 
    ];

    $ch = curl_init();
    
    // 1. Get Access Token
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

    // 2. Get Artist Data (Followers)
    curl_setopt($ch, CURLOPT_URL, $artistApiUrl);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader));
    $artistResult = curl_exec($ch);
    $artistData = json_decode($artistResult, true);

    if (isset($artistData['followers']['total'])) {
        $returnData['followers'] = number_format($artistData['followers']['total']);
    }
    
    curl_close($ch);
    return $returnData;
}

// --- Data for VexAura Stat Card (NEW) ---
$main_vexaura_id = getSetting($pdo, 'vexaura_main_spotify_id', '37QynDch02SFAB3ojVl6r2');
// Gamitin ang tamang relative path: '..' para umakyat sa parent directory (root)
if (file_exists('../spotify_config.php')) {
    require_once '../spotify_config.php';
    // Tiyakin na defined ang SPOTIFY_CLIENT_ID bago tawagin ang function
    if (defined('SPOTIFY_CLIENT_ID')) {
        $spotify_data = getArtistSpotifyData(SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET, $main_vexaura_id);
        $vexaura_followers = $spotify_data['followers'];
    } else {
         $vexaura_followers = 'Config Error';
    }
} else {
    $vexaura_followers = 'N/A (No Config)';
}

// --- Data for Team Wins Chart ---
$team_wins_stmt = $pdo->query("SELECT team_name, wins FROM teams WHERE status = 'active' ORDER BY wins DESC LIMIT 5");
$team_wins_data = $team_wins_stmt->fetchAll(PDO::FETCH_ASSOC);
$team_labels = json_encode(array_column($team_wins_data, 'team_name'));
$team_wins = json_encode(array_column($team_wins_data, 'wins'));

// --- Data for Upcoming Matches & Calendar ---
$matches_query = $pdo->query("SELECT opponent, date, time FROM matches WHERE date >= CURDATE() ORDER BY date ASC, time ASC");
$all_upcoming_matches = $matches_query->fetchAll(PDO::FETCH_ASSOC);
$match_dates_for_calendar = json_encode(array_unique(array_column($all_upcoming_matches, 'date')));
$upcoming_matches_for_table = array_slice($all_upcoming_matches, 0, 5); // Limit for table display

// --- Stat Cards Data ---
$total_creators = $pdo->query("SELECT COUNT(id) FROM creators")->fetchColumn();
$active_teams = $pdo->query("SELECT COUNT(id) FROM teams WHERE status = 'active'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(id) FROM store_orders WHERE status = 'pending'")->fetchColumn();
$new_inquiries = $pdo->query("SELECT COUNT(id) FROM inquiries WHERE status = 'open'")->fetchColumn();
?>

<style>
    /* Calendar Highlight */
    .calendar-day.today {
        background-color: #dc2626; /* Tailwind red-600 */
        color: white !important;
        font-weight: bold;
        border-radius: 9999px; /* circle */
    }

    /* Highlight upcoming match if today */
    .upcoming-today {
        background-color: #7f1d1d; /* darker red bg */
        color: #fff;
    }
</style>

<!-- Stat Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
    <div class="bg-zinc-800 p-5 rounded-lg flex items-center shadow-lg transition-transform hover:scale-105">
        <i class="fas fa-music text-3xl text-green-500"></i>
        <div class="ml-4">
            <p class="text-sm text-gray-300">VexAura Followers</p>
            <p class="text-2xl font-bold"><?php echo $vexaura_followers; ?></p>
        </div>
    </div>
    <div class="bg-zinc-800 p-5 rounded-lg flex items-center shadow-lg transition-transform hover:scale-105">
        <i class="fas fa-users text-3xl text-red-500"></i>
        <div class="ml-4">
            <p class="text-sm text-gray-300">Total Creators</p>
            <p class="text-2xl font-bold"><?php echo $total_creators; ?></p>
        </div>
    </div>
    <div class="bg-zinc-800 p-5 rounded-lg flex items-center shadow-lg transition-transform hover:scale-105">
        <i class="fas fa-shield-alt text-3xl text-red-500"></i>
        <div class="ml-4">
            <p class="text-sm text-gray-300">Active Teams</p>
            <p class="text-2xl font-bold"><?php echo $active_teams; ?></p>
        </div>
    </div>
    <div class="bg-zinc-800 p-5 rounded-lg flex items-center shadow-lg transition-transform hover:scale-105">
        <i class="fas fa-shopping-cart text-3xl text-red-500"></i>
        <div class="ml-4">
            <p class="text-sm text-gray-300">Pending Orders</p>
            <p class="text-2xl font-bold"><?php echo $pending_orders; ?></p>
        </div>
    </div>
    <div class="bg-zinc-800 p-5 rounded-lg flex items-center shadow-lg transition-transform hover:scale-105">
        <i class="fas fa-envelope-open-text text-3xl text-red-500"></i>
        <div class="ml-4">
            <p class="text-sm text-gray-300">New Inquiries</p>
            <p class="text-2xl font-bold"><?php echo $new_inquiries; ?></p>
        </div>
    </div>
</div>

<!-- Calendar and Matches Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 bg-zinc-800 p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-bold mb-4">Calendar</h3>
        <div id="interactive-calendar">
            <div class="flex justify-between items-center mb-4">
                <button id="prev-month" class="px-3 py-1 bg-zinc-700 rounded">&lt;</button>
                <h4 id="month-year" class="text-lg font-semibold"></h4>
                <button id="next-month" class="px-3 py-1 bg-zinc-700 rounded">&gt;</button>
            </div>
            <div class="grid grid-cols-7 gap-2 text-center text-xs text-gray-400 mb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div id="calendar-days" class="grid grid-cols-7 gap-2"></div>
        </div>
    </div>
    <div class="bg-zinc-800 p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-bold mb-4">Upcoming Matches</h3>
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-zinc-700">
                    <?php if (empty($upcoming_matches_for_table)): ?>
                        <tr><td class="py-3 text-center text-gray-400">No upcoming matches.</td></tr>
                    <?php else: ?>
                        <?php foreach($upcoming_matches_for_table as $match): ?>
                        <?php $isToday = (date('Y-m-d') === $match['date']) ? 'upcoming-today' : ''; ?>
                        <tr class="table-row-hover <?php echo $isToday; ?>">
                            <td class="py-3">
                                <p class="font-semibold">VX vs <?php echo htmlspecialchars($match['opponent']); ?></p>
                                <p class="text-xs">
                                    <?php echo date('M d, Y', strtotime($match['date'])) . ' at ' . date('h:i A', strtotime($match['time'])); ?>
                                </p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Team Wins Chart -->
<div class="bg-zinc-800 p-6 rounded-lg shadow-lg mt-6">
    <h3 class="text-xl font-bold mb-4">Top 5 Team Wins</h3>
    <div style="height: 300px;">
        <canvas id="teamWinsChart"></canvas>
    </div>
</div>

<script>
(function() {
    // Team Wins Chart
    const ctxWins = document.getElementById('teamWinsChart');
    if (ctxWins) {
        new Chart(ctxWins.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo $team_labels; ?>,
                datasets: [{
                    label: 'Wins',
                    data: <?php echo $team_wins; ?>,
                    backgroundColor: 'rgba(225, 29, 72, 0.6)',
                    borderColor: 'rgba(225, 29, 72, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#d4d4d8' } },
                    x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#d4d4d8' } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Interactive Calendar
    const calendarDays = document.getElementById('calendar-days');
    const monthYear = document.getElementById('month-year');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const matchDates = <?php echo $match_dates_for_calendar; ?>;
    
    if(calendarDays){
        let date = new Date();
        function renderCalendar() {
            date.setDate(1);
            const firstDayIndex = date.getDay();
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            const prevLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate();
            const lastDayIndex = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDay();
            const nextDays = 7 - lastDayIndex - 1;

            const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
            monthYear.innerText = `${months[date.getMonth()]} ${date.getFullYear()}`;
            
            let days = "";
            for (let x = firstDayIndex; x > 0; x--) {
                days += `<div class="p-2 text-center text-gray-500">${prevLastDay - x + 1}</div>`;
            }

            for (let i = 1; i <= lastDay; i++) {
                let classList = 'p-2 text-center calendar-day';
                const currentYear = date.getFullYear();
                const currentMonth = String(date.getMonth() + 1).padStart(2, '0');
                const currentDay = String(i).padStart(2, '0');
                const currentDateString = `${currentYear}-${currentMonth}-${currentDay}`;

                if (i === new Date().getDate() && date.getMonth() === new Date().getMonth() && date.getFullYear() === new Date().getFullYear()) {
                    classList += ' today';
                }
                if (matchDates.includes(currentDateString)) {
                     classList += ' match-day';
                }
                days += `<div class="${classList}">${i}</div>`;
            }

            for (let j = 1; j <= nextDays; j++) {
                days += `<div class="p-2 text-center text-gray-500">${j}</div>`;
            }
            calendarDays.innerHTML = days;
        }
        prevMonthBtn.addEventListener('click', () => {
            date.setMonth(date.getMonth() - 1);
            renderCalendar();
        });
        nextMonthBtn.addEventListener('click', () => {
            date.setMonth(date.getMonth() + 1);
            renderCalendar();
        });
        renderCalendar();
    }
})();
</script>
