<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true) {
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "real_estate");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get filter from URL or set default
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build SQL query based on filter
$sql = "SELECT p.*, c.name as category_name FROM properties p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'Available'";

if ($filter === 'buy') {
    $sql .= " AND c.name = 'Buy'";
} elseif ($filter === 'rent') {
    $sql .= " AND c.name = 'Rent'";
} elseif ($filter === 'flate') {
    $sql .= " AND c.name = 'Flate'";
}

$sql .= " ORDER BY p.id DESC";

// Fetch properties based on filter
$all_properties = [];
$property_query = mysqli_query($conn, $sql);
if ($property_query) {
    while ($row = mysqli_fetch_assoc($property_query)) {
        $all_properties[] = $row;
    }
}

// Get counts for each category for the filter buttons
$count_query = mysqli_query($conn, "
    SELECT c.name, COUNT(p.id) as count 
    FROM properties p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'Available' 
    GROUP BY c.name
");
$category_counts = [];
while ($row = mysqli_fetch_assoc($count_query)) {
    $category_counts[strtolower($row['name'])] = $row['count'];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title>All - <?php echo $_SESSION['username']?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
     .All-header {
        background: linear-gradient(rgba(15, 32, 50, 0.8), rgba(15, 32, 50, 0.8)),  /* Dark blue transparent overlay */
        url("/REAL_ESTATE/UserInterface/Img/All.jpg") no-repeat center center;
      background-size: cover;
      color: white;
      text-align: center;
      padding: 100px 20px;
     
    }
    .All-header h5 {
      letter-spacing: 4px;
    }
    .All-section {
      padding: 50px 20px;
    }
    
    .property-card {
      border: 1px solid #eee;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      transition: transform 0.3s ease;
      overflow: hidden;
      background-color: #fff;
      margin-bottom: 30px;
    }
    .property-card:hover {
      transform: translateY(-5px);
    }
    .property-image img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .badge-buy {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: red;
      color: white;
      font-size: 0.8rem;
      padding: 4px 10px;
      font-weight: bold;
      border-radius: 3px;
    }
    .badge-rent {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #007bff;
      color: white;
      font-size: 0.8rem;
      padding: 4px 10px;
      font-weight: bold;
      border-radius: 3px;
    }
    .badge-flate {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #28a745;
      color: white;
      font-size: 0.8rem;
      padding: 4px 10px;
      font-weight: bold;
      border-radius: 3px;
    }
    .property-body {
      padding: 15px;
      text-align: left;
    }
    .property-footer {
      display: flex;
      justify-content: space-between;
      padding: 10px 15px;
      font-size: 14px;
      border-top: 1px solid #eee;
    }
    .property-footer div {
      text-align: center;
    }
    .view-more-btn {
      background-color: red;
      color: white;
      border: none;
      padding: 10px 30px;
      font-weight: bold;
      border-radius: 3px;
      transition: background 0.3s;
    }
    .view-more-btn:hover {
      background-color: darkred;
    }
    
    /* Filter Button Styles */
    .filter-btn {
        padding: 10px 25px;
        margin: 5px;
        border: 2px solid transparent;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .filter-btn.active {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .filter-btn-all {
        background: linear-gradient(45deg, #6c757d, #495057);
        color: white;
    }
    
    .filter-btn-buy {
        background: linear-gradient(45deg, #dc3545, #c82333);
        color: white;
    }
    
    .filter-btn-rent {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }
    
    .filter-btn-flate {
        background: linear-gradient(45deg, #28a745, #1e7e34);
        color: white;
    }
    
    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .badge-count {
        background: rgba(255,255,255,0.3);
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 0.8rem;
        margin-left: 8px;
    }
    
    .no-properties {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .no-properties i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    @media (max-width: 768px) {
      .property-image img {
        height: 180px;
      }
      .filter-btn {
          padding: 8px 20px;
          font-size: 0.9rem;
      }
    }
    
    a {
      text-decoration: none;
      color: inherit;
    }
</style>
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

 <!-- Header Section -->
  <div class="All-header">
    <h5>OUR EXCLUSIVE PROPERTIES</h5>
    <h1 class="fw-bold"><I class="text-warning">ALL PROPERTIES</I></h1>
  </div>

  <!-- All Properties Section -->
  <div class="container py-5">
    <h2 class="text-center mb-4">All Available Properties</h2>
    
    <!-- Filter Buttons -->
    <div class="text-center mb-5">
        <a href="?filter=all" class="btn filter-btn filter-btn-all <?php echo $filter === 'all' ? 'active' : ''; ?>">
            All Properties 
            <span class="badge-count"><?php echo array_sum($category_counts); ?></span>
        </a>
        <a href="?filter=buy" class="btn filter-btn filter-btn-buy <?php echo $filter === 'buy' ? 'active' : ''; ?>">
            Buy 
            <span class="badge-count"><?php echo $category_counts['buy'] ?? 0; ?></span>
        </a>
        <a href="?filter=rent" class="btn filter-btn filter-btn-rent <?php echo $filter === 'rent' ? 'active' : ''; ?>">
            Rent 
            <span class="badge-count"><?php echo $category_counts['rent'] ?? 0; ?></span>
        </a>
        <a href="?filter=flate" class="btn filter-btn filter-btn-flate <?php echo $filter === 'flate' ? 'active' : ''; ?>">
            Flate 
            <span class="badge-count"><?php echo $category_counts['flate'] ?? 0; ?></span>
        </a>
    </div>

    <!-- Results Count -->
    <div class="row mb-4">
        <div class="col-12">
            <p class="text-muted">
                Showing 
                <strong><?php echo count($all_properties); ?></strong> 
                <?php echo $filter === 'all' ? 'properties' : $filter . ' properties'; ?>
            </p>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row g-4">
        <?php if(count($all_properties) > 0): ?>
            <?php foreach($all_properties as $property): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="property-card position-relative">
                        <div class="property-image">
                            <img src="/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>" />
                            <?php if(strtolower($property['category_name']) === 'buy'): ?>
                                <span class="badge-buy"><?php echo strtoupper($property['category_name']); ?></span>
                            <?php elseif(strtolower($property['category_name']) === 'rent'): ?>
                                <span class="badge-rent"><?php echo strtoupper($property['category_name']); ?></span>
                            <?php else: ?>
                                <span class="badge-flate"><?php echo strtoupper($property['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="property-body">
                        <h6 class="fw-bold mb-1"><a href="property_detail.php?id=<?php echo $property['id']; ?>"><?php echo $property['title']; ?></a></h6>
                            <p class="text-muted small mb-1"><?php echo $property['address'] . ', ' . $property['city']; ?></p>
                            <p class="fw-bold text-danger">$<?php echo number_format($property['price']); ?></p>
                        </div>
                        <div class="property-footer">
                            <div><i class="fa-solid fa-bed"></i> <br> <small>Beds</small><p class="mb-0 fw-bold"><?php echo $property['bedrooms']; ?></p></div>
                            <div><i class="fa-solid fa-bath"></i> <br> <small>Baths</small><p class="mb-0 fw-bold"><?php echo $property['bathrooms']; ?></p></div>
                            <div><i class="fa-solid fa-layer-group"></i> <br> <small>Levels</small><p class="mb-0 fw-bold"><?php echo $property['levels'] ?? '1'; ?></p></div>
                            <div><i class="fa-solid fa-square"></i> <br> <small>Sqft</small><p class="mb-0 fw-bold"><?php echo $property['area']; ?></p></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="no-properties">
                    <i class="fas fa-home"></i>
                    <h4>No Properties Found</h4>
                    <p>There are no <?php echo $filter === 'all' ? '' : $filter; ?> properties available at the moment.</p>
                    <?php if($filter !== 'all'): ?>
                        <a href="?filter=all" class="btn filter-btn filter-btn-all">View All Properties</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
  </div>

  <!-- Footer Section -->
  <?php require 'Partials/_footer.php'?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script>
        // Add smooth scrolling and active state management
        document.addEventListener('DOMContentLoaded', function() {
            // Add click effect to filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Scroll to properties section when filtering
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('filter')) {
                document.querySelector('.container.py-5').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    </body>
</html>