<?php
session_start();
include 'config.php';

if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php.");
}

if (!isset($_SESSION['khach_hang_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $ten_san_pham = $_POST['ten_san_pham'];
        $gia = floatval($_POST['gia']);
        $hinh_anh = $_POST['hinh_anh'];
        $mo_ta = $_POST['mo_ta'];

        $sql = "INSERT INTO Sanpham (ten_san_pham, gia, hinh_anh, mo_ta) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
        }
        $stmt->bind_param("sdss", $ten_san_pham, $gia, $hinh_anh, $mo_ta);
        $stmt->execute();
        if ($stmt) {
            $stmt->close();
        }
        header("Location: dashboard.php?success=Sản phẩm đã được thêm!");
        exit();
    } elseif (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $sql = "DELETE FROM Sanpham WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        if ($stmt) {
            $stmt->close();
        }
        header("Location: dashboard.php?success=Sản phẩm đã được xóa!");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .nav-link { transition: color 0.3s; }
        .nav-link:hover { color: #dc2626; }
        .dashboard-container { background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: #dc2626; transition: background 0.3s; }
        .btn-primary:hover { background: #b91c1c; }
        .about-us-content { background: #1f2937; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        footer { background: #111827; }
        .input-field { transition: border-color 0.3s; }
        .input-field:focus { border-color: #dc2626; outline: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="header py-4">
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
                    <li><a href="cart.php" class="text-white nav-link">Giỏ Hàng</a></li>
                    <li><a href="profile.php" class="text-white nav-link">Hồ Sơ</a></li>
                    <li><a href="dashboard.php" class="text-white nav-link">Bảng Điều Khiển</a></li>
                    <li>
                        <details class="relative">
                            <summary class="text-white cursor-pointer nav-link">Về Chúng Tôi</summary>
                            <div class="about-us-content absolute mt-2 p-4 w-64">
                                <p class="text-sm">Nhóm 11: Nguyễn Đức Cường, Trần Đình Thịnh, Nguyễn Trung Kiên, Hoàng Văn Định, Nguyễn Tùng Dương</p>
                            </div>
                        </details>
                    </li>
                    <li><a href="logout.php" class="text-white nav-link">Đăng Xuất</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-grow py-8">
        <div class="dashboard-container container mx-auto px-4 py-6">
            <h2 class="text-2xl font-semibold mb-4">Bảng Điều Khiển Quản Trị</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="text-green-600 text-sm text-center mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>
            <h3 class="text-xl font-semibold mb-2">Thêm Sản Phẩm</h3>
            <form method="POST" class="mb-6">
                <div class="mb-4">
                    <label for="ten_san_pham" class="block text-sm font-medium text-gray-700">Tên Sản Phẩm</label>
                    <input type="text" id="ten_san_pham" name="ten_san_pham" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="gia" class="block text-sm font-medium text-gray-700">Giá (VNĐ)</label>
                    <input type="number" id="gia" name="gia" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="hinh_anh" class="block text-sm font-medium text-gray-700">URL Hình Ảnh</label>
                    <input type="text" id="hinh_anh" name="hinh_anh" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="mo_ta" class="block text-sm font-medium text-gray-700">Mô Tả</label>
                    <textarea id="mo_ta" name="mo_ta" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md"></textarea>
                </div>
                <button type="submit" name="add_product" class="btn-primary py-2 px-4 rounded text-white">Thêm Sản Phẩm</button>
            </form>

            <h3 class="text-xl font-semibold mb-2">Danh Sách Sản Phẩm</h3>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4">Hình Ảnh</th>
                        <th class="py-2 px-4">Tên Sản Phẩm</th>
                        <th class="py-2 px-4">Giá</th>
                        <th class="py-2 px-4">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, ten_san_pham, gia, hinh_anh FROM Sanpham";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td class='py-2 px-4'><img src='{$row['hinh_anh']}' class='w-16 h-16 object-cover rounded'></td>
                                    <td class='py-2 px-4'>{$row['ten_san_pham']}</td>
                                    <td class='py-2 px-4'>" . number_format($row['gia'], 0, ',', '.') . " VNĐ</td>
                                    <td class='py-2 px-4'>
                                        <form method='POST' class='inline'>
                                            <input type='hidden' name='product_id' value='{$row['id']}'>
                                            <button type='submit' name='delete_product' class='text-red-600 hover:underline'>Xóa</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='py-2 px-4 text-center'>Không có sản phẩm nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="py-4 text-center text-white">
        <p class="text-sm">© 2025 Cửa Hàng Điện Tử 3M. Bảo lưu mọi quyền.</p>
    </footer>
</body>
</html>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>