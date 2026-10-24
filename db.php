<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "campus_lost_and_found";

// Tạo kết nối MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

// Thiết lập bảng mã hiển thị tiếng Việt chuẩn
$conn->set_charset("utf8mb4");
?>