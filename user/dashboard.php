<?php
session_start();
include('../config.php');

// Check if user is logged in and role is user
if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$user_name = $_SESSION["full_name"] ?? 'User';

// Handle search queries
$category_search = $_GET['category_search'] ?? '';
$mentor_search   = $_GET['mentor_search'] ?? '';
$resource_search = $_GET['resource_search'] ?? '';

// 1. Business Categories
$categories = [];
$cat_query = "SELECT * FROM business_categories WHERE name LIKE ? ORDER BY name ASC";
$cat_stmt = mysqli_prepare($conn, $cat_query);
$like_cat = "%$category_search%";
mysqli_stmt_bind_param($cat_stmt, "s", $like_cat);
mysqli_stmt_execute($cat_stmt);
$cat_result = mysqli_stmt_get_result($cat_stmt);
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}

// 2. Approved Agents/Mentors
$agents = [];
$agent_query = "SELECT u.id, u.full_name, u.email, a.expertise, a.company_name, a.website, a.linkedin, a.experience_years, a.profile_picture
                FROM users u
                JOIN agent_profiles a ON u.id = a.user_id
                WHERE u.role = 'agent' AND u.status = 'approved' 
                AND (u.full_name LIKE ? OR a.expertise LIKE ?)
                ORDER BY u.full_name ASC";
$agent_stmt = mysqli_prepare($conn, $agent_query);
$like_mentor = "%$mentor_search%";
mysqli_stmt_bind_param($agent_stmt, "ss", $like_mentor, $like_mentor);
mysqli_stmt_execute($agent_stmt);
$agent_result = mysqli_stmt_get_result($agent_stmt);
while ($row = mysqli_fetch_assoc($agent_result)) {
    $agents[] = $row;
}

// 3. Published Business Resources
$resources = [];
$res_query = "SELECT r.*, c.name AS category_name, u.full_name AS agent_name
              FROM agent_business_resources r
              LEFT JOIN business_categories c ON r.category_id = c.id
              LEFT JOIN users u ON r.agent_id = u.id
              WHERE r.status = 'published' AND r.title LIKE ?
              ORDER BY r.created_at DESC";
$res_stmt = mysqli_prepare($conn, $res_query);
$like_res = "%$resource_search%";
mysqli_stmt_bind_param($res_stmt, "s", $like_res);
mysqli_stmt_execute($res_stmt);
$res_result = mysqli_stmt_get_result($res_stmt);
while ($row = mysqli_fetch_assoc($res_result)) {
    $resources[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Entrepreneur Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .welcome-card {
            background: linear-gradient(135deg, #0d6efd, #198754);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .card-resource {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-resource:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        .mentor-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4">
    <!-- Welcome -->
    <div class="card welcome-card mb-5 shadow text-center py-4">
        <h1 class="font-weight-bold">Welcome to Your Dashboard</h1>
        <p class="lead">Hello, <strong><?= htmlspecialchars($user_name) ?></strong>! Discover ideas, connect with mentors, and build your business.</p>
    </div>

    <!-- Business Categories -->
    <section class="mb-5">
        <h3 class="mb-3"><i class="fas fa-th-large mr-2"></i>Business Categories</h3>
        <form class="form-inline mb-4" method="GET">
            <input type="text" name="category_search" value="<?= htmlspecialchars($category_search) ?>" 
                   class="form-control mr-2 w-50" placeholder="Search categories...">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="row">
            <?php if ($categories): ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 card-resource">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($cat['name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($cat['description'] ?: 'No description available.') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-info">No categories found.</div></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Business Mentors / Agents -->
    <section class="mb-5">
        <h3 class="mb-3"><i class="fas fa-user-tie mr-2"></i>Business Mentors</h3>
        <form class="form-inline mb-4" method="GET">
            <input type="text" name="mentor_search" value="<?= htmlspecialchars($mentor_search) ?>" 
                   class="form-control mr-2 w-50" placeholder="Search mentors by name or expertise...">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="row">
            <?php if ($agents): ?>
                <?php foreach ($agents as $agent): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 text-center card-resource">
                            <div class="card-body">
                                <img src="<?= $agent['profile_picture'] ? '../'.$agent['profile_picture'] : '../images/default-profile.png' ?>" 
                                     class="rounded-circle mentor-img mb-3" alt="Profile">
                                <h5 class="card-title"><?= htmlspecialchars($agent['full_name']) ?></h5>
                                <p class="text-muted"><strong>Expertise:</strong> <?= htmlspecialchars($agent['expertise'] ?: 'N/A') ?></p>
                                <p class="small text-muted mb-3"><?= htmlspecialchars($agent['company_name'] ?: '') ?></p>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="agent_profile_view.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                    <a href="message.php?with_user=<?= $agent['id'] ?>" class="btn btn-sm btn-success m-2">
                                        <i class="fas fa-comment"></i> Message
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-info">No mentors found.</div></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Business Resources -->
    <section class="mb-5">
        <h3 class="mb-3"><i class="fas fa-lightbulb mr-2"></i>Business Resources</h3>
        <form class="form-inline mb-4" method="GET">
            <input type="text" name="resource_search" value="<?= htmlspecialchars($resource_search) ?>" 
                   class="form-control mr-2 w-50" placeholder="Search ideas, templates, research...">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="row">
            <?php if ($resources): ?>
                <?php foreach ($resources as $res): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 card-resource">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($res['title']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($res['description'], 0, 120)) . (strlen($res['description']) > 120 ? '...' : '') ?></p>
                                <div class="mb-2">
                                    <span class="badge badge-primary mr-1"><?= ucfirst(str_replace('_', ' ', $res['type'])) ?></span>
                                    <?php if ($res['resource_type']): ?>
                                        <span class="badge badge-info"><?= ucfirst($res['resource_type']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="small"><strong>Category:</strong> <?= htmlspecialchars($res['category_name'] ?: 'N/A') ?></p>
                                <p class="small"><strong>By:</strong> <?= htmlspecialchars($res['agent_name']) ?></p>

                                <div class="d-flex gap-2 flex-wrap mt-3">
                                    <?php if ($res['file_url']): ?>
                                        <a href="../agent/<?= $res['file_url'] ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    <?php elseif ($res['link']): ?>
                                        <a href="<?= $res['link'] ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-external-link-alt"></i> View Link
                                        </a>
                                    <?php endif; ?>
                                    <a href="forum.php?resource_id=<?= $res['id'] ?>" class="btn btn-sm btn-info m-2">
                                        <i class="fas fa-comments"></i> View Forum
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-info">No resources found.</div></div>
            <?php endif; ?>
        </div>
    </section>
</div>

</body>
</html>