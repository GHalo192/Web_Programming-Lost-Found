<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../db.php';

// Hàm trả về JSON và kết thúc chương trình
function respond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Chỉ cho phép GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, [
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

// Kiểm tra đăng nhập
if (
    !isset($_SESSION['user_id']) ||
    !is_numeric($_SESSION['user_id']) ||
    (int) $_SESSION['user_id'] <= 0
) {
    respond(401, [
        'success' => false,
        'message' => 'Bạn cần đăng nhập để xem bài đăng của mình.'
    ]);
}

$current_user_id = (int) $_SESSION['user_id'];

// Nhận các bộ lọc và trang hiện tại từ URL
$keyword = trim($_GET['keyword'] ?? '');
$type = trim($_GET['type'] ?? '');
$status = trim($_GET['status'] ?? '');

$category_id = (
    isset($_GET['category_id']) && $_GET['category_id'] !== ''
)
    ? filter_var($_GET['category_id'], FILTER_VALIDATE_INT)
    : null;

$location_id = (
    isset($_GET['location_id']) && $_GET['location_id'] !== ''
)
    ? filter_var($_GET['location_id'], FILTER_VALIDATE_INT)
    : null;

// Xử lý phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$limit = 6; // Số lượng bài đăng trên mỗi trang (khớp với cấu trúc giao diện cột bootstrap)
$offset = ($page - 1) * $limit;

// Kiểm tra dữ liệu đầu vào
if (
    $status !== '' &&
    !in_array($status, ['OPEN', 'RESOLVED', 'CLOSED'], true)
) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid status.'
    ]);
}

if ($type !== '' && !in_array($type, ['LOST', 'FOUND'], true)) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid type.'
    ]);
}

if (
    $category_id === false ||
    ($category_id !== null && $category_id < 1)
) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid category_id.'
    ]);
}

if (
    $location_id === false ||
    ($location_id !== null && $location_id < 1)
) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid location_id.'
    ]);
}

// Điều kiện bắt buộc: chỉ lấy bài đăng của tài khoản hiện tại
$conditions = ['Items.user_id = ?'];
$params = [$current_user_id];
$param_types = 'i';

// Lọc theo từ khóa
if ($keyword !== '') {
    $conditions[] = "(Items.title LIKE ? OR Items.description LIKE ?)";
    $search_term = '%' . $keyword . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

// Lọc theo loại tin
if ($type !== '') {
    $conditions[] = 'Items.type = ?';
    $params[] = $type;
    $param_types .= 's';
}

// Lọc theo trạng thái
if ($status !== '') {
    $conditions[] = 'Items.status = ?';
    $params[] = $status;
    $param_types .= 's';
}

// Lọc theo danh mục
if ($category_id !== null) {
    $conditions[] = 'Items.category_id = ?';
    $params[] = $category_id;
    $param_types .= 'i';
}

// Lọc theo khu vực
if ($location_id !== null) {
    $conditions[] = 'Items.location_id = ?';
    $params[] = $location_id;
    $param_types .= 'i';
}

$where = 'WHERE ' . implode(' AND ', $conditions);

// 1. Đếm tổng số lượng bản ghi thỏa mãn điều kiện để tính tổng số trang
$count_sql = "SELECT COUNT(*) AS total FROM Items $where";
$count_stmt = $conn->prepare($count_sql);

if ($count_stmt) {
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total'] ?? 0;
    $count_stmt->close();
} else {
    $total_items = 0;
}

$total_pages = $total_items > 0 ? ceil($total_items / $limit) : 1;

// 2. Lấy danh sách bài đăng có giới hạn phân trang (LIMIT & OFFSET)
$sql = "
    SELECT
        Items.*,
        Categories.category_name,
        Locations.location_name
    FROM Items
    LEFT JOIN Categories
        ON Items.category_id = Categories.category_id
    LEFT JOIN Locations
        ON Items.location_id = Locations.location_id
    $where
    ORDER BY Items.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    respond(500, [
        'success' => false,
        'message' => 'Database query failed.'
    ]);
}

// Ghim thêm tham số limit và offset vào câu truy vấn
$bind_types = $param_types . 'ii';
$bind_params = array_merge($params, [$limit, $offset]);

$stmt->bind_param($bind_types, ...$bind_params);

if (!$stmt->execute()) {
    $stmt->close();
    respond(500, [
        'success' => false,
        'message' => 'Could not retrieve items.'
    ]);
}

$result = $stmt->get_result();
$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();

// Trả kết quả JSON kèm theo cấu trúc pagination
respond(200, [
    'success' => true,
    'data' => $items,
    'total_items' => $total_items,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => (int)$total_pages,
        'limit' => $limit
    ]
]);