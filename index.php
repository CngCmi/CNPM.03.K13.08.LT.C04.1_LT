<?php
session_start();
include 'config.php';

// Kiểm tra kết nối database
if (!$conn) {
    die("Kết nối database thất bại. Vui lòng kiểm tra lại config.php: " . mysqli_connect_error());
}

// Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'Customer') {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];

    // Kiểm tra sản phẩm đã tồn tại trong giỏ chưa
    $sql_check = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $product_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $new_quantity = $row['quantity'] + 1;
        $sql_update = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $user_id, $product_id);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();
    header("Location: index.php?success=Sản phẩm đã được thêm vào giỏ hàng!");
    exit();
}

$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$products = [];

if ($search_query) {
    $sql = "SELECT product_id, name, price, image_url, description FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
    }
    $search_term = "%$search_query%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    if ($stmt) {
        $stmt->close();
    }
} else {
    $sql = "SELECT product_id, name, price, image_url, description FROM products";
    $result = $conn->query($sql);
    if ($result === false) {
        die("Lỗi truy vấn SQL: " . $conn->error);
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Điện Tử 3M</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(to right, #f3f4f6, #ffffff); }
        .header { background: #111827; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .nav-link { transition: color 0.3s, transform 0.3s; }
        .nav-link:hover { color: #dc2626; transform: scale(1.05); }
        .main-content { display: flex; flex-direction: row; min-height: calc(100vh - 200px); }
        .spacer { width: 20%; }
        .content-center { width: 60%; padding: 20px; }
        .product-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
        .product-card { background: white; padding: 16px; border-radius: 15px; transition: transform 0.3s, box-shadow 0.3s; }
        .product-card:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
        .offer-section { background: #f3f4f6; padding: 20px; border-radius: 15px; transition: transform 0.3s; margin-top: 20px; }
        .offer-section:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); }
        .about-us-content { background: #1f2937; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        .about-us-content p { color: white; }
        footer { background: #111827; border-radius: 10px; }
        .btn-primary { background: #dc2626; border-radius: 10px; transition: background 0.3s, transform 0.3s; }
        .btn-primary:hover { background: #b91c1c; transform: scale(1.05); }
        .search-btn { border-radius: 10px; transition: transform 0.3s; }
        .search-btn:hover { transform: scale(1.05); }
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
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo $search_query; ?>" class="p-2 rounded-l-md border border-gray-300 w-64">
                    <button type="submit" class="search-btn bg-red-600 text-white p-2 rounded-r-md hover:bg-red-700">Tìm Kiếm</button>
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

    <main class="flex-grow mx-4">
        <?php if ($success_message): ?>
            <div class="text-green-600 text-center mt-4"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="main-content">
            <div class="spacer"></div>

            <section class="content-center">
                <h2 class="text-2xl font-semibold mb-4 text-center">Sản Phẩm Nổi Bật</h2>
                <div class="product-grid">
                    <?php
                    if (count($products) > 0) {
                        foreach ($products as $row) {
                            echo "<div class='product-card'>
                                    <img src='{$row['image_url']}' alt='{$row['name']}' class='w-full h-32 object-cover mb-2 rounded'>
                                    <h3 class='text-lg font-medium'>{$row['name']}</h3>
                                    <p class='text-gray-600'>" . number_format($row['price'], 0, ',', '.') . " VNĐ</p>
                                    <p class='text-sm text-gray-500 mb-2'>{$row['description']}</p>";
                            // Chỉ hiển thị nút "Thêm Vào Giỏ" nếu người dùng là Customer và đã đăng nhập
                            if (isset($_SESSION['role']) && $_SESSION['role'] === 'Customer') {
                                echo "<form method='POST' style='display:inline;'>
                                        <input type='hidden' name='product_id' value='{$row['product_id']}'>
                                        <button type='submit' name='add_to_cart' class='btn-primary py-1 px-2 text-white text-sm'>Thêm Vào Giỏ</button>
                                      </form>";
                            } else {
                                echo "<p class='text-sm text-gray-500'>Đăng nhập để thêm vào giỏ hàng</p>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-center'>Không có sản phẩm nào.</p>";
                    }
                    ?>
                </div>

                <div class="offer-section text-center">
                    <h3 class="text-xl font-semibold mb-4">Ưu Đãi Đặc Biệt</h3>
                    <p>Nhận giảm 20% cho lần mua hàng đầu tiên! <a href='#' class='text-blue-600 hover:underline'>Mua Ngay</a></p>
                </div>
            </section>

            <div class="spacer"></div>
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