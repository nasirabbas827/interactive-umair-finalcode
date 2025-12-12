<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("location: admin_login.php");
    exit;
}

$error_msg = "";
$success_msg = "";

// Get resource data
if (!isset($_GET['id'])) {
    header("Location: admin_view_resources.php");
    exit;
}
$resource_id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM educational_resources WHERE id=$resource_id");
$resource = mysqli_fetch_assoc($result);

if (!$resource) {
    header("Location: admin_view_resources.php");
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content = $_POST['content'];
    $video_url = $_POST['video_url'];
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : NULL;
    $location = $_POST['location'];
    $link = $_POST['link'];
    $type = $_POST['type'];

    $stmt = mysqli_prepare($conn, "UPDATE educational_resources SET title=?, description=?, content=?, video_url=?, event_date=?, location=?, link=?, type=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssssi", $title, $description, $content, $video_url, $event_date, $location, $link, $type, $resource_id);

    if (mysqli_stmt_execute($stmt)) $success_msg = "Resource updated successfully!";
    else $error_msg = "Failed to update resource.";

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Resource</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container py-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Resource</h2>
    <?php if($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
    <?php if($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($resource['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="tutorial" <?= $resource['type']=='tutorial'?'selected':'' ?>>Tutorial</option>
                <option value="event" <?= $resource['type']=='event'?'selected':'' ?>>Event</option>
            </select>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($resource['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Content (Tutorial Only)</label>
            <textarea name="content" class="form-control"><?= htmlspecialchars($resource['content']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Video URL (Tutorial Only)</label>
            <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($resource['video_url']) ?>">
        </div>

        <div class="form-group">
            <label>Event Date (Event Only)</label>
            <input type="datetime-local" name="event_date" class="form-control" value="<?= $resource['event_date'] ? date('Y-m-d\TH:i', strtotime($resource['event_date'])) : '' ?>">
        </div>

        <div class="form-group">
            <label>Location (Event Only)</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($resource['location']) ?>">
        </div>

        <div class="form-group">
            <label>Link (Event Only)</label>
            <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($resource['link']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Resource</button>
        <a href="admin_view_resources.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</div>
</div>
</body>
</html>
