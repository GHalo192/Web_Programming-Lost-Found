<?php
// ==========================================
// 1. SESSION
// ==========================================
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Campus Lost&Found</title>

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
                <li class="tm-nav-item active">
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


<!-- ==========================================
     3. MAIN CONTENT
========================================== -->

<div class="container-fluid">
    <main class="tm-main">

        <div class="row mb-4">
            <div class="col-12">
                <h2 class="tm-color-primary">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </h2>

                <p class="text-muted">
                    Dưới đây là danh sách tất cả các tin báo mất / nhặt của trang web
                </p>
            </div>
        </div>

        <!-- SEARCH FORM -->
        <div class="row tm-row mb-4">
            <div class="col-12">

                <form id="searchForm"
                        class="form-inline tm-search-form bg-white p-3 border rounded">

                    <div class="row w-100 g-2">

                        <div class="col-md-4">
                            <input
                                id="keyword"
                                class="form-control w-100"
                                type="text"
                                name="keyword"
                                placeholder="Nhập tên đồ vật, đặc điểm..."
                            >
                        </div>

                        <div class="col-md-2">
                            <select id="type" name="type" class="form-control w-100">
                                <option value="">-- Loại tin --</option>
                                <option value="LOST">Báo mất</option>
                                <option value="FOUND">Nhặt được</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select id="category_id" name="category_id" class="form-control w-100">
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

                        <div class="col-md-2">
                            <select id="location_id" name="location_id" class="form-control w-100">
                                <option value="">-- Khu vực --</option>
                                <option value="1">Tòa A</option>
                                <option value="2">Tòa B</option>
                                <option value="3">Tòa C</option>
                                <option value="4">Cầu A-B</option>
                                <option value="5">Cầu A-C</option>
                                <option value="6">Tòa D</option>
                                <option value="7">Tòa E</option>
                                <option value="8">Tòa F</option>
                                <option value="9">Tòa G (Thư viện)</option>
                                <option value="10">Tòa H</option>
                                <option value="11">Tòa I</option>
                                <option value="12">Tòa K</option>
                                <option value="13">Tòa L</option>
                                <option value="14">Tòa M</option>
                                <option value="15">Căng tin</option>
                                <option value="16">Sân vận động</option>
                                <option value="17">Nhà thi đấu</option>
                                <option value="18">Bãi xe tòa A</option>
                                <option value="19">Bãi xe tòa G</option>
                                <option value="20">Bãi xe nhà thi đấu</option>
                                <option value="21">Khác</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-outline-success w-100" type="submit">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
        <!-- END SEARCH FORM -->

        <!-- ==========================================
             4. POSTS CONTAINER
        ========================================== -->

        <div class="row tm-row" id="postsContainer">

            <div class="col-12 text-center py-5">
                <h4>Đang tải bài đăng...</h4>
            </div>

        </div>


        <!-- ==========================================
             5. PAGINATION
        ========================================== -->

        <div class="row tm-row mt-4" id="paginationContainer">

        </div>


        <!-- ==========================================
             6. FOOTER
        ========================================== -->

        <footer class="row tm-row">

            <hr class="col-12">

            <div class="col-md-6 col-12 tm-color-gray">

                Design:
                <a rel="nofollow"
                   target="_parent"
                   href="https://templatemo.com"
                   class="tm-external-link">
                    TemplateMo
                </a>

            </div>

            <div class="col-md-6 col-12 tm-color-gray tm-copyright">
                Copyright 2026 PNC
            </div>

        </footer>

    </main>

</div>


<!-- ==========================================
     7. JAVASCRIPT LIBRARIES
========================================== -->

<script src="js/jquery.min.js"></script>
<script src="js/templatemo-script.js"></script>
<script src="js/custom.js"></script>
<script src="js/common.js"></script>
<script src="js/items.js" defer></script>

<!-- ==========================================
     8. REST API JAVASCRIPT
========================================== -->

<script>
    window.ITEMS_CONFIG = {
        mode: 'public',
        apiUrl: 'api/items/index.php',
        status: 'OPEN'
    };
</script>


</body>
</html>
