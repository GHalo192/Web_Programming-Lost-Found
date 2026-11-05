
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

// Trả về JSON
function apiRespond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Chỉ chấp nhận PATCH
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    header('Allow: PATCH');

    apiRespond(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Kiểm tra đăng nhập
if (
    !isset($_SESSION['user_id']) ||
    !is_numeric($_SESSION['user_id']) ||
    (int) $_SESSION['user_id'] <= 0
) {
    apiRespond(401, [
        'success' => false,
        'message' => 'You must log in first.'
    ]);
}

$current_user_id = (int) $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) &&
            $_SESSION['role'] === 'ADMIN';

// Kiểm tra CSRF Token
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    empty($csrf_token) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    apiRespond(403, [
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);
}

// Lấy ID bài viết từ URL
$item_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if ($item_id === false || $item_id === null || $item_id <= 0) {
    apiRespond(400, [
        'success' => false,
        'message' => 'Invalid item ID.'
    ]);
}

// Đọc JSON request body
$raw_body = file_get_contents('php://input');
$body = json_decode($raw_body, true);

if (!is_array($body) || !isset($body['status'])) {
    apiRespond(400, [
        'success' => false,
        'message' => 'Missing status field.'
    ]);
}

// Kiểm tra trạng thái mới
$new_status = $body['status'];

$allowed_statuses = ['OPEN', 'RESOLVED', 'CLOSED'];

if (
    !is_string($new_status) ||
    !in_array($new_status, $allowed_statuses, true)
) {
    apiRespond(400, [
        'success' => false,
        'message' => 'Invalid status.'
    ]);
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Lấy thông tin bài viết và khóa dòng dữ liệu
    $stmt = $conn->prepare("
        SELECT item_id, user_id, status
        FROM Items
        WHERE item_id = ?
        FOR UPDATE
    ");

    $stmt->bind_param('i', $item_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();

    // Kiểm tra bài viết tồn tại
    if (!$item) {
        $conn->rollback();

        apiRespond(404, [
            'success' => false,
            'message' => 'Item not found.'
        ]);
    }

    $owner_id = (int) $item['user_id'];
    $current_status = $item['status'];

    $is_owner = ($owner_id === $current_user_id);

    // Kiểm tra quyền
    if ($is_admin) {

        // Admin có thể mở lại bài CLOSED thành OPEN
        $can_reopen = (
            $current_status === 'CLOSED' &&
            $new_status === 'OPEN'
        );

        // Admin có thể đóng bài viết chưa CLOSED
        $can_close = (
            $new_status === 'CLOSED' &&
            $current_status !== 'CLOSED'
        );

        // Nếu Admin cũng là chủ bài đăng,
        // Admin được sử dụng quyền quản lý như người dùng thường.
        $can_manage_own_item = (
            $is_owner &&
            $current_status !== 'CLOSED'
        );

        if (
            !$can_reopen &&
            !$can_close &&
            !$can_manage_own_item
        ) {
            $conn->rollback();

            apiRespond(403, [
                'success' => false,
                'message' => 'Admin is not allowed to perform this status change.'
            ]);
        }

    } else {

        // Người dùng thường chỉ được quản lý bài viết của mình
        if (!$is_owner) {
            $conn->rollback();

            apiRespond(403, [
                'success' => false,
                'message' => 'You do not have permission to update this item.'
            ]);
        }

        // Chủ bài viết không được thay đổi bài đã CLOSED
        if ($current_status === 'CLOSED') {
            $conn->rollback();

            apiRespond(409, [
                'success' => false,
                'message' => 'A closed item cannot change its status.'
            ]);
        }
    }

    // Không cần cập nhật nếu trạng thái không thay đổi
    if ($current_status === $new_status) {
        $conn->commit();

        apiRespond(200, [
            'success' => true,
            'message' => 'Item status is already up to date.',
            'data' => [
                'item_id' => (int) $item_id,
                'status' => $current_status
            ]
        ]);
    }

    // Cập nhật trạng thái
    $stmt = $conn->prepare("
        UPDATE Items
        SET status = ?
        WHERE item_id = ?
    ");

    $stmt->bind_param('si', $new_status, $item_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    apiRespond(200, [
        'success' => true,
        'message' => 'Item status updated successfully.',
        'data' => [
            'item_id' => (int) $item_id,
            'previous_status' => $current_status,
            'status' => $new_status
        ]
    ]);

} catch (Throwable $e) {

    $conn->rollback();

    error_log('Update item status error: ' . $e->getMessage());

    apiRespond(500, [
        'success' => false,
        'message' => 'An internal server error occurred.'
    ]);
}
