<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$success_msg = $error_msg = "";

// Handle Create / Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $error_msg = "Category name is required.";
    } else {
        if (!empty($_POST['category_id'])) { // Update
            $category_id = intval($_POST['category_id']);
            $stmt = mysqli_prepare($conn, "UPDATE business_categories SET name=?, description=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $category_id);
            if (mysqli_stmt_execute($stmt)) $success_msg = "Category updated successfully!";
            else $error_msg = "Failed to update category.";
            mysqli_stmt_close($stmt);
        } else { // Create
            $stmt = mysqli_prepare($conn, "INSERT INTO business_categories (name, description) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $name, $description);
            if (mysqli_stmt_execute($stmt)) $success_msg = "Category created successfully!";
            else $error_msg = "Failed to create category.";
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM business_categories WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: business_categories.php");
    exit;
}

// Fetch all categories
$result = mysqli_query($conn, "SELECT * FROM business_categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Categories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container py-5">

    <!-- Form Card -->
    <div class="card mx-auto mb-5" style="max-width: 500px;">
        <div class="card-body">
            <h4 class="card-title mb-4 text-center">Manage Business Category</h4>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="category_id" id="category_id">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter category name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" id="description" class="form-control" placeholder="Enter description">
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" 
                                data-id="<?= $row['id'] ?>" 
                                data-name="<?= htmlspecialchars($row['name']) ?>" 
                                data-desc="<?= htmlspecialchars($row['description']) ?>">Edit</button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Edit button functionality
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('category_id').value = btn.dataset.id;
            document.getElementById('name').value = btn.dataset.name;
            document.getElementById('description').value = btn.dataset.desc;
            window.scrollTo(0,0);
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
