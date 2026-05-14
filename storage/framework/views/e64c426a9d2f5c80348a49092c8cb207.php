<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="user-id" content="<?php echo e(auth()->id()); ?>">
    <title>Mini Chat</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; height: 100vh; display: flex; }
        .sidebar { width: 220px; background: #1e293b; color: #e2e8f0; display: flex; flex-direction: column; padding: 1rem; }
        .sidebar h2 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #94a3b8; }
        .online-list { list-style: none; flex: 1; }
        .online-list li { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 0.875rem; }
        .online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; flex-shrink: 0; }
        .logout-btn { padding: 8px; background: #334155; border: none; border-radius: 6px; color: #94a3b8; cursor: pointer; font-size: 0.8rem; width: 100%; }
        .chat-area { flex: 1; display: flex; flex-direction: column; }
        .chat-header { background: white; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #1e293b; }
        #messages { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 12px; }
        .message { display: flex; gap: 10px; align-items: flex-start; }
        .message.own { flex-direction: row-reverse; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; flex-shrink: 0; }
        .bubble { max-width: 65%; padding: 10px 14px; border-radius: 12px; background: white; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
        .message.own .bubble { background: #6366f1; color: white; }
        .bubble-name { font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; }
        .message.own .bubble-name { color: #c7d2fe; }
        .bubble-text { font-size: 0.9rem; line-height: 1.5; }
        .bubble-time { font-size: 0.7rem; color: #94a3b8; margin-top: 4px; text-align: right; }
        .message.own .bubble-time { color: #c7d2fe; }
        .chat-input { background: white; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; }
        #message-input { flex: 1; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; outline: none; }
        #message-input:focus { border-color: #6366f1; }
        #send-btn { padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        #send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Online — <span id="online-count">0</span></h2>
    <ul class="online-list" id="online-list"></ul>
    <form method="POST" action="<?php echo e(route('logout')); ?>">
        <?php echo csrf_field(); ?>
        <button class="logout-btn" type="submit">Logout (<?php echo e(auth()->user()->name); ?>)</button>
    </form>
</div>

<div class="chat-area">
    <div class="chat-header">💬 Mini Chat Real-time</div>
    <div id="messages">
        <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="message <?php echo e($msg->user_id === auth()->id() ? 'own' : ''); ?>">
                <div class="avatar"><?php echo e(strtoupper(substr($msg->user->name, 0, 1))); ?></div>
                <div class="bubble">
                    <div class="bubble-name"><?php echo e($msg->user->name); ?></div>
                    <div class="bubble-text"><?php echo e($msg->body); ?></div>
                    <div class="bubble-time"><?php echo e($msg->created_at->diffForHumans()); ?></div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="chat-input">
        <input type="text" id="message-input" placeholder="Ketik pesan..." autocomplete="off" maxlength="1000">
        <button id="send-btn">Kirim</button>
    </div>
</div>

<script>
const currentUserId = parseInt(document.querySelector('meta[name="user-id"]').content);
const messagesEl    = document.getElementById('messages');
const input         = document.getElementById('message-input');
const sendBtn       = document.getElementById('send-btn');
const onlineList    = document.getElementById('online-list');
const onlineCount   = document.getElementById('online-count');

// Scroll ke bawah
function scrollBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}
scrollBottom();

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Render pesan baru
function appendMessage(msg) {
    const isOwn   = parseInt(msg.user.id) === currentUserId;
    const initial = msg.user.name.charAt(0).toUpperCase();
    const div     = document.createElement('div');
    div.className = 'message' + (isOwn ? ' own' : '');
    div.dataset.id = msg.id;  // ← tambahkan ini
    div.innerHTML = `
        <div class="avatar">${initial}</div>
        <div class="bubble">
            <div class="bubble-name">${escapeHtml(msg.user.name)}</div>
            <div class="bubble-text">${escapeHtml(msg.body)}</div>
            <div class="bubble-time">${msg.created_at}</div>
        </div>`;
    messagesEl.appendChild(div);
    scrollBottom();
}

// Kirim pesan
// Kirim pesan
async function sendMessage() {
    const body = input.value.trim();
    if (!body) return;
    sendBtn.disabled = true;
    input.value = '';
    try {
        const res = await fetch('<?php echo e(route("chat.store")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Socket-ID': window.Echo ? window.Echo.socketId() : '',
            },
            body: JSON.stringify({ body }),
        });
        if (!res.ok) throw new Error('Server error ' + res.status);
        const msg = await res.json();
        appendMessage(msg);
    } catch (e) {
        console.error('Gagal kirim:', e);
    } finally {
        sendBtn.disabled = false;
        input.focus();
    }
}

sendBtn.addEventListener('click', sendMessage);
input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

// Tunggu Echo siap lalu daftarkan listener
function initEcho() {
    if (!window.Echo) {
        setTimeout(initEcho, 100);
        return;
    }

    window.Echo.join('chat')
        .here((users) => {
            onlineCount.textContent = users.length;
            onlineList.innerHTML = users.map(u =>
                `<li data-user-id="${u.id}"><span class="online-dot"></span>${u.name}</li>`
            ).join('');
        })
        .joining((user) => {
            const li = document.createElement('li');
            li.dataset.userId = user.id;
            li.innerHTML = `<span class="online-dot"></span>${user.name}`;
            onlineList.appendChild(li);
            onlineCount.textContent = onlineList.children.length;
        })
        .leaving((user) => {
            const li = onlineList.querySelector(`[data-user-id="${user.id}"]`);
            if (li) li.remove();
            onlineCount.textContent = onlineList.children.length;
        })
      .listen('.MessageSent', (e) => {
    if (document.querySelector(`.message[data-id="${e.id}"]`)) return;
    appendMessage(e);
});
}

initEcho();
</script>
</body>
</html><?php /**PATH C:\Users\LENOVO\mini-chat\resources\views/chat.blade.php ENDPATH**/ ?>