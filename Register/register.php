<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {

  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm = trim($_POST['confirm_password']);

  // Check empty fields
  if (!$username || !$email || !$password || !$confirm) {
    die("All fields must be filled.");
  }

  // Username validation (letters and spaces allowed)
  if (!preg_match("/^[A-Za-z]+( [A-Za-z]+)*$/", $username)) {
    die("Username should contain letters and spaces only.");
  }

  // Email validation
  if (!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/", $email)) {
    die("Enter a valid email format (example: name123@gmail.com)");
  }

  // Password validation
  if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{6,}$/", $password)) {
    die("Password must contain letters, numbers and a special character.");
  }

  // Password match check
  if ($password !== $confirm) {
    die("Passwords do not match.");
  }

  $username = mysqli_real_escape_string($conn, $username);
  $email = mysqli_real_escape_string($conn, $email);
  $hashed = password_hash($password, PASSWORD_DEFAULT);

  // Check if email already exists
  $checkEmail = "SELECT * FROM users WHERE email='$email'";
  $result = mysqli_query($conn, $checkEmail);

  if (mysqli_num_rows($result) > 0) {
    echo "<script>
            alert('Email already exists. Please use another email.');
            window.history.back();
          </script>";
    exit();
  }

  // Insert user
  $sql = "INSERT INTO users (username, email, password)
          VALUES ('$username', '$email', '$hashed')";

  if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('Account created successfully!');
            window.location.href='../Login/login.php';
          </script>";
  } else {
    echo "Database Error: " . mysqli_error($conn);
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
      font-family: Arial;
    }

    body {
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .register-container {
      background: white;
      padding: 30px;
      width: 380px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      font-size: 14px;
      color: #333;
    }

    input {
      width: 100%;
      padding: 8px;
      margin: 8px 0 15px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    input:focus {
      border-color: #ff7b00;
      outline: none;
    }

    .register-btn {
      width: 100%;
      padding: 10px;
      background: #ff7b00;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }

    .register-btn:hover {
      background: #e66d00;
    }
  </style>

  <script>

    function validateRegister(event) {

      let username = document.getElementById("username").value.trim();
      let email = document.getElementById("email").value.trim();
      let password = document.getElementById("password").value.trim();
      let confirmPassword = document.getElementById("confirm_password").value.trim();

      let usernamePattern = /^[A-Za-z]+( [A-Za-z]+)*$/;
      let emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      let passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{6,}$/;

      if (!username || !email || !password || !confirmPassword) {
        alert("Please fill all fields.");
        event.preventDefault();
        return false;
      }

      if (!usernamePattern.test(username)) {
        alert("Username should contain letters and spaces only.");
        event.preventDefault();
        return false;
      }

      if (!emailPattern.test(email)) {
        alert("Enter valid email like name123@gmail.com");
        event.preventDefault();
        return false;
      }

      if (!passwordPattern.test(password)) {
        alert("Password must include letters, numbers and a special character.");
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

    <form action="register.php" method="post" onsubmit="validateRegister(event)">

      <h2>Create Account</h2>

      <label>Username</label>
      <input type="text" id="username" name="username" placeholder="Enter your name" required>

      <label>Email</label>
      <input type="text" id="email" name="email" placeholder="name123@gmail.com" required>

      <label>Password</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>

      <label>Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>

      <button type="submit" class="register-btn">Register</button>

    </form>

  </div>

</body>

</html>