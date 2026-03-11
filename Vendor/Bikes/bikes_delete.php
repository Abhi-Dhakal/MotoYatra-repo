<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$bike_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$vendor_id = $_SESSION['vendor_id'];

if ($bike_id <= 0) {
    echo "<script>alert('Invalid Bike ID'); window.location='../vendor.php';</script>";
    exit;
}

/* Check if bike belongs to this vendor */
$checkBike = mysqli_query(
    $conn,
    "SELECT * FROM vendor_bikes WHERE vendor_id = $vendor_id AND bike_id = $bike_id"
);

if (mysqli_num_rows($checkBike) == 0) {
    echo "<script>alert('Unauthorized action'); window.location='../vendor.php';</script>";
    exit;
}

/* Check if bike has bookings */
$checkBooking = mysqli_query(
    $conn,
    "SELECT id FROM bookings WHERE bike_id = $bike_id LIMIT 1"
);

if (mysqli_num_rows($checkBooking) > 0) {
    echo "<script>
        alert('This bike cannot be deleted because it has existing bookings.');
        window.location='../vendor.php';
    </script>";
    exit;
}

/* Soft delete bike */
$softDelete = mysqli_query(
    $conn,
    "UPDATE bikes SET is_deleted = 1 WHERE id = $bike_id"
);

if ($softDelete) {

    mysqli_query(
        $conn,
        "UPDATE vendor_bikes 
         SET is_deleted = 1 
         WHERE vendor_id = $vendor_id AND bike_id = $bike_id"
    );

    echo "<script>
        alert('Bike deleted successfully.');
        window.location='../vendor.php';
    </script>";
    exit;

} else {

    $error = mysqli_error($conn);

    echo "<script>
        alert('Delete failed: $error');
        window.location='../vendor.php';
    </script>";
}
?>