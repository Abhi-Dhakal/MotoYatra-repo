<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "vendor") {
    header("Location: ../Login/login.php");
    exit;
}

require "connection.php";

$vendor_id = $_SESSION['vendor_id'] ?? 0;
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    echo "<script>alert('Invalid User ID'); window.location='vendor.php';</script>";
    exit;
}

$check = mysqli_query($conn, "
    SELECT DISTINCT users.*
    FROM users
    JOIN bookings ON bookings.user_id = users.user_id
    WHERE users.user_id = $user_id AND bookings.vendor_id = $vendor_id
");

if (mysqli_num_rows($check) === 0) {
    echo "<script>alert('Access denied'); window.location='vendor.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc($check);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    $update = "UPDATE users SET username='$username', email='$email', password='$password' WHERE user_id=$user_id";
    if (mysqli_query($conn, $update)) {
        echo "<script>alert('User updated successfully'); window.location='../vendor.php';</script>";
        exit;
    } else {
        $error = "Failed to update user: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #FF6600;
            margin-bottom: 25px;
            text-align: center;
        }

        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }

        form input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        form button {
            background: #FF6600;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }

        form button:hover {
            background: #e25700;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Edit User</h2>
        <?php if (isset($error))
            echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                required>

            <label for="password">Password (leave blank to keep unchanged)</label>
            <input type="password" name="password" id="password">

            <button type="submit">Update User</button>
        </form>
        <a href="../vendor.php" style="color:#FF6600;">&larr; Back</a>
    </div>

</body>

</html>