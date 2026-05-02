<?php
session_start();
include 'config.php';

/* ===== DRIVER LOGIN CHECK ===== */
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

$driver_id   = $_SESSION['driver_id'] ?? 0;
$driver_name = $_SESSION['user_name'] ?? 'Driver';

/* ===== GET REQUEST ID ===== */
$request_id = intval($_GET['rid']);
if (!$request_id) {
    die("Invalid request ID.");
}

/* ===== FETCH DATA FOR RECEIPT ===== */
$sql = "
SELECT 
    dr.id AS request_id,
    dr.pickup_date,
    dr.pickup_time,
    dr.pickup_quantity,
    dr.status AS request_status,
    fd.food_name,
    fd.unit AS food_unit,
    d.business_name AS donor_name,
    d.address AS donor_address,
    d.phone AS donor_phone,
    u.name AS ngo_name,
    u.phone AS ngo_contact,
    drv.full_name AS driver_name
FROM donation_requests dr
JOIN driver_assignments da ON da.request_id = dr.id
JOIN food_donations fd ON dr.donation_id = fd.id
JOIN donors d ON fd.donor_id = d.id
JOIN users u ON dr.ngo_id = u.id AND u.user_type='ngo'
JOIN drivers drv ON da.driver_id = drv.id
WHERE dr.id='$request_id' AND da.driver_id='$driver_id'
LIMIT 1
";

$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    die("No pickup found or not authorized.");
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pickup Receipt #<?= $row['request_id']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Arial', sans-serif; background: #f5f5f5; }
.card { max-width: 700px; margin: 40px auto; }
.header { text-align: center; }
.table th, .table td { vertical-align: middle; }
.print-btn { margin-bottom: 20px; }
</style>
</head>
<body>

<div class="container">
<div class="card shadow-sm">
<div class="card-body">

<div class="header mb-4">
    <h3>Pickup Receipt</h3>
    <small class="text-muted">Receipt ID: <?= $row['request_id']; ?></small>
</div>

<button onclick="window.print()" class="btn btn-primary print-btn"><i class="fas fa-print"></i> Print Receipt</button>

<h5 class="mt-4">Pickup Details</h5>
<table class="table table-bordered">
<tr>
    <th>Pickup Date</th>
    <td><?= $row['pickup_date']; ?></td>
</tr>
<tr>
    <th>Pickup Time</th>
    <td><?= $row['pickup_time']; ?></td>
</tr>
<tr>
    <th>Food Item</th>
    <td><?= htmlspecialchars($row['food_name']); ?></td>
</tr>
<tr>
    <th>Quantity</th>
    <td><?= $row['pickup_quantity'] . ' ' . htmlspecialchars($row['food_unit']); ?></td>
</tr>
<tr>
    <th>Status</th>
    <td><?= ucfirst($row['request_status']); ?></td>
</tr>
</table>

<h5 class="mt-4">Donor Information</h5>
<table class="table table-bordered">
<tr>
    <th>Name</th>
    <td><?= htmlspecialchars($row['donor_name']); ?></td>
</tr>
<tr>
    <th>Address</th>
    <td><?= htmlspecialchars($row['donor_address']); ?></td>
</tr>
<tr>
    <th>Phone</th>
    <td><?= htmlspecialchars($row['donor_phone']); ?></td>
</tr>
</table>

<h5 class="mt-4">NGO Information</h5>
<table class="table table-bordered">
<tr>
    <th>Name</th>
    <td><?= htmlspecialchars($row['ngo_name']); ?></td>
</tr>

</table>

<h5 class="mt-4">Driver Information</h5>
<table class="table table-bordered">
<tr>
    <th>Name</th>
    <td><?= htmlspecialchars($row['driver_name']); ?></td>
</tr>
</table>

<div class="mt-5 text-center">
    <small class="text-muted">Generated on <?= date('M d, Y H:i'); ?></small>
</div>

</div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
