<?php
session_start();
include 'config.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không có quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Employee'])) {
    header("Location: login.php?error=Vui lòng đăng nhập với vai trò Admin hoặc Employee!");
    exit();
}

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $description = $_POST['description'];

    $sql = "INSERT INTO products (name, price, image_url, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdss", $name, $price, $image_url, $description);
    if ($stmt->execute()) {
        $success = "Thêm sản phẩm thành công!";
    } else {
        $error = "Lỗi khi thêm sản phẩm: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $description = $_POST['description'];

    $sql = "UPDATE products SET name = ?, price = ?, image_url = ?, description = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $name, $price, $image_url, $description, $product_id);
    if ($stmt->execute()) {
        $success = "Sửa sản phẩm thành công!";
    } else {
        $error = "Lỗi khi sửa sản phẩm: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $success = "Xóa sản phẩm thành công!";
    } else {
        $error = "Lỗi khi xóa sản phẩm: " . $conn->error;
    }
    $stmt->close();
}

// Lấy danh sách sản phẩm
$products = [];
$sql = "SELECT product_id, name, price, image_url, description FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .nav-link { transition: color 0.3s, transform 0.3s; }
        .nav-link:hover { color: #dc2626; transform: scale(1.05); }
        .form-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .form-container:hover { transform: translateY(-5px); }
        .table-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .table-container:hover { transform: translateY(-5px); }
        .input-field { transition: border-color 0.3s, box-shadow 0.3s; border-radius: 10px; }
        .input-field:focus { border-color: #dc2626; outline: none; box-shadow: 0 0 5px rgba(220, 38, 38, 0.3); }
        .btn-primary { background: #dc2626; border-radius: 10px; transition: background 0.3s, transform 0.3s; }
        .btn-primary:hover { background: #b91c1c; transform: scale(1.05); }
        .btn-danger { background: #ef4444; border-radius: 10px; transition: background 0.3s, transform 0.3s; }
        .btn-danger:hover { background: #dc2626; transform: scale(1.05); }
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
        <h2 class="text-2xl font-semibold text-center mb-6">Quản Lý Sản Phẩm</h2>

        <?php if (isset($success)): ?>
            <div class="text-green-600 text-center mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="text-red-600 text-center mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form thêm sản phẩm -->
        <div class="form-container p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4">Thêm Sản Phẩm</h3>
            <form method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Tên Sản Phẩm</label>
                    <input type="text" id="name" name="name" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-700">Giá</label>
                    <input type="number" id="price" name="price" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="image_url" class="block text-sm font-medium text-gray-700">URL Hình Ảnh</label>
                    <input type="text" id="image_url" name="image_url" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Mô Tả</label>
                    <textarea id="description" name="description" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md"></textarea>
                </div>
                <button type="submit" name="add_product" class="btn-primary w-full py-2 rounded-md text-white font-medium">Thêm Sản Phẩm</button>
            </form>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="table-container p-6">
            <h3 class="text-xl font-semibold mb-4">Danh Sách Sản Phẩm</h3>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Tên</th>
                        <th class="p-2 border">Giá</th>
                        <th class="p-2 border">Hình Ảnh</th>
                        <th class="p-2 border">Mô Tả</th>
                        <th class="p-2 border">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="p-2 border"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="p-2 border"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td class="p-2 border"><img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Hình ảnh" class="w-16 h-16 object-cover"></td>
                            <td class="p-2 border"><?php echo htmlspecialchars($product['description']); ?></td>
                            <td class="p-2 border">
                                <!-- Form sửa sản phẩm -->
                                <form method="POST" class="inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                                    <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                                    <input type="hidden" name="description" value="<?php echo htmlspecialchars($product['description']); ?>">
                                    <button type="submit" name="edit_product" class="text-blue-600 hover:underline">Sửa</button>
                                </form>
                                |
                                <a href="manage_products.php?delete=<?php echo $product['product_id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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