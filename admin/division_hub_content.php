<?php
include_once '/../db_connect.php';
$subpage = $_GET['subpage'] ?? 'teams'; // Default to teams
$valid_subpages = ['teams', 'creators', 'sponsors', 'store'];
if (!in_array($subpage, $valid_subpages)) {
    $subpage = 'teams';
}
?>
<div class="bg-zinc-800 p-6 rounded-lg shadow-lg">
    <h2 class="text-3xl font-bold text-white mb-4 border-b-2 border-red-600 pb-2">Management Hub</h2>

    <!-- Sub-navigation Tabs -->
    <div class="flex border-b border-zinc-700 mb-6">
        <a href="?page=division_hub&subpage=teams" class="py-2 px-4 <?php echo $subpage === 'teams' ? 'text-red-500 border-b-2 border-red-500' : 'text-zinc-400 hover:text-white'; ?>">
            Teams & Members
        </a>
        <a href="?page=division_hub&subpage=creators" class="py-2 px-4 <?php echo $subpage === 'creators' ? 'text-red-500 border-b-2 border-red-500' : 'text-zinc-400 hover:text-white'; ?>">
            Content Creators
        </a>
        <a href="?page=division_hub&subpage=sponsors" class="py-2 px-4 <?php echo $subpage === 'sponsors' ? 'text-red-500 border-b-2 border-red-500' : 'text-zinc-400 hover:text-white'; ?>">
            Sponsors
        </a>
        <a href="?page=division_hub&subpage=store" class="py-2 px-4 <?php echo $subpage === 'store' ? 'text-red-500 border-b-2 border-red-500' : 'text-zinc-400 hover:text-white'; ?>">
            Store Items
        </a>
    </div>

    <!-- Content for the selected subpage -->
    <div id="management-content">
        <?php
        // Include the content file based on the subpage
        include "management_{$subpage}.php";
        ?>
    </div>
</div>

