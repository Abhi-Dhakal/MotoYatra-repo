<?php
session_start();
require "connection.php";

// Redirect if not logged in or not a user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$userQuery = mysqli_query($conn, "SELECT username, email FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($userQuery);

// Current bookings
$currentBookings = mysqli_query($conn, "
    SELECT b.make, b.model, bk.start_date, bk.end_date, bk.status
    FROM bookings bk
    INNER JOIN bikes b ON bk.bike_id = b.id
    WHERE bk.user_id = $user_id AND bk.end_date >= CURDATE()
    ORDER BY bk.start_date ASC
");

// Rental history
$history = mysqli_query($conn, "
    SELECT b.make, b.model, bk.start_date, bk.end_date, bk.total_price, bk.status
    FROM bookings bk
    INNER JOIN bikes b ON bk.bike_id = b.id
    WHERE bk.user_id = $user_id
    ORDER BY bk.start_date DESC
");

$totalBookings = mysqli_num_rows($history);
$activeBookings = mysqli_num_rows($currentBookings);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard | MotoYatra</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: auto;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo img {
            height: 50px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .logout-form {
            margin: 0;
        }

        .logout {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            background-color: #f97316;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background-color 0.3s, transform 0.2s;
        }

        .logout:hover {
            color: white;
            transform: scale(1.05);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 120px;
            /* space for fixed navbar */
        }

        h2 {
            text-align: center;
            color: #f97316;
            margin-bottom: 20px;
        }

        .dashboard-summary {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .summary-card {
            background: #fff;
            padding: 25px 35px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            min-width: 180px;
        }

        .summary-card h4 {
            font-size: 2rem;
            color: #f97316;
            margin-bottom: 5px;
        }

        .section-title {
            text-align: center;
            margin: 40px 0 20px;
            color: #f97316;
            font-size: 1.5rem;
        }

        .profile-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            max-width: 500px;
            margin: 20px auto 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-card label {
            display: block;
            margin: 10px 0 5px;
            color: #f97316;
            font-weight: 600;
        }

        .profile-card input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }

        .profile-card button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #f97316;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .change-password {
            display: block;
            text-align: center;
            padding: 10px 20px;
            margin: 15px auto 0;
            border-radius: 6px;
            border: 2px solid #f97316;
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
        }

        .change-password:hover {
            background: #f97316;
            color: #fff;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .card h4 {
            color: #f97316;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            color: #fff;
            width: max-content;
        }

        .badge-active {
            background: #28a745;
        }

        .badge-cancelled {
            background: #dc3545;
        }

        .badge-upcoming {
            background: #007bff;
        }
    </style>
</head>

<body>

    <nav>
        <div class="nav-container">
            <div class="logo">
                <img src="../Necessary Image/moto_yatra.png" alt="Logo">
            </div>

            <ul class="nav-links">
                <li><a href="user.php">Home</a></li>
                <li><a href="user_about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="user_profile.php">Profile</a></li>
                <li><a href="booking_details.php">Booking Details</a></li>
            </ul>

            <form action="../Logout/logout.php" method="POST" class="logout-form">
                <button type="submit" class="logout">Logout</button>
            </form>
        </div>
    </nav>


    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>

        <div class="dashboard-summary">
            <div class="summary-card">
                <h4><?php echo $totalBookings; ?></h4>
                <p>Total Rentals</p>
            </div>
            <div class="summary-card">
                <h4><?php echo $activeBookings; ?></h4>
                <p>Current Bookings</p>
            </div>
        </div>

        <h3 class="section-title">Personal Information</h3>
        <div class="profile-card">
            <form action="update_profile.php" method="POST">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <button type="submit">Save Changes</button>
            </form>
            <a class="change-password" href="change_password.php">Change Password</a>
        </div>

        <h3 class="section-title">Current Bookings</h3>
        <div class="cards-container">
            <?php
            if (mysqli_num_rows($currentBookings) > 0) {
                while ($row = mysqli_fetch_assoc($currentBookings)) {
                    $statusClass = ($row['status'] == 'active') ? 'badge-active' : 'badge-cancelled';
                    echo '<div class="card">';
                    echo '<h4>' . htmlspecialchars($row['make'] . ' ' . $row['model']) . '</h4>';
                    echo '<p>Start: ' . htmlspecialchars($row['start_date']) . '</p>';
                    echo '<p>End: ' . htmlspecialchars($row['end_date']) . '</p>';
                    echo '<span class="badge ' . $statusClass . '">' . ucfirst($row['status']) . '</span>';
                    echo '</div>';
                }
            } else {
                echo '<p style="text-align:center; grid-column:1/-1;">No current bookings available.</p>';
            }
            ?>
        </div>

        <h3 class="section-title">Rental History</h3>
        <div class="cards-container">
            <?php
            if (mysqli_num_rows($history) > 0) {
                while ($row = mysqli_fetch_assoc($history)) {
                    $statusClass = strtolower($row['status']) == 'active' ? 'badge-active' : 'badge-cancelled';
                    echo '<div class="card">';
                    echo '<h4>' . htmlspecialchars($row['make'] . ' ' . $row['model']) . '</h4>';
                    echo '<p>Period: ' . htmlspecialchars($row['start_date'] . ' to ' . $row['end_date']) . '</p>';
                    echo '<p>Total Cost: Rs. ' . htmlspecialchars($row['total_price']) . '</p>';
                    echo '<span class="badge ' . $statusClass . '">' . ucfirst($row['status']) . '</span>';
                    echo '</div>';
                }
            } else {
                echo '<p style="text-align:center; grid-column:1/-1;">No rental history available.</p>';
            }
            ?>
        </div>

    </div>
</body>

</html>