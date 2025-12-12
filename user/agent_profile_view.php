<?php
session_start();
include('../config.php');

// Check if user is logged in
if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$user_name = $_SESSION["full_name"];

// Get agent ID from query
$agent_id = $_GET['id'] ?? null;
if (!$agent_id) {
    die("Invalid Agent ID.");
}

// Fetch agent profile
$agent_stmt = mysqli_prepare($conn, "SELECT u.full_name, u.email, a.expertise, a.bio, a.company_name, a.website, a.linkedin, a.experience_years, a.profile_picture FROM users u JOIN agent_profiles a ON u.id = a.user_id WHERE u.id=?");
mysqli_stmt_bind_param($agent_stmt, "i", $agent_id);
mysqli_stmt_execute($agent_stmt);
$agent_result = mysqli_stmt_get_result($agent_stmt);
$agent = mysqli_fetch_assoc($agent_result);
mysqli_stmt_close($agent_stmt);

if (!$agent) {
    die("Agent profile not found.");
}

$success_msg = "";
$error_msg = "";

// Handle feedback form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($message)) {
        $error_msg = "Message cannot be empty.";
    } else {
        $feedback_stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, agent_id, subject, message) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($feedback_stmt, "iiss", $user_id, $agent_id, $subject, $message);
        if (mysqli_stmt_execute($feedback_stmt)) {
            $success_msg = "Feedback submitted successfully!";
        } else {
            $error_msg = "Failed to submit feedback.";
        }
        mysqli_stmt_close($feedback_stmt);
    }
}

// Fetch all feedbacks for this agent
$feedbacks = [];
$fb_stmt = mysqli_prepare($conn, "SELECT f.subject, f.message, f.created_at, u.full_name as user_name FROM feedback f JOIN users u ON f.user_id = u.id WHERE f.agent_id=? ORDER BY f.created_at DESC");
mysqli_stmt_bind_param($fb_stmt, "i", $agent_id);
mysqli_stmt_execute($fb_stmt);
$fb_result = mysqli_stmt_get_result($fb_stmt);
while ($row = mysqli_fetch_assoc($fb_result)) {
    $feedbacks[] = $row;
}
mysqli_stmt_close($fb_stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="card mx-auto mb-4" style="max-width: 600px;">
        <div class="card-body text-center">
            <img src="<?= $agent['profile_picture'] ? '../'.$agent['profile_picture'] : '../images/default-profile.png' ?>" 
                 class="rounded-circle mb-3" style="width:120px; height:120px; object-fit:cover;">
            <h2 class="card-title"><?= htmlspecialchars($agent['full_name']) ?></h2>
            <p><strong>Company:</strong> <?= htmlspecialchars($agent['company_name']) ?></p>
            <p><strong>Website:</strong> <a href="<?= htmlspecialchars($agent['website']) ?>" target="_blank"><?= htmlspecialchars($agent['website']) ?></a></p>
            <p><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($agent['linkedin']) ?>" target="_blank"><?= htmlspecialchars($agent['linkedin']) ?></a></p>
            <p><strong>Expertise:</strong> <?= htmlspecialchars($agent['expertise']) ?></p>
            <p><strong>Experience:</strong> <?= htmlspecialchars($agent['experience_years']) ?> years</p>
            <p><strong>Bio:</strong> <?= htmlspecialchars($agent['bio']) ?></p>
        </div>
    </div>

    <!-- Feedback Form -->
    <div class="card mx-auto mb-4" style="max-width: 600px;">
        <div class="card-body">
            <h4 class="card-title mb-3">Leave Feedback</h4>
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" class="form-control" required></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="card-title mb-3">Feedbacks</h4>
            <?php if ($feedbacks): ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div class="border rounded p-3 mb-2">
                        <p><strong><?= htmlspecialchars($fb['user_name']) ?></strong> <small class="text-muted"><?= $fb['created_at'] ?></small></p>
                        <?php if ($fb['subject']): ?>
                            <p><strong>Subject:</strong> <?= htmlspecialchars($fb['subject']) ?></p>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($fb['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No feedbacks yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
