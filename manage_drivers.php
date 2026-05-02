<?php
// manage_drivers.php
session_start();
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle driver status updates
if ($_POST && isset($_POST['update_driver_status'])) {
    $driver_id = (int)$_POST['driver_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE drivers SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $driver_id);

    if ($stmt->execute()) {
        $message = "Driver status updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Failed to update driver status!";
        $message_type = 'danger';
    }
}

// Fetch all drivers
$drivers_result = $conn->query("SELECT * FROM drivers ORDER BY created_at DESC");
$drivers = $drivers_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Manage Drivers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #001BB7;
    --secondary-color: #0046FF;
    --light-bg: #f5f5f5;
}
body { background: var(--light-bg); font-family: 'Roboto', sans-serif; }
.sidebar-fixed { position: sticky; top:0; height:100vh; overflow-y:auto; background: var(--primary-color); color:white; }
.sidebar-fixed .nav-link { color:white; margin:2px 0; }
.sidebar-fixed .nav-link:hover, .sidebar-fixed .nav-link.active { background: rgba(255,255,255,0.2); border-radius:5px; }
.sidebar-header { padding:1rem; font-weight:bold; display:flex; align-items:center; }
.sidebar-header i { margin-right:0.5rem; }
</style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 p-0">
        <?php include 'admin_sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
        <h2 class="mb-4">Manage Driver Accounts</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Drivers Table -->
        <section>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>License Number</th>
                                <th>Vehicle Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drivers as $driver): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($driver['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                                <td><?php echo htmlspecialchars($driver['license_number']); ?></td>
                                <td><?php echo ucfirst($driver['vehicle_type']); ?></td>
                                <td><span class="badge bg-<?php echo $driver['status']=='active'?'success':($driver['status']=='inactive'?'warning':'danger'); ?>"><?php echo ucfirst($driver['status']); ?></span></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto me-2">
                                            <option value="active" <?php echo $driver['status']=='active'?'selected':''; ?>>Active</option>
                                            <option value="inactive" <?php echo $driver['status']=='inactive'?'selected':''; ?>>Inactive</option>
                                           
                                        </select>
                                        <button type="submit" name="update_driver_status" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
