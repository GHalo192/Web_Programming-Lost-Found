<?php
session_start();

// Nếu chưa đăng nhập -> Đẩy về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Creating post</title>
	<link rel="stylesheet" href="fontawesome/css/all.min.css"> <!-- https://fontawesome.com/ -->
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet"> <!-- https://fonts.google.com/ -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!--
    TemplateMo 553 Xtra Blog
    https://templatemo.com/tm-553-xtra-blog
    -->
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
                    <li class="tm-nav-item"><a href="itemself.php" class="tm-nav-link">
                        <i class="fas fa-folder"></i>Đồ của tôi</a></li>
                    <!-- Chỉ ADMIN được xem -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                        <li class="tm-nav-item"><a href="itemresolved.php" class="tm-nav-link">
                            <i class="fas fa-flag"></i>Đồ được đã tìm thấy</a></li>
                        <li class="tm-nav-item"><a href="itemclosed.php" class="tm-nav-link">
                            <i class="fas fa-flag"></i>Đồ được đã bị ẩn đi</a></li>
                    <?php endif; ?>

                    <li class="tm-nav-item active"><a href="itempost.php" class="tm-nav-link">
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


    <main class="tm-main">
        <!-- Form Đăng Tin Main Content -->
        <div class="row tm-row">
            <div class="col-12">
                <h2 class="tm-color-primary tm-post-title mb-4">Đăng Tin Báo Mất / Nhặt Được Đồ</h2>
                <hr class="tm-hr-primary mb-5">
            </div>
        </div>

        <div class="row tm-row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <!-- FORM ĐĂNG TIN -->
                <!-- Lưu ý: Đăng ảnh BẮT BUỘC phải có enctype="multipart/form-data" -->
                <form action="save_post.php" method="POST" enctype="multipart/form-data" class="tm-contact-form">                    
                    <!-- 1. Loại tin đăng -->
                    <div class="form-group row mb-4">
                        <label class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Loại tin <span class="text-danger">*</span></label>
                        <div class="col-sm-9 d-flex align-items-center">
                            <div class="custom-control custom-radio custom-control-inline mr-4">
                                <input type="radio" id="typeLost" name="type" value="LOST" class="custom-control-input" checked>
                                <label class="custom-control-label text-danger font-weight-bold" for="typeLost">
                                    <i class="fas fa-exclamation-circle"></i> Tôi bị mất đồ (LOST)
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeFound" name="type" value="FOUND" class="custom-control-input">
                                <label class="custom-control-label text-success font-weight-bold" for="typeFound">
                                    <i class="fas fa-hand-holding-heart"></i> Tôi nhặt được đồ (FOUND)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Tiêu đề bài đăng -->
                    <div class="form-group row mb-4">
                        <label for="title" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Tiêu đề tin <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="title" id="title" type="text" placeholder="Ví dụ: Rớt ví da màu đen tại Căng tin Tòa C" required>
                        </div>
                    </div>

                    <!-- 3. Danh mục & Địa điểm -->
                    <div class="form-group row mb-4">
                        <label for="category_id" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Danh mục <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" id="category_id" name="category_id" required>
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
                    </div>

                    <div class="form-group row mb-4">
                        <label for="location_id" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Khu vực / Địa điểm <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" id="location_id" name="location_id" required>
                                <option value="">-- Khu vực --</option>
                                <option value="1">Tòa A</option>
                                <option value="2">Tòa B</option>
                                <option value="3">Tòa C</option>
                                <option value="4">Cầu A-B</option>
                                <option value="5">Cầu A-C</option>
                                <option value="6">Tòa D</option>
                                <option value="7">Tòa E</option>
                                <option value="8">Tòa F</option>
                                <option value="9">Tòa G (thư viện)</option>
                                <option value="10">Tòa H</option>
                                <option value="11">Tòa I</option>
                                <option value="12">Tòa K</option>
                                <option value="13">Tòa L</option>
                                <option value="14">Tòa M (tòa quân sự)</option>
                                <option value="15">Căng tin</option>
                                <option value="16">Sân vận động</option>
                                <option value="17">Nhà thi đấu</option>
                                <option value="18">Bãi xe tòa A</option>
                                <option value="19">Bãi xe tòa G</option>
                                <option value="20">Bãi xe nhà thi đấu</option>
                                <option value="21">Khác</option>
                                <!-- Còn nhiều nữa, thêm vào sau -->
                            </select>
                        </div>
                    </div>

                    <!-- 4. Thời gian xảy ra vụ việc -->
                    <div class="form-group row mb-4">
                        <label for="date_event" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Thời gian <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="date_event" id="date_event" type="datetime-local" required>
                        </div>
                    </div>

                    <!-- 5. Tải ảnh hình ảnh món đồ -->
                    <div class="form-group row mb-4">
                        <label for="image" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Hình ảnh minh họa (nếu có) </label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control-file mb-2" id="image" name="image" accept="image/*">
                            <!-- Khung hiển thị xem trước ảnh -->
                            <div class="image-preview-container" id="previewContainer">
                                <span class="text-muted" id="previewText">Xem trước hình ảnh tải lên </span>
                                <img id="imagePreview" src="#" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <!-- 6. Mô tả chi tiết -->
                    <div class="form-group row mb-4">
                        <label for="description" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Mô tả chi tiết <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" id="description" rows="5" placeholder="Mô tả kỹ đặc điểm: nhãn hiệu, màu sắc, vết trầy xước, các vật dụng bên trong (nếu là ví/balo)..." required></textarea>
                        </div>
                    </div>

                    <!-- 7. Thông tin liên hệ -->
                    <div class="form-group row mb-4">
                        <label for="phone" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="phone" id="phone" type="tel" placeholder="0901234567" required>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label for="email" class="col-sm-3 col-form-label tm-color-primary font-weight-bold">Cách liên hệ khác (nếu có) <span class="text-danger"></span></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="altcontact" id="altcontact" type="text">
                        </div>
                    </div>

                    <!-- Nút Submit -->
                    <div class="form-group row mb-0">
                        <div class="col-sm-9 offset-sm-3 text-right">
                            <button class="tm-btn tm-btn-primary tm-btn-small" type="submit">
                                <i class="fas fa-paper-plane mr-2"></i>Đăng bài
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- JavaScript xử lý Xem trước ảnh (Image Preview) -->
    <script src="js/custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>

</body>
</html>