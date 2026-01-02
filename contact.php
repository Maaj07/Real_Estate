<?php
  session_start();
  if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true)
  {
    header("location: /REAL_ESTATE/UserInterface/login.php");
    exit;
  }
  ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/REAL_ESTATE/UserInterface/Img/logo.jpg" type="image/X-icon">

    <title>Contact - <?php $_SESSION['username']?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="/REAL_ESTATE/CSS/style.css">
  </head>
  <body>
    
    <?php require 'Partials/_nav.php'?>

 <!-- Header Section -->
  <div class="contact-header">
    <h5>BE IN TOUCH</h5>
    <h1 class="fw-bold">Contact</h1>
  </div>

 <!-- Contact Info Section -->
  <div class="container contact-section">
    <div class="row text-center text-md-start align-items-center">
      <div class="col-md-3 mb-4 mb-md-0">
        <div class="office-title">Our Office</div>
      </div>
      <div class="col-md-3 mb-4 mb-md-0">
        <p class="mb-0">500 Terry Francine Street</p>
        <p>San Francisco, CA 94158</p>
      </div>
      <div class="col-md-3 mb-4 mb-md-0">
        <p class="mb-0">Tel: 123-456-7890</p>
        <p>Fax: 123-456-7890</p>
      </div>
      <div class="col-md-3">
        <p class="mb-0">info@3brotherrealestate.com</p>
      </div>
    </div>
  </div>


<!--Map Section-->

<div class="container-fluid px-0">
<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d117590.64861484822!2d72.43595220829823!3d22.924147179362556!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1754226880191!5m2!1sen!2sin"
     width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>


<!--Information-->

<div class="text">
<h5 class="text-uppercase text-center mt-5" style="letter-spacing: 4px;">Get In Touch</h5>
<p class="text-center mx-auto" style="max-width: 600px;">
  We'd love to hear from you! Whether you have a question about features, pricing, or anything else, our team is ready to help. Reach out to us and we’ll respond as soon as we can.
</p>
</div>



<!-- Footer Section -->

<?php require 'Partials/_footer.php'?>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    </body>
</html>


<style>
     .contact-header {
        background: linear-gradient(rgba(15, 32, 50, 0.8), rgba(15, 32, 50, 0.8)),  /* Dark blue transparent overlay */
        url("/REAL_ESTATE/UserInterface/Img/co.jpg") no-repeat center center;
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
    
</style>