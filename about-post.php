<?php
session_start();
require_once 'db.php';

// 1. Lấy item_id từ URL (ví dụ: about-post.php?id=5)
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id <= 0) {
    echo "<script>alert('Bài đăng không tồn tại!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Truy vấn thông tin chi tiết của bài đăng kèm theo Danh mục, Địa điểm và Người đăng
$sql = "SELECT Items.*, Categories.category_name, Locations.location_name, Users.fullname, Users.user_id as user_id, Users.email as user_email
        FROM Items 
        LEFT JOIN Categories ON Items.category_id = Categories.category_id
        LEFT JOIN Locations ON Items.location_id = Locations.location_id
        LEFT JOIN Users ON Items.user_id = Users.user_id
        WHERE Items.item_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Không tìm thấy thông tin bài đăng!'); window.location.href='index.php';</script>";
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($post['title']); ?> - Campus Lost&Found</title>
	<link rel="stylesheet" href="fontawesome/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>

    <!-- SIDE BAR -->
	<header class="tm-header" id="tm-header">
        <div class="tm-header-wrapper">
            <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="tm-site-header">
                <div class="mb-3 mx-auto tm-site-logo"><i class="fas fa-info-circle fa-2x"></i></div>            
                <h1 class="text-center">Chi Tiết Bài Đăng</h1>
            </div>

            <nav class="tm-nav" id="tm-nav">            
                <ul>
                    <li class="tm-nav-item"><a href="index.php" class="tm-nav-link">
                        <i class="fas fa-home"></i>Trang Chủ</a></li>
                    <li class="tm-nav-item"><a href="itemself.php" class="tm-nav-link">
                        <i class="fas fa-folder"></i>Đồ của tôi</a></li>
                    <!-- Chỉ ADMIN được xem -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                        <li class="tm-nav-item"><a href="itemresolved.php" class="tm-nav-link">
                            <i class="fas fa-flag"></i>Đồ được đã tìm thấy</a></li>
                        <li class="tm-nav-item"><a href="itemclosed.php" class="tm-nav-link">
                            <i class="fas fa-flag"></i>Đồ được đã bị ẩn đi</a></li>
                    <?php endif; ?>
                    <li class="tm-nav-item"><a href="itempost.php" class="tm-nav-link">
                        <i class="fas fa-plus"></i>Báo nhặt / mất đồ</a></li>
                </ul>
            </nav>

            <div class="tm-mb-65 user-auth-box">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-profile-info p-3 mb-2 bg-light rounded text-dark">
                        <div class="font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Thành viên'); ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                        </div>
                    </div>
                    <a href="logout.php" class="btn btn-outline-danger btn-block btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                <?php else: ?>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="login.php" class="btn btn-primary mr-0 mr-sm-2 mb-2 mb-sm-0"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        <a href="register.php" class="btn btn-outline-success"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>

            <p class="tm-mb-80 pr-5 text-white">Xem thông tin chi tiết của đồ vật thất lạc hoặc nhặt được trong khuôn viên trường.</p>
        </div>
    </header>

    <!-- MAIN CONTENT CHI TIẾT BÀI ĐĂNG -->
    <div class="container-fluid">
        <main class="tm-main">
            <div class="row tm-row">
                <div class="col-12">
                    <hr class="tm-hr-primary">
                    <div class="mb-3">
                        <a href="index.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>

            <div class="row tm-row">
                <div class="col-12">
                    <!-- Khung hiển thị chi tiết bài đăng -->
                    <div class="p-4 bg-white border rounded shadow-sm">
                        
                        <!-- Tiêu đề & Trạng thái -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <h2 class="tm-color-primary font-weight-bold"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <div>
                                <?php if($post['type'] == 'LOST'): ?>
                                    <span class="badge badge-danger p-2" style="font-size: 1rem;">
                                        <i class="fas fa-exclamation-circle"></i> BÁO MẤT ĐỒ
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success p-2" style="font-size: 1rem;">
                                        <i class="fas fa-hand-holding-heart"></i> NHẶT ĐƯỢC ĐỒ
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Hình ảnh minh họa lớn -->
                        <div class="text-center mb-4">
                            <img src="<?php echo !empty($post['image_url']) ? $post['image_url'] : 'img/default.png'; ?>" alt="Item Image" class="img-fluid rounded" style="max-height: 450px; width: 100%; object-fit: contain; background: #f8f9fa;">
                        </div>

                        <!-- vv Thông tin vv -->
                        <div class="row">
                            <!-- Thông tin chi tiết bên trái -->
                            <div class="col-md-7">
                                <h4 class="tm-color-primary mb-3"><i class="fas fa-align-left mr-2"></i>Mô tả chi tiết</h4>
                                <p class="text-justify" style="white-space: pre-line; font-size: 1.1rem; line-height: 1.6;">
                                    <?php echo htmlspecialchars($post['description']); ?>
                                </p>
                            </div>

                            <!-- Thông tin sự việc & liên hệ bên phải -->
                            <div class="col-md-5 bg-light p-4 rounded border">
                                <h4 class="tm-color-primary mb-3"><i class="fas fa-info-circle mr-2"></i>Thông tin chung</h4>
                                <ul class="list-unstyled" style="font-size: 1.05rem; line-height: 2;">
                                    <li><strong><i class="fas fa-tag text-info mr-2"></i>Danh mục:</strong> <?php echo htmlspecialchars($post['category_name'] ?? 'Khác'); ?></li>
                                    <li><strong><i class="fas fa-map-marker-alt text-danger mr-2"></i>Địa điểm:</strong> <?php echo htmlspecialchars($post['location_name'] ?? 'Khác'); ?></li>
                                    <li><strong><i class="fas fa-clock text-warning mr-2"></i>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($post['date_event'])); ?></li>
                                    <li><strong><i class="fas fa-user text-primary mr-2"></i>Người đăng:</strong> <a href="about-user.php?id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['fullname'] ?? 'Thành viên'); ?></a></li>
                                    <li><strong><i class="fas fa-phone text-success mr-2"></i>Số điện thoại:</strong> <a href="tel:<?php echo htmlspecialchars($post['contact_phone']); ?>"><?php echo htmlspecialchars($post['contact_phone']); ?></a></li>
                                    <?php if(!empty($post['alt_contact'])): ?>
                                        <li><strong><i class="fas fa-comments text-secondary mr-2"></i>Liên lạc khác:</strong> <?php echo htmlspecialchars($post['alt_contact']); ?></li>
                                    <?php endif; ?>
                                    <li><strong><i class="fas fa-calendar-plus text-muted mr-2"></i>Ngày tạo bài:</strong> <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></li>
                                </ul>
                            </div>
                        </div>
                        <!-- ^^ Thông tin ^^ -->

                        <?php
                        $is_user = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
                        $is_admin  = isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'; // Kiểm tra quyền admin      
                        ?>
                            <!-- Nút chỉnh sửa và xóa (CHỈ XUẤT HIỆN VỚI CHÍNH CHỦ BÀI ĐĂNG) -->
                            <!-- NẾU LÀ CHỦ BÀI ĐĂNG (VÀ BÀI KHÔNG BỊ CLOSED) -->
                            <?php if ($is_user && $post['status'] !== 'CLOSED'): ?>
                                <div class="pt-3 border-top mt-3">
                                    <h6 class="text-muted mb-2"><i class="fas fa-user-cog"></i> Quản lý bài viết (Chủ bài đăng):</h6>
                                    <div class="btn-group w-100 mb-2" role="group">
                                        <a href="edit_post.php?id=<?php echo $post['item_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>

                                        <?php if($post['status'] == 'OPEN'): ?>
                                            <a href="update_status.php?id=<?php echo $post['item_id']; ?>&status=RESOLVED" 
                                            class="btn btn-sm btn-outline-success" 
                                            onclick="return confirm('Xác nhận đã tìm thấy / trao trả thành công đồ vật này?');">
                                                <i class="fas fa-check"></i> Đã xong (Resolved)
                                            </a>
                                        <?php endif; ?>

                                        <a href="delete_post.php?id=<?php echo $post['item_id']; ?>" 
                                        class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- NẾU LÀ ADMIN (QUYỀN QUẢN TRỊ VIÊN CẤP CAO) -->
                            <?php if ($is_admin): ?>
                                <div class="pt-3 border-top mt-3 bg-light p-3 rounded border">
                                    <h6 class="text-danger font-weight-bold mb-2"><i class="fas fa-user-shield"></i> Bảng điều khiển Admin:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <!-- Nút chuyển về OPEN -->
                                        <?php if ($post['status'] !== 'OPEN'): ?>
                                            <a href="update_status.php?id=<?php echo $post['item_id']; ?>&status=OPEN" 
                                            class="btn btn-sm btn-outline-warning mr-2 mb-1"
                                            onclick="return confirm('Đưa bài đăng này về trạng thái công khai (OPEN)?');">
                                                <i class="fas fa-folder-open"></i> Đổi thành OPEN
                                            </a>
                                        <?php endif; ?>

                                        <!-- Nút chuyển sang RESOLVED (Chỉ hiện nếu bài chưa bị CLOSED theo yêu cầu) -->
                                        <?php if ($post['status'] !== 'RESOLVED' && $post['status'] !== 'CLOSED'): ?>
                                            <a href="update_status.php?id=<?php echo $post['item_id']; ?>&status=RESOLVED" 
                                            class="btn btn-sm btn-outline-success mr-2 mb-1"
                                            onclick="return confirm('Đánh dấu bài viết này là Đã tìm thấy (RESOLVED)?');">
                                                <i class="fas fa-check-circle"></i> Đổi thành RESOLVED
                                            </a>
                                        <?php endif; ?>

                                        <!-- Nút chuyển sang CLOSED (Ẩn bài) -->
                                        <?php if ($post['status'] !== 'CLOSED'): ?>
                                            <a href="update_status.php?id=<?php echo $post['item_id']; ?>&status=CLOSED" 
                                            class="btn btn-sm btn-dark mr-2 mb-1"
                                            onclick="return confirm('Cảnh báo: Đưa bài viết này vào danh sách Đã ẩn (CLOSED)?');">
                                                <i class="fas fa-eye-slash"></i> Ẩn bài (CLOSED)
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                    </div>
                    <!-- ^^ Khung hiển thị chi tiết bài đăng ^^ -->
                </div>
            </div>

            <footer class="row tm-row">
                <hr class="col-12">
                <div class="col-md-6 col-12 tm-color-gray">
                    Design: <a rel="nofollow" target="_parent" href="https://templatemo.com" class="tm-external-link">TemplateMo</a>
                </div>
                <div class="col-md-6 col-12 tm-color-gray tm-copyright">
                    Copyright 2026 PNC
                </div>
            </footer>
        </main>
    </div>

    <script src="custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>
</body>
</html>