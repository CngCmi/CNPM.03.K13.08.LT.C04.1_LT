<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO products (name, price, category, status, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $category, $status, $description]);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Cửa hàng linh kiện điện tử 3M</h1>
    </header>

    <main>
        <div class="add-product-form">
            <h2>Thêm sản phẩm mới</h2>
            <form method="POST">
                <label>Tên sản phẩm:</label>
                <input type="text" name="name" required>
                <label>Giá (VND):</label>
                <input type="number" name="price" step="0.01" required>
                <label>Danh mục:</label>
                <input type="text" name="category" required>
                <label>Trạng thái:</label>
                <select name="status">
                    <option value="Available">Có sẵn</option>
                    <option value="Unavailable">Hết hàng</option>
                </select>
                <label>Mô tả:</label>
                <textarea name="description" required></textarea>
                <button type="submit">Thêm</button>
            </form>
            <a href="index.php" class="add-to-cart">Quay lại</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Cửa hàng linh kiện điện tử 3M. All rights reserved.</p>
    </footer>
</body>
</html>