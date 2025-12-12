<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle status update request
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];

    $query = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: view_users.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">All Users</h2>

    <table class="table table-bordered table-striped table-responsive-sm">
        <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['role'] ?></td>

                <td>
                    <span class="badge 
                        <?= $row['status'] == 'approved' ? 'badge-success' : ($row['status'] == 'pending' ? 'badge-warning' : 'badge-danger'); ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>

                <td><?= $row['created_at'] ?></td>

                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">

                        <select name="status" class="form-control mb-2">
                            <option value="pending"   <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved"  <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected"  <?= $row['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>

                        <button type="submit" name="update_status" class="btn btn-primary btn-sm btn-block mb-1">
                            Update Status
                        </button>

                        <?php if ($row['role'] === 'agent'): ?>
                            <a href="agent_profile_view.php?user_id=<?= $row['id'] ?>" class="btn btn-info btn-sm btn-block">
                                View Profile
                            </a>
                        <?php endif; ?>
                    </form>
                </td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
