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

// Handle form submission
$form_message = '';
$form_success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_viewing'])) {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $user_phone = mysqli_real_escape_string($conn, $_POST['user_phone']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $requested_datetime = mysqli_real_escape_string($conn, $_POST['requested_datetime']);
    $property_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get agent_id from property
    $agent_query = "SELECT agent_id FROM properties WHERE id = '$property_id'";
    $agent_result = mysqli_query($conn, $agent_query);
    $agent_data = mysqli_fetch_assoc($agent_result);
    $agent_id = $agent_data['agent_id'];
    
    // Insert viewing request
    $insert_query = "INSERT INTO viewing_requests (property_id, user_name, user_email, user_phone, message, requested_datetime, agent_id) 
                     VALUES ('$property_id', '$user_name', '$user_email', '$user_phone', '$message', '$requested_datetime', '$agent_id')";
    
    if (mysqli_query($conn, $insert_query)) {
        $form_message = 'Your viewing request has been submitted successfully! We will contact you soon.';
        $form_success = true;
    } else {
        $form_message = 'Error submitting request. Please try again.';
    }
}

// Check if property ID is provided
$property_id = $_GET['id']; // Example

$query = "SELECT p.*, a.name as agent_name, a.email as agent_email, 
                 a.phone as agent_phone, a.image as agent_photo 
          FROM properties p 
          LEFT JOIN agents a ON p.agent_id = a.id 
          WHERE p.id = '$property_id'";

$result = mysqli_query($conn, $query);
$property = mysqli_fetch_assoc($result);

// Decrypt sensitive agent data
$property['agent_email'] = decrypt_data($property['agent_email']);
$property['agent_phone'] = decrypt_data($property['agent_phone']);

// Fetch property details
$property = null;
if ($property_id > 0) {
    $query = "SELECT p.*, c.name as category_name, a.name as agent_name, a.email as agent_email, a.phone as agent_phone 
              FROM properties p 
              JOIN categories c ON p.category_id = c.id 
              JOIN agents a ON p.agent_id = a.id 
              WHERE p.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $property = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

// If property not found, redirect to home
if (!$property) {
    header("location: index.php");
    exit;
}

// Fetch similar properties
$similar_properties = [];
$similar_query = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM properties p 
                                      JOIN categories c ON p.category_id = c.id 
                                      WHERE p.status = 'Available' AND p.id != $property_id 
                                      AND (p.city = '{$property['city']}' OR p.category_id = {$property['category_id']})
                                      ORDER BY p.id DESC LIMIT 4");
if ($similar_query) {
    while ($row = mysqli_fetch_assoc($similar_query)) {
        $similar_properties[] = $row;
    }
}

define('ENCRYPTION_KEY', 'mysecretkey12345');
define('ENCRYPTION_METHOD', 'AES-128-CTR');

function decrypt_data($data) {
    if (empty($data)) return '';
    $decoded = base64_decode($data);
    if ($decoded === false || strpos($decoded, '::') === false) return '';
    list($encrypted_data, $iv) = explode('::', $decoded, 2);
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title><?php echo $property['title']; ?> - 3 Brother Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
      :root {
        --primary-color: #dc3545;
        --secondary-color: #0b1f3a;
        --accent-color: #fd7e14;
        --light-bg: #f8f9fa;
        --dark-text: #333;
        --light-text: #6c757d;
        --shadow: 0 4px 12px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
      }
      
      .property-detail-hero {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $property['image']; ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
      }
      
      .property-detail-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, transparent 100%);
        opacity: 0.3;
      }
      
      .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        padding: 0 20px;
      }
      
      .property-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 50px 20px;
      }
      
      .property-main-image {
        width: 100%;
        height: 450px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: var(--shadow);
        transition: var(--transition);
      }
      
      .property-main-image:hover {
        transform: scale(1.02);
      }
      
      .property-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin: 40px 0;
      }
      
      .info-card {
        background: var(--light-bg);
        padding: 25px;
        border-radius: 12px;
        text-align: center;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 2px solid transparent;
      }
      
      .info-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
      }
      
      .info-card i {
        font-size: 32px;
        color: var(--primary-color);
        margin-bottom: 15px;
        background: white;
        width: 60px;
        height: 60px;
        line-height: 60px;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
      }
      
      .info-card h4 {
        color: var(--secondary-color);
        font-weight: 600;
        margin-bottom: 10px;
      }
      
      .info-card p {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0;
      }
      
      .detail-section {
        margin-bottom: 50px;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
      }
      
      .detail-section h3 {
        color: var(--secondary-color);
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 15px;
        margin-bottom: 25px;
        font-weight: 700;
        position: relative;
      }
      
      .detail-section h3::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 60px;
        height: 3px;
        background: var(--accent-color);
      }
      
      .detail-section p {
        color: var(--dark-text);
        line-height: 1.8;
        margin-bottom: 15px;
      }
      
      .detail-section strong {
        color: var(--secondary-color);
      }
      
      .badge {
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
      }
      
      .agent-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow);
        padding: 30px;
        text-align: center;
        transition: var(--transition);
        border: 2px solid transparent;
      }
      
      .agent-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
      }
      
      .agent-image {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 20px;
        border: 4px solid var(--primary-color);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      }
      
      .agent-card h3 {
        color: var(--secondary-color);
        margin-bottom: 20px;
        font-weight: 700;
      }
      
      .agent-card h4 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 600;
      }
      
      .agent-card p {
        color: var(--dark-text);
        margin-bottom: 10px;
      }
      
      .agent-card i {
        color: var(--primary-color);
        width: 20px;
      }
      
      .contact-form {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow);
        padding: 30px;
        margin-top: 30px;
        transition: var(--transition);
      }
      
      .contact-form:hover {
        transform: translateY(-3px);
      }
      
      .contact-form h3 {
        color: var(--secondary-color);
        margin-bottom: 25px;
        font-weight: 700;
        text-align: center;
      }
      
      .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 20px;
        transition: var(--transition);
      }
      
      .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
      }
      
      .btn {
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        transition: var(--transition);
      }
      
      .btn-danger {
        background: var(--primary-color);
        border-color: var(--primary-color);
      }
      
      .btn-danger:hover {
        background: #bb2d3b;
        border-color: #b02a37;
        transform: translateY(-2px);
      }
      
      .btn-outline-danger {
        color: var(--primary-color);
        border-color: var(--primary-color);
      }
      
      .btn-outline-danger:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-2px);
      }
      
      .alert {
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        border: none;
        font-weight: 500;
      }
      
      .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border-left: 5px solid #28a745;
      }
      
      .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 5px solid #dc3545;
      }

      /* Keep all existing CSS styles from your original code */
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
      /* Similar Properties Section */
      .similar-property-card {
          border: 1px solid #eee;
          border-radius: 10px;
          box-shadow: 0 2px 12px rgba(0,0,0,0.08);
          background: #fff;
          overflow: hidden;
          transition: transform 0.3s, box-shadow 0.3s;
          position: relative;
          min-height: 420px;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
      }
      .similar-property-card:hover {
          transform: translateY(-7px) scale(1.02);
          box-shadow: 0 6px 24px rgba(220,53,69,0.12);
          border-color: var(--primary-color);
      }
      .similar-property-card .property-image {
          position: relative;
          width: 100%;
          height: 200px;
          overflow: hidden;
          border-bottom: 1px solid #eee;
      }
      .similar-property-card .similar-property-image {
          width: 100%;
          height: 100%;
          object-fit: cover;
          transition: transform 0.3s;
      }
      .similar-property-card:hover .similar-property-image {
          transform: scale(1.05);
      }
      .similar-property-card .badge-buy {
          position: absolute;
          top: 12px;
          left: 12px;
          background: var(--primary-color);
          color: #fff;
          font-size: 0.85rem;
          padding: 6px 16px;
          border-radius: 20px;
          font-weight: 600;
          box-shadow: 0 2px 8px rgba(220,53,69,0.15);
          z-index: 2;
          letter-spacing: 1px;
      }
      .similar-property-card .property-body {
          padding: 18px 15px 10px 15px;
          flex: 1 1 auto;
      }
      .similar-property-card .property-body h6 {
          color: var(--secondary-color);
          font-size: 1.1rem;
          margin-bottom: 6px;
      }
      .similar-property-card .property-body p.text-danger {
          font-size: 1.1rem;
          margin-bottom: 0;
      }
      .similar-property-card .property-footer {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 10px 15px;
          font-size: 0.95rem;
          border-top: 1px solid #eee;
          background: #f8f9fa;
      }
      .similar-property-card .property-footer div {
          text-align: center;
          flex: 1;
      }
      .similar-property-card .property-footer i {
          color: var(--primary-color);
          font-size: 1.1rem;
          margin-bottom: 2px;
      }
      .similar-property-card .property-footer small {
          color: var(--light-text);
          font-size: 0.85rem;
      }
      .similar-property-card .p-3 {
          padding: 16px;
      }
      .similar-property-card .btn-outline-danger {
          border-radius: 6px;
          font-weight: 600;
          font-size: 0.95rem;
          transition: background 0.3s, color 0.3s;
      }
      .similar-property-card .btn-outline-danger:hover {
          background: var(--primary-color);
          color: #fff;
      }
      @media (max-width: 991px) {
          .similar-property-card {
              min-height: 390px;
          }
          .similar-property-card .property-image {
              height: 160px;
          }
      }
      @media (max-width: 767px) {
          .similar-property-card {
              min-height: 340px;
          }
          .similar-property-card .property-image {
              height: 120px;
          }
      }
    </style>
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

    <div class="property-detail-hero">
      <div class="hero-content">
        <h1 class="display-4 fw-bold"><?php echo $property['title']; ?></h1>
        <p class="sub-heading"><?php echo $property['address'] . ', ' . $property['city']; ?></p>
        <h3 class="text-danger">$<?php echo number_format($property['price']); ?></h3>
      </div>
    </div>

    <div class="property-details-container">
      <div class="row">
        <div class="col-md-8">
          <img src="/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>" class="property-main-image">
          
          <div class="detail-section">
            <h3>Property Details</h3>
            <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
          </div>
          
          <div class="property-info-grid">
            <div class="info-card">
              <i class="fa-solid fa-bed"></i>
              <h4>Bedrooms</h4>
              <p><?php echo $property['bedrooms']; ?></p>
            </div>
            
            <div class="info-card">
              <i class="fa-solid fa-bath"></i>
              <h4>Bathrooms</h4>
              <p><?php echo $property['bathrooms']; ?></p>
            </div>
            
            <div class="info-card">
              <i class="fa-solid fa-layer-group"></i>
              <h4>Levels</h4>
              <p><?php echo $property['levels'] ?? '1'; ?></p>
            </div>
            
            <div class="info-card">
              <i class="fa-solid fa-ruler-combined"></i>
              <h4>Area (sqft)</h4>
              <p><?php echo number_format($property['area']); ?></p>
            </div>
          </div>
          
          <div class="detail-section">
            <h3>Location & Information</h3>
            <p><strong>Address:</strong> <?php echo $property['address']; ?></p>
            <p><strong>City:</strong> <?php echo $property['city']; ?></p>
            <p><strong>Status:</strong> <span class="badge bg-<?php echo $property['status'] == 'Available' ? 'success' : 'secondary'; ?>"><?php echo $property['status']; ?></span></p>
            <p><strong>Category:</strong> <?php echo $property['category_name']; ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($property['price']); ?></p>
            <?php if (!empty($property['year_built'])): ?>
            <p><strong>Year Built:</strong> <?php echo $property['year_built']; ?></p>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="col-md-4"> 
          <div class="agent-card p-3 border rounded shadow-sm text-center">
            <h3 class="mb-3">Contact Agent</h3>
                    
            <?php if (!empty($property['agent_photo'])): ?>
              <img src="/REAL_ESTATE/UserInterface/Admin/Agents Img/<?php echo $property['agent_photo']; ?>" 
                   alt="<?php echo $property['agent_name']; ?>" 
                   class="agent-image rounded-circle mb-3" 
                   style="width:120px; height:120px; object-fit:cover;">
            <?php else: ?>
              <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($property['agent_name']); ?>&background=dc3545&color=fff" 
                   alt="<?php echo $property['agent_name']; ?>" 
                   class="agent-image rounded-circle mb-3" 
                   style="width:120px; height:120px; object-fit:cover;">
            <?php endif; ?>
            
            <h4><?php echo $property['agent_name']; ?></h4>
            <button class="btn btn-danger mt-3 w-100" onclick="document.getElementById('contact-form-section').scrollIntoView({ behavior: 'smooth' }); return false;">
              <i class="fas fa-paper-plane me-2"></i> Contact Now
            </button>
          </div>
          
          <div class="contact-form" id="contact-form-section">
            <h3 class="mb-3">Schedule a Viewing</h3>
            
            <?php if (!empty($form_message)): ?>
              <div class="alert <?php echo $form_success ? 'alert-success' : 'alert-danger'; ?>">
                <i class="fas <?php echo $form_success ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                <?php echo $form_message; ?>
              </div>
            <?php endif; ?>
            
            <form method="POST" action="">
              <div class="mb-3">
                <input type="text" class="form-control" name="user_name" placeholder="Your Name" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" name="user_email" placeholder="Your Email" required>
              </div>
              <div class="mb-3">
                <input type="tel" class="form-control" name="user_phone" placeholder="Your Phone">
              </div>
              <div class="mb-3">
                <textarea class="form-control" name="message" rows="3" placeholder="Message"></textarea>
              </div>
              <div class="mb-3">
                <label for="requested_datetime" class="form-label">Preferred Viewing Date & Time</label>
                <input type="datetime-local" class="form-control" name="requested_datetime" required>
              </div>
              <button type="submit" name="submit_viewing" class="btn btn-danger w-100">
                <i class="fas fa-calendar-check me-2"></i> Request Viewing
              </button>
            </form>
          </div>
        </div>
      </div>
      
      <?php if (!empty($similar_properties)): ?>
      <div class="detail-section mt-5">
        <h3>Similar Properties</h3>
        <div class="row g-4">
          <?php foreach($similar_properties as $similar): ?>
            <div class="col-sm-6 col-lg-3">
              <div class="similar-property-card">
                <div class="property-image">
                  <img src="/REAL_ESTATE/UserInterface/Admin/uploads/<?php echo $similar['image']; ?>" alt="<?php echo $similar['title']; ?>" class="similar-property-image">
                  <span class="badge-buy"><?php echo strtoupper($similar['category_name']); ?></span>
                </div>
                <div class="property-body">
                  <h6 class="fw-bold mb-1"><?php echo $similar['title']; ?></h6>
                  <p class="text-muted small mb-1"><?php echo $similar['address'] . ', ' . $similar['city']; ?></p>
                  <p class="fw-bold text-danger">$<?php echo number_format($similar['price']); ?></p>
                </div>
                <div class="property-footer">
                  <div><i class="fa-solid fa-bed"></i> <br> <small>Beds</small><p class="mb-0 fw-bold"><?php echo $similar['bedrooms']; ?></p></div>
                  <div><i class="fa-solid fa-bath"></i> <br> <small>Baths</small><p class="mb-0 fw-bold"><?php echo $similar['bathrooms']; ?></p></div>
                  <div><i class="fa-solid fa-ruler-combined"></i> <br> <small>Sqft</small><p class="mb-0 fw-bold"><?php echo $similar['area']; ?></p></div>
                </div>
                <div class="p-3">
                  <a href="property_detail.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-danger btn-sm w-100">View Details</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <section class="contact-section">
      <div class="section section1"></div>
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
    
    <script>
      // Add smooth scrolling
      document.addEventListener('DOMContentLoaded', function() {
        // Add animation to elements on scroll
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.style.opacity = 1;
              entry.target.style.transform = 'translateY(0)';
            }
          });
        }, { threshold: 0.1 });
        
        // Observe all detail sections
        document.querySelectorAll('.detail-section, .agent-card, .contact-form').forEach(el => {
          el.style.opacity = 0;
          el.style.transform = 'translateY(20px)';
          el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
          observer.observe(el);
        });
        
        // Set minimum datetime to current time
        const datetimeInput = document.querySelector('input[name="requested_datetime"]');
        if (datetimeInput) {
          const now = new Date();
          now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
          datetimeInput.min = now.toISOString().slice(0, 16);
        }
      });
    </script>
  </body>
</html>