<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$user_id = $_SESSION['user_id'];
$vendor_id = $_GET['vendor_id'];

if(isset($_POST['submit'])){

    $rating = $_POST['rating'];
    $review = mysqli_real_escape_string($conn,$_POST['review']);

    $query = "INSERT INTO vendor_reviews (vendor_id,user_id,rating,review)
              VALUES ('$vendor_id','$user_id','$rating','$review')";

    if(mysqli_query($conn,$query)){
        echo "<script>alert('Review submitted successfully'); window.location='user.php';</script>";
    }else{
        echo "Error: ".mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Review</title>

<style>

body{
font-family:Arial;
background:#f5f5f5;
}

.container{
width:400px;
margin:100px auto;
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

textarea{
width:100%;
height:100px;
}

button{
background:#ff6600;
color:white;
border:none;
padding:10px 20px;
cursor:pointer;
}

</style>

</head>

<body>

<div class="container">

<h2>Rate Vendor</h2>

<form method="POST">

<label>Rating</label>

<select name="rating" required>

<option value="">Select Rating</option>
<option value="5">⭐⭐⭐⭐⭐ Excellent</option>
<option value="4">⭐⭐⭐⭐ Good</option>
<option value="3">⭐⭐⭐ Average</option>
<option value="2">⭐⭐ Poor</option>
<option value="1">⭐ Bad</option>

</select>

<br><br>

<label>Review</label>

<textarea name="review" placeholder="Write your experience..."></textarea>

<br><br>

<button type="submit" name="submit">Submit Review</button>

</form>

</div>

</body>
</html>