<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "user") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";
$query = "
    SELECT DISTINCT b.*
    FROM bikes b
    JOIN vendor_bikes vb ON b.id = vb.bike_id
    WHERE b.status='Available' AND b.is_deleted=0 AND vb.is_deleted=0
    ORDER BY b.id ASC
";

$bikes = mysqli_query($conn, $query);
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM bikes WHERE status='Available' AND is_deleted=0");
$cc_ranges = [
    "150-200",
    "201-350",
    "351-600"
];
$price_result = mysqli_query($conn, "SELECT MAX(price_per_day) as max_price FROM bikes WHERE status='Available' AND is_deleted=0");
$max_price_row = mysqli_fetch_assoc($price_result);
$max_price = $max_price_row['max_price'] ?? 8000;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User | MotoYatra</title>
    <link rel="stylesheet" href="user.css">
</head>

<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <img src="../Necessary Image/moto_yatra.png" alt="Moto Yatra Logo">
        </div>

        <ul>
            <li><a href="user.php">Home</a></li>
            <li><a href="user_about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="user_profile.php">Profile</a></li>
            <li><a href="booking_details.php">Booking Details</a></li>
        </ul>

        <div class="buttons">
            <a href="user_profile.php"><button class="profile">Profile</button></a>
            <a href="../Logout/logout.php"><button class="logout">Logout</button></a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="first-page">
        <div class="first-page-text">
            <h1>Find Your Perfect Ride</h1>
            <p>Explore a diverse fleet of motorbikes for every adventure. Your journey begins here.</p>
            <a href="user.php"><button>Explore Bikes</button></a>
        </div>
        <img src="../Necessary Image/bike 1.png" alt="Bike">
    </section>

    <section class="discount-banner">
        <h2>Special Rental Discounts</h2>
        <p>Rent for <b>1 Week</b> and get <b>5% OFF</b></p>
        <p>Rent for <b>1 Month</b> and get <b>10% OFF</b></p>
    </section>

    <!-- Bike Filters and Listing -->
    <section class="bike-container">
        <aside class="filters">
            <h3>Bike Type</h3>
            <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                <label>
                    <input type="checkbox" name="type" value="<?= htmlspecialchars($cat['category']) ?>" checked>
                    <?= htmlspecialchars($cat['category']) ?>
                </label>
            <?php endwhile; ?>

            <h3>Cubic Capacity</h3>
            <?php foreach ($cc_ranges as $range): ?>
                <label>
                    <input type="checkbox" name="cc" value="<?= $range ?>" checked>
                    <?= $range ?> cc
                </label>
            <?php endforeach; ?>

            <h3>Price Range (NRs)</h3>
            <input type="range" id="priceRange" min="0" max="<?= $max_price ?>" value="<?= $max_price ?>">
            <p>Up to: <span id="priceValue"><?= $max_price ?></span> / day</p>
        </aside>

        <section class="bikes">
            <?php while ($b = mysqli_fetch_assoc($bikes)):
                $paths = [
                    "../bikes image/" . $b['image'],
                    "../vendor/Bikes/uploads/" . $b['image']
                ];
                $imgPath = "../Necessary Image/bike 1.png";
                foreach ($paths as $path) {
                    if (!empty($b['image']) && file_exists($path)) {
                        $imgPath = $path;
                        break;
                    }
                }
                ?>
                <div class="bike" data-type="<?= htmlspecialchars($b['category']); ?>"
                    data-cc="<?= htmlspecialchars($b['cc']); ?>" data-price="<?= $b['price_per_day']; ?>">

                    <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($b['model']); ?>">
                    <h4><?= htmlspecialchars($b['make'] . " " . $b['model']); ?></h4>
                    <span>Rs. <?= $b['price_per_day']; ?>/day</span>
                    <span class="discount">5% OFF Weekly | 10% OFF Monthly</span>

                    <div class="bike-buttons">
                        <a href="bike_details.php?id=<?= $b['id']; ?>" class="details-btn">Details</a>
                        <a class="book-btn" href="booking.php?id=<?= $b['id']; ?>">Book Now</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>
    </section>

    <!-- Why Choose Us Section -->
    <section class="thirdpage">
        <h2>Why Choose Us?</h2>
        <div class="features">
            <div class="box">
                <div class="icon"><img src="../Necessary Image/selection.png" alt="selection"></div>
                <h3>Wide Selection</h3>
                <p>Choose from a diverse fleet of high-performance and classic motorcycles.</p>
            </div>
            <div class="box">
                <div class="icon"><img src="../Necessary Image/Booking.png" alt="booking"></div>
                <h3>Easy Booking</h3>
                <p>Reserve your ride quickly and easily with our online booking system.</p>
            </div>
            <div class="box">
                <div class="icon"><img src="../Necessary Image/support.png" alt="Support"></div>
                <h3>Premium Support</h3>
                <p>Enjoy 24/7 customer service and on-road support.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact-section">
        <h2>Get in Touch</h2>
        <div class="contact-info">
            <div class="info-box">
                <img src="../Necessary Image/message-removebg-preview.png" alt="Email Icon" class="icon1">
                <p>abhidhakal40@gmail.com</p>
            </div>

            <div class="info-box">
                <img src="../Necessary Image/call.png" alt="Phone Icon" class="icon1">
                <p>+977- 9840700026</p>
            </div>

            <div class="info-box">
                <img src="../Necessary Image/location.png" alt="Location Icon" class="icon1">
                <p>Kavresthali-2, Kathmandu</p>
            </div>
        </div>
    </section>

    <!-- JS Filtering -->
    <script>
        const typeFilters = document.querySelectorAll("input[name='type']");
        const ccFilters = document.querySelectorAll("input[name='cc']");
        const priceRange = document.getElementById("priceRange");
        const priceValue = document.getElementById("priceValue");
        const bikes = document.querySelectorAll(".bike");

        function filterBikes() {
            const selectedTypes = [...typeFilters].filter(i => i.checked).map(i => i.value);
            const selectedCC = [...ccFilters].filter(i => i.checked).map(i => i.value);
            const maxPrice = parseInt(priceRange.value);
            priceValue.textContent = maxPrice;

            bikes.forEach(bike => {
                const bikeType = bike.dataset.type;
                const bikeCC = parseInt(bike.dataset.cc);
                const bikePrice = parseInt(bike.dataset.price);

                let ccMatch = false;
                selectedCC.forEach(range => {
                    const [min, max] = range.split("-").map(Number);
                    if (bikeCC >= min && bikeCC <= max) ccMatch = true;
                });

                const typeMatch = selectedTypes.includes(bikeType);
                const priceMatch = bikePrice <= maxPrice;

                bike.style.display = (typeMatch && ccMatch && priceMatch) ? "block" : "none";
            });
        }

        typeFilters.forEach(i => i.addEventListener("change", filterBikes));
        ccFilters.forEach(i => i.addEventListener("change", filterBikes));
        priceRange.addEventListener("input", filterBikes);
    </script>
</body>

</html>