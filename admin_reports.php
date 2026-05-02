<?php
session_start();
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$data = [];

if ($report_type && $start_date && $end_date) {
    switch($report_type) {
        case 'donations':
            $sql = "SELECT fd.id, d.business_name AS donor_name, fd.food_name, fd.category, fd.quantity, fd.unit, fd.status, fd.created_at
                    FROM food_donations fd
                    JOIN donors d ON fd.donor_id = d.id
                    WHERE fd.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                    ORDER BY fd.created_at DESC";
            break;

        case 'requests':
            $sql = "SELECT dr.id, d.business_name AS donor_name, n.organization_name AS ngo_name,
                           fd.food_name, dr.pickup_quantity, dr.status, dr.request_date,
                           da.driver_name, da.vehicle_no, da.pickup_location
                    FROM donation_requests dr
                    JOIN food_donations fd ON dr.donation_id = fd.id
                    JOIN donors d ON fd.donor_id = d.id
                    JOIN ngos n ON dr.ngo_id = n.id
                    LEFT JOIN driver_assignments da ON da.request_id = dr.id
                    WHERE dr.request_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                    ORDER BY dr.request_date DESC";
            break;

        case 'deliveries':
            $sql = "SELECT dr.id, d.business_name AS donor_name, n.organization_name AS ngo_name,
                           fd.food_name, dr.pickup_quantity, da.driver_name, da.vehicle_no, da.pickup_location,
                           dr.pickup_date, dr.pickup_time
                    FROM donation_requests dr
                    JOIN food_donations fd ON dr.donation_id = fd.id
                    JOIN donors d ON fd.donor_id = d.id
                    JOIN ngos n ON dr.ngo_id = n.id
                    LEFT JOIN driver_assignments da ON da.request_id = dr.id
                    WHERE dr.status='completed' 
                    AND dr.pickup_date BETWEEN '$start_date' AND '$end_date'
                    ORDER BY dr.pickup_date DESC";
            break;

        default:
            $sql = '';
    }

    if($sql) {
        $result = $conn->query($sql);
        if($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Reports - FoodSave</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-green: #28a745;
    --secondary-green: #20c997;
    --light-bg: #f5f9f8;
    --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
body { background-color: var(--light-bg); font-family: 'Roboto', sans-serif; }
.sidebar { background-color: var(--primary-green); min-height: 100vh; color: white; padding: 1rem; }
.sidebar .nav-link { color: white; margin: 0.3rem 0; font-weight: 500; }
.sidebar .nav-link:hover { background: rgba(255,255,255,0.2); border-radius: 8px; }
.table thead th { background-color: var(--secondary-green); color:#fff; }
.print-btn { margin-bottom: 15px; }
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
        <h2 class="mb-4">Generate Reports</h2>

        <!-- Report Filter -->
        <div class="card mb-4 p-3">
            <form method="POST" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="report_type" class="form-label">Report Type</label>
                    <select name="report_type" id="report_type" class="form-select" required>
                        <option value="">Select Report</option>
                        <option value="donations" <?php if($report_type=='donations') echo 'selected'; ?>>Food Donations</option>
                        <option value="requests" <?php if($report_type=='requests') echo 'selected'; ?>>Requests</option>
                        <option value="deliveries" <?php if($report_type=='deliveries') echo 'selected'; ?>>Successful Deliveries</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-file-alt me-2"></i>Generate</button>
                </div>
            </form>
        </div>

        <!-- Print Button -->
        <?php if($data): ?>
            <button class="btn btn-success print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
        <?php endif; ?>

        <!-- Report Table -->
        <?php if($data): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <?php
                            if($report_type=='donations') {
                                echo '<th>ID</th><th>Donor</th><th>Food</th><th>Category</th><th>Quantity</th><th>Unit</th><th>Status</th><th>Created At</th>';
                            } elseif($report_type=='requests') {
                                echo '<th>ID</th><th>Donor</th><th>NGO</th><th>Food</th><th>Quantity</th><th>Status</th><th>Request Date</th><th>Driver</th><th>Vehicle</th><th>Pickup Location</th>';
                            } elseif($report_type=='deliveries') {
                                echo '<th>ID</th><th>Donor</th><th>NGO</th><th>Food</th><th>Quantity</th><th>Driver</th><th>Vehicle</th><th>Pickup Location</th><th>Pickup Date</th><th>Pickup Time</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $row): ?>
                            <tr>
                                <?php foreach($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($_POST): ?>
            <p class="text-muted">No records found for selected criteria.</p>
        <?php endif; ?>

    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
