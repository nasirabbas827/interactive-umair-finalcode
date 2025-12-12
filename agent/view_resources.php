<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$success_msg = $error_msg = "";

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM agent_business_resources WHERE id=? AND agent_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $agent_id);
    if (mysqli_stmt_execute($stmt)) $success_msg = "Resource deleted successfully!";
    else $error_msg = "Failed to delete resource.";
    mysqli_stmt_close($stmt);
}

// Fetch resources
$res = mysqli_query($conn, "SELECT r.*, c.name as category_name FROM agent_business_resources r LEFT JOIN business_categories c ON r.category_id=c.id WHERE r.agent_id=$agent_id ORDER BY r.created_at DESC");

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Resources</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <h3 class="mb-4">Your Resources</h3>
    <?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
    <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
    <a href="add_resource.php" class="btn btn-primary mb-3">Add Resource</a>
    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Category</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['type'] ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="edit_resource.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info mb-1">Edit</a>
                    <a href="view_resources.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this resource?');" class="btn btn-sm btn-danger mb-1">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
