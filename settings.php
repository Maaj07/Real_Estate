<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true) {
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
}

include '../Partials/_dbconnect.php';

$update_success = false;
$update_error = '';
$password_success = false;
$password_error = '';

// Fetch current user data
$sno = $_SESSION['sno'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE sno = '$sno'");
$user_data = mysqli_fetch_assoc($user_query);

// Handle profile update
if(isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    // $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    // Check if email already exists (except current user)
    // $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$new_email' AND email != '$email'");
    
    // if(mysqli_num_rows($check_email) > 0) {
    //     $update_error = "Email already exists!";
    } else {
        $update_query = "UPDATE users SET username = '$username', email = '$new_email', number = '$number', city = '$city' WHERE email = '$email'";
        
        if(mysqli_query($conn, $update_query)) {
            $_SESSION['username'] = $username;
            // $_SESSION['email'] = $new_email;
            $email = $new_email;
            $update_success = true;
            
            // Refresh user data
            $user_query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
            $user_data = mysqli_fetch_assoc($user_query);
        } else {
            $update_error = "Error updating profile!";
        }
    }

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if(password_verify($current_password, $user_data['password'])) {
        if($new_password === $confirm_password) {
            if(strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
                
                if(mysqli_query($conn, $password_query)) {
                    $password_success = true;
                } else {
                    $password_error = "Error updating password!";
                }
            } else {
                $password_error = "Password must be at least 6 characters!";
            }
        } else {
            $password_error = "New passwords do not match!";
        }
    } else {
        $password_error = "Current password is incorrect!";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
    <title>Settings - <?php echo $_SESSION['username']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0b1f3a;
            --secondary-color: #1a3456;
            --accent-color: #dc3545;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .settings-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .settings-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .settings-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .settings-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px 60px;
        }
        
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .settings-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 25px;
            border-bottom: none;
        }
        
        .card-header-custom h5 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .card-header-custom i {
            margin-right: 12px;
            font-size: 1.4rem;
        }
        
        .card-body-custom {
            padding: 30px 25px;
        }
        
        .form-label {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(11, 31, 58, 0.15);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        }
        
        .alert-custom {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .user-info-display {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .user-info-display p {
            margin: 8px 0;
            color: var(--primary-color);
            font-size: 0.95rem;
        }
        
        .user-info-display strong {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .password-requirements {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin: 4px 0;
        }
        
        @media (max-width: 768px) {
            .settings-header h1 {
                font-size: 2rem;
            }
            
            .settings-header {
                padding: 40px 0 30px;
            }
            
            .card-body-custom {
                padding: 20px 15px;
            }
            
            .btn-primary-custom,
            .btn-danger-custom {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php require '../Partials/_nav.php'?>
    
    <div class="settings-header">
        <div class="container text-center">
            <h1><i class="fas fa-cog"></i> Account Settings</h1>
            <p>Manage your profile information and security settings</p>
        </div>
    </div>
    
    <div class="settings-container">
        <!-- Profile Information Card -->
        <div class="settings-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-user-circle"></i> Profile Information</h5>
            </div>
            <div class="card-body-custom">
                <?php if($update_success): ?>
                    <div class="alert alert-success alert-custom">
                        <i class="fas fa-check-circle"></i> Profile updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if($update_error): ?>
                    <div class="alert alert-danger alert-custom">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $update_error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="user-info-display">
                    <p><strong><i class="fas fa-calendar-alt"></i> Member Since:</strong> <?php echo date('F d, Y', strtotime($user_data['dt'])); ?></p>
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo $user_data['username']; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo $user_data['email']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="number" class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="text" class="form-control" id="number" name="number" 
                                   value="<?php echo $user_data['number']; ?>" maxlength="10" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">
                                <i class="fas fa-city"></i> City
                            </label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo $user_data['city']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" name="update_profile" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password Card -->
        <div class="settings-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-lock"></i> Change Password</h5>
            </div>
            <div class="card-body-custom">
                <?php if($password_success): ?>
                    <div class="alert alert-success alert-custom">
                        <i class="fas fa-check-circle"></i> Password changed successfully!
                    </div>
                <?php endif; ?>
                
                <?php if($password_error): ?>
                    <div class="alert alert-danger alert-custom">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-key"></i> Current Password
                        </label>
                        <input type="password" class="form-control" id="current_password" 
                               name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-lock"></i> New Password
                        </label>
                        <input type="password" class="form-control" id="new_password" 
                               name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirm New Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                    </div>
                    
                    <div class="password-requirements">
                        <strong><i class="fas fa-info-circle"></i> Password Requirements:</strong>
                        <ul>
                            <li>Must be at least 6 characters long</li>
                            <li>Should contain a mix of letters and numbers</li>
                            <li>Avoid using common passwords</li>
                        </ul>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="submit" name="change_password" class="btn btn-danger-custom">
                            <i class="fas fa-shield-alt"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require '../Partials/_footer.php'?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Phone number validation
        document.getElementById('number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>