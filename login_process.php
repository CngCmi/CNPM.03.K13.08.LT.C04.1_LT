<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=Thông tin đăng nhập không đúng");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=Lỗi truy cập cơ sở dữ liệu: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>