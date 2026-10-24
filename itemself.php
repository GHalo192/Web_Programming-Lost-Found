<?php
session_start();
require_once 'db.php'; // File kết nối CSDL

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập thì chuyển hướng về trang login
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// 2. Lấy tất cả bài đăng của CHÍNH NGƯỜI DÙNG NÀY (Bao gồm cả APPROVED, PENDING, RESOLVED, CLOSED)
$sql = "SELECT Items.*, Categories.category_name, Locations.location_name 
        FROM Items 
        LEFT JOIN Categories ON Items.category_id = Categories.category_id 
        LEFT JOIN Locations ON Items.location_id = Locations.location_id 
        WHERE Items.user_id = ? 
        ORDER BY Items.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My items</title>
	<link rel="stylesheet" href="fontawesome/css/all.min.css"> <!-- https://fontawesome.com/ -->
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet"> <!-- https://fonts.google.com/ -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!-- TemplateMo 553 Xtra Blog https://templatemo.com/tm-553-xtra-blog -->
</head>

<body>

    <!-- vv THIS IS THE SIDE BAR vv -->
	<header class="tm-header" id="tm-header">
        <div class="tm-header-wrapper">
            <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <!-- Logo -->
            <div class="tm-site-header">
                <div class="mb-3 mx-auto tm-site-logo"><i class="fas fa-times fa-2x"></i></div>            
                <h1 class="text-center">Campus Lost&Found</h1>
            </div>
            <!-- Menu -->

            <nav class="tm-nav" id="tm-nav">            
                <ul>
                    <li class="tm-nav-item"><a href="index.php" class="tm-nav-link">
                        <i class="fas fa-home"></i>Trang Chủ</a></li>
                    <li class="tm-nav-item active"><a href="itemself.php" class="tm-nav-link">
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

            <!-- vv This is the account name and gmail vv -->
            <div class="tm-mb-65 user-auth-box">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Khi người dùng ĐÃ đăng nhập -->
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
                    <!-- Khi người dùng CHƯA đăng nhập -->
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="login.php" class="btn btn-primary mr-0 mr-sm-2 mb-2 mb-sm-0">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="register.php" class="btn btn-outline-success">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ^^ This is the account name and gmail ^^ -->

            <h2 class="tm-mb-80 pr-5 text-white">
                Tìm lại đồ thất lạc trong Campus
            </h2>

            <p class="tm-mb-80 pr-5 text-white">Hệ thống hỗ trợ kết nối người nhặt được đồ và người làm mất đồ trong khuôn viên nhà trường.</p>
            
        </div>
    </header>
    <!-- ^^ THIS IS THE SIDE BAR ^^ -->

    <!-- vv search bar and posts vv -->
    <div class="container-fluid">
        <main class="tm-main">

            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="tm-color-primary"><i class="fas fa-user-edit"></i> Quản lý bài đăng của tôi</h2>
                    <p class="text-muted">Dưới đây là danh sách tất cả các tin báo mất / nhặt đồ do bạn tạo ra.</p>
                </div>
            </div>

            <!-- vv Search bar vv -->
            <div class="row tm-row mb-4">
                <div class="col-12">
                    <!-- Form Lọc & Tìm kiếm -->
                    <form method="GET" action="index.php" class="form-inline tm-search-form bg-white p-3 border rounded">
                        <div class="row w-100 g-2">
                            <!-- Từ khóa -->
                            <div class="col-md-3">
                                <input class="form-control w-100" type="text" name="keyword" placeholder="Nhập tên đồ vật, đặc điểm...">
                            </div>
                            <!-- Phân loại tin -->
                            <div class="col-md-2">
                                <select name="type" class="form-control w-100">
                                    <option value="">-- Loại tin --</option>
                                    <option value="LOST">Cần tìm đồ (Mất)</option>
                                    <option value="FOUND">Nhặt được đồ</option>
                                </select>
                            </div>
                            <!-- Danh mục -->
                            <div class="col-md-3">
                                <select name="category_id" class="form-control w-100">
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="1">Thiết bị điện tử</option>
                                    <option value="2">Giấy tờ tùy thân</option>
                                    <option value="3">Chìa khóa / Thiết bị mở khóa</option>
                                    <option value="4">Tài chính / vật phẩm giá trị</option>
                                    <option value="5">Vật dụng cá nhân</option>
                                    <option value="6">Đồ dùng y tế</option>
                                    <option value="7">Dụng cụ học tập</option>
                                    <option value="8">Khác</option>
                                </select>
                            </div>
                            <!-- Khu vực -->
                            <div class="col-md-2">
                                <select name="location_id" class="form-control w-100">
                                    <option value="">-- Khu vực --</option>
                                    <option value="1">Tòa A</option>
                                    <option value="2">Tòa B</option>
                                    <option value="3">Tòa C</option>
                                    <option value="4">Tòa D</option>
                                    <option value="5">Tòa E</option>
                                    <option value="6">Tòa F</option>
                                    <option value="7">Tòa G</option>
                                    <option value="8">Tòa M</option>
                                    <option value="9">Căng tin</option>
                                    <option value="10">Bãi xe</option>
                                    <option value="11">Bãi xe</option>
                                    <option value="12">Bãi xe</option>
                                    <option value="13">Sân vận động</option>
                                    <option value="14">Khác</option>
                                </select>
                            </div>
                            <!-- Nút bấm -->
                            <div class="col-md-2">
                                <button class="btn btn-outline-success w-100" type="submit">
                                    <i class="fas fa-search"></i> Lọc
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- ^^ Search bar ^^ -->

            <!-- vv Posts vv -->
            <div class="row tm-row">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <article class="col-12 col-md-6 tm-post mb-5">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between shadow-sm">
                                <div>
                                    <div class="position-relative mb-3">
                                        <!-- Ảnh bài đăng -->
                                        <img src="<?php echo !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'img/default.png'; ?>" alt="Image" class="img-fluid rounded" style="height: 200px; width: 100%; object-fit: cover;">            
                                        
                                        <!-- Badge Loại bài đăng (LOST / FOUND) -->
                                        <?php if($row['type'] == 'LOST'): ?>
                                            <span class="badge badge-danger p-2" style="position: absolute; top: 10px; left: 10px;">
                                                <i class="fas fa-exclamation-circle"></i> BÁO MẤT
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success p-2" style="position: absolute; top: 10px; left: 10px;">
                                                <i class="fas fa-hand-holding-heart"></i> NHẶT ĐƯỢC
                                            </span>
                                        <?php endif; ?>

                                        <!-- Badge Trạng thái hiện tại của bài viết -->
                                        <?php if($row['status'] == 'APPROVED'): ?>
                                            <span class="badge badge-primary p-2" style="position: absolute; top: 10px; right: 10px;">
                                                <i class="fas fa-eye"></i> ĐANG HIỂN THỊ
                                            </span>
                                        <?php elseif($row['status'] == 'PENDING'): ?>
                                            <span class="badge badge-warning p-2 text-white" style="position: absolute; top: 10px; right: 10px;">
                                                <i class="fas fa-hourglass-half"></i> CHỜ DUYỆT
                                            </span>
                                        <?php elseif($row['status'] == 'RESOLVED'): ?>
                                            <span class="badge badge-success p-2" style="position: absolute; top: 10px; right: 10px;">
                                                <i class="fas fa-check-circle"></i> ĐÃ TÌM THẤY
                                            </span>
                                        <?php elseif($row['status'] == 'CLOSED'): ?>
                                            <span class="badge badge-secondary p-2" style="position: absolute; top: 10px; right: 10px;">
                                                <i class="fas fa-lock"></i> ĐÃ ĐÓNG
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h4 class="tm-color-primary font-weight-bold"><?php echo htmlspecialchars($row['title']); ?></h4>
                                    
                                    <p class="small text-muted mt-2">
                                        <strong><i class="fas fa-map-marker-alt text-danger"></i> Địa điểm:</strong> <?php echo htmlspecialchars($row['location_name'] ?? 'Khác'); ?><br>
                                        <strong><i class="fas fa-clock text-info"></i> Ngày đăng:</strong> <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                    </p>
                                </div>

                                <!-- THAO TÁC / QUẢN LÝ BÀI ĐĂNG (ACTION BUTTONS) -->
                                <div class="pt-3 border-top mt-3">
                                    <div class="btn-group w-100 mb-2" role="group">
                                        <!-- Nút chỉnh sửa nội dung bài đăng -->
                                        <a href="edit_post.php?id=<?php echo $row['item_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>

                                        <!-- Nút cập nhật trạng thái đã tìm thấy -->
                                        <?php if($row['status'] == 'APPROVED'): ?>
                                            <a href="update_status.php?id=<?php echo $row['item_id']; ?>&status=RESOLVED" 
                                            class="btn btn-sm btn-outline-success" 
                                            onclick="return confirm('Xác nhận đã tìm thấy / trao trả thành công đồ vật này?');">
                                                <i class="fas fa-check"></i> Đã xong
                                            </a>
                                        <?php endif; ?>

                                        <!-- Nút xóa bài đăng -->
                                        <a href="delete_post.php?id=<?php echo $row['item_id']; ?>" 
                                        class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h4>Bạn chưa tạo bài đăng nào!</h4>
                        <a href="create_post.php" class="btn btn-primary mt-3">Tạo bài đăng ngay</a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- ^^ Posts ^^ -->



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
    <!-- ^^ search bar and posts ^^ -->


    <script src="custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>
</body>
</html>