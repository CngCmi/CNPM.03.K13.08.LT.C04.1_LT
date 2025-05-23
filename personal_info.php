<?php
session_start();
include 'config.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Vui lòng đăng nhập để chỉnh sửa thông tin cá nhân!");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_id, username, password, role, email, full_name, phone, avatar_url FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: login.php?error=Không tìm thấy thông tin người dùng!");
    exit();
}
$stmt->close();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $avatar_url = $user['avatar_url']; // Giữ giá trị cũ nếu không upload ảnh mới

    // Xử lý upload ảnh đại diện
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_size = $_FILES['avatar']['size'];
        $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        // Kiểm tra loại file và kích thước
        if (!in_array($file_type, $allowed_types)) {
            $error = "Chỉ chấp nhận file ảnh (JPEG, PNG, GIF)!";
        } elseif ($file_size > $max_file_size) {
            $error = "File ảnh quá lớn, tối đa 5MB!";
        } else {
            // Xóa ảnh cũ nếu có
            if ($avatar_url && file_exists($avatar_url)) {
                unlink($avatar_url);
            }
            // Di chuyển file vào thư mục uploads
            if (move_uploaded_file($file_tmp, $file_path)) {
                $avatar_url = $file_path;
            } else {
                $error = "Lỗi khi upload ảnh!";
            }
        }
    }

    // Cập nhật thông tin người dùng
    if (!isset($error)) {
        $sql = "UPDATE users SET password = ?, email = ?, full_name = ?, phone = ?, avatar_url = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $password, $email, $full_name, $phone, $avatar_url, $user_id);
        if ($stmt->execute()) {
            $success = "Cập nhật thông tin thành công!";
            // Cập nhật lại thông tin người dùng
            $sql = "SELECT user_id, username, password, role, email, full_name, phone, avatar_url FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Lỗi khi cập nhật thông tin: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân - Cửa Hàng 3M</title>
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
        .avatar-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; }
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

    <main class="flex-grow flex items-center justify-center py-8">
        <div class="form-container w-full max-w-md p-6">
            <h2 class="text-2xl font-semibold text-center mb-6">Thông Tin Cá Nhân</h2>

            <?php if (isset($success)): ?>
                <div class="text-green-600 text-center mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="text-red-600 text-center mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <label class="block text-sm font-medium text-gray-700">Ảnh Đại Diện</label>
                    <?php if ($user['avatar_url'] && file_exists($user['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="avatar-preview mx-auto mb-2">
                    <?php else: ?>
                        <p class="text-gray-500 mb-2">Chưa có ảnh đại diện</p>
                    <?php endif; ?>
                    <input type="file" name="avatar" accept="image/*" class="mt-1 w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tên Đăng Nhập (Không thể thay đổi)</label>
                    <p class="mt-1 p-2 w-full border border-gray-300 rounded-md"><?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mật Khẩu</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Họ và Tên</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Số Điện Thoại</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="input-field mt-1 w-full p-2 border border-gray-300 rounded-md">
                </div>
                <button type="submit" class="btn-primary w-full py-2 rounded-md text-white font-medium">Cập Nhật</button>
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