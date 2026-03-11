<?php
session_start();
require "../connection.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../../Login/login.php");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];
$error = "";

/* Fetch Users */
$users = mysqli_query($conn, "SELECT user_id, username FROM users WHERE role='user'");

/* Fetch Vendor Bikes */
$bikes = mysqli_query($conn, "
SELECT bikes.id, bikes.make, bikes.model, bikes.price_per_day
FROM vendor_bikes
JOIN bikes ON bikes.id = vendor_bikes.bike_id
WHERE vendor_bikes.vendor_id = $vendor_id
");

/* Insert Booking */
if (isset($_POST['submit'])) {

    $user_id = intval($_POST['user_id']);
    $bike_id = intval($_POST['bike_id']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $today = date("Y-m-d");

    /* Validation */

    if (empty($user_id) || empty($bike_id) || empty($start) || empty($end)) {
        $error = "All fields are required.";
    } elseif ($start < $today) {
        $error = "Start date cannot be in the past.";
    } elseif ($end < $start) {
        $error = "End date must be after start date.";
    } else {

        /* Get price */
        $price_query = mysqli_query($conn, "SELECT price_per_day FROM bikes WHERE id='$bike_id'");
        $price_row = mysqli_fetch_assoc($price_query);
        $price = $price_row['price_per_day'];

        /* Calculate days */
        $days = (strtotime($end) - strtotime($start)) / (60 * 60 * 24);

        if ($days <= 0) {
            $days = 1;
        }

        $total_price = $days * $price;

        /* Insert Booking */

        mysqli_query($conn, "
INSERT INTO bookings(user_id,bike_id,vendor_id,start_date,end_date,total_price)
VALUES('$user_id','$bike_id','$vendor_id','$start','$end','$total_price')
");

        header("Location: ../vendor.php");
        exit;

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Booking</title>

    <style>
        body {
            font-family: Poppins;
            background: #f4f4f9;
            padding: 40px;
        }

        .container {
            width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #FF8800;
            margin-bottom: 20px;
        }

        label {
            font-weight: 500;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #FF8800;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #e26f00;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Add Booking</h2>

        <?php
        if ($error != "") {
            echo "<div class='error'>$error</div>";
        }
        ?>

        <form method="POST" name="bookingForm" onsubmit="return validateForm()">

            <label>User</label>
            <select name="user_id" required>
                <option value="">Select User</option>

                <?php while ($u = mysqli_fetch_assoc($users)) { ?>

                    <option value="<?php echo $u['user_id']; ?>">
                        <?php echo htmlspecialchars($u['username']); ?>
                    </option>

                <?php } ?>

            </select>


            <label>Bike</label>
            <select name="bike_id" id="bike" required onchange="calculatePrice()">

                <option value="">Select Bike</option>

                <?php while ($b = mysqli_fetch_assoc($bikes)) { ?>

                    <option value="<?php echo $b['id']; ?>" data-price="<?php echo $b['price_per_day']; ?>">
                        <?php echo htmlspecialchars($b['make'] . " " . $b['model']); ?>
                        (Rs <?php echo $b['price_per_day']; ?>/day)
                    </option>

                <?php } ?>

            </select>


            <label>Start Date</label>
            <input type="date" name="start_date" id="start" required onchange="calculatePrice()">


            <label>End Date</label>
            <input type="date" name="end_date" id="end" required onchange="calculatePrice()">


            <label>Total Price</label>
            <input type="text" id="total_price" readonly placeholder="Total price will appear here">


            <button type="submit" name="submit">Create Booking</button>

        </form>

    </div>


    <script>

        /* Prevent Past Start Date */

        let today = new Date().toISOString().split('T')[0];
        document.getElementById("start").setAttribute("min", today);


        /* Price Calculator */

        function calculatePrice() {

            let bike = document.getElementById("bike");
            let start = document.getElementById("start").value;
            let end = document.getElementById("end").value;

            if (bike.selectedIndex === 0) {
                return;
            }

            let price = bike.options[bike.selectedIndex].dataset.price;

            if (start && end) {

                let startDate = new Date(start);
                let endDate = new Date(end);

                if (endDate < startDate) {
                    alert("End date must be after start date");
                    document.getElementById("end").value = "";
                    return;
                }

                let days = (endDate - startDate) / (1000 * 60 * 60 * 24);

                if (days <= 0) {
                    days = 1;
                }

                let total = days * price;

                document.getElementById("total_price").value = "Rs " + total;

            }

        }


        /* Final Form Validation */

        function validateForm() {

            let start = document.getElementById("start").value;
            let today = new Date().toISOString().split('T')[0];

            if (start < today) {
                alert("Start date cannot be in the past");
                return false;
            }

            return true;

        }

    </script>

</body>

</html>