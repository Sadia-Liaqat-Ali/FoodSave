<?php
// food_safety_guidelines.php
session_start();
include 'config.php';

// Admin access only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Add new guideline
if (isset($_POST['add_guideline'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if ($title && $description) {
        $stmt = $conn->prepare("INSERT INTO food_safety_guidelines (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        if ($stmt->execute()) {
            $message = "Guideline added successfully!";
            $message_type = 'success';
        } else {
            $message = "Failed to add guideline: ".$conn->error;
            $message_type = 'danger';
        }
    } else {
        $message = "Both Title and Description are required!";
        $message_type = 'warning';
    }
}

// Delete guideline
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM food_safety_guidelines WHERE id=$delete_id");
    $message = "Guideline deleted successfully!";
    $message_type = 'success';
}

// Fetch guidelines
$result = $conn->query("SELECT * FROM food_safety_guidelines ORDER BY created_at DESC");
$guidelines = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Safety Guidelines - Admin</title>
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
.sidebar-fixed { position: sticky; top:0; height:100vh; overflow-y:auto; background: var(--primary-color); color:white; }
.sidebar-fixed .nav-link { color:white; margin:2px 0; }
.sidebar-fixed .nav-link:hover, .sidebar-fixed .nav-link.active { background: rgba(255,255,255,0.2); border-radius:5px; }
.card-guide { background:white; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.card-guide h5 { font-weight:600; }
.card-guide p { margin-bottom:0.5rem; line-height:1.5; }
.card-guide small { color:#555; }
.btn-sm { font-size:.8rem; }
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
        <h2 class="mb-4">Manage Food Safety Guidelines</h2>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Guideline -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white"><i class="fas fa-plus-circle"></i> Add New Guideline</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="E.g. Proper Food Storage" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter guideline details..." required></textarea>
                    </div>
                    <button type="submit" name="add_guideline" class="btn btn-primary"><i class="fas fa-plus"></i> Add Guideline</button>
                </form>
            </div>
        </div>

        <!-- Guidelines List -->
        <?php if(!empty($guidelines)): ?>
            <?php foreach($guidelines as $guide): ?>
                <div class="card card-guide">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5><?php echo htmlspecialchars($guide['title']); ?></h5>
                            <p><?php echo nl2br(htmlspecialchars($guide['description'])); ?></p>
                            <small>Added on: <?php echo date('M d, Y H:i', strtotime($guide['created_at'])); ?></small>
                        </div>
                        <div>
                            <a href="?delete_id=<?php echo $guide['id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this guideline?');">
                               <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">No guidelines found. Add some to guide your donors!</p>
        <?php endif; ?>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
