<?php
require "connection.php";

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM vendors WHERE id=$id");

header("Location: admin_vendor.php");
?>
