<?php
session_start();
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {

  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // Check empty fields
  if (empty($email) || empty($password)) {
    echo "<script>alert('Please enter both email and password.'); history.back();</script>";
    exit;
  }

  // Email format validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Invalid email format.'); history.back();</script>";
    exit;
  }

  $email_safe = mysqli_real_escape_string($conn, $email);

  // Check user
  $sql = "SELECT * FROM users WHERE email = '$email_safe' LIMIT 1";
  $result = mysqli_query($conn, $sql);

  if ($result && mysqli_num_rows($result) === 1) {

    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (password_verify($password, $user['password'])) {

      // Session security
      session_regenerate_id(true);

      $_SESSION['email'] = $user['email'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['user_id'] = $user['user_id'];

      switch ($user['role']) {

        case "admin":
          header("Location: ../admin/admin_vendor.php");
          exit;

        case "vendor":

          $vendor_sql = "SELECT id FROM vendors WHERE email = '$email_safe' LIMIT 1";
          $vendor_res = mysqli_query($conn, $vendor_sql);

          if ($vendor_res && mysqli_num_rows($vendor_res) === 1) {
            $vendor = mysqli_fetch_assoc($vendor_res);
            $_SESSION['vendor_id'] = $vendor['id'];
            header("Location: ../Vendor/vendor.php");
            exit;
          } else {
            echo "<script>alert('Vendor account not found in vendors table.'); window.location='login.php';</script>";
            exit;
          }

        case "user":
          header("Location: ../User/user.php");
          exit;

        default:
          echo "<script>alert('Unknown user role. Contact admin.'); history.back();</script>";
          exit;
      }

    } else {
      echo "<script>alert('Incorrect password.'); history.back();</script>";
      exit;
    }

  } else {
    echo "<script>alert('Email not found.'); history.back();</script>";
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login | MotoYatra</title>
  <link rel="stylesheet" href="login.css">

  <script>
    function validateLogin(event) {

      let email = document.getElementById("email").value.trim();
      let password = document.getElementById("password").value.trim();

      // Check empty
      if (email === "" || password === "") {
        alert("Please fill in both email and password.");
        event.preventDefault();
        return;
      }

      // Email format validation (client side)
      let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        event.preventDefault();
        return;
      }
    }
  </script>

</head>

<body>

  <div class="login-container">
    <div class="login-card">

      <img src="../Necessary Image/moto_yatra.png" alt="Motoyatra Logo" class="logo">

      <h2>Welcome Back!</h2>
      <p>Enter your credentials to access your account.</p>

      <form action="login.php" method="post" onsubmit="validateLogin(event)">

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="button">Log In</button>

      </form>

      <div class="extra">
        <a href="forgot_password.php" class="forgot">Forgot Password?</a>
        <p>Don't have an account? <a href="../Register/register.php" class="signup">Sign Up</a></p>
      </div>

    </div>
  </div>

</body>

</html>