<?php
session_start();
include 'config.php';

// Kiểm tra kết nối database
if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn lấy thông tin người dùng từ bảng users
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Kiểm tra mật khẩu (dạng plain text theo dữ liệu hiện tại)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php?success=Đăng nhập thành công!");
            exit();
        } else {
            $error = "Mật khẩu không đúng.";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại.";
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
    <title>Đăng Nhập - Cửa Hàng 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .nav-link { transition: color 0.3s, transform 0.3s; }
        .nav-link:hover { color: #dc2626; transform: scale(1.05); }
        .form-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .form-container:hover { transform: translateY(-5px); }
        .input-field { transition: border-color 0.3s, box-shadow 0.3s; border-radius: 10px; }
        .input-field:focus { border-color: #dc2626; outline: none; box-shadow: 0 0 5px rgba(220, 38, 38, 0.3); }
        .btn-primary { background: #dc2626; border-radius: 10px; transition: background 0.3s, transform 0.3s; }
        .btn-primary:hover { background: #b91c1c; transform: scale(1.05); }
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
                    <li><a href="cart.php" class="text-white nav-link">Giỏ Hàng</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php" class="text-white nav-link">Hồ Sơ</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
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

    <main class="flex-grow flex items-center justify-center py-8">
        <div class="form-container w-full max-w-md p-6">
            <h2 class="text-2xl font-semibold text-center mb-6">Đăng Nhập</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-500 text-sm text-center mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <button type="submit" class="btn-primary w-full py-2 rounded-md text-white font-medium">Đăng Nhập</button>
                <p class="text-center mt-4 text-sm">Chưa có tài khoản? <a href="register.php" class="text-blue-600 hover:underline">Đăng Ký</a></p>
            </form>
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