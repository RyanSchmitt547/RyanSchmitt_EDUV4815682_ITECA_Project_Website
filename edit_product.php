<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    echo "Invalid product ID.";
    exit();
}

// Fetch existing product details
$stmt = $conn->prepare("SELECT name, description, price, quantity, image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "Product not found.";
    exit();
}

$stmt->bind_result($name, $description, $price, $quantity, $imagePath);
$stmt->fetch();
$stmt->close();

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    $newDesc = trim($_POST['description']);
    $newPrice = floatval($_POST['price']);
    $newQty = intval($_POST['quantity']);
    $updatedImagePath = $imagePath;

    // Handle new image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $imageName = basename($_FILES['product_image']['name']);
        $targetFile = $targetDir . time() . "_" . $imageName;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
            $updatedImagePath = $targetFile;
        }
    }

    // Update in DB
    $update = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity = ?, image = ? WHERE product_id = ?");
    $update->bind_param("ssdisi", $newName, $newDesc, $newPrice, $newQty, $updatedImagePath, $product_id);

    if ($update->execute()) {
        $_SESSION['message'] = "Product updated successfully.";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Failed to update product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<header>
    <h1>Edit Product</h1>
    <nav>
        <a href="admin_dashboard.php">Back to Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <form action="edit_product.php?id=<?= $product_id ?>" method="post" enctype="multipart/form-data">
        <label>Name: <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required></label><br><br>
        <label>Description: <textarea name="description" required><?= htmlspecialchars($description) ?></textarea></label><br><br>
        <label>Price (ZAR): <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" required></label><br><br>
        <label>Quantity: <input type="number" name="quantity" value="<?= htmlspecialchars($quantity) ?>" required></label><br><br>

        <?php if (!empty($imagePath)): ?>
            <p>Current Image:</p>
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="Product Image" style="max-width: 100px;"><br><br>
        <?php endif; ?>

        <label>Change Image: <input type="file" name="product_image"></label><br><br>

        <button type="submit">Update Product</button>
    </form>
</main>

</body>
</html>

<?php $conn->close(); ?>