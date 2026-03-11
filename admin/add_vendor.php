<?php
require "connection.php";

$err = "";
$success = "";

if (isset($_POST['submit'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $err = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format!";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $err = "Phone number must be 10 digits!";
    } elseif (strlen($password) < 6) {
        $err = "Password must be at least 6 characters!";
    } else {

        $checkVendor = mysqli_query($conn, "SELECT email FROM vendors WHERE email='$email'");
        if (mysqli_num_rows($checkVendor) > 0) {
            $err = "Vendor with this email already exists!";
        } else {
            $checkUser = mysqli_query($conn, "SELECT email FROM users WHERE email='$email'");
            if (mysqli_num_rows($checkUser) > 0) {
                $err = "This email already exists in users table!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $insertVendor = mysqli_query(
                    $conn,
                    "INSERT INTO vendors (name, email, phone, address, password)
                     VALUES ('$name', '$email', '$phone', '$address', '$hashed')"
                );
                $insertUser = mysqli_query(
                    $conn,
                    "INSERT INTO users (username, email, password, role)
                     VALUES ('$name', '$email', '$hashed', 'vendor')"
                );

                if ($insertVendor && $insertUser) {
                    $success = "Vendor added successfully!";
                    header("refresh:1; url=admin_vendor.php");
                } else {
                    $err = "Something went wrong while inserting data!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Vendor</title>

    <style>
        body {
            background: #f7f7f7;
            font-family: Arial;
        }

        .wrapper {
            max-width: 500px;
            margin: 60px auto;
            padding: 25px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 9px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .btn-orange {
            background: #FFA858;
            padding: 10px;
            color: white;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 15px;
        }

        .error {
            color: #d9534f;
            margin-bottom: 10px;
        }

        .success {
            color: #28a745;
            margin-bottom: 10px;
        }
    </style>

    <script>
        function validateForm() {
            let name = document.forms["vendorForm"]["name"].value.trim();
            let email = document.forms["vendorForm"]["email"].value.trim();
            let phone = document.forms["vendorForm"]["phone"].value.trim();
            let address = document.forms["vendorForm"]["address"].value.trim();
            let password = document.forms["vendorForm"]["password"].value.trim();

            if (name === "") {
                alert("Vendor name is required");
                return false;
            }

            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Enter a valid email address");
                return false;
            }

            if (!/^[0-9]{10}$/.test(phone)) {
                alert("Phone number must be 10 digits");
                return false;
            }

            if (address === "") {
                alert("Address is required");
                return false;
            }

            if (password.length < 6) {
                alert("Password must be at least 6 characters");
                return false;
            }

            return true;
        }
    </script>

</head>

<body>

    <div class="wrapper">
        <h2>Add Vendor</h2>

        <?php if ($err != "")
            echo "<p class='error'>$err</p>"; ?>
        <?php if ($success != "")
            echo "<p class='success'>$success</p>"; ?>

        <form name="vendorForm" method="POST" onsubmit="return validateForm();">
            <input type="text" name="name" placeholder="Vendor Name" required>
            <input type="email" name="email" placeholder="Vendor Email" required>
            <input type="text" name="phone" placeholder="Phone Number (10 digits)" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="password" name="password" placeholder="Password (min 6 characters)" required>

            <button name="submit" class="btn-orange">Add Vendor</button>
        </form>
    </div>

</body>

</html>