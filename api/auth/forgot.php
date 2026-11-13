<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../../db.php';

function respond(int $status, array $data): void {
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function validateCSRF(): void {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        respond(403, [
            'success' => false,
            'message' => 'CSRF token không hợp lệ.'
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    respond(405, [
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ.'
    ]);
}

validateCSRF();

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ]);
}

$email = trim($input['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, [
        'success' => false,
        'message' => 'Email không hợp lệ.'
    ]);
}

// Find user by email
$stmt = $conn->prepare(
    'SELECT user_id FROM Users WHERE email = ? LIMIT 1'
);

if (!$stmt) {
    respond(500, [
        'success' => false,
        'message' => 'Không thể xử lý yêu cầu.'
    ]);
}

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($userId);

$userExists = $stmt->fetch();
$stmt->close();

if (!$userExists) {
    respond(404, [
        'success' => false,
        'message' => 'Email này chưa được đăng ký.'
    ]);
}

// Tạo mã xác nhận
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code = '';

for ($i = 0; $i < 6; $i++) {
    $index = random_int(0, strlen($characters) - 1);
    $code .= $characters[$index];
}

// Hàm để xóa thông tin reset khỏi session
unset(
    $_SESSION['reset_user_id'],
    $_SESSION['reset_code_hash'],
    $_SESSION['reset_expiry'],
    $_SESSION['reset_attempts'],
    $_SESSION['reset_verified']
);

// Lưu thông tin reset
$_SESSION['reset_user_id'] = (int)$userId;
$_SESSION['reset_code_hash'] = hash('sha256', $code);
$_SESSION['reset_expiry'] = time() + 300;
$_SESSION['reset_attempts'] = 0;
$_SESSION['reset_verified'] = false;

// Gửi email chứa mã xác nhận (giả lập)
respond(200, [
    'success' => true,
    'message' => 'Tạo mã xác nhận thành công.',
    'data' => [
        'code' => $code,
        'expires_in' => 300
    ]
]);
