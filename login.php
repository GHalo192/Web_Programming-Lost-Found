<?php
session_start();

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
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
    <div
        id="loginMessage"
        class="alert alert-danger text-center mb-3"
        style="display: none;"
    ></div>

    <!-- FORM ĐĂNG NHẬP -->
    <form id="loginForm">
        <div class="form-group">
            <label for="email" class="font-weight-bold"><i class="fas fa-envelope mr-1"></i> Email sinh viên / Admin</label>
            <input type="email" 
            class="form-control" 
            id="email" 
            name="email" 
            placeholder="student@campus.edu.vn" 
            required>
        </div>

        <div class="form-group">
            <label for="password" class="font-weight-bold"><i class="fas fa-lock mr-1"></i> Mật khẩu</label>
            <input type="password" 
            class="form-control" 
            id="password" 
            name="password" 
            placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block py-2 mt-4 font-weight-bold">
            <i class="fas fa-sign-in-alt mr-1"></i> Đăng nhập
        </button>
    </form>

    <div class="text-center mt-4">
        <p class="mb-0">Chưa có tài khoản? <a href="register.php" class="text-primary font-weight-bold">Đăng ký ngay</a></p>
        <p class="mt-2"><a href="index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a></p>
        <p class="mt-2"><a href="forgot.php" class="text-primary">Quên mật khẩu?</a>
</p>
    </div>
</div>

<script>

const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');

const CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;

loginForm.addEventListener('submit', async function (event) {
    // Ngăn form gửi request theo cách truyền thống
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    // Ẩn thông báo lỗi cũ
    loginMessage.style.display = 'none';

    try {
        // Gửi thông tin đăng nhập đến REST API
        const response = await fetch('api/auth/login.php', {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },

            credentials: 'same-origin',

            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        // Đọc JSON từ API
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Đăng nhập thất bại.'
            );
        }

        // Đăng nhập thành công
        alert(result.message);

        // Chuyển về trang chủ
        window.location.href = 'index.php';

    } catch (error) {
        // Hiển thị lỗi từ API
        loginMessage.textContent = error.message;
        loginMessage.style.display = 'block';
    }
});
    
</script>


</body>
</html>