<?php
session_start();
include 'config.php';

// Only donors can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'];

// Fetch donations along with request info and feedback
$donations = [];
$sql = "
    SELECT 
        fd.id AS donation_id,
        fd.food_name,
        fd.category,
        fd.quantity,
        fd.unit,
        fd.status AS donation_status,
        dr.id AS request_id,
        dr.status AS request_status,
        dr.request_date,
        dr.pickup_date,
        df.food_quality,
        df.system_efficiency
    FROM food_donations fd
    LEFT JOIN donation_requests dr ON fd.id = dr.donation_id
    LEFT JOIN donation_feedback df ON dr.id = df.request_id
    WHERE fd.donor_id = ?
    ORDER BY fd.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $donations[] = $row;
}

// Calculate totals and impact
$totalDonations = 0;
$totalRequested = 0;
$totalCompleted = 0;
$totalFeedbacks = 0;

foreach($donations as $d){
    $totalDonations++;
    if(!empty($d['request_id'])) $totalRequested++;
    if($d['request_status'] === 'completed') $totalCompleted++;
    if(!empty($d['food_quality']) || !empty($d['system_efficiency'])) $totalFeedbacks++;
}

// Display donor impact report
echo "<h2>Donor Impact Report for $name</h2>";
echo "<p>Total Donations Listed: $totalDonations</p>";
echo "<p>Total Donation Requests Made: $totalRequested</p>";
echo "<p>Total Completed Requests: $totalCompleted</p>";
echo "<p>Total Feedback Received: $totalFeedbacks</p>";
echo "<hr>";

if(!empty($donations)){
    echo "<table border='1' cellpadding='5'>
        <tr>
            <th>Food Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Donation Status</th>
            <th>Request Status</th>
            <th>Pickup Date</th>
            <th>Food Quality Feedback</th>
            <th>System Efficiency Feedback</th>
        </tr>";
    foreach($donations as $d){
        echo "<tr>
            <td>{$d['food_name']}</td>
            <td>{$d['category']}</td>
            <td>{$d['quantity']}</td>
            <td>{$d['unit']}</td>
            <td>{$d['donation_status']}</td>
            <td>".($d['request_status'] ?? 'N/A')."</td>
            <td>".($d['pickup_date'] ?? 'N/A')."</td>
            <td>".($d['food_quality'] ?? '-')."</td>
            <td>".($d['system_efficiency'] ?? '-')."</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No donations found yet.</p>";
}
?>
