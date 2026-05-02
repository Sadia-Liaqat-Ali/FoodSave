<?php
session_start();
include 'config.php';

// NGO Login check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'ngo') {
    header("Location: ../login.php");
    exit();
}

// Fetch NGO details
$ngo_sql = "SELECT * FROM ngos WHERE email = ?";
$ngo_stmt = $conn->prepare($ngo_sql);
$ngo_stmt->bind_param("s", $_SESSION['user_email']);
$ngo_stmt->execute();
$ngo = $ngo_stmt->get_result()->fetch_assoc();

if (!$ngo) { 
    header("Location: ../login.php"); 
    exit(); 
}

// Handle update form submission
if (isset($_POST['update_info'])) {
    $org_name = $_POST['organization_name'];
    $reg_no   = $_POST['registration_number'];
    $type     = $_POST['organization_type'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];
    $mission  = $_POST['mission'];

    $update_sql = "UPDATE ngos SET organization_name=?, registration_number=?, organization_type=?, phone=?, address=?, mission=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssi", $org_name, $reg_no, $type, $phone, $address, $mission, $ngo['id']);

    $message = $stmt->execute() ? "Organization info updated successfully!" : "Error updating info!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Organization Info - NGO Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root { --ngo-color:#6f42c1; }
.btn-primary { background:var(--ngo-color); border-color:var(--ngo-color); }
</style>
</head>
<body>

<div class="container-fluid">
<div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 p-0">
        <?php include 'sidebar_ngo.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
        <h2 class="mb-4">Edit Organization Info</h2>

        <?php if(isset($message)): ?>
            <div class="alert alert-info"><?= $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Organization Name</label>
                    <input type="text" name="organization_name" class="form-control" value="<?= htmlspecialchars($ngo['organization_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" value="<?= htmlspecialchars($ngo['registration_number']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Organization Type</label>
                    <input type="text" name="organization_type" class="form-control" value="<?= htmlspecialchars($ngo['organization_type']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($ngo['phone']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($ngo['address']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Mission</label>
                <textarea name="mission" class="form-control" rows="4" required><?= htmlspecialchars($ngo['mission']); ?></textarea>
            </div>

            <button type="submit" name="update_info" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Info</button>
        </form>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
