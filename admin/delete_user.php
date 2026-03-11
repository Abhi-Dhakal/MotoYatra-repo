<?php
require "connection.php";
$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
header("Location: admin_vendor.php");
exit;