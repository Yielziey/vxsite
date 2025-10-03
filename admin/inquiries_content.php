<?php
// This line is important to ensure $pdo is available.
require_once __DIR__ . '/../db_connect.php';

// Determine which view to show: 'active' (default) or 'archived'
$view = $_GET['view'] ?? 'active'; 
?>
<div class="bg-zinc-800 p-6 rounded-lg shadow-lg">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b-2 border-red-600 pb-2 gap-4">
        <h2 class="text-3xl font-bold text-white">
            <?= $view === 'archived' ? 'Archived Inquiries' : 'Active Inquiries' ?>
        </h2>
        <div class="flex items-center gap-2">
            <?php if ($view === 'active'): ?>
                <button onclick="archiveAllClosedTickets()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-inbox mr-2"></i>Archive All Closed Tickets
                </button>
                <a href="javascript:void(0);" onclick="loadInquiriesView('archived')" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-archive mr-2"></i>View Archived
                </a>
            <?php else: ?>
                <a href="javascript:void(0);" onclick="loadInquiriesView('active')" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Active Inquiries
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-zinc-700 rounded-lg">
            <thead class="bg-zinc-900">
                <tr>
                    <th class="py-3 px-4 text-left">Ticket #</th>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Topic</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-200">
            <?php
            date_default_timezone_set('Asia/Manila');
            
            if ($view === 'archived') {
                $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE status = 'archived' ORDER BY created_at DESC");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE status != 'archived' ORDER BY FIELD(status, 'open', 'closed'), created_at DESC");
            }
            $stmt->execute();
            $inquiries = $stmt->fetchAll();

            if (empty($inquiries)) {
                $message = $view === 'archived' ? 'No archived inquiries found.' : 'No active inquiries found.';
                echo "<tr><td colspan='5' class='text-center p-4'>{$message}</td></tr>";
            } else {
                foreach($inquiries as $inquiry) {
                    $status_colors = ['open' => 'bg-red-600', 'closed' => 'bg-yellow-600', 'archived' => 'bg-zinc-600'];
                    $status_badge = "<span class='{$status_colors[$inquiry['status']]} text-white text-xs px-2 py-1 rounded-full capitalize'>" . htmlspecialchars($inquiry['status']) . "</span>";
                    
                    echo "<tr class='border-b border-zinc-600 hover:bg-zinc-600'>";
                    echo "<td class='py-3 px-4 font-mono text-sm'>" . htmlspecialchars($inquiry['ticket_number']) . "</td>";
                    echo "<td class='py-3 px-4'>" . htmlspecialchars($inquiry['fname'] . ' ' . $inquiry['lname']) . "</td>";
                    echo "<td class='py-3 px-4'>" . htmlspecialchars($inquiry['topic']) . "</td>";
                    echo "<td class='py-3 px-4'>" . $status_badge . "</td>";
                    echo "<td class='py-3 px-4 text-center'>
                            <button onclick='openInquiryModal({$inquiry['id']})' class='text-blue-400 hover:text-blue-300' title='View Inquiry'><i class='fas fa-envelope-open'></i></button>
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
let inquiryInterval = null;
let lastMessageId = 0;
let lastAdminTypingTime = 0;

if (!document.getElementById('inquiry-styles')) {
    const style = document.createElement('style');
    style.id = 'inquiry-styles';
    style.innerHTML = `
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .typing-dots span { display: inline-block; width: 8px; height: 8px; background-color: #d1d5db; border-radius: 50%; animation: typing-bounce 1.4s infinite; }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing-bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1.0); } }
    `;
    document.head.appendChild(style);
}

function loadInquiriesView(view) {
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        mainContent.innerHTML = '<div class="text-center p-8 text-white">Loading...</div>';
        fetch(`inquiries_content.php?view=${view}`)
            .then(res => res.text())
            .then(html => { mainContent.innerHTML = html; })
            .catch(err => {
                console.error('Failed to reload content:', err);
                window.location.href = `index.php?page=inquiries&view=${view}`;
            });
    } else {
        window.location.href = `index.php?page=inquiries&view=${view}`;
    }
}

function archiveAllClosedTickets() {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will archive all currently closed tickets.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#5a6268',
        confirmButtonText: 'Yes, Archive All'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('action', 'archive_all_closed');
            fetch('api_handler.php', { method: 'POST', body: fd })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Toast.fire({ icon: 'success', title: result.message });
                        loadInquiriesView('active'); 
                    } else { throw new Error(result.message); }
                })
                .catch(error => {
                    Swal.fire('Error', `Could not archive tickets: ${error.message}`, 'error');
                });
        }
    });
}

function parseMessage(message) {
    if (!message) return '';
    const fileRegex = /\[file:(.*?)\|(.+?)\]/g;
    const tempDiv = document.createElement('div');
    tempDiv.innerText = message;
    let html = tempDiv.innerHTML.replace(fileRegex, (match, fileName, filePath) => {
        let cleanPath = `../${filePath.trim().replace(/^\/vxsite\//, '')}`;
        return `<a href="${cleanPath}" target="_blank" class="flex items-center gap-2 bg-zinc-500/50 hover:bg-zinc-500/80 p-2 rounded-lg text-white no-underline transition-colors">
                    <i class="fas fa-file-download fa-fw"></i><span>${fileName.trim()}</span></a>`;
    });
    return html.replace(/\n/g, '<br>');
}

async function openInquiryModal(inquiryId) {
    if (inquiryInterval) clearInterval(inquiryInterval);
    lastMessageId = 0;
    lastAdminTypingTime = 0;
    openModal('5xl');
    modalTitle.innerHTML = ''; 
    modalBody.innerHTML = '<div class="text-center p-8 text-white">Loading conversation...</div>';
    
    try {
        const fd = new FormData();
        fd.append('action', 'get_inquiry_messages');
        fd.append('id', inquiryId);
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        
        const { messages, inquiry } = result.data;
        const inquiryDate = new Date(inquiry.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

        const headerHtml = `<div class="p-5 border-b border-zinc-700 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-sm text-red-400 font-semibold">${inquiry.topic}</p>
                    <h1 class="text-2xl font-bold text-white tracking-wider font-mono">${inquiry.ticket_number}</h1>
                </div>
                <div class="text-left text-sm text-zinc-400 space-y-1 border-l border-zinc-700 pl-6">
                    <p><i class="fas fa-user fa-fw mr-2 text-zinc-500"></i>${inquiry.fname} ${inquiry.lname}</p>
                    <p><i class="fas fa-calendar-alt fa-fw mr-2 text-zinc-500"></i>${inquiryDate}</p>
                </div></div></div>`;
        
        let messagesHtml = '<div id="chat-log" class="chat-body flex-1 p-6 overflow-y-auto space-y-4" style="height: 50vh;">';
        messages.forEach(msg => {
            const isClient = msg.sender === 'client';
            messagesHtml += `<div class="flex ${isClient ? 'justify-start' : 'justify-end'}"><div class="max-w-xl p-3 rounded-xl break-words ${isClient ? 'bg-zinc-700 text-white' : 'bg-red-700 text-white'}">
                <div>${parseMessage(msg.message)}</div>
                <p class="text-xs ${isClient ? 'text-zinc-400' : 'text-red-200'} mt-2 text-right">${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
            </div></div>`;
            if (msg.id > lastMessageId) lastMessageId = msg.id;
        });
        messagesHtml += '</div>';

        const formHtml = inquiry.status !== 'archived' ? `
            <div class="p-6 border-t border-zinc-700">
                <form id="replyForm" onsubmit="handleInquirySubmit(event)" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="send_inquiry_reply"><input type="hidden" name="inquiry_id" value="${inquiryId}">
                    <div class="relative"><input type="text" name="message" class="w-full bg-zinc-800 text-white rounded-lg py-3 pl-5 pr-24 focus:outline-none focus:ring-2 focus:ring-red-600" placeholder="Type your message...">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                            <label for="attachment-file-${inquiryId}" class="text-zinc-400 hover:text-red-500 cursor-pointer p-2 text-lg"><i class="fas fa-paperclip"></i><input type="file" name="attachment" id="attachment-file-${inquiryId}" class="hidden"></label>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-lg w-10 h-10 flex items-center justify-center ml-1"><i class="fas fa-paper-plane"></i></button>
                        </div></div><div id="attachment-preview" class="text-xs text-zinc-400 mt-2 pl-2"></div></form></div>` : 
            `<div class="p-4 text-center bg-zinc-900 text-zinc-400">This ticket is archived and read-only.</div>`;

        modalTitle.innerHTML = headerHtml;
        modalBody.innerHTML = messagesHtml + formHtml;
        
        let footerHtml = `<div class="flex justify-end w-full"><button onclick="closeInquiryModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded-lg">Close</button></div>`;
        if (inquiry.status !== 'archived') {
            const isClosed = inquiry.status === 'closed';
            const toggleText = isClosed ? 'Re-open Ticket' : 'Close Ticket';
            const toggleClass = isClosed ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700';
            footerHtml = `<div class="flex justify-between items-center w-full">
                <button onclick="toggleInquiryStatus(${inquiryId}, '${inquiry.status}')" class="${toggleClass} text-white font-bold py-2 px-4 rounded-lg">${toggleText}</button>
                <button onclick="closeInquiryModal()" class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-2 px-4 rounded-lg">Cancel</button>
            </div>`;
        }
        modalFooter.innerHTML = footerHtml;
        
        modalBody.querySelector('input[name="message"]')?.addEventListener('input', () => { if (new Date().getTime() - lastAdminTypingTime > 2000) { notifyAdminTyping(inquiryId); lastAdminTypingTime = new Date().getTime(); } });
        document.getElementById(`attachment-file-${inquiryId}`)?.addEventListener('change', function(){ document.getElementById('attachment-preview').textContent = this.files.length > 0 ? `File: ${this.files[0].name}` : ''; });
        
        const chatLog = document.getElementById('chat-log');
        if (chatLog) chatLog.scrollTop = chatLog.scrollHeight;
        if (inquiry.status !== 'archived') inquiryInterval = setInterval(() => fetchNewMessages(inquiryId), 3000);
    } catch(err) { modalBody.innerHTML = `<p class="text-red-500">Error: ${err.message}.</p>`; }
}

async function notifyAdminTyping(inquiryId) {
    const fd = new FormData(); fd.append('action', 'set_admin_typing'); fd.append('inquiry_id', inquiryId);
    try { await fetch('api_handler.php', { method: 'POST', body: fd }); } catch (e) { console.error("Typing notification failed:", e); }
}

async function fetchNewMessages(inquiryId) {
    const fd = new FormData();
    fd.append('action', 'get_new_inquiry_messages');
    fd.append('inquiry_id', inquiryId);
    fd.append('last_message_id', lastMessageId);

    const chatLog = document.getElementById('chat-log');
    if (!chatLog) return;

    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();

        if (result.success) {
            // --- HANDLE NEW MESSAGES ---
            if (result.data.messages.length > 0) {
                result.data.messages.forEach(msg => {
                    const isClient = msg.sender === 'client';
                    const messageEl = document.createElement('div');
                    messageEl.className = `flex ${isClient ? 'justify-start' : 'justify-end'}`;
                    messageEl.innerHTML = `<div class="max-w-xl p-3 rounded-xl break-words ${isClient ? 'bg-zinc-700 text-white' : 'bg-red-700 text-white'}">
                        <div>${parseMessage(msg.message)}</div>
                        <p class="text-xs ${isClient ? 'text-zinc-400' : 'text-red-200'} mt-2 text-right">${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                    </div>`;
                    chatLog.appendChild(messageEl);
                    if (msg.id > lastMessageId) lastMessageId = msg.id;
                });
                chatLog.scrollTop = chatLog.scrollHeight;
            }

            // --- HANDLE TYPING INDICATOR ---
            const indicator = document.getElementById('client-typing-indicator');
            if (result.data.is_client_typing) {
                if (!indicator) {
                    const indicatorEl = document.createElement('div');
                    indicatorEl.id = 'client-typing-indicator';
                    indicatorEl.className = 'flex justify-start';
                    indicatorEl.innerHTML = `<div class="max-w-xl p-3 rounded-xl bg-zinc-700">
                        <div class="text-white typing-dots"><span></span><span></span><span></span></div>
                    </div>`;
                    chatLog.appendChild(indicatorEl);
                    chatLog.scrollTop = chatLog.scrollHeight;
                }
            } else {
                if (indicator) {
                    indicator.remove();
                }
            }
        }
    } catch (e) {
        console.error("Failed to fetch new messages:", e);
    }
}


function closeInquiryModal() { if (inquiryInterval) clearInterval(inquiryInterval); closeModal(); }

async function handleInquirySubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const inquiryId = formData.get('inquiry_id');
    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            Toast.fire({ icon: 'success', title: 'Reply sent!' });
            form.reset();
            document.getElementById('attachment-preview').textContent = '';
            // Immediately fetch new messages after sending to show your own message
            await fetchNewMessages(inquiryId); 
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire('Error', `Could not send reply: ${error.message}`, 'error');
    }
}

async function toggleInquiryStatus(inquiryId, currentStatus) {
    const fd = new FormData(); fd.append('action', 'toggle_inquiry_status'); fd.append('id', inquiryId); fd.append('status', currentStatus);
    try {
        const response = await fetch('api_handler.php', { method: 'POST', body: fd });
        const result = await response.json();
        if (result.success) {
            Toast.fire({ icon: 'success', title: `Ticket status changed!` });
            closeInquiryModal();
            const currentView = document.querySelector('h2').textContent.toLowerCase().includes('archived') ? 'archived' : 'active';
            loadInquiriesView(currentView);
        } else { throw new Error(result.message); }
    } catch (error) { Swal.fire('Error', `Could not change status: ${error.message}`, 'error'); }
}
</script>

