<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_SESSION['user_id'];

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    if ($name == "" || $description == "" || $price <= 0 || $quantity <= 0) {
        echo "Please fill all fields correctly.";
        exit();
    }
$imagePath = "";
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
$targetDir = "uploads/";
if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

$imageName = basename($_FILES['product_image']['name']);
$targetFile = $targetDir . time() . "_" . $imageName;

if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            echo "Image upload failed.";
            exit();
        }
    }
    $stmt = $conn->prepare("INSERT INTO products (user_id, name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issdis", $user_id, $name, $description, $price, $quantity, $imagePath);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product added successfully.";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Failed to add product.";
        header("Location: dashboard.php");
        exit();
    }

} else {
    header('Location: dashboard.php');
    exit();
}
?>
