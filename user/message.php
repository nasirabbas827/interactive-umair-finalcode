<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$role = $_SESSION["role"]; // 'user' or 'agent'

if (!isset($_GET['with_user'])) {
    echo "No conversation selected.";
    exit;
}

$with_user = intval($_GET['with_user']);
$success_msg = "";
$error_msg = "";

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg) {
        $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $with_user, $msg);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Message sent!";
        } else {
            $error_msg = "Failed to send message.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Message cannot be empty.";
    }
}

// Fetch conversation
$stmt = mysqli_prepare($conn, "
    SELECT m.*, u.full_name AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
");
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $with_user, $with_user, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch user info for display
$stmt = mysqli_prepare($conn, "SELECT full_name FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $with_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $with_user_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conversation with <?= htmlspecialchars($with_user_name) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <h3>Conversation with <?= htmlspecialchars($with_user_name) ?></h3>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="border p-3 mb-3" style="max-height:400px; overflow-y:auto; background:#f8f9fa;">
        <?php if ($messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="mb-2">
                    <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?>
                    <small class="text-muted float-right"><?= $msg['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
    </div>

    <form method="post">
        <div class="form-group">
            <textarea name="message" class="form-control" rows="3" placeholder="Type your message here..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">Send Message</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
