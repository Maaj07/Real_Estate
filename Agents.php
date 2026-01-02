<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
}

// ✅ Place the logic RIGHT HERE
$conn = mysqli_connect("localhost", "root", "", "real_estate");

define('ENCRYPTION_KEY', 'mysecretkey12345');
define('ENCRYPTION_METHOD', 'AES-128-CTR');

// Decrypt function
function decrypt_data($data) {
    $decoded = base64_decode($data);
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) return '';
    list($encrypted_data, $iv) = $parts;
    if (strlen($iv) !== openssl_cipher_iv_length(ENCRYPTION_METHOD)) return '';
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title>Agents - <?php $_SESSION['username']?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

 <!-- Header Section -->
  <div class="contact-header">
    <h5>OUR TEAM</h5>
    <h1 class="fw-bold">Agents (Admin)</h1>
  </div>

  <br><br>


<!--Agents Section-->

<!--Agents Section-->
<div class="container py-4">
  <div class="row justify-content-center">
    <?php 
    $result = mysqli_query($conn, "SELECT * FROM agents");

    while ($row = mysqli_fetch_assoc($result)) {
        $email = decrypt_data($row['email']);
        $phone = decrypt_data($row['phone']);
        $linkedin = decrypt_data($row['linkedin']);
        $image = "/REAL_ESTATE/Agents Img/" . $row['image'];

        echo "
        <div class='col-md-4 mb-4'>
          <div class='card p-3'>
            <h5 class='fw-bold mb-0'>{$row['name']}</h5>
            <img src='{$image}' class='card-img-top my-2' alt='{$row['name']}'>
            <p class='mb-0 fw-bold mt-2'>Email</p>
            <p class='mb-1 text-muted'>{$email}</p>
            <p class='mb-0 fw-bold'>Phone</p>
            <p class='mb-1 text-muted'>{$phone}</p>
            <a href='{$linkedin}' target='_blank' class='linkedin-link'>LinkedIn</a>
          </div>
        </div>
        ";
    }
    ?>
  </div>
</div>

<!-- Footer Section -->

<?php require 'Partials/_footer.php'?>


<style>
     .contact-header {
        background: linear-gradient(rgba(15, 32, 50, 0.8), rgba(15, 32, 50, 0.8)),  /* Dark blue transparent overlay */
        url("/REAL_ESTATE/UserInterface/Img/Ag.jpg") no-repeat center center;
      background-size: cover;
      color: white;
      text-align: center;
      padding: 100px 20px;
     
    }
    .contact-header h5 {
      letter-spacing: 4px;
    }
    .contact-section {
      padding: 50px 20px;
    }
    .office-title {
      font-weight: bold;
      font-size: 1.25rem;
    }

    .card {
      border: none;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .linkedin-link {
      color: red;
      font-weight: bold;
      text-decoration: none;
      
    }
</style>