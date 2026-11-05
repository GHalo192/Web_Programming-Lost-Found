
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

// Hàm trả về JSON
function respond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Chỉ chấp nhận GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

    respond(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Lấy user_id từ URL
$profile_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (
    $profile_id === false ||
    $profile_id === null ||
    $profile_id <= 0
) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid user ID.'
    ]);
}

try {
    // 1. Lấy thông tin người dùng
    $user_sql = "
        SELECT
            user_id,
            fullname,
            email,
            created_at
        FROM Users
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param('i', $profile_id);
    $stmt->execute();

    $user_result = $stmt->get_result();
    $profile_user = $user_result->fetch_assoc();

    $stmt->close();

    if (!$profile_user) {
        respond(404, [
            'success' => false,
            'message' => 'User not found.'
        ]);
    }

    // 2. Lấy tất cả bài đăng của người dùng
    $items_sql = "
        SELECT
            Items.*,
            Categories.category_name,
            Locations.location_name
        FROM Items
        LEFT JOIN Categories
            ON Items.category_id = Categories.category_id
        LEFT JOIN Locations
            ON Items.location_id = Locations.location_id
        WHERE Items.user_id = ?
        ORDER BY Items.created_at DESC
    ";

    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param('i', $profile_id);
    $items_stmt->execute();

    $items_result = $items_stmt->get_result();

    $items = [];

    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }

    $items_stmt->close();
    $conn->close();

    // 3. Trả về JSON
    respond(200, [
        'success' => true,
        'data' => [
            'user' => $profile_user,
            'items' => $items,
            'total_items' => count($items)
        ]
    ]);

} catch (Throwable $e) {

    error_log('Get user profile error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'An internal server error occurred.'
    ]);
}
