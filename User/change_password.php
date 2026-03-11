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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $query = mysqli_query($conn, "SELECT password FROM users WHERE user_id = $user_id LIMIT 1");
    $user = mysqli_fetch_assoc($query);

    if (!password_verify($current_password, $user['password'])) {
        $message = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match!";
    } else {
        // Hash new password and update
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE user_id=$user_id");
        $message = "Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password | MotoYatra</title>
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }

.back-link {
    display:block;
    margin-top:10px;
    font-size:0.9rem;
    color:#ff7a00;
    text-decoration:none;
}

.back-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="card">
    <h2>Change Password</h2>
    <p>Update your account password for better security.</p>

    <?php if($message): ?>
        <div class="message <?php echo strpos($message,'successfully')!==false?'success':'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" class="button">Change Password</button>
    </form>

    <a href="user_profile.php" class="back-link">Back to Profile</a>
</div>

</body>
</html>
