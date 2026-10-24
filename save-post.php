<?php
session_start();
require_once 'db.php';

// 1. Bắt buộc người dùng phải đăng nhập mới được lưu bài
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thực hiện chức năng này!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy user_id và thông tin liên hệ trực tiếp từ SESSION tài khoản đang đăng nhập
    $user_id     = $_SESSION['user_id'];
    $phone       = $_SESSION['phone'];
    $email       = $_SESSION['email'];

    // Lấy dữ liệu từ Form
    $type        = $_POST['type'] ?? 'LOST';
    $title       = $_POST['title'] ?? '';
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $location_id = !empty($_POST['location_id']) ? intval($_POST['location_id']) : NULL;
    $date_event  = $_POST['date_event'] ?? date('Y-m-d H:i:s');
    $description = $_POST['description'] ?? '';

    // Xử lý Upload ảnh
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/items/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileExtension  = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $newFileName    = time() . "_" . uniqid() . "." . $fileExtension;
        $targetFilePath = $targetDir . $newFileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imageUrl = $targetFilePath;
        }
    }

    // Câu lệnh SQL thêm user_id vào bài đăng
    $sql = "INSERT INTO Items (user_id, title, description, type, image_url, date_event, phone, email, category_id, location_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')";

    $stmt = $conn->prepare($sql);
    
    // 10 tham số ("isssssssii": 1 int user_id, 7 string, 2 int)
    $stmt->bind_param("isssssssii", $user_id, $title, $description, $type, $imageUrl, $date_event, $phone, $email, $category_id, $location_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Đăng tin thành công!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "Lỗi thực thi CSDL: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>