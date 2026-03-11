<?php
require "connection.php";

$id = $_GET['id'];
$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id")
);

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query(
            $conn,
            "UPDATE users SET
             username='$username',
             email='$email',
             password='$hashed',
             role='$role'
             WHERE user_id=$id"
        );
    } else {
        mysqli_query(
            $conn,
            "UPDATE users SET
             username='$username',
             email='$email',
             role='$role'
             WHERE user_id=$id"
        );
    }
    header("Location: admin_vendor.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: Arial;
        }

        .wrapper {
            max-width: 450px;
            margin: 60px auto;
            background: white;
            padding: 25px;
            border-radius: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn-orange {
            background: #FFA858;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            width: 100%;
        }

        small {
            color: gray;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <h2>Edit User</h2>

        <form method="POST">
            <input type="text" name="username" value="<?= $user['username'] ?>" required>
            <input type="email" name="email" value="<?= $user['email'] ?>" required>

            <select name="role">
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
            </select>

            <input type="password" name="password" placeholder="New Password (optional)">
            <small>Leave blank to keep same password</small>

            <button class="btn-orange" name="submit">Update User</button>
        </form>
    </div>

</body>

</html>