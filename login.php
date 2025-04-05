<?php
session_start();

$credentials = json_decode(file_get_contents('credentials.json'), true);

if ($credentials === null) {
    die("Error loading credentials.");
}

$stored_username = $credentials['username'];
$stored_password_hash = $credentials['password_hash'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $stored_username && password_verify($password, $stored_password_hash)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: banner_admin.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-container {
      background-color: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      width: 300px;
      text-align: center;
    }
    h2 {
      color: #333;
      margin-bottom: 20px;
      font-size: 24px;
    }
    .error {
      color: #ff4d4d;
      margin-bottom: 20px;
      font-size: 14px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 16px;
    }
    button {
      background-color: #006847;
      color: white;
      padding: 12px 0;
      width: 100%;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #004d34;
    }

  </style>
</head>
<body>

<div class="login-container">
  <h2>Login</h2>

  <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
  </form>

</div>

</body>
</html>
