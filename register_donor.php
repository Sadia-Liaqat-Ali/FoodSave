<?php
session_start();
include 'config.php';

if (isset($_POST['register'])) {
    // Get form data
    $business_name = trim($_POST['business_name']);
    $contact_name = trim($_POST['contact_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $business_type = $_POST['business_type'];
    $address = trim($_POST['address']);
    $description = trim($_POST['description']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';
    $map_address = isset($_POST['map_address']) ? trim($_POST['map_address']) : $address;
    $final_address = !empty($map_address) ? $map_address : $address;

    // Basic validations
    if (empty($business_name) || empty($contact_name) || empty($email) || empty($phone) || 
        empty($business_type) || empty($final_address) || empty($password)) {
        echo "<script>alert('Please fill in all required fields.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } elseif (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address.');</script>";
    } elseif (empty($latitude) || empty($longitude)) {
        echo "<script>alert('Please select your location on the map.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $user_sql = "INSERT INTO users 
                     (name, email, password, user_type, phone, address, latitude, longitude, status, email_verified, created_at)
                     VALUES ('$contact_name','$email','$hashed_password','donor','$phone','$final_address','$latitude','$longitude','active',0,NOW())";
        if (mysqli_query($conn, $user_sql)) {
            $user_id = mysqli_insert_id($conn);

            // Insert into donors table
            $donor_sql = "INSERT INTO donors 
                          (user_id, business_name, contact_name, email, phone, business_type, address, description, password, latitude, longitude, created_at)
                          VALUES ('$user_id','$business_name','$contact_name','$email','$phone','$business_type','$final_address','$description','$hashed_password','$latitude','$longitude',NOW())";
            
            if (mysqli_query($conn, $donor_sql)) {
                echo "<script>alert('Registration successful! You can now login.'); window.location='login.php';</script>";
            } else {
                echo "<script>alert('Registration failed for donor. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('Registration failed for user. Please try again.');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Food Donor - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS (FREE - No API Key Needed) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        :root {
            --primary-color: #6f42c1; /* Purple */
            --secondary-color: #28a745; /* Green */
            --accent-color: #17a2b8;
            --dark-bg: #145214; /* Dark green for footer */
            --light-bg: #d4f4dd; /* Light green for whole site background */
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body { font-family: 'Roboto', sans-serif; line-height:1.6; background-color: var(--light-bg); color:#333; transition: all 0.3s ease; }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #4a49cc, #1e7e34);
            }
            
            .card {
                background-color: #2c2c2c;
                border-color: #444;
                color: #ffffff;
            }
            
            .form-control {
                background-color: #3a3a3a;
                border-color: #555;
                color: #ffffff;
            }
            
            .form-control:focus {
                background-color: #3a3a3a;
                border-color: var(--primary-color);
                color: #ffffff;
            }
            
            #map {
                filter: brightness(0.8) contrast(1.2);
            }
            
            .leaflet-tile {
                filter: invert(1) hue-rotate(180deg) brightness(0.8) contrast(1.2);
            }
        }

        .registration-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--secondary-color), #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(93, 92, 222, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #7C3AED);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(93, 92, 222, 0.3);
        }
        
        .required {
            color: #dc3545;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            z-index: 1000;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
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
            .card-body {
                padding: 1.5rem;
            }
            
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
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="container">
        <div class="registration-container">
            
            
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-store me-3"></i>Register as Food Donor
                    </h2>
                    <p class="mb-0 mt-2">Join our network of restaurants, grocery stores, and individuals reducing food waste</p>
                </div>
                <div class="card-body">
                    <form method="POST" id="donorRegistrationForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business/Organization Name <span class="required">*</span></label>
                                <input type="text" class="form-control" name="business_name" required 
                                       placeholder="Enter your business name" value="<?php echo isset($_POST['business_name']) ? htmlspecialchars($_POST['business_name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person Name <span class="required">*</span></label>
                                <input type="text" class="form-control" name="contact_name" required 
                                       placeholder="Enter contact person name" value="<?php echo isset($_POST['contact_name']) ? htmlspecialchars($_POST['contact_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" required 
                                       placeholder="Enter email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="required">*</span></label>
                                <input type="tel" class="form-control" name="phone" required 
                                       placeholder="Enter phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business Type <span class="required">*</span></label>
                                <select class="form-select" name="business_type" required>
                                    <option value="">Select Business Type</option>
                                    <option value="restaurant" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'restaurant') ? 'selected' : ''; ?>>Restaurant</option>
                                    <option value="grocery_store" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'grocery_store') ? 'selected' : ''; ?>>Grocery Store</option>
                                    <option value="bakery" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'bakery') ? 'selected' : ''; ?>>Bakery</option>
                                    <option value="catering" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'catering') ? 'selected' : ''; ?>>Catering Service</option>
                                    <option value="hotel" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'hotel') ? 'selected' : ''; ?>>Hotel</option>
                                    <option value="cafe" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'cafe') ? 'selected' : ''; ?>>Cafe</option>
                                    <option value="individual" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'individual') ? 'selected' : ''; ?>>Individual</option>
                                    <option value="other" <?php echo (isset($_POST['business_type']) && $_POST['business_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="required">*</span></label>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="Enter password" id="password">
                                <div class="password-strength text-muted">
                                    Password must be at least 6 characters long
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="required">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required 
                                       placeholder="Confirm your password" id="confirm_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business Address <span class="required">*</span></label>
                                <textarea class="form-control" name="address" id="address" rows="2" required 
                                          placeholder="Enter complete business address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                <small class="text-muted">Or select location on map below</small>
                            </div>
                        </div>
                        
                        <!-- Leaflet Map Integration (FREE - No API Key Needed) -->
                        <div class="mb-4">
                            <label class="form-label">Pickup Location on Map <span class="required">*</span></label>
                            <div class="map-container">
                                <div class="map-controls">
                                    <input type="text" id="search-box" class="form-control" placeholder="Search for address or place...">
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
                                <p><strong>Selected Location:</strong> <span id="selected-address">Not selected yet</span></p>
                                <p><strong>Coordinates:</strong> <span id="selected-coords">Click on the map to select a location</span></p>
                                <div class="map-instructions">
                                    <i class="fas fa-info-circle"></i> Click on the map or search for an address to set your pickup location. This helps volunteers find you easily.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for map data -->
                        <input type="hidden" name="latitude" id="latitude" value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>">
                        <input type="hidden" name="map_address" id="map_address" value="<?php echo isset($_POST['map_address']) ? htmlspecialchars($_POST['map_address']) : ''; ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">Business Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Brief description of your business and the type of food you typically have available for donation"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button type="submit" name="register" class="btn btn-secondary btn-lg me-md-2">
                                <i class="fas fa-user-plus me-2"></i>Register as Donor
                            </button>
                            <a href="login.php" class="btn btn-outline-secondary btn-lg">
                                Already have an account? Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS (FREE - No API Key Needed) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize map variables
        let map;
        let marker;
        let selectedLocation = false;
        
        // Initialize Leaflet map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            // Check if there's existing location data from form submission
            const existingLat = document.getElementById('latitude').value;
            const existingLng = document.getElementById('longitude').value;
            const existingAddress = document.getElementById('map_address').value;
            
            if (existingLat && existingLng) {
                const existingLocation = [parseFloat(existingLat), parseFloat(existingLng)];
                
                // Set map view to existing location
                map.setView(existingLocation, 16);
                
                // Place marker
                placeMarker(existingLocation);
                
                if (existingAddress) {
                    document.getElementById('selected-address').textContent = existingAddress;
                    document.getElementById('selected-coords').textContent = `${existingLat}, ${existingLng}`;
                } else {
                    // Reverse geocode to get address
                    reverseGeocode(existingLocation[0], existingLocation[1]);
                }
            }
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
            const searchButton = searchBox.nextElementSibling;
            
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
                            
                            map.setView(userLocation, 16);
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
            
            // Sync address textarea with map
            document.getElementById('address').addEventListener('blur', function() {
                const address = this.value.trim();
                if (address && address.length > 5 && !selectedLocation) {
                    searchAddress(address);
                }
            });
        }

        function placeMarker(latlng) {
            // Remove existing marker
            if (marker) {
                map.removeLayer(marker);
            }
            
            // Create custom marker icon
            const customIcon = L.divIcon({
                className: 'custom-marker-icon',
                html: '<i class="fas fa-map-marker-alt"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
            
            // Add new marker
            marker = L.marker(latlng, {
                draggable: true,
                icon: customIcon
            }).addTo(map);
            
            selectedLocation = true;
            
            // Update coordinates in hidden fields
            document.getElementById('latitude').value = latlng[0];
            document.getElementById('longitude').value = latlng[1];
            document.getElementById('selected-coords').textContent = 
                latlng[0].toFixed(6) + ', ' + latlng[1].toFixed(6);
            
            // Make marker draggable
            marker.on('dragend', function(e) {
                const newLatLng = e.target.getLatLng();
                document.getElementById('latitude').value = newLatLng.lat;
                document.getElementById('longitude').value = newLatLng.lng;
                document.getElementById('selected-coords').textContent = 
                    newLatLng.lat.toFixed(6) + ', ' + newLatLng.lng.toFixed(6);
                reverseGeocode(newLatLng.lat, newLatLng.lng);
            });
        }

        function clearMarker() {
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            selectedLocation = false;
            document.getElementById('selected-address').textContent = 'Not selected yet';
            document.getElementById('selected-coords').textContent = 'Click on the map to select a location';
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            document.getElementById('map_address').value = '';
            
            // Show warning
            alert('Location cleared. Please select a pickup location on the map before submitting.');
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
                        document.getElementById('map_address').value = address;
                        document.getElementById('address').value = address;
                    } else {
                        document.getElementById('selected-address').textContent = 'Address not found';
                        document.getElementById('map_address').value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        document.getElementById('address').value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    document.getElementById('selected-address').textContent = 'Error getting address';
                    document.getElementById('map_address').value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    document.getElementById('address').value = `Location at ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
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
                        map.setView(latlng, 16);
                        
                        // Place marker
                        placeMarker(latlng);
                        
                        // Update address
                        document.getElementById('selected-address').textContent = data[0].display_name;
                        document.getElementById('map_address').value = data[0].display_name;
                        document.getElementById('address').value = data[0].display_name;
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

        // Form validation
        document.getElementById('donorRegistrationForm').addEventListener('submit', function(e) {
            // Check if location is selected
            if (!selectedLocation) {
                e.preventDefault();
                alert('Please select your pickup location on the map. Click on the map or use the search box to set your location.');
                return false;
            }
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            // Additional validation for location
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                alert('Please select a valid location on the map.');
                return false;
            }
            
            // Validate coordinates are numbers
            if (isNaN(parseFloat(lat)) || isNaN(parseFloat(lng))) {
                e.preventDefault();
                alert('Invalid location coordinates. Please select a location again.');
                return false;
            }
            
            // Basic coordinate range validation
            if (Math.abs(parseFloat(lat)) > 90 || Math.abs(parseFloat(lng)) > 180) {
                e.preventDefault();
                alert('Invalid location coordinates. Please select a valid location on the map.');
                return false;
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.querySelector('.password-strength');
            
            if (password.length === 0) {
                strengthDiv.textContent = 'Password must be at least 6 characters long';
                strengthDiv.className = 'password-strength text-muted';
            } else if (password.length < 6) {
                strengthDiv.textContent = 'Password too short';
                strengthDiv.className = 'password-strength text-danger';
            } else if (password.length < 8) {
                strengthDiv.textContent = 'Password strength: Fair';
                strengthDiv.className = 'password-strength text-warning';
            } else {
                strengthDiv.textContent = 'Password strength: Good';
                strengthDiv.className = 'password-strength text-success';
            }
        });

        // Dark mode detection
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
</body>
</html>