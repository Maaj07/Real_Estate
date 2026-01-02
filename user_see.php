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

// Search and filter functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_city = isset($_GET['city']) ? mysqli_real_escape_string($conn, $_GET['city']) : '';

// Build WHERE clause for search and filter
$where_clause = "";
if (!empty($search)) {
    $where_clause .= " WHERE (username LIKE '%$search%' OR email LIKE '%$search%' OR number LIKE '%$search%')";
}

if (!empty($filter_city)) {
    if (empty($where_clause)) {
        $where_clause .= " WHERE city = '$filter_city'";
    } else {
        $where_clause .= " AND city = '$filter_city'";
    }
}

// Pagination variables
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of users with search/filter
$total_users_query = "SELECT COUNT(*) AS total FROM users" . $where_clause;
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users_row = mysqli_fetch_assoc($total_users_result);
$total_users = $total_users_row['total'];

// Calculate total pages
$total_pages = ceil($total_users / $records_per_page);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Get users for current page with search/filter
$query = "SELECT sno, username, email, number, city, dt FROM users" . $where_clause . " ORDER BY sno ASC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$num_rows = mysqli_num_rows($result);

// Get unique cities for filter dropdown
$cities_query = "SELECT DISTINCT city FROM users WHERE city IS NOT NULL AND city != '' ORDER BY city ASC";
$cities_result = mysqli_query($conn, $cities_query);
$cities = [];
while ($city_row = mysqli_fetch_assoc($cities_result)) {
    $cities[] = $city_row['city'];
}

// Get counts for dashboard cards
$agent_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_agents FROM agents");
$agent_count_row = mysqli_fetch_assoc($agent_count_result);
$total_agents = $agent_count_row['total_agents'];

$category_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_categories FROM categories");
$category_count_row = mysqli_fetch_assoc($category_count_result);
$total_categories = $category_count_row['total_categories'];

$user_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$user_count_row = mysqli_fetch_assoc($user_count_result);
$total_users_all = $user_count_row['total_users'];

$property_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$property_count_row = mysqli_fetch_assoc($property_count_result);
$total_properties = $property_count_row['total_properties'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Users</title>
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
      background-color: #ffffff;
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
      animation: fadeInUp 0.5s ease-out;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
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
      padding: 8px 15px;
    }

    .back-btn {
      margin-bottom: 20px;
      background-color: #0b1f3a;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.3s ease;
    }

    .back-btn:hover {
      background-color: #1a3a6e;
      color: white;
      transform: translateX(-5px);
    }

    /* Search and Filter Section */
    .search-filter-container {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

    .filter-select {
      min-width: 150px;
    }

    .filter-select select {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 25px;
      font-size: 0.9rem;
      background: white;
      cursor: pointer;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }

    .btn-reset, .btn-export {
      padding: 10px 20px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-reset {
      background: #6c757d;
      color: white;
    }

    .btn-export {
      background: #28a745;
      color: white;
    }

    .btn-reset:hover, .btn-export:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Table Styles */
    .table-container {
      width: 100%;
      overflow-x: auto;
      margin-top: 20px;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      background: white;
    }

    .table-title {
      color: #0b1f3a;
      margin-bottom: 20px;
      font-weight: 600;
      text-align: center;
      font-size: 1.8rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      font-family: 'Segoe UI', sans-serif;
      min-width: 800px;
    }

    thead {
      background: linear-gradient(135deg, #0b1f3a 0%, #1e3a5f 100%);
      color: white;
      position: sticky;
      top: 0;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      font-weight: 600;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
    }

    td {
      font-size: 14px;
      color: #333;
      vertical-align: middle;
    }

    tr {
      transition: all 0.3s ease;
    }

    tr:hover {
      background-color: #f8f9fa;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Mobile table styles */
    .mobile-table-row {
      display: none;
      background: white;
      border-radius: 10px;
      margin-bottom: 15px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .mobile-table-row:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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

    /* User detail modal */
    .user-modal {
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

    .user-modal-content {
      background: white;
      border-radius: 15px;
      width: 90%;
      max-width: 500px;
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
      grid-template-columns: 1fr;
      gap: 15px;
    }

    .modal-section {
      margin-bottom: 10px;
    }

    .modal-section h4 {
      font-size: 1rem;
      color: #0b1f3a;
      margin-bottom: 5px;
    }

    .modal-section p {
      margin: 5px 0;
      font-size: 0.9rem;
      color: #6c757d;
    }

    /* Responsive Adjustments */
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

      .search-filter-container {
        flex-direction: column;
      }

      .search-box, .filter-select {
        min-width: 100%;
      }

      .action-buttons {
        width: 100%;
        justify-content: center;
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

      .table-title {
        font-size: 1.5rem;
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

      .search-filter-container {
        padding: 15px;
      }

      .mobile-table-row .mobile-row-content {
        grid-template-columns: 1fr;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn-reset, .btn-export {
        width: 100%;
        justify-content: center;
      }
    }

    .overlay {
      display: none;
      position: fixed;
      top: 80px;
      left: 0;
      width: 100%;
      height: calc(100% - 80px);
      background-color: rgba(0,0,0,0.5);
      z-index: 998;
    }
    
    .overlay.active {
      display: block;
    }
    
    .pagination .page-item .page-link {
      color: #0b1f3a;
    }
    
    .pagination .page-item.active .page-link {
      background-color: #0b1f3a;
      border-color: #0b1f3a;
      color: white;
    }

    .no-results {
      text-align: center;
      padding: 40px;
      color: #6c757d;
    }

    .no-results i {
      font-size: 3rem;
      color: #dee2e6;
      margin-bottom: 15px;
    }

    .view-details-btn {
      background: #17a2b8;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
      transition: all 0.3s ease;
    }

    .view-details-btn:hover {
      background: #138496;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <button class="btn btn-light me-3 d-md-none mobile-nav-btn" type="button" id="toggleSidebar">
      <i class="fas fa-bars"></i>
    </button>
    <h4>Welcome Admin - <?php echo $_SESSION['username']; ?></h4>
  </div>
  <form method="post">
    <button type="submit" name="logout">Logout</button>
  </form>
</div>

<div class="overlay" id="overlay"></div>

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
        <p><?php echo $total_users_all; ?></p>
      </div>
      <div class="card">
        <i class="fa-solid fa-house-circle-check card-icon"></i>
        <h3>Categories</h3>
        <p><?php echo $total_categories; ?></p>
      </div>
    </div>

    <a href="dashboard.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <h2 class="table-title">Registered Users (<?php echo $total_users; ?> found)</h2>
    
    <!-- Search and Filter Section -->
    <div class="search-filter-container">
      <form method="GET" class="w-100 d-flex flex-wrap gap-3">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-select">
          <select name="city">
            <option value="">All Cities</option>
            <?php foreach($cities as $city): ?>
              <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $filter_city == $city ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($city); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="action-buttons">
          <button type="submit" class="btn-reset">
            <i class="fas fa-filter"></i> Apply Filters
          </button>
          <a href="user_see.php" class="btn-reset">
            <i class="fas fa-times"></i> Reset
          </a>
          <!-- <button type="button" class="btn-export" id="exportBtn">
            <i class="fas fa-download"></i> Export
          </button> -->
        </div>
      </form>
    </div>
    
    <div class="table-container">
      <!-- Desktop Table View -->
      <table class="table d-none d-lg-table">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>City</th>
            <th>Joined Date</th>
            <th>Actions</th>
          </tr>

        <tbody>
          <?php if ($num_rows > 0): ?>
            <?php 
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)): 
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['sno']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['number']); ?></td>
                <td><?php echo htmlspecialchars($row['city']); ?></td>
                <td><?php echo htmlspecialchars($row['dt']); ?></td>
                <td>
                  <button class="view-details-btn" onclick="showUserDetails(<?php echo $row['sno']; ?>, '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['number']); ?>', '<?php echo htmlspecialchars($row['city']); ?>', '<?php echo htmlspecialchars($row['dt']); ?>')">
                    <i class="fas fa-eye"></i> Details
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4">No users found matching your criteria</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      
      <!-- Mobile Table View -->
      <div class="d-lg-none" id="mobileUsersView">
        <?php if ($num_rows > 0): ?>
          <?php 
          mysqli_data_seek($result, 0);
          while($row = mysqli_fetch_assoc($result)): 
          ?>
            <div class="mobile-table-row">
              <div class="mobile-row-header">
                <div>
                  <strong>#<?php echo htmlspecialchars($row['sno']); ?></strong>
                  <span style="color: #6c757d; font-size: 0.8rem;"><?php echo htmlspecialchars($row['username']); ?></span>
                </div>
                <button class="view-details-btn" onclick="showUserDetails(<?php echo $row['sno']; ?>, '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['number']); ?>', '<?php echo htmlspecialchars($row['city']); ?>', '<?php echo htmlspecialchars($row['dt']); ?>')">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="mobile-row-content">
                <div class="mobile-row-item">
                  <div class="mobile-row-label">Email</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($row['email']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">Mobile</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($row['number']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">City</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($row['city']); ?></div>
                </div>
                <div class="mobile-row-item">
                  <div class="mobile-row-label">Joined</div>
                  <div class="mobile-row-value"><?php echo htmlspecialchars($row['dt']); ?></div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-results">
            <i class="fas fa-user-slash"></i>
            <h4>No Users Found</h4>
            <p>No users match your search criteria. Try adjusting your filters.</p>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center mt-4">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            <?php endif; ?>
            
            <?php 
            // Show page numbers with ellipsis for many pages
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1'.(!empty($search) ? '&search='.urlencode($search) : '').(!empty($filter_city) ? '&city='.urlencode($filter_city) : '').'">1</a></li>';
                if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            for ($i = $start_page; $i <= $end_page; $i++): ?>
              <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.(!empty($search) ? '&search='.urlencode($search) : '').(!empty($filter_city) ? '&city='.urlencode($filter_city) : '').'">'.$total_pages.'</a></li>';
            }
            ?>
            
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- User Details Modal -->
<div class="user-modal" id="userModal">
  <div class="user-modal-content">
    <button class="modal-close" onclick="closeUserModal()">&times;</button>
    <div class="modal-header">
      <h3>User Details</h3>
    </div>
    <div class="modal-body" id="userModalBody">
      <!-- User details will be loaded here -->
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const overlay = document.getElementById('overlay');
    
    // Toggle sidebar
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    });
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    });
    
    // Close sidebar when clicking on a menu item (mobile only)
    if (window.innerWidth <= 768) {
      document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
          sidebar.classList.remove('active');
          overlay.classList.remove('active');
        });
      });
    }
    
    // Close sidebar when window is resized to desktop
    function handleResize() {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
      }
    }
    
    window.addEventListener('resize', handleResize);
    
    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
      // Simple CSV export simulation
      alert('Export functionality would generate a CSV file with all user data.');
      // In a real implementation, you would make an AJAX request to generate and download a file
    });
  });

  // Show user details in modal
  function showUserDetails(id, username, email, mobile, city, joinDate) {
    document.getElementById('userModalBody').innerHTML = `
      <div class="modal-section">
        <h4>User Information</h4>
        <p><strong>ID:</strong> #${id}</p>
        <p><strong>Username:</strong> ${username}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Mobile:</strong> ${mobile || 'Not provided'}</p>
        <p><strong>City:</strong> ${city || 'Not specified'}</p>
        <p><strong>Joined:</strong> ${joinDate}</p>
      </div>
      <div class="modal-section">
        <h4>User Activity</h4>
        <p>In a full implementation, this would show user activity statistics, property views, etc.</p>
      </div>
    `;
    
    document.getElementById('userModal').style.display = 'flex';
  }

  // Close user modal
  function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
  }

  // Close modal when clicking outside
  document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeUserModal();
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>