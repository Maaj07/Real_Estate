 <?php
    include 'UserInterface/Partials/_dbconnect.php';
    header("refresh:5; url=/REAL_ESTATE/UserInterface/login.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
    <title>Loading - 3 Brother Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            background-color: #0b1f3a; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,170.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.5;
        }

        .loading-container {
            text-align: center;
            z-index: 1;
            /* background: rgba(255, 255, 255, 0.95); */
            padding: 60px 40px;
            border-radius: 20px;
            /* box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); */
            /* backdrop-filter: blur(10px); */
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loaderr {
            display: inline-flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .loaderr:before,
        .loaderr:after {
            content: "";
            height: 20px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(farthest-side, #667eea 95%, #0000) 50%/8px 8px no-repeat #764ba2;
            animation: l10 1.5s infinite alternate;
        }

        .loaderr:after {
            --s: -1;
        }

        @keyframes l10 {
            0%, 20% {
                transform: scaleX(var(--s, 1)) rotate(0deg);
                clip-path: inset(0);
            }
            60%, 100% {
                transform: scaleX(var(--s, 1)) rotate(30deg);
                clip-path: inset(40% 0 0);
            }
        }

        .loader {
            width: fit-content;
            font-weight: bold;
            font-family: monospace;
            font-size: 30px;
            background: radial-gradient(circle closest-side, #667eea 94%, #0000) right/calc(200% - 1em) 100%;
            animation: l24 1s infinite alternate linear;
            margin: 0 auto 30px;
        }

        .loader::before {
            content: "Loading...";
            line-height: 1em;
            color: #0000;
            background: inherit;
            background-image: radial-gradient(circle closest-side, #764ba2 94%, #000);
            -webkit-background-clip: text;
            background-clip: text;
        }

        @keyframes l24 {
            100% {
                background-position: left;
            }
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .progress-container {
            width: 100%;
            margin-top: 30px;
        }

        .progress {
            height: 6px;
            border-radius: 10px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            animation: progressAnimation 5s linear;
        }

        @keyframes progressAnimation {
            from {
                width: 0%;
            }
            to {
                width: 100%;
            }
        }

        .redirect-text {
            font-size: 0.9rem;
            color: #888;
            margin-top: 15px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .loading-container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 2rem;
            }

            p {
                font-size: 1rem;
            }

            .loader {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .loading-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.5rem;
            }

            p {
                font-size: 0.9rem;
            }

            .loader {
                font-size: 20px;
            }

            .loaderr:before,
            .loaderr:after {
                height: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="loaderr"></div>
        <div class="loader"></div>
        <h1>Welcome to the Real Estate Portal</h1>
        <p>Please log in to continue.</p>
        
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar" role="progressbar"></div>
            </div>
            <p class="redirect-text">Redirecting to login page in 5 seconds...</p>
        </div>
    </div>
</body>
</html>