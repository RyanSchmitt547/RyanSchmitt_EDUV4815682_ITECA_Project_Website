<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Please fill in all fields.";
        header("Location: login.html");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $_SESSION['user_id'] = $user_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        header("Location: login.html");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>
