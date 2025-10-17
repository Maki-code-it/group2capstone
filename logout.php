<?php
// logout.php - Handles user logout
include 'config.php';

// Destroy all session variables
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>