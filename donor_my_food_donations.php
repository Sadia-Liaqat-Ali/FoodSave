<?php
session_start();
include 'config.php';

// Check login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// ------------------ GET DONOR INFO ------------------
$donor_sql = "SELECT id, business_name FROM donors WHERE email = ?";
$donor_stmt = $conn->prepare($donor_sql);

if (!$donor_stmt) {
    die("Query Failed: " . $conn->error);
}

$donor_stmt->bind_param("s", $_SESSION['user_email']);
$donor_stmt->execute();
$donor = $donor_stmt->get_result()->fetch_assoc();

if (!$donor) { header("Location: login.php"); exit(); }

$donor_id = $donor['id'];
$donor_name = $donor['business_name'];

$message = "";
$type = "";

/* ------------------------- DELETE DONATION --------------------------*/
if (isset($_POST['delete_donation'])) {
    $id = (int) $_POST['donation_id'];

    $d = $conn->prepare("DELETE FROM food_donations WHERE id=? AND donor_id=?");
    $d->bind_param("ii", $id, $donor_id);

    if ($d->execute()) {
        $message = "Donation deleted!";
        $type = "success";
    } else {
        $message = "Delete failed!";
        $type = "danger";
    }
}

/* ------------------------- MARK AS URGENT + ALERT --------------------------*/
if (isset($_POST['mark_urgent'])) {
    $id = (int) $_POST['donation_id'];

    $u = $conn->prepare("UPDATE food_donations SET urgent=1 WHERE id=? AND donor_id=?");
    $u->bind_param("ii", $id, $donor_id);

    if ($u->execute()) {

        // GET FOOD NAME
        $get = $conn->prepare("SELECT food_name FROM food_donations WHERE id=?");
        $get->bind_param("i", $id);
        $get->execute();
        $don = $get->get_result()->fetch_assoc();
        $foodName = $don['food_name'];

        // ALERT MESSAGE
        $alert_msg = "$donor_name marked the donation \"$foodName\" as URGENT.";

        // INSERT ALERT
        $ins = $conn->prepare("INSERT INTO urgent_alerts(donor_id, donation_id, alert_message) VALUES(?,?,?)");
        $ins->bind_param("iis", $donor_id, $id, $alert_msg);
        $ins->execute();

        $message = "Marked as urgent!";
        $type = "success";
    } else {
        $message = "Failed!";
        $type = "danger";
    }
}

/* ------------------------- UPDATE DONATION --------------------------*/
if (isset($_POST['update_donation'])) {
    $sql = "UPDATE food_donations SET food_name=?, category=?, quantity=?, unit=?, expiration_date=?, description=? 
            WHERE id=? AND donor_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssisssii",
        $_POST['food_name'], $_POST['category'], $_POST['quantity'], $_POST['unit'],
        $_POST['expiration_date'], $_POST['description'], $_POST['donation_id'], $donor_id
    );

    if ($stmt->execute()) {
        $message = "Donation updated!";
        $type = "success";
    } else {
        $message = "Update failed!";
        $type = "danger";
    }
}

/* ------------------------- GET DONATIONS --------------------------*/
$q = $conn->prepare("SELECT * FROM food_donations WHERE donor_id=? ORDER BY created_at DESC");
$q->bind_param("i", $donor_id);
$q->execute();
$donations = $q->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
<title>My Donations</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
:root {
    --green:#28a745;
    --light:#d4f4dd;
    --dark:#145214;
}
body{background:var(--light);}
.sidebar{min-height:100vh;background:var(--green);}
.stats-card{border-left:4px solid var(--green);}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<div class="col-md-2 p-0">
    <?php include "sidebar_donor.php"; ?>
</div>

<div class="col-md-10 p-4">

<h3 class="mb-4">My Food Donations</h3>

<?php if ($message): ?>
<div class="alert alert-<?php echo $type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
<div class="card-body">

<?php if (empty($donations)): ?>
<p class="text-muted">No donations yet.</p>

<?php else: ?>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Food Item</th>
<th>Category</th>
<th>Qty</th>
<th>Expires</th>
<th>Urgent</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($donations as $d): ?>
<tr>
<td><?php echo htmlspecialchars($d['food_name']); ?></td>

<td>
<span class="badge bg-info">
<?php echo ucfirst(str_replace('_',' ', $d['category'])); ?>
</span>
</td>

<td><?php echo $d['quantity']." ".$d['unit']; ?></td>

<td><?php echo $d['expiration_date']; ?></td>

<td>
<?php if ($d['urgent'] == 1): ?>
<span class="badge bg-danger">Urgent</span>
<?php else: ?>
<form method="post" class="d-inline">
<input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
<button class="btn btn-sm btn-outline-danger" name="mark_urgent">
<i class="fa-solid fa-exclamation"></i>
</button>
</form>
<?php endif; ?>
</td>

<td>
<span class="badge bg-<?php echo $d['status']=="available"?"success":"secondary"; ?>">
<?php echo ucfirst($d['status']); ?>
</span>
</td>

<td>
<button class="btn btn-sm btn-primary"
onclick='editDonation(<?php echo json_encode($d); ?>)'>
<i class="fa fa-edit"></i>
</button>

<form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
<input type="hidden" name="donation_id" value="<?php echo $d['id']; ?>">
<button class="btn btn-sm btn-danger" name="delete_donation">
<i class="fa fa-trash"></i>
</button>
</form>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
<?php endif; ?>

</div>
</div>

</div>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Edit Donation</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<form method="post">

<input type="hidden" name="donation_id" id="e_id">

<div class="row">
<div class="col-md-6 mb-3">
<label>Food Name</label>
<input type="text" class="form-control" name="food_name" id="e_food">
</div>

<div class="col-md-6 mb-3">
<label>Category</label>
<select class="form-select" name="category" id="e_cat">
<option value="vegetables">Vegetables</option>
<option value="fruits">Fruits</option>
<option value="dairy">Dairy</option>
<option value="meat">Meat</option>
<option value="grains">Grains</option>
<option value="prepared_food">Prepared Food</option>
</select>
</div>
</div>

<div class="row">
<div class="col-md-4 mb-3">
<label>Quantity</label>
<input type="text" class="form-control" name="quantity" id="e_qty">
</div>

<div class="col-md-4 mb-3">
<label>Unit</label>
<input type="text" class="form-control" name="unit" id="e_unit">
</div>

<div class="col-md-4 mb-3">
<label>Expiration Date</label>
<input type="date" class="form-control" name="expiration_date" id="e_date">
</div>
</div>

<div class="mb-3">
<label>Description</label>
<textarea class="form-control" name="description" id="e_desc"></textarea>
</div>

<button class="btn btn-success" name="update_donation">Update</button>

</form>
</div>

</div>
</div>
</div>

<script>
function editDonation(d){
 document.getElementById("e_id").value = d.id;
 document.getElementById("e_food").value = d.food_name;
 document.getElementById("e_cat").value = d.category;
 document.getElementById("e_qty").value = d.quantity;
 document.getElementById("e_unit").value = d.unit;
 document.getElementById("e_date").value = d.expiration_date;
 document.getElementById("e_desc").value = d.description;

 new bootstrap.Modal(document.getElementById("editModal")).show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>