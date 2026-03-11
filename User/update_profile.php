<?php
session_start();
require "connection.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validate input
    if (empty($username) || empty($email)) {
        $message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    } else {
        // Check if email is already taken by another user
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email' AND user_id != $user_id LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $message = "Email is already in use by another account!";
        } else {
            // Update user info
            $update = mysqli_query($conn, "UPDATE users SET username='$username', email='$email' WHERE user_id=$user_id");
            if ($update) {
                $message = "Profile updated successfully!";
            } else {
                $message = "Failed to update profile. Try again.";
            }
        }
    }
}

// Fetch current user info
$query = mysqli_query($conn, "SELECT username, email FROM users WHERE user_id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Profile | MotoYatra</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f7f7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
        }

        h2 {
            color: #ff7a00;
            margin-bottom: 15px;
        }

        p {
            margin-bottom: 20px;
            color: #555;
            font-size: 0.95rem;
        }

        label {
            display: block;
            text-align: left;
            font-weight: 500;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
            color: #333;
        }

        input {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #ff7a00;
        }

        .button {
            width: 100%;
            background: #ff7a00;
            color: #fff;
            border: none;
            padding: 0.7rem;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .button:hover {
            background: #e96a00;
            transform: translateY(-1px);
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: block;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #ff7a00;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2>Update Profile</h2>
        <p>Change your username or email address.</p>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit" class="button">Update Profile</button>
        </form>

        <a href="user_profile.php" class="back-link">Back to Profile</a>
    </div>

</body>

</html>