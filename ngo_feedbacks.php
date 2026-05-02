<?php
session_start();
include 'config.php';

// NGO Login check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'ngo') {
    header("Location: ../login.php");
    exit();
}

$ngo_id = intval($_SESSION['user_id']);

// Handle feedback deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM donation_feedback WHERE id=$delete_id AND request_id IN (SELECT id FROM donation_requests WHERE ngo_id=$ngo_id)");
    echo "<script>alert('Feedback deleted successfully!'); window.location='ngo_feedbacks.php';</script>";
    exit();
}

// Fetch all feedbacks by this NGO
$sql = "SELECT f.id AS feedback_id, f.food_quality, f.system_efficiency, f.created_at,
               dr.id AS request_id, fd.food_name
        FROM donation_feedback f
        JOIN donation_requests dr ON f.request_id = dr.id
        JOIN food_donations fd ON dr.donation_id = fd.id
        WHERE dr.ngo_id = ?
        ORDER BY f.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Feedbacks - NGO Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
:root { --ngo-color:#6f42c1; }
.btn-primary { background:var(--ngo-color); border-color:var(--ngo-color); }
.card-header { background: var(--ngo-color); color:white; }
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
        <h2 class="mb-4">My Feedbacks</h2>

        <?php if($feedbacks->num_rows == 0): ?>
            <div class="alert alert-info">You haven't submitted any feedback yet.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php while($fb = $feedbacks->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            Feedback ID: <?= $fb['feedback_id']; ?> | Food: <?= htmlspecialchars($fb['food_name']); ?>
                            <a href="?delete_id=<?= $fb['feedback_id']; ?>" onclick="return confirm('Delete this feedback?');" class="text-white">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <h6>Food Quality</h6>
                            <p><?= nl2br(htmlspecialchars($fb['food_quality'])); ?></p>
                            <h6>System Efficiency</h6>
                            <p><?= nl2br(htmlspecialchars($fb['system_efficiency'])); ?></p>
                        </div>
                        <div class="card-footer text-muted text-end">
                            Submitted on: <?= date("d M Y, H:i", strtotime($fb['created_at'])); ?>
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
