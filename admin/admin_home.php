<?php
session_start();
include('config.php');

// Redirect if not admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// === Fetch Statistics ===
$total_users = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_agents = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'agent'")->fetch_assoc()['count'];
$pending_agents = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'agent' AND status = 'pending'")->fetch_assoc()['count'];
$approved_agents = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'agent' AND status = 'approved'")->fetch_assoc()['count'];

$total_resources = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources")->fetch_assoc()['count'];
$total_ideas = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE type = 'idea'")->fetch_assoc()['count'];
$total_templates = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE type = 'template'")->fetch_assoc()['count'];
$total_financial = $conn->query("SELECT COUNT(*) AS count FROM agent_business_resources WHERE type = 'financial_resource'")->fetch_assoc()['count'];

$total_forum_posts = $conn->query("SELECT COUNT(*) AS count FROM forum_posts")->fetch_assoc()['count'];
$total_messages = $conn->query("SELECT COUNT(*) AS count FROM messages")->fetch_assoc()['count'];
$total_feedback = $conn->query("SELECT COUNT(*) AS count FROM feedback")->fetch_assoc()['count'];

// Recent Pending Agents (last 10)
$recent_pending = $conn->query("SELECT u.id, u.full_name, u.email, u.created_at 
                                FROM users u 
                                WHERE u.role = 'agent' AND u.status = 'pending' 
                                ORDER BY u.created_at DESC LIMIT 10");

// Recent Resources (last 10)
$recent_resources = $conn->query("SELECT abr.title, abr.type, u.full_name AS agent_name, abr.created_at 
                                  FROM agent_business_resources abr 
                                  JOIN users u ON abr.agent_id = u.id 
                                  ORDER BY abr.created_at DESC LIMIT 10");

// Recent Feedback (last 10)
$recent_feedback = $conn->query("SELECT f.message, f.created_at, u.full_name AS user_name, a.full_name AS agent_name 
                                 FROM feedback f 
                                 JOIN users u ON f.user_id = u.id 
                                 JOIN users a ON f.agent_id = a.id 
                                 ORDER BY f.created_at DESC LIMIT 10");

// === Prepare Chart Data ===
// 1. User & Agent Distribution
$user_roles_labels = ['Regular Users', 'Agents'];
$user_roles_data = [$total_users, $total_agents];

// 2. Resource Type Distribution
$resource_types = $conn->query("SELECT type, COUNT(*) AS count FROM agent_business_resources GROUP BY type");
$resource_labels = [];
$resource_data = [];
while ($row = $resource_types->fetch_assoc()) {
    $resource_labels[] = ucfirst(str_replace('_', ' ', $row['type']));
    $resource_data[] = $row['count'];
}

// 3. Resources Created (Last 7 Days)
$trend_labels = [];
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_labels[] = date('M d', strtotime($date));
    $count = $conn->query("SELECT COUNT(*) AS c FROM agent_business_resources WHERE DATE(created_at) = '$date'")->fetch_assoc()['c'];
    $trend_data[] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Entrepreneur Portal</title>
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
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
            --accent: #198754;
        }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .welcome-card {
            background: linear-gradient(135deg, var(--accent), #0d6efd);
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
<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4">
    <!-- Welcome Card -->
    <div class="card welcome-card mb-5 shadow text-center py-4">
        <h1 class="font-weight-bold">Entrepreneur Portal Admin Center</h1>
        <p class="lead">Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"] ?? 'Admin'); ?></strong>!</p>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-primary"><i class="fas fa-users fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Total Users</h5>
                    <h3 class="stats-value"><?php echo $total_users; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-success"><i class="fas fa-user-tie fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Total Agents</h5>
                    <h3 class="stats-value"><?php echo $total_agents; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-warning"><i class="fas fa-hourglass-half fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Pending Agents</h5>
                    <h3 class="stats-value"><?php echo $pending_agents; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-info"><i class="fas fa-lightbulb fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Business Ideas</h5>
                    <h3 class="stats-value"><?php echo $total_ideas; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-danger"><i class="fas fa-file-alt fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Templates</h5>
                    <h3 class="stats-value"><?php echo $total_templates; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2 mb-3">
            <div class="card card-stats">
                <div class="card-header-icon bg-dark"><i class="fas fa-dollar-sign fa-2x"></i></div>
                <div class="card-body text-center">
                    <h5>Financial Resources</h5>
                    <h3 class="stats-value"><?php echo $total_financial; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>User & Agent Distribution</h5></div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>Resource Types</h5></div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="resourceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h5>New Resources (Last 7 Days)</h5></div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Pending Agents -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h5>Pending Agent Approvals</h5></div>
                <div class="card-body notification-list">
                    <?php if ($recent_pending->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = $recent_pending->fetch_assoc()): ?>
                                <li class="alert alert-warning mb-3">
                                    <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($row['email']); ?></small>
                                    <br><small class="text-muted">Requested: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No pending agents.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Resources -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h5>Latest Business Resources</h5></div>
                <div class="card-body notification-list">
                    <?php if ($recent_resources->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = $recent_resources->fetch_assoc()): ?>
                                <li class="alert alert-success mb-3">
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                    <br><small class="text-capitalize"><?php echo $row['type']; ?> by <?php echo htmlspecialchars($row['agent_name']); ?></small>
                                    <br><small class="text-muted">Posted: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No resources yet.</div>
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
                                    <strong><?php echo htmlspecialchars($row['user_name']); ?></strong> → 
                                    <em><?php echo htmlspecialchars($row['agent_name']); ?></em>
                                    <br><small>"<?php echo htmlspecialchars(substr($row['message'], 0, 60)) . (strlen($row['message']) > 60 ? '...' : ''); ?>"</small>
                                    <br><small class="text-muted">Submitted: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No feedback yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Role Distribution Chart
    new Chart(document.getElementById('roleChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($user_roles_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($user_roles_data); ?>,
                backgroundColor: ['#0d6efd', '#198754'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // Resource Type Chart
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