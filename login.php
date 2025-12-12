<?php
include('config.php');
session_start();

$email = $password = "";
$email_err = $password_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {

        $sql = "SELECT id, full_name, email, password_hash, role, status 
                FROM users 
                WHERE email = ? LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result($stmt, $id, $full_name, $email, $hashed_password, $role, $status);
                mysqli_stmt_fetch($stmt);

                // Only approved accounts can login
                if ($status !== 'approved') {
                    $login_msg = '<div class="alert alert-warning text-center">
                                    Your account is not approved yet.
                                  </div>';
                } elseif (password_verify($password, $hashed_password)) {

                    // Save session
                    $_SESSION["id"] = $id;
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["email"] = $email;
                    $_SESSION["role"] = $role;

                    // Redirect according to role
                    if ($role === 'user') {
                        header("Location: user/dashboard.php");
                    } elseif ($role === 'agent') {
                        header("Location: agent/dashboard.php");
                    } else {
                        $login_msg = '<div class="alert alert-danger text-center">
                                        Invalid user role.
                                      </div>';
                    }

                    exit();

                } else {
                    // Wrong password
                    $password_err = "Invalid password.";
                }

            } else {
                $email_err = "No account found with that email.";
            }
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
    <title>User Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="text-center mb-3">User Login</h3>

            <?php echo $login_msg; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" 
                           name="email" 
                           class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" 
                           name="password" 
                           class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <div class="form-group text-center mt-4">
                    <input type="submit" value="Login" class="btn btn-primary px-4">
                </div>
            </form>

            <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

</body>
</html>
