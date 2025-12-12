<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$success_msg = $error_msg = "";

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT id, name FROM business_categories ORDER BY name ASC");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $resource_type = $_POST['resource_type'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $category_id = $_POST['category_id'] ?: null;
    $file_url = '';
    $amount = $_POST['amount'] ?: null;
    $source = $_POST['source'] ?? null;
    $link = $_POST['link'] ?? null;

    // Handle file upload
    if (!empty($_FILES['file_url']['name'])) {
        $upload_dir = "uploads/resources/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES["file_url"]["name"]);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES["file_url"]["tmp_name"], $target_file)) {
            $file_url = "uploads/resources/" . $filename;
        } else {
            $error_msg = "Failed to upload file.";
        }
    }

    if (!$error_msg) {
        $stmt = mysqli_prepare($conn, "INSERT INTO agent_business_resources (title, description, file_url, amount, source, link, type, resource_type, status, category_id, agent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssssssss", $title, $description, $file_url, $amount, $source, $link, $type, $resource_type, $status, $category_id, $agent_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Resource added successfully!";
        } else {
            $error_msg = "Failed to add resource.";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Resource</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Add Resource</h3>
            <?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
            <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                <div class="form-group"><label>Type</label>
                    <select name="type" class="form-control" required onchange="toggleResourceType(this.value)">
                        <option value="idea">Idea</option>
                        <option value="template">Template</option>
                        <option value="market_research">Market Research</option>
                        <option value="startup_resource">Startup Resource</option>
                        <option value="financial_resource">Financial Resource</option>
                    </select>
                </div>
                <div class="form-group" id="resourceTypeDiv" style="display:none;">
                    <label>Resource Type</label>
                    <select name="resource_type" class="form-control">
                        <option value="plan">Plan</option>
                        <option value="framework">Framework</option>
                        <option value="opportunity">Opportunity</option>
                    </select>
                </div>
                <div class="form-group" id="statusDiv">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">Select Category</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group"><label>File Upload</label><input type="file" name="file_url" class="form-control-file"></div>
                <div class="form-group" id="financialDiv">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control">
                    <label>Source</label>
                    <input type="text" name="source" class="form-control">
                    <label>Link</label>
                    <input type="text" name="link" class="form-control">
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">Add Resource</button>
                </div>
            </form>
            <a href="view_resources.php" class="btn btn-secondary btn-block">View Resources</a>
        </div>
    </div>
</div>

<script>
function toggleResourceType(type){
    document.getElementById('resourceTypeDiv').style.display = type === 'startup_resource' ? 'block' : 'none';
    document.getElementById('statusDiv').style.display = (type==='idea'||type==='startup_resource') ? 'block' : 'none';
    document.getElementById('financialDiv').style.display = type==='financial_resource' ? 'block' : 'none';
}
toggleResourceType('idea'); // default
</script>
</body>
</html>
