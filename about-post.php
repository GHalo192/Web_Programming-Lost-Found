<?php
// ==========================================
// 1. SESSION
// ==========================================
session_start();

$item_id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? intval($_GET['id'])
    : 0;
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

                            <div class="p-4 bg-white border rounded shadow-sm col-12" id="postContainer">

                                <div class="text-center p-5">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p class="mt-3">Đang tải thông tin bài đăng...</p>
                                </div>

                            </div>                            
  
                        </div>


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
    <script src="js/common.js"></script>

<script>
const ITEM_ID = <?php echo json_encode($item_id); ?>;
const CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
const IS_ADMIN = <?php echo json_encode(
    isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'
); ?>;

const CSRF_TOKEN = <?php
    if (isset($_SESSION['user_id'])) {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo json_encode($_SESSION['csrf_token']);
    } else {
        echo 'null';
    }
?>;

const API_URL = 'api/items/detail.php';
const postContainer = document.getElementById('postContainer');




// Tạo các nút quản lý dành cho chủ bài đăng
function renderOwnerActions(post) {
    if (
        Number(CURRENT_USER_ID) !== Number(post.user_id) ||
        post.status === 'CLOSED'
    ) {
        return '';
    }

    return `
        <div class="pt-3 border-top mt-3">
            <h6 class="text-muted mb-2">
                <i class="fas fa-user-cog"></i>
                Quản lý bài viết (Chủ bài đăng):
            </h6>

            <div class="btn-group w-100 mb-2">
                <a href="postedit.php?id=${Number(post.item_id)}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>

                ${post.status === 'OPEN' ? `
                    <button class="btn btn-sm btn-outline-success"
                            onclick="updatePostStatus('RESOLVED')">
                        <i class="fas fa-check"></i> Đã xong
                    </button>
                ` : ''}

                <button class="btn btn-sm btn-outline-danger"
                        onclick="deletePost()">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
        </div>
    `;
}

// Tạo các nút quản lý dành cho ADMIN

function renderAdminActions(post) {
    if (!IS_ADMIN) return '';

    return `
        <div class="pt-3 border-top mt-3 bg-light p-3 rounded">
            <h6 class="text-danger font-weight-bold mb-2">
                <i class="fas fa-user-shield"></i>
                Bảng điều khiển Admin:
            </h6>

            <div class="d-flex flex-wrap gap-2">

                ${post.status === 'CLOSED' ? `
                    <button class="btn btn-sm btn-outline-warning mr-2 mb-1"
                            onclick="updatePostStatus('OPEN')">
                        <i class="fas fa-folder-open"></i>
                        Mở lại bài viết
                    </button>
                ` : ''}

                ${post.status !== 'CLOSED' ? `
                    <button class="btn btn-sm btn-dark mr-2 mb-1"
                            onclick="updatePostStatus('CLOSED')">
                        <i class="fas fa-eye-slash"></i>
                        Đóng bài viết
                    </button>
                ` : ''}

            </div>
        </div>
    `;
}


// Hiển thị toàn bộ thông tin bài đăng
function renderPost(post) {
    const isOwner =
        CURRENT_USER_ID !== null &&
        Number(CURRENT_USER_ID) === Number(post.user_id);

    const ownerActions = renderOwnerActions(post);
    const adminActions = renderAdminActions(post);

    postContainer.innerHTML = `
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h2 class="tm-color-primary font-weight-bold">
                ${escapeHTML(post.title)}
            </h2>

            <div>
                ${renderTypeBadge(post.type)}
                ${renderStatusBadge(post.status)}
            </div>
        </div>

        <div class="text-center mb-4">
            <img src="${escapeHTML(getImageURL(post.image_url))}"
                 alt="Item Image"
                 class="img-fluid rounded"
                 style="max-height: 450px; width: 100%;
                        object-fit: contain; background: #f8f9fa;">
        </div>

        <div class="row">
        
            <div class="col-md-12">
                <h4 class="tm-color-primary mb-3">
                    <i class="fas fa-align-left mr-2"></i>
                    Mô tả chi tiết
                </h4>

                <p class="text-justify"
                   style="white-space: pre-line; font-size: 1.1rem; line-height: 1.6;">
                    ${escapeHTML(post.description)}
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 bg-light p-4 rounded border">
                <h4 class="tm-color-primary mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Thông tin chung
                </h4>

                <ul class="list-unstyled"
                    style="font-size: 1.05rem; line-height: 2;">

                    <li>
                        <strong><i class="fas fa-tag text-info mr-2"></i>Danh mục:</strong>
                        ${escapeHTML(post.category_name || 'Khác')}
                    </li>

                    <li>
                        <strong><i class="fas fa-map-marker-alt text-danger mr-2"></i>Địa điểm:</strong>
                        ${escapeHTML(post.location_name || 'Khác')}
                    </li>

                    <li>
                        <strong><i class="fas fa-clock text-warning mr-2"></i>Thời gian:</strong>
                        ${escapeHTML(formatDate(post.date_event))}
                    </li>

                    <li>
                        <strong><i class="fas fa-user text-primary mr-2"></i>Người đăng:</strong>
                        <a href="about-user.php?id=${Number(post.user_id)}">
                            ${escapeHTML(post.fullname || 'Thành viên')}
                        </a>
                    </li>

                    <li>
                        <strong><i class="fas fa-phone text-success mr-2"></i>Số điện thoại:</strong>
                        <a href="tel:${escapeHTML(post.contact_phone)}">
                            ${escapeHTML(post.contact_phone)}
                        </a>
                    </li>

                    ${post.alt_contact ? `
                        <li>
                            <strong>
                                <i class="fas fa-comments text-secondary mr-2"></i>
                                Liên lạc khác:
                            </strong>
                            ${escapeHTML(post.alt_contact)}
                        </li>
                    ` : ''}

                    <li>
                        <strong><i class="fas fa-calendar-plus text-muted mr-2"></i>Ngày tạo bài:</strong>
                        ${escapeHTML(formatDate(post.created_at))}
                    </li>
                </ul>
            </div>
        </div>

        ${ownerActions}
        ${adminActions}
    `;
}

// Gọi API lấy thông tin chi tiết
async function loadPost() {
    if (ITEM_ID <= 0) {
        postContainer.innerHTML =
            '<div class="alert alert-danger">ID bài đăng không hợp lệ.</div>';
        return;
    }

    try {
        const response = await fetch(
            `${API_URL}?id=${encodeURIComponent(ITEM_ID)}`,
            { credentials: 'same-origin' }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không thể tải bài đăng');
        }

        const post = result.data;

        // Không cho phép truy cập link quản lý người dùng bằng dữ liệu thiếu
        if (!post) {
            throw new Error('Dữ liệu bài đăng không hợp lệ');
        }

        document.title = `${post.title} - Campus Lost&Found`;
        renderPost(post);

    } catch (error) {
        postContainer.innerHTML = `
            <div class="alert alert-danger">
                ${escapeHTML(error.message)}
                <div class="mt-3">
                    <a href="index.php" class="btn btn-outline-primary btn-sm">
                        Quay lại trang chủ
                    </a>
                </div>
            </div>
        `;
    }
}

// Cập nhật trạng thái bài đăng qua REST API
async function updatePostStatus(newStatus) {
    if (!confirm('Bạn có chắc chắn muốn thay đổi trạng thái bài đăng?')) {
        return;
    }

    try {
        const response = await fetch(
            `api/items/update-status.php?id=${ITEM_ID}`,
            {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({ status: newStatus })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không thể cập nhật trạng thái');
        }

        alert('Cập nhật trạng thái thành công!');
        loadPost();

    } catch (error) {
        alert(error.message);
    }
}

// Xóa bài đăng qua REST API
async function deletePost() {
    if (!confirm('Bạn có chắc chắn muốn xóa bài đăng này không?')) {
        return;
    }

    try {
        const response = await fetch(
            `api/items/item.php?id=${ITEM_ID}`,
            {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-Token': CSRF_TOKEN
                }
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không thể xóa bài đăng');
        }

        alert('Đã xóa bài đăng!');
        window.location.href = 'index.php';

    } catch (error) {
        alert(error.message);
    }
}

// Chạy khi trang được tải
loadPost();
</script>
    
</body>
</html>