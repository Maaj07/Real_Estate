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

$conn = mysqli_connect("localhost", "root", "", "real_estate");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle status update
$message = '';
if (isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE viewing_requests SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $request_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Status updated successfully!";
    } else {
        $message = "Error updating status!";
    }
    mysqli_stmt_close($stmt);
}

// Get counts for dashboard
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

// Get viewing requests count
$viewing_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_viewings FROM viewing_requests");
$viewing_count_row = mysqli_fetch_assoc($viewing_count_result);
$total_viewings = $viewing_count_row['total_viewings'];

// Get pending requests count
$pending_count_result = mysqli_query($conn, "SELECT COUNT(*) AS pending_viewings FROM viewing_requests WHERE status = 'Pending'");
$pending_count_row = mysqli_fetch_assoc($pending_count_result);
$pending_viewings = $pending_count_row['pending_viewings'];

// Fetch all viewing requests with property and agent details
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_clause = "";
if ($filter == 'pending') {
    $where_clause = "WHERE vr.status = 'Pending'";
} elseif ($filter == 'completed') {
    $where_clause = "WHERE vr.status = 'Completed'";
} elseif ($filter == 'cancelled') {
    $where_clause = "WHERE vr.status = 'Cancelled'";
}

$viewing_requests_query = "
    SELECT vr.*, p.title as property_title, p.address as property_address, p.city as property_city,
           p.price as property_price, p.image as property_image, a.name as agent_name
    FROM viewing_requests vr
    JOIN properties p ON vr.property_id = p.id
    JOIN agents a ON vr.agent_id = a.id
    $where_clause
    ORDER BY vr.created_at DESC
";

$viewing_requests_result = mysqli_query($conn, $viewing_requests_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>viewing Report - Admin Dashboard</title>
  <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f6f6f6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
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
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .header button {
      background-color: #f0f0f0;
      font-size: 16px;
      font-weight: bold;
      padding: 8px 12px;
      border: 2px solid #0b1f3a;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .header button:hover {
      background-color: #0b1f3a;
      color: white;
      border: 2px solid #f0f0f0;
    }

    .box1 {
      display: flex;
      flex-wrap: wrap;
      flex: 1;
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
      transition: all 0.3s ease;
    }

    .profile {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      margin-bottom: 10px;
      border: 3px solid white;
      object-fit: cover;
    }

    .profile p {
      font-size: 18px;
      font-weight: bold;
    }

    .menu {
      list-style: none;
      width: 100%;
      padding-left: 0;
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
      transition: all 0.3s ease;
    }

    .menu li:hover,
    .dropdown .dropdown-toggle:hover,
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
      align-content: flex-start;
    }
    a{
      text-decoration: none;
      color: white;
    }
    a:hover{
      color: black;
    }
    
    /* Enhanced Dashboard Cards */
    .dashboard-card-container {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .dashboard-card-row {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    
    .dashboard-card {
      flex: 1;
      min-width: 280px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(11, 31, 58, 0.12);
      padding: 30px 25px;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border-top: 4px solid #0b1f3a;
    }
    
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(11, 31, 58, 0.2);
    }
    
    .dashboard-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #0b1f3a, #1a4a7a);
    }
    
    .card-icon {
      font-size: 42px;
      margin-bottom: 15px;
      color: #0b1f3a;
      position: relative;
      z-index: 1;
    }
    
    .dashboard-card h3 {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #0b1f3a;
      text-align: center;
    }
    
    .card-count {
      font-size: 36px;
      font-weight: 700;
      color: #0b1f3a;
      margin: 10px 0;
    }
    
    .card-description {
      font-size: 14px;
      color: #666;
      text-align: center;
      margin-bottom: 15px;
    }
    
    .card-link {
      margin-top: auto;
      width: 100%;
    }
    
    .card-link a {
      display: block;
      text-align: center;
      background-color: #0b1f3a;
      color: white;
      padding: 10px 15px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    .card-link a:hover {
      background-color: #1a4a7a;
      color: white;
      transform: translateY(-2px);
    }
    
    /* Color variations for cards */
    .card-users {
      border-top-color: #3498db;
    }
    
    .card-users .card-icon {
      color: #3498db;
    }
    
    .card-properties {
      border-top-color: #2ecc71;
    }
    
    .card-properties .card-icon {
      color: #2ecc71;
    }
    
    .card-agents {
      border-top-color: #e74c3c;
    }
    
    .card-agents .card-icon {
      color: #e74c3c;
    }
    
    .card-categories {
      border-top-color: #f39c12;
    }
    
    .card-categories .card-icon {
      color: #f39c12;
    }
    
    .card-viewings {
      border-top-color: #9b59b6;
    }
    
    .card-viewings .card-icon {
      color: #9b59b6;
    }
    
    .card-pending {
      border-top-color: #e67e22;
    }
    
    .card-pending .card-icon {
      color: #e67e22;
    }

    @media (max-width: 900px) {
      .main1 {
        padding: 15px;
        gap: 15px;
      }
      .dashboard-card-row {
        gap: 15px;
      }
      .dashboard-card {
        min-width: calc(50% - 15px);
        padding: 20px 15px;
      }
      .box2 {
        width: 100px;
        padding: 10px 2px;
      }
    }

    @media (max-width: 600px) {
      .dashboard-card-row {
        flex-direction: column;
        gap: 15px;
      }
      .dashboard-card {
        min-width: 100%;
        width: 100%;
      }
      .box2 {
        position: fixed;
        left: -220px;
        top: 80px;
        height: calc(100vh - 80px);
        z-index: 999;
        transition: left 0.3s;
      }
      .box2.active {
        left: 0;
      }
    }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <button class="btn btn-light me-3 d-md-none mobile-nav-btn" type="button" id="toggleSidebar">
      <i class="fas fa-bars"></i>
    </button>
    <h4>Admin Dashboard - <?php echo $_SESSION['username']; ?></h4>
  </div>
  <form method="post">
    <button type="submit" name="logout">Logout</button>
  </form>
</div>

<div class="box1">
  <div class="box2" id="sidebar">
    <div class="profile">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin">
      <p>Hello, Admin</p>
    </div>

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
          <li><a class="dropdown-item" href="add_agent.php">Add Agent</a></li>
          <li><a class="dropdown-item" href="agents_list.php">Agent List</a></li>
        </ul>
      </div>

      <li><a href="dashboard.php"><i class="fa-solid fa-calendar-check"></i> Viewing Requests</a></li>
    </ul>
  </div>

  <div class="main1">
    <!-- Dashboard Cards Section -->
    <div class="dashboard-card-container">
      <!-- First Row - Two Cards -->
      <div class="dashboard-card-row">
        <div class="dashboard-card card-users">
          <i class="fa fa-users card-icon"></i>
          <h3>Total Users</h3>
          <div class="card-count"><?php echo $total_users; ?></div>
          <div class="card-description">Registered users in the system</div>
          <div class="card-link">
            <a href="UsersReport.php">Check Users Report</a>
          </div>
        </div>

        <div class="dashboard-card card-properties">
          <i class="fa-solid fa-building card-icon"></i>
          <h3>Total Properties</h3>
          <div class="card-count"><?php echo $total_properties; ?></div>
          <div class="card-description">Properties listed for sale/rent</div>
          <div class="card-link">
            <a href="PropertiesReport.php">Check Properties Report</a>
          </div>
        </div>
      </div>

      <!-- Second Row - Two Cards -->
      <div class="dashboard-card-row">
        <div class="dashboard-card card-agents">
          <i class="fa-solid fa-user-tie card-icon"></i>
          <h3>Total Agents</h3>
          <div class="card-count"><?php echo $total_agents; ?></div>
          <div class="card-description">Registered real estate agents</div>
          <div class="card-link">
            <a href="AgentsReport.php">Check Agents Report</a>
          </div>
        </div>

        <div class="dashboard-card card-categories">
          <i class="fa-solid fa-list card-icon"></i>
          <h3>Categories</h3>
          <div class="card-count"><?php echo $total_categories; ?></div>
          <div class="card-description">Property categories available</div>
          <div class="card-link">
            <a href="categoriesReport.php">Check Categories Report</a>
          </div>
        </div>
      </div>
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
  
  // Close sidebar when clicking outside
  document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    
    if (sidebar.classList.contains('active') && 
        !sidebar.contains(event.target) && 
        !toggleBtn.contains(event.target) &&
        event.target.id !== 'sidebar-overlay') {
      sidebar.classList.remove('active');
      const overlay = document.getElementById('sidebar-overlay');
      if (overlay) overlay.remove();
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>