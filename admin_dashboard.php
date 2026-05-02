<?php
session_start();
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM donors WHERE status = 'active') as active_donors,
    (SELECT COUNT(*) FROM ngos WHERE status = 'active') as active_ngos,
    (SELECT COUNT(*) FROM ngos WHERE status = 'pending') as pending_ngos,
    (SELECT COUNT(*) FROM food_donations WHERE status = 'available') as available_donations";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc(); 

// Get recent pending NGOs
$pending_ngos_sql = "SELECT * FROM ngos WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10";
$pending_ngos_result = $conn->query($pending_ngos_sql);
$pending_ngos = $pending_ngos_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - FoodSave</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-green: #28a745;
    --secondary-green: #20c997;
    --light-bg: #f5f9f8;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

body { background-color: var(--light-bg); font-family: 'Roboto', sans-serif; }
.sidebar { background-color: var(--primary-green); min-height: 100vh; color: white; padding: 1rem; }
.sidebar .nav-link { color: white; margin: 0.3rem 0; font-weight: 500; }
.sidebar .nav-link:hover { background: rgba(255,255,255,0.2); border-radius: 8px; }
.card-stats { border-radius: 12px; box-shadow: var(--card-shadow); transition: transform 0.2s; }
.card-stats:hover { transform: translateY(-5px); }
.card-stats i { font-size: 2.5rem; margin-bottom: 10px; }
.card-stats h3 { font-size: 2rem; margin-bottom: 5px; }
.table thead th { background-color: var(--secondary-green); color: #fff; }
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
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card card-stats text-center p-3 bg-white">
                    <i class="fas fa-hand-holding-heart text-success"></i>
                    <h3 class="text-success"><?php echo $stats['active_donors']; ?></h3>
                    <p class="mb-0">Active Donors</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats text-center p-3 bg-white">
                    <i class="fas fa-hands-helping text-success"></i>
                    <h3 class="text-success"><?php echo $stats['active_ngos']; ?></h3>
                    <p class="mb-0">Active NGOs</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats text-center p-3 bg-white">
                    <i class="fas fa-user-clock text-warning"></i>
                    <h3 class="text-warning"><?php echo $stats['pending_ngos']; ?></h3>
                    <p class="mb-0">Pending Approvals</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card card-stats text-center p-3 bg-white">
                    <i class="fas fa-drumstick-bite text-info"></i>
                    <h3 class="text-info"><?php echo $stats['available_donations']; ?></h3>
                    <p class="mb-0">Available Donations</p>
                </div>
            </div>
        </div>

        <!-- Recent NGO Registrations -->
        <section id="ngo-approvals">
            <h4 class="mb-3">Recent NGO Registration Approvals</h4>
            <div class="card">
                <div class="card-body">
                    <?php if (empty($pending_ngos)): ?>
                        <p class="text-muted">No pending NGO registrations.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Organization</th>
                                        <th>Contact</th>
                                        <th>Type</th>
                                        <th>Registration Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_ngos as $ngo): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($ngo['organization_name']); ?></strong><br>
                                                <small class="text-muted">Reg: <?php echo htmlspecialchars($ngo['registration_number']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($ngo['contact_name']); ?><br>
                                                <small><?php echo htmlspecialchars($ngo['email']); ?></small>
                                            </td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $ngo['organization_type'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($ngo['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
