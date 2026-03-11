<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$bike_id = intval($_GET['id']);

/* GET BIKE */
$bike_query = mysqli_query($conn, "SELECT * FROM bikes WHERE id=$bike_id");
$bike = mysqli_fetch_assoc($bike_query);

if (!$bike) {
    echo "Bike not found";
    exit;
}

/* IMAGE PATH */
$old_path = "../bikes image/" . $bike['image'];
$new_path = "../vendor/Bikes/uploads/" . $bike['image'];

if (file_exists($old_path) && !empty($bike['image'])) {
    $imgPath = $old_path;
} elseif (file_exists($new_path) && !empty($bike['image'])) {
    $imgPath = $new_path;
} else {
    $imgPath = "../Necessary Image/bike 1.png";
}

/* VENDORS PROVIDING THIS BIKE */
$vendor_query = mysqli_query($conn, "
SELECT v.id AS vendor_id, v.name AS vendor_name, vb.quantity
FROM vendor_bikes vb
JOIN vendors v ON vb.vendor_id=v.id
WHERE vb.bike_id=$bike_id
");

/* TOTAL AVAILABLE */
$total_query = mysqli_query($conn, "
SELECT SUM(quantity) AS total_available
FROM vendor_bikes
WHERE bike_id=$bike_id
");

$total_data = mysqli_fetch_assoc($total_query);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Bike Details</title>

    <style>
        body {
            font-family: Poppins;
            background: #eef1f6;
            margin: 0;
        }

        .wrapper {
            width: 80%;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .bike-header {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .bike-header img {
            width: 380px;
            border-radius: 12px;
        }

        .bike-title {
            font-size: 36px;
            font-weight: 700;
        }

        .price {
            margin-top: 10px;
            font-size: 22px;
            color: #ff6a00;
            font-weight: 600;
        }

        .condition {
            margin-top: 5px;
            font-size: 18px;
            font-weight: 600;
            color: #4CAF50;
            /* green for good condition */
        }

        .section-title {
            margin-top: 35px;
            font-size: 26px;
            font-weight: 700;
        }

        .availability-box {
            background: #f4f4f4;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .vendor-card {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 18px;
            border-radius: 10px;
            background: #fff;
        }

        .vendor-name {
            font-size: 20px;
            font-weight: 600;
        }

        .vendor-count {
            color: #ff6a00;
            font-weight: 700;
        }

        .rating {
            margin-top: 6px;
            font-size: 18px;
            color: gold;
        }

        .review {
            background: #fafafa;
            padding: 8px;
            border-radius: 6px;
            margin-top: 6px;
            font-size: 14px;
        }

        .review-btn {
            margin-top: 12px;
            background: #4e73df;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>

</head>

<body>

    <div class="wrapper">

        <div class="bike-header">

            <img src="<?= $imgPath ?>">

            <div>

                <div class="bike-title">
                    <?= $bike['make'] . " " . $bike['model'] ?>
                </div>

                <div class="condition">
                    Condition: <?= htmlspecialchars($bike['bike_condition'] ?? 'N/A') ?>
                </div>

                <div class="price">
                    Rs <?= $bike['price_per_day'] ?> / day
                </div>

            </div>

        </div>

        <div class="section-title">Availability</div>

        <div class="availability-box">
            Total Bikes Available : <b><?= $total_data['total_available'] ?? 0 ?></b>
        </div>

        <div class="section-title">Available Vendors</div>

        <?php

        if (mysqli_num_rows($vendor_query) > 0) {

            while ($vendor = mysqli_fetch_assoc($vendor_query)) {

                $vendor_id = $vendor['vendor_id'];

                /* AVERAGE RATING */
                $avg_query = mysqli_query($conn, "
SELECT AVG(rating) AS avg_rating
FROM vendor_reviews
WHERE vendor_id=$vendor_id
");

                $avg_data = mysqli_fetch_assoc($avg_query);
                $avg_rating = round($avg_data['avg_rating'], 1);

                /* RECENT REVIEWS */
                $review_query = mysqli_query($conn, "
SELECT vr.rating,vr.review,u.username
FROM vendor_reviews vr
JOIN users u ON vr.user_id=u.user_id
WHERE vr.vendor_id=$vendor_id
ORDER BY vr.id DESC
LIMIT 2
");

                ?>

                <div class="vendor-card">

                    <div class="vendor-name">
                        <?= $vendor['vendor_name'] ?>
                    </div>

                    <div>
                        Available Bikes :
                        <span class="vendor-count"><?= $vendor['quantity'] ?></span>
                    </div>

                    <div class="rating">

                        <?php

                        if ($avg_rating) {

                            $stars = round($avg_rating);

                            for ($i = 1; $i <= 5; $i++) {

                                if ($i <= $stars) {
                                    echo "⭐";
                                } else {
                                    echo "☆";
                                }

                            }

                            echo " ($avg_rating)";

                        } else {
                            echo "No rating yet";
                        }

                        ?>

                    </div>

                    <?php

                    if (mysqli_num_rows($review_query) > 0) {

                        while ($rev = mysqli_fetch_assoc($review_query)) {

                            ?>

                            <div class="review">

                                <b><?= $rev['username'] ?></b>
                                <br>

                                <?= str_repeat("⭐", $rev['rating']) ?>

                                <br>

                                <?= $rev['review'] ?>

                            </div>

                            <?php

                        }

                    }

                    ?>

                    <a href="add_review.php?vendor_id=<?= $vendor_id ?>">

                        <button class="review-btn">
                            Write Review
                        </button>

                    </a>

                </div>

                <?php

            }

        } else {
            echo "No vendors available.";
        }

        ?>

    </div>

</body>

</html>