<?php
session_start();

// Clear session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page or homepage
header("Location: home.php"); // Or another page like index.php
exit();
?>
