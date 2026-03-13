<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit;
}

require "connection.php";

/* ADD CATEGORY */
if (isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);

    if ($name != "") {
        $name = mysqli_real_escape_string($conn, $name);

        $check = mysqli_query($conn, "SELECT * FROM categories WHERE name='$name'");

        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO categories(name) VALUES('$name')");
        }
    }
}

/* DELETE CATEGORY */
if (isset($_GET['delete_category'])) {
    $id = intval($_GET['delete_category']);
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
}

/* FETCH DATA */
$vendors = mysqli_query($conn, "SELECT * FROM vendors");
$users = mysqli_query($conn, "SELECT * FROM users where role ='user'");
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Vendor & User Management</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f7f7f7;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 25px;
            background: #FFA858;
            color: white;
        }

        .navbar img {
            height: 55px;
        }

        .logout-btn {
            background: #ff7b00;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
        }

        .container {
            max-width: 1150px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #ffeedf;
        }

        tr:hover {
            background: #fff7f0;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-blue {
            background: #007bff;
            color: white;
        }

        .btn-red {
            background: #d9534f;
            color: white;
        }

        .btn-orange {
            background: #FFA858;
            color: white;
        }
    </style>

</head>

<body>

    <div class="navbar">
        <img src="../Necessary Image/moto_yatra.png" alt="Logo">
        <h2>Admin Panel</h2>
        <a href="../Logout/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <h2>Vendor Management</h2>

        <a href="add_vendor.php">
            <button class="btn btn-orange">Add Vendor</button>
        </a>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>

            <?php while ($v = mysqli_fetch_assoc($vendors)) { ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['name']) ?></td>
                    <td><?= htmlspecialchars($v['email']) ?></td>
                    <td><?= htmlspecialchars($v['phone']) ?></td>
                    <td><?= htmlspecialchars($v['address']) ?></td>
                    <td>
                        <a href="edit_vendor.php?id=<?= $v['id'] ?>">
                            <button class="btn btn-blue">Edit</button>
                        </a>

                        <a href="delete_vendor.php?id=<?= $v['id'] ?>" onclick="return confirm('Delete vendor?');">
                            <button class="btn btn-red">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>


    <div class="container">
        <h2>User Management</h2>

        <a href="add_user.php">
            <button class="btn btn-orange">Add User</button>
        </a>

        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>

            <?php while ($u = mysqli_fetch_assoc($users)) { ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td>

                        <a href="edit_user.php?id=<?= $u['user_id'] ?>">
                            <button class="btn btn-blue">Edit</button>
                        </a>

                        <a href="delete_user.php?id=<?= $u['user_id'] ?>" onclick="return confirm('Delete user?');">
                            <button class="btn btn-red">Delete</button>
                        </a>

                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>


    <!-- CATEGORY MANAGEMENT -->

    <div class="container">
        <h2>Category Management</h2>

        <form method="POST" style="margin-bottom:20px;">

            <input type="text" name="category_name" placeholder="Enter category name" required
                style="padding:8px;border-radius:6px;border:1px solid #ccc;width:250px;">

            <button type="submit" name="add_category" class="btn btn-orange">
                Add Category
            </button>

        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Action</th>
            </tr>

            <?php while ($c = mysqli_fetch_assoc($categories)) { ?>

                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>

                    <td>
                        <a href="?delete_category=<?= $c['id'] ?>" onclick="return confirm('Delete this category?');">
                            <button class="btn btn-red">Delete</button>
                        </a>
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</body>

</html>