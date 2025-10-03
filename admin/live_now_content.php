<?php include_once 'db_connect.php'; ?>
<div id="live-now-content" class="bg-zinc-800 p-6 rounded-lg shadow-lg">
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-3xl font-bold text-white border-b-2 border-red-600 pb-2">Live Now</h2>
        <button onclick="openAddStreamerModal()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add Streamer
        </button>
    </div>

    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-zinc-700 rounded-lg">
            <thead class="bg-zinc-900">
                <tr>
                    <th class="py-3 px-4 text-left">Streamer</th>
                    <th class="py-3 px-4 text-left">Platform</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="streamer-table-body">
                <?php
                $streamers = $pdo->query("SELECT * FROM streamers ORDER BY name ASC")->fetchAll();
                if (empty($streamers)) {
                    echo "<tr><td colspan='4' class='text-center p-4'>No streamers have been added yet.</td></tr>";
                } else {
                    foreach($streamers as $streamer) {
                        $username_lower = strtolower(htmlspecialchars($streamer['username']));
                        $platform_lower = strtolower(htmlspecialchars($streamer['platform']));
                        $stream_url = $platform_lower === 'twitch' ? "https://twitch.tv/{$username_lower}" : "#";

                        echo "<tr class='border-b border-zinc-600' data-streamer='{$username_lower}'>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($streamer['name']) . "</td>";
                        echo "<td class='py-3 px-4 capitalize'>" . $platform_lower . "</td>";
                        echo "<td class='py-3 px-4 status-cell'><span class='text-gray-400'>Checking...</span></td>";
                        echo "<td class='py-3 px-4 text-center action-cell space-x-4'>
                                <a href='{$stream_url}' target='_blank' class='text-blue-400 hover:text-blue-300 opacity-50 pointer-events-none' title='View Stream'><i class='fas fa-external-link-alt'></i></a>
                                <button onclick='deleteItem({$streamer['id']}, \"delete_streamer\", \"streamer\")' class='text-red-500 hover:text-red-400' title='Delete Streamer'><i class='fas fa-trash'></i></button>
                              </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
(async function checkStreamerStatus() {
    const tableBody = document.getElementById('streamer-table-body');
    // Do nothing if there are no streamers to check
    if (!tableBody.querySelector('tr[data-streamer]')) {
        return;
    }

    try {
        const response = await fetch(`check_live.php?t=${new Date().getTime()}`);
        const responseText = await response.text();
        
        // Attempt to parse the text as JSON. If it fails, we'll catch the error and display the raw text.
        const liveStatuses = JSON.parse(responseText);

        if (liveStatuses.error) {
            throw new Error(liveStatuses.error);
        }

        const rows = document.querySelectorAll('#streamer-table-body tr');
        rows.forEach(row => {
            const streamerUsername = row.dataset.streamer;
            if (!streamerUsername) return;

            const statusData = liveStatuses[streamerUsername];
            const statusCell = row.querySelector('.status-cell');
            const actionCellLink = row.querySelector('.action-cell a');

            if (statusData && statusData.status === 'live') {
                statusCell.innerHTML = `<span class='text-red-500 font-bold flex items-center'><i class='fas fa-circle text-xs mr-2 animate-pulse'></i>LIVE</span>`;
                actionCellLink.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                statusCell.innerHTML = `<span class='text-gray-500 flex items-center'><i class='fas fa-circle text-xs mr-2'></i>Offline</span>`;
            }
        });

    } catch (error) {
        console.error("Failed to fetch or parse live statuses:", error);
        
        // --- DEBUGGING BLOCK ---
        // Fetch the raw text again to show the user the exact server response
        const debugResponse = await fetch(`check_live.php?t=${new Date().getTime()}`);
        const debugText = await debugResponse.text();
        
        tableBody.innerHTML = `<tr><td colspan="4" class="p-4">
            <div class="bg-red-900 border border-red-600 text-white p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">An Error Occurred!</h3>
                <p class="mb-3 text-sm">The script could not check the live status. This is usually caused by incorrect Twitch API keys in <strong>check_live.php</strong> or a server configuration issue. Below is the exact response from the server:</p>
                <pre class="bg-zinc-900 p-3 rounded-md overflow-auto text-xs whitespace-pre-wrap">${debugText.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</pre>
            </div>
        </td></tr>`;
    }
})();

function openAddStreamerModal() {
    modalTitle.innerText = 'Add New Streamer';
    modalBody.innerHTML = `
        <form id="streamerForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" value="add_streamer">
            <label class="block mb-1">Streamer Name</label>
            <input type="text" name="name" class="w-full bg-zinc-700 p-2 rounded mb-4" placeholder="e.g., Yielziey" required>
            
            <label class="block mb-1">Platform Username</label>
            <input type="text" name="username" class="w-full bg-zinc-700 p-2 rounded mb-4" placeholder="e.g., yielziey (case-insensitive)" required>
            
            <label class="block mb-1">Platform</label>
            <select name="platform" class="w-full bg-zinc-700 p-2 rounded">
                <option value="twitch">Twitch</option>
                <option value="kick" disabled>Kick (Not Supported Yet)</option>
            </select>
        </form>`;
    modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Cancel</button><button type="submit" form="streamerForm" class="bg-red-600 px-4 py-2 rounded">Add Streamer</button>`;
    openModal();
}
</script>

