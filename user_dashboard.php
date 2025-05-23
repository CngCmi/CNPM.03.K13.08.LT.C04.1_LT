<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Nhân viên') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$orders = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sản phẩm
$products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách khách hàng
$customers = $pdo->query("SELECT * FROM users WHERE role IN ('Khách hàng', 'Khách hàng tiềm năng')")->fetchAll(PDO::FETCH_ASSOC);

// Lấy KPI (giả lập)
$kpi = $pdo->query("SELECT * FROM kpi WHERE user_id = $user_id AND month = '2025-05'")->fetch(PDO::FETCH_ASSOC);
if (!$kpi) {
    $pdo->prepare("INSERT INTO kpi (user_id, month, orders_processed, revenue) VALUES (?, ?, ?, ?)")
        ->execute([$user_id, '2025-05', 10, 5000000]); // Giả lập
    $kpi = ['orders_processed' => 10, 'revenue' => 5000000];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Cửa hàng linh kiện điện tử 3M</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="employee_dashboard.php">Quản lý</a></li>
            <li><a href="profile.php">Tài khoản</a></li>
            <li><a href="logout.php">Đăng xuất</a></li>
        </ul>
    </nav>

    <main>
        <div class="dashboard">
            <h2>Dashboard Nhân viên</h2>
            <div class="tab-nav">
                <a href="#orders" class="active">Đơn hàng</a>
                <a href="#products">Sản phẩm</a>
                <a href="#customers">Khách hàng</a>
                <a href="#kpi">KPI</a>
            </div>

            <div id="orders" class="tab-content active">
                <h3>Quản lý đơn hàng</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['username']; ?></td>
                            <td><?php echo number_format($order['total'], 2); ?> VND</td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td><?php echo $order['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div id="products" class="tab-content">
                <h3>Quản lý sản phẩm</h3>
                <form class="search-bar" method="GET">
                    <input type="text" name="product_search" placeholder="Tìm kiếm theo ID hoặc tên...">
                    <button type="submit">Tìm kiếm</button>
                </form>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Giá</th>
                        <th>Danh mục</th>
                    </tr>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo number_format($product['price'], 2); ?> VND</td>
                            <td><?php echo $product['category']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div id="customers" class="tab-content">
                <h3>Quản lý khách hàng</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                    </tr>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo $customer['user_id']; ?></td>
                            <td><?php echo $customer['username']; ?></td>
                            <td><?php echo $customer['email']; ?></td>
                            <td><?php echo $customer['role']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div id="kpi" class="tab-content">
                <h3>KPI tháng 5/2025</h3>
                <p>Số đơn hàng xử lý: <?php echo $kpi['orders_processed']; ?></p>
                <p>Doanh thu: <?php echo number_format($kpi['revenue'], 2); ?> VND</p>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2025 Cửa hàng linh kiện điện tử 3M. All rights reserved.</p>
    </footer>

    <script>
        const tabs = document.querySelectorAll('.tab-nav a');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                document.querySelector(tab.getAttribute('href')).classList.add('active');
            });
        });
    </script>
</body>
</html>