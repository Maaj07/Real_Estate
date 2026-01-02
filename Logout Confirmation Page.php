<?php
    include 'Partials/_dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0b1f3a; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .confirmation-card {
            /* background: white; */
            border-radius: 20px;
            /* box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); */
            padding: 40px;
            max-width: 500px;
            width: 90%;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #0d6efd;
            padding: 3px;
            animation: pulse 2s infinite;
        }

        /* .logout-icon {
            font-size: 80px;
            color: #667eea;
            animation: pulse 2s infinite;
        } */

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .confirmation-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .confirmation-text {
            color: #666;
            text-align: center;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-custom {
            padding: 12px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-yes {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-yes:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-no {
            background: #f0f0f0;
            color: #333;
        }

        .btn-no:hover {
            background: #e0e0e0;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Loader Styles */
        .loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #0b1f3a; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .loader-overlay.active {
            display: flex;
        }

        .loader {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
            animation: fadeInOut 1.5s infinite;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        @media (max-width: 576px) {
            .confirmation-card {
                padding: 30px 20px;
            }

            .confirmation-title {
                font-size: 24px;
            }

            .logout-icon {
                font-size: 60px;
            }

            .btn-custom {
                padding: 10px 30px;
                font-size: 14px;
                width: 100%;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Loader Overlay -->
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader"></div>
        <div class="loader-text">Logging out...</div>
    </div>

    <!-- Confirmation Card -->
    <div class="confirmation-card">
        <div class="icon-container">
            <!-- <i class="fas fa-sign-out-alt logout-icon"></i> -->
            <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="3 Brother Real Estate Logo" class="brand-logo">
        </div>
        <h1 class="confirmation-title"> <i class="fas fa-sign-out-alt logout-icon"></i> Logout Confirmation</h1>
        <p class="confirmation-text">Are you sure you want to log out? You will need to log in again to access your account.</p>
        
        <div class="button-group">
            <button class="btn btn-custom btn-yes" onclick="confirmLogout()">
                <i class="fas fa-check"></i> Yes, Logout
            </button>
            <button class="btn btn-custom btn-no" onclick="cancelLogout()">
                <i class="fas fa-times"></i> No, Stay
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            // Show loader
            document.getElementById('loaderOverlay').classList.add('active');
            
            // Simulate logout process and redirect after 2 seconds
            setTimeout(function() {
                // Redirect to login page
                window.location.href = '/REAL_ESTATE/UserInterface/logout.php';
            }, 2000);
        }

        function cancelLogout() {
            // Redirect to welcome page
            window.location.href = '/REAL_ESTATE/UserInterface/welcome.php';
        }
    </script>
</body>
</html>