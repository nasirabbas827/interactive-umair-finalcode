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
    $stmt = mysqli_prepare($conn, "DELETE FROM feedback WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: view_feedbacks.php");
    exit;
}

// Fetch all feedbacks
$query = "SELECT f.*, u1.full_name AS user_name, u2.full_name AS agent_name 
          FROM feedback f
          LEFT JOIN users u1 ON f.user_id = u1.id
          LEFT JOIN users u2 ON f.agent_id = u2.id
          ORDER BY f.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Feedbacks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">All Feedbacks</h2>

    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Agent</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['agent_name']) ?></td>
                    <td><?= htmlspecialchars($row['subject'] ?: '-') ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="view_feedbacks.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No feedbacks found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
