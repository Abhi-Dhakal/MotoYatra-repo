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
    echo "<script>alert('Invalid User ID'); window.location='../vendor.php';</script>";
    exit;
}

/* Check access */
$check = mysqli_query($conn, "
SELECT DISTINCT users.*
FROM users
JOIN bookings ON bookings.user_id = users.user_id
WHERE users.user_id = $user_id
AND bookings.vendor_id = $vendor_id
");

if (mysqli_num_rows($check) === 0) {
    echo "<script>alert('Access denied'); window.location='../vendor.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc($check);
$error = "";

/* Update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password_input = trim($_POST['password']);

    /* Username validation */
    if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $error = "Username must be 3-20 characters (letters, numbers, underscore).";
    }

    /* Email validation */ elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }

    /* Check duplicate email */ else {
        $check_email = mysqli_query($conn, "
SELECT user_id FROM users
WHERE email='$email'
AND user_id!=$user_id
");

        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email already exists.";
        }
    }

    /* Password validation */
    if ($error == "" && !empty($password_input)) {
        if (strlen($password_input) < 6) {
            $error = "Password must be at least 6 characters.";
        }
    }

    if ($error == "") {

        if (!empty($password_input)) {
            $password = password_hash($password_input, PASSWORD_DEFAULT);
        } else {
            $password = $user['password'];
        }

        $update = "
UPDATE users SET
username='$username',
email='$email',
password='$password'
WHERE user_id=$user_id
";

        if (mysqli_query($conn, $update)) {

            echo "<script>
alert('User updated successfully');
window.location='../vendor.php';
</script>";

            exit;

        } else {

            $error = "Failed to update user: " . mysqli_error($conn);

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <title>Edit User</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Poppins, sans-serif;
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

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #FF6600;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #e25700;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        .back {
            color: #FF6600;
            text-decoration: none;
            font-weight: 500;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Edit User</h2>

        <?php
        if ($error != "") {
            echo "<p class='error'>$error</p>";
        }
        ?>

        <form method="post">

            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                pattern="[A-Za-z0-9_]{3,20}">

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label>Password (leave blank to keep unchanged)</label>
            <input type="password" name="password" minlength="6">

            <button type="submit">Update User</button>

        </form>

        <br>

        <a class="back" href="../vendor.php">Back to Dashboard</a>

    </div>

</body>

</html>