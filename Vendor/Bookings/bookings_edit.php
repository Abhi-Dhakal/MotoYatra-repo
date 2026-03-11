<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'] ?? 0;
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    echo "<script>alert('Invalid booking ID'); window.location='../vendor.php';</script>";
    exit;
}
$query = "
    SELECT bookings.*, users.username, CONCAT(bikes.make,' ',bikes.model) AS bike_name
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $total_price = floatval($_POST['total_price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = "
        UPDATE bookings SET
        start_date='$start_date',
        end_date='$end_date',
        total_price=$total_price,
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Booking</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #FF6600;
            outline: none;
        }

        button {
            background: #FF6600;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #e25700;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            font-weight: 600;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #FF6600;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Edit Booking</h2>
        <?php if (isset($error))
            echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>User</label>
            <input type="text" value="<?php echo htmlspecialchars($booking['username']); ?>" disabled>

            <label>Bike</label>
            <input type="text" value="<?php echo htmlspecialchars($booking['bike_name']); ?>" disabled>

            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo $booking['start_date']; ?>" required>

            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo $booking['end_date']; ?>" required>

            <label for="total_price">Total Price (Rs)</label>
            <input type="number" name="total_price" id="total_price" value="<?php echo $booking['total_price']; ?>"
                required>

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="Pending" <?php if ($booking['status'] == 'Pending')
                    echo 'selected'; ?>>Pending</option>
                <option value="Confirmed" <?php if ($booking['status'] == 'Confirmed')
                    echo 'selected'; ?>>Confirmed
                </option>
                <option value="Cancelled" <?php if ($booking['status'] == 'Cancelled')
                    echo 'selected'; ?>>Cancelled
                </option>
            </select>

            <button type="submit">Update Booking</button>
        </form>
        <a href="../vendor.php" class="back-link">&larr; Back to Dashboard</a>
    </div>

</body>

</html>