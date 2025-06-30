<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['product_id'])) {
    echo "<p>No product selected.</p>";
    exit();
}

$product_id = intval($_GET['product_id']);

$sql = "SELECT name, description, price FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p>Product not found.</p>";
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Payment -
        <?= htmlspecialchars($product['name']) ?>
    </title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <header>
        <h1>Payment</h1>
        <nav>
            <a href="home.php">Home</a> |
            <a href="dashboard.php">My Dashboard</a> |
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Product Details</h2>
        <p><strong>Name:</strong>
            <?= htmlspecialchars($product['name']) ?>
        </p>
        <p><strong>Description:</strong>
            <?= htmlspecialchars($product['description']) ?>
        </p>
        <p><strong>Price:</strong> R
            <?= number_format($product['price'], 2) ?>
        </p>

        <h2>Enter Payment Details</h2>
        <form action="payprocess.php" method="POST">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>" />

            <label for="card_number">Card Number:</label><br />
            <input type="text" id="card_number" name="card_number" required
                placeholder="1234 5678 9012 3456" /><br /><br />

            <label for="expiry_date">Expiry Date (MM/YY):</label><br />
            <input type="text" id="expiry_date" name="expiry_date" required placeholder="MM/YY" /><br /><br />

            <label for="cvv">CVV:</label><br />
            <input type="text" id="cvv" name="cvv" required placeholder="123" /><br /><br />

            <label for="address">Shipping Address:</label><br />
            <textarea id="address" name="address" rows="4" required
                placeholder="Enter shipping address"></textarea><br /><br />

            <button type="submit">Pay Now</button>
        </form>
    </main>

</body>

</html>

<?php $conn->close(); ?>