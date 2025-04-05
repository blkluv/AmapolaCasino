<?php
session_start();

// Define valid credentials
$valid_username = "admin";
$valid_password = "secret123"; // change this to a secure password!

// Get credentials from POST
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === $valid_username && $password === $valid_password) {
    $_SESSION['logged_in'] = true;
    header("Location: banner-manager.php");
    exit;
} else {
    echo "Invalid login. <a href='login.php'>Try again</a>";
}
