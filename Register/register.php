<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if (!$username || !$email || !$password || !$confirm) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password)
            VALUES ('$username', '$email', '$hashed')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Account created successfully!');
                window.location.href='../Login/login.php';
              </script>";
    } else {
        if (mysqli_errno($conn) == 1062) {
            echo "Email already registered.";
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Create Account - Moto Rental</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", Arial, sans-serif;
    }

    body {
      background-color: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .register-container {
      background: #fff;
      width: 100%;
      max-width: 380px;
      padding: 2rem 2.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .register-form h2 {
      text-align: center;
      color: #222;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .form-subtext {
      text-align: center;
      color: #777;
      font-size: 0.9rem;
      line-height: 1.4;
      margin-bottom: 1.5rem;
    }

    label {
      font-size: 0.9rem;
      color: #333;
      margin-bottom: 0.3rem;
      display: block;
    }

    input,
    select {
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1rem;
      border: 1px solid #d3d3d3;
      border-radius: 5px;
      background-color: #f7f8fa;
      font-size: 0.95rem;
    }

    input:focus,
    select:focus {
      border-color: #ff7b00;
      outline: none;
      background-color: #fff;
      box-shadow: 0 0 0 2px rgba(255, 123, 0, 0.1);
    }

    .role-container {
      position: relative;
      margin-bottom: 1rem;
    }

    .role-container::after {
      font-size: 0.7rem;
      color: #777;
      position: absolute;
      right: 10px;
      top: 50%;
    }

    select.role option {
      padding: 8px;
    }

    .register-btn {
      width: 100%;
      background-color: #ff7b00;
      border: none;
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      padding: 0.7rem 0;
      border-radius: 5px;
      cursor: pointer;
    }

    .register-btn:hover {
      background-color: #e86c00;
    }

    .signin-text {
      text-align: center;
      color: #666;
      font-size: 0.9rem;
      margin-top: 1rem;
    }

    .signin-link {
      color: #ff7b00;
      font-weight: 600;
      text-decoration: none;
      margin-left: 3px;
    }

    .signin-link:hover {
      text-decoration: underline;
    }
  </style>
  <script>
    function validateRegister(event) {
      let username = document.getElementById("username").value.trim();
      let email = document.getElementById("email").value.trim();
      let password = document.getElementById("password").value.trim();
      let confirmPassword = document.getElementById("confirm_password").value.trim();

      let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      let passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&^()_+=-]).{6,}$/;

      if (!username || !email || !password || !confirmPassword) {
        alert("Please fill in all the fields.");
        event.preventDefault();
        return false;
      }
      if (!emailPattern.test(email)) {
        alert("Enter a valid email address.");
        event.preventDefault();
        return false;
      }
      if (!passwordPattern.test(password)) {
        alert("Password must include letters, numbers, and at least one special character.");
        event.preventDefault();
        return false;
      }
      if (password !== confirmPassword) {
        alert("Passwords do not match.");
        event.preventDefault();
        return false;
      }
    }
  </script>
</head>
<body>
  <div class="register-container">
    <form class="register-form" action="register.php" method="post" onsubmit="validateRegister(event)">
      <h2>Create Your Account</h2>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter your username" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="your@example.com" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required />

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />

      <button type="submit" class="register-btn">Register</button>
    </form>
  </div>
</body>
</html>