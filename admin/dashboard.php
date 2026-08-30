<?php
require '../config.php';
require '../auth.php';
require_admin();

// Overview stats
$totals = $conn->query("SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_price),0) AS total_revenue FROM orders")->fetch_assoc();
$today  = $conn->query("SELECT COUNT(*) AS today_orders FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc();
$busiest = $conn->query("
    SELECT e.event_name, SUM(o.quantity) AS sold
    FROM orders o JOIN events e ON e.id = o.event_id
    GROUP BY e.id, e.event_name
    ORDER BY sold DESC LIMIT 1
")->fetch_assoc();

// Revenue over time (last 14 days), for the chart
$trend = $conn->query("
    SELECT DATE(created_at) AS day, SUM(total_price) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
    GROUP BY day ORDER BY day
")->fetch_all(MYSQLI_ASSOC);

// Per-event breakdown, for the bar chart
$perEvent = $conn->query("
    SELECT e.event_name, SUM(o.quantity) AS sold, SUM(o.total_price) AS revenue
    FROM orders o JOIN events e ON e.id = o.event_id
    GROUP BY e.id, e.event_name
    ORDER BY sold DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
require 'partials/header.php';
?>
<h1>Dashboard</h1>

<div class="card-grid">
<div class="card">
<h3>Total bookings</h3>
<p style="font-size:1.8rem;font-weight:700;color:var(--text);"><?= (int)$totals['total_orders'] ?></p>
</div>
<div class="card">
<h3>Total revenue</h3>
<p style="font-size:1.8rem;font-weight:700;color:var(--text);">RM<?= number_format($totals['total_revenue'], 2) ?></p>
</div>
<div class="card">
<h3>Bookings today</h3>
<p style="font-size:1.8rem;font-weight:700;color:var(--text);"><?= (int)$today['today_orders'] ?></p>
</div>
<div class="card">
<h3>Busiest event</h3>
<p style="font-size:1.1rem;font-weight:600;color:var(--text);"><?= htmlspecialchars($busiest['event_name'] ?? 'N/A') ?></p>
<p><?= (int)($busiest['sold'] ?? 0) ?> tickets sold</p>
</div>
</div>

<h2 style="margin-top:32px;">Revenue, last 14 days</h2>
<canvas id="revenueChart" height="80"></canvas>

<h2 style="margin-top:32px;">Bookings per event</h2>
<canvas id="eventsChart" height="100"></canvas>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const trendData = <?= json_encode($trend) ?>;
const perEventData = <?= json_encode($perEvent) ?>;

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: trendData.map(r => r.day),
        datasets: [{
            label: 'Revenue (RM)',
            data: trendData.map(r => r.revenue),
            borderColor: '#4f7cff',
            backgroundColor: 'rgba(79,124,255,0.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('eventsChart'), {
    type: 'bar',
    data: {
        labels: perEventData.map(r => r.event_name),
        datasets: [{
            label: 'Tickets sold',
            data: perEventData.map(r => r.sold),
            backgroundColor: '#4f7cff'
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>

<?php require 'partials/footer.php'; ?>