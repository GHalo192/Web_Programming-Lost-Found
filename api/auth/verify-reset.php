<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

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

$code = strtoupper(trim($input['code'] ?? ''));

if (!preg_match('/^[A-Z0-9]{6}$/', $code)) {
    respond(400, [
        'success' => false,
        'message' => 'Mã xác nhận phải gồm đúng 6 ký tự.'
    ]);
}

// Check whether reset information exists
if (
    empty($_SESSION['reset_user_id']) ||
    empty($_SESSION['reset_code_hash']) ||
    !isset($_SESSION['reset_expiry']) ||
    !isset($_SESSION['reset_attempts'])
) {
    respond(400, [
        'success' => false,
        'message' => 'Bạn chưa yêu cầu tạo mã xác nhận.'
    ]);
}

// Check expiration
if (time() > $_SESSION['reset_expiry']) {
    clearResetSession();

    respond(400, [
        'success' => false,
        'message' => 'Mã xác nhận đã hết hạn. Vui lòng tạo mã mới.'
    ]);
}

// Do not allow more than 5 attempts
if ($_SESSION['reset_attempts'] >= 5) {
    clearResetSession();

    respond(429, [
        'success' => false,
        'message' => 'Bạn đã nhập sai quá số lần cho phép. Hãy tạo mã mới.'
    ]);
}

// Count this attempt
$_SESSION['reset_attempts']++;

// Compare hashes
$inputHash = hash('sha256', $code);

if (!hash_equals($_SESSION['reset_code_hash'], $inputHash)) {

    if ($_SESSION['reset_attempts'] >= 5) {
        clearResetSession();

        respond(429, [
            'success' => false,
            'message' => 'Bạn đã nhập sai 5 lần. Vui lòng tạo mã mới.'
        ]);
    }

    respond(400, [
        'success' => false,
        'message' => 'Mã xác nhận không chính xác.'
    ]);
}


$_SESSION['reset_verified'] = true;

respond(200, [
    'success' => true,
    'message' => 'Xác nhận mã thành công.'
]);
