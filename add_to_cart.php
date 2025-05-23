<?php
session_start();
include 'config.php';

if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php.");
}

if (!isset($_SESSION['khach_hang_id'])) {
    header("Location: login.php");
    exit();
}
$khach_hang_id = $_SESSION['khach_hang_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $san_pham_id = intval($_POST['san_pham_id']);
    $so_luong = 1;

    $sql = "SELECT id FROM Giohang WHERE khach_hang_id = ? AND san_pham_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
    }
    $stmt->bind_param("ii", $khach_hang_id, $san_pham_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sql = "UPDATE Giohang SET so_luong = so_luong + 1 WHERE khach_hang_id = ? AND san_pham_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
        }
        $stmt->bind_param("ii", $khach_hang_id, $san_pham_id);
    } else {
        $sql = "INSERT INTO Giohang (khach_hang_id, san_pham_id, so_luong) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
        }
        $stmt->bind_param("iii", $khach_hang_id, $san_pham_id, $so_luong);
    }
    $stmt->execute();
    if ($stmt) {
        $stmt->close();
    }
    header("Location: cart.php?success=Sản phẩm đã được thêm vào giỏ hàng!");
    exit();
}
?>