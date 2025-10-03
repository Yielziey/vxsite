<?php
require_once __DIR__ . '/../db_connect.php';
?>
<div class="content-area p-0">
    <!-- News Management Section -->
    <div class="mb-8">
        <div class="mb-4 border-b-2 border-red-600 pb-2 flex justify-between items-center">
            <h2 class="text-3xl font-bold text-white">News Management</h2>
            <button onclick="openAddNewsModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add New Article
            </button>
        </div>
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full rounded-lg overflow-hidden">
                <thead class="table-header">
                    <tr>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-center">Featured</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php
                    $news_items = $pdo->query("SELECT id, title, DATE_FORMAT(date, '%b %d, %Y %h:%i %p') as formatted_date, featured FROM news ORDER BY date DESC")->fetchAll();
                    if ($news_items):
                        foreach ($news_items as $item): ?>
                            <tr class='border-b border-zinc-600 table-row-hover'>
                                <td class='p-3'><?php echo htmlspecialchars($item['title']); ?></td>
                                <td class='p-3'><?php echo $item['formatted_date']; ?></td>
                                <td class='p-3 text-center'><?php echo $item['featured'] ? '<i class="fas fa-star text-yellow-400"></i>' : '-'; ?></td>
                                <td class='p-3 text-center space-x-2'>
                                    <button onclick='openEditNewsModal(<?php echo $item['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'><i class='fas fa-edit'></i></button>
                                    <button onclick='deleteItem(<?php echo $item['id']; ?>, "delete_news", "news article")' class='text-red-500 hover:text-red-400' title='Delete'><i class='fas fa-trash'></i></button>
                                </td>
                            </tr>
                    <?php endforeach;
                    else: ?>
                        <tr><td colspan="4" class="text-center p-4">No news articles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upcoming Matches Section (No changes here) -->
    <div>
        <div class="mb-4 border-b-2 border-red-600 pb-2 flex justify-between items-center">
            <h2 class="text-3xl font-bold text-white">Upcoming Matches</h2>
            <button onclick="openAddMatchModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add Match
            </button>
        </div>
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full rounded-lg overflow-hidden">
                <thead class="table-header">
                    <tr>
                        <th class="p-3 text-left">Date & Time</th>
                        <th class="p-3 text-left">Opponent</th>
                        <th class="p-3 text-left">Platform</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                     <?php
                    $matches = $pdo->query("SELECT id, opponent, platform, DATE_FORMAT(date, '%b %d, %Y') as match_date, TIME_FORMAT(time, '%h:%i %p') as match_time FROM matches ORDER BY date DESC, time DESC")->fetchAll();
                    if ($matches):
                        foreach ($matches as $match): ?>
                            <tr class='border-b border-zinc-600 table-row-hover'>
                                <td class='p-3'><?php echo $match['match_date'] . ' at ' . $match['match_time']; ?></td>
                                <td class='p-3'><?php echo htmlspecialchars($match['opponent']); ?></td>
                                <td class='p-3'><?php echo htmlspecialchars($match['platform']); ?></td>
                                <td class='p-3 text-center space-x-2'>
                                    <button onclick='openEditMatchModal(<?php echo $match['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'><i class='fas fa-edit'></i></button>
                                    <button onclick='deleteItem(<?php echo $match['id']; ?>, "delete_match", "match")' class='text-red-500 hover:text-red-400' title='Delete'><i class='fas fa-trash'></i></button>
                                </td>
                            </tr>
                    <?php endforeach;
                    else: ?>
                        <tr><td colspan="4" class="text-center p-4">No upcoming matches found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Functions for News
function openAddNewsModal() {
    modalTitle.innerText = 'Add New Article';
    modalBody.innerHTML = `
        <form id="newsForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_news">
            <label class="block mb-1">Title</label>
            <input type="text" name="title" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="block mb-1">Content</label>
            <textarea name="content" rows="5" class="w-full bg-zinc-700 p-2 rounded mb-4"></textarea>
            
            <label class="block mb-1 font-semibold">Main Article Image</label>
            <input type="file" name="image" accept="image/jpeg, image/png, image/gif" class="w-full bg-zinc-700 p-2 rounded mb-4">

            <!-- Field for multiple additional images -->
            <label class="block mb-1 font-semibold">Additional Images (Optional)</label>
            <input type="file" name="additional_images[]" accept="image/jpeg, image/png, image/gif" class="w-full bg-zinc-700 p-2 rounded mb-4" multiple>
            
            <label class="block mb-1">Date & Time</label>
            <input type="datetime-local" name="date" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="flex items-center"><input type="checkbox" name="featured" class="mr-2 bg-zinc-700">Featured Article</label>
        </form>
    `;
    modalFooter.innerHTML = `
        <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
        <button type="submit" form="newsForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Save Article</button>
    `;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function openEditNewsModal(id) {
    modalTitle.innerText = 'Edit Article';
    modalBody.innerHTML = '<p>Loading article data...</p>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const formData = new FormData();
    formData.append('action', 'get_news_details');
    formData.append('id', id);
    const response = await fetch('api_handler.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) {
        const news = result.data.news;
        
        let imagePreviews = '<p class="mb-2 font-semibold">Current Images:</p>';
        if (news.all_images && news.all_images.length > 0) {
            imagePreviews += '<div class="flex flex-wrap gap-4 mb-4">';
            news.all_images.forEach(img => {
                imagePreviews += '<img src="uploads/news/' + img + '" class="w-24 h-24 object-cover rounded">';
            });
            imagePreviews += '</div>';
        } else {
            imagePreviews = '<p>No images uploaded.</p>';
        }

        modalBody.innerHTML = `
            <form id="newsForm" onsubmit="handleFormSubmit(event)">
                <input type="hidden" name="action" value="edit_news">
                <input type="hidden" name="id" value="${news.id}">
                <label class="block mb-1">Title</label>
                <input type="text" name="title" class="w-full bg-zinc-700 p-2 rounded mb-4" value="${news.title}" required>
                <label class="block mb-1">Content</label>
                <textarea name="content" rows="5" class="w-full bg-zinc-700 p-2 rounded mb-4">${news.content}</textarea>
                
                ${imagePreviews}
                
                <label class="block mb-1 font-semibold">Upload New Main Image (Optional)</label>
                <p class="text-xs text-zinc-400 mb-2">Uploading a new main image will replace the first image shown above.</p>
                <input type="file" name="image" accept="image/jpeg, image/png, image/gif" class="w-full bg-zinc-700 p-2 rounded mb-4">
                
                <label class="block mb-1 font-semibold">Add More Images (Optional)</label>
                <input type="file" name="additional_images[]" accept="image/jpeg, image/png, image/gif" class="w-full bg-zinc-700 p-2 rounded mb-4" multiple>

                <label class="block mb-1">Date & Time</label>
                <input type="datetime-local" name="date" class="w-full bg-zinc-700 p-2 rounded mb-4" value="${news.date}" required>
                <label class="flex items-center"><input type="checkbox" name="featured" class="mr-2 bg-zinc-700" ${news.featured == 1 ? 'checked' : ''}>Featured Article</label>
            </form>
        `;
        modalFooter.innerHTML = `
            <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
            <button type="submit" form="newsForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Update Article</button>
        `;
    } else {
        modalBody.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
    }
}

// Functions for Matches (No changes here)
function openAddMatchModal() {
    modalTitle.innerText = 'Add New Match';
    modalBody.innerHTML = `
        <form id="matchForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_match">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block mb-1">Date</label><input type="date" name="date" class="w-full bg-zinc-700 p-2 rounded" required></div>
                <div><label class="block mb-1">Time</label><input type="time" name="time" class="w-full bg-zinc-700 p-2 rounded" required></div>
            </div>
            <div class="mt-4"><label class="block mb-1">Opponent</label><input type="text" name="opponent" class="w-full bg-zinc-700 p-2 rounded" required></div>
            <div class="mt-4"><label class="block mb-1">Platform</label><input type="text" name="platform" class="w-full bg-zinc-700 p-2 rounded" placeholder="e.g., Twitch, YouTube"></div>
            <div class="mt-4"><label class="block mb-1">Watch Link</label><input type="url" name="watch_link" class="w-full bg-zinc-700 p-2 rounded" placeholder="https://..."></div>
        </form>
    `;
    modalFooter.innerHTML = `
        <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
        <button type="submit" form="matchForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Save Match</button>
    `;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function openEditMatchModal(id) {
    modalTitle.innerText = 'Edit Match';
    modalBody.innerHTML = '<p>Loading match data...</p>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const formData = new FormData();
    formData.append('action', 'get_match_details');
    formData.append('id', id);
    const response = await fetch('api_handler.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) {
        const match = result.data.match;
        modalBody.innerHTML = `
            <form id="matchForm" onsubmit="handleFormSubmit(event)">
                <input type="hidden" name="action" value="edit_match">
                <input type="hidden" name="id" value="${match.id}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block mb-1">Date</label><input type="date" name="date" value="${match.date}" class="w-full bg-zinc-700 p-2 rounded" required></div>
                    <div><label class="block mb-1">Time</label><input type="time" name="time" value="${match.time}" class="w-full bg-zinc-700 p-2 rounded" required></div>
                </div>
                <div class="mt-4"><label class="block mb-1">Opponent</label><input type="text" name="opponent" value="${match.opponent}" class="w-full bg-zinc-700 p-2 rounded" required></div>
                <div class="mt-4"><label class="block mb-1">Platform</label><input type="text" name="platform" value="${match.platform}" class="w-full bg-zinc-700 p-2 rounded"></div>
                <div class="mt-4"><label class="block mb-1">Watch Link</label><input type="url" name="watch_link" value="${match.watch_link || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
            </form>
        `;
        modalFooter.innerHTML = `
            <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
            <button type="submit" form="matchForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Update Match</button>
        `;
    } else {
        modalBody.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
    }
}
</script>

