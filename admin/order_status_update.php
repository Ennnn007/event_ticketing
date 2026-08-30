<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$orderId = (int)($_POST['order_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';

$allowed = ['pending', 'confirmed', 'done'];
if ($orderId > 0 && in_array($newStatus, $allowed, true)) {
    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $newStatus, $orderId);
    $stmt->execute();
    $stmt->close();
}

header('Location: orders.php');
exit;