<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = isset($_SESSION['vendor_id']) ? intval($_SESSION['vendor_id']) : 0;

/* BIKE SEARCH */
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/* BIKES */
$bikes = [];
if ($vendor_id > 0) {

    $bike_query = "
    SELECT bikes.*, vendor_bikes.quantity 
    FROM vendor_bikes
    JOIN bikes ON bikes.id = vendor_bikes.bike_id
    WHERE vendor_bikes.vendor_id = $vendor_id
    AND bikes.is_deleted = 0
    AND vendor_bikes.is_deleted = 0
    ";

    if ($search != "") {
        $bike_query .= " AND (bikes.make LIKE '%$search%' 
                         OR bikes.model LIKE '%$search%')";
    }

    $bike_result = mysqli_query($conn, $bike_query);

    if ($bike_result)
        $bikes = $bike_result;
}

/* USERS */
$users = [];
if ($vendor_id > 0) {
    $user_query = "
        SELECT DISTINCT users.*
        FROM users
        JOIN bookings ON bookings.user_id = users.user_id
        WHERE bookings.vendor_id = $vendor_id
    ";
    $user_result = mysqli_query($conn, $user_query);
    if ($user_result)
        $users = $user_result;
}

/* BOOKINGS */
$bookings = [];
if ($vendor_id > 0) {
    $booking_query = "
        SELECT bookings.*, users.username, CONCAT(bikes.make,' ',bikes.model) AS bike_name
        FROM bookings
        JOIN users ON users.user_id = bookings.user_id
        JOIN bikes ON bikes.id = bookings.bike_id
        WHERE bookings.vendor_id = $vendor_id
    ";
    $booking_result = mysqli_query($conn, $booking_query);
    if ($booking_result)
        $bookings = $booking_result;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Moto Vendor Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        body {display:flex;background:#f4f4f9;color:#333;font-size:14px;}

        a {text-decoration:none;transition:0.3s;}

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #FF8800;
            padding: 30px 20px;
            position: fixed;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .sidebar h2 {text-align:center;margin-bottom:40px;}
        .menu {list-style:none;flex-grow:1;}
        .menu li {margin-bottom:20px;}
        .menu a {color:white;display:block;padding:10px 15px;border-radius:6px;font-weight:600;}
        .menu a:hover {background:#e06600;}

        /* Topbar */
        .topbar {
            position: fixed;
            left:250px;
            right:0;
            height:70px;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 30px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            z-index:5;
        }
        .topbar img {height:45px;margin-right:12px;}
        .top-title {display:flex;align-items:center;font-size:20px;font-weight:600;color:#FF8800;}
        .logout-btn {padding:10px 18px;background:#ff4444;border:none;border-radius:6px;color:white;cursor:pointer;transition:0.3s;}
        .logout-btn:hover {background:#e03333;}

        /* Content */
        .content {margin-left:250px;margin-top:80px;padding:30px;width:calc(100%-250px);}

        .section {margin-bottom:40px;}
        .section h3 {font-size:22px;color:#FF8800;margin-bottom:20px;border-left:5px solid #FF8800;padding-left:15px;}

        .search-box {padding:10px;border:1px solid #ccc;border-radius:6px;width:250px;margin-bottom:15px;}

        /* Table styling */
        table {width:100%;border-collapse:collapse;margin-top:15px;box-shadow:0 2px 8px rgba(0,0,0,0.05);}
        th,td {padding:12px;text-align:left;}
        th {background:#FF8800;color:white;}
        tr:nth-child(even){background:#f9f9f9;}
        tr:hover{background:#fff2e6;}
        .bike-image-box {width:100px;height:70px;display:flex;align-items:center;justify-content:center;background:#fafafa;border-radius:6px;overflow:hidden;}
        .bike-image-box img{width:100%;height:100%;object-fit:cover;border-radius:6px;}

        /* Buttons */
        .add-btn{background:#FF8800;color:white;padding:10px 20px;border-radius:6px;font-weight:600;display:inline-block;margin-bottom:15px;transition:0.3s;}
        .add-btn:hover{background:#e06600;}
        .btn-edit{background:#4CAF50;color:white;padding:6px 12px;border-radius:6px;transition:0.3s;}
        .btn-edit:hover{background:#45a049;}
        .btn-delete{background:#ff4444;color:white;padding:6px 12px;border-radius:6px;transition:0.3s;}
        .btn-delete:hover{background:#e03333;}

    </style>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this item?");
        }
    </script>
</head>

<body>

<div class="sidebar">
    <h2>Moto Vendor</h2>
    <ul class="menu">
        <li><a href="#bikes">Manage Bikes</a></li>
        <li><a href="#users">Manage Users</a></li>
        <li><a href="#bookings">Manage Bookings</a></li>
    </ul>
</div>

<div class="topbar">
    <div class="top-title">
        <img src="../Necessary Image/moto_yatra.png">
        Moto Vendor Dashboard
    </div>
    <a href="../Logout/logout.php"><button class="logout-btn">Logout</button></a>
</div>

<div class="content">

    <!-- Bikes Section -->
    <div class="section" id="bikes">
        <h3>Manage Your Bikes</h3>

        <form method="GET">
            <input type="text" name="search" class="search-box" placeholder="Search bikes by make or model..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <a href="Bikes/bikes_add.php" class="add-btn">+ Add Bike</a>

        <table>
            <tr>
                <th>Image</th>
                <th>Make</th>
                <th>Model</th>
                <th>Category</th>
                <th>CC</th>
                <th>Price/Day</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Condition</th>
                <th>Actions</th>
            </tr>
            <?php if($bikes && mysqli_num_rows($bikes) > 0): ?>
                <?php while($bike = mysqli_fetch_assoc($bikes)): ?>
                    <tr>
                        <td>
                            <div class="bike-image-box">
                                <?php
                                $imgPath = 'Bikes/uploads/' . $bike['image'];
                                if(!empty($bike['image']) && file_exists($imgPath)){
                                    echo '<img src="'.$imgPath.'">';
                                } else {
                                    echo '<img src="Bikes/uploads/no-image.png">';
                                }
                                ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($bike['make']); ?></td>
                        <td><?php echo htmlspecialchars($bike['model']); ?></td>
                        <td><?php echo ucfirst($bike['category']); ?></td>
                        <td><?php echo $bike['cc']; ?> cc</td>
                        <td>Rs <?php echo $bike['price_per_day']; ?></td>
                        <td><?php echo $bike['quantity']; ?></td>
                        <td><?php echo $bike['status']; ?></td>
                        <td><?php echo $bike['bike_condition']; ?></td>
                        <td>
                            <a href="Bikes/bikes_edit.php?id=<?php echo $bike['id']; ?>" class="btn-edit">Edit</a>
                            <a href="Bikes/bikes_delete.php?id=<?php echo $bike['id']; ?>" class="btn-delete"
                               onclick="return confirmDelete()">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" style="text-align:center;">No bikes found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Users Section -->
    <div class="section" id="users">
        <h3>Manage Users</h3>

        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php if($users && mysqli_num_rows($users) > 0): ?>
                <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <a href="Users/users_edit.php?id=<?php echo $u['user_id']; ?>" class="btn-edit">Edit</a>
                            <a href="Users/users_delete.php?id=<?php echo $u['user_id']; ?>" class="btn-delete"
                               onclick="return confirmDelete()">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center;">No users found for your bikes.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Bookings Section -->
    <div class="section" id="bookings">
        <h3>Bookings</h3>
        <a href="Bookings/booking_add.php" class="add-btn">+ Add Booking</a>

        <table>
            <tr>
                <th>User</th>
                <th>Bike</th>
                <th>Start</th>
                <th>End</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
            <?php if($bookings && mysqli_num_rows($bookings) > 0): ?>
                <?php while($b = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b['username']); ?></td>
                        <td><?php echo htmlspecialchars($b['bike_name']); ?></td>
                        <td><?php echo $b['start_date']; ?></td>
                        <td><?php echo $b['end_date']; ?></td>
                        <td>Rs <?php echo $b['total_price']; ?></td>
                        <td>
                            <a href="Bookings/bookings_edit.php?id=<?php echo $b['id']; ?>" class="btn-edit">Edit</a>
                            <a href="Bookings/bookings_delete.php?id=<?php echo $b['id']; ?>" class="btn-delete"
                               onclick="return confirmDelete()">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No bookings found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>