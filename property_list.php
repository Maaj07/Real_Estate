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

// Handle property deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $sql = "DELETE FROM properties WHERE id = $delete_id";
    if (mysqli_query($conn, $sql)) {
        $success = "Property deleted successfully!";
    } else {
        $error = "Error deleting property: " . mysqli_error($conn);
    }
}

// Search and filter functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$filter_city = isset($_GET['city']) ? mysqli_real_escape_string($conn, $_GET['city']) : '';

// Build WHERE clause for search and filter
$where_clause = "";
if (!empty($search)) {
    $where_clause .= " WHERE (p.title LIKE '%$search%' OR p.city LIKE '%$search%' OR a.name LIKE '%$search%')";
}

if (!empty($filter_status)) {
    if (empty($where_clause)) {
        $where_clause .= " WHERE p.status = '$filter_status'";
    } else {
        $where_clause .= " AND p.status = '$filter_status'";
    }
}

if (!empty($filter_city)) {
    if (empty($where_clause)) {
        $where_clause .= " WHERE p.city = '$filter_city'";
    } else {
        $where_clause .= " AND p.city = '$filter_city'";
    }
}

// Pagination settings
$per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $per_page;

// Get total number of properties with search/filter
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties p JOIN agents a ON p.agent_id = a.id" . $where_clause);
$total_row = mysqli_fetch_assoc($total_result);
$total_properties_count = $total_row['total'];

// Calculate total pages
$total_pages = ceil($total_properties_count / $per_page);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Fetch properties for current page with images
$properties_query = mysqli_query($conn, 
    "SELECT p.id, p.title, p.status, p.price, p.city, p.area, p.bedrooms, p.bathrooms, p.image, a.name AS agent_name 
     FROM properties p
     JOIN agents a ON p.agent_id = a.id"
     . $where_clause . 
     " ORDER BY p.id DESC
     LIMIT $start, $per_page");

$properties = [];
if ($properties_query) {
    while ($row = mysqli_fetch_assoc($properties_query)) {
        $properties[] = $row;
    }
}

// Get unique cities for filter dropdown
$cities_query = mysqli_query($conn, "SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city != '' ORDER BY city ASC");
$cities = [];
while ($city_row = mysqli_fetch_assoc($cities_query)) {
    $cities[] = $city_row['city'];
}

// Get property statistics
$property_stats_query = "
    SELECT 
        COUNT(*) as total_properties,
        AVG(price) as avg_price,
        COUNT(CASE WHEN status = 'For Sale' THEN 1 END) as for_sale,
        COUNT(CASE WHEN status = 'For Rent' THEN 1 END) as for_rent
    FROM properties
";
$property_stats_result = mysqli_query($conn, $property_stats_query);
$property_stats = mysqli_fetch_assoc($property_stats_result);

// Get counts for dashboard cards
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
  <meta charset="UTF-8">
  <title>Property List - Admin Dashboard</title>
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
    }
    
    /* Property List Styles */
    .property-list-container {
      width: 100%;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      padding: 30px;
      margin-top: 20px;
    }
    
    .section-title {
      color: #0b1f3a;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #0b1f3a;
      text-align: center;
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    /* Property Stats Section */
    .property-stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stat-card i {
      font-size: 2rem;
      color: #0b1f3a;
      margin-bottom: 10px;
    }
    
    .stat-card h3 {
      font-size: 1.5rem;
      margin-bottom: 5px;
      color: #0b1f3a;
    }
    
    .stat-card p {
      color: #6c757d;
      font-size: 0.9rem;
      position: static;
    }
    
    /* Search and Filter Section */
    .search-filter-container {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
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

    .btn-reset, .btn-export, .btn-add {
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
    
    .btn-add {
      background: #0b1f3a;
      color: white;
    }

    .btn-reset:hover, .btn-export:hover, .btn-add:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* Property Table Styles */
    .table-container {
      overflow-x: auto;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .property-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1000px;
      background: white;
    }
    
    .property-table thead {
      background: linear-gradient(135deg, #0b1f3a 0%, #1e3a5f 100%);
    }
    
    .property-table th {
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 600;
      font-size: 0.95rem;
    }
    
    .property-table td {
      padding: 15px;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }
    
    .property-table tr {
      transition: all 0.3s ease;
    }
    
    .property-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    .property-table tr:hover {
      /* background-color: #f8f9fa; */
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .property-image {
      width: 80px;
      height: 60px;
      border-radius: 8px;
      object-fit: cover;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .property-status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-sale {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }
    
    .status-rent {
      background: linear-gradient(135deg, #fd7e14 0%, #e65c00 100%);
      color: white;
    }
    
    .actions {
      display: flex;
      gap: 8px;
    }
    
    .btn-sm {
      padding: 6px 12px;
      font-size: 0.85rem;
      border-radius: 6px;
      transition: all 0.3s ease;
    }
    
    .btn-sm:hover {
      transform: translateY(-2px);
    }
    
    /* Mobile Property Cards */
    .mobile-property-list {
      display: none;
    }
    
    .property-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .property-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .property-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .property-image-mobile {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .property-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-label {
      font-size: 0.8rem;
      color: #666;
      margin-bottom: 3px;
      font-weight: 600;
    }
    
    .info-value {
      font-weight: 500;
      color: #0b1f3a;
    }
    
    .property-actions {
      display: flex;
      gap: 10px;
    }
    
    /* Animations */
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
      
      .property-stats-container {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
      
      .property-table-container {
        display: none;
      }
      
      .mobile-property-list {
        display: block;
      }
      
      .property-info {
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
      
      .property-list-container {
        padding: 20px;
      }
      
      .section-title {
        font-size: 1.5rem;
      }
      
      .property-actions {
        flex-direction: column;
      }
      
      .property-actions .btn {
        width: 100%;
      }
      
      .action-buttons {
        flex-direction: column;
      }

      .btn-reset, .btn-export, .btn-add {
        width: 100%;
        justify-content: center;
      }
      
      .property-stats-container {
        grid-template-columns: 1fr;
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
    
    .price-highlight {
      color: #28a745;
      font-weight: 700;
    }
    
    .area-badge {
      background: #e9ecef;
      color: #495057;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
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
    <!-- Dashboard Cards -->
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
    <div class="property-list-container">
      <h2 class="section-title">Property List (<?php echo $total_properties_count; ?> found)</h2>
      
      <!-- Search and Filter Section -->
      <div class="search-filter-container">
        <form method="GET" class="w-100 d-flex flex-wrap gap-3">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search properties..." value="<?php echo htmlspecialchars($search); ?>">
          </div>
          
          <div class="filter-select">
            <select name="status">
              <option value="">All Status</option>
              <option value="For Sale" <?php echo $filter_status == 'For Sale' ? 'selected' : ''; ?>>For Sale</option>
              <option value="For Rent" <?php echo $filter_status == 'For Rent' ? 'selected' : ''; ?>>For Rent</option>
            </select>
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
            <a href="property_list.php" class="btn-reset">
              <i class="fas fa-times"></i> Reset
            </a>
            <a href="add_property.php" class="btn-add">
              <i class="fas fa-plus"></i> Add Property
            </a>
            <!-- <button type="button" class="btn-export" id="exportBtn">
              <i class="fas fa-download"></i> Export
            </button> -->
          </div>
        </form>
      </div>
      
      <?php if (isset($success)): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <!-- Property Table (Desktop) -->
      <div class="table-container d-none d-lg-block">
        <table class="property-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Status</th>
              <th>Price</th>
              <th>City</th>
              <th>Area</th>
              <th>Bed/Bath</th>
              <th>Agent</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($properties)): ?>
              <tr>
                <td colspan="9">
                  <div class="no-results">
                    <i class="fas fa-home"></i>
                    <h4>No Properties Found</h4>
                    <p>No properties match your search criteria. Try adjusting your filters.</p>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($properties as $property): ?>
                <tr>
                  <td>
                    <?php if (!empty($property['image'])): ?>
                      <img src="uploads/<?php echo htmlspecialchars($property['image']); ?>" 
                           alt="<?php echo htmlspecialchars($property['title']); ?>" 
                           class="property-image">
                    <?php else: ?>
                      <div style="width:80px; height:60px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:8px;">
                        <i class="fas fa-home" style="color:#999;"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><strong><?php echo htmlspecialchars($property['title']); ?></strong></td>
                  <td>
                    <span class="property-status <?php echo $property['status'] === 'For Sale' ? 'status-sale' : 'status-rent'; ?>">
                      <?php echo htmlspecialchars($property['status']); ?>
                    </span>
                  </td>
                  <td class="price-highlight">$<?php echo number_format($property['price']); ?></td>
                  <td><?php echo htmlspecialchars($property['city']); ?></td>
                  <td><span class="area-badge"><?php echo number_format($property['area']); ?> sq.ft</span></td>
                  <td><?php echo $property['bedrooms']; ?> Beds / <?php echo $property['bathrooms']; ?> Baths</td>
                  <td><?php echo htmlspecialchars($property['agent_name']); ?></td>
                  <td>
                    <div class="actions">
                      <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                      </a>
                      <a href="property_list.php?delete_id=<?php echo $property['id']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_status) ? '&status='.urlencode($filter_status) : ''; ?><?php echo !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?><?php echo $page > 1 ? '&page='.$page : ''; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this property?');">
                        <i class="fas fa-trash me-1"></i>Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Mobile Property List -->
      <div class="mobile-property-list d-lg-none">
        <?php if (empty($properties)): ?>
          <div class="no-results">
            <i class="fas fa-home"></i>
            <h4>No Properties Found</h4>
            <p>No properties match your search criteria. Try adjusting your filters.</p>
          </div>
        <?php else: ?>
          <?php foreach ($properties as $property): ?>
            <div class="property-card">
              <div class="property-card-header">
                <h4><?php echo htmlspecialchars($property['title']); ?></h4>
                <span class="property-status <?php echo $property['status'] === 'For Sale' ? 'status-sale' : 'status-rent'; ?>">
                  <?php echo htmlspecialchars($property['status']); ?>
                </span>
              </div>
              
              <?php if (!empty($property['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($property['image']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-image-mobile">
              <?php else: ?>
                <div style="width:100%; height:200px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:8px; margin-bottom:15px;">
                  <i class="fas fa-home" style="color:#999; font-size:48px;"></i>
                </div>
              <?php endif; ?>
              
              <div class="property-info">
                <div class="info-item">
                  <span class="info-label">Price</span>
                  <span class="info-value price-highlight">$<?php echo number_format($property['price']); ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">City</span>
                  <span class="info-value"><?php echo htmlspecialchars($property['city']); ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Area</span>
                  <span class="info-value"><?php echo number_format($property['area']); ?> sq.ft</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Bed/Bath</span>
                  <span class="info-value"><?php echo $property['bedrooms']; ?> / <?php echo $property['bathrooms']; ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Agent</span>
                  <span class="info-value"><?php echo htmlspecialchars($property['agent_name']); ?></span>
                </div>
              </div>
              
              <div class="property-actions">
                <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
                <a href="property_list.php?delete_id=<?php echo $property['id']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_status) ? '&status='.urlencode($filter_status) : ''; ?><?php echo !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?><?php echo $page > 1 ? '&page='.$page : ''; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">
                  <i class="fas fa-trash me-1"></i>Delete
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
          <nav>
            <ul class="pagination">
              <?php if ($page > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page - 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : ''; ?><?= !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>">
                    <i class="fas fa-chevron-left me-1"></i>Previous
                  </a>
                </li>
              <?php endif; ?>
              
              <?php 
              // Show page numbers with ellipsis for many pages
              $start_page = max(1, $page - 2);
              $end_page = min($total_pages, $page + 2);
              
              if ($start_page > 1) {
                  echo '<li class="page-item"><a class="page-link" href="?page=1'.(!empty($search) ? '&search='.urlencode($search) : '').(!empty($filter_status) ? '&status='.urlencode($filter_status) : '').(!empty($filter_city) ? '&city='.urlencode($filter_city) : '').'">1</a></li>';
                  if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
              }
              
              for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                  <a class="page-link" href="?page=<?= $i; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : ''; ?><?= !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>"><?= $i; ?></a>
                </li>
              <?php endfor; ?>
              
              <?php
              if ($end_page < $total_pages) {
                  if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.(!empty($search) ? '&search='.urlencode($search) : '').(!empty($filter_status) ? '&status='.urlencode($filter_status) : '').(!empty($filter_city) ? '&city='.urlencode($filter_city) : '').'">'.$total_pages.'</a></li>';
              }
              ?>
              
              <?php if ($page < $total_pages): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page + 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= !empty($filter_status) ? '&status='.urlencode($filter_status) : ''; ?><?= !empty($filter_city) ? '&city='.urlencode($filter_city) : ''; ?>">
                    Next<i class="fas fa-chevron-right ms-1"></i>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      <?php endif; ?>
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
      alert('Export functionality would generate a CSV file with all property data.');
      // In a real implementation, you would make an AJAX request to generate and download a file
    });
    
    // Confirm before deleting a property
    document.querySelectorAll('.btn-danger').forEach(button => {
      button.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this property?')) {
          e.preventDefault();
        }
      });
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>