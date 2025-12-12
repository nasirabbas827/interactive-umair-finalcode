<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$success_msg = $error_msg = "";

// Fetch resource
$id = $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM agent_business_resources WHERE id=? AND agent_id=?");
mysqli_stmt_bind_param($stmt, "ii", $id, $agent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resource = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$resource) {
    header("Location: view_resources.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $resource_type = $_POST['resource_type'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $category_id = $_POST['category_id'] ?: null;
    $file_url = $resource['file_url'];
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
        $stmt = mysqli_prepare($conn, "UPDATE agent_business_resources SET title=?, description=?, file_url=?, amount=?, source=?, link=?, type=?, resource_type=?, status=?, category_id=? WHERE id=? AND agent_id=?");
        mysqli_stmt_bind_param($stmt, "ssssssssssss", $title, $description, $file_url, $amount, $source, $link, $type, $resource_type, $status, $category_id, $id, $agent_id);
        if (mysqli_stmt_execute($stmt)) $success_msg = "Resource updated successfully!";
        else $error_msg = "Failed to update resource.";
        mysqli_stmt_close($stmt);
    }
}

$categories = mysqli_query($conn, "SELECT id, name FROM business_categories ORDER BY name ASC");
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Resource</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Edit Resource</h3>
            <?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
            <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($resource['title']) ?>" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control"><?= htmlspecialchars($resource['description']) ?></textarea></div>
                <div class="form-group"><label>Type</label>
                    <select name="type" class="form-control" onchange="toggleResourceType(this.value)">
                        <option value="idea" <?= $resource['type']=='idea'?'selected':'' ?>>Idea</option>
                        <option value="template" <?= $resource['type']=='template'?'selected':'' ?>>Template</option>
                        <option value="market_research" <?= $resource['type']=='market_research'?'selected':'' ?>>Market Research</option>
                        <option value="startup_resource" <?= $resource['type']=='startup_resource'?'selected':'' ?>>Startup Resource</option>
                        <option value="financial_resource" <?= $resource['type']=='financial_resource'?'selected':'' ?>>Financial Resource</option>
                    </select>
                </div>
                <div class="form-group" id="resourceTypeDiv" style="display:<?= $resource['type']=='startup_resource'?'block':'none' ?>;">
                    <label>Resource Type</label>
                    <select name="resource_type" class="form-control">
                        <option value="plan" <?= $resource['resource_type']=='plan'?'selected':'' ?>>Plan</option>
                        <option value="framework" <?= $resource['resource_type']=='framework'?'selected':'' ?>>Framework</option>
                        <option value="opportunity" <?= $resource['resource_type']=='opportunity'?'selected':'' ?>>Opportunity</option>
                    </select>
                </div>
                <div class="form-group" id="statusDiv" style="display:<?= ($resource['type']=='idea'||$resource['type']=='startup_resource')?'block':'none' ?>;">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= $resource['status']=='draft'?'selected':'' ?>>Draft</option>
                        <option value="published" <?= $resource['status']=='published'?'selected':'' ?>>Published</option>
                    </select>
                </div>
                <div class="form-group"><label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">Select Category</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= $resource['category_id']==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group"><label>File Upload</label><input type="file" name="file_url" class="form-control-file"></div>
                <div class="form-group" id="financialDiv" style="display:<?= $resource['type']=='financial_resource'?'block':'none' ?>;">
                    <label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" value="<?= htmlspecialchars($resource['amount']) ?>">
                    <label>Source</label><input type="text" name="source" class="form-control" value="<?= htmlspecialchars($resource['source']) ?>">
                    <label>Link</label><input type="text" name="link" class="form-control" value="<?= htmlspecialchars($resource['link']) ?>">
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary px-4">Update Resource</button>
                    <a href="view_resources.php" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleResourceType(type){
    document.getElementById('resourceTypeDiv').style.display = type==='startup_resource'?'block':'none';
    document.getElementById('statusDiv').style.display = (type==='idea'||type==='startup_resource')?'block':'none';
    document.getElementById('financialDiv').style.display = type==='financial_resource'?'block':'none';
}
toggleResourceType('<?= $resource['type'] ?>');
</script>
</body>
</html>
