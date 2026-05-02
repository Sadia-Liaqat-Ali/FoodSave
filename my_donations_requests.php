<?php
// my_donations_requests.php
// Donor view for incoming NGO requests — accepts/rejects requests
session_start();
include 'config.php';

// --- Auth check ---
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: ../login.php");
    exit();
}

// --- Helper: safe prepare with error message (for debugging) ---
function safe_prepare($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // For production you may want to log this instead of echoing
        die("SQL prepare error: " . $conn->error . " | Query: " . $sql);
    }
    return $stmt;
}

// --- Get donor id safely ---
$donor_sql = "SELECT id FROM donors WHERE email = ?";
$donor_stmt = safe_prepare($conn, $donor_sql);
$donor_stmt->bind_param("s", $_SESSION['user_email']);
$donor_stmt->execute();
$donor_result = $donor_stmt->get_result();
$donor = $donor_result->fetch_assoc();

if (!$donor) {
    header("Location: ../login.php");
    exit();
}
$donor_id = (int)$donor['id'];

$message = "";
$message_type = "";

// --- Handle accept / reject (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['action'])) {
    $req_id = (int)$_POST['request_id'];
    $action = $_POST['action'] === 'accept' ? 'approved' : 'rejected';

    // Update only when the donation belongs to this donor (join used)
    $update_sql = "UPDATE donation_requests dr
                   INNER JOIN food_donations fd ON dr.donation_id = fd.id
                   SET dr.status = ?
                   WHERE dr.id = ? AND fd.donor_id = ?";
    $update_stmt = safe_prepare($conn, $update_sql);
    $update_stmt->bind_param("sii", $action, $req_id, $donor_id);

    if ($update_stmt->execute()) {
        $message = "Request " . ($action === 'approved' ? "accepted" : "rejected") . " successfully.";
        $message_type = "success";
    } else {
        $message = "Database error: " . $update_stmt->error;
        $message_type = "danger";
    }
}

$fetch_sql = "SELECT 
                dr.id AS request_id,
                dr.status,
                dr.request_date,
                fd.food_name,
                fd.quantity,
                fd.unit,
                ng.organization_name AS ngo_name
              FROM donation_requests dr
              INNER JOIN food_donations fd ON dr.donation_id = fd.id
              LEFT JOIN ngos ng ON dr.ngo_id = ng.id
              WHERE fd.donor_id = ?
              ORDER BY dr.request_date DESC";

$fetch_stmt = $conn->prepare($fetch_sql);
if (!$fetch_stmt) {
    die("Prepare failed: " . $conn->error);
}
$fetch_stmt->bind_param("i", $donor_id);
$fetch_stmt->execute();
$requests_result = $fetch_stmt->get_result();
$requests = $requests_result->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>NGO Requests — Donor | FoodSave</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <style>
       :root {
    --green:#28a745;
    --light:#d4f4dd;
    --dark:#145214;
}
body{background:var(--light);}
.sidebar{min-height:100vh;background:var(--green);}
.stats-card{border-left:4px solid var(--green);}
        .sidebar { background: var(--accent); min-height: 100vh; }
        .btn-accent { background: var(--accent); border-color: var(--accent); color: #fff; }
        .status-badge { padding: .35rem .6rem; font-size:.9rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- sidebar -->
        <div class="col-md-2 p-0">
            <?php include 'sidebar_donor.php'; ?>
        </div>

        <!-- main -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Incoming Requests</h2>
                <div class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Requests for your Food Items</h5>

                    <?php if (empty($requests)): ?>
                        <p class="text-muted mb-0">No requests received yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Quantity</th>
                                        <th>Requested By (NGO)</th>
                                        <th>Requested On</th>
                                        <th>Status</th>
                                        <th style="width:140px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['food_name']); ?></td>
                                            <td><?php echo htmlspecialchars($r['quantity'] . ' ' . $r['unit']); ?></td>
                                            <td><?php echo htmlspecialchars($r['ngo_name']); ?></td>
                                            <td><?php echo date("M d, Y - h:i A", strtotime($r['request_date'])); ?></td>
                                            <td>
                                                <?php
                                                    $s = $r['status'];
                                                    $cls = $s === 'pending' ? 'warning' : ($s === 'approved' ? 'success' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $cls; ?> status-badge"><?php echo ucfirst($s); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] === 'pending'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                                                        <button type="submit" name="action" value="accept" class="btn btn-sm btn-success">
                                                            <i class="fa fa-check me-1"></i>Accept
                                                        </button>
                                                    </form>

                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-times me-1"></i>Reject
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
