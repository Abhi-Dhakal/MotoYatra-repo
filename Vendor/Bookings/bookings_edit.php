<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'] ?? 0;
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = "";

if ($booking_id <= 0) {
    echo "<script>alert('Invalid booking ID'); window.location='../vendor.php';</script>";
    exit;
}

/* Fetch Booking */

$query = "
SELECT bookings.*, users.username, bikes.price_per_day,
CONCAT(bikes.make,' ',bikes.model) AS bike_name
FROM bookings
JOIN users ON users.user_id = bookings.user_id
JOIN bikes ON bikes.id = bookings.bike_id
WHERE bookings.id = $booking_id AND bookings.vendor_id = $vendor_id
LIMIT 1
";

$result = mysqli_query($conn, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo "<script>alert('Booking not found or access denied'); window.location='../vendor.php';</script>";
    exit;
}

$price_per_day = $booking['price_per_day'];

/* Update Booking */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $today = date("Y-m-d");

    /* Validation */

    if (empty($start_date) || empty($end_date)) {
        $error = "Dates cannot be empty.";
    } elseif ($start_date < $today) {
        $error = "Start date cannot be in the past.";
    } elseif ($end_date <= $start_date) {
        $error = "End date must be after start date.";
    } else {

        /* Calculate days */

        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);

        if ($days <= 0) {
            $days = 1;
        }

        $total_price = $days * $price_per_day;

        /* Discount */

        $discount = 0;

        if ($days >= 30) {
            $discount = $total_price * 0.10;
        } elseif ($days >= 7) {
            $discount = $total_price * 0.05;
        }

        $final_price = $total_price - $discount;

        /* Update */

        $update = "
UPDATE bookings SET
start_date='$start_date',
end_date='$end_date',
total_price='$final_price',
status='$status'
WHERE id=$booking_id AND vendor_id=$vendor_id
";

        if (mysqli_query($conn, $update)) {
            echo "<script>alert('Booking updated successfully'); window.location='../vendor.php';</script>";
            exit;
        } else {
            $error = "Failed to update booking: " . mysqli_error($conn);
        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Booking</title>

    <style>
        *{
            box-sizing: border-box;
        }
        
        body {
            font-family: Poppins;
            background: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #FF6600;
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input:focus,
        select:focus {
            border-color: #FF6600;
            outline: none;
        }

        button {
            background: #FF6600;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #e25700;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        .back-link {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #FF6600;
            text-decoration: none;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Edit Booking</h2>

        <?php
        if ($error != "") {
            echo "<p class='error'>$error</p>";
        }
        ?>

        <form method="post">

            <label>User</label>
            <input type="text" value="<?php echo htmlspecialchars($booking['username']); ?>" disabled>

            <label>Bike</label>
            <input type="text" value="<?php echo htmlspecialchars($booking['bike_name']); ?>" disabled>

            <label>Start Date</label>
            <input type="date" name="start_date" id="start" value="<?php echo $booking['start_date']; ?>" required
                onchange="calculatePrice()">

            <label>End Date</label>
            <input type="date" name="end_date" id="end" value="<?php echo $booking['end_date']; ?>" required
                onchange="calculatePrice()">

            <label>Total Price</label>
            <input type="text" id="total_price" value="<?php echo $booking['total_price']; ?>" readonly>

            <label>Status</label>
            <select name="status" required>
                <option value="Pending" <?php if ($booking['status'] == "Pending")
                    echo "selected"; ?>>Pending</option>
                <option value="Confirmed" <?php if ($booking['status'] == "Confirmed")
                    echo "selected"; ?>>Confirmed
                </option>
                <option value="Cancelled" <?php if ($booking['status'] == "Cancelled")
                    echo "selected"; ?>>Cancelled
                </option>
            </select>

            <button type="submit">Update Booking</button>

        </form>

        <a href="../vendor.php" class="back-link">Back to Dashboard</a>

    </div>

    <script>

        let price = <?php echo $price_per_day; ?>;
        let today = new Date().toISOString().split('T')[0];

        document.getElementById("start").setAttribute("min", today);
        document.getElementById("end").setAttribute("min", today);

        function calculatePrice() {

            let start = document.getElementById("start").value;
            let end = document.getElementById("end").value;

            if (start && end) {

                let startDate = new Date(start);
                let endDate = new Date(end);

                if (endDate <= startDate) {
                    alert("End date must be after start date");
                    document.getElementById("end").value = "";
                    return;
                }

                let days = (endDate - startDate) / (1000 * 60 * 60 * 24);

                if (days <= 0) { days = 1; }

                let total = days * price;

                let discount = 0;

                if (days >= 30) {
                    discount = total * 0.10;
                }
                else if (days >= 7) {
                    discount = total * 0.05;
                }

                let final = total - discount;

                document.getElementById("total_price").value = "Rs " + final;

            }

        }

    </script>

</body>

</html>