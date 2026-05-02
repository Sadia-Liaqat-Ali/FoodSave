<?php
// admin_manage_accounts.php
session_start();
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle donor/NGO status updates
if ($_POST) {
    if (isset($_POST['update_donor_status'])) {
        $donor_id = (int)$_POST['donor_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE donors SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $donor_id);
        if ($stmt->execute()) {
            // Keep users table in sync (users.status doesn't have 'inactive')
            $user_status = $status;
            if ($user_status === 'inactive') {
                $user_status = 'suspended';
            }
            $sync_stmt = $conn->prepare("UPDATE users u JOIN donors d ON u.id = d.user_id SET u.status = ? WHERE d.id = ?");
            $sync_stmt->bind_param("si", $user_status, $donor_id);
            $sync_stmt->execute();

            $message = "Donor status updated successfully!";
            $message_type = 'success';
        }
    }

    if (isset($_POST['update_ngo_status'])) {
        $ngo_id = (int)$_POST['ngo_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE ngos SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $ngo_id);
        if ($stmt->execute()) {
            // Keep users table in sync so login status checks match
            $sync_stmt = $conn->prepare("UPDATE users u JOIN ngos n ON u.id = n.user_id SET u.status = ? WHERE n.id = ?");
            $sync_stmt->bind_param("si", $status, $ngo_id);
            $sync_stmt->execute();

            $message = "NGO status updated successfully!";
            $message_type = 'success';
        }
    }
}

// Fetch all donors
$donors_result = $conn->query("SELECT * FROM donors ORDER BY created_at DESC");
$donors = $donors_result->fetch_all(MYSQLI_ASSOC);

// Fetch all NGOs
$ngos_result = $conn->query("SELECT * FROM ngos ORDER BY created_at DESC");
$ngos = $ngos_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Manage Accounts - FoodSave</title>
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
        <h2 class="mb-4">Manage Donor and NGO Accounts</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Donors Table -->
        <section class="mb-5">
            <h4 class="mb-3">Donors</h4>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donor['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($donor['contact_name']); ?><br><small><?php echo htmlspecialchars($donor['email']); ?></small></td>
                                <td><?php echo ucfirst(str_replace('_',' ',$donor['business_type'])); ?></td>
                                <td><span class="badge bg-<?php echo $donor['status']=='active'?'success':'warning'; ?>"><?php echo ucfirst($donor['status']); ?></span></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto me-2">
                                            <option value="active" <?php echo $donor['status']=='active'?'selected':''; ?>>Active</option>
                                            <option value="inactive" <?php echo $donor['status']=='inactive'?'selected':''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo $donor['status']=='suspended'?'selected':''; ?>>Suspended</option>
                                        </select>
                                        <button type="submit" name="update_donor_status" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- NGOs Table -->
        <section class="mb-5">
            <h4 class="mb-3">NGOs</h4>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ngos as $ngo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ngo['organization_name']); ?></td>
                                <td><?php echo htmlspecialchars($ngo['contact_name']); ?><br><small><?php echo htmlspecialchars($ngo['email']); ?></small></td>
                                <td><?php echo ucfirst(str_replace('_',' ',$ngo['organization_type'])); ?></td>
                                <td><span class="badge bg-<?php echo $ngo['status']=='active'?'success':($ngo['status']=='pending'?'warning':'danger'); ?>"><?php echo ucfirst($ngo['status']); ?></span></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="ngo_id" value="<?php echo $ngo['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto me-2">
                                            <option value="active" <?php echo $ngo['status']=='active'?'selected':''; ?>>Active</option>
                                            <option value="pending" <?php echo $ngo['status']=='pending'?'selected':''; ?>>Pending</option>
                                            <option value="rejected" <?php echo $ngo['status']=='rejected'?'selected':''; ?>>Rejected</option>
                                        </select>
                                        <button type="submit" name="update_ngo_status" class="btn btn-sm btn-primary">Update</button>
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
