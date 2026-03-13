<?php
session_start();
require "connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

if (isset($_POST['booking_id'])) {

    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    $booking_id = mysqli_real_escape_string($conn, $booking_id);

    $query = "UPDATE bookings 
              SET status='cancelled' 
              WHERE id='$booking_id' 
              AND user_id='$user_id'";

    mysqli_query($conn, $query);
}

header("Location: user_profile.php");
exit();
?>