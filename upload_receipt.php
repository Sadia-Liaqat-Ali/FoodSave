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

/* ===== HANDLE COMPLETE ===== */
if (isset($_GET['complete_id'])) {
    $aid = intval($_GET['complete_id']); // assignment_id

    // First, get the request_id for this assignment and confirm it belongs to this driver
    $res = mysqli_query($conn, "SELECT request_id FROM driver_assignments WHERE id='$aid' AND driver_id='$driver_id'");
    if($row = mysqli_fetch_assoc($res)) {
        $request_id = $row['request_id'];

        // Update driver_assignments
        mysqli_query($conn, "UPDATE driver_assignments SET status='completed' WHERE id='$aid'");

        // Update donation_requests
        mysqli_query($conn, "UPDATE donation_requests SET status='completed', completed_at=NOW() WHERE id='$request_id'");

        echo "<script>alert('Pickup marked as completed'); window.location='driver_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Invalid assignment'); window.location='driver_dashboard.php';</script>";
        exit();
    }
}

/* ===== HANDLE DELETE ===== */
if (isset($_GET['delete_id'])) {
    $aid = intval($_GET['delete_id']);
    mysqli_query($conn,"DELETE FROM driver_assignments WHERE id='$aid' AND driver_id='$driver_id'");
    echo "<script>alert('Duty deleted'); window.location='driver_dashboard.php';</script>";
    exit();
}

/* ===== HANDLE RECEIPT UPLOAD ===== */
if (isset($_POST['upload_receipt'])) {
    $aid = intval($_POST['assignment_id']);
    if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] == 0) {
        $ext = pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION);
        if ($ext != 'pdf') {
            echo "<script>alert('Only PDF files allowed'); window.location='driver_dashboard.php';</script>";
            exit();
        }

        $upload_dir = 'receipts/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = "receipt_".$aid."_".time().".pdf";
        $filepath = $upload_dir.$filename;

        if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $filepath)) {
            mysqli_query($conn, "UPDATE driver_assignments SET receipt_file='$filename' WHERE id='$aid'");
            echo "<script>alert('Receipt uploaded successfully'); window.location='driver_dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Upload failed'); window.location='driver_dashboard.php';</script>";
            exit();
        }
    }
}

/* ===== FETCH ASSIGNED DUTIES ===== */
$sql = "
SELECT 
    da.id AS assignment_id,
    da.status AS duty_status,
    da.receipt_file,
    dr.id AS request_id,
    dr.pickup_quantity,
    dr.pickup_date,
    dr.pickup_time,
    fd.food_name,
    fd.unit,
    d.business_name AS donor_name,
    d.address AS donor_address,
    d.phone AS donor_phone,
    u.name AS ngo_name
FROM driver_assignments da
JOIN donation_requests dr ON da.request_id = dr.id
JOIN food_donations fd ON dr.donation_id = fd.id
JOIN donors d ON fd.donor_id = d.id
JOIN users u ON dr.ngo_id = u.id AND u.user_type='ngo'
WHERE da.driver_id = '$driver_id'
ORDER BY dr.pickup_date ASC
";

$result = mysqli_query($conn, $sql);
if (!$result) die("Query failed: ".mysqli_error($conn));

/* ===== SUMMARY COUNTS ===== */
$total_assigned = mysqli_num_rows($result);
$total_completed = 0;
$total_pending = 0;
$duties = [];
while ($row = mysqli_fetch_assoc($result)) {
    $duties[] = $row;
    if ($row['duty_status'] === 'completed') $total_completed++;
    else $total_pending++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Driver Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.sidebar-fixed { position: sticky; top: 0; height: 100vh; overflow-y: auto; background: linear-gradient(180deg,#145214,#0f3d0f); box-shadow: 3px 0 8px rgba(0,0,0,0.3);}
.sidebar-fixed h5 { font-weight: 600; letter-spacing: 1px; text-transform: uppercase; text-align: center; }
.nav-link { padding: 12px 15px; font-size: 15px; display: flex; align-items: center; transition: 0.3s; }
.nav-link i { font-size: 18px; }
.nav-link.active { background: rgba(255,255,255,0.25); border-left: 4px solid #fff; font-weight: 600; border-radius: 5px; }
.nav-link:hover { background: rgba(255,255,255,0.2); border-radius: 5px; transform: translateX(5px); }
.sidebar-fixed hr { border-color: rgba(255,255,255,0.3); }
</style>
</head>
<body>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<div class="col-md-2 p-0">
<div class="sidebar-fixed text-white">
<div class="p-3">
<h5><i class="fas fa-truck me-2"></i>Driver Panel</h5>
<hr>
<ul class="nav flex-column">
<li class="nav-item"><a class="nav-link text-white active" href="driver_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
</ul>
</div>
</div>
</div>

<!-- MAIN CONTENT -->
<div class="col-md-10 p-4">
<h3 class="mb-4">Welcome, <?= htmlspecialchars($driver_name); ?></h3>

<!-- SUMMARY CARDS -->
<div class="row mb-4">
<div class="col-md-4"><div class="card text-white bg-primary mb-3"><div class="card-body"><h5 class="card-title">Total Duties</h5><p class="card-text fs-3"><?= $total_assigned; ?></p></div></div></div>
<div class="col-md-4"><div class="card text-white bg-success mb-3"><div class="card-body"><h5 class="card-title">Completed Duties</h5><p class="card-text fs-3"><?= $total_completed; ?></p></div></div></div>
<div class="col-md-4"><div class="card text-white bg-warning mb-3"><div class="card-body"><h5 class="card-title">Pending Duties</h5><p class="card-text fs-3"><?= $total_pending; ?></p></div></div></div>
</div>

<!-- DUTIES TABLE -->
<div class="card shadow-sm">
<div class="card-header bg-success text-white"><i class="fas fa-tasks me-2"></i>Assigned Pickup Duties</div>
<div class="card-body">

<?php if ($total_assigned == 0): ?>
<p class="text-muted">No duties assigned yet.</p>
<?php else: ?>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th><th>Food</th><th>Qty</th><th>Donor</th><th>Address</th><th>Phone</th><th>NGO</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($duties as $row): ?>
<tr>
<td><?= $i++; ?></td>
<td><?= htmlspecialchars($row['food_name']); ?></td>
<td><?= $row['pickup_quantity']." ".$row['unit']; ?></td>
<td><?= htmlspecialchars($row['donor_name']); ?></td>
<td><?= htmlspecialchars($row['donor_address']); ?></td>
<td><?= htmlspecialchars($row['donor_phone']); ?></td>
<td><?= htmlspecialchars($row['ngo_name']); ?></td>
<td><?= $row['pickup_date']; ?></td>
<td><?= $row['pickup_time']; ?></td>
<td><span class="badge bg-<?= $row['duty_status']=='completed'?'success':'warning'; ?>"><?= ucfirst($row['duty_status']); ?></span></td>
<td>
<?php if ($row['duty_status'] != 'completed'): ?>
<a href="?complete_id=<?= $row['assignment_id']; ?>" class="btn btn-sm btn-success mb-1"><i class="fas fa-check"></i></a>
<?php else: ?>
<!-- Upload Receipt Form -->
<?php if (!$row['receipt_file']): ?>
<form method="POST" enctype="multipart/form-data" style="display:inline-block;">
<input type="hidden" name="assignment_id" value="<?= $row['assignment_id']; ?>">
<input type="file" name="receipt_file" accept="application/pdf" required class="form-control form-control-sm mb-1">
<button type="submit" name="upload_receipt" class="btn btn-sm btn-primary mb-1"><i class="fas fa-upload"></i> Upload</button>
</form>
<?php else: ?>
<a href="receipts/<?= htmlspecialchars($row['receipt_file']); ?>" target="_blank" class="btn btn-sm btn-success mb-1"><i class="fas fa-file-pdf"></i> View Receipt</a>
<?php endif; ?>
<?php endif; ?>

<a href="?delete_id=<?= $row['assignment_id']; ?>" onclick="return confirm('Delete this duty?')" class="btn btn-sm btn-danger mb-1"><i class="fas fa-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

</div>
</div>

</div>
</div>
</div>

</body>
</html>
