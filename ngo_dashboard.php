<?php
session_start();
include 'config.php';

// NGO Login check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'ngo') {
    header("Location: login.php");
    exit();
}

// ---------- HANDLE PICKUP REQUEST WITH FORM ----------
if (isset($_POST['submit_pickup'])) {

    $donation_id    = intval($_POST['donation_id']);
    $ngo_id         = intval($_SESSION['user_id']);
    $qty            = $_POST['pickup_quantity'];
    $date           = $_POST['pickup_date'];
    $time           = $_POST['pickup_time'];

    $sql = "INSERT INTO donation_requests 
            (donation_id, ngo_id, pickup_quantity, pickup_date, pickup_time) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $donation_id, $ngo_id, $qty, $date, $time);

    if ($stmt->execute()) {
        $conn->query("UPDATE food_donations SET status='requested' WHERE id=$donation_id");
        echo "<script>alert('Pickup request submitted successfully!'); window.location='ngo_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error saving request');</script>";
    }
}
// --------------------------------------------------

// Fetch NGO details
 $ngo_sql = "SELECT * FROM ngos WHERE email = ?";
 $ngo_stmt = $conn->prepare($ngo_sql);
 $ngo_stmt->bind_param("s", $_SESSION['user_email']);
 $ngo_stmt->execute();
 $ngo = $ngo_stmt->get_result()->fetch_assoc();

if (!$ngo) { header("Location: ../login.php"); exit(); }

// Pending / Rejected message
if ($ngo['status'] !== 'active') {
    $pending_message = "Your NGO registration is ".$ngo['status'].". ";
    if ($ngo['status'] === 'pending') $pending_message .= "Please wait for admin approval.";
    if ($ngo['status'] === 'rejected') $pending_message .= "Reason: ".htmlspecialchars($ngo['rejection_reason']);
}

// Search + filter inputs
 $search = isset($_GET['search']) ? $_GET['search'] : "";
 $category = isset($_GET['category']) ? $_GET['category'] : "";
 $map_filter = isset($_GET['map_filter']) ? $_GET['map_filter'] : "";

// Donations Query
 $sql = "SELECT fd.*, d.business_name, d.contact_name, d.phone, d.address, d.latitude, d.longitude
        FROM food_donations fd
        JOIN donors d ON fd.donor_id = d.id
        WHERE fd.status = 'available'
        AND fd.expiration_date >= CURDATE()";

if ($search !== "") {
    $s = $conn->real_escape_string($search);
    $sql .= " AND fd.food_name LIKE '%$s%'";
}

if ($category !== "") {
    $c = $conn->real_escape_string($category);
    $sql .= " AND fd.category = '$c'";
}

// Map filter - if a location is selected on the map
if ($map_filter !== "") {
    $parts = explode(',', $map_filter);
    if (count($parts) == 2) {
        $lat = floatval($parts[0]);
        $lng = floatval($parts[1]);
        // Filter donations within approximately 10km radius
        $sql .= " AND (6371 * acos(cos(radians($lat)) * cos(radians(d.latitude)) * cos(radians(d.longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(d.latitude)))) < 10";
    }
}

 $sql .= " ORDER BY fd.expiration_date ASC";

 $donations_result = $conn->query($sql);
 $donations = [];
while ($row = $donations_result->fetch_assoc()) $donations[] = $row;

// Stats
 $stats_q = $conn->query("SELECT COUNT(*) AS total_available FROM food_donations WHERE status='available'");
 $stats = $stats_q->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Dashboard - FoodSave</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS (FREE - No API Key Needed) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root { 
            --ngo-color:#6f42c1; 
            --primary-color: #6f42c1; /* Purple */
            --secondary-color: #28a745; /* Green */
            --accent-color: #17a2b8;
            --dark-bg: #145214; /* Dark green for footer */
            --light-bg: #d4f4dd; /* Light green for whole site background */
        }
        
        .stats-card { border-left:4px solid var(--ngo-color); }
        .btn-primary { background:var(--ngo-color); border-color:var(--ngo-color); }
        
        /* Map Container Styles */
        .map-container {
            margin: 1.5rem 0;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e9ecef;
        }
        
        #map {
            height: 400px;
            width: 100%;
        }
        
        .map-controls {
            background: white;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .map-controls input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .location-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .location-info p {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .map-instructions {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Leaflet Custom Styles */
        .leaflet-control-attribution {
            font-size: 10px;
        }
        
        .custom-marker-icon {
            background-color: var(--secondary-color);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #map {
                height: 300px;
            }
            
            .map-controls {
                flex-direction: column;
            }
            
            .map-controls input {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 p-0">
            <?php include 'sidebar_ngo.php'; ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>NGO Dashboard</h2>
                <span class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>

            <?php if(isset($pending_message)): ?>
                <div class="alert alert-warning"><i class="fas fa-info-circle me-2"></i><?= $pending_message; ?></div>
            <?php endif; ?>

            <!-- OVERVIEW -->
            <section id="overview" class="mb-5">
                <h4 class="mb-3">Overview</h4>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?= $stats['total_available']; ?></h3>
                                <p>Available Donations</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?= ucfirst($ngo['status']); ?></h3>
                                <p>Account Status</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <h6><?= htmlspecialchars($ngo['organization_name']); ?></h6>
                            <p class="text-muted"><?= htmlspecialchars($ngo['mission']); ?></p>
                        </div></div>
                    </div>
                </div>
            </section>

            <?php if($ngo['status']=="active"): ?>
            <!-- DONATIONS LIST -->
            <section id="available-donations" class="mb-5">
                <h4 class="mb-3">Available Food Donations</h4>

                <!-- SEARCH + FILTER -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               value="<?= $search ?>" placeholder="Search food name">
                    </div>

                    <div class="col-md-4">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="fruits"      <?= $category=="fruits"?"selected":"" ?>>Fruits</option>
                            <option value="vegetables"  <?= $category=="vegetables"?"selected":"" ?>>Vegetables</option>
                            <option value="cooked_food" <?= $category=="cooked_food"?"selected":"" ?>>Cooked Food</option>
                            <option value="bakery"      <?= $category=="bakery"?"selected":"" ?>>Bakery</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="button" id="clear-map-filter" class="btn btn-outline-secondary w-100">
                            Clear Map Filter
                        </button>
                    </div>
                </form>

                <!-- MAP FILTER SECTION -->
                <div class="mb-4">
                    <label class="form-label">Filter Donations by Location</label>
                    <div class="map-container">
                        <div class="map-controls">
                            <input type="text" id="search-box" class="form-control" placeholder="Search for area or location...">
                            <button type="button" id="use-current-location" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-location-arrow"></i> Use My Location
                            </button>
                            <button type="button" id="clear-marker" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Clear
                            </button>
                        </div>
                        <div id="map"></div>
                    </div>
                    <div class="location-info">
                        <p><strong>Selected Location:</strong> <span id="selected-address">Click on the map to filter donations by location</span></p>
                        <p><strong>Coordinates:</strong> <span id="selected-coords">Not selected</span></p>
                        <div class="map-instructions">
                            <i class="fas fa-info-circle"></i> Click on the map or search for an area to filter donations within a 10km radius. This helps you find food donations near your location.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if(empty($donations)): ?>
                        <p class="text-muted">No donations found.</p>
                    <?php else: foreach($donations as $donation): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">

                                <h6><?= htmlspecialchars($donation['food_name']); ?></h6>

                                <span class="badge bg-info"><?= $donation['category']; ?></span><br>
                                <strong>Qty:</strong> <?= $donation['quantity']." ".$donation['unit']; ?><br>
                                <strong>Expires:</strong> <?= $donation['expiration_date']; ?><br>

                                <hr>
                                <strong>Donor:</strong> <?= htmlspecialchars($donation['business_name']); ?><br>
                                Contact: <?= htmlspecialchars($donation['contact_name']); ?><br>
                                Phone: <?= htmlspecialchars($donation['phone']); ?><br>
                                Phone: <?= htmlspecialchars($donation['phone']); ?><br>
                                location: <?= htmlspecialchars($donation['address']); ?><br>


                                <button class="btn btn-primary btn-sm w-100 mt-3"
                                        onclick="openPickupModal(<?= $donation['id']; ?>)">
                                    Request Pickup
                                </button>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- PICKUP REQUEST MODAL -->
<div class="modal fade" id="pickupModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Pickup Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="donation_id" id="modal_donation_id">
          <label class="form-label">Pickup Quantity</label>
          <input type="text" name="pickup_quantity" class="form-control" required>
          <label class="form-label mt-3">Pickup Date</label>
          <input type="date" name="pickup_date" class="form-control" required>
          <label class="form-label mt-3">Pickup Time</label>
          <input type="time" name="pickup_time" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit_pickup" class="btn btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leaflet JS (FREE - No API Key Needed) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize map variables
let map;
let markers = [];
let selectedLocation = false;

// Initialize Leaflet map when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Check if there's existing map filter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const mapFilter = urlParams.get('map_filter');
    
    if (mapFilter) {
        const parts = mapFilter.split(',');
        if (parts.length === 2) {
            const lat = parseFloat(parts[0]);
            const lng = parseFloat(parts[1]);
            const location = [lat, lng];
            
            // Set map view to existing location
            map.setView(location, 13);
            
            // Place marker
            placeMarker(location);
            
            // Update display
            document.getElementById('selected-coords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('selected-address').textContent = 'Filtered location';
        }
    }
    
    // Load donation markers
    loadDonationMarkers();
});

function initMap() {
    // Default center (New York City)
    const defaultCenter = [40.7128, -74.0060];
    
    // Initialize map
    map = L.map('map').setView(defaultCenter, 12);
    
    // Add OpenStreetMap tiles (FREE - No API Key)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add click handler to map
    map.on('click', function(e) {
        placeMarker([e.latlng.lat, e.latlng.lng]);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
    
    // Search box functionality
    const searchBox = document.getElementById('search-box');
    
    searchBox.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchAddress(this.value);
        }
    });
    
    // Use current location button
    document.getElementById('use-current-location').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLocation = [position.coords.latitude, position.coords.longitude];
                    
                    map.setView(userLocation, 13);
                    placeMarker(userLocation);
                    reverseGeocode(userLocation[0], userLocation[1]);
                },
                function(error) {
                    let errorMessage = "Unable to retrieve your location. ";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += "Please enable location permissions in your browser.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += "Location information is unavailable.";
                            break;
                        case error.TIMEOUT:
                            errorMessage += "Location request timed out.";
                            break;
                        default:
                            errorMessage += "An unknown error occurred.";
                    }
                    alert(errorMessage);
                }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    });
    
    // Clear marker button
    document.getElementById('clear-marker').addEventListener('click', function() {
        clearMarker();
    });
    
    // Clear map filter button
    document.getElementById('clear-map-filter').addEventListener('click', function() {
        const url = new URL(window.location);
        url.searchParams.delete('map_filter');
        window.location.href = url.toString();
    });
}

function placeMarker(latlng) {
    // Remove existing marker
    if (markers.length > 0) {
        map.removeLayer(markers[0]);
        markers = [];
    }
    
    // Create custom marker icon
    const customIcon = L.divIcon({
        className: 'custom-marker-icon',
        html: '<i class="fas fa-map-marker-alt"></i>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });
    
    // Add new marker
    const marker = L.marker(latlng, {
        draggable: true,
        icon: customIcon
    }).addTo(map);
    
    markers.push(marker);
    selectedLocation = true;
    
    // Update coordinates display
    document.getElementById('selected-coords').textContent = 
        latlng[0].toFixed(6) + ', ' + latlng[1].toFixed(6);
    
    // Make marker draggable
    marker.on('dragend', function(e) {
        const newLatLng = e.target.getLatLng();
        document.getElementById('selected-coords').textContent = 
            newLatLng.lat.toFixed(6) + ', ' + newLatLng.lng.toFixed(6);
        reverseGeocode(newLatLng.lat, newLatLng.lng);
    });
}

function clearMarker() {
    if (markers.length > 0) {
        map.removeLayer(markers[0]);
        markers = [];
    }
    selectedLocation = false;
    document.getElementById('selected-address').textContent = 'Click on the map to filter donations by location';
    document.getElementById('selected-coords').textContent = 'Not selected';
}

// Free geocoding using Nominatim (OpenStreetMap API - FREE)
function reverseGeocode(lat, lng) {
    // Show loading message
    document.getElementById('selected-address').textContent = 'Getting address...';
    
    // Use OpenStreetMap Nominatim API (FREE)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                const address = data.display_name;
                document.getElementById('selected-address').textContent = address;
            } else {
                document.getElementById('selected-address').textContent = 'Location found';
            }
            
            // Apply map filter by updating URL
            applyMapFilter(lat, lng);
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            document.getElementById('selected-address').textContent = 'Error getting address';
            
            // Still apply filter even if geocoding fails
            applyMapFilter(lat, lng);
        });
}

function searchAddress(query) {
    if (!query.trim()) return;
    
    // Show loading
    document.getElementById('selected-address').textContent = 'Searching...';
    
    // Use OpenStreetMap Nominatim API (FREE)
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                const latlng = [lat, lon];
                
                // Update map view
                map.setView(latlng, 13);
                
                // Place marker
                placeMarker(latlng);
                
                // Update address
                document.getElementById('selected-address').textContent = data[0].display_name;
                
                // Apply map filter
                applyMapFilter(lat, lon);
            } else {
                document.getElementById('selected-address').textContent = 'Address not found';
                alert('Address not found. Please try a different search or click on the map directly.');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            document.getElementById('selected-address').textContent = 'Search error';
            alert('Search failed. Please check your connection and try again.');
        });
}

function applyMapFilter(lat, lng) {
    const url = new URL(window.location);
    url.searchParams.set('map_filter', `${lat},${lng}`);
    window.location.href = url.toString();
}

function loadDonationMarkers() {
    // Get all donation locations from the page
    const donationElements = document.querySelectorAll('.donation-card');
    
    // Add markers for each donation
    <?php foreach($donations as $donation): ?>
        <?php if(!empty($donation['latitude']) && !empty($donation['longitude'])): ?>
            // Create a marker for this donation
            const donationIcon = L.divIcon({
                className: 'donation-marker',
                html: '<div style="background-color: #6f42c1; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"><i class="fas fa-utensils"></i></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            const marker = L.marker([<?= $donation['latitude'] ?>, <?= $donation['longitude'] ?>], {
                icon: donationIcon
            }).addTo(map);
            
            // Add popup with donation info
            marker.bindPopup(`
                <div style="min-width: 200px;">
                    <h6><?= htmlspecialchars($donation['food_name']) ?></h6>
                    <p><strong>Donor:</strong> <?= htmlspecialchars($donation['business_name']) ?></p>
                    <p><strong>Quantity:</strong> <?= $donation['quantity']." ".$donation['unit'] ?></p>
                    <p><strong>Category:</strong> <?= $donation['category'] ?></p>
                    <p><strong>Expires:</strong> <?= $donation['expiration_date'] ?></p>
                    <button class="btn btn-sm btn-primary" onclick="openPickupModal(<?= $donation['id']; ?>)">Request Pickup</button>
                </div>
            `);
            
            markers.push(marker);
        <?php endif; ?>
    <?php endforeach; ?>
}

function openPickupModal(id) {
    document.getElementById("modal_donation_id").value = id;
    let modal = new bootstrap.Modal(document.getElementById('pickupModal'));
    modal.show();
}
</script>

</body>
</html>