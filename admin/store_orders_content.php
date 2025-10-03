<?php 
require_once __DIR__ . '/../db_connect.php';
$view = $_GET['view'] ?? 'active'; // 'active' or 'archived'
?>
<div class="bg-zinc-800 p-6 rounded-lg shadow-lg">
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b-2 border-red-600 pb-2 gap-4">
        <h2 class="text-3xl font-bold text-white">
            <?= $view === 'archived' ? 'Archived Orders' : 'Store Orders' ?>
        </h2>
        <div class="flex items-center gap-2">
            <?php if ($view === 'active'): ?>
                <button onclick="archiveAllCompleted()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-inbox mr-2"></i>Archive All Completed
                </button>
                <a href="javascript:void(0);" onclick="loadOrdersView('archived')" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-archive mr-2"></i>View Archived
                </a>
            <?php else: ?>
                <a href="javascript:void(0);" onclick="loadOrdersView('active')" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Active Orders
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ORDERS TABLE -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-zinc-700 rounded-lg">
            <thead class="bg-zinc-900">
                <tr>
                    <th class="py-3 px-4 text-left">Order ID</th>
                    <th class="py-3 px-4 text-left">Date</th>
                    <th class="py-3 px-4 text-left">Customer</th>
                    <th class="py-3 px-4 text-left">Item</th>
                    <th class="py-3 px-4 text-right">Total</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="orders-table-body" class="text-gray-200">
                <?php
                date_default_timezone_set('Asia/Manila');
                if ($view === 'archived') {
                    $stmt = $pdo->query("SELECT * FROM store_orders WHERE status = 'Archived' ORDER BY created_at DESC");
                } else {
                    $stmt = $pdo->query("SELECT * FROM store_orders WHERE status != 'Archived' ORDER BY FIELD(status, 'Pending', 'Confirmed', 'Shipped', 'Completed', 'Cancelled'), created_at DESC");
                }
                $orders = $stmt->fetchAll();

                if (empty($orders)) {
                    echo "<tr><td colspan='7' class='text-center p-4'>No orders found.</td></tr>";
                } else {
                    foreach($orders as $order) {
                        $status_colors = [
                            'Pending' => 'bg-orange-500', 'Confirmed' => 'bg-blue-500',
                            'Shipped' => 'bg-purple-500', 'Completed' => 'bg-green-500',
                            'Cancelled' => 'bg-red-700', 'Archived' => 'bg-zinc-600'
                        ];
                        $status_badge = "<span class='text-white text-xs px-2 py-1 rounded-full {$status_colors[$order['status']]}'>" . htmlspecialchars($order['status']) . "</span>";
                        
                        echo "<tr class='border-b border-zinc-600 hover:bg-zinc-600' id='order-row-{$order['id']}'>";
                        echo "<td class='py-3 px-4 font-mono text-sm'>#" . htmlspecialchars($order['id']) . "</td>";
                        echo "<td class='py-3 px-4'>" . date('M d, Y', strtotime($order['created_at'])) . "</td>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($order['customer_name']) . "</td>";
                        echo "<td class='py-3 px-4'>" . htmlspecialchars($order['item_name']) . "</td>";
                        echo "<td class='py-3 px-4 text-right'>₱" . number_format($order['total'], 2) . "</td>";
                        echo "<td class='py-3 px-4 text-center status-text'>{$status_badge}</td>";
                        echo "<td class='py-3 px-4 text-center space-x-2'>
                                <button onclick='openViewOrderModal({$order['id']})' class='text-blue-400 hover:text-blue-300' title='View Details'><i class='fas fa-eye'></i></button>
                                <button onclick='deleteItem({$order['id']}, \"delete_order\", \"order\")' class='text-red-500 hover:text-red-400' title='Delete Order'><i class='fas fa-trash'></i></button>
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
function loadOrdersView(view) {
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        mainContent.innerHTML = '<div class="text-center p-8 text-white">Loading...</div>';
        fetch(`store_orders_content.php?view=${view}`)
            .then(res => res.text())
            .then(html => { mainContent.innerHTML = html; })
            .catch(err => { window.location.href = `index.php?page=store_orders&view=${view}`; });
    } else {
        window.location.href = `index.php?page=store_orders&view=${view}`;
    }
}

function archiveAllCompleted() {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will archive all orders currently marked as 'Completed'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#5a6268',
        confirmButtonText: 'Yes, Archive All'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'archive_all_completed_orders');
            fetch('api_handler.php', { method: 'POST', body: fd })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Toast.fire({ icon: 'success', title: result.message });
                        loadOrdersView('active'); 
                    } else { throw new Error(result.message); }
                })
                .catch(error => {
                    Swal.fire('Error', `Could not archive orders: ${error.message}`, 'error');
                });
        }
    });
}

async function openViewOrderModal(orderId) {
    modalTitle.innerText = 'Order Details';
    modalBody.innerHTML = '<div class="text-center p-8 text-white">Loading...</div>';
    openModal('3xl');

    const fd = new FormData();
    fd.append('action', 'get_order_details');
    fd.append('id', orderId);

    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const order = result.data.order;
        const proofPath = order.proof_file ? `../assets/payment/${order.proof_file}` : '../assets/images/no-proof.png';
        const isArchived = order.status === 'Archived';

        modalBody.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-zinc-200">
                <div class="space-y-4 bg-zinc-800 p-4 rounded-lg">
                    <div><strong class="text-red-400 block">Order ID:</strong> #${order.id}</div>
                    <div><strong class="text-red-400 block">Customer Name:</strong> ${order.customer_name}</div>
                    <div><strong class="text-red-400 block">Contact:</strong> ${order.contact}</div>
                    <div><strong class="text-red-400 block">Address:</strong><p class="mt-1">${order.address}</p></div>
                </div>
                <div class="space-y-4 bg-zinc-800 p-4 rounded-lg">
                    <div><strong class="text-red-400 block">Item:</strong> ${order.item_name} (Size: ${order.size})</div>
                    <div><strong class="text-red-400 block">Quantity:</strong> ${order.quantity}</div>
                    <div><strong class="text-red-400 block">Total Price:</strong> ₱${parseFloat(order.total).toFixed(2)}</div>
                    <div><strong class="text-red-400 block">Payment Method:</strong> ${order.payment_method}</div>
                </div>
                <div class="md:col-span-2 bg-zinc-800 p-4 rounded-lg">
                    <strong class="text-red-400 block mb-2">Proof of Payment:</strong>
                    <a href="${proofPath}" target="_blank" title="View full image">
                        <img src="${proofPath}" alt="Proof of payment" class="max-h-80 max-w-xs w-auto rounded-lg border-2 border-zinc-600 hover:border-red-500 transition-colors">
                    </a>
                </div>
            </div>
        `;
        
        let footerContent = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Close</button>`;
        if(isArchived) {
             footerContent = `<div class="flex justify-between items-center w-full">
                <button onclick="unarchiveOrder(${order.id})" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Unarchive Order</button>
                <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded-lg">Close</button>
             </div>`;
        } else {
            footerContent = `<div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <label for="status-select" class="text-sm">Update Status:</label>
                    <select id="status-select" onchange="updateOrderStatus(${order.id}, this.value)" class="bg-zinc-600 rounded p-2 border border-zinc-500">
                        <option value="Pending" ${order.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Confirmed" ${order.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                        <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                        <option value="Completed" ${order.status === 'Completed' ? 'selected' : ''}>Completed</option>
                        <option value="Cancelled" ${order.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </div>
                <button onclick="closeModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded-lg">Close</button>
            </div>`;
        }
        modalFooter.innerHTML = footerContent;
    } catch (error) {
        modalBody.innerHTML = `<div class="text-center p-8 text-red-500">Error: ${error.message}</div>`;
        modalFooter.innerHTML = `<button onclick="closeModal()" class="bg-zinc-600 px-4 py-2 rounded">Close</button>`;
    }
}

async function unarchiveOrder(orderId) {
    const fd = new FormData();
    fd.append('action', 'unarchive_order');
    fd.append('id', orderId);

    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();
        if (result.success) {
            Toast.fire({ icon: 'success', title: 'Order has been unarchived!' });
            closeModal();
            loadOrdersView('archived'); // Refresh the archived view
        } else { throw new Error(result.message); }
    } catch (error) {
        Swal.fire('Error', `Could not unarchive order: ${error.message}`, 'error');
    }
}

async function updateOrderStatus(orderId, newStatus) {
    const fd = new FormData();
    fd.append('action', 'update_order_status');
    fd.append('id', orderId);
    fd.append('status', newStatus);

    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();
        if (result.success) {
            Toast.fire({ icon: 'success', title: 'Order status updated!' });
            // Refresh view to show updated badge
            const currentView = document.querySelector('h2').textContent.toLowerCase().includes('archived') ? 'archived' : 'active';
            loadOrdersView(currentView);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Toast.fire({ icon: 'error', title: `Error: ${error.message || 'Could not update status.'}` });
    }
}
</script>

