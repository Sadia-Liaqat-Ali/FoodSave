<?php
// admin_manage_foods.php
session_start();
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle food donation status update
if ($_POST && isset($_POST['update_food_status'])) {
    $donation_id = (int)$_POST['donation_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE food_donations SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $donation_id);
    if ($stmt->execute()) {
        $message = "Food donation status updated successfully!";
        $message_type = 'success';
    }
}

// Fetch all food donations
$sql = "SELECT fd.*, d.business_name 
        FROM food_donations fd 
        JOIN donors d ON fd.donor_id = d.id
        ORDER BY fd.created_at DESC";
$result = $conn->query($sql);
$donations = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Manage Food Donations - FoodSave</title>
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
        <h2 class="mb-4">Manage Food Donation Listings</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Donor</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donation['food_name']); ?></td>
                            <td><?php echo htmlspecialchars($donation['business_name']); ?></td>
                            <td><?php echo ucfirst(str_replace('_',' ',$donation['category'])); ?></td>
                            <td><?php echo htmlspecialchars($donation['quantity']) . ' ' . htmlspecialchars($donation['unit']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($donation['expiration_date'])); ?></td>
                            <td><span class="badge bg-<?php echo $donation['status']=='available'?'success':'secondary'; ?>">
                                <?php echo ucfirst($donation['status']); ?></span></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                    <select name="status" class="form-select form-select-sm d-inline w-auto me-2">
                                        <option value="available" <?php echo $donation['status']=='available'?'selected':''; ?>>Available</option>
                                        <option value="unavailable" <?php echo $donation['status']=='unavailable'?'selected':''; ?>>Unavailable</option>
                                        <option value="collected" <?php echo $donation['status']=='collected'?'selected':''; ?>>Collected</option>
                                    </select>
                                    <button type="submit" name="update_food_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($donations)) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No food donations found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
