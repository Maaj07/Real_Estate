<?php
// Session and access control
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

// Encryption setup
define('ENCRYPTION_KEY', 'mysecretkey12345');
define('ENCRYPTION_METHOD', 'AES-128-CTR');

// Encryption function
function encrypt_data($data) {
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Decryption function
function decrypt_data($data) {
    $decoded = base64_decode($data);
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) return '';
    list($encrypted_data, $iv) = $parts;
    if (strlen($iv) !== openssl_cipher_iv_length(ENCRYPTION_METHOD)) return '';
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

// Fetch agent data
$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM agents WHERE id=$id");
$agent = mysqli_fetch_assoc($result);

if (!$agent) {
    echo "Agent not found.";
    exit();
}

// Update agent
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = encrypt_data($_POST['email']);
    $phone = encrypt_data($_POST['phone']);
    $linkedin = encrypt_data($_POST['linkedin']);

    // Check for new image upload
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "../Agents Img/" . $image);
        $query = "UPDATE agents SET name='$name', email='$email', phone='$phone', linkedin='$linkedin', image='$image' WHERE id=$id";
    } else {
        $query = "UPDATE agents SET name='$name', email='$email', phone='$phone', linkedin='$linkedin' WHERE id=$id";
    }

    mysqli_query($conn, $query);
    header("Location: agents_list.php");
    exit();
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
$total_users = $user_count_row['total_users'];

$property_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_properties FROM properties");
$property_count_row = mysqli_fetch_assoc($property_count_result);
$total_properties = $property_count_row['total_properties'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Agent - Admin Dashboard</title>
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
        
        /* Edit Agent Form Styles */
        .edit-agent-container {
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
        
        .agent-form {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .agent-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #0b1f3a;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .agent-info {
            flex: 1;
        }
        
        .agent-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0b1f3a;
            margin-bottom: 5px;
        }
        
        .agent-id {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            font-weight: 600;
            color: #0b1f3a;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #0b1f3a;
            box-shadow: 0 0 0 3px rgba(11, 31, 58, 0.1);
        }
        
        .image-preview-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }
        
        .current-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-success {
            background: #28a745;
            border: none;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            
            .form-grid {
                grid-template-columns: 1fr;
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
            
            .form-header {
                flex-direction: column;
                text-align: center;
            }
            
            .agent-avatar-large {
                width: 100px;
                height: 100px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
            
            .edit-agent-container {
                padding: 20px;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .image-preview-container {
                flex-direction: column;
                align-items: flex-start;
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
        
        .form-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .encryption-badge {
            display: inline-flex;
            align-items: center;
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 10px;
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
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
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
        
        <div class="edit-agent-container">
            <h2 class="section-title">Edit Agent Information</h2>
            
            <div class="agent-form">
                <div class="form-header">
                    <img src="/REAL_ESTATE/Agents Img/<?= htmlspecialchars($agent['image']) ?>" 
                         class="agent-avatar-large" 
                         alt="<?= htmlspecialchars($agent['name']) ?>">
                    <div class="agent-info">
                        <div class="agent-name"><?= htmlspecialchars($agent['name']) ?></div>
                        <div class="agent-id">Agent ID: #<?= $agent['id'] ?></div>
                        <div class="form-note mt-2">
                            <i class="fas fa-shield-alt me-1"></i>All sensitive data is encrypted for security
                        </div>
                    </div>
                </div>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($agent['name']) ?>" 
                                   class="form-control" required placeholder="Enter agent's full name">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Email Address <span class="text-danger">*</span>
                                <span class="encryption-badge"><i class="fas fa-lock me-1"></i>Encrypted</span>
                            </label>
                            <input type="email" name="email" value="<?= htmlspecialchars(decrypt_data($agent['email'])) ?>" 
                                   class="form-control" required placeholder="agent@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Phone Number <span class="text-danger">*</span>
                                <span class="encryption-badge"><i class="fas fa-lock me-1"></i>Encrypted</span>
                            </label>
                            <input type="text" name="phone" value="<?= htmlspecialchars(decrypt_data($agent['phone'])) ?>" 
                                   class="form-control" required placeholder="+1 (555) 123-4567">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                LinkedIn Profile
                                <span class="encryption-badge"><i class="fas fa-lock me-1"></i>Encrypted</span>
                            </label>
                            <input type="url" name="linkedin" value="<?= htmlspecialchars(decrypt_data($agent['linkedin'])) ?>" 
                                   class="form-control" placeholder="https://linkedin.com/in/username">
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Profile Image</label>
                            <div class="image-preview-container">
                                <div>
                                    <div class="form-label">Current Image:</div>
                                    <img src="/REAL_ESTATE/Agents Img/<?= htmlspecialchars($agent['image']) ?>" 
                                         class="current-image" 
                                         alt="Current Agent Image">
                                </div>
                                <div style="flex: 1;">
                                    <div class="form-label">Upload New Image (optional):</div>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-note">
                                        Recommended: Square image, 300x300 pixels or larger. JPG, PNG formats accepted.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="agents_list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Agent List
                        </a>
                        <button type="submit" name="update" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Update Agent
                        </button>
                    </div>
                </form>
            </div>
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
        
        // Image preview functionality
        const imageInput = document.querySelector('input[name="image"]');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Create a preview of the new image
                        const previewContainer = document.querySelector('.image-preview-container');
                        const newImagePreview = document.createElement('div');
                        newImagePreview.innerHTML = `
                            <div>
                                <div class="form-label">New Image Preview:</div>
                                <img src="${e.target.result}" class="current-image" alt="New Agent Image Preview">
                            </div>
                        `;
                        // Remove existing new image preview if it exists
                        const existingNewPreview = previewContainer.querySelector('.new-image-preview');
                        if (existingNewPreview) {
                            existingNewPreview.remove();
                        }
                        newImagePreview.classList.add('new-image-preview');
                        previewContainer.appendChild(newImagePreview);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            const phone = document.querySelector('input[name="phone"]').value;
            
            // Basic email validation
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
            
            // Basic phone validation (at least 10 digits)
            const phoneDigits = phone.replace(/\D/g, '');
            if (phoneDigits.length < 10) {
                e.preventDefault();
                alert('Please enter a valid phone number with at least 10 digits.');
                return;
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>