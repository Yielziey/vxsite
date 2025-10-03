<div class="bg-zinc-800 p-6 rounded-lg shadow-lg">
    <h2 class="text-3xl font-bold text-white mb-4 border-b-2 border-red-600 pb-2">Creators Management</h2>
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-zinc-700 rounded-lg">
            <thead class="bg-zinc-900">
                <tr>
                    <th class="py-3 px-4 text-left">Creator Name</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-200">
                <?php
                $creators = $pdo->query("SELECT id, creator_name, status FROM creators ORDER BY creator_name ASC")->fetchAll();
                if ($creators) {
                    foreach($creators as $row) {
                        $status_badge = $row['status'] == 'active' 
                            ? '<span class="bg-green-600 text-white text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">Active</span>'
                            : '<span class="bg-gray-500 text-white text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">Inactive</span>';

                        echo "<tr class='border-b border-zinc-600 hover:bg-zinc-600'>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($row['creator_name']) . "</td>";
                        echo "<td class='py-3 px-4'>" . $status_badge . "</td>";
                        echo "<td class='py-3 px-4 text-center'>
                                <button class='text-blue-400 hover:text-blue-300 mr-2'><i class='fas fa-edit'></i></button>
                                <button class='text-red-500 hover:text-red-400'><i class='fas fa-trash'></i></button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center py-4'>No creators found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

