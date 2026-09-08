<?php
require 'config.php';
require 'auth.php';
require_login();
header('Content-Type: application/json');

$uid = current_user_id();

// Admins polling on behalf of a specific customer pass user_id explicitly
if (current_user_is_admin() && isset($_GET['user_id'])) {
    $uid = (int)$_GET['user_id'];
}

$afterId = (int)($_GET['after_id'] ?? 0);

$stmt = $conn->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
$stmt->bind_param('ii', $uid, $afterId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($messages);