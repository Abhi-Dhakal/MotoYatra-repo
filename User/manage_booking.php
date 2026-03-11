<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$user_id = $_SESSION['user_id'];

/* Get Latest Booking */
$query = "SELECT b.*, bk.make, bk.model, bk.price_per_day
          FROM bookings b
          INNER JOIN bikes bk ON b.bike_id = bk.id
          WHERE b.user_id = '$user_id'
          ORDER BY b.id DESC LIMIT 1";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<h2 style='text-align:center;margin-top:50px;color:red;'>No Booking Found.</h2>";
    exit;
}

$data = mysqli_fetch_assoc($result);

/* If status is NULL make it active */
if ($data['status'] == NULL) {
    $data['status'] = "active";
}

/* Cancel Booking */
if (isset($_POST['cancel'])) {

    $id = $data['id'];
    $bike_id = $data['bike_id'];

    mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id='$id'");
    mysqli_query($conn, "UPDATE bikes SET status='available' WHERE id='$bike_id'");

    header("Location: manage_booking.php");
    exit;
}

/* Update Return Date */
if (isset($_POST['update'])) {

    $new_date = $_POST['end_date'];
    $id = $data['id'];

    mysqli_query($conn, "UPDATE bookings SET end_date='$new_date' WHERE id='$id'");

    header("Location: manage_booking.php");
    exit;
}

/* -------- PRICE CALCULATION -------- */

$start = strtotime($data['start_date']);
$end = strtotime($data['end_date']);

$days = floor(($end - $start) / (60 * 60 * 24)) + 1;

if ($days <= 0) {
    $days = 1;
}

$subtotal = $data['price_per_day'] * $days;

$discount = 0;
$discountAmount = 0;

/* Weekly & Monthly Offer */
if ($days >= 30) {
    $discount = 0.10;
} elseif ($days >= 7) {
    $discount = 0.05;
}

$discountAmount = $subtotal * $discount;

$afterDiscount = $subtotal - $discountAmount;

$tax = $afterDiscount * 0.13;

$total = $afterDiscount + $tax;

?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>

*{
box-sizing:border-box;
margin:0;
padding:0;
font-family:'Poppins',sans-serif;
}

body{
background:#f7f7f7;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.card{
background:#fff;
width:400px;
padding:30px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

h2{
text-align:center;
margin-bottom:20px;
}

.status{
text-align:center;
font-weight:600;
margin-bottom:15px;
}

.active{
color:green;
}

.cancelled{
color:red;
}

p{
margin-bottom:8px;
font-size:14px;
}

.price-box{
background:#f8f9fa;
padding:10px;
border-radius:8px;
margin-top:10px;
}

.discount{
color:green;
font-weight:600;
}

input{
width:100%;
padding:8px;
margin:10px 0;
border:1px solid #ccc;
border-radius:5px;
}

input:focus{
border-color:#ff7a00;
outline:none;
}

.button{
width:100%;
background:#ff7a00;
color:#fff;
border:none;
padding:10px;
border-radius:5px;
font-weight:600;
cursor:pointer;
margin-top:8px;
}

.button:hover{
background:#e96a00;
}

.cancel{
background:#dc3545;
}

.cancel:hover{
background:#b02a37;
}

.back{
display:block;
text-align:center;
margin-top:15px;
text-decoration:none;
color:#ff7a00;
}

</style>

</head>

<body>

<div class="card">

<h2>Manage Booking</h2>

<div class="status <?php echo $data['status']; ?>">
Status: <?php echo ucfirst($data['status']); ?>
</div>

<p><strong>Bike:</strong> <?php echo $data['make']." ".$data['model']; ?></p>
<p><strong>Start Date:</strong> <?php echo $data['start_date']; ?></p>
<p><strong>Return Date:</strong> <?php echo $data['end_date']; ?></p>
<p><strong>Total Days:</strong> <?php echo $days; ?></p>

<div class="price-box">

<p><strong>Subtotal:</strong> Rs. <?php echo number_format($subtotal,2); ?></p>

<?php if($discountAmount > 0): ?>
<p class="discount">
Discount: -Rs. <?php echo number_format($discountAmount,2); ?>
<?php
if($days >= 30){
echo "(10% Monthly Offer)";
}else{
echo "(5% Weekly Offer)";
}
?>
</p>
<?php endif; ?>

<p><strong>Tax (13%):</strong> Rs. <?php echo number_format($tax,2); ?></p>

<hr>

<p><strong>Total Amount:</strong> Rs. <?php echo number_format($total,2); ?></p>

</div>

<?php if ($data['status'] == "active") { ?>

<form method="POST">
<label>Update Return Date</label>
<input type="date" name="end_date" required>
<button type="submit" name="update" class="button">
Update Date
</button>
</form>

<form method="POST">
<button type="submit" name="cancel" class="button cancel">
Cancel Booking
</button>
</form>

<?php } else { ?>

<p style="text-align:center;color:red;margin-top:10px;">
Booking is cancelled.
</p>

<?php } ?>

<a href="booking_details.php" class="back">Back</a>

</div>

</body>
</html>

