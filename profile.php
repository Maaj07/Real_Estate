<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true){
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
}

// Database connection with comprehensive error handling
$conn = null;
$db_error = false;

// Try to include the database connection file
if (file_exists('_dbconnect.php')) {
    try {
        require '_dbconnect.php';
        // Verify connection is established
        if (!isset($conn) || !$conn || mysqli_connect_error()) {
            throw new Exception("Connection variable not set or connection failed");
        }
    } catch (Exception $e) {
        $db_error = true;
    }
} else {
    $db_error = true;
}

// If the include failed, try direct connection with common settings
if ($db_error) {
    try {
        $conn = mysqli_connect("localhost", "root", "", "real_estate");
        if (!$conn) {
            throw new Exception(mysqli_connect_error());
        }
    } catch (Exception $e) {
        die("
        <div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
            <h3>Database Connection Error</h3>
            <p>Unable to connect to the database. Please check:</p>
            <ul>
                <li>Database server is running</li>
                <li>Database credentials are correct</li>
                <li>Database exists</li>
                <li>_dbconnect.php file exists and is configured properly</li>
            </ul>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            <a href='/REAL_ESTATE/UserInterface/logout.php' style='color: #721c24;'>← Logout and try again</a>
        </div>");
    }
}

// Initialize variables
$user = null;
$update_message = "";
$error_message = "";

// Get user data using prepared statements
function getUserData($conn) {
    // Check if connection is valid
    if (!$conn) {
        return null;
    }
    
    if(isset($_SESSION['sno'])) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE sno = ?");
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['sno']);
    } else if(isset($_SESSION['username'])) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    } else {
        return null;
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return null;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Store sno in session for future use
    if($user && !isset($_SESSION['sno'])) {
        $_SESSION['sno'] = $user['sno'];
    }
    
    return $user;
}

$user = getUserData($conn);

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST' && $user){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    
    // Validate input
    if(empty($username) || empty($email)) {
        $error_message = "Username and email are required fields.";
    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another user
        $email_check = mysqli_prepare($conn, "SELECT sno FROM users WHERE email = ? AND sno != ?");
        mysqli_stmt_bind_param($email_check, "si", $email, $_SESSION['sno']);
        mysqli_stmt_execute($email_check);
        $email_result = mysqli_stmt_get_result($email_check);
        
        if(mysqli_num_rows($email_result) > 0) {
            $error_message = "This email is already registered with another account.";
        } else {
            // Check if username already exists for another user
            $username_check = mysqli_prepare($conn, "SELECT sno FROM users WHERE username = ? AND sno != ?");
            mysqli_stmt_bind_param($username_check, "si", $username, $_SESSION['sno']);
            mysqli_stmt_execute($username_check);
            $username_result = mysqli_stmt_get_result($username_check);
            
            if(mysqli_num_rows($username_result) > 0) {
                $error_message = "This username is already taken by another user.";
            } else {
                // Update user data - using correct column names from your database
                $update_sql = "UPDATE users SET username = ?, email = ?, number = ?, city = ? WHERE sno = ?";
                
                // Check if database connection is still valid
                if (!$conn || mysqli_connect_error()) {
                    $error_message = "Database connection lost. Please try again.";
                } else {
                    $stmt = mysqli_prepare($conn, $update_sql);
                    
                    if($stmt) {
                        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $phone, $city, $_SESSION['sno']);
                        
                        if(mysqli_stmt_execute($stmt)) {
                            $update_message = "Profile updated successfully!";
                            // Update session username if it was changed
                            $_SESSION['username'] = $username;
                            // Refresh user data
                            $user = getUserData($conn);
                        } else {
                            $error_message = "Error updating profile: " . mysqli_stmt_error($stmt);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error_message = "Database prepare error: " . mysqli_error($conn);
                    }
                }
            }
            mysqli_stmt_close($username_check);
        }
        mysqli_stmt_close($email_check);
    }
}

// Get user statistics based on your database tables
// function getUserStats($conn, $sno) {
//     $stats = [
//         'properties_viewed' => 0,
//         'favorites' => 0,
//         'inquiries' => 0
//     ];
    
//     // Get inquiries count from viewing_requests table
//     $inquiries_stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM viewing_requests WHERE user_email = ?");
//     if ($inquiries_stmt) {
//         $user_email = $_SESSION['username']; // Using username as email since your table uses username for login
//         mysqli_stmt_bind_param($inquiries_stmt, "s", $user_email);
//         mysqli_stmt_execute($inquiries_stmt);
//         $result = mysqli_stmt_get_result($inquiries_stmt);
//         $stats['inquiries'] = mysqli_fetch_array($result)[0] ?? 0;
//         mysqli_stmt_close($inquiries_stmt);
//     }
    
//     // For properties viewed and favorites, you can implement these when you add the functionality
//     // Currently setting some placeholder values that make sense for a real estate site
//     $stats['properties_viewed'] = rand(8, 25);
//     $stats['favorites'] = rand(3, 15);
    
//     return $stats;
// }

// $stats = $user ? getUserStats($conn, $user['sno']) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - 3 Brother Real Estate</title>
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0b1f3a;
            --secondary-color: #4e9eff;
            --accent-color: #00ffcc;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 15px 35px rgba(0,0,0,0.1);
            --hover-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }
        
        .profile-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .profile-header {
            background: var(--gradient-bg);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            background-color: #0b1f3a;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            margin: 0 auto 1.5rem;
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: white;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .profile-username {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .profile-member-since {
            opacity: 0.9;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }
        
        .profile-body {
            padding: 3rem 2rem;
        }
        
        .info-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(78, 158, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--secondary-color), var(--accent-color));
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            padding: 2rem;
            border-radius: 1rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(78, 158, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(78, 158, 255, 0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 158, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 1rem 2.5rem;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 158, 255, 0.3);
        }
        
        .btn-outline-primary, .btn-outline-secondary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover, .btn-outline-secondary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 158, 255, 0.3);
        }
        
        .alert {
            border-radius: 0.75rem;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .alert-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(45deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 0.75rem;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .quick-action-btn:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            transform: translateY(-2px);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-header {
                padding: 3rem 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .profile-body {
                padding: 2rem 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 2rem 1rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .profile-username {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .profile-body {
                padding: 1.5rem 1rem;
            }
            
            .info-card {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .profile-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }
            
            .btn-primary {
                padding: 0.75rem 1.5rem;
                width: 100%;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        
        /* Loading animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <?php 
    // Include navigation
    $nav_paths = [
        '_nav.php',
        '../_nav.php',
        '../../_nav.php',
        'Partials/_nav.php',
        '../Partials/_nav.php'
    ];
    
    foreach($nav_paths as $path) {
        if(file_exists($path)) {
            include $path;
            break;
        }
    }
    ?>
    
    <div class="profile-container">
        <?php if(!$user): ?>
            <div class="alert alert-danger fade-in">
                <h4><i class="fa-solid fa-exclamation-triangle me-2"></i>Error</h4>
                <p>Unable to load user profile. Please try logging out and logging back in.</p>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        <?php else: ?>
            <div class="profile-card fade-in">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h2 class="profile-username"><?php echo htmlspecialchars($user['username'] ?? $_SESSION['username']); ?></h2>
                    <p class="profile-member-since mb-0">
                        <i class="fa-solid fa-calendar-alt me-2"></i>
                        Member since <?php echo date('F Y', strtotime($user['dt'] ?? 'now')); ?>
                    </p>
                </div>
                
                <div class="profile-body">
                    <?php if($update_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-check-circle me-2"></i><?php echo $update_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- User Statistics -->
                    <!-- <div class="stats-grid fade-in stagger-1">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-eye"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats['properties_viewed']; ?></div>
                            <div class="stat-label">Properties Viewed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-heart"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats['favorites']; ?></div>
                            <div class="stat-label">Favorites</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats['inquiries']; ?></div>
                            <div class="stat-label">Inquiries</div>
                        </div>
                    </div> -->
                    
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="info-card fade-in stagger-2">
                                <h4 class="section-title">
                                    <i class="fa-solid fa-user-edit"></i>
                                    Edit Profile Information
                                </h4>
                                <form method="POST" action="profile.php" id="profileForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username *</label>
                                            <input type="text" class="form-control" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username'] ?? $_SESSION['username']); ?>" 
                                                   required maxlength="20">
                                            <small class="text-muted">Your unique username</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Registration Date</label>
                                            <input type="text" class="form-control" 
                                                   value="<?php echo date('M j, Y', strtotime($user['dt'] ?? 'now')); ?>" 
                                                   disabled>
                                            <small class="text-muted">Account creation date</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                                   required maxlength="30">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?php echo htmlspecialchars($user['number'] ?? ''); ?>"
                                                   maxlength="10" pattern="[0-9]{10}">
                                            <small class="text-muted">10-digit phone number</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city" 
                                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                                   maxlength="20">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">User ID</label>
                                            <input type="text" class="form-control" 
                                                   value="#<?php echo $user['sno']; ?>" 
                                                   disabled>
                                            <small class="text-muted">Your unique identifier</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-2"></i>Update Profile
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-undo me-2"></i>Reset Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-lg-5">
                            <div class="info-card fade-in stagger-3">
                                <h4 class="section-title">
                                    <i class="fa-solid fa-shield-alt"></i>
                                    Account Security
                                </h4>
                                <div class="mb-3">
                                    <strong>Account Status:</strong>
                                    <span class="badge bg-success ms-2">Active</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Last Activity:</strong><br>
                                    <small class="text-muted">
                                        <?php 
                                        $lastActivity = $user['dt'] ?? 'now';
                                        echo date('M j, Y g:i A', strtotime($lastActivity)); 
                                        ?>
                                    </small>
                                </div>
                                <div class="quick-actions">
                                    <a href="/REAL_ESTATE/UserInterface/Forgote_password.php" class="quick-action-btn">
                                        <i class="fa-solid fa-key"></i>
                                        Change Password
                                    </a>
                                    <!-- <a href="notifications.php" class="quick-action-btn">
                                        <i class="fa-solid fa-bell"></i>
                                        Notifications
                                    </a>
                                    <a href="privacy-settings.php" class="quick-action-btn">
                                        <i class="fa-solid fa-user-shield"></i>
                                        Privacy Settings
                                    </a> -->
                                    <a href="/REAL_ESTATE/UserInterface/logout.php" class="quick-action-btn text-danger">
                                        <i class="fa-solid fa-sign-out-alt"></i>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.getElementById('profileForm');
            if(form) {
                form.addEventListener('submit', function(e) {
                    const username = form.querySelector('input[name="username"]').value.trim();
                    const email = form.querySelector('input[name="email"]').value.trim();
                    const phone = form.querySelector('input[name="phone"]').value.trim();
                    
                    if(!username || !email) {
                        e.preventDefault();
                        alert('Username and email are required fields.');
                        return false;
                    }
                    
                    // Email validation
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if(!emailPattern.test(email)) {
                        e.preventDefault();
                        alert('Please enter a valid email address.');
                        return false;
                    }
                    
                    // Phone validation (if provided)
                    if(phone && !/^\d{10}$/.test(phone)) {
                        e.preventDefault();
                        alert('Please enter a valid 10-digit phone number.');
                        return false;
                    }
                });
            }
            
            // Add loading state to form submission
            const submitBtn = form?.querySelector('button[type="submit"]');
            if(submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...';
                    submitBtn.disabled = true;
                });
            }
        });
        
        // Add intersection observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);
        
        // Observe all cards that should animate
        document.querySelectorAll('.stat-card, .info-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>

<?php 
if(isset($conn)) {
    mysqli_close($conn); 
}
?>