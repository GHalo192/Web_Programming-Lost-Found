
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

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

// Đọc dữ liệu JSON từ request
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ]);
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

// Kiểm tra CSRF token
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

// Kiểm tra dữ liệu đầu vào
if ($email === '' || $password === '') {
    respond(400, [
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ email và mật khẩu.'
    ]);
}

try {
    // Tìm tài khoản theo email
    $sql = "
        SELECT user_id, fullname, email, phone, role, password
        FROM Users
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    // Không tìm thấy tài khoản hoặc mật khẩu không đúng
    if (!$user || md5($password) !== $user['password']) {
        respond(401, [
            'success' => false,
            'message' => 'Email hoặc mật khẩu không chính xác.'
        ]);
    }

    // Đăng nhập thành công: tạo lại session ID
    session_regenerate_id(true);

    // Lưu thông tin người dùng vào Session
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['phone']    = $user['phone'];
    $_SESSION['role']     = $user['role'];

    // Không trả mật khẩu về client
    unset($user['password']);

    respond(200, [
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'data' => [
            'user' => $user
        ]
    ]);

} catch (Throwable $e) {
    error_log('Login API error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Đã xảy ra lỗi máy chủ.'
    ]);
}
