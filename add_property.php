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

// Fetch agents for dropdown
$agents = [];
$agent_query = mysqli_query($conn, "SELECT id, name FROM agents");
if ($agent_query) {
    while ($row = mysqli_fetch_assoc($agent_query)) {
        $agents[] = $row;
    }
}

// Fetch categories for dropdown
$categories = [];
$category_query = mysqli_query($conn, "SELECT id, name FROM categories");
if ($category_query) {
    while ($row = mysqli_fetch_assoc($category_query)) {
        $categories[] = $row;
    }
}

$user_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$user_count_row = mysqli_fetch_assoc($user_count_result);
$total_users = $user_count_row['total_users'];

$property_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$property_count_row = mysqli_fetch_assoc($property_count_result);
$total_properties = $property_count_row['total_properties'];

// Form submission handling
$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_property'])) {
    // Retrieve form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = (int)$_POST['category'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $price = (float)$_POST['price'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $area = (int)$_POST['area'];
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $agent_id = (int)$_POST['agent'];
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_name = $new_filename;
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "Please upload an image for the property.";
    }
    
    if (empty($error)) {
        // Insert property into database
        $sql = "INSERT INTO properties (title, category_id, status, price, address, city, pincode, area, bedrooms, bathrooms, description, image, agent_id)
                VALUES ('$title', $category_id, '$status', $price, '$address', '$city', '$pincode', $area, $bedrooms, $bathrooms, '$description', '$image_name', $agent_id)";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Property added successfully!";
            // Reset form fields on success
            $_POST = array();
        } else {
            $error = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Property - Admin Dashboard</title>
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
    }

    .header button {
      background-color: #f0f0f0;
      font-size: 16px;
      font-weight: bold;
      padding: 8px 12px;
      border: 2px solid #0b1f3a;
      border-radius: 5px;
      cursor: pointer;
    }

    .header button:hover {
      background-color: #0b1f3a;
      color: white;
      border: 2px solid #f0f0f0;
    }

    .box1 {
      display: flex;
      flex-wrap: wrap;
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
    }

    .profile img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      margin-bottom: 10px;
      border: 3px solid white;
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
    }

    .menu li:hover,
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
    }

    .card {
        width: 100%;
        max-width: 250px;
        height: 130px;
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        position: relative;
        transition: transform 0.3s ease, background-color 0.3s;
        margin-bottom: 20px;
    }

    .card:hover {
        transform: scale(1.05);
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

    #sidebar {
      transition: all 0.3s ease;
    }
    
    /* Add Property Form Styles */
    .form-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      padding: 30px;
      width: 100%;
      margin-top: 20px;
    }
    
    .form-header {
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #0b1f3a;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }
    
    .form-control, .form-select {
      border: 1px solid #ced4da;
      border-radius: 5px;
      padding: 10px 15px;
      width: 100%;
      transition: border-color 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #0b1f3a;
      box-shadow: 0 0 0 0.2rem rgba(11, 31, 58, 0.25);
      outline: none;
    }
    
    .btn-primary {
      background-color: #0b1f3a;
      border-color: #0b1f3a;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-primary:hover {
      background-color: #1a3a5f;
      border-color: #1a3a5f;
      transform: translateY(-2px);
    }
    
    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-secondary:hover {
      background-color: #5a6268;
      border-color: #545b62;
      transform: translateY(-2px);
    }
    
    .alert {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .form-row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -10px;
    }
    
    .form-col {
      flex: 1;
      padding: 0 10px;
      min-width: 250px;
    }
    
    .image-preview {
      width: 200px;
      height: 150px;
      border: 2px dashed #ced4da;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 10px;
      overflow: hidden;
      background-color: #f8f9fa;
    }
    
    .image-preview img {
      max-width: 100%;
      max-height: 100%;
      display: none;
    }
    
    .required:after {
      content: " *";
      color: #dc3545;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .box1 {
        flex-direction: column;
      }

      .box2 {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-around;
      }

      .main1 {
        padding: 15px;
        justify-content: center;
      }

      #sidebar {
        position: absolute;
        left: -100%;
        top: 80px;
        width: 220px;
        height: calc(100% - 80px);
        z-index: 999;
      }

      #sidebar.active {
        left: 0;
      }
      
      .form-col {
        flex: 100%;
      }
    }
  </style>
</head>
<body>

<div class="header">
  <div class="d-flex align-items-center">
    <button class="btn btn-light me-3 d-md-none" type="button" id="toggleSidebar">
      <i class="fas fa-bars"></i>
    </button>
    <h4>Welcome Admin - <?php echo $_SESSION['username']; ?></h4>
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
    <!-- Dashboard Cards -->
    <div class="card">
      <i class="fa-solid fa-building card-icon"></i>
      <h3>Total Properties</h3>
      <p><?php echo $total_properties; ?></p>
    </div>
    <div class="card">
      <i class="fa-solid fa-user-tie card-icon"></i>
      <h3>Total Agents</h3>
      <p><?php echo count($agents); ?></p>
    </div>
    <div class="card">
        <i class="fa fa-users card-icon"></i>
        <h3>Registered Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>
    <div class="card">
        <i class="fa-solid fa-house-circle-check card-icon"></i>
        <h3>Categories</h3>
        <p><?php echo count($categories); ?></p>
    </div>
    
    <!-- Add Property Form -->
    <div class="form-container">
      <div class="form-header">
        <h2>Add New Property</h2>
        <p class="text-muted">Fill in all required fields to add a new property</p>
      </div>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="title" class="form-label required">Property Title</label>
              <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="category" class="form-label required">Property Category</label>
              <select class="form-select" id="category" name="category" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="status" class="form-label required">Status</label>
              <select class="form-select" id="status" name="status" required>
                <option value="">Select Status</option>
                <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Not Available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Not Available') ? 'selected' : ''; ?>>Not Available</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="price" class="form-label required">Price ($)</label>
              <input type="number" class="form-control" id="price" name="price" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" min="0" step="0.01" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label for="address" class="form-label required">Address</label>
              <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="city" class="form-label required">City</label>
              <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($_POST['city']) ? $_POST['city'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="pincode" class="form-label required">Pin Code</label>
              <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo isset($_POST['pincode']) ? $_POST['pincode'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="area" class="form-label required">Area (sq. ft)</label>
              <input type="number" class="form-control" id="area" name="area" value="<?php echo isset($_POST['area']) ? $_POST['area'] : ''; ?>" min="0" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label for="bedrooms" class="form-label required">Bedrooms</label>
              <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="<?php echo isset($_POST['bedrooms']) ? $_POST['bedrooms'] : ''; ?>" min="0" required>
            </div>
            
            <div class="form-group">
              <label for="bathrooms" class="form-label required">Bathrooms</label>
              <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="<?php echo isset($_POST['bathrooms']) ? $_POST['bathrooms'] : ''; ?>" min="0" required>
            </div>
            
            <div class="form-group">
              <label for="agent" class="form-label required">Agent</label>
              <select class="form-select" id="agent" name="agent" required>
                <option value="">Select Agent</option>
                <?php foreach ($agents as $agent): ?>
                  <option value="<?php echo $agent['id']; ?>" <?php echo (isset($_POST['agent']) && $_POST['agent'] == $agent['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($agent['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="image" class="form-label required">Property Image</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*" required onchange="previewImage(event)">
              <div class="image-preview">
                <img id="imagePreview" src="#" alt="Image preview">
                <span id="previewText">Image Preview</span>
              </div> 
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="description" class="form-label required">Description</label>
          <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
          <a href="property_list.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Property List
          </a>
          <button type="submit" name="add_property" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Property
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Toggle sidebar on mobile
  document.getElementById('toggleSidebar').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('active');
  });
  
  // Image preview function
  function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    const previewText = document.getElementById('previewText');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        previewText.style.display = 'none';
      }
      
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>