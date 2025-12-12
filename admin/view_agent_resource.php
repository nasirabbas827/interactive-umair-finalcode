<?php
session_start();
include('../config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Check if resource ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_business_resources.php");
    exit;
}

$resource_id = $_GET['id'];

// Fetch resource details
$stmt = mysqli_prepare($conn, "SELECT abr.*, bc.name AS category_name, u.full_name AS agent_name, u.email AS agent_email 
                                FROM agent_business_resources abr
                                LEFT JOIN business_categories bc ON abr.category_id = bc.id
                                LEFT JOIN users u ON abr.agent_id = u.id
                                WHERE abr.id=?");
mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resource = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$resource) {
    echo "<div class='container mt-5'><h3>Resource not found.</h3></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Resource Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">Resource Details</h2>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title"><?= htmlspecialchars($resource['title']) ?></h4>
            <p><strong>Type:</strong> <?= ucfirst($resource['type']) ?><?= $resource['resource_type'] ? ' ('.ucfirst($resource['resource_type']).')' : '' ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($resource['category_name'] ?: '-') ?></p>
            <p><strong>Status:</strong> <?= ucfirst($resource['status']) ?></p>
            <p><strong>Agent:</strong> <?= htmlspecialchars($resource['agent_name']) ?> (<?= htmlspecialchars($resource['agent_email']) ?>)</p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($resource['description'])) ?></p>
            <?php if ($resource['file_url']): ?>
                <a href="../agent/<?= $resource['file_url'] ?>" target="_blank" class="btn btn-success mb-2">Download File</a>
            <?php endif; ?>
            <?php if ($resource['link']): ?>
                <a href="<?= $resource['link'] ?>" target="_blank" class="btn btn-info mb-2">View Link</a>
            <?php endif; ?>
            <p><strong>Created At:</strong> <?= $resource['created_at'] ?></p>
            <p><strong>Last Updated:</strong> <?= $resource['updated_at'] ?></p>

            <a href="admin_business_resources.php" class="btn btn-secondary mt-3">Back to List</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
