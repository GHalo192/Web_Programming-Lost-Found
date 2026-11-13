
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

function clearResetSession(): void {
    unset(
        $_SESSION['reset_user_id'],
        $_SESSION['reset_code_hash'],
        $_SESSION['reset_expiry'],
        $_SESSION['reset_attempts'],
        $_SESSION['reset_verified']
    );
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

$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Check whether code has been verified
if (
    empty($_SESSION['reset_user_id']) ||
    empty($_SESSION['reset_verified']) ||
    $_SESSION['reset_verified'] !== true
) {
    respond(403, [
        'success' => false,
        'message' => 'Bạn cần xác nhận mã trước khi đổi mật khẩu.'
    ]);
}

// Check expiration again
if (
    !isset($_SESSION['reset_expiry']) ||
    time() > $_SESSION['reset_expiry']
) {
    clearResetSession();

    respond(400, [
        'success' => false,
        'message' => 'Phiên đặt lại mật khẩu đã hết hạn.'
    ]);
}

// Validate password
if (!is_string($password) || !is_string($confirmPassword)) {
    respond(400, [
        'success' => false,
        'message' => 'Mật khẩu không hợp lệ.'
    ]);
}

if (strlen($password) < 8) {
    respond(400, [
        'success' => false,
        'message' => 'Mật khẩu phải có ít nhất 8 ký tự.'
    ]);
}

if ($password !== $confirmPassword) {
    respond(400, [
        'success' => false,
        'message' => 'Hai mật khẩu không khớp.'
    ]);
}

$userId = (int)$_SESSION['reset_user_id'];

// Keep MD5 to remain compatible with the current login/register system.
// IMPORTANT: Replace this with password_hash() when migrating all auth APIs.
$hashedPassword = md5($password);

$stmt = $conn->prepare(
    'UPDATE Users SET password = ? WHERE user_id = ?'
);

if (!$stmt) {
    respond(500, [
        'success' => false,
        'message' => 'Không thể chuẩn bị câu lệnh cập nhật.'
    ]);
}

$stmt->bind_param('si', $hashedPassword, $userId);

if (!$stmt->execute()) {
    $stmt->close();

    respond(500, [
        'success' => false,
        'message' => 'Không thể cập nhật mật khẩu.'
    ]);
}

$stmt->close();

// Reset code can no longer be reused
clearResetSession();

respond(200, [
    'success' => true,
    'message' => 'Đổi mật khẩu thành công.'
]);
