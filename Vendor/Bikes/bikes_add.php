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

$errors = [];

if (isset($_POST['add'])) {

    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $price = $_POST['price_per_day'];
    $status = $_POST['status'];
    $category = $_POST['category'];
    $cc = $_POST['cc'];
    $qty = $_POST['quantity'];
    $condition = $_POST['bike_condition'];

    /* VALIDATION */

    if (!preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $make)) {
        $errors[] = "Make must be 2-50 characters and contain only letters or numbers.";
    }

    if (!preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $model)) {
        $errors[] = "Model must be 2-50 characters and contain only letters or numbers.";
    }

    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Price per day must be a positive number.";
    }

    if (!is_numeric($cc) || $cc < 150 || $cc > 600) {
        $errors[] = "CC must be between 150 and 600.";
    }

    if (!is_numeric($qty) || $qty < 1) {
        $errors[] = "Quantity must be at least 1.";
    }

    /* IMAGE VALIDATION */

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $size = $_FILES['image']['size'];

    $allowed = ["jpg", "jpeg", "png", "webp"];
    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $errors[] = "Only JPG, JPEG, PNG or WEBP images allowed.";
    }

    if ($size > 2000000) {
        $errors[] = "Image size must be less than 2MB.";
    }

    /* IF NO ERRORS */

    if (empty($errors)) {

        $image_name = time() . "_" . $image;
        $upload_folder = "uploads/";

        move_uploaded_file($tmp, $upload_folder . $image_name);

        $sql = "INSERT INTO bikes (make, model, price_per_day, status, category, cc, bike_condition, image)
                VALUES ('$make','$model','$price','$status','$category','$cc','$condition','$image_name')";
        mysqli_query($conn, $sql);

        $bike_id = mysqli_insert_id($conn);

        $sql2 = "INSERT INTO vendor_bikes (vendor_id, bike_id, quantity)
                 VALUES ('$vendor_id','$bike_id','$qty')";
        mysqli_query($conn, $sql2);

        header("Location: ../vendor.php?success=added");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Bike</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Poppins, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-top: 7px solid #ff6600;
        }

        h2 {
            text-align: center;
            color: #ff6600;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #aaa;
            margin-bottom: 14px;
            background: #fafafa;
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
        }

        .btn:hover {
            background: #e25700;
        }

        .error {
            background: #ffd6d6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            color: #a00000;
        }

        .back-btn {
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Add New Bike</h2>

        <?php
        if (!empty($errors)) {
            foreach ($errors as $e) {
                echo "<div class='error'>$e</div>";
            }
        }
        ?>

        <form action="" method="POST" enctype="multipart/form-data">

            <label>Make</label>
            <input type="text" name="make" required pattern="[A-Za-z0-9 ]{2,50}">

            <label>Model</label>
            <input type="text" name="model" required pattern="[A-Za-z0-9 ]{2,50}">

            <label>Price Per Day</label>
            <input type="number" name="price_per_day" required min="1">

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
            <input type="file" name="image" required accept="image/*">

            <button class="btn" name="add">Add Bike</button>

            <a href="../vendor.php" class="back-btn">Back to Dashboard</a>

        </form>

    </div>

</body>

</html>