<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$user_name = $_SESSION["full_name"] ?? "User";

// Get resource ID from GET
if (!isset($_GET['resource_id'])) {
    header("location: user_dashboard.php");
    exit;
}

$resource_id = intval($_GET['resource_id']);
$success_msg = "";
$error_msg = "";

// Fetch resource details
$stmt = mysqli_prepare($conn, "SELECT r.*, c.name AS category_name, u.full_name AS agent_name 
                              FROM agent_business_resources r
                              LEFT JOIN business_categories c ON r.category_id = c.id
                              LEFT JOIN users u ON r.agent_id = u.id
                              WHERE r.id = ?");
mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resource = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle new forum message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message) {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO forum_posts (resource_id, message, user_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "isi", $resource_id, $message, $user_id);
        if (mysqli_stmt_execute($insert_stmt)) {
            $success_msg = "Message posted successfully!";
        } else {
            $error_msg = "Failed to post message.";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $error_msg = "Message cannot be empty.";
    }
}

// Fetch all forum messages
$stmt = mysqli_prepare($conn, "SELECT f.*, u.full_name FROM forum_posts f 
                              LEFT JOIN users u ON f.user_id = u.id 
                              WHERE f.resource_id = ? ORDER BY f.created_at ASC");
mysqli_stmt_bind_param($stmt, "i", $resource_id);
mysqli_stmt_execute($stmt);
$messages_result = mysqli_stmt_get_result($stmt);
$messages = mysqli_fetch_all($messages_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resource Forum</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($resource['title']) ?></h3>
            <p><strong>Category:</strong> <?= htmlspecialchars($resource['category_name'] ?: 'N/A') ?></p>
            <p><strong>Type:</strong> <?= ucfirst(str_replace('_',' ', $resource['type'])) ?><?= $resource['resource_type'] ? ' ('.ucfirst($resource['resource_type']).')' : '' ?></p>
            <p><strong>Agent:</strong> <?= htmlspecialchars($resource['agent_name']) ?></p>
            <p><?= htmlspecialchars($resource['description']) ?></p>
            <?php if ($resource['file_url']): ?>
                <a href="../agent/<?= $resource['file_url'] ?>" target="_blank" class="btn btn-success btn-sm">Download</a>
            <?php elseif ($resource['link']): ?>
                <a href="<?= $resource['link'] ?>" target="_blank" class="btn btn-success btn-sm">View Link</a>
            <?php endif; ?>
        </div>
    </div>

    <h4 class="mb-3">Forum Messages</h4>
    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <form method="post">
            <div class="form-group">
                <textarea name="message" class="form-control" placeholder="Add your message..." rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Message</button>
        </form>
    </div>

    <div class="list-group">
        <?php if ($messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="list-group-item">
                    <p><?= htmlspecialchars($msg['message']) ?></p>
                    <small>By <?= htmlspecialchars($msg['full_name']) ?> on <?= $msg['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet. Be the first to post!</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
