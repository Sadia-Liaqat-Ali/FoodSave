<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

if ($_POST && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $message_type = 'danger';
    } else {
        // Check in users table first
        // (Don't hard-filter by status here; we want to show a clear message for pending/rejected users.)
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Fetch user data

        if ($user && password_verify($password, $user['password'])) {
            // Enforce account status after password verification
            if (isset($user['status']) && $user['status'] !== 'active') {
                if ($user['status'] === 'pending') {
                    $message = "Your account is pending admin approval.";
                } elseif ($user['status'] === 'rejected') {
                    $message = "Your account was rejected. Please contact admin.";
                } elseif ($user['status'] === 'suspended') {
                    $message = "Your account is suspended. Please contact admin.";
                } else {
                    $message = "Your account is not active. Please contact admin.";
                }
                $message_type = 'warning';
            } else {
                // Optional: also enforce role-specific status in donor/ngo tables (keeps things consistent).
                if ($user['user_type'] === 'ngo') {
                    $ngo_stmt = $conn->prepare("SELECT status FROM ngos WHERE user_id = ? LIMIT 1");
                    $ngo_stmt->bind_param("i", $user['id']);
                    $ngo_stmt->execute();
                    $ngo_row = $ngo_stmt->get_result()->fetch_assoc();
                    if ($ngo_row && isset($ngo_row['status']) && $ngo_row['status'] !== 'active') {
                        $message = "Your NGO account is " . $ngo_row['status'] . ". Please wait for approval.";
                        $message_type = 'warning';
                    }
                } elseif ($user['user_type'] === 'donor') {
                    $donor_stmt = $conn->prepare("SELECT status FROM donors WHERE user_id = ? LIMIT 1");
                    $donor_stmt->bind_param("i", $user['id']);
                    $donor_stmt->execute();
                    $donor_row = $donor_stmt->get_result()->fetch_assoc();
                    if ($donor_row && isset($donor_row['status']) && $donor_row['status'] !== 'active') {
                        $message = "Your donor account is " . $donor_row['status'] . ". Please contact admin.";
                        $message_type = 'warning';
                    }
                }
            }

            if ($message) {
                // Stop login flow if we set a status-related message above
            } else {
            // Update last login
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect based on user type
            switch ($user['user_type']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'donor':
                    header("Location: donor_dashboard.php");
                    break;
                case 'ngo':
                    header("Location: ngo_dashboard.php");
                    break;
            }
            exit();
            }
        } else {
            $message = "Invalid email or password.";
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodSave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #7C3AED);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="container">
        <div class="login-container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sign-in-alt me-2"></i>Login to FoodSave</h3>
                    <p class="mb-0">Enter your credentials to access your dashboard</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    <div class="text-center">
                        <p class="mb-2">Don't have an account?</p>
                        <a href="register_donor.php" class="btn btn-outline-primary btn-sm me-2">Register as Donor</a>
                        <a href="register_ngo.php" class="btn btn-outline-success btn-sm">Register as NGO</a>
                            <a href="register_drivers.php" class="btn btn-outline-dark btn-sm mt-2">Register as Driver</a>
                              <a href="login_drivers.php" class="btn btn-danger btn-sm mt-2">Login as Driver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>