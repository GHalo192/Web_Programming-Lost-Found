
<?php

// 1. Khởi tạo Session và kết nối database
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../../db.php';

// Hàm trả về JSON response
function apiRespond(int $status, array $data): void {
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// ==================================================
// POST: CREATE NEW ITEM
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
        apiRespond(401, [
            'success' => false,
            'message' => 'Bạn cần đăng nhập để đăng bài.'
        ]);
    }

    $userId = (int)$_SESSION['user_id'];

    // 2. Kiểm tra CSRF token
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        !isset($_SESSION['csrf_token']) ||
        $token === '' ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        apiRespond(403, [
            'success' => false,
            'message' => 'Invalid CSRF token.'
        ]);
    }

    // 3. Lấy dữ liệu từ FormData
    $type = trim($_POST['type'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $locationId = filter_var(
        $_POST['location_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $dateInput = trim($_POST['date_event'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $altContact = trim($_POST['altcontact'] ?? '');

    // 4. Kiểm tra dữ liệu bắt buộc
    if (
        !in_array($type, ['LOST', 'FOUND'], true) ||
        $title === '' ||
        $description === '' ||
        $categoryId === false || $categoryId === null || $categoryId <= 0 ||
        $locationId === false || $locationId === null || $locationId <= 0 ||
        $phone === '' ||
        $dateInput === ''
    ) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Vui lòng kiểm tra lại thông tin bài đăng.'
        ]);
    }

    // 5. Chuyển datetime-local sang định dạng MySQL
    $dateEvent = str_replace('T', ' ', $dateInput);

    if (strlen($dateEvent) === 16) {
        $dateEvent .= ':00';
    }

    $date = DateTime::createFromFormat(
        '!Y-m-d H:i:s',
        $dateEvent
    );

    $dateErrors = DateTime::getLastErrors();

    if (
        $date === false ||
        ($dateErrors !== false &&
        ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    ) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Thời gian không hợp lệ.'
        ]);
    }

    // 6. Xử lý ảnh nếu người dùng tải lên
    $imageUrl = null;
    $uploadedFilePath = null;

    if (isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Không thể tải ảnh lên.'
            ]);
        }

        // Giới hạn ảnh 5 MB
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Ảnh không được vượt quá 5 MB.'
            ]);
        }

        // Kiểm tra MIME type thực tế của file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mimeType])) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.'
            ]);
        }

        // Tạo tên file ngẫu nhiên
        $extension = $allowedTypes[$mimeType];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $targetDir = __DIR__ . '/../../uploads/items/';

        if (
            !is_dir($targetDir) &&
            !mkdir($targetDir, 0755, true)
        ) {
            apiRespond(500, [
                'success' => false,
                'message' => 'Không thể tạo thư mục lưu ảnh.'
            ]);
        }

        $uploadedFilePath = $targetDir . $fileName;

        if (!move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadedFilePath
        )) {
            apiRespond(500, [
                'success' => false,
                'message' => 'Không thể lưu ảnh.'
            ]);
        }

        $imageUrl = 'uploads/items/' . $fileName;
    }

    // 7. Lưu bài đăng vào database
    // Status mặc định là OPEN theo cấu trúc bảng Items.
    $sql = "
        INSERT INTO Items (
            user_id,
            category_id,
            location_id,
            title,
            description,
            type,
            image_url,
            date_event,
            contact_phone,
            alt_contact
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        apiRespond(500, [
            'success' => false,
            'message' => 'Không thể chuẩn bị câu lệnh SQL.'
        ]);
    }

    $stmt->bind_param(
        'iiisssssss',
        $userId,
        $categoryId,
        $locationId,
        $title,
        $description,
        $type,
        $imageUrl,
        $dateEvent,
        $phone,
        $altContact
    );

    if (!$stmt->execute()) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        $stmt->close();

        apiRespond(500, [
            'success' => false,
            'message' => 'Không thể lưu bài đăng.'
        ]);
    }

    $newItemId = $stmt->insert_id;

    $stmt->close();

    // 8. Trả kết quả cho frontend
    apiRespond(201, [
        'success' => true,
        'message' => 'Đăng bài thành công!',
        'data' => [
            'item_id' => $newItemId,
            'status' => 'OPEN'
        ]
    ]);
}

// ==================================================
// GET: GET ITEM LIST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiRespond(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
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
    $limit = 6;
    $offset = ($page - 1) * $limit;

    // 5. Xây dựng điều kiện WHERE
    $status = trim($_GET['status'] ?? 'OPEN');

    if (!in_array($status, ['OPEN', 'CLOSED', 'RESOLVED'], true)) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Invalid item status.'
        ]);
    }

    $conditions = ["Items.status = ?"];

    $params = [$status];
    $types_str = "s";

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

