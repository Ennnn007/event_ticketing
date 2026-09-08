<?php
header('Content-Type: application/json');
require '../config.php';

$dbOk = $conn->ping();

echo json_encode([
    'status' => $dbOk ? 'ok' : 'db_error',
    'timestamp' => date('c'),
]);