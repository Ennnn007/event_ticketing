<?php
header('Content-Type: application/json');
require '../config.php';

$result = $conn->query('SELECT id, event_name, event_date, venue, ticket_price, total_tickets, tickets_sold FROM events ORDER BY event_date');
$events = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'status' => 'ok',
    'count' => count($events),
    'events' => $events,
]);