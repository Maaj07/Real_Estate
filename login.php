<?php
    include 'Partials/_dbconnect.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
    <title>Login - 3 Brother Real Estate</title>
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
        
        .login-container {
            max-width: 450px;
            margin: 30px auto;
            border-radius: 15px;
            padding: 30px;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
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
        
        .btn-login {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .signup-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        
        .signup-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
                padding: 25px 20px;
            }
        }
    </style>
</head>
  <body>
    <?php require 'Partials/_nav.php'?>
    <?php require 'Autho.php';?>

    <?php
        if($login)
        {
            echo'<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> you Are Logged in.
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
        <div class="login-container">
            <div class="brand-container">
                <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="3 Brother Real Estate Logo" class="brand-logo">
                <h2 class="brand-title">3 Brother Real Estate</h2>
                <p class="text-muted">Login to your account</p>
            </div>
            
            <form action="/REAL_ESTATE/UserInterface/Autho.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" aria-describedby="emailHelp" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="Password" name="password" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                </div>
            </form>
            
            <!-- <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-text">or continue with</div>
                <div class="divider-line"></div>
            </div>
            
             <div class="social-login">
                <button class="social-btn btn-facebook">
                    <i class="fab fa-facebook-f me-2"></i> Login with Facebook
                </button>
                <button class="social-btn btn-google">
                    <i class="fab fa-google me-2"></i> Login with Google
                </button>
            </div> -->
            <div class="text-center mt-4">
                <p><a href="Forgote_password.php" class="signup-link">Forgote Password</a></p>
            </div>
            <div class="text-center mt-4">
                <p>Don't have an account? <a href="signup.php" class="signup-link">Sign up here</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    </body>
</html>