<?php
  session_start();
  
  setcookie("username", "", time() - 3600, "/");
  session_unset();
  session_destroy();

  // header("location: /REAL_ESTATE/UserInterface/Logout Confirmation Page.php");
  header("location: /REAL_ESTATE/UserInterface/login.php");
  exit();
?>