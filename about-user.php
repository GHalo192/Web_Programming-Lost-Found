<?php
session_start();

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
                            <i class="fas fa-flag"></i>Đồ được đã bị ẩn đi
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

    <script src="custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>
    <script src="js/common.js"></script>


    <script>

        const PROFILE_ID = <?php echo json_encode($profile_id); ?>;
        const API_URL = 'api/users/detail.php';

        const profileContainer = document.getElementById('profileContainer');
        const totalItems = document.getElementById('totalItems');
        const itemsContainer = document.getElementById('itemsContainer');
        // 1. Khai báo thêm element paginationContainer
        const paginationContainer = document.getElementById('paginationContainer');

        // Hiển thị thông tin người dùng
        function renderProfile(user) {
            profileContainer.innerHTML = `
                <div class="mr-4 text-success" style="font-size: 3rem;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <h2 class="tm-color-primary font-weight-bold mb-1">
                        ${escapeHTML(user.fullname)}
                    </h2>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-envelope mr-2"></i>
                        Email: <strong>${escapeHTML(user.email)}</strong>
                    </p>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Thành viên tham gia hệ thống từ: ${escapeHTML(formatDate(user.created_at))}
                    </p>
                </div>
            `;
            document.title = `Trang cá nhân - ${user.fullname} - Campus Lost&Found`;
        }

        // Hiển thị một bài đăng
        function renderItem(item) {
            return `
                <article class="col-12 col-md-6 tm-post">
                    <hr class="tm-hr-primary">
                    <a href="about-post.php?id=${Number(item.item_id)}" class="effect-lily tm-post-link tm-pt-20">
                        <div class="tm-post-link-inner">
                            <img src="${escapeHTML(getImageURL(item.image_url))}" alt="Image" class="img-fluid" style="height: 220px; width: 100%; object-fit: cover;">
                        </div>
                        ${renderTypeBadge(item.type)}
                        <h2 class="tm-pt-30 tm-color-primary tm-post-title">
                            ${escapeHTML(item.title)}
                        </h2>
                    </a>
                    <p class="tm-pt-20">
                        <strong><i class="fas fa-map-marker-alt text-danger"></i> Địa điểm:</strong> ${escapeHTML(item.location_name || 'Khác')}<br>
                        <strong><i class="fas fa-clock text-info"></i> Thời gian:</strong> ${escapeHTML(formatDate(item.date_event))}<br>
                        <strong><i class="fas fa-phone text-success"></i> SĐT:</strong> ${escapeHTML(item.contact_phone || '')}<br>
                        <strong><i class="fas fa-envelope text-primary"></i> Cách liên lạc khác:</strong> ${escapeHTML(item.alt_contact || 'Không có')}
                    </p>
                    <div class="d-flex justify-content-between tm-pt-10 mb-4">
                        <span class="tm-color-primary">
                            <i class="fas fa-tag"></i> Danh mục: ${escapeHTML(item.category_name || 'Khác')}
                        </span>
                    </div>
                </article>
            `;
        }

        // Hiển thị danh sách bài đăng
        function renderItems(items) {
            if (!items || items.length === 0) {
                itemsContainer.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">Thành viên này chưa có bài đăng nào!</h4>
                    </div>
                `;
                return;
            }

            itemsContainer.innerHTML = items.map(item => renderItem(item)).join('');
        }

        // 2. Thêm hàm vẽ các nút phân trang (Lấy từ items.js)
        function renderPagination(pagination) {
            if (!paginationContainer) return;

            const currentPage = Number(pagination.current_page);
            const totalPages = Number(pagination.total_pages);

            // Không phân trang nếu số trang <= 1
            if (totalPages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let html = `
                <div class="col-12 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
            `;

            // Nút Nút Trước
            if (currentPage > 1) {
                html += `
                    <li class="page-item">
                        <a href="#" class="page-link" data-page="${currentPage - 1}">&laquo; Trước</a>
                    </li>
                `;
            }

            // Các nút số trang
            for (let i = 1; i <= totalPages; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a href="#" class="page-link" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            // Nút Sau
            if (currentPage < totalPages) {
                html += `
                    <li class="page-item">
                        <a href="#" class="page-link" data-page="${currentPage + 1}">Sau &raquo;</a>
                    </li>
                `;
            }

            html += `
                        </ul>
                    </nav>
                </div>
            `;

            paginationContainer.innerHTML = html;
        }

        // 3. Cập nhật loadProfile để truyền tham số `page` và vẽ lại thanh phân trang
        async function loadProfile(page = 1) {
            if (PROFILE_ID <= 0) {
                profileContainer.innerHTML = `<div class="alert alert-danger">ID người dùng không hợp lệ.</div>`;
                itemsContainer.innerHTML = '';
                return;
            }

            // Hiển thị loading trong danh sách khi chuyển trang
            itemsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-3">Đang tải danh sách bài đăng...</p>
                </div>
            `;

            try {
                // Truyền thêm param ?page= vào API
                const response = await fetch(
                    `${API_URL}?id=${encodeURIComponent(PROFILE_ID)}&page=${page}`,
                    {
                        method: 'GET',
                        credentials: 'same-origin'
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Không thể tải thông tin người dùng.');
                }

                const user = result.data.user;
                const items = result.data.items;

                renderProfile(user);
                totalItems.textContent = result.data.total_items;
                renderItems(items);

                // Gọi hàm render phân trang nếu API trả về đối tượng pagination
                if (result.pagination) {
                    renderPagination(result.pagination);
                } else {
                    paginationContainer.innerHTML = '';
                }

            } catch (error) {
                profileContainer.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        ${escapeHTML(error.message)}
                        <a href="index.php" class="btn btn-outline-primary btn-sm ml-2">Quay lại trang chủ</a>
                    </div>
                `;
                itemsContainer.innerHTML = '';
                if (paginationContainer) paginationContainer.innerHTML = '';
            }
        }

        // 4. Đăng ký sự kiện click chọn trang (Lấy từ items.js)
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const pageLink = e.target.closest('[data-page]');
                if (!pageLink) return;

                e.preventDefault();
                const selectedPage = Number(pageLink.dataset.page);
                loadProfile(selectedPage);
            });
        }

        // Tải trang đầu tiên khi load giao diện
        loadProfile(1);

    </script>

</body>
</html>