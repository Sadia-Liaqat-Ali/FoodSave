<?php
session_start();
// Note: config.php will be included separately by the user
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave - Online Food Waste Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

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

/* Navbar */
.navbar-brand { font-weight:bold; font-size:1.8rem; color:var(--primary-color) !important; }
.navbar-brand i { margin-right:10px; }

/* Hero Section */
.hero-section { position: relative; width:100%; height:100vh; overflow:hidden; }
.hero-carousel .carousel-inner { width:100vw; margin:0; }
.hero-carousel .carousel-item img { width:100vw; height:100vh; object-fit:cover; display:block; }

.hero-content { position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); z-index:2; text-align:center; }
.hero-content h1 { font-family:'Poppins', sans-serif; font-size:3.5rem; font-weight:700; color:#fffb00; text-shadow:2px 2px 5px rgba(0,0,0,0.6); }
.hero-content p { font-size:1.3rem; margin-bottom:2rem; color:#fff; text-shadow:1px 1px 4px rgba(0,0,0,0.6); }
.hero-content .btn-primary { background: linear-gradient(135deg, var(--primary-color), #7C3AED); border:none; padding:12px 30px; border-radius:50px; font-weight:600; transition:all 0.3s ease; }
.hero-content .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(111,66,193,0.3); }
.hero-content .btn-outline-light { border:2px solid white; padding:12px 30px; border-radius:50px; font-weight:600; transition: all 0.3s ease; }
.hero-content .btn-outline-light:hover { background-color:white; color:var(--primary-color); transform:translateY(-2px); }

/* Features Section */
.features-section { padding:80px 0; }
.feature-card { background:#fff; border-radius:20px; padding:2rem; text-align:center; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.1); transition:all 0.3s ease; }
.feature-card:hover { transform:translateY(-10px); box-shadow:0 15px 40px rgba(0,0,0,0.15); }
.feature-icon { font-size:3rem; color:var(--primary-color); margin-bottom:1rem; }

/* Statistics Section */
.stats-section { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color:white; padding:60px 0; }
.stat-item { text-align:center; margin-bottom:2rem; }
.stat-number { font-size:3rem; font-weight:bold; display:block; }

/* Contribute Section */
.contribute-section { padding:80px 0; background: #e0f7e5; }
.contribute-card { background:white; border-radius:20px; padding:2rem; text-align:center; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.1); transition:all 0.3s ease; height:100%; }
.contribute-card:hover { transform:translateY(-5px); box-shadow:0 15px 40px rgba(0,0,0,0.15); }
.contribute-icon { width:80px; height:80px; margin:0 auto 1.5rem; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:2rem; }
.donor-card .contribute-icon { background: linear-gradient(135deg, var(--secondary-color), #20c997); }
.ngo-card .contribute-icon { background: linear-gradient(135deg, var(--primary-color), #6f42c1); }

/* Footer */
.footer { background: var(--dark-bg); color:white; padding:40px 0; }
.footer a { text-decoration:none; color:white; transition:all 0.3s ease; }
.footer a:hover { color:var(--secondary-color); }

@media(max-width:768px){ .hero-content h1{ font-size:2.5rem; } .stat-number{ font-size:2rem; } }

</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
<div class="container">
<a class="navbar-brand" href="index.php"><i class="fas fa-leaf"></i>FoodSave</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
<li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Register</a>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="register_donor.php"><i class="fas fa-store"></i> Register as Donor</a></li>
<li><a class="dropdown-item" href="register_ngo.php"><i class="fas fa-hands-helping"></i> Register as NGO</a></li>

<li><a class="dropdown-item" href="register_drivers.php"><i class="fas fa-hands-helping"></i> Register as Driver</a></li>

</ul>
</li>
</ul>
</div>
</div>
</nav>

<!-- Hero Section with 6 full-width slides -->
<section class="hero-section">
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
<div class="carousel-inner">
<div class="carousel-item active"><img src="images/hero1.jpg" class="d-block w-100" alt="Slide 1"></div>
<div class="carousel-item"><img src="images/hero2.jpg" class="d-block w-100" alt="Slide 2"></div>
<div class="carousel-item"><img src="images/hero3.jpg" class="d-block w-100" alt="Slide 3"></div>
<div class="carousel-item"><img src="images/hero4.jpg" class="d-block w-100" alt="Slide 4"></div>
<div class="carousel-item"><img src="images/hero5.jpg" class="d-block w-100" alt="Slide 5"></div>
<div class="carousel-item"><img src="images/hero6.jpg" class="d-block w-100" alt="Slide 6"></div>
</div>
<button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
<span class="carousel-control-prev-icon" aria-hidden="true"></span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
<span class="carousel-control-next-icon" aria-hidden="true"></span>
</button>
</div>

<!-- Hero Text -->
<div class="hero-content">
<h1>Reduce Food Waste, Feed the Hungry</h1>
<p>Connect restaurants, grocery stores, and individuals with NGOs and food banks to donate surplus food efficiently.</p>
<div class="d-flex flex-wrap gap-3 justify-content-center">
<a href="register_donor.php" class="btn btn-primary btn-lg"><i class="fas fa-plus-circle me-2"></i>Start Donating</a>
<a href="register_ngo.php" class="btn btn-outline-light btn-lg"><i class="fas fa-hands-helping me-2"></i>Join as NGO</a>
</div>
</div>
</section>

<!-- Features Section -->
<section class="features-section">
<div class="container">
<div class="text-center mb-5">
<h2 class="display-5 fw-bold">Our Key Features</h2>
<p class="lead text-muted">Empowering donors and NGOs with smart tools for impactful food donation.</p>
</div>
<div class="row g-4">
<div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="fas fa-bullseye"></i></div><h4>Real-time Matching</h4><p>Instantly match available food donations with NGOs in need, ensuring no food goes to waste.</p></div></div>
<div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="fas fa-chart-line"></i></div><h4>Impact Tracking</h4><p>Monitor your contributions and see the tangible difference you make in feeding communities.</p></div></div>
<div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="fas fa-shield-alt"></i></div><h4>Secure & Verified</h4><p>All donors and NGOs are verified, ensuring a safe and trustworthy donation experience.</p></div></div>
</div>
</div>
</section>


<!-- Contribute Section -->
<section class="contribute-section">
<div class="container">
<div class="text-center mb-5">
<h2 class="display-5 fw-bold">How You Can Contribute</h2>
<p class="lead text-muted">Join our mission to reduce food waste through different ways</p>
</div>
<div class="row justify-content-center">
<div class="col-lg-5 col-md-6 mb-4"><div class="card contribute-card donor-card">
<div class="contribute-icon"><i class="fas fa-store"></i></div>
<h4 class="mb-3">Food Donors</h4>
<p class="text-muted mb-4">Restaurants, grocery stores, and individuals can list surplus food items for donation to help reduce waste and feed those in need.</p>
<ul class="list-unstyled text-start">
<li><i class="fas fa-check text-success me-2"></i>List surplus food items</li>
<li><i class="fas fa-check text-success me-2"></i>Manage your donations</li>
<li><i class="fas fa-check text-success me-2"></i>Track your impact</li>
<li><i class="fas fa-check text-success me-2"></i>Schedule easy pickups</li>
</ul>
<div class="mt-3"><a href="register_donor.php" class="btn btn-primary me-2">Register Now</a><a href="login.php" class="btn btn-outline-primary">Login</a></div>
</div></div>

<div class="col-lg-5 col-md-6 mb-4"><div class="card contribute-card ngo-card">
<div class="contribute-icon"><i class="fas fa-hands-helping"></i></div>
<h4 class="mb-3">NGOs & Food Banks</h4>
<p class="text-muted mb-4">Browse available donations, request pickups, and help distribute food to communities and families in need across your area.</p>
<ul class="list-unstyled text-start">
<li><i class="fas fa-check text-success me-2"></i>Browse available donations</li>
<li><i class="fas fa-check text-success me-2"></i>Request food pickups</li>
<li><i class="fas fa-check text-success me-2"></i>Locate nearby donors</li>
<li><i class="fas fa-check text-success me-2"></i>Track distribution impact</li>
</ul>
<div class="mt-3"><a href="register_ngo.php" class="btn btn-primary me-2">Register Now</a><a href="login.php" class="btn btn-outline-primary">Login</a></div>
</div></div>
</div>
</div>
</section>

<!-- Footer -->
<footer class="footer">
<div class="container">
<div class="row">
<div class="col-lg-4 mb-4"><h5><i class="fas fa-leaf me-2"></i>FoodSave</h5><p class="mb-3">Connecting food donors with NGOs and food banks to reduce waste and fight hunger through technology.</p>
<div class="d-flex gap-3"><a href="#" class="text-white"><i class="fab fa-facebook"></i></a><a href="#" class="text-white"><i class="fab fa-twitter"></i></a><a href="#" class="text-white"><i class="fab fa-instagram"></i></a><a href="#" class="text-white"><i class="fab fa-linkedin"></i></a></div></div>

<div class="col-lg-2 col-md-6 mb-4"><h6>Quick Links</h6>
<ul class="list-unstyled">
<li><a href="index.php" class="text-white-50">Home</a></li>
<li><a href="about.php" class="text-white-50">About Us</a></li>
<li><a href="contact.php" class="text-white-50">Contact</a></li>
<li><a href="login.php" class="text-white-50">Login</a></li>
</ul></div>

<div class="col-lg-3 col-md-6 mb-4"><h6>For Donors</h6>
<ul class="list-unstyled">
<li><a href="register_donor.php" class="text-white-50">Register as Donor</a></li>
<li><a href="donor/dashboard.php" class="text-white-50">Donor Dashboard</a></li>
<li><a href="donor/add-donation.php" class="text-white-50">Add Donation</a></li>
<li><a href="donor/manage-donations.php" class="text-white-50">Manage Donations</a></li>
</ul></div>

<div class="col-lg-3 col-md-6 mb-4"><h6>For NGOs</h6>
<ul class="list-unstyled">
<li><a href="register_ngo.php" class="text-white-50">Register as NGO</a></li>
<li><a href="ngo/dashboard.php" class="text-white-50">NGO Dashboard</a></li>
<li><a href="ngo/browse-donations.php" class="text-white-50">Browse Donations</a></li>
<li><a href="ngo/pickup-requests.php" class="text-white-50">Pickup Requests</a></li>
</ul></div>
</div>
<hr class="my-4">
<div class="row align-items-center">
<div class="col-md-6"><p class="mb-0">&copy; 2024 FoodSave. All rights reserved.</p></div>
<div class="col-md-6 text-md-end"><p class="mb-0">Prototype Phase - Online Food Waste Management</p></div>
</div>
</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
