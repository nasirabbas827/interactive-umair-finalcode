<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$success_msg = "";
$error_msg = "";

// Handle delete request
if (isset($_GET['delete'])) {
    $feedback_id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM feedback WHERE id = ? AND agent_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $feedback_id, $agent_id);
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Feedback deleted successfully!";
    } else {
        $error_msg = "Failed to delete feedback.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all feedbacks for this agent
$stmt = mysqli_prepare($conn, "
    SELECT f.id, f.subject, f.message, f.created_at, u.full_name AS user_name
    FROM feedback f
    INNER JOIN users u ON f.user_id = u.id
    WHERE f.agent_id = ?
    ORDER BY f.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $agent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$feedbacks = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Feedbacks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">Feedbacks from Users</h2>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <?php if ($feedbacks): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $fb): ?>
                        <tr>
                            <td><?= $fb['id'] ?></td>
                            <td><?= htmlspecialchars($fb['user_name']) ?></td>
                            <td><?= htmlspecialchars($fb['subject']) ?></td>
                            <td><?= htmlspecialchars($fb['message']) ?></td>
                            <td><?= $fb['created_at'] ?></td>
                            <td>
                                <a href="?delete=<?= $fb['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No feedbacks found.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
