<?php
session_start();
include 'config.php';

if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = $_POST['ho_ten'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];

    $sql = "INSERT INTO Khachhang (ho_ten, username, so_dien_thoai, dia_chi, mat_khau, is_admin) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
    }
    $stmt->bind_param("sssss", $ho_ten, $username, $so_dien_thoai, $dia_chi, $password);
    if ($stmt->execute()) {
        header("Location: login.php?success=Đăng ký thành công! Vui lòng đăng nhập.");
        exit();
    } else {
        $error = "Tên đăng nhập đã tồn tại.";
    }
    if ($stmt) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .nav-link { transition: color 0.3s; }
        .nav-link:hover { color: #dc2626; }
        .form-container { background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .input-field { transition: border-color 0.3s; }
        .input-field:focus { border-color: #dc2626; outline: none; }
        .btn-primary { background: #dc2626; transition: background 0.3s; }
        .btn-primary:hover { background: #b91c1c; }
        .about-us-content { background: #1f2937; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        footer { background: #111827; }
        a.text-blue-600 { color: #dc2626 !important; }
        a.text-blue-600:hover { color: #b91c1c !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="header py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="logo">
                <h1 class="text-white text-2xl font-bold">Cửa Hàng 3M</h1>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="text-white nav-link">Trang Chủ</a></li>
                    <li><a href="cart.php" class="text-white nav-link">Giỏ Hàng</a></li>
                    <?php if (isset($_SESSION['khach_hang_id'])): ?>
                        <li><a href="profile.php" class="text-white nav-link">Hồ Sơ</a></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li><a href="dashboard.php" class="text-white nav-link">Bảng Điều Khiển</a></li>
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
                        <?php if (isset($_SESSION['khach_hang_id'])): ?>
                            <a href="logout.php" class="text-white nav-link">Đăng Xuất</a>
                        <?php else: ?>
                            <a href="login.php" class="text-white nav-link">Đăng Nhập</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center py-8">
        <div class="form-container w-full max-w-md p-6">
            <h2 class="text-2xl font-semibold text-center mb-6">Đăng Ký</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-500 text-sm text-center mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="ho_ten" class="block text-sm font-medium text-gray-700">Họ Tên</label>
                    <input type="text" id="ho_ten" name="ho_ten" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="so_dien_thoai" class="block text-sm font-medium text-gray-700">Số Điện Thoại</label>
                    <input type="text" id="so_dien_thoai" name="so_dien_thoai" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="dia_chi" class="block text-sm font-medium text-gray-700">Địa Chỉ</label>
                    <input type="text" id="dia_chi" name="dia_chi" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <button type="submit" class="btn-primary w-full py-2 rounded-md text-white font-medium">Đăng Ký</button>
                <p class="text-center mt-4 text-sm">Đã có tài khoản? <a href="login.php" class="text-blue-600 hover:underline">Đăng Nhập</a></p>
            </form>
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