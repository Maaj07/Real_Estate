<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true) {
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
}

// Set a cookie for the logged-in user (valid for 1 hour)
if (isset($_SESSION['username'])) {
    setcookie('user', $_SESSION['username'], time() + 3600, "/");
}

include 'Partials/_dbconnect.php';

// Fetch properties for display
$properties = [];
$property_query = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM properties p 
                                      JOIN categories c ON p.category_id = c.id 
                                      WHERE p.status = 'Available' 
                                      ORDER BY p.id DESC");
if ($property_query) {
    while ($row = mysqli_fetch_assoc($property_query)) {
        $properties[] = $row;
    }
}

// Separate properties into buy, rent, and flate
$buy_properties = array_filter($properties, function($prop) {
    return strtolower($prop['category_name']) === 'buy';
});

$rent_properties = array_filter($properties, function($prop) {
    return strtolower($prop['category_name']) === 'rent';
});

$flate_properties = array_filter($properties, function($prop) {
    return strtolower($prop['category_name']) === 'flate';
});

// Reset array indices
$buy_properties = array_values($buy_properties);
$rent_properties = array_values($rent_properties);
$flate_properties = array_values($flate_properties);

// Take only 4 of each for display (or all if less than 4)
$buy_display = array_slice($buy_properties, 0, 4);
$rent_display = array_slice($rent_properties, 0, 4);
$flate_display = array_slice($flate_properties, 0, 4);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title>Welcome - <?php echo $_SESSION['username']?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .hero-section {
          position: relative;
          width: 100%;
          min-height: 60vh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: url('/REAL_ESTATE/UserInterface/Img/House.jpg') center center/cover no-repeat;
          }
          .hero-overlay {
          position: absolute;
          top: 0; left: 0; right: 0; bottom: 0;
          background: rgba(0,0,0,0.45);
          z-index: 1;
          }
          .hero-content {
          position: relative;
          z-index: 2;
          text-align: center;
          color: #fff;
          padding: 40px 20px;
          width: 100%;
          max-width: 700px;
          margin: 0 auto;
          }
          .hero-content h1 {
          font-size: 3rem;
          margin-bottom: 18px;
          letter-spacing: 2px;
          text-shadow: 0 2px 10px rgba(0,0,0,0.2);
          }
          .hero-content .sub-heading {
          font-size: 1.3rem;
          margin-bottom: 30px;
          font-weight: 400;
          letter-spacing: 1px;
          text-shadow: 0 1px 6px rgba(0,0,0,0.15);
          }
          .hero-btn {
          background: red;
          color: #fff;
          border: none;
          padding: 12px 36px;
          font-size: 1.1rem;
          font-weight: bold;
          border-radius: 4px;
          transition: background 0.3s, transform 0.3s;
          box-shadow: 0 2px 8px rgba(0,0,0,0.12);
          }
          .hero-btn a {
          color: #fff;
          text-decoration: none;
          }
          .hero-btn:hover {
          background: darkred;
          transform: scale(1.05);
          }
          @media (max-width: 768px) {
          .hero-content h1 {
            font-size: 2.1rem;
          }
          .hero-content .sub-heading {
            font-size: 1rem;
          }
          .hero-btn {
            padding: 10px 24px;
            font-size: 1rem;
          }
          .hero-section {
            min-height: 40vh;
          }
          }
          @media (max-width: 480px) {
          .hero-content h1 {
            font-size: 1.3rem;
          }
          .hero-content .sub-heading {
            font-size: 0.9rem;
          }
          .hero-btn {
            padding: 8px 16px;
            font-size: 0.95rem;
          }
          }
        .property-card {
          border: 1px solid #eee;
          border-radius: 5px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.08);
          transition: transform 0.3s ease;
          overflow: hidden;
          background-color: #fff;
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
        .contact-section {
          text-align: center;
          padding: 60px 20px;
        }
        .contact-section h6 {
          text-transform: uppercase;
          letter-spacing: 3px;
          font-weight: 400;
          color: #333;
          margin-bottom: 40px;
        }
        .contact-icon {
          font-size: 48px;
          color: red;
          margin-bottom: 15px;
        }
        .contact-box h5 {
          font-weight: bold;
          margin-bottom: 8px;
        }
        .contact-box p {
          margin: 0;
        }
        .section {
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                width: 100%;
                height: 75vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                color: white;
            }
        .section1{
          background-image: url("/REAL_ESTATE/UserInterface/Img/Back.jpg"); 
          }
        @media (max-width: 768px) {
          .property-image img {
            height: 180px;
          }
        }
        .hero-section {
      background-image: url("/REAL_ESTATE/UserInterface/Img/House.jpg"); /* Replace with your image filename */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      position: relative;
    }

    .hero-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4); /* Dark overlay */
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .hero-btn {
      background-color: red;
      color: white;
      border: none;
      padding: 10px 25px;
      font-weight: bold;
      transition: 0.3s;
    }

    .hero-btn:hover {
      background-color: darkred;
    }

    .sub-heading {
      letter-spacing: 4px;
      margin-bottom: 20px;
    }
         .section-heading {
      text-align: center;
      margin-top: 40px;
      margin-bottom: 40px;
      font-size: 18px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #555;
    }

    .option-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      max-width: 1200px;
      margin: 0 auto;
    }

    .option-box {
      position: relative;
      width: 100%;
      height: 400px;
      overflow: hidden;
    }

    @media (min-width: 768px) {
      .option-box {
        width: 50%;
      }
    }

    .option-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .option-box:hover img {
      transform: scale(1.05);
    }

    .option-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 60px;
      font-weight: 700;
      color: white;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
      text-align: center;
      width: 100%;
    }
    a
    {
      text-decoration: none;
      color: inherit;
    }
    a:hover{
      color: red;
      
    }
    </style>
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="hero-content animate__animated animate__fadeInDown">
        <h1 class="display-4 fw-bold">New Properties</h1>
        <p class="sub-heading">EXCLUSIVELY BY 3 Brother</p>
        <!-- <button class="hero-btn animate__animated animate__pulse animate__delay-1s"><a href="All.php">Explore</a></button> -->
        <button class="hero-btn animate__animated animate__pulse animate__delay-1s" onclick="this.textContent='Loading...';this.disabled=true;setTimeout(()=>window.location.href='All.php',3000)">Explore</button>
      </div>
    </section>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<!-- Heading Section -->
  <div class="section-heading">What are you looking for?</div>
    <div class="option-container">
      <!-- Buy Option -->
        <div class="option-box">
          <img src="/REAL_ESTATE/UserInterface/Img/Buy.jpg" alt="Buy a House">
            <div class="option-text">
              <a href="/REAL_ESTATE/UserInterface/Buy.php">Buy</a>
            </div>
        </div>

      <!-- Rent Option -->
        <div class="option-box">
          <img src="/REAL_ESTATE/UserInterface/Img/Rent.jpg" alt="Rent a House">
            <div class="option-text">
              <a href="/REAL_ESTATE/UserInterface/Rent.php">Rent</a>
            </div>
        </div>
  </div>
  <hr class="my-4" style="border: 1px solid #eee;">

<!-- Buy Properties Section -->
<div class="container py-5 text-center">
  <p class="text-uppercase text-muted mb-1">New Properties</p>
  <h2 class="fw-bold mb-4">For Buy</h2>
  <div class="row g-4">
    <?php if(count($buy_display) > 0): ?>
      <?php foreach($buy_display as $property): ?>
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
        <p class="text-muted">No properties for sale at the moment.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if(count($buy_properties) > 4): ?>
    <button class="view-more-btn mt-4" onclick="this.textContent='Loading...';this.disabled=true;setTimeout(()=>window.location.href='Buy.php',3000)">View More</button>
  <?php endif; ?>
</div>

<hr class="my-4" style="border: 1px solid #eee;">

<!-- Rent Properties Section -->
<div class="container py-5 text-center">
  <p class="text-uppercase text-muted mb-1">New Properties</p>
  <h2 class="fw-bold mb-4">For Rent</h2>
  <div class="row g-4">
    <?php if(count($rent_display) > 0): ?>
      <?php foreach($rent_display as $property): ?>
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
        <p class="text-muted">No properties for rent at the moment.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if(count($rent_properties) > 4): ?>
    <button class="view-more-btn mt-4" onclick="this.textContent='Loading...';this.disabled=true;setTimeout(()=>window.location.href='Rent.php',3000)">View More</button>
  <?php endif; ?>
</div>


  <!-- Flate Properties Section -->
  <div class="container py-5 text-center">
    <p class="text-uppercase text-muted mb-1">New Properties</p>
    <h2 class="fw-bold mb-4">Flate</h2>
    <div class="row g-4">
      <?php if(count($flate_display) > 0): ?>
        <?php foreach($flate_display as $property): ?>
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

    <?php if(count($flate_properties) > 4): ?>
      <button class="view-more-btn mt-4" onclick="this.textContent='Loading...';this.disabled=true;setTimeout(()=>window.location.href='Flate.php',3000)">View More</button>
    <?php endif; ?>
  </div>
<hr class="my-4" style="border: 1px solid #eee;">

<section class="contact-section">
  <div class="section section1">
    <!-- <img src="/REAL_ESTATE/UserInterface/Img/Back.jpg" alt="Back Img" > -->
  </div>
<br><br>
    <h6>Your Dream House is One Step Away!</h6>
    <div class="container">
      <div class="row text-center">
        <div class="col-md-4 mb-4 contact-box">
          <div class="contact-icon">
            <i class="fas fa-phone-alt"></i>
          </div>
          <h5>Call Us</h5>
          <p>Free Calls</p>
          <p>1-800-000-000</p>
        </div>
        <div class="col-md-4 mb-4 contact-box">
          <div class="contact-icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <h5>Find Us</h5>
          <p>500 Terry Francine St.</p>
          <p>San Francisco, CA 94158</p>
        </div>
        <div class="col-md-4 mb-4 contact-box">
          <div class="contact-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <h5>Email Us</h5>
          <p>Direct Email</p>
          <p> info@3brotherrealestate.com</p>
        </div>
      </div>
    </div>
  </section>

  <?php require 'Partials/_footer.php'?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    </body>
</html>