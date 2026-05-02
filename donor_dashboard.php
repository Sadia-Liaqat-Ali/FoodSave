<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['user_name'];

// Fetch safety guidelines from DB
$guidelines = [];
$sql = "SELECT title, description FROM food_safety_guidelines ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $guidelines[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Donor Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
:root {
 --green:#28a745;
 --light:#d4f4dd;
 --dark:#145214;
}

body { background: var(--light); font-family: 'Roboto', sans-serif; }

.card-box {
    background: #ffffff;
    border-left: 6px solid var(--green);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: 0.3s;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
}

.card-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.15);
}

.card-box i {
    font-size: 40px;
    color: var(--green);
    margin-right: 15px;
}

.card-content h4 {
    margin: 0;
    font-weight: 600;
}

.card-content p {
    margin: 0;
    color: #555;
}

.sidebar {
    background: var(--green);
    min-height: 100vh;
}

.guidelines {
    background: #ffffff;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.guidelines h5 {
    color: var(--dark);
    margin-bottom: 15px;
}

.guidelines ul {
    padding-left: 20px;
    margin: 0;
}

.guidelines ul li {
    margin-bottom: 10px;
    color: #333;
}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

<div class="col-md-2 p-0">
<?php include "sidebar_donor.php"; ?>
</div>

<div class="col-md-10 p-4">

<h3>Welcome, <?php echo htmlspecialchars($name); ?></h3>

<div class="row mt-4">

<div class="col-md-4">
<div class="card-box">
    <i class="fas fa-boxes"></i>
    <div class="card-content">
        <h4>Total Donations</h4>
        <p>Manage all your food donations</p>
    </div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
    <i class="fas fa-plus-circle"></i>
    <div class="card-content">
        <h4>Add Donation</h4>
        <p>Add new food items for NGOs</p>
    </div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
    <i class="fas fa-hand-holding-heart"></i>
    <div class="card-content">
        <h4>Requests</h4>
        <p>View NGO requests for your donations</p>
    </div>
</div>
</div>

</div>

<!-- Donor Safety Guidelines -->
<div class="guidelines">
<h5><i class="fas fa-shield-alt me-2"></i>Donor Safety Guidelines</h5>
<ul>
<?php if(!empty($guidelines)): ?>
    <?php foreach($guidelines as $g): ?>
        <li>
            <strong><?php echo htmlspecialchars($g['title']); ?>:</strong> 
            <?php echo htmlspecialchars($g['description']); ?>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li>No safety guidelines available.</li>
<?php endif; ?>
</ul>
</div>

</div>

</div>
</div>

</body>
</html>
