<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    try {
        // Kiểm tra username và email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            header("Location: register.php?error=Tên đăng nhập hoặc email đã tồn tại");
            exit();
        }

        // Thêm người dùng mới với mật khẩu không mã hóa
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'Customer')");
        $stmt->execute([$username, $password, $email]);

        header("Location: login.php?success=Đăng ký thành công, vui lòng đăng nhập");
        exit();
    } catch (PDOException $e) {
        header("Location: register.php?error=Lỗi truy cập cơ sở dữ liệu: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>