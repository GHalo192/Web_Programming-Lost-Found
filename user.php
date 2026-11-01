<?php
session_start();
require_once 'db.php';

// 1. Lấy user_id từ URL (ví dụ: about.php?id=3)
$profile_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

if ($profile_id <= 0) {
    echo "<script>alert('Không tìm thấy người dùng!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Truy vấn thông tin chi tiết của người dùng được chọn
$user_sql = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    echo "<script>alert('Người dùng không tồn tại!'); window.location.href='index.php';</script>";
    exit;
}
$profile_user = $user_result->fetch_assoc();
$stmt->close();

// 3. Truy vấn tất cả các bài đăng của người dùng này
$items_sql = "SELECT Items.*, Categories.category_name, Locations.location_name 
              FROM Items 
              LEFT JOIN Categories ON Items.category_id = Categories.category_id
              LEFT JOIN Locations ON Items.location_id = Locations.location_id
              WHERE Items.user_id = ? 
              ORDER BY Items.created_at DESC";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $profile_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Trang cá nhân - <?php echo htmlspecialchars($profile_user['fullname']); ?></title>
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
                <div class="mb-3 mx-auto tm-site-logo"><i class="fas fa-user-shield fa-2x"></i></div>            
                <h1 class="text-center">Hồ Sơ Thành Viên</h1>
            </div>

            <nav class="tm-nav" id="tm-nav">            
                <ul>
                    <li class="tm-nav-item"><a href="index.php" class="tm-nav-link"><i class="fas fa-home"></i>Trang Chủ</a></li>
                    <li class="tm-nav-item"><a href="itemself.php" class="tm-nav-link"><i class="fas fa-folder"></i>Đồ của tôi</a></li>
                    <li class="tm-nav-item"><a href="itemresolved.php" class="tm-nav-link"><i class="fas fa-flag"></i>Đồ đã tìm thấy</a></li>
                    <li class="tm-nav-item"><a href="itempost.php" class="tm-nav-link"><i class="fas fa-plus"></i>Báo nhặt / mất đồ</a></li>
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

            <p class="tm-mb-80 pr-5 text-white">Xem thông tin chi tiết và danh sách các bài đăng của thành viên trong hệ thống.</p>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <div class="container-fluid">
        <main class="tm-main">

            <!-- THÔNG TIN NGƯỜI ĐƯỢC CHỌN THỂ HIỆN Ở VỊ TRÍ TƯƠNG ĐƯƠNG THANH SEARCH -->
            <div class="row tm-row mb-4">
                <div class="col-12">
                    <div class="p-4 bg-white border rounded shadow-sm d-flex align-items-center">
                        <div class="mr-4 text-success" style="font-size: 3rem;">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <h2 class="tm-color-primary font-weight-bold mb-1"><?php echo htmlspecialchars($profile_user['fullname']); ?></h2>
                            <p class="mb-1 text-muted"><i class="fas fa-envelope mr-2"></i>Email: <strong><?php echo htmlspecialchars($profile_user['email']); ?></strong></p>
                            <p class="mb-0 text-muted"><i class="fas fa-calendar-alt mr-2"></i>Thành viên tham gia hệ thống từ: <?php echo date('d/m/Y', strtotime($profile_user['created_at'] ?? 'now')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TIÊU ĐỀ DANH SÁCH BÀI ĐĂNG -->
            <div class="row tm-row">
                <div class="col-12">
                    <h3 class="tm-color-primary mb-3"><i class="fas fa-list mr-2"></i>Tất cả bài đăng của thành viên này (<?php echo $items_result->num_rows; ?>)</h3>
                    <hr class="tm-hr-primary">
                </div>
            </div>

            <!-- DANH SÁCH BÀI ĐĂNG CỦA NGƯỜI ĐÓ -->
            <div class="row tm-row">
                <?php if ($items_result && $items_result->num_rows > 0): ?>
                    <?php while($row = $items_result->fetch_assoc()): ?>
                        <article class="col-12 col-md-6 tm-post">
                            <hr class="tm-hr-primary">
                            
                            <a href="#" class="effect-lily tm-post-link tm-pt-20">
                                <div class="tm-post-link-inner">
                                    <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'img/huhDog.jpg'; ?>" alt="Image" class="img-fluid" style="height: 220px; width: 100%; object-fit: cover;">                            
                                </div>
                                
                                <?php if($row['type'] == 'LOST'): ?>
                                    <span class="badge badge-danger p-2 style-badge" style="position: absolute; top: 15px; left: 15px;">
                                        <i class="fas fa-exclamation-circle"></i> BÁO MẤT
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success p-2 style-badge" style="position: absolute; top: 15px; left: 15px;">
                                        <i class="fas fa-hand-holding-heart"></i> NHẶT ĐƯỢC
                                    </span>
                                <?php endif; ?>

                                <h2 class="tm-pt-30 tm-color-primary tm-post-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                            </a>                    
                            
                            <p class="tm-pt-20">
                                <strong><i class="fas fa-map-marker-alt text-danger"></i> Địa điểm:</strong> <?php echo htmlspecialchars($row['location_name'] ?? 'Khác'); ?><br>
                                <strong><i class="fas fa-clock text-info"></i> Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($row['date_event'])); ?><br>
                                <strong><i class="fas fa-phone text-success"></i> SĐT:</strong> <?php echo htmlspecialchars($row['contact_phone']); ?><br>
                                <strong><i class="fas fa-envelope text-primary"></i> Cách liên lạc khác:</strong> <?php echo htmlspecialchars($row['alt_contact']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between tm-pt-10 mb-4">
                                <span class="tm-color-primary"><i class="fas fa-tag"></i> Danh mục: <?php echo htmlspecialchars($row['category_name'] ?? 'Khác'); ?></span>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">Thành viên này chưa có bài đăng nào!</h4>
                    </div>
                <?php endif; ?>
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