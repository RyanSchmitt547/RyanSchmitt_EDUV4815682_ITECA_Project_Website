<?php
session_start();
require 'config.php';

// If already logged in as admin, redirect
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $stored_password, $role);
        $stmt->fetch();

        // For this project, you mentioned no hashing, so we use plain comparison
        if ($password === $stored_password && $role === 'admin') {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $message = "Invalid admin credentials.";
        }
    } else {
        $message = "Invalid admin credentials.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header>
        <h1>Admin Login</h1>
    </header>

    <main>
        <?php if ($message): ?>
            <p style="color:red; font-weight:bold;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="admin_login.php" method="post">
            <label>Username: <input type="text" name="username" required></label><br><br>
            <label>Password: <input type="password" name="password" required></label><br><br>
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>