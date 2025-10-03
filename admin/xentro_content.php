<?php
require_once __DIR__ . '/../db_connect.php';
?>
<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- SortableJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="content-area p-0">
    <!-- Xentro Management Section -->
    <div class="mb-4 border-b-2 border-red-600 pb-2 flex justify-between items-center">
        <h2 class="text-3xl font-bold text-white">Xentro Management</h2>
        <div>
            <button id="saveOrderBtn" onclick="saveOrder()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg hidden mr-2">
                <i class="fas fa-save mr-2"></i>Save Order
            </button>
            <button onclick="openAddXentroModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add New Member
            </button>
        </div>
    </div>
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="table-header">
                <tr>
                    <th class="p-3 text-center w-16">Order</th>
                    <th class="p-3 text-left">Image</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Role</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body" id="xentro-sortable">
                <?php
                $members = $pdo->query("SELECT id, name, role, image, sort_order FROM sentro ORDER BY sort_order ASC, id ASC")->fetchAll();
                if ($members):
                    foreach($members as $member): 
                        $image_path = $member['image'] ? '../assets/uploads/' . htmlspecialchars($member['image']) : '../assets/images/default.jpg';
                ?>
                        <tr class='border-b border-zinc-600 table-row-hover' data-id='<?php echo $member['id']; ?>'>
                            <td class='p-3 text-center cursor-grab text-zinc-400'><i class="fas fa-grip-vertical"></i></td>
                            <td class='p-3'><img src='<?php echo $image_path; ?>' alt='<?php echo htmlspecialchars($member['name']); ?>' class='w-12 h-12 rounded-full object-cover'></td>
                            <td class='p-3'><?php echo htmlspecialchars($member['name']); ?></td>
                            <td class='p-3'><?php echo htmlspecialchars($member['role']); ?></td>
                            <td class='p-3 text-center space-x-2'>
                                <button onclick='openEditXentroModal(<?php echo $member['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'>
                                    <i class='fas fa-edit'></i>
                                </button>
                                <button onclick='deleteItem(<?php echo $member['id']; ?>, "delete_xentro", "Xentro member")' class='text-red-500 hover:text-red-400' title='Delete'>
                                    <i class='fas fa-trash'></i>
                                </button>
                            </td>
                        </tr>
                <?php endforeach;
                else: ?>
                    <tr><td colspan="5" class="text-center p-4">No Xentro members found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize SortableJS
const sortableList = document.getElementById('xentro-sortable');
const saveOrderBtn = document.getElementById('saveOrderBtn');
if (sortableList) {
    new Sortable(sortableList, {
        animation: 150,
        handle: '.cursor-grab',
        onUpdate: function () {
            saveOrderBtn.classList.remove('hidden');
        }
    });
}
async function saveOrder() {
    const items = sortableList.querySelectorAll('tr');
    const order = Array.from(items).map(item => item.dataset.id);
    const formData = new FormData();
    formData.append('action', 'update_xentro_order');
    formData.append('order', JSON.stringify(order));
    const response = await fetch('api_handler.php', { method: 'POST', body: formData });
    const result = await response.json();
    if(result.success) {
        Swal.fire({ title: 'Success!', text: result.message, icon: 'success', timer: 1500, showConfirmButton: false });
        saveOrderBtn.classList.add('hidden');
    } else {
        Swal.fire({ title: 'Error!', text: result.message, icon: 'error' });
    }
}

function openAddXentroModal() {
    modalTitle.innerText = 'Add New Xentro Member';
    modalBody.innerHTML = `
        <form id="xentroForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_xentro">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6">
                <!-- Left Column -->
                <div class="flex flex-col space-y-4">
                    <div><label class="block mb-1 font-semibold">Name</label><input type="text" name="name" class="w-full bg-zinc-700 p-2 rounded" required></div>
                    <div><label class="block mb-1 font-semibold">Role</label><input type="text" name="role" class="w-full bg-zinc-700 p-2 rounded" required></div>
                    <div class="flex-grow flex flex-col"><label class="block mb-1 font-semibold">Bio</label><textarea name="bio" class="w-full bg-zinc-700 p-2 rounded flex-grow"></textarea></div>
                </div>
                <!-- Right Column -->
                <div class="space-y-4 mt-4 lg:mt-0">
                     <div><label class="block mb-1 font-semibold">Profile Image</label><input type="file" name="image" class="w-full bg-zinc-700 p-1 rounded file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"></div>
                     <div class="pt-4 border-t border-zinc-600"><label class="block mb-1 font-semibold">Portfolio Images (Optional)</label><input type="file" name="portfolio_images[]" class="w-full bg-zinc-700 p-1 rounded file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" multiple></div>
                </div>
            </div>
            <!-- Social Links Section -->
            <div class="mt-6 pt-4 border-t border-zinc-600">
                 <h3 class="text-lg font-semibold mb-3 text-white">Social & Contact Links</h3>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block mb-1">Email</label><input type="email" name="email" class="w-full bg-zinc-700 p-2 rounded"></div>
                    <div><label class="block mb-1">Portfolio URL</label><input type="url" name="portfolio_url" class="w-full bg-zinc-700 p-2 rounded" placeholder="https://yourportfolio.com"></div>
                    <div><label class="block mb-1">Facebook URL</label><input type="url" name="facebook" class="w-full bg-zinc-700 p-2 rounded"></div>
                    <div><label class="block mb-1">Twitch URL</label><input type="url" name="twitch" class="w-full bg-zinc-700 p-2 rounded"></div>
                    <div><label class="block mb-1">TikTok URL</label><input type="url" name="tiktok" class="w-full bg-zinc-700 p-2 rounded"></div>
                    <div><label class="block mb-1">Kick URL</label><input type="url" name="kick" class="w-full bg-zinc-700 p-2 rounded"></div>
                 </div>
            </div>
        </form>
    `;
    modalFooter.innerHTML = `
        <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
        <button type="submit" form="xentroForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Save Member</button>
    `;
    modal.querySelector('.modal-content').classList.add('max-w-4xl');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function openEditXentroModal(id) {
    modalTitle.innerText = 'Edit Xentro Member';
    modalBody.innerHTML = '<p>Loading member data...</p>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const formData = new FormData();
    formData.append('action', 'get_xentro_details');
    formData.append('id', id);

    const response = await fetch('api_handler.php', { method: 'POST', body: formData });
    const result = await response.json();

    if (result.success) {
        const member = result.data.member;
        
        let portfolioPreviews = '<div class="space-y-2">';
        portfolioPreviews += '<p class="font-semibold mb-2">Current Portfolio Images:</p>';
        if (member.portfolio_images && member.portfolio_images.length > 0) {
            portfolioPreviews += '<div class="flex flex-wrap gap-2">';
            member.portfolio_images.forEach(img => {
                portfolioPreviews += `<div class="relative group"><img src="../assets/uploads/${img}" class="w-20 h-20 rounded object-cover"></div>`;
            });
            portfolioPreviews += '</div>';
        } else {
            portfolioPreviews += '<p class="text-zinc-400 text-sm">No portfolio images uploaded.</p>';
        }
        portfolioPreviews += '</div>';

        modalBody.innerHTML = `
            <form id="xentroForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_xentro">
                <input type="hidden" name="id" value="${member.id}">
                <input type="hidden" name="existing_image" value="${member.image || ''}">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6">
                    <!-- Left Column -->
                    <div class="flex flex-col space-y-4">
                        <div><label class="block mb-1 font-semibold">Name</label><input type="text" name="name" class="w-full bg-zinc-700 p-2 rounded" value="${member.name || ''}" required></div>
                        <div><label class="block mb-1 font-semibold">Role</label><input type="text" name="role" class="w-full bg-zinc-700 p-2 rounded" value="${member.role || ''}" required></div>
                        <div class="flex-grow flex flex-col"><label class="block mb-1 font-semibold">Bio</label><textarea name="bio" class="w-full bg-zinc-700 p-2 rounded flex-grow">${member.bio || ''}</textarea></div>
                    </div>
                    <!-- Right Column -->
                    <div class="space-y-4 mt-4 lg:mt-0">
                        <div>
                           <label class="block mb-1 font-semibold">Current Profile Image</label>
                           <img src="${member.image ? '../assets/uploads/' + member.image : '../assets/images/default.jpg'}" class="w-24 h-24 rounded-full object-cover border-2 border-zinc-500 mb-2">
                           <label class="block mb-1 font-semibold">Upload New Profile Image</label>
                           <input type="file" name="image" class="w-full bg-zinc-700 p-1 rounded file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        </div>
                        <div class="pt-4 border-t border-zinc-600">${portfolioPreviews}</div>
                        <div><label class="block mb-1 font-semibold">Add More Portfolio Images</label><input type="file" name="portfolio_images[]" class="w-full bg-zinc-700 p-1 rounded file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" multiple></div>
                    </div>
                </div>
                <!-- Social Links Section -->
                <div class="mt-6 pt-4 border-t border-zinc-600">
                     <h3 class="text-lg font-semibold mb-3 text-white">Social & Contact Links</h3>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block mb-1">Email</label><input type="email" name="email" value="${member.email || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
                        <div><label class="block mb-1">Portfolio URL</label><input type="url" name="portfolio_url" value="${member.portfolio_url || ''}" class="w-full bg-zinc-700 p-2 rounded" placeholder="https://yourportfolio.com"></div>
                        <div><label class="block mb-1">Facebook URL</label><input type="url" name="facebook" value="${member.facebook || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
                        <div><label class="block mb-1">Twitch URL</label><input type="url" name="twitch" value="${member.twitch || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
                        <div><label class="block mb-1">TikTok URL</label><input type="url" name="tiktok" value="${member.tiktok || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
                        <div><label class="block mb-1">Kick URL</label><input type="url" name="kick" value="${member.kick || ''}" class="w-full bg-zinc-700 p-2 rounded"></div>
                    </div>
                </div>
            </form>
        `;
        modalFooter.innerHTML = `
            <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded">Cancel</button>
            <button type="submit" form="xentroForm" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Update Member</button>
        `;
        modal.querySelector('.modal-content').classList.add('max-w-4xl');
    } else {
        modalBody.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
        modal.querySelector('.modal-content').classList.remove('max-w-4xl');
    }
}

function closeModal() {
    const modal = document.getElementById('modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.querySelector('.modal-content').classList.remove('max-w-4xl');
}
</script>

