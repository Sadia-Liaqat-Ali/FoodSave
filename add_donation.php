<?php
session_start();
include 'config.php';

// Donor check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// Get Donor ID
$donor_sql = "SELECT id FROM donors WHERE email = ?";
$donor_stmt = $conn->prepare($donor_sql);
$donor_stmt->bind_param("s", $_SESSION['user_email']);
$donor_stmt->execute();
$donor = $donor_stmt->get_result()->fetch_assoc();

if (!$donor) {
    header("Location: login.php");
    exit();
}

$donor_id = $donor['id'];
$message = '';
$message_type = '';

// Add Donation
if (isset($_POST['add_donation'])) {
    $food_name       = trim($_POST['food_name']);
    $category        = $_POST['category'];
    $quantity        = trim($_POST['quantity']);
    $unit            = $_POST['unit'];
    $expiration_date = $_POST['expiration_date'];
    $description     = trim($_POST['description']);

    if ($food_name && $category && $quantity && $unit && $expiration_date) {
        $sql = "INSERT INTO food_donations 
                (donor_id, food_name, category, quantity, unit, expiration_date, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'available')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ississs", $donor_id, $food_name, $category, $quantity, $unit, $expiration_date, $description);

        if ($stmt->execute()) {
            $message = "Donation added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding donation!";
            $message_type = "danger";
        }
    } else {
        $message = "Please fill all required fields.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Donation - FoodSave</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
:root{
    --primary:#28a745;
    --light:#d4f4dd;
    --dark:#145214;
}
body{
    background:var(--light);
}
.card{
    border-radius:10px;
    border:1px solid #c2e8c8;
}
.btn-primary{
    background:var(--primary);
    border-color:var(--primary);
}
.btn-primary:hover{
    background:#1e7e34;
}
.sidebar{
    background:#145214;
    min-height:100vh;
    padding:20px 0;
    color:white;
}
.sidebar a{
    color:white;
    display:block;
    padding:12px 20px;
    text-decoration:none;
}
.sidebar a:hover{
    background:#0f3d12;
}
input,select,textarea{
    border-radius:6px!important;
}
</style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar">
            <?php include 'sidebar_donor.php'; ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 py-5">

            <h2 class="mb-4 text-center text-success">
                <i class="fas fa-hand-holding-heart me-2"></i> Add Food Donation
            </h2>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> text-center">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm p-4 mx-auto" style="max-width:850px;">
                <form method="POST">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Food Name *</label>
                            <input type="text" name="food_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="vegetables">Vegetables</option>
                                <option value="fruits">Fruits</option>
                                <option value="dairy">Dairy</option>
                                <option value="meat">Meat</option>
                                <option value="grains">Grains</option>
                                <option value="prepared_food">Prepared Food</option>
                                <option value="beverages">Beverages</option>
                                <option value="snacks">Snacks</option>
                                <option value="frozen">Frozen</option>
                                <option value="canned">Canned</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Quantity *</label>
                            <input type="text" name="quantity" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Unit *</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kg</option>
                                <option value="lbs">Lbs</option>
                                <option value="servings">Servings</option>
                                <option value="pieces">Pieces</option>
                                <option value="liters">Liters</option>
                                <option value="boxes">Boxes</option>
                                <option value="bags">Bags</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Expiration Date *</label>
                            <input type="date" name="expiration_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Description (optional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" name="add_donation" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>Add Donation
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split('T')[0];
    document.querySelector("input[name='expiration_date']").setAttribute("min", today);
});
</script>

</body>
</html>
