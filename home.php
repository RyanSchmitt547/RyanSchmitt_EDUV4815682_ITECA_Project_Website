<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT p.product_id, p.name, p.description, p.price, p.quantity, p.image, u.username 
        FROM products p 
        LEFT JOIN users u ON p.user_id = u.user_id
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Home - All Products</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<header>
    <h1>All Products</h1>
    <nav>
        <a href="home.php">Home</a> |
        <a href="dashboard.php">My Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (ZAR)</th>
                    <th>Quantity</th>
                    <th>Owner</th>
                    <th>Buy</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_id']) ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Product Image" style="max-width:100px; max-height:80px;">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>R<?= number_format($row['price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                    <td>
                        <form action="payment.php" method="GET" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['product_id']) ?>" />
                            <button type="submit" style="padding:5px 10px; cursor:pointer;">Buy Now</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</main>

</body>
</html>

<?php $conn->close(); ?>