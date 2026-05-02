<?php
session_start();
include 'config.php';

// Check admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM donation_requests WHERE id=$delete_id");
    $message = "Request deleted successfully!";
    $message_type = 'success';
}

// Handle driver assignment
if ($_POST && isset($_POST['assign_driver'])) {
    $request_id = (int)$_POST['request_id'];
    $driver_id = (int)$_POST['driver_id'];

    // Delete previous assignments for this request
    $conn->query("DELETE FROM driver_assignments WHERE request_id=$request_id");

    $conn->query("INSERT INTO driver_assignments (request_id, driver_id, assigned_at, status) 
                  VALUES ($request_id, $driver_id, NOW(), 'pending')");
    $message = "Driver assigned successfully!";
    $message_type = 'success';
}



// Fetch approved and completed requests with donor, NGO, and food info including donor location
$approved_requests = $conn->query("
SELECT dr.id AS request_id,
       dr.request_date,
       dr.status,
       dr.pickup_quantity,
       fd.food_name,
       fd.unit AS food_unit,
       d.business_name AS donor_name,
       d.contact_name AS donor_contact,
       d.latitude AS donor_lat,
       d.longitude AS donor_lng,
       u.name AS ngo_name,
       u.phone AS ngo_contact
FROM donation_requests dr
LEFT JOIN food_donations fd ON dr.donation_id = fd.id
LEFT JOIN donors d ON fd.donor_id = d.id  -- Join on donors.id (PK)
LEFT JOIN users u ON dr.ngo_id = u.id AND u.user_type='ngo'
WHERE dr.status IN ('approved','completed')
ORDER BY dr.request_date DESC
")->fetch_all(MYSQLI_ASSOC);

$assignments = $conn->query("
SELECT da.id AS assignment_id, 
       da.status AS delivery_status, 
       da.assigned_at,
       da.receipt_file,  -- ADD THIS
       dr.id AS request_id, 
       dr.status AS request_status, 
       dr.pickup_quantity,
       fd.food_name, 
       fd.unit AS food_unit,
       d.business_name AS donor_name, 
       d.contact_name AS donor_contact, 
       d.latitude AS donor_lat, 
       d.longitude AS donor_lng,
       u.name AS ngo_name, 
       u.phone AS ngo_contact,
       drv.full_name AS driver_name
FROM driver_assignments da
LEFT JOIN donation_requests dr ON da.request_id = dr.id
LEFT JOIN food_donations fd ON dr.donation_id = fd.id
LEFT JOIN donors d ON fd.donor_id = d.id
LEFT JOIN users u ON dr.ngo_id = u.id AND u.user_type='ngo'
LEFT JOIN drivers drv ON da.driver_id = drv.id
ORDER BY da.assigned_at DESC
")->fetch_all(MYSQLI_ASSOC);


// Fetch active drivers
$drivers = $conn->query("SELECT * FROM drivers WHERE status='active' ORDER BY full_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Manage Requests - FoodSave</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Roboto', sans-serif; background: #f5f9f8; }
.table td, .table th { vertical-align: middle; }
</style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <div class="col-md-2 p-0">
        <?php include 'admin_sidebar.php'; ?>
    </div>
    <div class="col-md-10 p-4">
        <h2 class="mb-4">Approved & Completed Requests</h2>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

      

        <!-- Approved & Completed Requests Table -->
        <section class="mb-5">
            <h4>Requests</h4>
            <div class="card p-3 mb-4">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor</th>
                            <th>Donor Contact</th>
                            <th>NGO</th>
                            <th>Food</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th>Assign Driver</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($approved_requests as $req): ?>
                        <tr>
                            <td><?php echo $req['request_id']; ?></td>
                            <td><?php echo htmlspecialchars($req['donor_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($req['donor_contact'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($req['ngo_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($req['food_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($req['pickup_quantity'] ?? '') . ' ' . htmlspecialchars($req['food_unit'] ?? ''); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $req['status']=='completed'?'success':'primary'; ?>">
                                    <?php echo ucfirst($req['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($req['request_date'])); ?></td>
                            <td>
                                <?php if($req['status'] != 'completed'): ?>
                                <form method="POST" class="d-flex">
                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                    <select name="driver_id" class="form-select form-select-sm me-2" required
                                            data-donor-lat="<?php echo $req['donor_lat'] ?? ''; ?>"
                                            data-donor-lng="<?php echo $req['donor_lng'] ?? ''; ?>">
                                        <option value="">Select Driver</option>
                                        <?php foreach($drivers as $drv): ?>
                                            <option value="<?php echo $drv['id']; ?>"><?php echo htmlspecialchars($drv['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="assign_driver" class="btn btn-sm btn-primary">Assign</button>
                                </form>
                                <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?delete_id=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($approved_requests)): ?>
                        <tr><td colspan="11" class="text-center text-muted">No approved or completed requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Driver Assignments Table -->
<section>
    <h4>Driver Assignments</h4>
    <div class="card p-3">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Assignment ID</th>
                    <th>Request ID</th>
                    <th>Driver</th>
                    <th>Donor</th>
                    <th>Donor Contact</th>
                    <th>NGO</th>
                    <th>Food</th>
                    <th>Quantity</th>
                    <th>Assigned At</th>
                    <th>Status</th>
                    <th>Pickup Receipt</th> <!-- New Column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach($assignments as $ass): ?>
                <tr>
                    <td><?php echo $ass['assignment_id']; ?></td>
                    <td><?php echo $ass['request_id']; ?></td>
                    <td><?php echo htmlspecialchars($ass['driver_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ass['donor_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ass['donor_contact'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ass['ngo_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ass['food_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ass['pickup_quantity'] ?? '') . ' ' . htmlspecialchars($ass['food_unit'] ?? ''); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($ass['assigned_at'])); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $ass['delivery_status']=='completed'?'success':'warning'; ?>">
                            <?php echo ucfirst($ass['delivery_status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if(!empty($ass['receipt_file'])): ?>
                            <a href="receipts/<?php echo htmlspecialchars($ass['receipt_file']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                View PDF
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Not uploaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($assignments)): ?>
                <tr><td colspan="11" class="text-center text-muted">No driver assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>


    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>