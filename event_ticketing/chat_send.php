<?php
require 'config.php';
require 'auth.php';
require_login();
header('Content-Type: application/json');

$uid = current_user_id();
$message = trim($_POST['message'] ?? '');

if ($message === '') {
    echo json_encode(['ok' => false, 'error' => 'Empty message']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender, message) VALUES (?, 'user', ?)");
$stmt->bind_param('is', $uid, $message);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);