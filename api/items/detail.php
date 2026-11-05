
<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ chấp nhận GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Lấy item_id từ query string
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID bài đăng không hợp lệ'
    ]);
    exit;
}

// Lấy thông tin bài đăng cùng danh mục, địa điểm và người đăng
$sql = "
    SELECT
        Items.*,
        Categories.category_name,
        Locations.location_name,
        Users.fullname,
        Users.email AS user_email
    FROM Items
    LEFT JOIN Categories
        ON Items.category_id = Categories.category_id
    LEFT JOIN Locations
        ON Items.location_id = Locations.location_id
    LEFT JOIN Users
        ON Items.user_id = Users.user_id
    WHERE Items.item_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy bài đăng'
    ]);
    exit;
}

$post = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Trả về JSON
echo json_encode([
    'success' => true,
    'data' => $post
], JSON_UNESCAPED_UNICODE);
