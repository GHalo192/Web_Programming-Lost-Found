
<?php

// 1. Khởi tạo Session và kết nối database
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../../db.php';

// 2. Chỉ cho phép HTTP GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);

    exit;
}

try {

    // 3. Nhận các tham số từ URL
    $keyword = trim($_GET['keyword'] ?? '');
    $type = trim($_GET['type'] ?? '');

    $category_id = (
        isset($_GET['category_id']) &&
        is_numeric($_GET['category_id'])
    ) ? (int)$_GET['category_id'] : '';

    $location_id = (
        isset($_GET['location_id']) &&
        is_numeric($_GET['location_id'])
    ) ? (int)$_GET['location_id'] : '';

    $page = max(1, (int)($_GET['page'] ?? 1));

    // 4. Thiết lập phân trang
    $limit = 5;
    $offset = ($page - 1) * $limit;

    // 5. Xây dựng điều kiện WHERE
    $conditions = ["Items.status = 'OPEN'"];

    $params = [];
    $types_str = "";

    if ($keyword !== '') {
        $conditions[] = "
            (Items.title LIKE ? OR Items.description LIKE ?)
        ";

        $searchTerm = "%" . $keyword . "%";

        $params[] = $searchTerm;
        $params[] = $searchTerm;

        $types_str .= "ss";
    }

    if ($type !== '') {

        if (!in_array($type, ['LOST', 'FOUND'], true)) {
            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Invalid item type"
            ]);

            exit;
        }

        $conditions[] = "Items.type = ?";

        $params[] = $type;
        $types_str .= "s";
    }

    if ($category_id !== '') {
        $conditions[] = "Items.category_id = ?";

        $params[] = $category_id;
        $types_str .= "i";
    }

    if ($location_id !== '') {
        $conditions[] = "Items.location_id = ?";

        $params[] = $location_id;
        $types_str .= "i";
    }

    $whereClause = "WHERE " . implode(" AND ", $conditions);

    // 6. Đếm tổng số bài đăng
    $total_sql = "
        SELECT COUNT(*) AS total
        FROM Items
        $whereClause
    ";

    $stmt_total = $conn->prepare($total_sql);

    if (!empty($params)) {
        $stmt_total->bind_param($types_str, ...$params);
    }

    $stmt_total->execute();

    $total_result = $stmt_total->get_result();

    $total_rows = (int)$total_result->fetch_assoc()['total'];

    $total_pages = (int)ceil($total_rows / $limit);

    // 7. Lấy danh sách bài đăng
    $sql = "
        SELECT
            Items.*,
            Categories.category_name,
            Locations.location_name,
            Users.fullname

        FROM Items

        LEFT JOIN Categories
            ON Items.category_id = Categories.category_id

        LEFT JOIN Locations
            ON Items.location_id = Locations.location_id

        LEFT JOIN Users
            ON Items.user_id = Users.user_id

        $whereClause

        ORDER BY Items.created_at DESC

        LIMIT ? OFFSET ?
    ";

    // Thêm LIMIT và OFFSET vào parameters
    $params_limit = $params;

    $params_limit[] = $limit;
    $params_limit[] = $offset;

    $types_str_limit = $types_str . "ii";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param($types_str_limit, ...$params_limit);

    $stmt->execute();

    $result = $stmt->get_result();

    // 8. Chuyển kết quả SQL thành PHP array
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // 9. Trả kết quả JSON
    http_response_code(200);

    echo json_encode([
        "success" => true,

        "data" => $data,

        "pagination" => [
            "current_page" => $page,
            "per_page" => $limit,
            "total_items" => $total_rows,
            "total_pages" => $total_pages
        ]

    ], JSON_UNESCAPED_UNICODE);

    // 10. Đóng statement
    $stmt->close();
    $stmt_total->close();

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
    ], JSON_UNESCAPED_UNICODE);

}

?>

