
<?php
session_start();

// Nếu chưa đăng nhập thì chuyển về login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Tạo CSRF Token nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
</head>
<body>

    <p id="logoutMessage">Đang đăng xuất...</p>

    <script>
        const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;

        async function logout() {
            const message = document.getElementById('logoutMessage');

            try {
                const response = await fetch('api/auth/logout.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = 'login.php';
                } else {
                    message.textContent =
                        result.message || 'Không thể đăng xuất.';
                }

            } catch (error) {
                message.textContent =
                    'Có lỗi xảy ra khi kết nối đến máy chủ.';
                console.error(error);
            }
        }

        logout();
    </script>

</body>
</html>
