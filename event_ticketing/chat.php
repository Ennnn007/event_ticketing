<?php
require 'config.php';
require 'auth.php';
require_login();

$uid = current_user_id();
$stmt = $conn->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE user_id = ? ORDER BY id ASC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Support Chat';
require 'partials/header.php';
?>
<div class="form-card" style="max-width:600px;">
<h1>Support Chat</h1>
<div id="chat-box" style="height:360px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:12px;">
<?php foreach ($history as $m): ?>
<div style="margin-bottom:8px;text-align:<?= $m['sender'] === 'user' ? 'right' : 'left' ?>;">
<span style="display:inline-block;padding:8px 12px;border-radius:8px;background:<?= $m['sender'] === 'user' ? 'var(--accent-soft)' : 'var(--surface-alt)' ?>;">
<?= htmlspecialchars($m['message']) ?>
</span>
</div>
<?php endforeach; ?>
</div>
<form id="chat-form">
<input type="text" id="chat-input" placeholder="Type a message..." style="width:75%;">
<button type="submit">Send</button>
</form>
</div>

<script>
let lastId = <?= !empty($history) ? end($history)['id'] : 0 ?>;
const chatBox = document.getElementById('chat-box');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');

function appendMessage(sender, message) {
    const div = document.createElement('div');
    div.style.marginBottom = '8px';
    div.style.textAlign = sender === 'user' ? 'right' : 'left';
    div.innerHTML = '<span style="display:inline-block;padding:8px 12px;border-radius:8px;background:' +
        (sender === 'user' ? 'var(--accent-soft)' : 'var(--surface-alt)') + ';">' +
        message.replace(/</g, '&lt;') + '</span>';
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

form.addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;
    fetch('chat_send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(msg)
    }).then(() => {
        appendMessage('user', msg);
        input.value = '';
    });
});

setInterval(function () {
    fetch('chat_poll.php?after_id=' + lastId)
        .then(r => r.json())
        .then(messages => {
            messages.forEach(m => {
                if (m.sender === 'admin') appendMessage('admin', m.message);
                lastId = m.id;
            });
        });
}, 3000);
</script>

<?php require 'partials/footer.php'; ?>