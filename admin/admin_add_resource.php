<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("location: admin_login.php");
    exit;
}

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content = $_POST['content'];
    $video_url = $_POST['video_url'];
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : NULL;
    $location = $_POST['location'];
    $link = $_POST['link'];
    $type = $_POST['type'];

    $stmt = mysqli_prepare($conn, "INSERT INTO educational_resources (title, description, content, video_url, event_date, location, link, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssssss", $title, $description, $content, $video_url, $event_date, $location, $link, $type);

    if (mysqli_stmt_execute($stmt)) $success_msg = "Resource added successfully!";
    else $error_msg = "Failed to add resource.";

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Educational Resource</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container py-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Add Resource</h2>
    <?php if($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
    <?php if($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="tutorial">Tutorial</option>
                <option value="event">Event</option>
            </select>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Content (Tutorial Only)</label>
            <textarea name="content" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Video URL (Tutorial Only)</label>
            <input type="text" name="video_url" class="form-control">
        </div>

        <div class="form-group">
            <label>Event Date (Event Only)</label>
            <input type="datetime-local" name="event_date" class="form-control">
        </div>

        <div class="form-group">
            <label>Location (Event Only)</label>
            <input type="text" name="location" class="form-control">
        </div>

        <div class="form-group">
            <label>Link (Event Only)</label>
            <input type="text" name="link" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Add Resource</button>
        <a href="admin_view_resources.php" class="btn btn-info">View Resources</a>
    </form>
</div>
</div>
</div>
</body>
</html>
