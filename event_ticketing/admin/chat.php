<?php
require '../config.php';
require '../auth.php';
require_admin();

$users = $conn->query("
    SELECT DISTINCT u.id, u.name
    FROM chat_messages cm JOIN users u ON u.id = cm.user_id
    ORDER BY u.name
")->fetch_all(MYSQLI_ASSOC);

$activeUserId = (int)($_GET['user_id'] ?? ($users[0]['id'] ?? 0));

$history = [];
if ($activeUserId > 0) {
    $stmt = $conn->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE user_id = ? ORDER BY id ASC');
    $stmt->bind_param('i', $activeUserId);
    $stmt->execute();
    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$pageTitle = 'Support Chat';
require 'partials/header.php';
?>
<h1>Support Chat</h1>
<div style="display:flex;gap:20px;">
<div style="width:200px;">
<?php foreach ($users as $u): ?>
<a href="chat.php?user_id=<?= (int)$u['id'] ?>" style="display:block;padding:8px;<?= $activeUserId === (int)$u['id'] ? 'font-weight:700;' : '' ?>"><?= htmlspecialchars($u['name']) ?></a>
<?php endforeach; ?>
</div>
<div style="flex:1;">
<div id="chat-box" style="height:360px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:12px;">
<?php foreach ($history as $m): ?>
<div style="margin-bottom:8px;text-align:<?= $m['sender'] === 'admin' ? 'right' : 'left' ?>;">
<span style="display:inline-block;padding:8px 12px;border-radius:8px;background:<?= $m['sender'] === 'admin' ? 'var(--accent-soft)' : 'var(--surface-alt)' ?>;">
<?= htmlspecialchars($m['message']) ?>
</span>
</div>
<?php endforeach; ?>
</div>
<form id="chat-form">
<input type="hidden" id="active-user-id" value="<?= (int)$activeUserId ?>">
<input type="text" id="chat-input" placeholder="Type a reply..." style="width:75%;">
<button type="submit">Send</button>
</form>
</div>
</div>

<script>
let lastId = <?= !empty($history) ? end($history)['id'] : 0 ?>;
const chatBox = document.getElementById('chat-box');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const activeUserId = document.getElementById('active-user-id').value;

function appendMessage(sender, message) {
    const div = document.createElement('div');
    div.style.marginBottom = '8px';
    div.style.textAlign = sender === 'admin' ? 'right' : 'left';
    div.innerHTML = '<span style="display:inline-block;padding:8px 12px;border-radius:8px;background:' +
        (sender === 'admin' ? 'var(--accent-soft)' : 'var(--surface-alt)') + ';">' +
        message.replace(/</g, '&lt;') + '</span>';
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

form.addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;
    fetch('chat_admin_send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + activeUserId + '&message=' + encodeURIComponent(msg)
    }).then(() => {
        appendMessage('admin', msg);
        input.value = '';
    });
});

setInterval(function () {
    fetch('../chat_poll.php?after_id=' + lastId + '&user_id=' + activeUserId)
        .then(r => r.json())
        .then(messages => {
            messages.forEach(m => {
                if (m.sender === 'user') appendMessage('user', m.message);
                lastId = m.id;
            });
        });
}, 3000);
</script>
<?php require 'partials/footer.php'; ?>