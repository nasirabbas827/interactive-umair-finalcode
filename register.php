<?php
include('config.php');

$full_name = $username = $email = $password = $role = "";
$full_name_err = $username_err = $email_err = $password_err = $role_err = "";
$registration_msg = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Full Name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Validate Username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $username_err = "This username is already taken.";
        }
        mysqli_stmt_close($stmt);
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 3) {
        $password_err = "Password must be at least 3 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate Role
    if (empty($_POST["role"])) {
        $role_err = "Please select your user type.";
    } else {
        $role = $_POST["role"];
    }

    // Auto-Assign Status Based on Role
    if ($role === "agent") {
        $status = "pending";        // agents need admin approval
    } else {
        $status = "approved";       // normal users auto-approved
    }

    // Insert if no errors
    if (empty($full_name_err) && empty($username_err) && empty($email_err) && empty($password_err) && empty($role_err)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password_hash, full_name, role, status)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss",
            $username, $email, $hashed_password, $full_name, $role, $status
        );

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success text-center">Registration successful!</div>';
            $full_name = $username = $email = $password = $role = "";
        } else {
            $registration_msg = '<div class="alert alert-danger text-center">Error: Unable to register. Try again.</div>';
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Registration</h3>

            <?php echo $registration_msg; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name"
                           class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $full_name; ?>">
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username"
                           class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                           class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                           value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password"
                           class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <div class="form-group">
                    <label>User Type</label>
                    <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select User Type</option>
                        <option value="user" <?php echo ($role=='user') ? 'selected' : ''; ?>>END User</option>
                        <option value="agent" <?php echo ($role=='agent') ? 'selected' : ''; ?>>Agent</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $role_err; ?></span>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4 mt-3">Register</button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
