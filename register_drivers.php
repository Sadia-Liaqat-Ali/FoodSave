<?php
session_start();
include 'config.php'; // database connection

$message = '';
$message_type = '';

if($_POST && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $license_number = trim($_POST['license_number']);
    $vehicle_type = trim($_POST['vehicle_type']);

    // Basic validation
    if(empty($full_name) || empty($phone) || empty($license_number) || empty($vehicle_type)) {
        $message = "Please fill in all required fields.";
        $message_type = 'danger';
    } else {
        $sql = "INSERT INTO drivers (full_name, phone, license_number, vehicle_type, status, created_at) 
                VALUES ('$full_name', '$phone', '$license_number', '$vehicle_type', 'inactive', NOW())";
        if(mysqli_query($conn, $sql)) {
            $message = "Driver registration successful!";
            $message_type = 'success';
            $_POST = array(); // clear form
        } else {
            $message = "Registration failed. Please try again.";
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Driver</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #6f42c1;
    --secondary-color: #28a745;
    --accent-color: #17a2b8;
    --dark-bg: #145214;
    --light-bg: #d4f4dd;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Roboto', sans-serif; line-height:1.6; background-color: var(--light-bg); color:#333; transition: all 0.3s ease; }
@media (prefers-color-scheme: dark) {
    body { background: linear-gradient(135deg, #4a49cc, #5a2d81); }
    .card { background-color: #2c2c2c; border-color: #444; color: #ffffff; }
    .form-control { background-color: #3a3a3a; border-color: #555; color: #ffffff; }
    .form-control:focus { background-color: #3a3a3a; border-color: var(--primary-color); color: #ffffff; }
}
.registration-container { max-width: 700px; margin: 40px auto; padding: 20px; }
.card { border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden; }
.card-header { background: linear-gradient(135deg, var(--primary-color), #6f42c1); color: white; padding: 2rem; text-align: center; border: none; }
.card-body { padding: 2.5rem; }
.form-control, .form-select { border-radius: 10px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 16px; }
.btn-primary { background: linear-gradient(135deg, var(--primary-color), #7C3AED); border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s ease; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(93, 92, 222, 0.3); }
.required { color: #dc3545; }
@media (max-width: 768px) { .card-body { padding: 1.5rem; } }
</style>
</head>
<body>

<div class="container registration-container">
    <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-truck me-2"></i>Register Driver</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" class="form-control" required 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-control" required 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">License Number <span class="required">*</span></label>
                        <input type="text" name="license_number" class="form-control" required 
                               value="<?php echo isset($_POST['license_number']) ? htmlspecialchars($_POST['license_number']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Type <span class="required">*</span></label>
                        <select name="vehicle_type" class="form-select" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="bike" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type']=='bike')?'selected':''; ?>>Bike</option>
                            <option value="car" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type']=='car')?'selected':''; ?>>Car</option>
                            <option value="van" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type']=='van')?'selected':''; ?>>Van</option>
                            <option value="truck" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type']=='truck')?'selected':''; ?>>Truck</option>
                        </select>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" name="register" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Register Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
