<style>
    .sidebar-fixed {
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        background: linear-gradient(180deg,#145214,#0f3d0f);
        box-shadow: 3px 0 8px rgba(0,0,0,0.3);
    }

    .sidebar-fixed h5 {
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-align: center;
    }

    .nav-link {
        padding: 12px 15px;
        font-size: 15px;
        display: flex;
        align-items: center;
        transition: 0.3s;
    }

    .nav-link i {
        font-size: 18px;
    }

    .nav-link.active {
        background: rgba(255,255,255,0.25);
        border-left: 4px solid #fff;
        font-weight: 600;
        border-radius: 5px;
    }

    .nav-link:hover {
        background: rgba(255,255,255,0.2);
        border-radius: 5px;
        transform: translateX(5px);
    }

    .sidebar-fixed hr {
        border-color: rgba(255,255,255,0.3);
    }
</style>

<div class="sidebar-fixed text-white p-0">
    <div class="p-3">

        <h5><i class="fas fa-hand-holding-heart me-2"></i>Donor Panel</h5>
        <hr>

        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link text-white active" href="donor_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="add_donation.php">
                    <i class="fas fa-plus-circle me-2"></i>Add Donation
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="donor_my_food_donations.php">
                    <i class="fas fa-box-open me-2"></i>All Donations
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="my_donations_requests.php">
                    <i class="fas fa-handshake me-2"></i>Donation Requests
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="donor_impact.php">
                    <i class="fas fa-chart-line me-2"></i>Impact
                </a>
            </li>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>

        </ul>

    </div>
</div>
```
