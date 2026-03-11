<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'] ?? 0;
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id > 0) {
    $delete = "DELETE FROM bookings WHERE id=$booking_id AND vendor_id=$vendor_id";
    mysqli_query($conn, $delete);
}

header("Location: ../vendor.php");
exit;
?>
