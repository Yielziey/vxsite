<?php require_once __DIR__ . '/../db_connect.php'; ?>
<div id="creators-content">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-2xl font-semibold text-white">Content Creators</h3>
        <button onclick="openAddCreatorModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add New Creator
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="table-header">
                <tr>
                    <th class="p-3 text-left">Image</th>
                    <th class="p-3 text-left">Creator Name</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body">
            <?php foreach ($pdo->query("SELECT * FROM creators ORDER BY id") as $creator): 
                $pfp = $creator['profile_picture'] ? '../' . $creator['profile_picture'] : '../assets/images/default.jpg';
            ?>
                <tr class='border-b border-zinc-600 table-row-hover'>
                    <td class='p-3'><img src="<?php echo $pfp; ?>" class="w-12 h-12 rounded-full object-cover"></td>
                    <td class='p-3'><?php echo htmlspecialchars($creator['creator_name']); ?></td>
                    <td class='p-3 text-center'><span class="px-2 py-1 rounded-full text-xs <?php echo $creator['status'] == 'active' ? 'bg-green-700' : 'bg-zinc-600'; ?>"><?php echo ucfirst($creator['status']); ?></span></td>
                    <td class='p-3 text-center space-x-2'>
                        <button onclick='openEditCreatorModal(<?php echo $creator['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'><i class='fas fa-edit'></i></button>
                        <button onclick='deleteItem(<?php echo $creator['id']; ?>, "delete_creator", "creator")' class='text-red-500 hover:text-red-400' title='Delete'><i class='fas fa-trash'></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function openAddCreatorModal() {
    modalTitle.innerText = 'Add New Creator';
    modalBody.innerHTML = `
        <form id="creatorForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_creator">
            <label>Name</label><input type="text" name="creator_name" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Bio</label><textarea name="bio" class="w-full bg-zinc-700 p-2 rounded mb-2"></textarea>
            <label>Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded mb-2"><option value="active">Active</option><option value="inactive">Inactive</option></select>
            <label>Profile Picture</label><input type="file" name="profile_picture" class="w-full bg-zinc-700 p-1 rounded mb-2">
            <label>Facebook URL</label><input type="url" name="facebook" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>TikTok URL</label><input type="url" name="tiktok" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>Twitch URL</label><input type="url" name="twitch" class="w-full bg-zinc-700 p-2 rounded mb-2">
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="creatorForm" class="bg-red-600 px-4 py-2 rounded">Save</button>`;
    openModal();
}
async function openEditCreatorModal(id) {
    modalTitle.innerText = 'Edit Creator';
    modalBody.innerHTML = 'Loading...';
    openModal();
    const fd = new FormData(); fd.append('action', 'get_creator_details'); fd.append('id', id);
    const res = await fetch('api_handler.php', {method: 'POST', body: fd});
    const {data} = await res.json();
    const creator = data.creator;
    modalBody.innerHTML = `
        <form id="creatorForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_creator"><input type="hidden" name="id" value="${creator.id}">
            <label>Name</label><input type="text" name="creator_name" value="${creator.creator_name}" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Bio</label><textarea name="bio" class="w-full bg-zinc-700 p-2 rounded mb-2">${creator.bio || ''}</textarea>
            <label>Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded mb-2"><option value="active" ${creator.status=='active'?'selected':''}>Active</option><option value="inactive" ${creator.status=='inactive'?'selected':''}>Inactive</option></select>
            <label>New Profile Picture</label><input type="file" name="profile_picture" class="w-full bg-zinc-700 p-1 rounded mb-2">
            <label>Facebook URL</label><input type="url" name="facebook" value="${creator.social_media?.facebook || ''}" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>TikTok URL</label><input type="url" name="tiktok" value="${creator.social_media?.tiktok || ''}" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>Twitch URL</label><input type="url" name="twitch" value="${creator.social_media?.twitch || ''}" class="w-full bg-zinc-700 p-2 rounded mb-2">
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="creatorForm" class="bg-red-600 px-4 py-2 rounded">Update</button>`;
}
</script>

