<?php
session_start();
include('../config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    header("Location: view_users.php");
    exit;
}

$agent_id = intval($_GET['user_id']);

// Fetch agent profile
$stmt = mysqli_prepare($conn, "SELECT u.full_name, u.username, u.email, a.expertise, a.bio, a.company_name, a.website, a.profile_picture, a.linkedin, a.experience_years 
                                FROM agent_profiles a 
                                JOIN users u ON a.user_id = u.id 
                                WHERE a.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $agent_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    echo "<div class='alert alert-warning text-center'>Agent profile not found.</div>";
    exit;
}

mysqli_stmt_bind_result($stmt, $full_name, $username, $email, $expertise, $bio, $company_name, $website, $profile_picture, $linkedin, $experience_years);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Profile - <?= htmlspecialchars($full_name) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body text-center">
            <?php if ($profile_picture): ?>
                <img src="../<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="rounded-circle mb-3" style="width:150px; height:150px; object-fit:cover;">
            <?php endif; ?>
            <h2 class="card-title mb-2"><?= htmlspecialchars($full_name) ?></h2>
            <p class="text-muted mb-3">@<?= htmlspecialchars($username) ?> | <?= htmlspecialchars($email) ?></p>

            <div class="text-left">
                <p><strong>Company:</strong> <?= htmlspecialchars($company_name) ?></p>
                <p><strong>Website:</strong> <a href="<?= htmlspecialchars($website) ?>" target="_blank"><?= htmlspecialchars($website) ?></a></p>
                <p><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($linkedin) ?>" target="_blank"><?= htmlspecialchars($linkedin) ?></a></p>
                <p><strong>Experience:</strong> <?= htmlspecialchars($experience_years) ?> year(s)</p>
                <p><strong>Expertise:</strong> <?= nl2br(htmlspecialchars($expertise)) ?></p>
                <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($bio)) ?></p>
            </div>

            <a href="view_users.php" class="btn btn-secondary mt-3">Back to Users</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
