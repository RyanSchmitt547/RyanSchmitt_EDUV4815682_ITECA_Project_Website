<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $card = $_POST['cardNumber'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE products SET payment_status = 'paid' WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "Payment successful! Thank you.";
    } else {
        echo "Payment failed. Try again.";
    }
} else {
    echo "Invalid request.";
}
?>
