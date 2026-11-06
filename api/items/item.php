
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';

// --------------------------------------------------
// HELPER FUNCTIONS
// --------------------------------------------------

function apiRespond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

// Kiểm tra người dùng đã đăng nhập
function requireLogin(): int {
    if (!isset($_SESSION['user_id'])) {
        apiRespond(401, [
            'success' => false,
            'message' => 'Bạn cần đăng nhập.'
        ]);
    }

    $userId = (int) $_SESSION['user_id'];

    if ($userId <= 0) {
        apiRespond(401, [
            'success' => false,
            'message' => 'Invalid user session.'
        ]);
    }

    return $userId;
}

// Kiểm tra CSRF token
function verifyCsrf(): void {
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
}

// Xóa ảnh cũ nếu ảnh thuộc thư mục uploads/items
function deleteStoredImage(?string $imageUrl): void {

    if (!$imageUrl || !str_starts_with($imageUrl, 'uploads/items/')) {
        return;
    }

    // Chỉ sử dụng tên file, không cho phép path traversal
    $filename = basename($imageUrl);

    if ($filename === '') {
        return;
    }

    $filePath = __DIR__ . '/../../uploads/items/' . $filename;

    if (is_file($filePath)) {
        unlink($filePath);
    }
}

// Kiểm tra định dạng ngày
function validateDateTime(string $value): bool {

    $date = DateTime::createFromFormat('!Y-m-d H:i:s', $value);

    $errors = DateTime::getLastErrors();

    return $date !== false &&
        ($errors === false ||
        ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
}

// --------------------------------------------------
// AUTHENTICATION
// --------------------------------------------------

$userId = requireLogin();

// Xác định HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// PHP thường chỉ tự phân tích multipart/form-data cho POST.
// Vì vậy, POST + _method=PATCH được xem là PATCH.
if (
    $method === 'POST' &&
    isset($_POST['_method']) &&
    strtoupper($_POST['_method']) === 'PATCH'
) {
    $method = 'PATCH';
}

// Lấy item_id từ URL
$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($itemId === false || $itemId === null || $itemId <= 0) {
    apiRespond(400, [
        'success' => false,
        'message' => 'Invalid item ID.'
    ]);
}

// --------------------------------------------------
// GET: RETRIEVE ONE ITEM
// --------------------------------------------------

if ($method === 'GET') {

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
        WHERE Items.item_id = ?
          AND Items.user_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        apiRespond(500, [
            'success' => false,
            'message' => 'Database query failed.'
        ]);
    }

    $stmt->bind_param('ii', $itemId, $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();

    if (!$item) {
        apiRespond(404, [
            'success' => false,
            'message' => 'Không tìm thấy bài đăng hoặc bạn không có quyền truy cập.'
        ]);
    }

    // Lấy danh sách danh mục
    $categories = [];

    $categoryResult = $conn->query("
        SELECT category_id, category_name
        FROM Categories
        ORDER BY category_id
    ");

    if ($categoryResult) {
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    // Lấy danh sách khu vực
    $locations = [];

    $locationResult = $conn->query("
        SELECT location_id, location_name
        FROM Locations
        ORDER BY location_id
    ");

    if ($locationResult) {
        while ($row = $locationResult->fetch_assoc()) {
            $locations[] = $row;
        }
    }

    apiRespond(200, [
        'success' => true,
        'data' => $item,
        'categories' => $categories,
        'locations' => $locations
    ]);
}

// --------------------------------------------------
// PATCH: UPDATE ITEM
// --------------------------------------------------

if ($method === 'PATCH') {

    verifyCsrf();

    // Kiểm tra bài đăng có thuộc về người dùng không
    $checkStmt = $conn->prepare("
        SELECT image_url
        FROM Items
        WHERE item_id = ? AND user_id = ?
        LIMIT 1
    ");

    $checkStmt->bind_param('ii', $itemId, $userId);
    $checkStmt->execute();

    $existingItem = $checkStmt->get_result()->fetch_assoc();

    $checkStmt->close();

    if (!$existingItem) {
        apiRespond(404, [
            'success' => false,
            'message' => 'Không tìm thấy bài đăng hoặc bạn không có quyền chỉnh sửa.'
        ]);
    }

    // Đọc dữ liệu request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST;
    } else {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Invalid JSON body.'
            ]);
        }
    }

    // Chỉ nhận các trường được phép chỉnh sửa
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $type = trim($input['type'] ?? '');
    $categoryId = filter_var(
        $input['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $locationId = filter_var(
        $input['location_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $dateEvent = trim($input['date_event'] ?? '');
    $contactPhone = trim($input['contact_phone'] ?? '');
    $altContact = trim($input['alt_contact'] ?? '');

    // Kiểm tra các trường bắt buộc
    if (
        $title === '' ||
        $description === '' ||
        $contactPhone === '' ||
        $dateEvent === ''
    ) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Vui lòng điền đầy đủ các trường bắt buộc.'
        ]);
    }

    // Kiểm tra độ dài
    if (strlen($title) > 255) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Tiêu đề không được vượt quá 255 ký tự.'
        ]);
    }

    if (strlen($contactPhone) > 20) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Số điện thoại không được vượt quá 20 ký tự.'
        ]);
    }

    // Kiểm tra loại bài đăng
    if (!in_array($type, ['LOST', 'FOUND'], true)) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Invalid item type.'
        ]);
    }

    // Kiểm tra danh mục và khu vực
    if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Vui lòng chọn danh mục hợp lệ.'
        ]);
    }

    if ($locationId === false || $locationId === null || $locationId <= 0) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Vui lòng chọn khu vực hợp lệ.'
        ]);
    }

    // Chuyển datetime-local thành DATETIME của MySQL
    $dateEvent = str_replace('T', ' ', $dateEvent);

    if (strlen($dateEvent) === 16) {
        $dateEvent .= ':00';
    }

    if (!validateDateTime($dateEvent)) {
        apiRespond(400, [
            'success' => false,
            'message' => 'Ngày xảy ra sự việc không hợp lệ.'
        ]);
    }

    // NULL nếu không có liên hệ bổ sung
    $altContact = $altContact !== '' ? $altContact : null;

    // Mặc định giữ ảnh hiện tại
    $imageUrl = $existingItem['image_url'];

    $newImagePath = null;

    // --------------------------------------------------
    // HANDLE IMAGE UPLOAD
    // --------------------------------------------------

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Lỗi khi tải ảnh lên.'
            ]);
        }

        // Giới hạn 5 MB
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Ảnh không được vượt quá 5 MB.'
            ]);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Invalid uploaded file.'
            ]);
        }

        // Kiểm tra MIME type thực tế
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mimeType])) {
            apiRespond(400, [
                'success' => false,
                'message' => 'Chỉ chấp nhận ảnh JPG, PNG, GIF hoặc WEBP.'
            ]);
        }

        // Kiểm tra file có thực sự là ảnh
        if (getimagesize($file['tmp_name']) === false) {
            apiRespond(400, [
                'success' => false,
                'message' => 'File tải lên không phải hình ảnh hợp lệ.'
            ]);
        }

        // Tạo thư mục nếu chưa tồn tại
        $uploadDir = __DIR__ . '/../../uploads/items/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                apiRespond(500, [
                    'success' => false,
                    'message' => 'Không thể tạo thư mục lưu ảnh.'
                ]);
            }
        }

        // Tạo tên file ngẫu nhiên để tránh trùng tên
        try {
            $filename = bin2hex(random_bytes(16))
                . '.' . $allowedTypes[$mimeType];
        } catch (Throwable $e) {
            apiRespond(500, [
                'success' => false,
                'message' => 'Không thể tạo tên file.'
            ]);
        }

        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            apiRespond(500, [
                'success' => false,
                'message' => 'Không thể lưu ảnh.'
            ]);
        }

        $imageUrl = 'uploads/items/' . $filename;
        $newImagePath = $destination;
    }

    // --------------------------------------------------
    // UPDATE DATABASE
    // --------------------------------------------------

    $sql = "
        UPDATE Items
        SET
            title = ?,
            description = ?,
            type = ?,
            category_id = ?,
            location_id = ?,
            date_event = ?,
            contact_phone = ?,
            alt_contact = ?,
            image_url = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE item_id = ?
          AND user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        if ($newImagePath && is_file($newImagePath)) {
            unlink($newImagePath);
        }

        apiRespond(500, [
            'success' => false,
            'message' => 'Database query failed.'
        ]);
    }

    $stmt->bind_param(
        'sssiissssii',
        $title,
        $description,
        $type,
        $categoryId,
        $locationId,
        $dateEvent,
        $contactPhone,
        $altContact,
        $imageUrl,
        $itemId,
        $userId
    );

    if (!$stmt->execute()) {

        $stmt->close();

        if ($newImagePath && is_file($newImagePath)) {
            unlink($newImagePath);
        }

        apiRespond(500, [
            'success' => false,
            'message' => 'Không thể cập nhật bài đăng.'
        ]);
    }

    $stmt->close();

    // Chỉ xóa ảnh cũ sau khi database cập nhật thành công
    if ($newImagePath) {
        deleteStoredImage($existingItem['image_url']);
    }

    apiRespond(200, [
        'success' => true,
        'message' => 'Cập nhật bài đăng thành công.',
        'data' => [
            'item_id' => $itemId,
            'image_url' => $imageUrl
        ]
    ]);
}

// --------------------------------------------------
// DELETE: DELETE ITEM
// --------------------------------------------------

if ($method === 'DELETE') {

    verifyCsrf();

    // Kiểm tra quyền sở hữu và lấy ảnh
    $checkStmt = $conn->prepare("
        SELECT image_url
        FROM Items
        WHERE item_id = ? AND user_id = ?
        LIMIT 1
    ");

    $checkStmt->bind_param('ii', $itemId, $userId);
    $checkStmt->execute();

    $existingItem = $checkStmt->get_result()->fetch_assoc();

    $checkStmt->close();

    if (!$existingItem) {
        apiRespond(404, [
            'success' => false,
            'message' => 'Không tìm thấy bài đăng hoặc bạn không có quyền xóa.'
        ]);
    }

    $stmt = $conn->prepare("
        DELETE FROM Items
        WHERE item_id = ? AND user_id = ?
    ");

    $stmt->bind_param('ii', $itemId, $userId);

    if (!$stmt->execute()) {
        $stmt->close();

        apiRespond(500, [
            'success' => false,
            'message' => 'Không thể xóa bài đăng.'
        ]);
    }

    $stmt->close();

    // Xóa ảnh sau khi xóa bài đăng thành công
    deleteStoredImage($existingItem['image_url']);

    apiRespond(200, [
        'success' => true,
        'message' => 'Xóa bài đăng thành công.'
    ]);
}

// --------------------------------------------------
// METHOD NOT ALLOWED
// --------------------------------------------------

header('Allow: GET, PATCH, DELETE, POST');

apiRespond(405, [
    'success' => false,
    'message' => 'Method not allowed.'
]);
