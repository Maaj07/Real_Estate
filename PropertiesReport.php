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

// Handle date filter - FIXED: Use POST for form submission
$date_filter = isset($_POST['date_filter']) ? $_POST['date_filter'] : 'all';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Also check GET parameters for pagination
if (isset($_GET['date_filter'])) {
    $date_filter = $_GET['date_filter'];
}
if (isset($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}
if (isset($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}

// Build WHERE clause for date filtering - FIXED: Use created_at for properties table
$where_clause = "WHERE 1=1";
if ($date_filter == 'today') {
    $where_clause .= " AND DATE(created_at) = CURDATE()";
} elseif ($date_filter == 'yesterday') {
    $where_clause .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($date_filter == 'this_week') {
    $where_clause .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($date_filter == 'this_month') {
    $where_clause .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
} elseif ($date_filter == 'custom' && !empty($start_date) && !empty($end_date)) {
    $where_clause .= " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
}

// Handle PDF download
if (isset($_POST['download_pdf'])) {
    require_once('tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Real Estate Admin');
    $pdf->SetAuthor('Real Estate System');
    $pdf->SetTitle('Properties Report');
    $pdf->SetSubject('Properties Data Export');
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, 'Properties Report - Real Estate System', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Filter information
    $pdf->SetFont('helvetica', '', 10);
    $filter_text = "Date Filter: " . ucfirst(str_replace('_', ' ', $date_filter));
    if ($date_filter == 'custom' && !empty($start_date) && !empty($end_date)) {
        $filter_text .= " (From: $start_date To: $end_date)";
    }
    $pdf->Cell(0, 10, $filter_text, 0, 1);
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1);
    $pdf->Ln(5);
    
    // Get all properties data for PDF with category and agent names
    $pdf_properties_query = "SELECT p.*, c.name as category_name, a.name as agent_name 
                            FROM properties p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN agents a ON p.agent_id = a.id 
                            $where_clause 
                            ORDER BY p.created_at DESC";
    $pdf_properties_result = mysqli_query($conn, $pdf_properties_query);
    
    if (mysqli_num_rows($pdf_properties_result) > 0) {
        // Create table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);
        
        $headers = array('ID', 'Title', 'Category', 'Status', 'Price', 'City', 'Area', 'Beds', 'Baths', 'Created Date');
        $widths = array(10, 35, 25, 20, 25, 20, 15, 10, 10, 20);
        
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', 1);
        }
        $pdf->Ln();
        
        // Table data
        $pdf->SetFont('helvetica', '', 7);
        $counter = 1;
        
        while ($property = mysqli_fetch_assoc($pdf_properties_result)) {
            // Check if we need a new page
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                // Add header again for new page
                $pdf->SetFont('helvetica', 'B', 8);
                for ($i = 0; $i < count($headers); $i++) {
                    $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', 1);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 7);
            }
            
            $pdf->Cell($widths[0], 6, $property['id'], 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($property['title'], 0, 25), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, substr($property['category_name'], 0, 15), 1, 0, 'L');
            $pdf->Cell($widths[3], 6, $property['status'], 1, 0, 'C');
            $pdf->Cell($widths[4], 6, '$' . number_format($property['price']), 1, 0, 'R');
            $pdf->Cell($widths[5], 6, substr($property['city'], 0, 12), 1, 0, 'C');
            $pdf->Cell($widths[6], 6, $property['area'] . ' sqft', 1, 0, 'C');
            $pdf->Cell($widths[7], 6, $property['bedrooms'], 1, 0, 'C');
            $pdf->Cell($widths[8], 6, $property['bathrooms'], 1, 0, 'C');
            $pdf->Cell($widths[9], 6, date('Y-m-d', strtotime($property['created_at'])), 1, 1, 'C');
            
            $counter++;
        }
        
        // Add summary
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Total Properties: " . ($counter - 1), 0, 1);
        
    } else {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'No properties found with the current filter criteria.', 0, 1);
    }
    
    // Output PDF
    $filename = 'properties_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' forces download
    exit();
}

// Get total properties count
$total_properties_query = "SELECT COUNT(*) as total FROM properties $where_clause";
$total_properties_result = mysqli_query($conn, $total_properties_query);
$total_properties_row = mysqli_fetch_assoc($total_properties_result);
$total_properties = $total_properties_row['total'];

// Get properties with pagination including category and agent names
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$properties_query = "SELECT p.*, c.name as category_name, a.name as agent_name 
                    FROM properties p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN agents a ON p.agent_id = a.id 
                    $where_clause 
                    ORDER BY p.created_at DESC 
                    LIMIT $limit OFFSET $offset";
$properties_result = mysqli_query($conn, $properties_query);

// Get total pages for pagination
$total_pages_query = "SELECT COUNT(*) as total FROM properties $where_clause";
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

// Function to format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Properties Report - Admin Dashboard</title>
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
    
    /* Properties Table */
    .properties-table-container {
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
    
    .status-available {
      background-color: #28a745;
    }
    
    .status-not-available {
      background-color: #dc3545;
    }
    
    .price-tag {
      font-weight: bold;
      color: #0b1f3a;
      font-size: 1.1em;
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
    
    /* Custom Date Picker */
    .custom-date-fields {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-top: 15px;
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
    
    .property-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
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
    
    .d-grid {
      margin: 2rem;
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
          <h4><i class="fas fa-filter me-2"></i>Filter Properties by Date</h4>
          <form method="POST" class="row g-3"> <!-- CHANGED: method to POST -->
            <div class="col-md-6">
              <label class="form-label">Date Range</label>
              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="date_filter" value="all" id="all" 
                  <?= $date_filter == 'all' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="all">All</label>

                <input type="radio" class="btn-check" name="date_filter" value="today" id="today" 
                  <?= $date_filter == 'today' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="today">Today</label>

                <input type="radio" class="btn-check" name="date_filter" value="yesterday" id="yesterday" 
                  <?= $date_filter == 'yesterday' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="yesterday">Yesterday</label>

                <input type="radio" class="btn-check" name="date_filter" value="this_week" id="this_week" 
                  <?= $date_filter == 'this_week' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="this_week">This Week</label>

                <input type="radio" class="btn-check" name="date_filter" value="this_month" id="this_month" 
                  <?= $date_filter == 'this_month' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="this_month">This Month</label>

                <input type="radio" class="btn-check" name="date_filter" value="custom" id="custom" 
                  <?= $date_filter == 'custom' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="custom">Custom</label>
              </div>
            </div>
            
            <div class="col-md-6 custom-date-fields" id="customDateFields" 
              style="<?= $date_filter == 'custom' ? '' : 'display: none;' ?>">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label">Start Date</label>
                  <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">End Date</label>
                  <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                </div>
              </div>
            </div>
            
            <div class="col-12 mt-3">
              <button type="submit" class="btn btn-primary" name="apply_filter">
                <i class="fas fa-search me-2"></i>Apply Filter
              </button>
              <button type="submit" name="download_pdf" class="btn download-btn ms-2">
                <i class="fas fa-download me-2"></i>Download PDF
              </button>
            </div>
          </form>
        </div>
        
        <div class="col-md-4">
          <div class="stats-card">
            <h3><?= $total_properties ?></h3>
            <p>Total Properties Found</p>
            <?php if ($date_filter != 'all'): ?>
              <small>Filtered by: <?= ucfirst(str_replace('_', ' ', $date_filter)) ?></small>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Properties Table -->
    <div class="properties-table-container">
      <div class="table-header">
        <h4><i class="fas fa-building me-2"></i>Properties List</h4>
        <form method="POST" class="d-inline">
          <input type="hidden" name="date_filter" value="<?= $date_filter ?>">
          <input type="hidden" name="start_date" value="<?= $start_date ?>">
          <input type="hidden" name="end_date" value="<?= $end_date ?>">
          <button type="submit" name="download_pdf" class="btn download-btn">
            <i class="fas fa-download me-2"></i>Download PDF
          </button>
        </form>
      </div>
      
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Category</th>
              <th>Status</th>
              <th>Price</th>
              <th>City</th>
              <th>Area</th>
              <th>Beds</th>
              <th>Baths</th>
              <th>Agent</th>
              <th>Created Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($properties_result) > 0): ?>
              <?php $counter = $offset + 1; ?>
              <?php while ($property = mysqli_fetch_assoc($properties_result)): ?>
                <tr>
                  <td><?= $counter ?></td>
                  <td>
                    <span class="text-truncate" title="<?= htmlspecialchars($property['title']) ?>">
                      <?= htmlspecialchars(truncateText($property['title'], 25)) ?>
                    </span>
                    <?php if (strlen($property['title']) > 25): ?>
                      <a href="#" class="read-more" data-bs-toggle="modal" data-bs-target="#propertyModal<?= $property['id'] ?>">Read more</a>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($property['category_name']) ?></td>
                  <td>
                    <span class="badge <?= $property['status'] == 'Available' ? 'status-available' : 'status-not-available' ?>">
                      <?= $property['status'] ?>
                    </span>
                  </td>
                  <td class="price-tag"><?= formatPrice($property['price']) ?></td>
                  <td><?= htmlspecialchars($property['city']) ?></td>
                  <td><?= $property['area'] ?> sqft</td>
                  <td><?= $property['bedrooms'] ?></td>
                  <td><?= $property['bathrooms'] ?></td>
                  <td><?= htmlspecialchars($property['agent_name']) ?></td>
                  <td>
                    <span class="badge bg-secondary">
                      <?= date('M j, Y g:i A', strtotime($property['created_at'])) ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#propertyModal<?= $property['id'] ?>">
                      <i class="fas fa-eye"></i> View
                    </button>
                  </td>
                </tr>
                
                <!-- Property Details Modal -->
                <div class="modal fade" id="propertyModal<?= $property['id'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Property Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6">
                            <p><strong>Property ID:</strong><br><?= $property['id'] ?></p>
                            <p><strong>Title:</strong><br><?= htmlspecialchars($property['title']) ?></p>
                            <p><strong>Category:</strong><br><?= htmlspecialchars($property['category_name']) ?></p>
                            <p><strong>Status:</strong><br>
                              <span class="badge <?= $property['status'] == 'Available' ? 'status-available' : 'status-not-available' ?>">
                                <?= $property['status'] ?>
                              </span>
                            </p>
                            <p><strong>Price:</strong><br><?= formatPrice($property['price']) ?></p>
                            <p><strong>Address:</strong><br><?= htmlspecialchars($property['address']) ?></p>
                          </div>
                          <div class="col-md-6">
                            <p><strong>City:</strong><br><?= htmlspecialchars($property['city']) ?></p>
                            <p><strong>Pincode:</strong><br><?= htmlspecialchars($property['pincode']) ?></p>
                            <p><strong>Area:</strong><br><?= $property['area'] ?> sqft</p>
                            <p><strong>Bedrooms:</strong><br><?= $property['bedrooms'] ?></p>
                            <p><strong>Bathrooms:</strong><br><?= $property['bathrooms'] ?></p>
                            <p><strong>Agent:</strong><br><?= htmlspecialchars($property['agent_name']) ?></p>
                            <p><strong>Created:</strong><br><?= date('M j, Y g:i A', strtotime($property['created_at'])) ?></p>
                          </div>
                        </div>
                        <?php if (!empty($property['description'])): ?>
                          <div class="row mt-3">
                            <div class="col-12">
                              <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($property['description'])) ?></p>
                            </div>
                          </div>
                        <?php endif; ?>
                        <?php if (!empty($property['image'])): ?>
                          <div class="row mt-3">
                            <div class="col-12">
                              <p><strong>Image:</strong></p>
                              <img src="/REAL_ESTATE/UserInterface/Admin/<?= htmlspecialchars($property['image']) ?>" 
                                   alt="Property Image" class="img-fluid rounded" 
                                   onerror="this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'">
                            </div>
                          </div>
                        <?php endif; ?>
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
                <td colspan="13" class="text-center py-4">
                  <i class="fas fa-building fa-3x text-muted mb-3"></i>
                  <h5>No properties found</h5>
                  <p class="text-muted">No properties match your current filter criteria.</p>
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
                <a class="page-link" href="?page=<?= $page - 1 ?>&date_filter=<?= $date_filter ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">
                  Previous
                </a>
              </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&date_filter=<?= $date_filter ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?>&date_filter=<?= $date_filter ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">
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
  
  // Show/hide custom date fields
  document.querySelectorAll('input[name="date_filter"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const customFields = document.getElementById('customDateFields');
      if (this.value === 'custom') {
        customFields.style.display = 'block';
      } else {
        customFields.style.display = 'none';
      }
    });
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