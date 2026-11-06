<?php

// Kiểm tra người dùng có truy cập trực tiếp db.php hay không
if (
    isset($_SERVER['SCRIPT_FILENAME']) &&
    realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)
) {
    http_response_code(403);
    exit("You weren't supposed to be here.");
}

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

