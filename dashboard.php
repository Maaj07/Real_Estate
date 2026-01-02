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
  <title>Admin Dashboard - Viewing Requests</title>
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

    .dashboard-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      width: 100%;
      margin-bottom: 30px;
    }

    .card {
      flex: 1;
      min-width: 180px;
      height: 130px;
      color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      position: relative;
      transition: transform 0.3s ease, background-color 0.3s;
      margin-bottom: 0;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .card:nth-child(1) { background-color: #007bff; }
    .card:nth-child(2) { background-color: #28a745; }
    .card:nth-child(3) { background-color: #17a2b8; }
    .card:nth-child(4) { background-color: #fd7e14; }
    .card:nth-child(5) { background-color: #6f42c1; }
    .card:nth-child(6) { background-color: #e83e8c; }

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

    /* Viewing Requests Table Styles */
    .viewing-requests-section {
      width: 100%;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 30px;
      margin-top: 20px;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .section-title {
      color: #0b1f3a;
      font-weight: 700;
      font-size: 1.8rem;
      margin: 0;
    }

    .filter-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 8px 16px;
      border: 2px solid #dc3545;
      background: transparent;
      color: #dc3545;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .filter-btn:hover, .filter-btn.active {
      background: #dc3545;
      color: white;
      text-decoration: none;
    }

    .table-responsive {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table {
      margin: 0;
      background: white;
    }

    .table thead {
      background: linear-gradient(135deg, #0b1f3a 0%, #1e3a5f 100%);
    }

    .table thead th {
      color: white;
      font-weight: 600;
      padding: 15px 10px;
      border: none;
      font-size: 0.95rem;
      text-align: center;
      vertical-align: middle;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid #eee;
    }

    .table tbody tr:hover {
      background-color: #f8f9fa;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .table tbody td {
      padding: 15px 10px;
      vertical-align: middle;
      border: none;
      font-size: 0.9rem;
      text-align: center;
    }

    .property-info {
      display: flex;
      align-items: center;
      gap: 15px;
      text-align: left;
    }

    .property-img {
      width: 60px;
      height: 60px;
      border-radius: 8px;
      object-fit: cover;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .property-details h6 {
      margin: 0 0 5px 0;
      color: #0b1f3a;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .property-details small {
      color: #6c757d;
      font-size: 0.8rem;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: none;
    }

    .status-pending {
      background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
      color: #856404;
    }

    .status-completed {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .status-cancelled {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }

    .action-buttons {
      display: flex;
      gap: 5px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-action {
      padding: 6px 12px;
      font-size: 0.8rem;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 80px;
    }

    .btn-complete {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .btn-cancel {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }

    .btn-pending {
      background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
      color: #856404;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .user-info {
      text-align: left;
    }

    .user-info strong {
      color: #0b1f3a;
      font-size: 0.95rem;
    }

    .user-info small {
      display: block;
      color: #6c757d;
      margin-top: 2px;
    }

    .datetime-info {
      font-weight: 600;
      color: #0b1f3a;
    }

    .datetime-info small {
      display: block;
      color: #6c757d;
      font-weight: normal;
      margin-top: 2px;
    }

    .no-requests {
      text-align: center;
      padding: 50px;
      color: #6c757d;
    }

    .no-requests i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 20px;
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

    .dropdown-item:hover {
      background-color: whitesmoke;
      color: black;
    }

    .mobile-nav-btn {
      display: none;
    }

    /* Mobile table styles */
    .mobile-table-row {
      display: none;
      background: white;
      border-radius: 10px;
      margin-bottom: 15px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .mobile-table-row .mobile-row-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .mobile-table-row .mobile-row-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .mobile-table-row .mobile-row-item {
      margin-bottom: 8px;
    }

    .mobile-table-row .mobile-row-label {
      font-weight: 600;
      color: #0b1f3a;
      font-size: 0.85rem;
    }

    .mobile-table-row .mobile-row-value {
      color: #6c757d;
      font-size: 0.85rem;
    }

    .mobile-table-row .mobile-row-full {
      grid-column: 1 / -1;
    }

    /* Card animations */
    .card {
      animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Search and filter expansion */
    .search-filter-container {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .search-box {
      flex: 1;
      min-width: 250px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 10px 15px 10px 40px;
      border: 1px solid #ddd;
      border-radius: 25px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      outline: none;
      border-color: #0b1f3a;
      box-shadow: 0 0 0 2px rgba(11, 31, 58, 0.1);
    }

    .search-box i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .export-btn {
      background: #28a745;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .export-btn:hover {
      background: #218838;
      transform: translateY(-2px);
    }

    /* Expanded details modal */
    .details-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1100;
      justify-content: center;
      align-items: center;
    }

    .details-modal-content {
      background: white;
      border-radius: 15px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 30px;
      position: relative;
      animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
      from {
        opacity: 0;
        transform: scale(0.9);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .modal-close {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6c757d;
    }

    .modal-header {
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .modal-body {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .modal-section {
      margin-bottom: 15px;
    }

    .modal-section h4 {
      font-size: 1rem;
      color: #0b1f3a;
      margin-bottom: 10px;
    }

    .modal-section p {
      margin: 5px 0;
      font-size: 0.9rem;
    }

    .modal-full-width {
      grid-column: 1 / -1;
    }

    @media (max-width: 1200px) {
      .card {
        min-width: 150px;
      }
    }
    
    @media (max-width: 992px) {
      .dashboard-cards {
        gap: 15px;
      }
      
      .card {
        min-width: calc(50% - 15px);
      }

      .property-info {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }

      .action-buttons {
        flex-direction: column;
        gap: 5px;
      }

      .table thead {
        display: none;
      }

      .table tbody tr {
        display: none;
      }

      .mobile-table-row {
        display: block;
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
        position: static;
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
        text-align: left;
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

      .section-header {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-buttons {
        justify-content: center;
      }

      .table-responsive {
        font-size: 0.8rem;
      }

      .property-img {
        width: 40px;
        height: 40px;
      }

      .modal-body {
        grid-template-columns: 1fr;
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
      
      .card {
        height: auto;
        padding: 15px;
      }
      
      .card p {
        position: static;
        margin-top: 10px;
      }

      .viewing-requests-section {
        padding: 20px;
      }

      .search-filter-container {
        flex-direction: column;
      }

      .search-box {
        min-width: 100%;
      }
    }

    .alert {
      border-radius: 10px;
      border: none;
      padding: 15px 20px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert-success {
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      color: #155724;
      border-left: 5px solid #28a745;
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
      <li><a href="Report.php"><i class="fa-solid fa-file-alt"></i> Viewing Report</a></li>
    </ul>
  </div>
  <div class="main1">
    <?php if (!empty($message)): ?>
      <div class="alert alert-success w-100">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
      </div>
    <?php endif; ?>

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
      <div class="card">
        <i class="fa-solid fa-calendar-check card-icon"></i>
        <h3>Total Viewings</h3>
        <p><?php echo $total_viewings; ?></p>
      </div>
      <div class="card">
        <i class="fa-solid fa-clock card-icon"></i>
        <h3>Pending Viewings</h3>
        <p><?php echo $pending_viewings; ?></p>
      </div>
    </div>

    <div class="viewing-requests-section">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-calendar-check me-2"></i>Viewing Requests Management
        </h2>
        <div class="filter-buttons">
          <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-list me-1"></i>All
          </a>
          <a href="?filter=pending" class="filter-btn <?php echo $filter == 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock me-1"></i>Pending
          </a>
          <a href="?filter=completed" class="filter-btn <?php echo $filter == 'completed' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle me-1"></i>Completed
          </a>
          <a href="?filter=cancelled" class="filter-btn <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle me-1"></i>Cancelled
          </a>
        </div>
      </div>

      <div class="search-filter-container">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Search requests...">
        </div>
        <!-- <button class="export-btn" id="exportBtn">
          <i class="fas fa-download"></i> Export Data
        </button> -->
      </div>

      <?php if (mysqli_num_rows($viewing_requests_result) > 0): ?>
        <div class="table-responsive d-none d-lg-block">
          <table class="table table-hover" id="requestsTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Property Details</th>
                <th>User Information</th>
                <th>Agent</th>
                <th>Requested Date/Time</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              mysqli_data_seek($viewing_requests_result, 0); // Reset result pointer
              while ($request = mysqli_fetch_assoc($viewing_requests_result)): 
              ?>
                <tr data-id="<?php echo $request['id']; ?>">
                  <td>
                    <strong>#<?php echo $request['id']; ?></strong>
                  </td>
                  <td>
                    <div class="property-info">
                      <img src="/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $request['property_image']; ?>" 
                           alt="<?php echo $request['property_title']; ?>" 
                           class="property-img">
                      <div class="property-details">
                        <h6><?php echo htmlspecialchars($request['property_title']); ?></h6>
                        <small><?php echo htmlspecialchars($request['property_address'] . ', ' . $request['property_city']); ?></small>
                        <small class="d-block text-success fw-bold">$<?php echo number_format($request['property_price']); ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="user-info">
                      <strong><?php echo htmlspecialchars($request['user_name']); ?></strong>
                      <small><?php echo htmlspecialchars($request['user_email']); ?></small>
                      <?php if (!empty($request['user_phone'])): ?>
                        <small><?php echo htmlspecialchars($request['user_phone']); ?></small>
                      <?php endif; ?>
                      <?php if (!empty($request['message'])): ?>
                        <small class="text-muted">"<?php echo htmlspecialchars(substr($request['message'], 0, 50)) . (strlen($request['message']) > 50 ? '...' : ''); ?>"</small>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars($request['agent_name']); ?></strong>
                  </td>
                  <td>
                    <div class="datetime-info">
                      <?php echo date('M j, Y', strtotime($request['requested_datetime'])); ?>
                      <small><?php echo date('g:i A', strtotime($request['requested_datetime'])); ?></small>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                      <?php echo $request['status']; ?>
                    </span>
                  </td>
                  <td>
                    <small><?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></small>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <?php if ($request['status'] == 'Pending'): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                          <input type="hidden" name="new_status" value="Completed">
                          <button type="submit" name="update_status" class="btn-action btn-complete" 
                                  onclick="return confirm('Mark this viewing as completed?')">
                            <i class="fas fa-check me-1"></i>Complete
                          </button>
                        </form>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                          <input type="hidden" name="new_status" value="Cancelled">
                          <button type="submit" name="update_status" class="btn-action btn-cancel" 
                                  onclick="return confirm('Cancel this viewing request?')">
                            <i class="fas fa-times me-1"></i>Cancel
                          </button>
                        </form>
                      <?php elseif ($request['status'] == 'Completed'): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                          <input type="hidden" name="new_status" value="Pending">
                          <button type="submit" name="update_status" class="btn-action btn-pending" 
                                  onclick="return confirm('Mark this viewing as pending?')">
                            <i class="fas fa-undo me-1"></i>Pending
                          </button>
                        </form>
                      <?php elseif ($request['status'] == 'Cancelled'): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                          <input type="hidden" name="new_status" value="Pending">
                          <button type="submit" name="update_status" class="btn-action btn-pending" 
                                  onclick="return confirm('Restore this viewing request?')">
                            <i class="fas fa-undo me-1"></i>Restore
                          </button>
                        </form>
                      <?php endif; ?>
                      <button class="btn-action btn-info mt-1" onclick="showDetails(<?php echo $request['id']; ?>)">
                        <i class="fas fa-eye me-1"></i>Details
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Mobile view -->
        <div class="d-lg-none" id="mobileRequestsView">
          <?php 
          mysqli_data_seek($viewing_requests_result, 0); // Reset result pointer
          while ($request = mysqli_fetch_assoc($viewing_requests_result)): 
          ?>
            <div class="mobile-table-row" data-id="<?php echo $request['id']; ?>">
              <div class="mobile-row-header">
                <div>
                  <strong>#<?php echo $request['id']; ?></strong>
                  <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                    <?php echo $request['status']; ?>
                  </span>
                </div>
                <button class="btn-action btn-info" onclick="showDetails(<?php echo $request['id']; ?>)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="mobile-row-content">
                <div class="mobile-row-item mobile-row-full">
                  <div class="mobile-row-label">Property</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($request['property_title']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">User</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($request['user_name']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">Agent</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($request['agent_name']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">Requested</div>
                  <div class="mobile-row-value"><?php echo date('M j, g:i A', strtotime($request['requested_datetime'])); ?></div>
                </div>
                <div class="mobile-row-item mobile-row-full">
                  <div class="mobile-row-label">Actions</div>
                  <div class="action-buttons">
                    <?php if ($request['status'] == 'Pending'): ?>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <input type="hidden" name="new_status" value="Completed">
                        <button type="submit" name="update_status" class="btn-action btn-complete" 
                                onclick="return confirm('Mark this viewing as completed?')">
                          <i class="fas fa-check me-1"></i>Complete
                        </button>
                      </form>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <input type="hidden" name="new_status" value="Cancelled">
                        <button type="submit" name="update_status" class="btn-action btn-cancel" 
                                onclick="return confirm('Cancel this viewing request?')">
                          <i class="fas fa-times me-1"></i>Cancel
                        </button>
                      </form>
                    <?php elseif ($request['status'] == 'Completed'): ?>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <input type="hidden" name="new_status" value="Pending">
                        <button type="submit" name="update_status" class="btn-action btn-pending" 
                                onclick="return confirm('Mark this viewing as pending?')">
                          <i class="fas fa-undo me-1"></i>Pending
                        </button>
                      </form>
                    <?php elseif ($request['status'] == 'Cancelled'): ?>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <input type="hidden" name="new_status" value="Pending">
                        <button type="submit" name="update_status" class="btn-action btn-pending" 
                                onclick="return confirm('Restore this viewing request?')">
                          <i class="fas fa-undo me-1"></i>Restore
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="no-requests">
          <i class="fas fa-calendar-times"></i>
          <h4>No Viewing Requests Found</h4>
          <p>
            <?php 
            if ($filter == 'pending') echo "No pending viewing requests at the moment.";
            elseif ($filter == 'completed') echo "No completed viewing requests found.";
            elseif ($filter == 'cancelled') echo "No cancelled viewing requests found.";
            else echo "No viewing requests have been submitted yet.";
            ?>
          </p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Details Modal -->
    <div class="details-modal" id="detailsModal">
      <div class="details-modal-content">
        <button class="modal-close" onclick="closeDetails()">&times;</button>
        <div class="modal-header">
          <h3>Viewing Request Details</h3>
        </div>
        <div class="modal-body" id="modalBody">
          <!-- Details will be loaded here via JavaScript -->
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div style="width: 100%; margin-top: 30px; display: flex; flex-wrap: wrap; gap: 20px;">
      <div style="flex: 1; min-width: 300px; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h4><i class="fas fa-chart-line me-2"></i>Recent Activity</h4>
        <div style="margin-top: 15px;">
          <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
            <div style="background: #e6f7ff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
              <i class="fas fa-calendar-check text-primary"></i>
            </div>
            <div>
              <div style="font-weight: 500;">New viewing request</div>
              <div style="font-size: 13px; color: #666;">2 hours ago</div>
            </div>
          </div>
          <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
            <div style="background: #e6f7ff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
              <i class="fas fa-home text-success"></i>
            </div>
            <div>
              <div style="font-weight: 500;">Property viewing completed</div>
              <div style="font-size: 13px; color: #666;">5 hours ago</div>
            </div>
          </div>
          <div style="display: flex; align-items: center; padding: 10px 0;">
            <div style="background: #e6f7ff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
              <i class="fas fa-user text-info"></i>
            </div>
            <div>
              <div style="font-weight: 500;">New user registered</div>
              <div style="font-size: 13px; color: #666;">Yesterday</div>
            </div>
          </div>
        </div>
      </div>
      
      <div style="flex: 1; min-width: 300px; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h4><i class="fas fa-calendar me-2"></i>Upcoming Viewings</h4>
        <div style="margin-top: 15px;">
          <?php
          $upcoming_query = "
            SELECT vr.*, p.title as property_title, p.address as property_address
            FROM viewing_requests vr
            JOIN properties p ON vr.property_id = p.id
            WHERE vr.status = 'Pending' AND vr.requested_datetime > NOW()
            ORDER BY vr.requested_datetime ASC
            LIMIT 3
          ";
          $upcoming_result = mysqli_query($conn, $upcoming_query);
          
          if (mysqli_num_rows($upcoming_result) > 0):
            while ($upcoming = mysqli_fetch_assoc($upcoming_result)):
          ?>
            <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
              <div style="background: #dc3545; color: white; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                <span style="font-weight: bold; font-size: 12px;"><?php echo date('j', strtotime($upcoming['requested_datetime'])); ?></span>
              </div>
              <div>
                <div style="font-weight: 500;"><?php echo htmlspecialchars(substr($upcoming['property_title'], 0, 30)); ?></div>
                <div style="font-size: 13px; color: #666;"><?php echo date('M j, g:i A', strtotime($upcoming['requested_datetime'])); ?></div>
                <div style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($upcoming['user_name']); ?></div>
              </div>
            </div>
          <?php 
            endwhile;
          else:
          ?>
            <div style="text-align: center; padding: 20px; color: #6c757d;">
              <i class="fas fa-calendar-times" style="font-size: 2rem; color: #dee2e6; margin-bottom: 10px;"></i>
              <p style="margin: 0;">No upcoming viewings scheduled</p>
            </div>
          <?php endif; ?>
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

  // Search functionality
  document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#requestsTable tbody tr, .mobile-table-row');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  // Export functionality
  document.getElementById('exportBtn').addEventListener('click', function() {
    // Simple CSV export simulation
    alert('Export functionality would generate a CSV file with all viewing requests data.');
    // In a real implementation, you would make an AJAX request to generate and download a file
  });

  // Show details modal
  function showDetails(requestId) {
    // In a real implementation, you would fetch detailed data via AJAX
    // For this example, we'll use the data already in the table
    
    const row = document.querySelector(`tr[data-id="${requestId}"]`) || 
                document.querySelector(`.mobile-table-row[data-id="${requestId}"]`);
    
    if (row) {
      // Extract data from the row (simplified example)
      const propertyTitle = row.querySelector('.property-details h6')?.textContent || 'N/A';
      const userInfo = row.querySelector('.user-info strong')?.textContent || 'N/A';
      const agentName = row.querySelector('td:nth-child(4) strong')?.textContent || 
                       row.querySelector('.mobile-row-item:nth-child(3) .mobile-row-value')?.textContent || 'N/A';
      const requestedDate = row.querySelector('.datetime-info')?.textContent || 'N/A';
      const status = row.querySelector('.status-badge')?.textContent || 'N/A';
      
      // Populate modal with data
      document.getElementById('modalBody').innerHTML = `
        <div class="modal-section">
          <h4>Property Information</h4>
          <p><strong>Title:</strong> ${propertyTitle}</p>
        </div>
        <div class="modal-section">
          <h4>User Information</h4>
          <p><strong>Name:</strong> ${userInfo}</p>
        </div>
        <div class="modal-section">
          <h4>Agent Information</h4>
          <p><strong>Agent:</strong> ${agentName}</p>
        </div>
        <div class="modal-section">
          <h4>Request Details</h4>
          <p><strong>Requested Date:</strong> ${requestedDate}</p>
          <p><strong>Status:</strong> ${status}</p>
        </div>
        <div class="modal-section modal-full-width">
          <h4>Additional Notes</h4>
          <p>No additional notes available in this demo. In a real implementation, this would show the full message from the user.</p>
        </div>
      `;
      
      // Show modal
      document.getElementById('detailsModal').style.display = 'flex';
    }
  }

  // Close details modal
  function closeDetails() {
    document.getElementById('detailsModal').style.display = 'none';
  }

  // Close modal when clicking outside
  document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeDetails();
    }
  });

  // Auto-refresh every 30 seconds for new requests
  setInterval(function() {
    // Only refresh if we're on the "all" or "pending" filter to see new requests
    if (window.location.search.includes('filter=all') || window.location.search.includes('filter=pending') || window.location.search === '') {
      // You can add AJAX refresh here if needed
    }
  }, 30000);

  // Add notification sound for status updates (optional)
  function playNotificationSound() {
    // You can add audio notification here
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>