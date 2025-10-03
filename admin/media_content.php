<?php
// FILE: media_content.php (Redesigned for Podcast)
$subpage = $_GET['subpage'] ?? 'podcast';
$valid_subpages = ['podcast', 'vexaura'];

if (!in_array($subpage, $valid_subpages)) {
    $subpage = 'podcast';
}

$active_podcast = ($subpage === 'podcast') ? 'active' : '';
$active_vexaura = ($subpage === 'vexaura') ? 'active' : '';

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

// --- Initialize all variables to prevent errors ---
// Podcast Data
$podcast_title = '';
$podcast_embed = '';
$podcast_platforms = [];
$podcast_topics = [];
// VexAura Data
$vexaura_artist_id = '';
$vexaura_apple_url = '';
$vexaura_youtube_url = '';
$vexaura_vex_platform_url = '';
$featured_artists = [];


// --- Fetch data based on the active tab ---
if ($subpage === 'podcast') {
    // Fetch Podcast Data
    $podcast_title = getSetting($pdo, 'podcast_latest_title', '');
    $podcast_embed = getSetting($pdo, 'podcast_latest_embed_src', '');

    try {
        $platforms_stmt = $pdo->query("SELECT id, platform_name, icon_class, url FROM podcast_platforms ORDER BY sort_order ASC");
        $podcast_platforms = $platforms_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching podcast platforms: " . $e->getMessage());
    }

    try {
        $topics_stmt = $pdo->query("SELECT id, topic_title, guests, planned_date FROM podcast_topics ORDER BY planned_date DESC");
        $podcast_topics = $topics_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching podcast topics: " . $e->getMessage());
    }

} elseif ($subpage === 'vexaura') {
    // Fetch VexAura Data
    $vexaura_artist_id = getSetting($pdo, 'vexaura_main_spotify_id', '');
    $vexaura_apple_url = getSetting($pdo, 'vexaura_main_apple_music_url', '');
    $vexaura_youtube_url = getSetting($pdo, 'vexaura_main_youtube_music_url', '');
    $vexaura_vex_platform_url = getSetting($pdo, 'vexaura_main_vex_platform_url', '');

    try {
        $featured_artists_stmt = $pdo->query("SELECT id, artist_name, spotify_id, apple_music_url, youtube_music_url FROM featured_artists ORDER BY sort_order");
        $featured_artists = $featured_artists_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching featured artists in admin: " . $e->getMessage());
    }
}
?>

<style>
    /* Custom styles for podcast admin to match the frontend */
    :root {
        --podcast-violet: #ff0000ff;
        --podcast-violet-dark: #ff0000ff;
    }
    .podcast-admin-header {
        font-family: 'Orbitron', sans-serif;
        color: white;
        text-shadow: 0 0 10px var(--podcast-violet);
    }
    .podcast-card {
        background-color: #1a1a1a;
        border: 1px solid #333;
        border-left: 4px solid var(--podcast-violet);
    }
    .podcast-btn {
        background-color: var(--podcast-violet);
        color: white;
    }
    .podcast-btn:hover {
        background-color: var(--podcast-violet-dark);
    }
    .podcast-table th {
        background-color: #2a2a2a;
    }
    .sortable-ghost {
        opacity: 0.4;
        background: #2a2a2a;
    }
</style>

<h1 class="text-3xl font-bold mb-6 text-white">Media Hub Management</h1>

<!-- Tabs Navigation -->
<div class="mb-6 border-b border-gray-700">
    <nav class="flex space-x-4">
        <a href="?page=media&subpage=podcast" class="tab-button <?php echo $active_podcast; ?>">
            <i class="fas fa-podcast mr-2"></i>VX Podcast
        </a>
        <a href="?page=media&subpage=vexaura" class="tab-button <?php echo $active_vexaura; ?>">
            <i class="fas fa-music mr-2"></i>Vexaura (Music/Artists)
        </a>
    </nav>
</div>

<!-- Tab Content -->
<div>
    <?php if ($subpage === 'podcast'): ?>
        <!-- ======================================================= -->
        <!--                NEW PODCAST ADMIN DESIGN                 -->
        <!-- ======================================================= -->
        <div class="space-y-8">
            <!-- Latest Episode -->
            <div class="podcast-card p-6 rounded-lg">
                <h2 class="text-2xl mb-4 podcast-admin-header">Latest Episode</h2>
                <form id="podcast-form" onsubmit="handleFormSubmit(event)">
                    <input type="hidden" name="action" value="update_podcast_details">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="latest_episode_title" class="block mb-2 text-sm font-medium text-white">Episode Title</label>
                            <input type="text" id="latest_episode_title" name="latest_episode_title" value="<?php echo htmlspecialchars($podcast_title); ?>" class="w-full p-2 bg-zinc-800 border border-zinc-600 rounded" placeholder="e.g., The Future of PH Esports">
                        </div>
                        <div>
                            <label for="latest_episode_embed" class="block mb-2 text-sm font-medium text-white">Spotify/YouTube Embed URL</label>
                            <input type="url" id="latest_episode_embed" name="latest_episode_embed" value="<?php echo htmlspecialchars($podcast_embed); ?>" class="w-full p-2 bg-zinc-800 border border-zinc-600 rounded" placeholder="https://open.spotify.com/embed/episode/...">
                        </div>
                    </div>
                    <div class="text-right mt-4">
                        <button type="submit" class="px-5 py-2 podcast-btn rounded hover:bg-purple-700 transition font-semibold">Save Episode Details</button>
                    </div>
                </form>
            </div>

            <!-- Podcast Platforms -->
            <div class="podcast-card p-6 rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl podcast-admin-header">Podcast Platforms</h2>
                    <button type="button" onclick="showAddPlatformModal()" class="px-4 py-2 podcast-btn rounded hover:bg-purple-700 transition font-semibold"><i class="fas fa-plus mr-2"></i> Add Platform</button>
                </div>
                 <p class="text-sm text-zinc-400 mb-4">Drag and drop the rows to reorder how they appear on the website.</p>
                <table class="min-w-full podcast-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider w-12">Order</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Platform</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">URL</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="platforms-table-body" class="bg-zinc-800 divide-y divide-gray-700">
                        <?php foreach ($podcast_platforms as $platform): ?>
                            <tr data-id="<?php echo $platform['id']; ?>">
                                <td class="px-4 py-4 text-sm text-zinc-400 cursor-grab"><i class="fas fa-grip-vertical"></i></td>
                                <td class="px-4 py-4 text-sm text-white"><i class="<?php echo htmlspecialchars($platform['icon_class']); ?> mr-3"></i><?php echo htmlspecialchars($platform['platform_name']); ?></td>
                                <td class="px-4 py-4 text-sm text-zinc-400 truncate max-w-xs"><a href="<?php echo htmlspecialchars($platform['url']); ?>" target="_blank"><?php echo htmlspecialchars($platform['url']); ?></a></td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <button onclick="showEditPlatformModal(<?php echo $platform['id']; ?>, '<?php echo htmlspecialchars(addslashes($platform['platform_name'])); ?>', '<?php echo htmlspecialchars(addslashes($platform['icon_class'])); ?>', '<?php echo htmlspecialchars(addslashes($platform['url'])); ?>')" class="text-purple-400 hover:text-purple-300 mr-3"><i class="fas fa-edit"></i> Edit</button>
                                    <button onclick="deleteItem(<?php echo $platform['id']; ?>, 'delete_podcast_platform', 'Podcast Platform')" class="text-zinc-400 hover:text-red-500"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Upcoming Topics -->
            <div class="podcast-card p-6 rounded-lg">
                <div class="flex justify-between items-center mb-4">
                     <h2 class="text-2xl podcast-admin-header">Upcoming Topics</h2>
                     <button type="button" onclick="showAddTopicModal()" class="px-4 py-2 podcast-btn rounded hover:bg-purple-700 transition font-semibold"><i class="fas fa-plus mr-2"></i> Add Topic</button>
                </div>
                 <table class="min-w-full podcast-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Topic</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Guests</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-zinc-800 divide-y divide-gray-700">
                        <?php foreach ($podcast_topics as $topic): ?>
                            <tr>
                                <td class="px-4 py-4 text-sm text-purple-400"><?php echo date('M d, Y', strtotime($topic['planned_date'])); ?></td>
                                <td class="px-4 py-4 text-sm text-white"><?php echo htmlspecialchars($topic['topic_title']); ?></td>
                                <td class="px-4 py-4 text-sm text-zinc-400"><?php echo htmlspecialchars($topic['guests'] ?: 'N/A'); ?></td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <button onclick="showEditTopicModal(<?php echo $topic['id']; ?>, '<?php echo htmlspecialchars(addslashes($topic['topic_title'])); ?>', '<?php echo htmlspecialchars(addslashes($topic['guests'])); ?>', '<?php echo htmlspecialchars($topic['planned_date']); ?>')" class="text-purple-400 hover:text-purple-300 mr-3"><i class="fas fa-edit"></i> Edit</button>
                                    <button onclick="deleteItem(<?php echo $topic['id']; ?>, 'delete_podcast_topic', 'Podcast Topic')" class="text-zinc-400 hover:text-red-500"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($subpage === 'vexaura'): ?>
        <!-- Vexaura Management -->
        <h2 class="text-xl font-semibold text-red-500 mb-4">Vexaura (Music/Artists) Management</h2>
        <div class="mt-4 p-4 border border-gray-700 rounded-md">
            <h3 class="text-lg font-semibold text-white mb-3">Main VexAura Artist</h3>
            <form id="vexaura-main-form" onsubmit="handleFormSubmit(event)">
                <input type="hidden" name="action" value="update_vexaura_main">
                
                <label for="vexaura_id" class="block mb-2 text-sm font-medium text-white">VexAura Spotify Artist ID</label>
                <input type="text" id="vexaura_id" name="vexaura_id" value="<?php echo htmlspecialchars($vexaura_artist_id); ?>" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
                
                <label for="vexaura_apple_url" class="block mb-2 text-sm font-medium text-white">Apple Music URL (Optional)</label>
                <input type="url" id="vexaura_apple_url" name="apple_music_url" value="<?php echo htmlspecialchars($vexaura_apple_url); ?>" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">

                <label for="vexaura_youtube_url" class="block mb-2 text-sm font-medium text-white">YouTube Music URL (Optional)</label>
                <input type="url" id="vexaura_youtube_url" name="youtube_music_url" value="<?php echo htmlspecialchars($vexaura_youtube_url); ?>" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
                
                <label for="vexaura_vex_platform_url" class="block mb-2 text-sm font-medium text-white">VexAura Music Platform URL (Optional)</label>
                <input type="url" id="vexaura_vex_platform_url" name="vex_platform_url" value="<?php echo htmlspecialchars($vexaura_vex_platform_url); ?>" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
                
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Update Main VexAura Details</button>
            </form>
        </div>

        <!-- Featured Artists List (CRUD) -->
        <div class="mt-6 p-4 border border-gray-700 rounded-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">Featured Artists List</h3>
                <button type="button" onclick="showAddArtistModal()" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition"><i class="fas fa-plus mr-1"></i> Add Artist</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-zinc-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Spotify ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Other Links</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-zinc-800 divide-y divide-gray-700">
                        <?php foreach ($featured_artists as $artist): ?>
                            <tr class="table-row-hover">
                                <td class="px-4 py-4 text-sm text-white"><?php echo htmlspecialchars($artist['artist_name']); ?></td>
                                <td class="px-4 py-4 text-sm text-zinc-400"><?php echo htmlspecialchars($artist['spotify_id']); ?></td>
                                <td class="px-4 py-4 text-sm text-zinc-400">
                                    <?php if (!empty($artist['apple_music_url'])): ?><i class="fab fa-apple text-pink-400 mr-2" title="Has Apple Music link"></i><?php endif; ?>
                                    <?php if (!empty($artist['youtube_music_url'])): ?><i class="fab fa-youtube text-red-500" title="Has YouTube Music link"></i><?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <button onclick="showEditArtistModal(
                                        <?php echo $artist['id']; ?>, 
                                        '<?php echo htmlspecialchars(addslashes($artist['artist_name'])); ?>', 
                                        '<?php echo htmlspecialchars(addslashes($artist['spotify_id'])); ?>',
                                        '<?php echo htmlspecialchars(addslashes($artist['apple_music_url'])); ?>',
                                        '<?php echo htmlspecialchars(addslashes($artist['youtube_music_url'])); ?>'
                                    )" class="text-red-500 hover:text-red-700 mr-2"><i class="fas fa-edit"></i> Edit</button>
                                    <button onclick="deleteItem(<?php echo $artist['id']; ?>, 'delete_featured_artist', 'Featured Artist')" class="text-zinc-400 hover:text-red-600"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- SortableJS for drag-and-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
// Initialize Sortable
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('platforms-table-body');
    if(el) {
        const sortable = new Sortable(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                const order = Array.from(el.children).map(row => row.dataset.id);
                
                const formData = new FormData();
                formData.append('action', 'update_platform_order');
                formData.append('order', JSON.stringify(order));

                fetch('form_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred while saving the order.', 'error');
                    console.error('Error:', error);
                });
            }
        });
    }
});


// MODAL FUNCTIONS FOR PLATFORMS
function showAddPlatformModal() {
    modalTitle.textContent = 'Add New Podcast Platform';
    modalBody.innerHTML = `
        <form id="add-platform-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_podcast_platform">
            <label class="block mb-2 text-sm font-medium text-white">Platform Name</label>
            <input type="text" name="platform_name" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" placeholder="e.g., Spotify" required>
            <label class="block mb-2 text-sm font-medium text-white">Font Awesome Icon Class</label>
            <input type="text" name="icon_class" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" placeholder="e.g., fab fa-spotify" required>
            <label class="block mb-2 text-sm font-medium text-white">URL</label>
            <input type="url" name="url" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" placeholder="https://..." required>
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 podcast-btn text-white rounded transition">Add Platform</button>
            </div>
        </form>
    `;
    openModal('md');
}

function showEditPlatformModal(id, name, icon, url) {
    modalTitle.textContent = 'Edit Podcast Platform';
    modalBody.innerHTML = `
        <form id="edit-platform-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="update_podcast_platform">
            <input type="hidden" name="platform_id" value="${id}">
            <label class="block mb-2 text-sm font-medium text-white">Platform Name</label>
            <input type="text" name="platform_name" value="${name}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label class="block mb-2 text-sm font-medium text-white">Font Awesome Icon Class</label>
            <input type="text" name="icon_class" value="${icon}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label class="block mb-2 text-sm font-medium text-white">URL</label>
            <input type="url" name="url" value="${url}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 podcast-btn text-white rounded transition">Save Changes</button>
            </div>
        </form>
    `;
    openModal('md');
}


// MODAL FUNCTIONS FOR TOPICS (Existing, no changes)
function showAddTopicModal() {
    modalTitle.textContent = 'Add Upcoming Topic';
    modalBody.innerHTML = `
        <form id="add-topic-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_podcast_topic">
            <label class="block mb-2 text-sm font-medium text-white">Topic Title</label>
            <input type="text" name="topic_title" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label class="block mb-2 text-sm font-medium text-white">Planned Guests (Optional)</label>
            <input type="text" name="topic_guests" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <label class="block mb-2 text-sm font-medium text-white">Planned Date</label>
            <input type="date" name="topic_date" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 podcast-btn text-white rounded transition">Add Topic</button>
            </div>
        </form>
    `;
    openModal('md');
}

function showEditTopicModal(id, title, guests, date) {
    modalTitle.textContent = 'Edit Upcoming Topic';
    modalBody.innerHTML = `
        <form id="edit-topic-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="update_podcast_topic">
            <input type="hidden" name="topic_id" value="${id}">
            <label class="block mb-2 text-sm font-medium text-white">Topic Title</label>
            <input type="text" name="topic_title" value="${title}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label class="block mb-2 text-sm font-medium text-white">Planned Guests (Optional)</label>
            <input type="text" name="topic_guests" value="${guests}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <label class="block mb-2 text-sm font-medium text-white">Planned Date</label>
            <input type="date" name="topic_date" value="${date}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 podcast-btn text-white rounded transition">Save Changes</button>
            </div>
        </form>
    `;
    openModal('md');
}

function showAddArtistModal() {
    modalTitle.textContent = 'Add New Featured Artist';
    modalBody.innerHTML = `
        <form id="add-artist-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_featured_artist">
            <label for="new_artist_name" class="block mb-2 text-sm font-medium text-white">Artist Name</label>
            <input type="text" id="new_artist_name" name="new_artist_name" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label for="new_spotify_id" class="block mb-2 text-sm font-medium text-white">Spotify Artist ID</label>
            <input type="text" id="new_spotify_id" name="new_spotify_id" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label for="new_apple_music_url" class="block mb-2 text-sm font-medium text-white">Apple Music URL (Optional)</label>
            <input type="url" id="new_apple_music_url" name="apple_music_url" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <label for="new_youtube_music_url" class="block mb-2 text-sm font-medium text-white">YouTube Music URL (Optional)</label>
            <input type="url" id="new_youtube_music_url" name="youtube_music_url" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Add Artist</button>
            </div>
        </form>
    `;
    openModal('md');
}

function showEditArtistModal(id, name, spotifyId, appleUrl, youtubeUrl) {
    modalTitle.textContent = 'Edit Featured Artist';
    modalBody.innerHTML = `
        <form id="edit-artist-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="update_featured_artist">
            <input type="hidden" name="artist_id" value="${id}">
            <label for="edit_artist_name" class="block mb-2 text-sm font-medium text-white">Artist Name</label>
            <input type="text" id="edit_artist_name" name="artist_name" value="${name}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label for="edit_spotify_id" class="block mb-2 text-sm font-medium text-white">Spotify Artist ID</label>
            <input type="text" id="edit_spotify_id" name="spotify_id" value="${spotifyId}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded" required>
            <label for="edit_apple_music_url" class="block mb-2 text-sm font-medium text-white">Apple Music URL (Optional)</label>
            <input type="url" id="edit_apple_music_url" name="apple_music_url" value="${appleUrl}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <label for="edit_youtube_music_url" class="block mb-2 text-sm font-medium text-white">YouTube Music URL (Optional)</label>
            <input type="url" id="edit_youtube_music_url" name="youtube_music_url" value="${youtubeUrl}" class="w-full p-2 mb-4 bg-zinc-700 border border-zinc-600 rounded">
            <div class="text-right">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-zinc-600 text-white rounded hover:bg-zinc-700 transition mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Save Changes</button>
            </div>
        </form>
    `;
    openModal('md');
}
</script>

