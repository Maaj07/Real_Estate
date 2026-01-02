<?php
$login = false;
$showError = false;
// login.php
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {    
        include 'Partials/_dbconnect.php';
        $username = $_POST["username"];
        $password = $_POST["password"];
            
        $sql = "Select * from users where username = '$username'";
        $result = mysqli_query($conn, $sql);
        $num = mysqli_num_rows($result);
        if($num == 1)
       {
            setcookie("username", $username, time() + 3600, "/");
            $_COOKIE['username'] = $username;
            while($row = mysqli_fetch_assoc($result))
            {
                if(password_verify($password, $row['password']))
                {
                    $login = true;
                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $username;

                    echo ' <div class="success-container">
                                <div class="loader"></div>
                                <h3>Login successful! Redirecting... To Home Page </h3>
                            </div>';

                    header("refresh:3; url=/REAL_ESTATE/UserInterface/welcome.php");
                }
                
                 else 
                {
                    $showError = "Invalid Credentials";
                    echo"<div class='error-message'><h3>$showError</h3></div>";
                    echo '<div class="success-container">
                                <div class="loader"></div>
                                <h3>Login Unsuccessful! Redirecting... To Login Page</h3>
                          </div>';
                    header("refresh:3; url=/REAL_ESTATE/UserInterface/login.php");
                }
            }
       }
        else 
        {
            $showError = "Invalid Credentials";
        }
    }
?>
<!-- <link rel="stylesheet" href="../CSS/Autho.css"> -->
<style>

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .success-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 50px 60px;
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        text-align: center;
        z-index: 10000;
        animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        min-width: 380px;
        border: 3px solid #f0f0f0;
    }

    @keyframes popIn {
        0% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.7);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.05);
        }
        100% {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    .success-container h3 {
        color: #28a745;
        font-weight: 600;
        margin-top: 25px;
        font-size: 1.35rem;
        line-height: 1.6;
    }

    /* Error Message */
    .error-message {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
        padding: 18px 24px;
        border-radius: 12px;
        margin-bottom: 25px;
        text-align: center;
        animation: shake 0.5s ease, fadeInDown 0.4s ease;
        box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
    }

    .error-message h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
        20%, 40%, 60%, 80% { transform: translateX(8px); }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loader Animation (Your Custom Loader) */
    .loader {
        --s: 25px;
        --g: 5px;  
        
        height: calc(1.353*var(--s) + var(--g));
        aspect-ratio: 3;
        margin: 25px auto;
        background:
            linear-gradient(#667eea 0 0) left/33% 100% no-repeat,
            conic-gradient(from -90deg at var(--s) calc(0.353*var(--s)),
            #fff 135deg,#666 0 270deg,#aaa 0); 
        background-blend-mode: multiply;
        --_m:
            linear-gradient(to bottom right,
            #0000 calc(0.25*var(--s)),#000 0 calc(100% - calc(0.25*var(--s)) - 1.414*var(--g)),#0000 0),
            conic-gradient(from -90deg at right var(--g) bottom var(--g),#000 90deg,#0000 0);
        -webkit-mask: var(--_m);
                mask: var(--_m);
        background-size: calc(100%/3) 100%;
        -webkit-mask-size: calc(100%/3) 100%;
                mask-size: calc(100%/3) 100%;
        -webkit-mask-composite: source-in;
                mask-composite: intersect;
        animation: l7 steps(3) 1.5s infinite;
    }

    @keyframes l7 {
        to {background-position: 150% 0%}
    }

    /* Overlay for Messages */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .overlay.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    /* ============================================
    RESPONSIVE DESIGN
    ============================================ */

    /* Tablets (768px and below) */
    @media (max-width: 768px) {
        .success-container {
            min-width: 340px;
            padding: 40px 50px;
        }

        .success-container h3 {
            font-size: 1.2rem;
        }
    }

    /* Mobile Devices (576px and below) */
    @media (max-width: 576px) {
        .success-container {
            min-width: 300px;
            padding: 35px 40px;
        }

        .success-container h3 {
            font-size: 1.1rem;
        }

        .error-message {
            padding: 16px 20px;
        }

        .error-message h3 {
            font-size: 0.95rem;
        }
    }

    /* Small Mobile (400px and below) */
    @media (max-width: 400px) {

        .success-container {
            min-width: 280px;
            padding: 30px 35px;
        }

        .btn-login {
            font-size: 0.95rem;
        }
    }

    /* Extra Small Mobile (360px and below) */
    @media (max-width: 360px) {

        .success-container {
            min-width: 260px;
            padding: 25px 30px;
        }
    }



    /* ============================================
    ACCESSIBILITY ENHANCEMENTS
    ============================================ */

    /* Focus Visible for Keyboard Navigation */
    *:focus-visible {
        outline: 3px solid #667eea;
        outline-offset: 2px;
    }

    /* Reduced Motion for Accessibility */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>