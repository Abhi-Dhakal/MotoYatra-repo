<?php
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {

        header("Location: reset_password.php?email=$email");
        exit;

    } else {

        echo "<script>alert('Email not found');</script>";

    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">

            <h2>Forgot Password</h2>

            <form method="post">

                <label>Email</label>
                <input type="email" name="email" required>

                <button type="submit" class="button">Verify Email</button>

            </form>

        </div>
    </div>

</body>

</html>