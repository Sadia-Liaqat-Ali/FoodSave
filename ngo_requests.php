<?php
session_start();
include 'config.php';

// NGO Login check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'ngo') {
    header("Location: ../login.php");
    exit();
}

// Feedback submission
if (isset($_POST['submit_feedback'])) {
    $request_id = intval($_POST['request_id']);
    $food_quality = $_POST['food_quality'];
    $system_efficiency = $_POST['system_efficiency'];

    $sql = "INSERT INTO donation_feedback (request_id, food_quality, system_efficiency) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $request_id, $food_quality, $system_efficiency);
    $stmt->execute();

    echo "<script>alert('Feedback submitted successfully!'); window.location='ngo_requests.php';</script>";
    exit();
}

// Fetch NGO requests including the receipt of completed driver assignments
$ngo_id = intval($_SESSION['user_id']);
$sql = "
SELECT dr.id AS request_id,
       dr.status AS request_status,
       dr.request_date,
       fd.food_name,
       fd.quantity,
       fd.unit,
       fd.expiration_date,
       d.business_name,
       d.contact_name,
       d.phone,
       f.id AS feedback_id,
       da.receipt_file
FROM donation_requests dr
JOIN food_donations fd ON dr.donation_id = fd.id
JOIN donors d ON fd.donor_id = d.id
LEFT JOIN donation_feedback f ON dr.id = f.request_id
LEFT JOIN driver_assignments da 
       ON da.id = (
           SELECT da2.id 
           FROM driver_assignments da2 
           WHERE da2.request_id = dr.id AND da2.status='completed' 
           ORDER BY da2.assigned_at DESC 
           LIMIT 1
       )
WHERE dr.ngo_id = ?
ORDER BY dr.request_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$requests = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Requests - NGO Dashboard</title>
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
        <h2 class="mb-4">My Requests</h2>

        <?php if($requests->num_rows == 0): ?>
            <div class="alert alert-info">You haven't made any requests yet.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php while($req = $requests->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header">
                            Request ID: <?= $req['request_id']; ?> | Status: 
                            <?php 
                                $status_class = 'secondary';
                                if($req['request_status']=='approved') $status_class='success';
                                if($req['request_status']=='rejected') $status_class='danger';
                                if($req['request_status']=='completed') $status_class='info';
                            ?>
                            <span class="badge bg-<?= $status_class; ?>"><?= ucfirst($req['request_status']); ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($req['food_name']); ?></h5>
                            <p class="mb-1"><strong>Quantity:</strong> <?= $req['quantity'].' '.$req['unit']; ?></p>
                            <p class="mb-1"><strong>Expires:</strong> <?= $req['expiration_date']; ?></p>
                            <hr>
                            <h6>Donor Info</h6>
                            <p class="mb-1"><strong>Business:</strong> <?= htmlspecialchars($req['business_name']); ?></p>
                            <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($req['contact_name']); ?></p>
                            <p class="mb-0"><strong>Phone:</strong> <?= htmlspecialchars($req['phone']); ?></p>
                        </div>
                        <div class="card-footer text-end">
                            Requested on: <?= date("d M Y, H:i", strtotime($req['request_date'])); ?>

                            <?php if($req['request_status']=='completed'): ?>
                                <?php if($req['receipt_file']): ?>
                                    <a href="receipts/<?= htmlspecialchars($req['receipt_file']); ?>" target="_blank" class="btn btn-sm btn-success ms-2">
                                        <i class="fas fa-file-pdf"></i> View Receipt
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted ms-2">Receipt not uploaded yet</span>
                                <?php endif; ?>

                                <?php if($req['feedback_id']): ?>
                                    <a href="ngo_feedbacks.php" class="btn btn-sm btn-info ms-2">View Feedback</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary ms-2" onclick="showFeedbackForm(<?= $req['request_id']; ?>)">Give Feedback</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Provide Feedback</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="request_id" id="feedback_request_id">
          <label class="form-label">Food Quality</label>
          <textarea name="food_quality" class="form-control" required></textarea>

          <label class="form-label mt-3">System Efficiency</label>
          <textarea name="system_efficiency" class="form-control" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showFeedbackForm(request_id){
    document.getElementById('feedback_request_id').value = request_id;
    let modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}
</script>
</body>
</html>
