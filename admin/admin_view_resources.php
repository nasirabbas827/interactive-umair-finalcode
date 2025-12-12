<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("location: admin_login.php");
    exit;
}

// Delete resource
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM educational_resources WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin_view_resources.php?deleted=1");
    exit;
}

// Fetch resources
$result = mysqli_query($conn, "SELECT * FROM educational_resources ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Resources</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <script>
        function confirmDelete(id){
            if(confirm("Are you sure you want to delete this resource?")){
                window.location.href = "admin_view_resources.php?delete_id=" + id;
            }
        }
    </script>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <h2>All Educational Resources</h2>
    <?php if(isset($_GET['deleted'])) echo "<div class='alert alert-success'>Resource deleted successfully!</div>"; ?>
    <a href="admin_add_resource.php" class="btn btn-primary mb-3">Add Resource</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="admin_edit_resource.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
