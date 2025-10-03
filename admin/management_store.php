<?php require_once __DIR__ . '/../db_connect.php'; ?>
<div id="store-content">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-2xl font-semibold text-white">Store Items</h3>
        <button onclick="openAddStoreItemModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add New Item
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="table-header">
                <tr>
                    <th class="p-3 text-left">Media</th>
                    <th class="p-3 text-left">Item Name</th>
                    <th class="p-3 text-right">Price</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body">
            <?php foreach ($pdo->query("SELECT * FROM store ORDER BY id") as $item): 
                $media_path = $item['media'] ? '../' . $item['media'] : '../assets/images/default.jpg';
                $is_video = in_array(pathinfo($media_path, PATHINFO_EXTENSION), ['mp4', 'webm']);
            ?>
                <tr class='border-b border-zinc-600 table-row-hover'>
                    <td class='p-3'>
                        <?php if($is_video): ?>
                            <video src="<?php echo $media_path; ?>" class="w-24 h-24 object-cover rounded" autoplay loop muted playsinline></video>
                        <?php else: ?>
                            <img src="<?php echo $media_path; ?>" class="w-24 h-24 object-cover rounded">
                        <?php endif; ?>
                    </td>
                    <td class='p-3'><?php echo htmlspecialchars($item['name']); ?></td>
                    <td class='p-3 text-right'>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td class='p-3 text-center space-x-2'>
                        <button onclick='openEditStoreItemModal(<?php echo $item['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'><i class='fas fa-edit'></i></button>
                        <button onclick='deleteItem(<?php echo $item['id']; ?>, "delete_store_item", "store item")' class='text-red-500 hover:text-red-400' title='Delete'><i class='fas fa-trash'></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function openAddStoreItemModal() {
    modalTitle.innerText = 'Add New Store Item';
    modalBody.innerHTML = `
        <form id="storeItemForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_store_item">
            <label>Item Name</label><input type="text" name="name" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Price (PHP)</label><input type="number" step="0.01" name="price" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Media (Image/Video)</label><input type="file" name="media" accept="image/*,video/mp4" class="w-full bg-zinc-700 p-1 rounded mb-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="storeItemForm" class="bg-red-600 px-4 py-2 rounded">Save Item</button>`;
    openModal();
}
async function openEditStoreItemModal(id) {
    modalTitle.innerText = 'Edit Store Item';
    modalBody.innerHTML = 'Loading...';
    openModal();
    const fd = new FormData(); fd.append('action', 'get_store_item_details'); fd.append('id', id);
    const res = await fetch('api_handler.php', {method: 'POST', body: fd});
    const {data} = await res.json();
    const item = data.item;
    const media_path = item.media ? '../' + item.media : '../assets/images/default.jpg';

    modalBody.innerHTML = `
        <form id="storeItemForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_store_item"><input type="hidden" name="id" value="${item.id}">
            <input type="hidden" name="existing_media" value="${item.media || ''}">
            <label>Item Name</label><input type="text" name="name" value="${item.name}" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Price (PHP)</label><input type="number" step="0.01" name="price" value="${item.price}" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>New Media (Image/Video)</label><input type="file" name="media" accept="image/*,video/mp4" class="w-full bg-zinc-700 p-1 rounded mb-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
            <p class="text-xs text-zinc-400 mt-1 mb-2">Current Media is set. Uploading a new file will overwrite it.</p>
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="storeItemForm" class="bg-red-600 px-4 py-2 rounded">Update Item</button>`;
}
</script>

