<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product_id'])) {
    $deleteId = intval($_POST['delete_product_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        $_SESSION['message'] = "Failed to delete product.";
    }
    header("Location: dashboard.php");
    exit();
}

$sql = "SELECT product_id, name, description, price, quantity, image FROM products WHERE user_id = ? ORDER BY product_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<header>
    <h1>My Dashboard</h1>
    <nav>
        <a href="home.php">Home</a> |
        <a href="dashboard.php">My Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <?php if (isset($_SESSION['message'])): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($_SESSION['message']) ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <h2>Your Products</h2>
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
                    <th>Actions</th>
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
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_product_id" value="<?= $row['product_id'] ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not uploaded any products yet.</p>
    <?php endif; ?>

    <h2>Add New Product</h2>
    <!-- Add enctype and image upload input -->
    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Description: <textarea name="description" required></textarea></label><br>
        <label>Price (ZAR): <input type="number" step="0.01" name="price" required></label><br>
        <label>Quantity: <input type="number" name="quantity" required></label><br>
        <label>Product Image: <input type="file" name="product_image" accept="image/*"></label><br>
        <button type="submit">Add Product</button>
    </form>
</main>

</body>
</html>

<?php $conn->close(); ?>
