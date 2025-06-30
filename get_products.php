<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$sql = "SELECT p.product_id, p.name, p.description, p.price, p.quantity, p.image, u.username 
        FROM products p
        JOIN users u ON p.user_id = u.user_id";

$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();