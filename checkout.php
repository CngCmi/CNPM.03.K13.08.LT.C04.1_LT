<?php
session_start();
include 'config.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải Customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: login.php?error=Vui lòng đăng nhập với vai trò Customer để thanh toán!");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin giỏ hàng
$cart_items = [];
$total = 0;
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, p.image_url, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['stock'] >= $row['quantity']) {
            $cart_items[] = $row;
            $total += $row['price'] * $row['quantity'];
        } else {
            $error = "Số lượng sản phẩm '" . $row['name'] . "' trong kho không đủ!";
        }
    }
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($error)) {
    $conn->begin_transaction();

    try {
        // Tạo đơn hàng
        $sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        // Thêm chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();

            // Giảm số lượng tồn kho
            $sql = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Xóa giỏ hàng
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: checkout.php?success=Thanh toán thành công!");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Lỗi khi thanh toán: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .nav-link { transition: color 0.3s, transform 0.3s; }
        .nav-link:hover { color: #dc2626; transform: scale(1.05); }
        .checkout-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .checkout-container:hover { transform: translateY(-5px); }
        .about-us-content { background: #1f2937; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        .about-us-content p { color: white; }
        footer { background: #111827; border-radius: 10px; }
        a.text-blue-600 { color: #dc2626 !important; }
        a.text-blue-600:hover { color: #b91c1c !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="header py-4 mx-4 mt-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="logo">
                    <h1 class="text-white text-2xl font-bold">Cửa Hàng 3M</h1>
                </div>
                <form method="GET" action="index.php" class="flex items-center">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." class="p-2 rounded-l-md border border-gray-300 w-64">
                    <button type="submit" class="bg-red-600 text-white p-2 rounded-r-md hover:bg-red-700">Tìm Kiếm</button>
                </form>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="text-white nav-link">Trang Chủ</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Customer'): ?>
                        <li><a href="cart.php" class="text-white nav-link">Giỏ Hàng</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="personal_info.php" class="text-white nav-link">Thông Tin Cá Nhân</a></li>
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Employee'])): ?>
                            <li><a href="manage_products.php" class="text-white nav-link">Quản Lý Sản Phẩm</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                            <li><a href="manage_users.php" class="text-white nav-link">Quản Lý Người Dùng</a></li>
                            <li><a href="revenue_report.php" class="text-white nav-link">Báo Cáo Doanh Thu</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li>
                        <details class="relative">
                            <summary class="text-white cursor-pointer nav-link">Về Chúng Tôi</summary>
                            <div class="about-us-content absolute mt-2 p-4 w-64">
                                <p class="text-sm">Nhóm 11: Nguyễn Đức Cường, Trần Đình Thịnh, Nguyễn Trung Kiên, Hoàng Văn Định, Nguyễn Tùng Dương</p>
                            </div>
                        </details>
                    </li>
                    <li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="logout.php" class="text-white nav-link">Đăng Xuất</a>
                        <?php else: ?>
                            <a href="login.php" class="text-white nav-link">Đăng Nhập</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-grow mx-4 py-8">
        <h2 class="text-2xl font-semibold text-center mb-6">Thanh Toán</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="text-green-600 text-center mb-4"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="text-red-600 text-center mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="checkout-container p-6">
            <?php if (empty($cart_items)): ?>
                <p class="text-center">Giỏ hàng của bạn trống. Vui lòng thêm sản phẩm trước khi thanh toán!</p>
            <?php else: ?>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 border">Sản phẩm</th>
                            <th class="p-2 border">Hình ảnh</th>
                            <th class="p-2 border">Giá</th>
                            <th class="p-2 border">Số lượng</th>
                            <th class="p-2 border">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td class="p-2 border"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="p-2 border"><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50"></td>
                                <td class="p-2 border"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                <td class="p-2 border"><?php echo $item['quantity']; ?></td>
                                <td class="p-2 border"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="p-2 border text-right">Tổng cộng:</td>
                            <td class="p-2 border"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
                <form method="POST" class="mt-4">
                    <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">Xác nhận thanh toán</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="py-4 text-center text-white mx-4 mb-4">
        <p class="text-sm">© 2025 Cửa Hàng Điện Tử 3M. Bảo lưu mọi quyền.</p>
    </footer>
</body>
</html>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>