
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

// Đọc JSON từ request
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'success' => false,
        'message' => 'Dữ liệu gửi lên không hợp lệ.'
    ]);
}

// Lấy dữ liệu
$fullname = trim($input['fullname'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

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

// 1. Kiểm tra dữ liệu rỗng
if (
    $fullname === '' ||
    $email === '' ||
    $phone === '' ||
    $password === '' ||
    $confirm_password === ''
) {
    respond(400, [
        'success' => false,
        'message' => 'Vui lòng điền đầy đủ các trường thông tin.'
    ]);
}

// 2. Kiểm tra email hợp lệ
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, [
        'success' => false,
        'message' => 'Địa chỉ email không hợp lệ.'
    ]);
}

// 3. Kiểm tra mật khẩu nhập lại
if ($password !== $confirm_password) {
    respond(400, [
        'success' => false,
        'message' => 'Mật khẩu nhập lại không khớp.'
    ]);
}

try {
    // 4. Kiểm tra email đã tồn tại chưa
    $check_sql = "
        SELECT user_id
        FROM Users
        WHERE email = ?
        LIMIT 1
    ";

    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();

    $check_result = $check_stmt->get_result();
    $email_exists = $check_result->num_rows > 0;

    $check_stmt->close();

    if ($email_exists) {
        respond(409, [
            'success' => false,
            'message' => 'Email này đã được sử dụng.'
        ]);
    }

    // 5. Mã hóa mật khẩu theo định dạng hiện tại
    $hashed_password = md5($password);

    // Tài khoản đăng ký mới mặc định là STUDENT
    $role = 'STUDENT';

    // 6. Thêm tài khoản vào database
    $insert_sql = "
        INSERT INTO Users
            (fullname, email, phone, password, role)
        VALUES (?, ?, ?, ?, ?)
    ";

    $insert_stmt = $conn->prepare($insert_sql);

    $insert_stmt->bind_param(
        "sssss",
        $fullname,
        $email,
        $phone,
        $hashed_password,
        $role
    );

    $insert_stmt->execute();

    // Lấy ID của tài khoản vừa tạo
    $new_user_id = $conn->insert_id;

    $insert_stmt->close();

    // 7. Trả kết quả thành công
    respond(201, [
        'success' => true,
        'message' => 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.',
        'data' => [
            'user_id' => $new_user_id,
            'fullname' => $fullname,
            'email' => $email,
            'role' => $role
        ]
    ]);

} catch (Throwable $e) {
    error_log('Register API error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Đăng ký thất bại, vui lòng thử lại.'
    ]);
}
