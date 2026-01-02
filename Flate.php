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

// Fetch flate properties
$flate_properties = [];
$property_query = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM properties p 
                                      JOIN categories c ON p.category_id = c.id 
                                      WHERE p.status = 'Available' AND LOWER(c.name) = 'flate' 
                                      ORDER BY p.id DESC");
if ($property_query) {
    while ($row = mysqli_fetch_assoc($property_query)) {
        $flate_properties[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title>Flate - <?php echo $_SESSION['username']?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
     .Buy-header {
        background: linear-gradient(rgba(15, 32, 50, 0.8), rgba(15, 32, 50, 0.8)),  /* Dark blue transparent overlay */
        url("/REAL_ESTATE/UserInterface/Img/Buy1.jpg") no-repeat center center;
      background-size: cover;
      color: white;
      text-align: center;
      padding: 100px 20px;
     
    }
    .Buy-header h5 {
      letter-spacing: 4px;
    }
    .Buy-section {
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
    @media (max-width: 768px) {
      .property-image img {
        height: 180px;
      }
    }
    a
    {
      text-decoration: none;
      color: inherit;
    }
</style>
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

 <!-- Header Section -->
  <div class="Buy-header">
    <h5>OUR EXCLUSIVE PROPERTIES</h5>
    <h1 class="fw-bold"><I class="text-warning">Flate</I></h1>
  </div>

  <!-- Flate Properties Section -->
  <div class="container py-5 text-center">
    <p class="text-uppercase text-muted mb-1">New Properties</p>
    <h2 class="fw-bold mb-4">Flate</h2>
    <div class="row g-4">
      <?php if(count($flate_properties) > 0): ?>
        <?php foreach($flate_properties as $property): ?>
          <div class="col-sm-6 col-lg-3">
            <div class="property-card position-relative">
              <div class="property-image">
                <img src="/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>" />
                <span class="badge-buy"><?php echo strtoupper($property['category_name']); ?></span>
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
          <p class="text-muted">No flate properties available at the moment.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <!-- Footer Section -->
  <?php require 'Partials/_footer.php'?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    </body>
</html>