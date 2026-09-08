<?php
require '../config.php';
require '../auth.php';
require_admin();
header('Content-Type: application/json');

$targetUserId = (int)($_POST['user_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($targetUserId > 0 && $message !== '') {
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender, message) VALUES (?, 'admin', ?)");
    $stmt->bind_param('is', $targetUserId, $message);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false]);
}