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

// Database connection
$conn = mysqli_connect("localhost", "root", "", "real_estate");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    if (!empty($name)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            // Refresh to show the new category
            header("Location: categories.php");
            exit();
        } else {
            $add_error = "Error adding category: " . $conn->error;
        }
    } else {
        $add_error = "Category name cannot be empty";
    }
}

$agent_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_agents FROM agents");
$agent_count_row = mysqli_fetch_assoc($agent_count_result);
$total_agents = $agent_count_row['total_agents'];

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit();
}

// Handle Edit
if (isset($_POST['edit_category'])) {
  $id = intval($_POST['edit_id']);
  $new_name = trim($_POST['edit_name']);
  if (!empty($new_name)) {
      $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
      $stmt->bind_param("si", $new_name, $id);
      $stmt->execute();
  }
  header("Location: categories.php");
  exit();
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $where_clause = " WHERE name LIKE '%$search%'";
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories" . $where_clause);
$total_row = mysqli_fetch_assoc($total_result);
$total_categories_count = $total_row['total'];
$total_pages = ceil($total_categories_count / $limit);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Fetch categories
$result = mysqli_query($conn, "SELECT * FROM categories" . $where_clause . " ORDER BY id ASC LIMIT $limit OFFSET $offset");

$category_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_categories FROM categories");
$category_count_row = mysqli_fetch_assoc($category_count_result);
$total_categories = $category_count_row['total_categories'];

$user_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$user_count_row = mysqli_fetch_assoc($user_count_result);
$total_users = $user_count_row['total_users'];

$property_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$property_count_row = mysqli_fetch_assoc($property_count_result);
$total_properties = $property_count_row['total_properties'];

// Get category usage statistics
$category_usage_query = "
    SELECT c.id, c.name, COUNT(p.id) as property_count 
    FROM categories c 
    LEFT JOIN properties p ON c.id = p.category_id 
    GROUP BY c.id
";
$category_usage_result = mysqli_query($conn, $category_usage_query);
$category_usage = [];
while ($row = mysqli_fetch_assoc($category_usage_result)) {
    $category_usage[$row['id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Categories</title>
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

    .dropdown-item:hover {
      background-color: whitesmoke;
      color: black;
    }
    
    /* Category container styles */
    .category-container {
      width: 100%;
      margin-top: 20px;
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
    
    .add-category-form {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
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
    
    .table-container {
      overflow-x: auto;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .category-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
      background: white;
    }
    
    .category-table thead {
      background: linear-gradient(135deg, #0b1f3a 0%, #1e3a5f 100%);
    }
    
    .category-table th {
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 600;
      font-size: 0.95rem;
    }
    
    .category-table td {
      padding: 15px;
      border-bottom: 1px solid #e0e0e0;
      vertical-align: middle;
    }
    
    .category-table tr {
      transition: all 0.3s ease;
    }
    
    .category-table tr:nth-child(even) {
      background-color: #f9f9f9;
      color: black;
    }
    
    .category-table tr:hover {
      /* background-color: #f0f8ff; */
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .actions {
      display: flex;
      gap: 10px;
    }
    
    .btn-sm {
      padding: 6px 12px;
      font-size: 14px;
      border-radius: 6px;
      transition: all 0.3s ease;
    }
    
    .btn-sm:hover {
      transform: translateY(-2px);
    }
    
    .property-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #0b1f3a;
      color: white;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      font-size: 0.8rem;
      margin-left: 8px;
    }
    
    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    
    /* Mobile table styles */
    .mobile-category-card {
      display: none;
      background: white;
      border-radius: 10px;
      margin-bottom: 15px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .mobile-category-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .mobile-category-card .mobile-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .mobile-category-card .mobile-card-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .mobile-category-card .mobile-card-item {
      margin-bottom: 8px;
    }

    .mobile-category-card .mobile-card-label {
      font-weight: 600;
      color: #0b1f3a;
      font-size: 0.85rem;
    }

    .mobile-category-card .mobile-card-value {
      color: #6c757d;
      font-size: 0.85rem;
    }

    .mobile-category-card .mobile-card-full {
      grid-column: 1 / -1;
    }
    
    /* Stats Section */
    .stats-container {
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

    /* Mobile Navigation */
    .mobile-nav-btn {
      display: none;
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
        min-width: calc(50% - 20px);
      }
      
      .stats-container {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
      
      .add-category-form .d-flex {
        flex-direction: column;
        gap: 15px;
      }
      
      .add-category-form .col-md-10,
      .add-category-form .col-md-2 {
        width: 100%;
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
      
      .category-table thead {
        display: none;
      }

      .category-table tbody tr {
        display: none;
      }

      .mobile-category-card {
        display: block;
      }
      
      .actions {
        flex-direction: column;
      }
      
      .actions .btn {
        width: 100%;
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
      
      .category-container {
        padding: 20px;
      }
      
      .section-title {
        font-size: 1.5rem;
      }
      
      .mobile-category-card .mobile-card-content {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }

      .btn-reset, .btn-export {
        width: 100%;
        justify-content: center;
      }
      
      .stats-container {
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
        <p><?php echo $total_users; ?></p>
      </div>
      <div class="card">
        <i class="fa-solid fa-house-circle-check card-icon"></i>
        <h3>Categories</h3>
        <p><?php echo $total_categories; ?></p>
      </div>
    </div>

    <div class="category-container">
      <h2 class="section-title">Property Categories Management</h2>
      
      <!-- Category Stats -->
      <div class="stats-container">
        <div class="stat-card">
          <i class="fas fa-list"></i>
          <h3><?php echo $total_categories; ?></h3>
          <p>Total Categories</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-home"></i>
          <h3><?php echo $total_properties; ?></h3>
          <p>Properties Using Categories</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-chart-pie"></i>
          <h3><?php echo $total_categories > 0 ? round($total_properties / $total_categories, 1) : 0; ?></h3>
          <p>Avg Properties per Category</p>
        </div>
      </div>

      <!-- Add Category Form -->
      <div class="add-category-form">
        <?php if (!empty($add_error)): ?>
          <div class="alert alert-danger"><?php echo $add_error; ?></div>
        <?php endif; ?>
        <form method="POST" class="d-flex justify-content-between gap-2">
          <div class="col-md-10">
            <input type="text" name="category_name" class="form-control" placeholder="Enter new category name" required>
          </div>
          <div class="col-md-2">
            <button type="submit" name="add_category" class="btn btn-primary w-100">
              <i class="fa fa-plus me-2"></i>Add Category
            </button>
          </div>
        </form>
      </div>
      
      <!-- Search and Filter Section -->
      <div class="search-filter-container">
        <form method="GET" class="w-100 d-flex flex-wrap gap-3">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
          </div>
          
          <div class="action-buttons">
            <button type="submit" class="btn-reset">
              <i class="fas fa-filter"></i> Search
            </button>
            <a href="categories.php" class="btn-reset">
              <i class="fas fa-times"></i> Reset
            </a>
            <!-- <button type="button" class="btn-export" id="exportBtn">
              <i class="fas fa-download"></i> Export
            </button> -->
          </div>
        </form>
      </div>

      <!-- Category Table -->
      <div class="table-container">
        <!-- Desktop Table View -->
        <table class="category-table d-none d-lg-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Category Name</th>
              <th>Properties Count</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php 
              mysqli_data_seek($result, 0);
              while ($row = mysqli_fetch_assoc($result)): 
                $property_count = isset($category_usage[$row['id']]) ? $category_usage[$row['id']]['property_count'] : 0;
              ?>
                <tr>
                  <td><?= $row['id']; ?></td>
                  <td>
                    <?= htmlspecialchars($row['name']); ?>
                    <?php if ($property_count > 0): ?>
                      <span class="property-count" title="<?= $property_count; ?> properties"><?= $property_count; ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= $property_count; ?> properties</td>
                  <td class="actions">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                      <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <a href="?delete=<?= $row['id']; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= $page > 1 ? '&page='.$page : ''; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">
                      <i class="fas fa-trash me-1"></i>Delete
                    </a>
                  </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Category</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="edit_id" value="<?= $row['id']; ?>">
                          <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="edit_name" class="form-control" value="<?= htmlspecialchars($row['name']); ?>" required>
                          </div>
                          <div class="form-group mt-3">
                            <label>Properties in this category: <strong><?= $property_count; ?></strong></label>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="edit_category" class="btn btn-success">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center py-4">No categories found matching your criteria</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <!-- Mobile Category Cards -->
        <div class="d-lg-none" id="mobileCategoriesView">
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php 
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)): 
              $property_count = isset($category_usage[$row['id']]) ? $category_usage[$row['id']]['property_count'] : 0;
            ?>
              <div class="mobile-category-card">
                <div class="mobile-card-header">
                  <div>
                    <strong>#<?= $row['id']; ?></strong>
                    <span style="color: #6c757d; font-size: 0.9rem;"><?= htmlspecialchars($row['name']); ?></span>
                  </div>
                  <span class="property-count" title="<?= $property_count; ?> properties"><?= $property_count; ?></span>
                </div>
                <div class="mobile-card-content">
                  <div class="mobile-card-item">
                    <div class="mobile-card-label">Properties</div>
                    <div class="mobile-card-value"><?= $property_count; ?> properties</div>
                  </div>
                  <div class="mobile-card-item mobile-card-full">
                    <div class="mobile-card-label">Actions</div>
                    <div class="actions mt-2">
                      <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                        <i class="fas fa-edit me-1"></i>Edit
                      </button>
                      <a href="?delete=<?= $row['id']; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= $page > 1 ? '&page='.$page : ''; ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this category?')">
                        <i class="fas fa-trash me-1"></i>Delete
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-results">
              <i class="fas fa-list-alt"></i>
              <h4>No Categories Found</h4>
              <p>No categories match your search criteria. Try adjusting your search term.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
          <nav>
            <ul class="pagination">
              <?php if ($page > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page - 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                    <i class="fas fa-chevron-left me-1"></i>Prev
                  </a>
                </li>
              <?php endif; ?>
              
              <?php 
              // Show page numbers with ellipsis for many pages
              $start_page = max(1, $page - 2);
              $end_page = min($total_pages, $page + 2);
              
              if ($start_page > 1) {
                  echo '<li class="page-item"><a class="page-link" href="?page=1'.(!empty($search) ? '&search='.urlencode($search) : '').'">1</a></li>';
                  if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
              }
              
              for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                  <a class="page-link" href="?page=<?= $i; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?>"><?= $i; ?></a>
                </li>
              <?php endfor; ?>
              
              <?php
              if ($end_page < $total_pages) {
                  if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.(!empty($search) ? '&search='.urlencode($search) : '').'">'.$total_pages.'</a></li>';
              }
              ?>
              
              <?php if ($page < $total_pages): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page + 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?>">
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
      alert('Export functionality would generate a CSV file with all category data.');
      // In a real implementation, you would make an AJAX request to generate and download a file
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>