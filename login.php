<?php
session_start(); // Luôn đảm bảo dòng này nằm ở dòng đầu tiên của login.php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Lấy thông tin user từ CSDL
        $sql = "SELECT * FROM Users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                
                // =========================================================
                // CÁC DÒNG CẦN CHỈNH SỬA / BỔ SUNG TẠI ĐÂY
                // =========================================================
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['fullname'] = $user['fullname']; // Lưu Họ tên vào Session
                $_SESSION['email']    = $user['email'];    // Lưu Email vào Session
                $_SESSION['phone']    = $user['phone'] ?? ''; // Lưu SĐT vào Session (nếu có)
                // =========================================================

                // Chuyển hướng về trang chủ
                header('Location: index.php');
                exit();
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Email không tồn tại trong hệ thống!";
        }
        $stmt->close();
    } else {
        $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Campus Lost & Found</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: #ffffff;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <i class="fas fa-search-location fa-3x text-primary mb-2"></i>
        <h3 class="font-weight-bold">Đăng Nhập</h3>
        <p class="text-muted">Campus Lost & Found System</p>
    </div>

    <!-- Thông báo lỗi nếu đăng nhập thất bại -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center mb-3">
            <?php 
                if ($_GET['error'] == 'invalid') echo "Email hoặc mật khẩu không chính xác!";
                elseif ($_GET['error'] == 'empty') echo "Vui lòng nhập đầy đủ thông tin!";
            ?>
        </div>
    <?php endif; ?>

    <!-- FORM ĐĂNG NHẬP -->
    <form action="process-login.php" method="POST">
        <div class="form-group">
            <label for="email" class="font-weight-bold"><i class="fas fa-envelope mr-1"></i> Email sinh viên / Admin</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="student@campus.edu.vn" required>
        </div>

        <div class="form-group">
            <label for="password" class="font-weight-bold"><i class="fas fa-lock mr-1"></i> Mật khẩu</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block py-2 mt-4 font-weight-bold">
            <i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập
        </button>
    </form>

    <div class="text-center mt-4">
        <p class="mb-0">Chưa có tài khoản? <a href="register.php" class="text-primary font-weight-bold">Đăng ký ngay</a></p>
        <p class="mt-2"><a href="index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a></p>
    </div>
</div>

</body>
</html>