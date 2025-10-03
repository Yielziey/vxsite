<?php
// Set the timezone to Philippines
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../db_connect.php';

$ticket = $_GET['ticket'] ?? null;
if (!$ticket) die("Invalid ticket.");

$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE ticket_number=?");
$stmt->execute([$ticket]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inquiry) die("Ticket not found.");

$isClosed = $inquiry['status'] === 'closed';

// Format the inquiry date for display
$inquiry_date = new DateTime($inquiry['created_at']);
$formatted_date = $inquiry_date->format('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Support Ticket #<?= htmlspecialchars($ticket) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: #18181b; /* zinc-900 */
        background-image: radial-gradient(circle at 1px 1px, #27272a 1px, transparent 0);
        background-size: 25px 25px;
    }
    .font-mono {
        font-family: 'JetBrains Mono', monospace;
    }
    .chat-bubble {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeIn 0.5s forwards;
    }
    @keyframes fadeIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .chat-body::-webkit-scrollbar { width: 8px; }
    .chat-body::-webkit-scrollbar-track { background: #1f2937; }
    .chat-body::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 20px; border: 3px solid #1f2937; }
    
    .typing-dots span {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #9ca3af;
        border-radius: 50%;
        animation: typing-bounce 1.4s infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing-bounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1.0); }
    }
</style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div class="w-full max-w-3xl mx-auto bg-zinc-900/50 backdrop-blur-xl border border-zinc-700/50 rounded-xl shadow-2xl">
    <div class="p-5 border-b border-zinc-800 flex justify-between items-center">
        <div class="flex items-center gap-6">
            <div>
                <p class="text-sm text-red-400 font-semibold"><?= htmlspecialchars($inquiry['topic']) ?></p>
                <h1 class="text-2xl font-bold text-white tracking-wider font-mono">
                    <?= htmlspecialchars($inquiry['ticket_number']) ?>
                </h1>
            </div>
            <div class="text-left text-sm text-zinc-400 space-y-1 border-l border-zinc-700 pl-6">
                <p><i class="fas fa-user fa-fw mr-2 text-zinc-500"></i><?= htmlspecialchars($inquiry['fname'].' '.$inquiry['lname']) ?></p>
                <p><i class="fas fa-calendar-alt fa-fw mr-2 text-zinc-500"></i><?= $formatted_date ?></p>
            </div>
        </div>
        <div>
            <a href="../contact.php" class="text-zinc-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="flex flex-col" style="height: 70vh;">
        <div id="chatBody" class="chat-body flex-1 p-6 overflow-y-auto space-y-4">
            </div>

        <div id="closedBanner" class="bg-red-900/50 border-t border-red-700/30 text-red-300 p-3 text-center text-sm font-semibold <?= $isClosed ? 'block' : 'hidden' ?>">
            <i class="fas fa-lock mr-2"></i> This ticket has been closed. You can no longer send messages.
        </div>
        
        <div id="upload-preview-container" class="px-6 pt-4 border-t border-zinc-800 hidden">
             <div class="bg-zinc-800 p-2 rounded-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-white font-semibold" id="upload-filename">filename.jpg</p>
                    <div class="w-full bg-zinc-700 rounded-full h-1.5 mt-1">
                        <div id="upload-progress" class="bg-red-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <button onclick="cancelUpload()" class="text-zinc-400 hover:text-white">&times;</button>
            </div>
        </div>

        <div class="p-6 border-t border-zinc-800">
            <div class="relative">
                <input type="text" id="messageInput" class="w-full bg-zinc-800 text-white rounded-lg py-3 pl-5 pr-24 focus:outline-none focus:ring-2 focus:ring-red-600 transition-all" placeholder="Type your message..." <?= $isClosed ? 'disabled' : '' ?>>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                     <label for="fileInput" class="text-zinc-400 hover:text-red-500 cursor-pointer transition-colors p-2 text-lg">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="fileInput" class="hidden" <?= $isClosed ? 'disabled' : '' ?>>
                    </label>
                    <button id="sendBtn" class="bg-red-600 hover:bg-red-700 text-white rounded-lg w-10 h-10 flex items-center justify-center transition-colors ml-1" <?= $isClosed ? 'disabled' : '' ?>>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ticket = "<?= addslashes($ticket) ?>";
const chatBody = document.getElementById('chatBody');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const fileInput = document.getElementById('fileInput');
let lastMessageId = 0;
let chatInterval;
let uploadController = null;
let lastTypingTime = 0;
let typingIndicatorAdded = false;

const typingIndicator = document.createElement('div');
typingIndicator.id = 'typingIndicator';
typingIndicator.className = 'chat-bubble flex justify-start';
typingIndicator.innerHTML = `
    <div class="max-w-md p-3 rounded-2xl bg-zinc-700 text-white rounded-bl-lg">
        <div class="typing-dots">
            <span></span><span></span><span></span>
        </div>
    </div>`;

function parseMessage(message) {
    if (!message) return '';
    const fileRegex = /\[file:(.*?)\|(.*?)\]/g;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerText = message;
    
    return tempDiv.innerHTML.replace(fileRegex, (match, fileName, filePath) => {
        let cleanPath = filePath;
        if (cleanPath.startsWith('/vxsite/')) {
            cleanPath = cleanPath.substring('/vxsite/'.length);
        }
        const finalPath = `../${cleanPath}`;
        return `<a href="${finalPath}" target="_blank" class="flex items-center gap-2 bg-zinc-500/50 hover:bg-zinc-500/80 p-2 rounded-lg text-white no-underline transition-colors">
                    <i class="fas fa-file-download"></i><span>${fileName}</span>
                </a>`;
    }).replace(/\n/g, '<br>');
}

async function loadChat() {
    const fd = new FormData();
    fd.append('action', 'load');
    fd.append('ticket', ticket);
    fd.append('last_id', lastMessageId);

    try {
        const response = await fetch('messenger_api.php', { method: 'POST', body: fd });
        const result = await response.json();

        if(result.success) {
            // ADDED: Logic to auto-detect when ticket is closed by an admin
            if (result.data.status === 'closed') {
                if (chatInterval) {
                    clearInterval(chatInterval);
                    chatInterval = null; // Prevent it from being cleared again
                }
                messageInput.disabled = true;
                sendBtn.disabled = true;
                if (fileInput) fileInput.disabled = true;
                document.getElementById('closedBanner').classList.remove('hidden');
            }

            if (result.data.messages.length > 0) {
                result.data.messages.forEach(msg => {
                    const isClient = msg.sender === 'client';
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `chat-bubble flex ${isClient ? 'justify-end' : 'justify-start'}`;
                    
                    messageDiv.innerHTML = `
                        <div class="max-w-xl p-3 rounded-xl break-words ${isClient ? 'bg-red-700 text-white rounded-br-lg' : 'bg-zinc-700 text-white rounded-bl-lg'}">
                            <div>${parseMessage(msg.message)}</div>
                            <p class="text-xs ${isClient ? 'text-red-200' : 'text-zinc-400'} mt-2 text-right">${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                        </div>`;
                        
                    chatBody.appendChild(messageDiv);
                    lastMessageId = msg.id;
                });
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            if (result.data.is_admin_typing) {
                if (!typingIndicatorAdded) {
                    chatBody.appendChild(typingIndicator);
                    typingIndicatorAdded = true;
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            } else {
                if (typingIndicatorAdded) {
                    chatBody.removeChild(typingIndicator);
                    typingIndicatorAdded = false;
                }
            }
        }
    } catch(e) {
        console.error("Failed to load chat:", e);
        if(chatInterval) clearInterval(chatInterval);
    }
}

async function sendMessage() {
    const msg = messageInput.value.trim();
    if (!msg) return;

    messageInput.disabled = true;
    sendBtn.disabled = true;

    const fd = new FormData();
    fd.append('action', 'send');
    fd.append('ticket', ticket);
    fd.append('msg', msg);
    
    try {
        const response = await fetch('messenger_api.php', { method: 'POST', body: fd });
        const result = await response.json();
        if(result.success) {
            messageInput.value = '';
            await loadChat();
        } else {
            alert(result.message || 'Failed to send message.');
        }
    } catch(e) {
        alert('An error occurred while sending the message.');
    } finally {
        if (!<?= $isClosed ? 'true' : 'false' ?>) {
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        }
    }
}

async function uploadFile(file) {
    if(uploadController) return;

    uploadController = new AbortController();
    const signal = uploadController.signal;

    const previewContainer = document.getElementById('upload-preview-container');
    const filenameEl = document.getElementById('upload-filename');
    const progressEl = document.getElementById('upload-progress');
    
    filenameEl.textContent = file.name;
    progressEl.style.width = '0%';
    previewContainer.classList.remove('hidden');

    const fd = new FormData();
    fd.append('action', 'upload');
    fd.append('ticket', ticket);
    fd.append('file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'messenger_api.php', true);
    
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressEl.style.width = percentComplete + '%';
        }
    };

    xhr.onload = async () => {
        if (xhr.status === 200) {
            try {
                const result = JSON.parse(xhr.responseText);
                if(result.success) {
                    await loadChat();
                } else {
                    alert(result.message || 'File upload failed.');
                }
            } catch (e) {
                alert('An error occurred parsing the server response.');
            }
        } else {
                alert('An error occurred during upload.');
        }
        cancelUpload();
    };

    xhr.onerror = () => {
        alert('Upload failed due to a network error.');
        cancelUpload();
    };
    
    signal.addEventListener('abort', () => xhr.abort());

    xhr.send(fd);
}

function cancelUpload() {
    if (uploadController) {
        uploadController.abort();
        uploadController = null;
    }
    document.getElementById('upload-preview-container').classList.add('hidden');
    fileInput.value = '';
}

async function notifyTyping() {
    const fd = new FormData();
    fd.append('action', 'set_typing');
    fd.append('ticket', ticket);
    try {
        await fetch('messenger_api.php', { method: 'POST', body: fd });
    } catch (e) {
        console.error("Could not send typing notification:", e);
    }
}

// Event Listeners
sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

messageInput.addEventListener('input', () => {
    const now = new Date().getTime();
    if (now - lastTypingTime > 2000) { 
        notifyTyping();
        lastTypingTime = now;
    }
});

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        uploadFile(this.files[0]);
    }
});

// Initial Load and Interval
window.onload = () => {
    loadChat().then(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
    });
    if(!<?= $isClosed ? 'true' : 'false' ?>) {
        chatInterval = setInterval(loadChat, 3000);
    }
};
</script>
</body>
</html>
