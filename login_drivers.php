<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

if (isset($_POST['login'])) {

    $license = mysqli_real_escape_string($conn, $_POST['license_number']);
    $name    = mysqli_real_escape_string($conn, $_POST['full_name']);

    $sql = "SELECT * FROM drivers 
            WHERE license_number='$license' 
            AND full_name='$name' 
            AND status='active'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $driver = mysqli_fetch_assoc($result);

        // ✅ SAVE CORRECT SESSION DATA
        $_SESSION['driver_id']   = $driver['id'];
        $_SESSION['user_name'] = $driver['full_name'];
        $_SESSION['user_type'] = 'driver';

        header("Location: driver_dashboard.php");
        exit();
    } else {
        $message = "Invalid name or license number, or your account is not active.";
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Driver Login - FoodSave</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
:root{
    --primary:#6f42c1;
    --bg:#d4f4dd;
}
body{
    background:var(--bg);
    font-family:Roboto,sans-serif;
}
.login-box{
    max-width:400px;
    margin:0 auto;
}
.card{
    border-radius:20px;
    border:none;
    box-shadow:0 15px 35px rgba(0,0,0,0.1);
}
.card-header{
    background:linear-gradient(135deg,var(--primary),#7C3AED);
    color:#fff;
    text-align:center;
    padding:2rem;
    border-radius:20px 20px 0 0;
}
.form-control{
    border-radius:10px;
}
.btn-primary{
    background:var(--primary);
    border:none;
    border-radius:50px;
}
.back-link{
    position:absolute;
    top:20px;
    left:20px;
    color:white;
    text-decoration:none;
}
</style>
</head>

<body>

<a href="index.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Home
</a>

<div class="container mt-5">
<div class="login-box">

<?php if ($message): ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-truck me-2"></i>Driver Login</h3>
        <p class="mb-0">Login using name & license number</p>
    </div>

    <div class="card-body p-4">

        <div class="alert alert-info">
            <strong>Note:</strong><br>
            Admin, NGO, and Donor should login from main login page.<br>
            Drivers must login from this page.
        </div>

        <form method="post">

            <div class="mb-3">
                <label>Full Name</label>
                  <input type="text" name="full_name" class="form-control" required
                      value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label>License Number</label>
                  <input type="text" name="license_number" class="form-control" required
                      value="<?php echo isset($_POST['license_number']) ? htmlspecialchars($_POST['license_number']) : ''; ?>">
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>

        </form>

        <hr>

        <div class="text-center">
            <p class="mb-1">Not registered?</p>
            <a href="register_drivers.php" class="btn btn-outline-dark btn-sm">
                Register as Driver
            </a>
        </div>

    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
