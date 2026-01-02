<?php
    if(isset($_POST['submit']))
    {
       include '_admindb.php';
       $username = $_POST['username'];
       $password = $_POST['password'];
       $email = $_POST['email'];
       $number = $_POST['number'];
       $sql = "SELECT * FROM `admin` WHERE `Admin_name` = '$_POST[username]' AND `Admin_Email` = '$_POST[email]' AND `Admin_Pnumber` = '$_POST[number]' AND  `Admin_Password` = '$_POST[password]' ";
       $result = mysqli_query($conn, $sql);
       if(mysqli_num_rows($result) == 1)
       {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $_POST['username'];
            header("location: /REAL_ESTATE/UserInterface/Admin/dashboard.php");
       }  
       else
       {
         echo "<script>alert('Invalid Username or Password');</script>";
       }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
    <title>Admin - 3 Brother Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            max-width: 500px;
            margin: 30px auto;
            border-radius: 15px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .admin-container:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
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
            background-color: white;
        }
        
        .brand-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .admin-subtitle {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .admin-badge {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            min-width: 45px;
            justify-content: center;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        .admin-note {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .admin-note h6 {
            color: #0d6efd;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        /* .btn-admin {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            border: none;
            transition: all 0.3s;
        } */
        
        /* .btn-admin:hover {
            background: linear-gradient(45deg, #0a58ca, #0d6efd);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        } */
        
        .security-tag {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .security-tag i {
            color: #0d6efd;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .admin-container {
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
        
        .password-toggle {
            cursor: pointer;
            background-color: #f8f9fa;
            border-left: none;
        }
    </style>
</head>
<body>
<?php require '../Partials/_nav.php'?>
    <div class="container my-4">
        <div class="admin-container">
            <div class="brand-container">
                <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="3 Brother Real Estate Logo" class="brand-logo">
                <h2 class="brand-title">3 Brother Real Estate <br><span class="admin-badge">Admin</span></h2>
                <!-- <p class="admin-subtitle">Secure Admin Portal</p> -->
            </div>
            
            <form action="/REAL_ESTATE/UserInterface/Admin/Admin.php" method="post" class="w-75 mx-auto">
                <div class="mb-3">
                    <label for="username" class="form-label">Admin Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                        <input type="text" class="form-control" id="username" name="username" aria-describedby="emailHelp" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Admin Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="number" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="number" maxlength="10" class="form-control" id="number" name="number" aria-describedby="emailHelp" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <span class="input-group-text password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="submit" class="btn btn-primary">Login to Admin Panel</button>
                </div>
            </form>
            
            <div class="admin-note">
                <h6><i class="fas fa-exclamation-circle me-2"></i>Security Notice</h6>
                <p class="mb-0">This portal is restricted to authorized personnel only. Any unauthorized access attempts will be logged and investigated.</p>
            </div>
            
            <div class="security-tag">
                <p><i class="fas fa-shield-alt"></i> Secure Admin Portal</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>