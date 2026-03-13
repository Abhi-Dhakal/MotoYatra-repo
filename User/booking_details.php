<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$user_id = intval($_SESSION['user_id']);

// Fetch all bookings for the user
$query = "SELECT b.*, 
                 u.username, u.email,
                 bk.make, bk.model, bk.price_per_day, bk.image,
                 v.id AS vendor_id, v.name AS vendor_name, v.address
          FROM bookings b
          INNER JOIN users u ON b.user_id = u.user_id
          INNER JOIN bikes bk ON b.bike_id = bk.id
          INNER JOIN vendors v ON b.vendor_id = v.id
          WHERE b.user_id = $user_id
          ORDER BY b.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

$bookings = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Calculate days
    $start = strtotime($row['start_date']);
    $end = strtotime($row['end_date']);
    $days = floor(($end - $start) / (60 * 60 * 24)) + 1;
    if ($days <= 0)
        $days = 1;

    // Payment calculations
    $subtotal = $row['price_per_day'] * $days;
    $discount = 0;
    if ($days >= 30)
        $discount = 0.10;
    elseif ($days >= 7)
        $discount = 0.05;

    $discountAmount = $subtotal * $discount;
    $afterDiscount = $subtotal - $discountAmount;
    $tax = $afterDiscount * 0.13;
    $total = $afterDiscount + $tax;

    // Bike image
    $bikeImage = "../Necessary Image/bike 1.png";
    if (!empty($row['image'])) {
        $serverPath = "../Vendor/Bikes/uploads/" . $row['image'];
        $browserPath = "../Vendor/Bikes/uploads/" . $row['image'];
        if (file_exists($serverPath))
            $bikeImage = $browserPath;
    }

    $row['days'] = $days;
    $row['subtotal'] = $subtotal;
    $row['discountAmount'] = $discountAmount;
    $row['tax'] = $tax;
    $row['total'] = $total;
    $row['bikeImage'] = $bikeImage;

    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Booking Details | MotoYatra</title>
    <style>
        body {
            font-family: Segoe UI;
            background: #f9f9f9;
            margin: 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 50px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 25px;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }

        .bike-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
        }

        .btn,
        .review-btn {
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            color: white;
        }

        .btn {
            background: #007bff;
        }

        .review-btn {
            background: #ff7b00;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            color: white;
        }

        .badge-active {
            background: #28a745;
        }

        .badge-cancelled {
            background: #dc3545;
        }

        .payment-total h2 {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .discount {
            color: green;
            font-weight: bold;
        }

        .vendor-box {
            margin-top: 15px;
            padding: 12px;
            background: #f3f3f3;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <nav>
        <div><img src="../Necessary Image/moto_yatra.png" width="130"></div>
        <ul>
            <li><a href="user.php">Home</a></li>
            <li><a href="user_about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="user_profile.php">Profile</a></li>
            <li><a href="booking_details.php">Booking Details</a></li>
        </ul>
        <div><a href="../Logout/logout.php"><button class="btn">Logout</button></a></div>
    </nav>

    <div class="container">

        <h1 style="text-align:center;">Booking Details</h1>

        <?php if (empty($bookings)): ?>
            <h3 style="color:red;text-align:center;">No Booking Found</h3>
        <?php else: ?>
            <?php foreach ($bookings as $data): ?>
                <div class="grid">
                    <div class="card">
                        <h3>Booking Summary</h3>
                        <span class="badge <?php echo $data['status'] == 'active' ? 'badge-active' : 'badge-cancelled'; ?>">
                            <?php echo ucfirst($data['status']); ?>
                        </span>
                        <p><b>Booking ID:</b> MRR-<?php echo $data['id']; ?></p>
                        <p><b>Booked By:</b> <?php echo htmlspecialchars($data['username']); ?></p>
                        <p><b>Email:</b> <?php echo htmlspecialchars($data['email']); ?></p>
                    </div>

                    <div class="card">
                        <h3><?php echo htmlspecialchars($data['make'] . " " . $data['model']); ?></h3>
                        <img src="<?php echo $data['bikeImage']; ?>" class="bike-img"
                            onerror="this.src='../Necessary Image/bike 1.png';">
                        <p><b>Price Per Day:</b> Rs. <?php echo $data['price_per_day']; ?></p>
                        <div class="vendor-box">
                            <p><b>Vendor:</b> <?php echo htmlspecialchars($data['vendor_name']); ?></p>
                            <p><b>Vendor Address:</b> <?php echo htmlspecialchars($data['address']); ?></p>
                            <a href="add_review.php?vendor_id=<?php echo $data['vendor_id']; ?>">
                                <button class="review-btn">⭐ Review This Vendor</button>
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Rental Period</h3>
                        <p><b>Pickup Date:</b> <?php echo $data['start_date']; ?></p>
                        <p><b>Return Date:</b> <?php echo $data['end_date']; ?></p>
                        <p><b>Total Days:</b> <?php echo $data['days']; ?></p>
                    </div>

                    <div class="card payment-total">
                        <h3>Payment Details</h3>
                        <p>Daily Rate: Rs. <?php echo $data['price_per_day']; ?></p>
                        <p>Rental Days: <?php echo $data['days']; ?></p>
                        <p>Subtotal: Rs. <?php echo number_format($data['subtotal'], 2); ?></p>
                        <?php if ($data['discountAmount'] > 0): ?>
                            <p class="discount">Discount: -Rs. <?php echo number_format($data['discountAmount'], 2); ?></p>
                        <?php endif; ?>
                        <p>Tax (13%): Rs. <?php echo number_format($data['tax'], 2); ?></p>
                        <hr>
                        <h2>Total Amount: Rs. <?php echo number_format($data['total'], 2); ?></h2>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</body>

</html>