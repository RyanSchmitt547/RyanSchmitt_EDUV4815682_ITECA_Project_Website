<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "<script>alert('Please fill all fields'); window.history.back();</script>";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already registered'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful'); window.location.href='login.html';</script>";
    } else {
        echo "<script>alert('Error during registration'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method'); window.history.back();</script>";
}
?>
