<?php
session_start();
include('../config.php');

// Check if user is logged in and role is agent
if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$agent_name = $_SESSION["full_name"] ?? 'Agent';

// === Fetch Statistics for this Agent ===
$total_resources = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id")->fetch_assoc()['count'];
$total_ideas = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id AND type = 'idea'")->fetch_assoc()['count'];
$total_templates = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id AND type = 'template'")->fetch_assoc()['count'];
$total_market_research = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id AND type = 'market_research'")->fetch_assoc()['count'];
$total_financial = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id AND type = 'financial_resource'")->fetch_assoc()['count'];

$total_forum_posts = $conn->query("SELECT COUNT(*) AS count FROM forum_posts fp 
                                  JOIN agent_business_resources abr ON fp.resource_id = abr.id 
                                  WHERE abr.agent_id = $agent_id")->fetch_assoc()['count'];

$total_messages = $conn->query("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = $agent_id OR sender_id = $agent_id")->fetch_assoc()['count'];
$total_feedback = $conn->query("SELECT COUNT(*) AS count FROM feedback WHERE agent_id = $agent_id")->fetch_assoc()['count'];

// Recent Resources Uploaded by Agent (last 8)
$recent_resources = $conn->query("SELECT title, type, created_at 
                                  FROM agent_business_resources 
                                  WHERE agent_id = $agent_id 
                                  ORDER BY created_at DESC LIMIT 8");

// Recent Feedback Received (last 6)
$recent_feedback = $conn->query("SELECT f.message, f.created_at, u.full_name AS user_name 
                                 FROM feedback f 
                                 JOIN users u ON f.user_id = u.id 
                                 WHERE f.agent_id = $agent_id 
                                 ORDER BY f.created_at DESC LIMIT 6");

// Recent Messages (last 6)
$recent_messages = $conn->query("SELECT m.message, m.created_at, u.full_name AS sender_name 
                                 FROM messages m 
                                 JOIN users u ON m.sender_id = u.id 
                                 WHERE m.receiver_id = $agent_id 
                                 ORDER BY m.created_at DESC LIMIT 6");

// === Chart Data ===
// 1. Agent's Resource Types
$resource_types = $conn->query("SELECT type, COUNT(*) AS count FROM agent_business_resources WHERE agent_id = $agent_id GROUP BY type");
$resource_labels = [];
$resource_data = [];
while ($row = $resource_types->fetch_assoc()) {
    $resource_labels[] = ucfirst(str_replace('_', ' ', $row['type']));
    $resource_data[] = $row['count'];
}

// 2. Resources Uploaded (Last 7 Days)
$trend_labels = [];
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('M d', strtotime($date));
    $count = $conn->query("SELECT COUNT(*) AS c FROM agent_business_resources WHERE agent_id = $agent_id AND DATE(created_at) = '$date'")->fetch_assoc()['c'];
    $trend_data[] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard | Entrepreneur Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #0d6efd;
            --success: #198754;
            --info: #0dcaf0;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #212529;
        }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .welcome-card {
            background: linear-gradient(135deg, #198754, #0d6efd);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .card-stats {
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .card-stats:hover { transform: translateY(-8px); }
        .card-header-icon {
            position: absolute;
            top: -20px;
            right: 20px;
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        .stats-value { font-size: 2.3rem; font-weight: 700; color: #212529; }
        .notification-list { max-height: 380px; overflow-y: auto; }
        .chart-container { position: relative; height: 320px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4">
    <!-- Welcome Card -->
    <div class="card welcome-card mb-5 shadow text-center py-4">
        <h1 class="font-weight-bold">Agent Dashboard</h1>
        <p class="lead">Welcome back, <strong><?= htmlspecialchars($agent_name) ?></strong>! Inspire and connect with aspiring entrepreneurs.</p>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-success"><i class="fas fa-lightbulb fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Business Ideas</h5>
                    <h3 class="stats-value"><?php echo $total_ideas; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-primary"><i class="fas fa-file-alt fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Templates</h5>
                    <h3 class="stats-value"><?php echo $total_templates; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-info"><i class="fas fa-chart-line fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Market Research</h5>
                    <h3 class="stats-value"><?php echo $total_market_research; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-warning"><i class="fas fa-dollar-sign fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Financial Resources</h5>
                    <h3 class="stats-value"><?php echo $total_financial; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-dark"><i class="fas fa-comments fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Forum Posts</h5>
                    <h3 class="stats-value"><?php echo $total_forum_posts; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-danger"><i class="fas fa-star fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Feedback Received</h5>
                    <h3 class="stats-value"><?php echo $total_feedback; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>Your Resource Distribution</h5></div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="resourceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>Resources Uploaded (Last 7 Days)</h5></div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>Recent Activity Overview</h5></div>
                <div class="card-body text-center">
                    <h5 class="mb-4">Total Resources: <strong><?php echo $total_resources; ?></strong></h5>
                    <h5>Messages: <strong><?php echo $total_messages; ?></strong></h5>
                    <h5 class="mt-3">Feedback: <strong><?php echo $total_feedback; ?></strong></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Resources -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h5>Latest Resources You've Shared</h5></div>
                <div class="card-body notification-list">
                    <?php if ($recent_resources->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = $recent_resources->fetch_assoc()): ?>
                                <li class="alert alert-success mb-3">
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                    <br><small class="text-capitalize"><?php echo $row['type']; ?></small>
                                    <br><small class="text-muted">Uploaded: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">You haven't shared any resources yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h5>Recent Feedback</h5></div>
                <div class="card-body notification-list">
                    <?php if ($recent_feedback->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = $recent_feedback->fetch_assoc()): ?>
                                <li class="alert alert-info mb-3">
                                    <strong><?php echo htmlspecialchars($row['user_name']); ?></strong>
                                    <br><small>"<?php echo htmlspecialchars(substr($row['message'], 0, 70)) . (strlen($row['message']) > 70 ? '...' : ''); ?>"</small>
                                    <br><small class="text-muted">Received: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No feedback yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h5>Recent Messages</h5></div>
                <div class="card-body notification-list">
                    <?php if ($recent_messages->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = $recent_messages->fetch_assoc()): ?>
                                <li class="alert alert-primary mb-3">
                                    <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                                    <br><small>"<?php echo htmlspecialchars(substr($row['message'], 0, 70)) . (strlen($row['message']) > 70 ? '...' : ''); ?>"</small>
                                    <br><small class="text-muted">Received: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No new messages.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Resource Distribution Chart
    new Chart(document.getElementById('resourceChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($resource_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($resource_data); ?>,
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // Trend Chart (Last 7 Days)
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trend_labels); ?>,
            datasets: [{
                label: 'New Resources',
                data: <?php echo json_encode($trend_data); ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.2)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
</script>
</body>
</html>