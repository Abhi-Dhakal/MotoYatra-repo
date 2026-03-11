<?php
include("connection.php");

if(!isset($_GET['email'])){
die("Invalid Request");
}

$email=$_GET['email'];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$password=$_POST['password'];
$confirm=$_POST['confirm_password'];

if($password!=$confirm){

echo "<script>alert('Passwords do not match');</script>";

}else{

$hash=password_hash($password,PASSWORD_DEFAULT);

$query="UPDATE users SET password='$hash' WHERE email='$email'";
mysqli_query($conn,$query);

echo "<script>alert('Password Updated Successfully'); window.location='login.php';</script>";

}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="stylesheet" href="login.css">
</head>

<body>

<div class="login-container">
<div class="login-card">

<h2>Reset Password</h2>

<form method="post">

<label>New Password</label>
<input type="password" name="password" required>

<label>Confirm Password</label>
<input type="password" name="confirm_password" required>

<button type="submit" class="button">Reset Password</button>

</form>

</div>
</div>

</body>
</html>