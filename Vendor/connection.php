<?php
$dbhost = "localhost";
$dbuser = "abhi";
$dbpass = "718718";
$dbname = "projectdb";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Failed to connect to database: " . mysqli_connect_error());
}
?>