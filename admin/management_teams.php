<?php require_once __DIR__ . '/../db_connect.php'; ?>
<div id="teams-content">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-2xl font-semibold text-white">Teams</h3>
        <button onclick="openAddTeamModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add New Team
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="table-header">
                <tr>
                    <th class="p-3 text-left">Team Name</th>
                    <th class="p-3 text-center">Wins</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="table-body">
            <?php foreach ($pdo->query("SELECT * FROM teams ORDER BY id") as $team): ?>
                <tr class='border-b border-zinc-600 table-row-hover'>
                    <td class='p-3'><?php echo htmlspecialchars($team['team_name']); ?></td>
                    <td class='p-3 text-center'><?php echo $team['wins']; ?></td>
                    <td class='p-3 text-center'><span class="px-2 py-1 rounded-full text-xs <?php echo $team['status'] == 'active' ? 'bg-green-700' : 'bg-zinc-600'; ?>"><?php echo ucfirst($team['status']); ?></span></td>
                    <td class='p-3 text-center space-x-2'>
                        <button onclick='openViewMembersModal(<?php echo $team['id']; ?>, "<?php echo htmlspecialchars(addslashes($team['team_name'])); ?>")' class='text-green-400 hover:text-green-300' title='View Members'><i class='fas fa-users'></i></button>
                        <button onclick='openEditTeamModal(<?php echo $team['id']; ?>)' class='text-blue-400 hover:text-blue-300' title='Edit Team'><i class='fas fa-edit'></i></button>
                        <button onclick='deleteItem(<?php echo $team['id']; ?>, "delete_team", "team")' class='text-red-500 hover:text-red-400' title='Delete Team'><i class='fas fa-trash'></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// --- Team Modals ---
function openAddTeamModal() {
    modalTitle.innerText = 'Add New Team';
    modalBody.innerHTML = `
        <form id="teamForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_team">
            <label class="block mb-1">Team Name</label><input type="text" name="team_name" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="block mb-1">Wins</label><input type="number" name="wins" value="0" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="block mb-1">Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded"><option value="active">Active</option><option value="inactive">Inactive</option></select>
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="teamForm" class="bg-red-600 px-4 py-2 rounded">Save Team</button>`;
    openModal();
}
async function openEditTeamModal(id) {
    modalTitle.innerText = 'Edit Team';
    modalBody.innerHTML = 'Loading...';
    openModal();
    const fd = new FormData(); fd.append('action', 'get_team_details'); fd.append('id', id);
    const res = await fetch('api_handler.php', {method: 'POST', body: fd});
    const {data} = await res.json();
    const team = data.team;
    modalBody.innerHTML = `
        <form id="teamForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="edit_team"><input type="hidden" name="id" value="${team.id}">
            <label class="block mb-1">Team Name</label><input type="text" name="team_name" value="${team.team_name}" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="block mb-1">Wins</label><input type="number" name="wins" value="${team.wins}" class="w-full bg-zinc-700 p-2 rounded mb-4" required>
            <label class="block mb-1">Status</label><select name="status" class="w-full bg-zinc-700 p-2 rounded"><option value="active" ${team.status == 'active' ? 'selected' : ''}>Active</option><option value="inactive" ${team.status == 'inactive' ? 'selected' : ''}>Inactive</option></select>
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="teamForm" class="bg-red-600 px-4 py-2 rounded">Update Team</button>`;
}

// --- Member Modals & Functions ---
async function openViewMembersModal(teamId, teamName) {
    modalTitle.innerText = `Members of ${teamName}`;
    modalBody.innerHTML = 'Loading members...';
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Close</button>`;
    openModal('4xl');
    await refreshMembersList(teamId, teamName);
}

async function refreshMembersList(teamId, teamName) {
    // Update the title in case we came from an edit modal
    modalTitle.innerText = `Members of ${teamName}`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Close</button>`;

    const fd = new FormData(); fd.append('action', 'get_team_members'); fd.append('team_id', teamId);
    const res = await fetch('api_handler.php', { method: 'POST', body: fd });
    const { data } = await res.json();
    let membersHtml = `
        <div class="mb-4 p-4 border border-zinc-700 rounded-lg">
             <h4 class="text-lg font-semibold text-white mb-3">Add New Member</h4>
             <form id="addMemberForm" onsubmit="event.preventDefault(); handleAddMember(${teamId}, '${teamName}');">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Member Name</label>
                        <input type="text" name="member_name" placeholder="Juan Dela Cruz" class="w-full bg-zinc-700 p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Role</label>
                        <input type="text" name="member_role" placeholder="Player" class="w-full bg-zinc-700 p-2 rounded" required>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Image</label>
                        <input type="file" name="member_image" class="w-full text-sm text-zinc-400 bg-zinc-700 rounded file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-zinc-600 file:text-zinc-200 hover:file:bg-zinc-500">
                    </div>
                </div>
                 <div class="text-right mt-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2 rounded-lg">Add Member</button>
                </div>
            </form>
        </div>
        <table class="min-w-full">
            <thead class="table-header"><tr><th class="p-2 text-left">Image</th><th class="p-2 text-left">Name</th><th class="p-2 text-left">Role</th><th class="p-2 text-center">Actions</th></tr></thead>
            <tbody class="table-body">`;
    if(data.members.length > 0) {
        data.members.forEach(m => {
            const imgSrc = m.member_image ? '../' + m.member_image : '../assets/images/default.jpg';
            // Important: Pass teamName to the edit function
            const memberData = JSON.stringify(m).replace(/"/g, '&quot;');
            membersHtml += `<tr class="border-b border-zinc-700">
                <td class="p-2"><img src="${imgSrc}" class="w-10 h-10 rounded-full object-cover"></td>
                <td class="p-2">${m.member_name}</td>
                <td class="p-2">${m.member_role}</td>
                <td class="p-2 text-center">
                    <button onclick='openEditMemberModal(${memberData}, ${teamId}, "${teamName}")' class='text-blue-400 hover:text-blue-300 mr-2' title='Edit Member'><i class='fas fa-edit'></i></button>
                    <button onclick="handleDeleteMember(${m.id}, ${teamId}, '${teamName}')" class="text-red-500 hover:text-red-400" title='Delete Member'><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    } else {
        membersHtml += `<tr><td colspan="4" class="p-4 text-center">No members found for this team.</td></tr>`;
    }
    membersHtml += `</tbody></table>`;
    modalBody.innerHTML = membersHtml;
}

function openEditMemberModal(member, teamId, teamName) {
    const currentImgSrc = member.member_image ? '../' + member.member_image : '../assets/images/default.jpg';
    // This function now changes the content of the SAME modal
    modalTitle.innerText = `Edit Member: ${member.member_name}`;
    modalBody.innerHTML = `
        <form id="editMemberForm" onsubmit="event.preventDefault(); handleEditMember(${member.id}, ${teamId}, '${teamName}');">
            <input type="hidden" name="action" value="edit_member">
            <input type="hidden" name="id" value="${member.id}">
            <input type="hidden" name="existing_image" value="${member.member_image || ''}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">
                <div>
                    <label class="block mb-1">Member Name</label>
                    <input type="text" name="member_name" value="${member.member_name}" class="w-full bg-zinc-700 p-2 rounded" required>
                </div>
                <div>
                    <label class="block mb-1">Role</label>
                    <input type="text" name="member_role" value="${member.member_role}" class="w-full bg-zinc-700 p-2 rounded" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1">New Image (Optional)</label>
                    <input type="file" name="member_image" class="w-full text-sm text-zinc-400 bg-zinc-700 rounded file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-zinc-600 file:text-zinc-200 hover:file:bg-zinc-500">
                    <p class="text-xs text-zinc-400 mt-2">Current: <img src="${currentImgSrc}" class="w-8 h-8 rounded-full inline-block ml-2 align-middle"></p>
                </div>
            </div>
        </form>
    `;
    modalFooter.innerHTML = `
        <button onclick="refreshMembersList(${teamId}, '${teamName}')" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button>
        <button type="submit" form="editMemberForm" class="bg-blue-600 px-4 py-2 rounded">Update Member</button>
    `;
}

async function handleAddMember(teamId, teamName) {
    const form = document.getElementById('addMemberForm');
    const fd = new FormData(form);
    fd.append('action', 'add_member');
    fd.append('team_id', teamId);
    
    await fetch('api_handler.php', { method: 'POST', body: fd });
    await refreshMembersList(teamId, teamName);
}

async function handleEditMember(memberId, teamId, teamName) {
    const form = document.getElementById('editMemberForm');
    const fd = new FormData(form);
    // action, id, and existing_image are already in the form
    
    await fetch('api_handler.php', { method: 'POST', body: fd });
    // After updating, refresh the main members list
    await refreshMembersList(teamId, teamName);
}

async function handleDeleteMember(memberId, teamId, teamName) {
    // Optional: Add a confirmation dialog here
    const fd = new FormData();
    fd.append('action', 'delete_member');
    fd.append('id', memberId);
    await fetch('api_handler.php', { method: 'POST', body: fd });
    await refreshMembersList(teamId, teamName);
}
</script>

