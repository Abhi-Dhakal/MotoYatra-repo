<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'];
$bike_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bike_id <= 0) {
    echo "<script>alert('Invalid Bike ID'); window.location='../vendor.php';</script>";
    exit;
}

/* Fetch bike */
$query = "SELECT * FROM bikes WHERE id='$bike_id' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Bike not found'); window.location='../vendor.php';</script>";
    exit;
}

$bike = mysqli_fetch_assoc($result);

/* Fetch quantity */
$q_query = "SELECT quantity FROM vendor_bikes 
            WHERE vendor_id='$vendor_id' AND bike_id='$bike_id' LIMIT 1";

$q_result = mysqli_query($conn, $q_query);
$vendor_bike = mysqli_fetch_assoc($q_result);
$quantity = $vendor_bike ? $vendor_bike['quantity'] : 0;

/* Fetch categories */
$cat_query = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = mysqli_query($conn, $cat_query);

$error = "";

/* Update */
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $cc = intval($_POST['cc']);
    $price_per_day = floatval($_POST['price_per_day']);
    $quantity = intval($_POST['quantity']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $condition = mysqli_real_escape_string($conn, $_POST['bike_condition']);

    /* Validation */

    if (!preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $make)) {
        $error = "Make must be 2-50 characters (letters or numbers).";
    } elseif (!preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $model)) {
        $error = "Model must be 2-50 characters (letters or numbers).";
    } elseif ($cc < 150 || $cc > 600) {
        $error = "CC must be between 150 and 600.";
    } elseif ($price_per_day <= 0) {
        $error = "Price must be greater than 0.";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1.";
    } else {

        $upload_dir = "uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image_name = $bike['image'];

        /* Image Upload */

        if (!empty($_FILES['image']['name'])) {

            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, JPEG, PNG, WEBP images allowed.";
            } elseif ($file['size'] > 2000000) {
                $error = "Image must be less than 2MB.";
            } else {

                $image_name = time() . "_" . basename($file['name']);
                $target = $upload_dir . $image_name;

                move_uploaded_file($file['tmp_name'], $target);

            }

        }

        if ($error == "") {

            /* Update Bike */

            $update = "UPDATE bikes SET
make='$make',
model='$model',
category='$category',
cc='$cc',
price_per_day='$price_per_day',
status='$status',
bike_condition='$condition',
image='$image_name'
WHERE id='$bike_id'";

            if (mysqli_query($conn, $update)) {

                /* Update quantity */

                $check = mysqli_query($conn, "
SELECT * FROM vendor_bikes
WHERE vendor_id='$vendor_id'
AND bike_id='$bike_id'
");

                if (mysqli_num_rows($check) > 0) {

                    mysqli_query($conn, "
UPDATE vendor_bikes
SET quantity='$quantity'
WHERE vendor_id='$vendor_id'
AND bike_id='$bike_id'
");

                } else {

                    mysqli_query($conn, "
INSERT INTO vendor_bikes(vendor_id,bike_id,quantity)
VALUES('$vendor_id','$bike_id','$quantity')
");

                }

                echo "<script>
alert('Bike updated successfully');
window.location='../vendor.php';
</script>";

                exit;

            } else {
                $error = "Update failed: " . mysqli_error($conn);
            }

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Bike</title>

    <style>
        *{
            box-sizing: border-box;
        }
        
        body {
            font-family: Poppins;
            background: #f4f4f9;
            margin: 0;
        }

        .container {
            width: 600px;
            margin: auto;
            margin-top: 40px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #ff6600;
        }

        label {
            font-weight: 500;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #ff6600;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #e25700;
        }

        img {
            max-width: 150px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Edit Bike</h2>

        <?php
        if ($error != "") {
            echo "<p class='error'>$error</p>";
        }
        ?>

        <form method="post" enctype="multipart/form-data">

            <label>Make</label>
            <input type="text" name="make" value="<?php echo htmlspecialchars($bike['make']); ?>" required
                pattern="[A-Za-z0-9 ]{2,50}">

            <label>Model</label>
            <input type="text" name="model" value="<?php echo htmlspecialchars($bike['model']); ?>" required
                pattern="[A-Za-z0-9 ]{2,50}">

            <label>Category</label>
            <select name="category" required>

                <?php
                while ($cat = mysqli_fetch_assoc($cat_result)) {
                    $selected = ($bike['category'] == $cat['name']) ? "selected" : "";
                    echo "<option value='" . $cat['name'] . "' $selected>" . $cat['name'] . "</option>";
                }
                ?>

            </select>

            <label>Condition</label>
            <select name="bike_condition" required>
                <option value="Good" <?php if ($bike['bike_condition'] == "Good")
                    echo "selected"; ?>>Good</option>
                <option value="Fair" <?php if ($bike['bike_condition'] == "Fair")
                    echo "selected"; ?>>Fair</option>
                <option value="Poor" <?php if ($bike['bike_condition'] == "Poor")
                    echo "selected"; ?>>Poor</option>
            </select>

            <label>CC</label>
            <input type="number" name="cc" value="<?php echo $bike['cc']; ?>" min="150" max="600" required>

            <label>Price per Day (Rs)</label>
            <input type="number" name="price_per_day" value="<?php echo $bike['price_per_day']; ?>" min="1" required>

            <label>Quantity</label>
            <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" required>

            <label>Status</label>
            <select name="status">
                <option value="Available" <?php if ($bike['status'] == "Available")
                    echo "selected"; ?>>Available</option>
                <option value="Unavailable" <?php if ($bike['status'] == "Unavailable")
                    echo "selected"; ?>>Unavailable
                </option>
            </select>

            <label>Current Image</label><br>
            <img src="uploads/<?php echo $bike['image']; ?>">

            <label>Change Image</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Update Bike</button>

        </form>

        <br>

        <a href="../vendor.php">Back to Dashboard</a>

    </div>

</body>

</html>