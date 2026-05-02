<style>
.sidebar-fixed {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    background: linear-gradient(180deg,#145214,#0f3d0f); /* greenish gradient like NGO sidebar */
    box-shadow: 3px 0 8px rgba(0,0,0,0.3);
    color: white;
}

.sidebar-fixed .nav-link {
    padding: 12px 15px;
    font-size: 15px;
    display: flex;
    align-items: center;
    transition: 0.3s;
    color: white;
}

.sidebar-fixed .nav-link i {
    font-size: 18px;
    margin-right: 0.5rem;
}

.sidebar-fixed .nav-link.active {
    background: rgba(255,255,255,0.25);
    border-left: 4px solid #fff;
    font-weight: 600;
    border-radius: 5px;
}

.sidebar-fixed .nav-link:hover {
    background: rgba(255,255,255,0.2);
    border-radius: 5px;
    transform: translateX(5px);
}

.sidebar-header {
    padding: 1rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-align: center;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-header i {
    margin-right: 0.5rem;
}

.sidebar-fixed hr {
    border-color: rgba(255,255,255,0.3);
}
</style>

<div class="sidebar-fixed text-white p-0">
    <div class="sidebar-header">
        <i class="fas fa-user-shield me-2"></i>Admin Panel
    </div>
    <hr>
    <ul class="nav flex-column px-2">

        <li class="nav-item">
            <a class="nav-link active" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin_manage_accounts.php">
                <i class="fas fa-users"></i>Manage Donors
            </a>
        </li>

       

        <li class="nav-item">
            <a class="nav-link" href="admin_manage_foods.php">
                <i class="fas fa-utensils"></i>Food Donations
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin_manage_requests.php">
                <i class="fas fa-check-circle"></i>NGO Requests
            </a>
        </li>

        <li class="nav-item">
    <a class="nav-link" href="manage_drivers.php">
        <i class="fas fa-truck"></i>Manage Drivers
    </a>
</li>


        <li class="nav-item">
            <a class="nav-link" href="food_safety_guidelines.php">
                <i class="fas fa-book-medical"></i>Food Safety Guidelines
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin_reports.php">
                <i class="fas fa-chart-line"></i>Reports
            </a>
        </li>

        <hr>

        <li class="nav-item mt-2">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </li>

    </ul>
</div>
