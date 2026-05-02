<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

if ($_POST && isset($_POST['register'])) {
    // Get form data
    $organization_name = trim($_POST['organization_name']);
    $registration_number = trim($_POST['registration_number']);
    $contact_name = trim($_POST['contact_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $organization_type = $_POST['organization_type'];
    $website = trim($_POST['website']);
    $address = trim($_POST['address']);
    $mission = trim($_POST['mission']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($organization_name) || empty($registration_number) || empty($contact_name) || 
        empty($email) || empty($phone) || empty($organization_type) || empty($address) || 
        empty($mission) || empty($password)) {
        $message = "Please fill in all required fields.";
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = 'danger';
    } elseif (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $message = "Please enter a valid website URL.";
        $message_type = 'danger';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Start MySQLi transaction
        $conn->begin_transaction();

        try {
            // 1. Insert into users table
            // NGOs typically require approval, so keep status as pending until admin activates.
            $user_sql = "INSERT INTO users (name, email, password, user_type, status, created_at) VALUES (?, ?, ?, 'ngo', 'pending', NOW())";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("sss", $organization_name, $email, $hashed_password);
            $user_stmt->execute();

            if ($user_stmt->errno) {
                throw new Exception("User insert failed: " . $user_stmt->error);
            }

            $user_id = $conn->insert_id;

            // 2. Insert into ngos table
            $ngo_sql = "INSERT INTO ngos (user_id, organization_name, registration_number, contact_name, email, phone, organization_type, website, address, mission, password, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $ngo_stmt = $conn->prepare($ngo_sql);
            $ngo_stmt->bind_param("issssssssss", $user_id, $organization_name, $registration_number, $contact_name, $email, $phone, $organization_type, $website, $address, $mission, $hashed_password);
            $ngo_stmt->execute();

            if ($ngo_stmt->errno) {
                throw new Exception("NGO insert failed: " . $ngo_stmt->error);
            }

            // Commit transaction
            $conn->commit();

            $message = "Registration successful! Your account is pending admin approval.";
            $message_type = 'success';

        } catch (Exception $e) {
            $conn->rollback();
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $message = "This email is already registered. Please use another email or login.";
            } else {
                $message = "Registration failed. " . $e->getMessage();
            }
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
    <title>Register as NGO/Food Bank - FoodSave</title>
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
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #4a49cc, #5a2d81);
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
            background: linear-gradient(135deg, var(--primary-color), #6f42c1);
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }
        
        .card-body {
            padding: 3rem;
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
        
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
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
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #fff;
            transform: translateX(-5px);
        }
        
        .required {
            color: #dc3545;
        }
        
        .approval-notice {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .registration-container {
                padding: 10px;
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
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-hands-helping me-3"></i>Register as NGO/Food Bank
                    </h2>
                    <p class="mb-0 mt-2">Join our network of organizations helping to distribute food to those in need</p>
                </div>
                <div class="card-body">
                    <div class="approval-notice">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> NGO registrations require admin approval to ensure authenticity. You will be notified via email once your account is verified and approved.
                    </div>
                    
                    <form method="POST" id="ngoRegistrationForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Name <span class="required">*</span></label>
                                <input type="text" class="form-control" name="organization_name" required 
                                       placeholder="Enter organization name" value="<?php echo isset($_POST['organization_name']) ? htmlspecialchars($_POST['organization_name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number <span class="required">*</span></label>
                                <input type="text" class="form-control" name="registration_number" required 
                                       placeholder="Official registration number" value="<?php echo isset($_POST['registration_number']) ? htmlspecialchars($_POST['registration_number']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person Name <span class="required">*</span></label>
                                <input type="text" class="form-control" name="contact_name" required 
                                       placeholder="Primary contact person" value="<?php echo isset($_POST['contact_name']) ? htmlspecialchars($_POST['contact_name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email <span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" required 
                                       placeholder="Official email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="required">*</span></label>
                                <input type="tel" class="form-control" name="phone" required 
                                       placeholder="Contact phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Type <span class="required">*</span></label>
                                <select class="form-select" name="organization_type" required>
                                    <option value="">Select Organization Type</option>
                                    <option value="ngo" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'ngo') ? 'selected' : ''; ?>>Non-Governmental Organization (NGO)</option>
                                    <option value="food_bank" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'food_bank') ? 'selected' : ''; ?>>Food Bank</option>
                                    <option value="charity" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'charity') ? 'selected' : ''; ?>>Charity Organization</option>
                                    <option value="community_center" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'community_center') ? 'selected' : ''; ?>>Community Center</option>
                                    <option value="religious" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'religious') ? 'selected' : ''; ?>>Religious Organization</option>
                                    <option value="government" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'government') ? 'selected' : ''; ?>>Government Agency</option>
                                    <option value="other" <?php echo (isset($_POST['organization_type']) && $_POST['organization_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website (Optional)</label>
                                <input type="url" class="form-control" name="website" 
                                       placeholder="https://www.yourorganization.org" value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="required">*</span></label>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="Create a secure password" id="password">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="required">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required 
                                       placeholder="Confirm your password" id="confirm_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Address <span class="required">*</span></label>
                                <textarea class="form-control" name="address" rows="2" required 
                                          placeholder="Complete organization address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Mission Statement <span class="required">*</span></label>
                            <textarea class="form-control" name="mission" rows="4" required 
                                      placeholder="Describe your organization's mission, the communities you serve, and how you plan to use donated food"><?php echo isset($_POST['mission']) ? htmlspecialchars($_POST['mission']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="required">*</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="verification" required>
                                <label class="form-check-label" for="verification">
                                    I confirm that all information provided is accurate and that our organization is legally registered <span class="required">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button type="submit" name="register" class="btn btn-primary btn-lg me-md-2">
                                <i class="fas fa-hands-helping me-2"></i>Register NGO
                            </button>
                            <a href="login.php" class="btn btn-outline-secondary btn-lg">
                                Already registered? Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Form validation
        document.getElementById('ngoRegistrationForm').addEventListener('submit', function(e) {
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
        });

        // Character counter for mission statement
        const missionTextarea = document.querySelector('textarea[name="mission"]');
        const charCounter = document.createElement('div');
        charCounter.className = 'text-muted small mt-1';
        charCounter.textContent = '0 characters';
        missionTextarea.parentNode.appendChild(charCounter);

        missionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} characters`;
            
            if (length < 50) {
                charCounter.className = 'text-danger small mt-1';
                charCounter.textContent = `${length} characters (minimum 50 recommended)`;
            } else if (length > 500) {
                charCounter.className = 'text-warning small mt-1';
                charCounter.textContent = `${length} characters (maximum 500 recommended)`;
            } else {
                charCounter.className = 'text-success small mt-1';
                charCounter.textContent = `${length} characters`;
            }
        });
    </script>
</body>
</html>