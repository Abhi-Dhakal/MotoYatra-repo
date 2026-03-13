<?php
session_start();
require "connection.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

/* FETCH CATEGORIES */
$categories = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['add'])) {

    $make = $_POST['make'];
    $model = $_POST['model'];
    $price = $_POST['price_per_day'];
    $status = $_POST['status'];
    $category = $_POST['category'];
    $cc = $_POST['cc'];
    $qty = $_POST['quantity'];
    $condition = $_POST['bike_condition']; // new field

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    $upload_folder = "uploads/";
    move_uploaded_file($tmp, $upload_folder . $image);

    $sql = "INSERT INTO bikes (make, model, price_per_day, status, category, cc, bike_condition, image)
        VALUES ('$make', '$model', '$price', '$status', '$category', '$cc', '$condition', '$image')";
    mysqli_query($conn, $sql);

    $bike_id = mysqli_insert_id($conn);

    $sql2 = "INSERT INTO vendor_bikes (vendor_id, bike_id, quantity)
             VALUES ('$vendor_id', '$bike_id', '$qty')";
    mysqli_query($conn, $sql2);

    header("Location: ../vendor.php?success=added");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Bike</title>

    <style>
        body {
            font-family: Poppins, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 500px;
            margin: 40px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-top: 7px solid #ff6600;
        }

        h2 {
            text-align: center;
            color: #ff6600;
            font-size: 26px;
            margin-bottom: 20px;
            letter-spacing: .5px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #aaa;
            margin-bottom: 14px;
            font-size: 15px;
            background: #fafafa;
            transition: 0.3s;
        }

        input:focus,
        select:focus {
            border-color: #ff6600;
            background: #fff;
        }

        .btn {
            width: 100%;
            background: #ff6600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: 0.25s;
            font-weight: 600;
        }

        .btn:hover {
            background: #e25700;
        }

        .back-btn {
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
        }

        .back-btn:hover {
            color: #ff6600;
        }
    </style>

</head>

<body>

    <div class="container">
        <h2>Add New Bike</h2>

        <form action="" method="POST" enctype="multipart/form-data">

            <label>Make</label>
            <input type="text" name="make" required>

            <label>Model</label>
            <input type="text" name="model" required>

            <label>Price Per Day</label>
            <input type="number" name="price_per_day" required>

            <label>Status</label>
            <select name="status" required>
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
            </select>

            <label>Category</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
                    <option value="<?= htmlspecialchars($c['name']) ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php } ?>
            </select>

            <label>Condition</label>
            <select name="bike_condition" required>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
                <option value="Poor">Poor</option>
            </select>

            <label>CC</label>
            <input type="number" name="cc" min="150" max="600" required>

            <label>Quantity</label>
            <input type="number" name="quantity" required min="1">

            <label>Bike Image</label>
            <input type="file" name="image" required>

            <button class="btn" name="add">Add Bike</button>

            <a href="../vendor.php" class="back-btn">Back to Dashboard</a>
        </form>
    </div>

</body>

</html>