<?php
session_start();
include 'config.php';

// NGO Login check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'ngo') {
    header("Location: ../login.php");
    exit();
}

$ngo_id = intval($_SESSION['user_id']);

$sql = "SELECT ua.id, ua.alert_message, ua.created_at, fd.food_name, d.business_name AS donor_name
        FROM urgent_alerts ua
        JOIN food_donations fd ON ua.donation_id = fd.id
        JOIN donors d ON ua.donor_id = d.id
        ORDER BY ua.created_at DESC";

$result = $conn->query($sql);
if (!$result) {
    echo "SQL Error: " . $conn->error;
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Urgent Alerts - NGO Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root { --ngo-color:#6f42c1; }
.btn-primary { background:var(--ngo-color); border-color:var(--ngo-color); }
.card-header { background: var(--ngo-color); color:white; }
.alert-instruction { background:#fff3cd; border-left: 5px solid #ffeeba; padding:15px; margin-bottom:20px; border-radius:5px; }
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
        <h2 class="mb-4">Urgent Alerts</h2>

        <div class="alert-instruction">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Automatic alerts for urgent food donations nearing expiration. Please pick them up quickly!
        </div>

        <?php if ($result->num_rows == 0): ?>
            <div class="alert alert-info">No urgent alerts at the moment.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($alert = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-danger">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <i class="fas fa-bell me-2"></i> Urgent Alert
                                <small><?= date("d M Y, H:i", strtotime($alert['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($alert['alert_message']); ?></p>
                                <p><strong>Food:</strong> <?= htmlspecialchars($alert['food_name']); ?></p>
                                <p><strong>Donor:</strong> <?= htmlspecialchars($alert['donor_name']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
