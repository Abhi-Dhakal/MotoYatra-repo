<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'] ?? 0;
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    echo "<script>alert('Invalid User ID'); window.location='../vendor.php';</script>";
    exit;
}

$check = mysqli_query($conn, "
    SELECT DISTINCT users.*
    FROM users
    JOIN bookings ON bookings.user_id = users.user_id
    WHERE users.user_id = $user_id AND bookings.vendor_id = $vendor_id
");

if (mysqli_num_rows($check) === 0) {
    echo "<script>alert('Access denied'); window.location='../vendor.php';</script>";
    exit;
}

mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");

mysqli_query($conn, "DELETE FROM bookings WHERE user_id = $user_id AND vendor_id = $vendor_id");

echo "<script>alert('User deleted successfully'); window.location='../vendor.php';</script>";
exit;
?>