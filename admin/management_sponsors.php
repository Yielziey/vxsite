<?php require_once __DIR__ . '/../db_connect.php'; ?>
<div id="sponsors-content">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-2xl font-semibold text-white">Sponsors</h3>
        <button onclick="openAddSponsorModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add New Sponsor
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="table-header">
                <tr>
                    <th class="p-3 text-left">Logo</th>
                    <th class="p-3 text-left">Sponsor Name</th>
                    <th class="p-3 text-left">Website</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body">
            <?php foreach ($pdo->query("SELECT * FROM sponsors ORDER BY id") as $sponsor): 
                $logo = $sponsor['sponsor_logo'] ? '../' . $sponsor['sponsor_logo'] : '../assets/sponsors/default.png';
            ?>
                <tr class='border-b border-zinc-600 table-row-hover'>
                    <td class='p-3'><img src="<?php echo $logo; ?>" class="w-24 h-12 object-contain bg-white rounded"></td>
                    <td class='p-3'><?php echo htmlspecialchars($sponsor['sponsor_name']); ?></td>
                    <td class='p-3'><a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank" class="text-blue-400 hover:underline"><?php echo htmlspecialchars($sponsor['website_url']); ?></a></td>
                    <td class='p-3 text-center'><span class="px-2 py-1 rounded-full text-xs <?php echo $sponsor['status'] == 'active' ? 'bg-green-700' : 'bg-zinc-600'; ?>"><?php echo ucfirst($sponsor['status']); ?></span></td>
                    <td class='p-3 text-center space-x-2'>
                        <button onclick='openEditSponsorModal(<?php echo $sponsor['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit'><i class='fas fa-edit'></i></button>
                        <button onclick='deleteItem(<?php echo $sponsor['id']; ?>, "delete_sponsor", "sponsor")' class='text-red-500 hover:text-red-400' title='Delete'><i class='fas fa-trash'></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function openAddSponsorModal() {
    modalTitle.innerText = 'Add New Sponsor';
    modalBody.innerHTML = `
        <form id="sponsorForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_sponsor">
            <label>Sponsor Name</label><input type="text" name="sponsor_name" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Website URL</label><input type="url" name="website_url" class="w-full bg-zinc-700 p-2 rounded mb-2" required placeholder="https://...">
            <label>Contact Person</label><input type="text" name="contact_person" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded mb-2"><option value="active">Active</option><option value="inactive">Inactive</option></select>
            <label>Sponsor Logo</label><input type="file" name="sponsor_logo" class="w-full bg-zinc-700 p-1 rounded mb-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="sponsorForm" class="bg-red-600 px-4 py-2 rounded">Save</button>`;
    openModal();
}
async function openEditSponsorModal(id) {
    modalTitle.innerText = 'Edit Sponsor';
    modalBody.innerHTML = 'Loading...';
    openModal();
    const fd = new FormData(); fd.append('action', 'get_sponsor_details'); fd.append('id', id);
    const res = await fetch('api_handler.php', {method: 'POST', body: fd});
    const {data} = await res.json();
    const sponsor = data.sponsor;
    const logo = sponsor.sponsor_logo ? '../' + sponsor.sponsor_logo : '../assets/sponsors/default.png';

    modalBody.innerHTML = `
        <form id="sponsorForm" onsubmit="handleFormSubmit(event)" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_sponsor"><input type="hidden" name="id" value="${sponsor.id}">
            <input type="hidden" name="existing_logo" value="${sponsor.sponsor_logo || ''}">
            <label>Sponsor Name</label><input type="text" name="sponsor_name" value="${sponsor.sponsor_name}" class="w-full bg-zinc-700 p-2 rounded mb-2" required>
            <label>Website URL</label><input type="url" name="website_url" value="${sponsor.website_url}" class="w-full bg-zinc-700 p-2 rounded mb-2" required placeholder="https://...">
            <label>Contact Person</label><input type="text" name="contact_person" value="${sponsor.contact_person || ''}" class="w-full bg-zinc-700 p-2 rounded mb-2">
            <label>Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded mb-2"><option value="active" ${sponsor.status=='active'?'selected':''}>Active</option><option value="inactive" ${sponsor.status=='inactive'?'selected':''}>Inactive</option></select>
            <label>New Sponsor Logo</label><input type="file" name="sponsor_logo" class="w-full bg-zinc-700 p-1 rounded mb-2 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
            <p class="text-xs text-zinc-400 mt-1 mb-2">Current: <img src="${logo}" class="w-16 h-8 object-contain bg-white rounded inline-block ml-2 align-middle"></p>
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="sponsorForm" class="bg-red-600 px-4 py-2 rounded">Update</button>`;
}
</script>

