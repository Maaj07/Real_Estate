<?php
$showAlert = false;
$showError = false;
// signup.php
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {    
        include 'Partials/_dbconnect.php';
        $username = $_POST["username"];
        $email = $_POST["email"];
        $number = $_POST["number"];
        $city = $_POST["city"];
        $password = $_POST["password"];
        $password1 = $_POST["password1"];
        // $exists = false;
        // Check whether this username exists
        $existsSql = "Select * from users where username = '$username'";
        $result = mysqli_query($conn, $existsSql);
        $numExistRows = mysqli_num_rows($result);
        if($numExistRows > 0)
        {
           // $exists = true;
            $showError = "Username already exists";
        }
        else 
        {
            // $exists = false;
            if(($password == $password1))
            {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                 // Insert the user into the database
                $sql = "INSERT INTO `users` (`username`, `email`, `number`, `city`, `password`, `dt`) VALUES 
                ('$username','$email','$number','$city','$hash', current_timestamp())";
                $result = mysqli_query($conn, $sql);
                if($result)
                {
                    $showAlert = true;
                }

            }
            else 
            {
                $showError = "Passwords do not match";
            }
        }
    }
?>
<!doctype html>
<html lang="en">
  <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
        <title>Signup - 3 Brother Real Estate</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .signup-container {
                max-width: 550px;
                margin: 30px auto;
                border-radius: 15px;
                padding: 30px;
                background-color: white;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            
            .signup-container:hover {
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
                transform: translateY(-5px);
            }
            
            .brand-container {
                text-align: center;
                margin-bottom: 25px;
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
            
            .form-control:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            }
            
            .input-group-text {
                background-color: #f8f9fa;
                border-right: none;
            }
            
            .input-group .form-control {
                border-left: none;
            }
            
            .input-group .form-control:focus {
                box-shadow: none;
                border-color: #ced4da;
            }
            
            .divider {
                display: flex;
                align-items: center;
                margin: 25px 0;
            }
            
            .divider-line {
                flex-grow: 1;
                height: 1px;
                background-color: #ddd;
            }
            
            .divider-text {
                padding: 0 15px;
                color: #7a7a7a;
                font-size: 14px;
            }
            
            .social-btn {
                display: block;
                width: 100%;
                padding: 12px;
                border: none;
                border-radius: 5px;
                margin-bottom: 12px;
                font-weight: 500;
                transition: all 0.3s;
            }
            
            .social-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
            
            .btn-facebook {
                background-color: #3b5998;
                color: white;
            }
            
            .btn-google {
                background-color: #dd4b39;
                color: white;
            }
            
            .btn-signup {
                padding: 12px;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            
            .login-link {
                color: #0d6efd;
                text-decoration: none;
                font-weight: 500;
            }
            
            .login-link:hover {
                text-decoration: underline;
            }
            
            .password-help {
                font-size: 0.85rem;
                color: #6c757d;
            }
            
            @media (max-width: 768px) {
                .signup-container {
                    margin: 20px;
                    padding: 25px 20px;
                }
                
                form {
                    width: 100% !important;
                }
            }
            
            .form-label {
                font-weight: 500;
                margin-bottom: 5px;
                color: #495057;
            }
        </style>
    </head>
  <body>
    <?php require 'Partials/_nav.php'?>

    <?php
        if($showAlert)
        {
            echo'<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> your account has been created successfully. Please login to continue.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <span aria-hidden="true">&times;</span>
                </div>';
        }
        if($showError)
        {
            echo'<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> '.$showError.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <span aria-hidden="true">&times;</span>
                </div>';
        }
    ?>

    <div class="container my-4">
        <div class="signup-container">
            <div class="brand-container">
                <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="3 Brother Real Estate Logo" class="brand-logo">
                <h2 class="brand-title">3 Brother Real Estate</h2>
                <p class="text-muted">Create your account</p>
            </div>
            
            <form action="/REAL_ESTATE/UserInterface/signup.php" method="post" class="w-75 mx-auto">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" maxlength="20" class="form-control" id="username" name="username" aria-describedby="emailHelp" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" maxlength="30" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="number" class="form-label">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="number" min="1000000000" max="9999999999" class="form-control" id="number" name="number" aria-describedby="emailHelp" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-city"></i></span>
                            <input type="text" class="form-control" id="city" name="city" aria-describedby="emailHelp" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="Password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="password1" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="Password1" name="password1" required>
                        </div>
                        <div class="password-help">Make sure to type the same password.</div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-signup">Sign Up</button>
                </div>
            </form>
            
            <!-- <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-text">or sign up with</div>
                <div class="divider-line"></div>
            </div>
            
            <div class="social-login">
                <button class="social-btn btn-facebook">
                    <i class="fab fa-facebook-f me-2"></i> Sign up with Facebook
                </button>
                <button class="social-btn btn-google">
                    <i class="fab fa-google me-2"></i> Sign up with Google
                </button>
            </div> -->
            
            <div class="text-center mt-4">
                <p>Already have an account? <a href="login.php" class="login-link">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    </body>
</html>