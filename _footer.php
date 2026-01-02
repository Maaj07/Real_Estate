<!-- Footer Section
<footer class="bg text-white mt-5" style="background-color: #0b1f3a;">
      <div id="_contact" class="container my-5">
        <div class="row justify-content-center">
          <div class="col-lg-8"><br><br>
            <h3 class="mb-4 text-center">Contact Us</h3>
            <form action="/REAL_ESTATE/UserInterface/welcome.php" method="POST" class="bg text-white p-4 rounded">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="firstName" class="form-label">First Name *</label>
                  <input type="text" class="form-control" id="firstName" name="firstName" required>
                </div>
                <div class="col-md-6">
                  <label for="lastName" class="form-label">Last Name *</label>
                  <input type="text" class="form-control" id="lastName" name="lastName" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label class="form-label d-block">Interested in</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="interest" id="buy" value="Buy" required>
                  <label class="form-check-label" for="buy">Buy</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="interest" id="rent" value="Rent">
                  <label class="form-check-label" for="rent">Rent</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="interest" id="other" value="Other">
                  <label class="form-check-label" for="other">Other</label>
                </div>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4"></textarea>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-danger px-4">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="container py-4">
        <div class="row">
          <div class="col-md-4 mb-3">
            <h5>About Us</h5>
            <p>3 Brother Real Estate is committed to helping you find your dream home. With years of experience and a dedicated team, we offer the best properties and customer service in the region.</p>
          </div>
          <div class="col-md-4 mb-3">
            <h5>Quick Links</h5>
            <ul class="list-unstyled">
              <li><a href="/REAL_ESTATE/UserInterface/welcome.php" class="text-white text-decoration-none">Home</a></li>
              <li><a href="/REAL_ESTATE/UserInterface/All.php" class="text-white text-decoration-none">Properties</a></li>
              <li><a href="/REAL_ESTATE/UserInterface/Agents.php" class="text-white text-decoration-none">Agents</a></li>
              <li><a href="/REAL_ESTATE/UserInterface/contact.php" class="text-white text-decoration-none">Contact Us</a></li>
            </ul>
          </div>
      
          <div class="col-md-4 mb-3">
            <h5>Contact Info</h5>
            <ul class="list-unstyled">
              <li><i class="bi bi-geo-alt"></i> 123 Juhapura, Ahmedabad</li>
              <li><i class="bi bi-telephone"></i> +1 (555) 123-4567</li>
              <li><i class="bi bi-envelope"></i> info@3brotherrealestate.com</li>
            </ul>
            <div class="mt-2">
              <a href="https://www.facebook.com/threebrotherrealestate" class="text-white me-2" target="_blank"><i class="bi bi-facebook"></i></a>
              <a href="https://twitter.com/threebrotherrealestate" class="text-white me-2" target="_blank"><i class="bi bi-twitter"></i></a>
              <a href="https://www.instagram.com/three_brother_real_estate?igsh=dDM0ZnltcHIwd25m" class="text-white" target="_blank"><i class="bi bi-instagram"></i></a>
              <a href="https://www.linkedin.com/company/threebrotherrealestate" class="text-white" target="_blank"><i class="bi bi-linkedin"></i></a>
            </div>
          </div>
        </div>
        <hr class="bg-light">
        <div class="text-center">
          &copy; <?php echo date("Y"); ?> 3 Brother Real Estate. All rights reserved.
        </div>
      </div>
    </footer> -->


<?php
// Initialize variables
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['firstName'])) {
    // Admin email where data will be sent
    $admin_email = "mansurizaid663@gmail.com"; // 👉 Change this to your admin email

    // Collect and sanitize form data
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $interest = htmlspecialchars(trim($_POST['interest']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please enter a valid email address.";
    } else {
        // Create full name
        $fullName = $firstName . " " . $lastName;

        // Email subject and body
        $mail_subject = "New Contact Form Submission from 3 Brother Real Estate";
        $mail_body = "
You have received a new message from your website contact form:

Name: $fullName
Email: $email
Interested in: $interest

Message:
$message

---
This email was sent from the 3 Brother Real Estate contact form.
        ";

        // Headers
        $headers = "From: 3 Brother Real Estate <noreply@3brotherrealestate.com>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Send email
        if (mail($admin_email, $mail_subject, $mail_body, $headers)) {
            $success = "Thank you, $firstName! Your message has been sent successfully. We'll get back to you soon.";
            
            // Optional: Clear form data after successful submission
            $firstName = $lastName = $email = $interest = $message = "";
        } else {
            $error = "Sorry, your message could not be sent. Please try again later or contact us directly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3 Brother Real Estate - Contact</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
<!-- Footer Section -->
<footer class="bg text-white mt-5" style="background-color: #0b1f3a;">
    <div id="_contact" class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8"><br><br>
                <h3 class="mb-4 text-center">Contact Us</h3>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="bg text-white p-4 rounded">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo isset($firstName) ? $firstName : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo isset($lastName) ? $lastName : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Interested in</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="interest" id="buy" value="Buy" <?php echo (isset($interest) && $interest == 'Buy') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="buy">Buy</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="interest" id="rent" value="Rent" <?php echo (isset($interest) && $interest == 'Rent') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="rent">Rent</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="interest" id="other" value="Other" <?php echo (isset($interest) && $interest == 'Other') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="other">Other</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4"><?php echo isset($message) ? $message : ''; ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-danger px-4">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>About Us</h5>
                <p>3 Brother Real Estate is committed to helping you find your dream home. With years of experience and a dedicated team, we offer the best properties and customer service in the region.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/REAL_ESTATE/UserInterface/welcome.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="/REAL_ESTATE/UserInterface/All.php" class="text-white text-decoration-none">Properties</a></li>
                    <li><a href="/REAL_ESTATE/UserInterface/Agents.php" class="text-white text-decoration-none">Agents</a></li>
                    <li><a href="/REAL_ESTATE/UserInterface/contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contact Info</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-geo-alt"></i> 123 Juhapura, Ahmedabad</li>
                    <li><i class="bi bi-telephone"></i> +1 (555) 123-4567</li>
                    <li><i class="bi bi-envelope"></i> info@3brotherrealestate.com</li>
                </ul>
                <div class="mt-2">
                    <a href="https://www.facebook.com/threebrotherrealestate" class="text-white me-2" target="_blank"><i class="bi bi-facebook"></i></a>
                    <a href="https://twitter.com/threebrotherrealestate" class="text-white me-2" target="_blank"><i class="bi bi-twitter"></i></a>
                    <a href="https://www.instagram.com/three_brother_real_estate?igsh=dDM0ZnltcHIwd25m" class="text-white" target="_blank"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.linkedin.com/company/threebrotherrealestate" class="text-white" target="_blank"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-light">
        <div class="text-center">
            &copy; <?php echo date("Y"); ?> 3 Brother Real Estate. All rights reserved.
        </div>
    </div>
</footer>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>

