<?php
require "connection.php";

$err = "";
$success = "";

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password)) {
        $err = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $err = "Password must be at least 6 characters!";
    } else {
        $check = mysqli_query($conn, "SELECT email FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $err = "Email already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query(
                $conn,
                "INSERT INTO users (username,email,password,role)
                 VALUES ('$username','$email','$hashed','$role')"
            );
            header("Location: admin_vendor.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add User</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: Arial;
        }

        .wrapper {
            max-width: 450px;
            margin: 60px auto;
            background: white;
            padding: 25px;
            border-radius: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn-orange {
            background: #FFA858;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            width: 100%;
        }

        .error {
            color: #d9534f;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <h2>Add User</h2>
        <?php if ($err)
            echo "<p class='error'>$err</p>"; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <select name="role">
                <option value="user">User</option>
            </select>

            <button class="btn-orange" name="submit">Add User</button>
        </form>
    </div>

</body>

</html>