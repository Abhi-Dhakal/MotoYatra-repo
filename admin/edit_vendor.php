<?php
require "connection.php";

$id = $_GET['id'];
$vendor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vendors WHERE id=$id"));

if (isset($_POST['submit'])) {

    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];
    $password = $_POST['password'];

    if (!empty($password)) {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query($conn, "
            UPDATE vendors 
            SET name='$name', email='$email', phone='$phone', address='$address', password='$hashed'
            WHERE id=$id
        ");

        mysqli_query($conn, "
            UPDATE users 
            SET username='$name', email='$email', password='$hashed'
            WHERE email = '$vendor[email]'
        ");

    } else {

        mysqli_query($conn, "
            UPDATE vendors 
            SET name='$name', email='$email', phone='$phone', address='$address'
            WHERE id=$id
        ");

        mysqli_query($conn, "
            UPDATE users 
            SET username='$name', email='$email'
            WHERE email = '$vendor[email]'
        ");
    }

    header("Location: admin_vendor.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Vendor</title>

<style>
    body { background: #f7f7f7; font-family: Arial; }
    .wrapper {
        max-width: 500px;
        margin: 60px auto;
        padding: 25px;
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    input {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
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
    }
    small { color: gray; }
</style>
</head>

<body>

<div class="wrapper">
    <h2>Edit Vendor</h2>

    <form method="POST">
        <input type="text" name="name" value="<?= $vendor['name'] ?>" required>
        <input type="email" name="email" value="<?= $vendor['email'] ?>" required>
        <input type="text" name="phone" value="<?= $vendor['phone'] ?>" required>
        <input type="text" name="address" value="<?= $vendor['address'] ?>" required>

        <input type="password" name="password" placeholder="New Password (leave blank to keep same)">
        <small>Leave password empty if you don't want to change it.</small>

        <button name="submit" class="btn-orange">Update Vendor</button>
    </form>
</div>

</body>
</html>
