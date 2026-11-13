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

if ($profile_id === false || $profile_id === null || $profile_id <= 0) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid user ID.'
    ]);
}

// XỬ LÝ THAM SỐ PHÂN TRANG
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page !== false && $page !== null && $page > 0) ? $page : 1;

$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
$limit = ($limit !== false && $limit !== null && $limit > 0) ? $limit : 6; // Mặc định 6 bài/trang

$offset = ($page - 1) * $limit;

try {
    // Lấy thông tin người dùng
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

    // ĐẾM TỔNG SỐ BÀI ĐĂNG (ĐỂ TÍNH TỔNG SỐ TRANG)
    $count_sql = "SELECT COUNT(*) as total FROM Items WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('i', $profile_id);
    $count_stmt->execute();
    $total_items = (int) $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();

    $total_pages = ceil($total_items / $limit);

    // LẤY BÀI ĐĂNG THEO TRANG (SỬ DỤNG LIMIT VÀ OFFSET)
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
        LIMIT ? OFFSET ?
    ";

    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param('iii', $profile_id, $limit, $offset);
    $items_stmt->execute();

    $items_result = $items_stmt->get_result();
    $items = [];

    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }

    $items_stmt->close();
    $conn->close();

    // TRẢ VỀ JSON KÈM ĐỐI TƯỢNG PAGINATION
    respond(200, [
        'success' => true,
        'data' => [
            'user' => $profile_user,
            'items' => $items,
            'total_items' => $total_items
        ],
        'pagination' => [
            'current_page' => $page,
            'total_pages' => (int) $total_pages,
            'limit' => $limit,
            'total_items' => $total_items
        ]
    ]);

} catch (Throwable $e) {
    error_log('Get user profile error: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'An internal server error occurred.'
    ]);
}