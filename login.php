<?php
session_start(); // Start the session

// Include the configuration file where the hashed password is stored
include('config.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate login: compare the entered password with the stored hashed password
    if ($username == $admin_username && password_verify($password, $admin_password_hash)) {
        // Store the session variable to indicate the user is logged in
        $_SESSION['logged_in'] = true;
        header('Location: banner_admin.php'); // Redirect to the admin page
        exit;
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

    <h2>Login to Banner Admin</h2>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <?php
    if (isset($error_message)) {
        echo "<p style='color: red;'>$error_message</p>";
    }
    ?>

</body>
</html>
