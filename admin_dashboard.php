<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM products LIMIT 5");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - 3M Linh kiện Điện tử</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>3M Linh kiện Điện tử</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="#">Sản phẩm</a></li>
                    <li><a href="#footer" onclick="openAboutUs()">Về chúng tôi</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="dashboard-container">
            <h2>Quản trị Sản phẩm</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price'], 2); ?> VND</td>
                            <td><?php echo htmlspecialchars($product['stock']); ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="edit-btn">Sửa</a>
                                <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="remove-btn" onclick="return confirm('Bạn có chắc?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="add_product.php" class="add-btn">Thêm sản phẩm</a>
        </div>
    </main>

    <footer id="footer">
        <div class="footer-content">
            <p>© 2025 3M Linh kiện Điện tử. All rights reserved.</p>
            <details class="footer-about-us">
                <summary>Về chúng tôi</summary>
                <div class="footer-about-content">
                    <p>Sản phẩm được lấy ý tưởng từ web <a href="https://www.3m.com/" target="_blank">https://www.3m.com/</a>, được tạo bởi nhóm 11 với các thành viên:</p>
                    <ul>
                        <li>Mã sv: 20223868 - Nguyễn Đức Cường - Lớp: DCCNTT 13.10.20</li>
                        <li>Mã sv: 20223733 - Trần Đình Thịnh - Lớp: DCCNTT 13.10.20</li>
                        <li>Mã sv: 20223759 - Nguyễn Trung Kiên - Lớp: DCCNTT 13.10.20</li>
                        <li>Mã sv: 20223871 - Hoàng Văn Định - Lớp: DCCNTT 13.10.20</li>
                        <li>Mã sv: 20223839 - Nguyễn Tùng Dương - Lớp: DCCNTT 13.10.20</li>
                    </ul>
                </div>
            </details>
        </div>
    </footer>

    <script>
    function openAboutUs() {
        document.querySelector('.footer-about-us').open = true;
    }
    </script>
</body>
</html>