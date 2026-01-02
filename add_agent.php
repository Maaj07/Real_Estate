<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: Admin.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: /REAL_ESTATE/UserInterface/Admin/Admin.php");
    exit();
}

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "real_estate");

// Encryption Config
define('ENCRYPTION_KEY', 'mysecretkey12345');
define('ENCRYPTION_METHOD', 'AES-128-CTR');

function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Handle Agent Submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = encrypt_data($_POST['email']);
    $phone = encrypt_data($_POST['phone']);
    $linkedin = encrypt_data($_POST['linkedin']);

    // Image Upload
    $image = $_FILES['image']['name'];
    $temp = $_FILES['image']['tmp_name'];
    $upload_folder = "../../Agents Img/";
    move_uploaded_file($temp, $upload_folder . $image);

    // Insert Query
    $query = "INSERT INTO agents (name, email, phone, linkedin, image) 
              VALUES ('$name', '$email', '$phone', '$linkedin', '$image')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Agent Added Successfully'); window.location.href='agents_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

$agent_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_agents FROM agents");
$agent_count_row = mysqli_fetch_assoc($agent_count_result);
$total_agents = $agent_count_row['total_agents'];

$category_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_categories FROM categories");
$category_count_row = mysqli_fetch_assoc($category_count_result);
$total_categories = $category_count_row['total_categories'];

$user_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$user_count_row = mysqli_fetch_assoc($user_count_result);
$total_users = $user_count_row['total_users'];

$property_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$property_count_row = mysqli_fetch_assoc($property_count_result);
$total_properties = $property_count_row['total_properties'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Agent - Admin Panel</title>
  <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #ffffff;
    }

    .header {
      font-family: poppins;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      background-color: #0b1f3a;
      color: white;
      height: 80px;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .header button {
      background-color: #f0f0f0;
      font-size: 16px;
      font-weight: bold;
      padding: 8px 12px;
      border: 2px solid #0b1f3a;
      border-radius: 5px;
      cursor: pointer;
    }

    .header button:hover {
      background-color: #0b1f3a;
      color: white;
      border: 2px solid #f0f0f0;
    }

    .box1 {
      display: flex;
      flex-wrap: wrap;
      min-height: calc(100vh - 80px);
    }

    .box2 {
      width: 220px;
      background-color: #0b1f3a;
      color: white;
      padding: 20px 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
      flex-shrink: 0;
      overflow-y: auto;
      position: sticky;
      top: 80px;
      height: calc(100vh - 80px);
    }

    .profile img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      margin-bottom: 10px;
      border: 3px solid white;
    }

    .profile p {
      font-size: 18px;
      font-weight: bold;
    }

    .menu {
      list-style: none;
      width: 100%;
      padding-left: 0;
      margin-top: 20px;
    }

    .menu li,
    .dropdown .dropdown-toggle {
      padding: 10px 15px;
      margin-bottom: 10px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      color: white;
      display: block;
    }

    .menu li:hover,
    .dropdown-menu .dropdown-item:hover {
      background-color: whitesmoke;
      color: black;
    }

    .main1 {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
      background-color: #f6f6f6;
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: flex-start;
    }

    .dashboard-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      width: 100%;
    }

    .card {
        flex: 1;
        min-width: 200px;
        height: 130px;
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        position: relative;
        transition: transform 0.3s ease, background-color 0.3s;
        margin-bottom: 20px;
    }

    .card:hover {
        transform: scale(1.05);
    }

    .card:nth-child(1) { background-color: #007bff; }
    .card:nth-child(2) { background-color: #28a745; }
    .card:nth-child(3) { background-color: #17a2b8; }
    .card:nth-child(4) { background-color: #fd7e14; }

    .card-icon {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .card h3 {
        font-size: 16px;
        margin-bottom: 5px;
    }

    .card p {
        font-size: 22px;
        font-weight: bold;
        position: absolute;
        bottom: 15px;
        right: 20px;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    .dropdown-menu {
      background-color: #0b1f3a;
      border: none;
    }

    .dropdown-item {
      color: white;
    }

    #sidebar {
      transition: all 0.3s ease;
    }
    
    /* Form Styles */
    .form-container {
      width: 100%;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      padding: 30px;
      margin-top: 20px;
    }
    
    .form-title {
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #0b1f3a;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }
    
    .form-control {
      border: 1px solid #ced4da;
      border-radius: 5px;
      padding: 12px 15px;
      width: 100%;
      transition: border-color 0.3s;
    }
    
    .form-control:focus {
      border-color: #0b1f3a;
      box-shadow: 0 0 0 0.2rem rgba(11, 31, 58, 0.25);
      outline: none;
    }
    
    .btn-success {
      background-color: #28a745;
      border-color: #28a745;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-success:hover {
      background-color: #218838;
      border-color: #1e7e34;
      transform: translateY(-2px);
    }
    
    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-secondary:hover {
      background-color: #5a6268;
      border-color: #545b62;
      transform: translateY(-2px);
    }
    
    /* Mobile Navigation */
    .mobile-nav-btn {
      display: none;
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1200px) {
      .card {
        min-width: 180px;
      }
    }
    
    @media (max-width: 992px) {
      .dashboard-cards {
        gap: 15px;
      }
      
      .card {
        min-width: calc(50% - 20px);
      }
    }
    
    @media (max-width: 768px) {
      .box1 {
        flex-direction: column;
      }

      .box2 {
        width: 100%;
        position: relative;
        height: auto;
        padding: 15px;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-around;
      }

      .main1 {
        padding: 15px;
        justify-content: center;
      }

      #sidebar {
        position: fixed;
        left: -100%;
        top: 80px;
        width: 250px;
        height: calc(100% - 80px);
        z-index: 999;
        transition: left 0.3s ease;
      }

      #sidebar.active {
        left: 0;
      }
      
      .mobile-nav-btn {
        display: block;
      }
      
      .profile {
        display: flex;
        align-items: center;
        gap: 15px;
        width: 100%;
        margin-bottom: 15px;
      }
      
      .profile img {
        margin-bottom: 0;
      }
      
      .menu {
        width: 100%;
      }
      
      .card {
        min-width: 100%;
      }
    }
    
    @media (max-width: 576px) {
      .header {
        height: auto;
        padding: 15px;
        flex-direction: column;
        gap: 15px;
      }
      
      .header h4 {
        font-size: 18px;
        text-align: center;
      }
      
      .form-container {
        padding: 20px;
      }
      
      .form-title {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="d-flex align-items-center">
      <button class="btn btn-light me-3 d-md-none" type="button" id="toggleSidebar"><i class="fas fa-bars"></i></button>
      <h4>Welcome Admin - <?php echo $_SESSION['username']; ?></h4>
    </div>
    <form method="post"><button type="submit" name="logout">Logout</button></form>
  </div>

  <div class="box1">
    <div class="box2" id="sidebar">
      <div class="profile"><img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin"><p>Hello, Admin</p></div>
      <ul class="menu">
        <li>
            <a href="dashboard.php">
                <i class="fa fa-home"></i> Dashboard
            </a>
        </li>

        <div class="dropdown">
          <a class="dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-building"></i> Properties</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="add_property.php">Add Properties</a></li>
            <li><a class="dropdown-item" href="property_list.php">Properties List</a></li>
          </ul>
        </div>
        <li><a href="user_see.php"><i class="fa fa-users"></i> Users</a></li>
        <li><a href="categories.php"><i class="fa-solid fa-list"></i> Categories</a></li>
        <div class="dropdown">
          <a class="dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-user-tie"></i> Agents</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Add Agent</a></li>
            <li><a class="dropdown-item" href="agents_list.php">Agent List</a></li>
          </ul>
        </div>
        <li><a href="dashboard.php"><i class="fa-solid fa-calendar-check"></i> Viewing Requests</a></li>
      </ul>
    </div>

    <div class="main1">
      <div class="dashboard-cards">
        <div class="card">
          <i class="fa-solid fa-building card-icon"></i>
          <h3>Total Properties</h3>
          <p><?php echo $total_properties; ?></p>
        </div>
        <div class="card">
          <i class="fa-solid fa-user-tie card-icon"></i>
          <h3>Total Agents</h3>
          <p><?php echo $total_agents; ?></p>
        </div>
        <div class="card">
          <i class="fa fa-users card-icon"></i>
          <h3>Registered Users</h3>
          <p><?php echo $total_users; ?></p>
        </div>
        <div class="card">
          <i class="fa-solid fa-house-circle-check card-icon"></i>
          <h3>Categories</h3>
          <p><?php echo $total_categories; ?></p>
        </div>
      </div>
      
      <div class="form-container">
        <h2 class="form-title">Add New Agent</h2>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" name="name" required class="form-control">
          </div>

          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" required class="form-control">
          </div>

          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" required class="form-control">
          </div>

          <div class="form-group">
            <label class="form-label">LinkedIn URL</label>
            <input type="url" name="linkedin" required class="form-control">
          </div>

          <div class="form-group">
            <label class="form-label">Profile Image</label>
            <input type="file" name="image" required class="form-control">
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" name="submit" class="btn btn-success">
              <i class="fas fa-plus me-2"></i>Add Agent
            </button>

            <a href="/REAL_ESTATE/UserInterface/Admin/agents_list.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Toggle sidebar on mobile
    document.getElementById('toggleSidebar').addEventListener('click', function () {
      document.getElementById('sidebar').classList.toggle('active');
      
      // Add overlay when sidebar is active
      if (document.getElementById('sidebar').classList.contains('active')) {
        const overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '80px';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = 'calc(100% - 80px)';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = '998';
        overlay.onclick = function() {
          document.getElementById('sidebar').classList.remove('active');
          this.remove();
        };
        document.body.appendChild(overlay);
      } else {
        const overlay = document.getElementById('sidebar-overlay');
        if (overlay) overlay.remove();
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>