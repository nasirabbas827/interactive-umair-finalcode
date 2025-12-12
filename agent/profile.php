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

// Fetch current profile
$stmt = mysqli_prepare($conn, "SELECT expertise, bio, company_name, website, profile_picture, linkedin, experience_years FROM agent_profiles WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $agent_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $expertise, $bio, $company_name, $website, $profile_picture, $linkedin, $experience_years);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $expertise = $_POST['expertise'];
    $bio = $_POST['bio'];
    $company_name = $_POST['company_name'];
    $website = $_POST['website'];
    $linkedin = $_POST['linkedin'];
    $experience_years = $_POST['experience_years'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "../uploads/profile_pictures/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = time() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = "uploads/profile_pictures/" . $filename;
        } else {
            $error_msg = "Failed to upload profile picture.";
        }
    }

    // Check if profile exists
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM agent_profiles WHERE user_id = ?");
    mysqli_stmt_bind_param($check_stmt, "i", $agent_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        // Update existing profile
        $update_stmt = mysqli_prepare($conn, "UPDATE agent_profiles SET expertise=?, bio=?, company_name=?, website=?, profile_picture=?, linkedin=?, experience_years=? WHERE user_id=?");
        mysqli_stmt_bind_param($update_stmt, "ssssssii", $expertise, $bio, $company_name, $website, $profile_picture, $linkedin, $experience_years, $agent_id);
        if (mysqli_stmt_execute($update_stmt)) $success_msg = "Profile updated successfully!";
        else $error_msg = "Failed to update profile.";
        mysqli_stmt_close($update_stmt);
    } else {
        // Insert new profile
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO agent_profiles (user_id, expertise, bio, company_name, website, profile_picture, linkedin, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "issssssi", $agent_id, $expertise, $bio, $company_name, $website, $profile_picture, $linkedin, $experience_years);
        if (mysqli_stmt_execute($insert_stmt)) $success_msg = "Profile created successfully!";
        else $error_msg = "Failed to create profile.";
        mysqli_stmt_close($insert_stmt);
    }
    mysqli_stmt_close($check_stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body text-center">
            <?php if ($profile_picture): ?>
                <img src="../<?= $profile_picture ?>" alt="Profile Picture" class="rounded-circle mb-3" style="width:120px; height:120px; object-fit:cover;">
            <?php else: ?>
                <img src="../images/default-profile.png" alt="Profile Picture" class="rounded-circle mb-3" style="width:120px; height:120px; object-fit:cover;">
            <?php endif; ?>

            <h2 class="card-title mb-4">Agent Profile</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($company_name) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Website</label>
                        <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($website) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>LinkedIn</label>
                        <input type="text" name="linkedin" class="form-control" value="<?= htmlspecialchars($linkedin) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($experience_years) ?>">
                    </div>
                </div>

                <div class="form-group text-left">
                    <label>Expertise</label>
                    <textarea name="expertise" class="form-control"><?= htmlspecialchars($expertise) ?></textarea>
                </div>

                <div class="form-group text-left">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control"><?= htmlspecialchars($bio) ?></textarea>
                </div>

                <div class="form-group text-left">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control-file">
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary px-4">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
