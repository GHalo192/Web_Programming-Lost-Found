<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname         = trim($_POST['fullname'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // 1. Kiểm tra đầu vào rỗng
    if (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        header('Location: register.php?error=empty');
        exit();
    }

    // 2. Kiểm tra mật khẩu nhập lại có khớp không
    if ($password !== $confirm_password) {
        header('Location: register.php?error=password_mismatch');
        exit();
    }

    // 3. Kiểm tra Email đã tồn tại trong CSDL chưa
    $check_sql  = "SELECT user_id FROM Users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $check_stmt->close();
        header('Location: register.php?error=email_exists');
        exit();
    }
    $check_stmt->close();

    // 4. Mã hóa mật khẩu theo chuẩn MD5 (khớp với tài khoản mẫu)
    $hashed_password = md5($password);
    $role = 'STUDENT'; // Mặc định tài khoản tự đăng ký là Sinh viên

    // 5. Chèn tài khoản mới vào Bảng Users
    $insert_sql  = "INSERT INTO Users (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssss", $fullname, $email, $phone, $hashed_password, $role);

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        $conn->close();
        
        // Đăng ký thành công -> Thông báo và chuyển về trang Login
        echo "<script>
                alert('Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
                window.location.href = 'login.php';
              </script>";
        exit();
    } else {
        $insert_stmt->close();
        $conn->close();
        header('Location: register.php?error=failed');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
?>