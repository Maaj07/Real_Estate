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

// Initialize filter variables
$name_filter = isset($_GET['name_filter']) ? $_GET['name_filter'] : '';
$email_filter = isset($_GET['email_filter']) ? $_GET['email_filter'] : '';
$phone_filter = isset($_GET['phone_filter']) ? $_GET['phone_filter'] : '';
$linkedin_filter = isset($_GET['linkedin_filter']) ? $_GET['linkedin_filter'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id_desc';

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];

if (!empty($name_filter)) {
    $where_conditions[] = "name LIKE '%" . mysqli_real_escape_string($conn, $name_filter) . "%'";
}

if (!empty($email_filter)) {
    $where_conditions[] = "email LIKE '%" . mysqli_real_escape_string($conn, $email_filter) . "%'";
}

if (!empty($phone_filter)) {
    $where_conditions[] = "phone LIKE '%" . mysqli_real_escape_string($conn, $phone_filter) . "%'";
}

if (!empty($linkedin_filter)) {
    $where_conditions[] = "linkedin LIKE '%" . mysqli_real_escape_string($conn, $linkedin_filter) . "%'";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build ORDER BY clause for sorting
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'name_asc':
        $order_by .= "name ASC";
        break;
    case 'name_desc':
        $order_by .= "name DESC";
        break;
    case 'email_asc':
        $order_by .= "email ASC";
        break;
    case 'email_desc':
        $order_by .= "email DESC";
        break;
    case 'id_asc':
        $order_by .= "id ASC";
        break;
    default:
        $order_by .= "id DESC";
        break;
}

// Handle PDF download
if (isset($_POST['download_pdf'])) {
    require_once('tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Real Estate Admin');
    $pdf->SetAuthor('Real Estate System');
    $pdf->SetTitle('Agents Report');
    $pdf->SetSubject('Agents Data Export');
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, 'Agents Report - Real Estate System', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Filter information
    $pdf->SetFont('helvetica', '', 10);
    
    $filter_text = "Applied Filters: ";
    $filters = [];
    if (!empty($name_filter)) $filters[] = "Name: $name_filter";
    if (!empty($email_filter)) $filters[] = "Email: $email_filter";
    if (!empty($phone_filter)) $filters[] = "Phone: $phone_filter";
    if (!empty($linkedin_filter)) $filters[] = "LinkedIn: $linkedin_filter";
    
    if (empty($filters)) {
        $filter_text .= "All Agents";
    } else {
        $filter_text .= implode(", ", $filters);
    }
    
    $pdf->Cell(0, 10, $filter_text, 0, 1);
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1);
    $pdf->Ln(5);
    
    // Get all agents data for PDF
    $pdf_agents_query = "SELECT * FROM agents $where_clause $order_by";
    $pdf_agents_result = mysqli_query($conn, $pdf_agents_query);
    
    if (mysqli_num_rows($pdf_agents_result) > 0) {
        // Create table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        
        $headers = array('ID', 'Name', 'Email', 'Phone', 'LinkedIn');
        $widths = array(15, 40, 60, 35, 50);
        
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', 1);
        }
        $pdf->Ln();
        
        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $counter = 1;
        
        while ($agent = mysqli_fetch_assoc($pdf_agents_result)) {
            // Check if we need a new page
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                // Add header again for new page
                $pdf->SetFont('helvetica', 'B', 10);
                for ($i = 0; $i < count($headers); $i++) {
                    $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', 1);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 9);
            }
            
            $pdf->Cell($widths[0], 6, $counter, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($agent['name'], 0, 25), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, substr($agent['email'], 0, 30), 1, 0, 'L');
            $pdf->Cell($widths[3], 6, $agent['phone'], 1, 0, 'C');
            $pdf->Cell($widths[4], 6, substr($agent['linkedin'], 0, 35), 1, 1, 'L');
            
            $counter++;
        }
        
        // Add summary
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Total Agents: " . ($counter - 1), 0, 1);
        
    } else {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'No agents found.', 0, 1);
    }
    
    // Output PDF
    $filename = 'agents_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' forces download
    exit();
}

// Get total agents count
$total_agents_query = "SELECT COUNT(*) as total FROM agents $where_clause";
$total_agents_result = mysqli_query($conn, $total_agents_query);
$total_agents_row = mysqli_fetch_assoc($total_agents_result);
$total_agents = $total_agents_row['total'];

// Get agents with pagination
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$agents_query = "SELECT * FROM agents $where_clause $order_by LIMIT $limit OFFSET $offset";
$agents_result = mysqli_query($conn, $agents_query);

// Get total pages for pagination
$total_pages_query = "SELECT COUNT(*) as total FROM agents $where_clause";
$total_pages_result = mysqli_query($conn, $total_pages_query);
$total_pages_row = mysqli_fetch_assoc($total_pages_result);
$total_pages = ceil($total_pages_row['total'] / $limit);

// Function to truncate long text
function truncateText($text, $length = 50) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Build pagination URL parameters
function buildPaginationUrl($page, $params) {
    $url = "?page=" . $page;
    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $url .= "&" . $key . "=" . urlencode($value);
        }
    }
    return $url;
}

$filter_params = [
    'name_filter' => $name_filter,
    'email_filter' => $email_filter,
    'phone_filter' => $phone_filter,
    'linkedin_filter' => $linkedin_filter,
    'sort_by' => $sort_by
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agents Report - Admin Dashboard</title>
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
    
    /* Filter Section */
    .filter-section {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(11, 31, 58, 0.1);
      margin-bottom: 30px;
    }
    
    .filter-section h4 {
      color: #0b1f3a;
      margin-bottom: 20px;
      font-weight: 600;
    }
    
    .stats-card {
      background: linear-gradient(135deg, #0b1f3a, #1a4a7a);
      color: white;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      margin-bottom: 20px;
    }
    
    .stats-card h3 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .stats-card p {
      font-size: 1.1rem;
      opacity: 0.9;
    }
    
    /* Agents Table */
    .agents-table-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(11, 31, 58, 0.1);
    }
    
    .table-header {
      background: #0b1f3a;
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .table-header h4 {
      margin: 0;
      font-weight: 600;
    }
    
    .table-responsive {
      overflow-x: auto;
    }
    
    .table {
      margin: 0;
    }
    
    .table th {
      background-color: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      color: #0b1f3a;
      padding: 15px 12px;
    }
    
    .table td {
      padding: 12px;
      vertical-align: middle;
      border-color: #e9ecef;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    .text-truncate {
      max-width: 200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .read-more {
      color: #dc3545;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
    }
    
    .read-more:hover {
      color: #c82333;
      text-decoration: underline;
    }
    
    .badge {
      font-size: 0.85em;
      padding: 6px 12px;
    }
    
    /* Pagination */
    .pagination {
      justify-content: center;
      margin: 30px 0 20px;
    }
    
    .page-link {
      color: #0b1f3a;
      border: 1px solid #dee2e6;
      padding: 8px 16px;
    }
    
    .page-link:hover {
      background-color: #0b1f3a;
      color: white;
      border-color: #0b1f3a;
    }
    
    .page-item.active .page-link {
      background-color: #0b1f3a;
      border-color: #0b1f3a;
    }
    
    .filter-row {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }
    
    .download-btn {
      background: linear-gradient(135deg, #28a745, #20c997);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .download-btn:hover {
      background: linear-gradient(135deg, #218838, #1e9e8a);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .reset-btn {
      background: linear-gradient(135deg, #6c757d, #495057);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .reset-btn:hover {
      background: linear-gradient(135deg, #5a6268, #343a40);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .agent-image {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #dee2e6;
    }
    
    .active-filters {
      background: #e7f3ff;
      border: 1px solid #b3d9ff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .filter-badge {
      background: #0b1f3a;
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.85rem;
      margin-right: 8px;
      margin-bottom: 8px;
      display: inline-block;
    }
    
    @media (max-width: 768px) {
      .main1 {
        padding: 15px;
        gap: 15px;
      }
      
      .filter-section {
        padding: 15px;
      }
      
      .table th, .table td {
        padding: 8px 6px;
        font-size: 0.9rem;
      }
      
      .text-truncate {
        max-width: 120px;
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
      
      .table-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }
    }
    
    @media (max-width: 576px) {
      .stats-card h3 {
        font-size: 2rem;
      }
      
      .table-responsive {
        font-size: 0.85rem;
      }
      
      .btn-group {
        flex-direction: column;
      }
      
      .btn-group .btn {
        border-radius: 6px !important;
        margin-bottom: 5px;
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
    <div class="d-grid gap-2 d-md-block mb-4">
      <button class="btn btn-primary" type="button">
        <i class="fa fa-arrow-left"></i> <a href="Report.php" style="color: white;">Back</a>
      </button>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="row">
        <div class="col-md-8">
          <h4><i class="fas fa-filter me-2"></i>Filter Agents</h4>
          
          <!-- Active Filters Display -->
          <?php if (!empty($name_filter) || !empty($email_filter) || !empty($phone_filter) || !empty($linkedin_filter)): ?>
          <div class="active-filters">
            <h6><i class="fas fa-tags me-2"></i>Active Filters:</h6>
            <?php if (!empty($name_filter)): ?>
              <span class="filter-badge">Name: <?= htmlspecialchars($name_filter) ?> <a href="<?= buildPaginationUrl(1, array_merge($filter_params, ['name_filter' => ''])) ?>" style="color: white; margin-left: 5px;">×</a></span>
            <?php endif; ?>
            <?php if (!empty($email_filter)): ?>
              <span class="filter-badge">Email: <?= htmlspecialchars($email_filter) ?> <a href="<?= buildPaginationUrl(1, array_merge($filter_params, ['email_filter' => ''])) ?>" style="color: white; margin-left: 5px;">×</a></span>
            <?php endif; ?>
            <?php if (!empty($phone_filter)): ?>
              <span class="filter-badge">Phone: <?= htmlspecialchars($phone_filter) ?> <a href="<?= buildPaginationUrl(1, array_merge($filter_params, ['phone_filter' => ''])) ?>" style="color: white; margin-left: 5px;">×</a></span>
            <?php endif; ?>
            <?php if (!empty($linkedin_filter)): ?>
              <span class="filter-badge">LinkedIn: <?= htmlspecialchars($linkedin_filter) ?> <a href="<?= buildPaginationUrl(1, array_merge($filter_params, ['linkedin_filter' => ''])) ?>" style="color: white; margin-left: 5px;">×</a></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          
          <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name_filter" value="<?= htmlspecialchars($name_filter) ?>" placeholder="Search by name...">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="text" class="form-control" name="email_filter" value="<?= htmlspecialchars($email_filter) ?>" placeholder="Search by email...">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" name="phone_filter" value="<?= htmlspecialchars($phone_filter) ?>" placeholder="Search by phone...">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">LinkedIn</label>
              <input type="text" class="form-control" name="linkedin_filter" value="<?= htmlspecialchars($linkedin_filter) ?>" placeholder="Search by LinkedIn...">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Sort By</label>
              <select class="form-select" name="sort_by">
                <option value="id_desc" <?= $sort_by == 'id_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="id_asc" <?= $sort_by == 'id_asc' ? 'selected' : '' ?>>Oldest First</option>
                <option value="name_asc" <?= $sort_by == 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                <option value="name_desc" <?= $sort_by == 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                <option value="email_asc" <?= $sort_by == 'email_asc' ? 'selected' : '' ?>>Email A-Z</option>
                <option value="email_desc" <?= $sort_by == 'email_desc' ? 'selected' : '' ?>>Email Z-A</option>
              </select>
            </div>
            
            <div class="col-12 mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Apply Filters
              </button>
              <a href="?" class="btn reset-btn ms-2">
                <i class="fas fa-redo me-2"></i>Reset Filters
              </a>
              <button type="button" class="btn download-btn ms-2" onclick="downloadPDF()">
                <i class="fas fa-download me-2"></i>Download PDF
              </button>
            </div>
          </form>
        </div>
        
        <div class="col-md-4">
          <div class="stats-card">
            <h3><?= $total_agents ?></h3>
            <p>Total Agents</p>
            <?php if (!empty($name_filter) || !empty($email_filter) || !empty($phone_filter) || !empty($linkedin_filter)): ?>
              <small>Filtered Results</small>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Agents Table -->
    <div class="agents-table-container">
      <div class="table-header">
        <h4><i class="fas fa-user-tie me-2"></i>Agents List (<?= $total_agents ?> found)</h4>
        <button type="button" class="btn download-btn" onclick="downloadPDF()">
          <i class="fas fa-download me-2"></i>Download PDF
        </button>
      </div>
      
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>LinkedIn</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($agents_result) > 0): ?>
              <?php $counter = $offset + 1; ?>
              <?php while ($agent = mysqli_fetch_assoc($agents_result)): ?>
                <tr>
                  <td><?= $counter ?></td>
                  <td>
                    <span class="text-truncate" title="<?= htmlspecialchars($agent['name']) ?>">
                      <?= htmlspecialchars(truncateText($agent['name'], 20)) ?>
                    </span>
                    <?php if (strlen($agent['name']) > 20): ?>
                      <a href="#" class="read-more" data-bs-toggle="modal" data-bs-target="#agentModal<?= $agent['id'] ?>">Read more</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="text-truncate" title="<?= htmlspecialchars($agent['email']) ?>">
                      <?= htmlspecialchars(truncateText($agent['email'], 25)) ?>
                    </span>
                    <?php if (strlen($agent['email']) > 25): ?>
                      <a href="#" class="read-more" data-bs-toggle="modal" data-bs-target="#agentModal<?= $agent['id'] ?>">Read more</a>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($agent['phone']) ?></td>
                  <td>
                    <span class="text-truncate" title="<?= htmlspecialchars($agent['linkedin']) ?>">
                      <?= htmlspecialchars(truncateText($agent['linkedin'], 30)) ?>
                    </span>
                    <?php if (strlen($agent['linkedin']) > 30): ?>
                      <a href="#" class="read-more" data-bs-toggle="modal" data-bs-target="#agentModal<?= $agent['id'] ?>">Read more</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#agentModal<?= $agent['id'] ?>">
                      <i class="fas fa-eye"></i> View
                    </button>
                  </td>
                </tr>
                
                <!-- Agent Details Modal -->
                <div class="modal fade" id="agentModal<?= $agent['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Agent Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="text-center mb-4">
                          <h4 class="mt-3"><?= htmlspecialchars($agent['name']) ?></h4>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <p><strong>Email:</strong><br><?= htmlspecialchars($agent['email']) ?></p>
                            <p><strong>Phone:</strong><br><?= htmlspecialchars($agent['phone']) ?></p>
                          </div>
                          <div class="col-md-6">
                            <p><strong>LinkedIn:</strong><br>
                              <?php if (!empty($agent['linkedin'])): ?>
                                <a href="<?= htmlspecialchars($agent['linkedin']) ?>" target="_blank">View Profile</a>
                              <?php else: ?>
                                Not provided
                              <?php endif; ?>
                            </p>
                            <p><strong>Agent ID:</strong><br><?= $agent['id'] ?></p>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
                <?php $counter++; ?>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center py-4">
                  <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                  <h5>No agents found</h5>
                  <p class="text-muted">No agents match your current filter criteria.</p>
                  <a href="?" class="btn btn-primary">Clear Filters</a>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav>
          <ul class="pagination">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildPaginationUrl($page - 1, $filter_params) ?>">
                  Previous
                </a>
              </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= buildPaginationUrl($i, $filter_params) ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildPaginationUrl($page + 1, $filter_params) ?>">
                  Next
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
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

  // PDF download function
  function downloadPDF() {
    // Create a hidden form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    // Add current filter values
    const nameFilter = document.querySelector('input[name="name_filter"]').value;
    const emailFilter = document.querySelector('input[name="email_filter"]').value;
    const phoneFilter = document.querySelector('input[name="phone_filter"]').value;
    const linkedinFilter = document.querySelector('input[name="linkedin_filter"]').value;
    const sortBy = document.querySelector('select[name="sort_by"]').value;
    
    if (nameFilter) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'name_filter';
      input.value = nameFilter;
      form.appendChild(input);
    }
    
    if (emailFilter) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'email_filter';
      input.value = emailFilter;
      form.appendChild(input);
    }
    
    if (phoneFilter) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'phone_filter';
      input.value = phoneFilter;
      form.appendChild(input);
    }
    
    if (linkedinFilter) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'linkedin_filter';
      input.value = linkedinFilter;
      form.appendChild(input);
    }
    
    if (sortBy) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'sort_by';
      input.value = sortBy;
      form.appendChild(input);
    }
    
    const downloadInput = document.createElement('input');
    downloadInput.type = 'hidden';
    downloadInput.name = 'download_pdf';
    downloadInput.value = '1';
    form.appendChild(downloadInput);
    
    document.body.appendChild(form);
    form.submit();
  }

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