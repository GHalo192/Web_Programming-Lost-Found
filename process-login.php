<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Kiểm tra đầu vào rỗng
    if (empty($email) || empty($password)) {
        header('Location: login.php?error=empty');
        exit();
    }

    // Mã hóa MD5 mật khẩu nhập vào để so sánh với CSDL
    $hashed_password = md5($password);

    // Truy vấn kiểm tra tài khoản
    $sql  = "SELECT user_id, fullname, email, phone, role FROM Users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Đăng nhập thành công -> Lưu dữ liệu vào SESSION
        $user = $result->fetch_assoc();

        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['phone']    = $user['phone'];
        $_SESSION['role']     = $user['role']; // 'STUDENT' hoặc 'ADMIN'

        // Chuyển hướng về trang chủ
        header('Location: index.php');
        exit();
    } else {
        // Đăng nhập thất bại -> Chuyển về lại trang login báo lỗi
        header('Location: login.php?error=invalid');
        exit();
    }

    $stmt->close();
} else {
    header('Location: login.php');
    exit();
}

$conn->close();
?>