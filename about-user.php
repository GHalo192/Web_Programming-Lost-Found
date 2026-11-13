<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$profile_id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? intval($_GET['id'])
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cá nhân - Campus Lost&Found</title>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>

<!-- ==========================================
     2. SIDEBAR
========================================== -->

<header class="tm-header" id="tm-header">

    <div class="tm-header-wrapper">

        <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="tm-site-header">
            <div class="mb-3 mx-auto tm-site-logo">
                <i class="fas fa-times fa-2x"></i>
            </div>

            <h1 class="text-center">Campus Lost&Found</h1>
        </div>

        <nav class="tm-nav" id="tm-nav">
            <ul>
                <li class="tm-nav-item">
                    <a href="index.php" class="tm-nav-link">
                        <i class="fas fa-home"></i>Trang Chủ
                    </a>
                </li>

                <li class="tm-nav-item">
                    <a href="itemself.php" class="tm-nav-link">
                        <i class="fas fa-folder"></i>Đồ của tôi
                    </a>
                </li>

                <li class="tm-nav-item">
                    <a href="itemresolved.php" class="tm-nav-link">
                        <i class="fas fa-flag"></i>Đồ được đã tìm thấy
                    </a>
                </li>          

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>

                    <li class="tm-nav-item">
                        <a href="itemclosed.php" class="tm-nav-link">
                            <i class="fas fa-ban"></i>Đồ đã bị ẩn đi
                        </a>
                    </li>

                <?php endif; ?>

                <li class="tm-nav-item">
                    <a href="itempost.php" class="tm-nav-link">
                        <i class="fas fa-plus"></i>Báo nhặt / mất đồ
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Account information -->

        <div class="tm-mb-65 user-auth-box">

            <?php if (isset($_SESSION['user_id'])): ?>

                <div class="user-profile-info p-3 mb-2 bg-light rounded text-dark">

                    <div class="font-weight-bold" style="font-size: 1.1rem;">
                        <i class="fas fa-user-circle mr-1"></i>

                        <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Thành viên'); ?>
                    </div>

                    <div class="text-muted small">
                        <i class="fas fa-envelope mr-1"></i>

                        <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                    </div>

                </div>

                <a href="logout.php" class="btn btn-outline-danger btn-block btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>

            <?php else: ?>

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

        <h2 class="tm-mb-80 pr-5 text-white">
            Tìm lại đồ thất lạc trong Campus
        </h2>

        <p class="tm-mb-80 pr-5 text-white">
            Hệ thống hỗ trợ kết nối người nhặt được đồ và người làm mất đồ trong khuôn viên nhà trường.
        </p>

    </div>
</header>

    <!-- MAIN CONTENT -->
    <div class="container-fluid">
        <main class="tm-main">


            <!-- THÔNG TIN NGƯỜI DÙNG -->
            <div class="row tm-row mb-4">
                <div class="col-12">
                    <div class="p-4 bg-white border rounded shadow-sm d-flex align-items-center"
                        id="profileContainer">

                        <div class="mr-4 text-success" style="font-size: 3rem;">
                            <i class="fas fa-user-circle"></i>
                        </div>

                        <div>
                            <h2 class="tm-color-primary font-weight-bold mb-1">
                                Đang tải thông tin...
                            </h2>
                        </div>

                    </div>
                </div>
            </div>

            <!-- TIÊU ĐỀ DANH SÁCH BÀI ĐĂNG -->
            <div class="row tm-row">
                <div class="col-12">
                    <h3 class="tm-color-primary mb-3">
                        <i class="fas fa-list mr-2"></i>
                        Tất cả bài đăng của thành viên này
                        (<span id="totalItems">0</span>)
                    </h3>

                    <hr class="tm-hr-primary">
                </div>
            </div>

            <!-- DANH SÁCH BÀI ĐĂNG -->
            <div class="row tm-row" id="itemsContainer">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-3">Đang tải danh sách bài đăng...</p>
                </div>
            </div>

            <!-- ==========================================
                5. PAGINATION
            ========================================== -->

            <div class="row tm-row mt-4" id="paginationContainer"></div>        

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

    <script src="js/custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>
    <script src="js/common.js"></script>


    <script>
        // Truyền dữ liệu từ PHP sang JavaScript
        window.ABOUT_USER_CONFIG = {
            profileId: <?php echo json_encode($profile_id); ?>,
            currentUserId: <?php echo json_encode(
                isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0
            ); ?>,
            csrfToken: <?php echo json_encode($_SESSION['csrf_token']); ?>
        };
    </script>

    
    <script src="js/about-user.js"></script>
</body>
</html>