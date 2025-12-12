<?php
session_start();
include('../config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM agent_business_resources WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin_business_resources.php");
    exit;
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : "";
$search_sql = $search ? "WHERE abr.title LIKE '%".mysqli_real_escape_string($conn, $search)."%' 
                        OR abr.description LIKE '%".mysqli_real_escape_string($conn, $search)."%' " : "";

$query = "SELECT abr.*, bc.name AS category_name, u.full_name AS agent_name 
          FROM agent_business_resources abr
          LEFT JOIN business_categories bc ON abr.category_id = bc.id
          LEFT JOIN users u ON abr.agent_id = u.id
          $search_sql
          ORDER BY abr.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Business Resources</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">Agent Business Resources</h2>

    <!-- Search Form -->
    <form class="form-inline mb-3" method="GET">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control mr-2" placeholder="Search resources...">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="admin_business_resources.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <!-- Table -->
    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Category</th>
                <th>Agent</th>
                <th>File/Link</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= ucfirst($row['type']) ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['agent_name']) ?></td>
                    <td>
                        <?php if($row['file_url']): ?>
                            <a href="../agent/<?= $row['file_url'] ?>" target="_blank" class="btn btn-sm btn-success mb-1">Download</a>
                        <?php endif; ?>
                        <?php if($row['link']): ?>
                            <a href="<?= $row['link'] ?>" target="_blank" class="btn btn-sm btn-info mb-1">View Link</a>
                        <?php endif; ?>
                    </td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="view_agent_resource.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1">View Details</a>
                        <a href="admin_business_resources.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this resource?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" class="text-center">No resources found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
