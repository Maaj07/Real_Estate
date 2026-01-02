<?php
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==true)
{
  $loggedin = true;
}
else
{
  $loggedin = false;
}
echo'
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0b1f3a; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/REAL_ESTATE/UserInterface/welcome.php">
      <img src="/REAL_ESTATE/UserInterface/Img/logo.jpg" alt="Logo" width="50" height="50" class="rounded-circle me-2 brand-logo">
      <span class="brand-text">3 Brother Real Estate</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">';
        if(!$loggedin)
        { 
        echo '
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="/REAL_ESTATE/UserInterface/login.php">
            <i class="fa-solid fa-right-to-bracket me-1"></i> 
            <span>Login</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="/REAL_ESTATE/UserInterface/signup.php">
            <i class="fa-solid fa-user-plus me-1"></i> 
            <span>Sign Up</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center admin-link" href="/REAL_ESTATE/UserInterface/Admin/Admin.php">
            <i class="fa-solid fa-user-shield me-1"></i> 
            <span>Admin</span>
          </a>
        </li>';

        }

        if($loggedin)
        {
        echo ' 
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="/REAL_ESTATE/UserInterface/welcome.php">
            <i class="fa-solid fa-house me-1"></i> 
            <span>Home</span>
          </a>
        </li>
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-building me-1"></i> 
            <span>Properties</span>
          </a>
          <ul class="dropdown-menu" style="background-color: #0b1f3a; color: #fff;">
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/All.php">
              <i class="fa-solid fa-list me-2"></i>All Properties
            </a></li>
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Buy.php">
              <i class="fa-solid fa-money-bill-wave me-2"></i>Buy
            </a></li>
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Rent.php">
              <i class="fa-solid fa-key me-2"></i>Rent
            </a></li>
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Flate.php">
              <i class="fa-solid fa-building-columns me-2"></i>Flate
            </a></li>
          </ul>
        </li>
              
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Agents.php">
            <i class="fa-solid fa-user-tie me-1"></i> 
            <span>Agents</span>
          </a>
        </li>
              
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="/REAL_ESTATE/UserInterface/contact.php">
            <i class="fa-solid fa-envelope me-1"></i> 
            <span>Contact Us</span>
          </a>
        </li>
        
        <li class="nav-item dropdown ms-lg-2">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user-circle me-1"></i> 
            <span>'.$_SESSION['username'].'</span>
          </a>
          <ul class="dropdown-menu" style="background-color: #0b1f3a; color: #fff;">
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Partials/profile.php">
              <i class="fa-solid fa-user me-2"></i>Profile
            </a></li>
            <!--<li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Partials/settings.php">
              <i class="fa-solid fa-gear me-2"></i>Settings
            </a></li>-->
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item d-flex align-items-center" href="/REAL_ESTATE/UserInterface/Logout Confirmation Page.php">
              <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
            </a></li>
          </ul>
        </li>';

        }

      echo '</ul>
    </div>
  </div>
</nav>

<style>
  .navbar {
    padding: 0.5rem 0;
    transition: all 0.3s ease;
  }
  
  .navbar-brand {
    font-weight: 700;
    font-size: 1.4rem;
  }
  
  .brand-logo {
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
  }
  
  .navbar-brand:hover .brand-logo {
    transform: rotate(10deg);
    border-color: rgba(255, 255, 255, 0.4);
  }
  
  .brand-text {
    background: linear-gradient(45deg, #fff, #a8d8ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .nav-link {
    font-weight: 500;
    padding: 0.6rem 1rem !important;
    border-radius: 0.5rem;
    margin: 0 0.2rem;
    transition: all 0.3s ease;
    position: relative;
  }
  
  .nav-link:not(.dropdown-toggle):hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
  }
  
  .nav-link::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(45deg, #4e9eff, #00ffcc);
    transition: all 0.3s ease;
    transform: translateX(-50%);
  }
  
  .nav-link:hover::after {
    width: 70%;
  }
  
  .dropdown-menu {
    border: 1px solid #0b1f3a;
    border-radius: 0.5rem;
    box-shadow: 0 5px 15px #0b1f3a;
  }
  
  .dropdown-item {
    padding: 0.6rem 1rem;
    transition: all 0.2s ease;
    border-radius: 0.3rem;
    margin: 0.2rem;
    color: #fff;
  }
  
  .dropdown-item:hover {
    background-color: #0b1f3a;
    transform: translateX(5px);
    color: #fff;
  }
  
  .admin-link {
    background: linear-gradient(45deg, #dc3545, #c82333);
    padding: 0.4rem 1rem !important;
  }
  
  .admin-link:hover {
    background: linear-gradient(45deg, #c82333, #dc3545);
    transform: translateY(-2px) scale(1.05);
  }
  
  .navbar-toggler {
    border: none;
    padding: 0.4rem;
  }
  
  .navbar-toggler:focus {
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
  }
  
  @media (max-width: 991.98px) {
    .navbar-nav {
      padding: 1rem 0;
    }
    
    .nav-link {
      margin: 0.2rem 0;
    }
    
    .dropdown-menu {
      margin-left: 1rem;
      border: none;
      background-color: rgba(11, 31, 58, 0.9);
    }
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>';
?>