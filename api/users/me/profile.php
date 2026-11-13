<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../db.php';

// Hàm trả về JSON response
function respond(int $status, array $data): void {
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    respond(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// 1. Kiểm tra đăng nhập
if (empty($_SESSION['user_id'])) {
    respond(401, [
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'
    ]);
}

// 2. Kiểm tra CSRF token
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    empty($csrf_token) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    respond(403, [
        'success' => false,
        'message' => 'CSRF token không hợp lệ.'
    ]);
}

// 3. Đọc dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ]);
}

$fullname = trim($input['fullname'] ?? '');

if ($fullname === '') {
    respond(400, [
        'success' => false,
        'message' => 'Tên hiển thị không được để trống.'
    ]);
}

// Giới hạn 255 ký tự; điều chỉnh nếu cột fullname trong DB ngắn hơn.
$fullname_length = function_exists('mb_strlen')
    ? mb_strlen($fullname, 'UTF-8')
    : strlen($fullname);

if ($fullname_length > 255) {
    respond(400, [
        'success' => false,
        'message' => 'Tên hiển thị không được vượt quá 255 ký tự.'
    ]);
}

try {
    // 4. Chỉ cập nhật tài khoản đang đăng nhập
    $user_id = (int) $_SESSION['user_id'];

    $sql = "
        UPDATE Users
        SET fullname = ?
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $fullname, $user_id);
    $stmt->execute();
    $stmt->close();

    // 5. Cập nhật Session để tên mới xuất hiện ở sidebar
    $_SESSION['fullname'] = $fullname;

    $conn->close();

    respond(200, [
        'success' => true,
        'message' => 'Cập nhật tên hiển thị thành công.',
        'data' => [
            'fullname' => $fullname
        ]
    ]);

} catch (Throwable $e) {
    error_log('Update profile API error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Không thể cập nhật thông tin. Vui lòng thử lại.'
    ]);
}