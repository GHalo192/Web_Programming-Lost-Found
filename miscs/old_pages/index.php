<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Campus Lost&Found</title>
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
                    <li class="tm-nav-item active"><a href="index.php" class="tm-nav-link">
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

    <!-- vv search bar, posts and navigation vv -->
    <div class="container-fluid">
        <main class="tm-main">

            <!-- vv Search bar vv -->
            <div class="row tm-row mb-4">
                <div class="col-12">

                    <!-- vv Form Lọc & Tìm kiếm vv -->
                    <form method="GET" action="index.php" class="form-inline tm-search-form bg-white p-3 border rounded">
                        <div class="row w-100 g-2">

                            <!-- Từ khóa -->
                            <div class="col-md-3">
                                <input class="form-control w-100" type="text" name="keyword" placeholder="Nhập tên đồ vật, đặc điểm..." value="<?php echo htmlspecialchars($keyword); ?>">
                            </div>

                            <!-- Phân loại tin -->
                            <div class="col-md-2">
                                <select name="type" class="form-control w-100">
                                    <option value="">-- Loại tin --</option>
                                    <option value="LOST" <?php echo ($type === 'LOST') ? 'selected' : ''; ?>>Cần tìm đồ (Mất)</option>
                                    <option value="FOUND" <?php echo ($type === 'FOUND') ? 'selected' : ''; ?>>Nhặt được đồ</option>
                                </select>
                            </div>

                            <!-- Danh mục -->
                            <div class="col-md-3">
                                <select name="category_id" class="form-control w-100">
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="1" <?php echo ($category_id == 1) ? 'selected' : ''; ?>>Thiết bị điện tử</option>
                                    <option value="2" <?php echo ($category_id == 2) ? 'selected' : ''; ?>>Giấy tờ tùy thân</option>
                                    <option value="3" <?php echo ($category_id == 3) ? 'selected' : ''; ?>>Chìa khóa / Thiết bị mở khóa</option>
                                    <option value="4" <?php echo ($category_id == 4) ? 'selected' : ''; ?>>Tài chính / vật phẩm giá trị</option>
                                    <option value="5" <?php echo ($category_id == 5) ? 'selected' : ''; ?>>Vật dụng cá nhân</option>
                                    <option value="6" <?php echo ($category_id == 6) ? 'selected' : ''; ?>>Đồ dùng y tế</option>
                                    <option value="7" <?php echo ($category_id == 7) ? 'selected' : ''; ?>>Dụng cụ học tập</option>
                                    <option value="8" <?php echo ($category_id == 8) ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>

                            <!-- Khu vực -->
                            <div class="col-md-2">
                                <select name="location_id" class="form-control w-100">
                                    <option value="">-- Khu vực --</option>
                                    <option value="1" <?php echo ($location_id == 1) ? 'selected' : ''; ?>>Tòa A</option>
                                    <option value="2" <?php echo ($location_id == 2) ? 'selected' : ''; ?>>Tòa B</option>
                                    <option value="3" <?php echo ($location_id == 3) ? 'selected' : ''; ?>>Tòa C</option>
                                    <option value="4" <?php echo ($location_id == 4) ? 'selected' : ''; ?>>Tòa D</option>
                                    <option value="5" <?php echo ($location_id == 5) ? 'selected' : ''; ?>>Tòa E</option>
                                    <option value="6" <?php echo ($location_id == 6) ? 'selected' : ''; ?>>Tòa F</option>
                                    <option value="7" <?php echo ($location_id == 7) ? 'selected' : ''; ?>>Tòa G</option>
                                    <option value="8" <?php echo ($location_id == 8) ? 'selected' : ''; ?>>Tòa M</option>
                                    <option value="9" <?php echo ($location_id == 9) ? 'selected' : ''; ?>>Căng tin</option>
                                    <option value="10" <?php echo ($location_id == 10) ? 'selected' : ''; ?>>Bãi xe</option>
                                    <option value="13" <?php echo ($location_id == 13) ? 'selected' : ''; ?>>Sân vận động</option>
                                    <option value="14" <?php echo ($location_id == 14) ? 'selected' : ''; ?>>Khác</option>
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
                    <!-- ^^ Form Lọc & Tìm kiếm ^^ -->
                </div>
            </div>
            <!-- ^^ Search bar ^^ -->

            
            <!-- vv Posts vv -->
            <div class="row tm-row">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <article class="col-12 col-md-6 tm-post">
                            <hr class="tm-hr-primary">
                            
                            <a href="about-post.php?id=<?php echo $row['item_id']; ?>" class="effect-lily tm-post-link tm-pt-20">
                                <div class="tm-post-link-inner">
                                    <!-- Nếu có ảnh thì hiển thị, không có thì dùng ảnh mặc định -->
                                    <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'img/default.png'; ?>" alt="Image" class="img-fluid" style="height: 220px; width: 100%; object-fit: cover;">                            
                                </div>
                                
                                <!-- Badge nhãn LOST / FOUND -->
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
                                <strong><i class="fas fa-user text-primary"></i> Người đăng:</strong> <a href="about-user.php?id=<?php echo $row['user_id']; ?>"><?php echo htmlspecialchars($row['fullname'] ?? 'Thành viên'); ?></a><br>
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
                        <h4>Chưa có bài đăng nào trong hệ thống!</h4>
                    </div>
                <?php endif; ?>
            </div>
            <!-- ^^ Posts ^^-->


            <!-- vv Thanh chuyển trang (Pagination) vv -->
            <?php if ($total_pages > 1): ?>
            <div class="row tm-row mt-4">
                <div class="col-12 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            
                            <!-- Nút Trang trước (Previous) -->
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?php echo $page - 1; ?><?php echo $queryString; ?>">&laquo; Trước</a>
                                </li>
                            <?php endif; ?>

                            <!-- Danh sách các số trang -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=<?php echo $i; ?><?php echo $queryString; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Nút Trang sau (Next) -->
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?php echo $page + 1; ?><?php echo $queryString; ?>">Sau &raquo;</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
            <!-- ^^ Thanh chuyển trang (Pagination) ^^ -->


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
    <script>

        async function loadItems() {
            try {
                const response = await fetch(
                    'api/items/index.php?keyword=laptop&type=LOST'
                );

                const result = await response.json();

                if (result.success) {
                    console.log(result.data);

                    // TODO: Render danh sách bài đăng
                }

            } catch (error) {
                console.error("API Error:", error);
            }
        }

        loadItems();
    </script>
</body>
</html>