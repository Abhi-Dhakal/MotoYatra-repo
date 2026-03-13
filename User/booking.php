<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$bike_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

$bike_query = mysqli_query($conn, "SELECT * FROM bikes WHERE id = $bike_id");

if (mysqli_num_rows($bike_query) == 0) {
    die("Bike not found.");
}

$bike = mysqli_fetch_assoc($bike_query);

$vendors = mysqli_query(
    $conn,
    "SELECT v.id, v.name, vb.quantity 
     FROM vendor_bikes vb 
     JOIN vendors v ON vb.vendor_id = v.id 
     WHERE vb.bike_id = $bike_id AND vb.quantity > 0"
);

$payment_success = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $vendor_id = intval($_POST['vendor']);
    $start_date = $_POST['start'];
    $end_date = $_POST['end'];

    if (empty($vendor_id) || empty($start_date) || empty($end_date)) {
        $error_message = "All fields are required.";
    }

    $today = date('Y-m-d');

    if ($start_date < $today) {
        $error_message = "Start date cannot be in the past.";
    }

    if ($end_date < $start_date) {
        $error_message = "End date cannot be before start date.";
    }

    $check_vendor = mysqli_query(
        $conn,
        "SELECT quantity FROM vendor_bikes 
         WHERE vendor_id = $vendor_id AND bike_id = $bike_id"
    );

    if (mysqli_num_rows($check_vendor) == 0) {
        $error_message = "Invalid vendor selected.";
    } else {
        $vendor_data = mysqli_fetch_assoc($check_vendor);

        if ($vendor_data['quantity'] <= 0) {
            $error_message = "Selected vendor is out of stock.";
        }
    }

    if (empty($error_message)) {

        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);

        $days = floor(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;

        if ($days <= 0) {
            $error_message = "Invalid booking duration.";
        } else {

            $base_price = $days * $bike['price_per_day'];

            $discount = 0;

            if ($days >= 30) {
                $discount = 0.10;
            } elseif ($days >= 7) {
                $discount = 0.05;
            }

            $total_price = $base_price - ($base_price * $discount);

            mysqli_begin_transaction($conn);

            $insert = mysqli_query(
                $conn,
                "INSERT INTO bookings 
                (user_id, bike_id, vendor_id, start_date, end_date, total_price, status) 
                VALUES 
                ($user_id, $bike_id, $vendor_id, '$start_date', '$end_date', $total_price, 'active')"
            );

            $update = mysqli_query(
                $conn,
                "UPDATE vendor_bikes 
                 SET quantity = quantity - 1 
                 WHERE bike_id = $bike_id AND vendor_id = $vendor_id"
            );

            if ($insert && $update) {

                mysqli_commit($conn);
                $payment_success = true;

            } else {

                mysqli_rollback($conn);
                $error_message = "Booking failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Book Bike</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            padding: 30px;
        }

        .card {
            background: white;
            width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
        }

        label {
            font-weight: bold;
            margin-top: 12px;
            display: block;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #ff8800;
            color: white;
            border: none;
            font-size: 17px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #e07000;
        }

        .success {
            text-align: center;
            color: green;
            font-size: 20px;
            padding: 30px;
        }

        .error {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }

        .total-price {
            font-weight: bold;
            margin-top: 10px;
            font-size: 18px;
            text-align: right;
        }

        .discount {
            color: green;
            font-weight: bold;
            margin-top: 5px;
            text-align: right;
        }
    </style>
</head>

<body>

    <?php if ($payment_success): ?>

        <div class="success">
            Payment Successful! Booking Confirmed.
        </div>

        <div style="text-align:center;">
            <a href="user.php">Return to Home</a>
        </div>

    <?php else: ?>

        <div class="card">

            <h2>Bike Booking Form</h2>

            <?php if (!empty($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST">

                <label>Bike</label>
                <input type="text" value="<?php echo htmlspecialchars($bike['make'] . ' ' . $bike['model']); ?>" readonly>

                <label>Price Per Day</label>
                <input type="number" id="pricePerDay" value="<?php echo $bike['price_per_day']; ?>" readonly>

                <label>Choose Vendor</label>

                <select name="vendor" required>

                    <option value="">-- Select Vendor --</option>

                    <?php while ($v = mysqli_fetch_assoc($vendors)): ?>

                        <option value="<?php echo $v['id']; ?>">
                            <?php echo htmlspecialchars($v['name']); ?> (Available: <?php echo $v['quantity']; ?>)
                        </option>

                    <?php endwhile; ?>

                </select>

                <label>Start Date</label>
                <input type="date" id="startDate" name="start" min="<?php echo date('Y-m-d'); ?>" required>

                <label>End Date</label>
                <input type="date" id="endDate" name="end" min="<?php echo date('Y-m-d'); ?>" required>

                <div class="total-price">
                    Total Price: Rs.<span id="totalPrice">0</span>
                </div>

                <div class="discount" id="discountMessage"></div>

                <button type="submit">Confirm Booking</button>

            </form>

        </div>

        <script>

            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const pricePerDay = parseFloat(document.getElementById('pricePerDay').value);
            const totalPriceElem = document.getElementById('totalPrice');
            const discountMessage = document.getElementById('discountMessage');

            function calculateTotal() {

                const start = new Date(startDate.value);
                const end = new Date(endDate.value);

                if (start && end && end >= start) {

                    const diffTime = end - start;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                    let total = diffDays * pricePerDay;
                    let discount = 0;
                    let message = "";

                    if (diffDays >= 30) {
                        discount = 0.10;
                        message = "10% Monthly Discount Applied";
                    }
                    else if (diffDays >= 7) {
                        discount = 0.05;
                        message = "5% Weekly Discount Applied";
                    }

                    total = total - (total * discount);

                    totalPriceElem.textContent = total.toFixed(2);
                    discountMessage.textContent = message;

                }
                else {
                    totalPriceElem.textContent = "0";
                    discountMessage.textContent = "";
                }

            }

            startDate.addEventListener('change', calculateTotal);
            endDate.addEventListener('change', calculateTotal);

        </script>

    <?php endif; ?>

</body>

</html>