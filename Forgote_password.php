<?php
    include 'Partials/_dbconnect.php';
    session_start();
    // Set a cookie for the logged-in user (valid for 1 hour)
    if (isset($_SESSION['username'])) {
        setcookie('user', $_SESSION['username'], time() + 3600, "/");
    }
    
    // Initialize variables
    $username = $old_password = $new_password = $confirm_password = "";
    $username_err = $old_password_err = $new_password_err = $confirm_password_err = "";
    $success_msg = $error_msg = "";
    
    // Process form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter your username.";
        } else {
            $username = trim($_POST["username"]);
        }
        
        // Validate old password (optional)
        if (!empty(trim($_POST["old_password"]))) {
            $old_password = trim($_POST["old_password"]);
            
            // Check if username and old password match
            $sql = "SELECT password FROM users WHERE username = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $username;
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (!password_verify($old_password, $hashed_password)) {
                                $old_password_err = "The old password you entered is incorrect.";
                            }
                        }
                    } else {
                        $username_err = "No account found with that username.";
                    }
                } else {
                    $error_msg = "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $new_password_err = "Password must have at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($new_password_err) && ($new_password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }
        
        // Check input errors before updating the database
        if (empty($username_err) && empty($new_password_err) && empty($confirm_password_err) && empty($old_password_err)) {
            // Prepare an update statement
            $sql = "UPDATE users SET password = ? WHERE username = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_username);
                
                // Set parameters
                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                $param_username = $username;
                
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Password updated successfully!";
                    // Clear form fields
                    $username = $old_password = $new_password = $confirm_password = "";
                } else {
                    $error_msg = "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - 3 Brother Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }
        .forgot-password-container {
            max-width: 450px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .forgot-password-container:hover
        {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        .forgot-password-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .forgot-password-header i {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 15px;
        }
        .form-label {
            font-weight: 600;
        }
        .optional-text {
            font-size: 0.85rem;
            color: #6c757d;
            font-style: italic;
        }
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .alert {
            border-radius: 8px;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #0d6efd;
            padding: 3px;
        }
        
        .brand-title {
            color: #2c3e50;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php require 'Partials/_nav.php'?>
    
    <div class="container">
        <div class="forgot-password-container">
            <div class="forgot-password-header">
                <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="3 Brother Real Estate Logo" class="brand-logo">
                <h2>Reset Your Password</h2>
                <p class="text-muted">Enter your details to reset your password</p>
            </div>
            
            <?php 
            if (!empty($success_msg)) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-check-circle me-2"></i>' . $success_msg;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
            if (!empty($error_msg)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-exclamation-circle me-2"></i>' . $error_msg;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                           id="username" name="username" value="<?php echo $username; ?>" placeholder="Enter your username">
                    <div class="invalid-feedback">
                        <?php echo $username_err; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="old_password" class="form-label">Old Password <span class="optional-text">(optional)</span></label>
                    <input type="password" class="form-control <?php echo (!empty($old_password_err)) ? 'is-invalid' : ''; ?>" 
                           id="old_password" name="old_password" value="<?php echo $old_password; ?>" placeholder="Enter your old password">
                    <div class="invalid-feedback">
                        <?php echo $old_password_err; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" 
                           id="new_password" name="new_password" value="<?php echo $new_password; ?>" placeholder="Enter your new password">
                    <div class="invalid-feedback">
                        <?php echo $new_password_err; ?>
                    </div>
                    <div class="password-requirements">
                        <i class="fas fa-info-circle me-1"></i> Password must be at least 6 characters long.
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                           id="confirm_password" name="confirm_password" value="<?php echo $confirm_password; ?>" placeholder="Confirm your new password">
                    <div class="invalid-feedback">
                        <?php echo $confirm_password_err; ?>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sync-alt me-2"></i>Update Password
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <p class="mb-0">Remember your password? <a href="login.php" class="text-decoration-none">Back to Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous"></script>
</body>
</html>