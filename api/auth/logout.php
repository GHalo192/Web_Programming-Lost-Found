
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// Hàm trả về JSON
function respond($success, $message, $statusCode = 200) {
    http_response_code($statusCode);

    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);

    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', 405);
}

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    respond(false, 'Bạn chưa đăng nhập', 401);
}

// Kiểm tra CSRF Token
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    empty($csrfToken) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    respond(false, 'Invalid CSRF token', 403);
}

// Lấy thông tin cookie session hiện tại
$cookieParams = session_get_cookie_params();

// Xóa cookie session khỏi trình duyệt
setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => $cookieParams['secure'],
    'httponly' => $cookieParams['httponly'],
    'samesite' => $cookieParams['samesite'] ?? 'Lax'
]);

// Xóa dữ liệu session
session_unset();
session_destroy();

// Trả kết quả về cho frontend
respond(true, 'Đăng xuất thành công');
