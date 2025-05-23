<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "3m_electronics";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>