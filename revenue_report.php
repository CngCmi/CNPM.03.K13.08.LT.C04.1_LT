<?php
session_start();
include 'config.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?error=Vui lòng đăng nhập với vai trò Admin!");
    exit();
}

// Kiểm tra kết nối database
if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php: " . mysqli_connect_error());
}

// Lấy báo cáo doanh thu theo ngày (sử dụng cột order_date mới)
$daily_report = [];
$sql = "SELECT DATE(order_date) as order_day, SUM(total_amount) as total FROM orders GROUP BY DATE(order_date)";
$result = $conn->query($sql);

if ($result === false) {
    $error = "Lỗi truy vấn SQL: " . $conn->error . ". Vui lòng kiểm tra bảng orders (đảm bảo có cột order_date và total_amount).";
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $daily_report[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Doanh Thu - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .nav-link { transition: color 0.3s, transform 0.3s; }
        .nav-link:hover { color: #dc2626; transform: scale(1.05); }
        .table-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .table-container:hover { transform: translateY(-5px); }
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
        <h2 class="text-2xl font-semibold text-center mb-6">Báo Cáo Doanh Thu</h2>

        <?php if (isset($error)): ?>
            <div class="text-red-600 text-center mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-container p-6">
            <h3 class="text-xl font-semibold mb-4">Doanh Thu Theo Ngày</h3>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Ngày</th>
                        <th class="p-2 border">Tổng Doanh Thu (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daily_report) > 0): ?>
                        <?php foreach ($daily_report as $report): ?>
                            <tr>
                                <td class="p-2 border"><?php echo htmlspecialchars($report['order_day']); ?></td>
                                <td class="p-2 border"><?php echo number_format($report['total'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="p-2 border text-center">Không có dữ liệu doanh thu.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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