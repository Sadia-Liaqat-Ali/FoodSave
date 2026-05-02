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

        <h5><i class="fas fa-hands-helping me-2"></i>NGO Panel</h5>
        <hr>

        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link text-white active" href="ngo_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="ngo_requests.php">
                    <i class="fas fa-handshake me-2"></i>My Requests
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="organization_info.php">
                    <i class="fas fa-building me-2"></i>Organization Info
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="urgent_alerts.php">
                    <i class="fas fa-bell me-2"></i>Alerts
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="ngo_feedbacks.php">
                    <i class="fas fa-comment-dots me-2"></i>Feedback
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
