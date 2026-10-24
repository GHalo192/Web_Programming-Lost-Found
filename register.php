<?php
session_start();
// Nếu đã đăng nhập thì tự động chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Campus Lost & Found</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-card {
            width: 100%;
            max-width: 480px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: #ffffff;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <i class="fas fa-user-plus fa-3x text-primary mb-2"></i>
        <h3 class="font-weight-bold">Đăng Ký Tài Khoản</h3>
        <p class="text-muted">Tạo tài khoản sinh viên để đăng tin thất lạc</p>
    </div>

    <!-- Hiển thị thông báo lỗi nếu có -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center mb-3">
            <?php 
                if ($_GET['error'] == 'empty') echo "Vui lòng điền đầy đủ các trường thông tin!";
                elseif ($_GET['error'] == 'email_exists') echo "Email này đã được sử dụng!";
                elseif ($_GET['error'] == 'password_mismatch') echo "Mật khẩu nhập lại không khớp!";
                elseif ($_GET['error'] == 'failed') echo "Đăng ký thất bại, vui lòng thử lại!";
            ?>
        </div>
    <?php endif; ?>

    <!-- FORM ĐĂNG KÝ -->
    <form action="process-register.php" method="POST">
        
        <div class="form-group">
            <label for="fullname" class="font-weight-bold"><i class="fas fa-id-card mr-1"></i> Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Nguyễn Văn A" required>
        </div>

        <div class="form-group">
            <label for="email" class="font-weight-bold"><i class="fas fa-envelope mr-1"></i> Email sinh viên <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="student@campus.edu.vn" required>
        </div>

        <div class="form-group">
            <label for="phone" class="font-weight-bold"><i class="fas fa-phone mr-1"></i> Số điện thoại <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="0901234567" required>
        </div>

        <div class="form-group">
            <label for="password" class="font-weight-bold"><i class="fas fa-lock mr-1"></i> Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Nhập lại mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-success btn-block py-2 mt-4 font-weight-bold">
            <i class="fas fa-user-check mr-1"></i> Hoàn tất đăng ký
        </button>
    </form>

    <div class="text-center mt-4">
        <p class="mb-0">Đã có tài khoản? <a href="login.php" class="text-primary font-weight-bold">Đăng nhập ngay</a></p>
        <p class="mt-2"><a href="index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a></p>
    </div>
</div>

</body>
</html>