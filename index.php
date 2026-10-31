
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

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>

                    <li class="tm-nav-item">
                        <a href="itemresolved.php" class="tm-nav-link">
                            <i class="fas fa-flag"></i>Đồ được đã tìm thấy
                        </a>
                    </li>

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


<!-- ==========================================
     3. MAIN CONTENT
========================================== -->

<div class="container-fluid">

    <main class="tm-main">

        <!-- Search and filter -->

        <div class="row tm-row mb-4">

            <div class="col-12">

                <form id="searchForm"
                      class="form-inline tm-search-form bg-white p-3 border rounded">

                    <div class="row w-100 g-2">

                        <!-- Keyword -->

                        <div class="col-md-3">

                            <input
                                id="keyword"
                                class="form-control w-100"
                                type="text"
                                placeholder="Nhập tên đồ vật, đặc điểm..."
                            >

                        </div>

                        <!-- Item type -->

                        <div class="col-md-2">

                            <select id="type" class="form-control w-100">

                                <option value="">-- Loại tin --</option>
                                <option value="LOST">Cần tìm đồ (Mất)</option>
                                <option value="FOUND">Nhặt được đồ</option>

                            </select>

                        </div>

                        <!-- Category -->

                        <div class="col-md-3">

                            <select id="category_id" class="form-control w-100">

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

                        <!-- Location -->

                        <div class="col-md-2">

                            <select id="location_id" class="form-control w-100">
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

                        <!-- Search button -->

                        <div class="col-md-2">

                            <button class="btn btn-outline-success w-100" type="submit">
                                <i class="fas fa-search"></i> Lọc
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


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
<script src="custom.js"></script>


<!-- ==========================================
     8. REST API JAVASCRIPT
========================================== -->

<script>

// API endpoint
const API_URL = 'api/items/index.php';

// DOM elements
const searchForm = document.getElementById('searchForm');
const postsContainer = document.getElementById('postsContainer');
const paginationContainer = document.getElementById('paginationContainer');


// Escape HTML to prevent XSS
function escapeHTML(value) {

    return String(value ?? '').replace(/[&<>"']/g, function(char) {

        const entities = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return entities[char];

    });

}


// Validate image URL
function getImageURL(imageURL) {

    if (!imageURL) {
        return 'img/default.png';
    }

    const value = String(imageURL);

    if (/^(https?:\/\/|\/|\.\/|\.\.\/)/i.test(value)) {
        return value;
    }

    return 'img/default.png';

}


// Format event date
function formatDate(dateString) {

    if (!dateString) {
        return 'Không rõ';
    }

    const date = new Date(dateString.replace(' ', 'T'));

    if (isNaN(date.getTime())) {
        return escapeHTML(dateString);
    }

    return date.toLocaleString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

}


// ==========================================
// LOAD ITEMS FROM REST API
// ==========================================

async function loadItems(page = 1) {

    // Get filter values
    const keyword = document.getElementById('keyword').value.trim();
    const type = document.getElementById('type').value;
    const category_id = document.getElementById('category_id').value;
    const location_id = document.getElementById('location_id').value;

    // Build query parameters
    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (type) params.append('type', type);
    if (category_id) params.append('category_id', category_id);
    if (location_id) params.append('location_id', location_id);

    params.append('page', page);

    // Update browser URL without reloading page
    const queryString = params.toString();

    history.pushState(
        { page: page },
        '',
        'index.php?' + queryString
    );

    // Show loading message
    postsContainer.innerHTML = `
        <div class="col-12 text-center py-5">
            <h4>Đang tải bài đăng...</h4>
        </div>
    `;

    paginationContainer.innerHTML = '';

    try {

        // Send GET request to API
        const response = await fetch(
            API_URL + '?' + queryString
        );

        // Convert response to JSON
        const result = await response.json();

        // Check HTTP status and API result
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không thể tải dữ liệu');
        }

        // Render posts
        renderPosts(result.data);

        // Render pagination
        renderPagination(result.pagination);

    } catch (error) {

        console.error('API Error:', error);

        postsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <h4 class="text-danger">
                    Không thể tải bài đăng!
                </h4>
                <p>Vui lòng thử lại sau.</p>
            </div>
        `;

    }

}


// ==========================================
// RENDER POSTS
// ==========================================

function renderPosts(items) {

    // No posts found
    if (!items || items.length === 0) {

        postsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <h4>Chưa có bài đăng nào trong hệ thống!</h4>
            </div>
        `;

        return;
    }

    let html = '';

    items.forEach(function(item) {

        // Determine item type
        const isLost = item.type === 'LOST';

        const badge = isLost
            ? `
                <span class="badge badge-danger p-2 style-badge"
                      style="position:absolute; top:15px; left:15px;">
                    <i class="fas fa-exclamation-circle"></i>
                    BÁO MẤT
                </span>
              `
            : `
                <span class="badge badge-success p-2 style-badge"
                      style="position:absolute; top:15px; left:15px;">
                    <i class="fas fa-hand-holding-heart"></i>
                    NHẶT ĐƯỢC
                </span>
              `;

        // Build post card
        html += `

            <article class="col-12 col-md-6 tm-post">

                <hr class="tm-hr-primary">

                <a href="about-post.php?id=${encodeURIComponent(item.item_id)}"
                   class="effect-lily tm-post-link tm-pt-20">

                    <div class="tm-post-link-inner">

                        <img
                            src="${escapeHTML(getImageURL(item.image_url))}"
                            alt="Image"
                            class="img-fluid"
                            style="height:220px; width:100%; object-fit:cover;"
                        >

                    </div>

                    ${badge}

                    <h2 class="tm-pt-30 tm-color-primary tm-post-title">
                        ${escapeHTML(item.title)}
                    </h2>

                </a>

                <p class="tm-pt-20">

                    <strong>
                        <i class="fas fa-user text-primary"></i>
                        Người đăng:
                    </strong>

                    <a href="about-user.php?id=${encodeURIComponent(item.user_id)}">
                        ${escapeHTML(item.fullname || 'Thành viên')}
                    </a>

                    <br>

                    <strong>
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        Địa điểm:
                    </strong>

                    ${escapeHTML(item.location_name || 'Khác')}

                    <br>

                    <strong>
                        <i class="fas fa-clock text-info"></i>
                        Thời gian:
                    </strong>

                    ${formatDate(item.date_event)}

                    <br>

                    <strong>
                        <i class="fas fa-phone text-success"></i>
                        SĐT:
                    </strong>

                    ${escapeHTML(item.contact_phone || 'Không cung cấp')}

                    <br>

                    <strong>
                        <i class="fas fa-envelope text-primary"></i>
                        Cách liên lạc khác:
                    </strong>

                    ${escapeHTML(item.alt_contact || 'Không cung cấp')}

                </p>

                <div class="d-flex justify-content-between tm-pt-10 mb-4">

                    <span class="tm-color-primary">
                        <i class="fas fa-tag"></i>
                        Danh mục:
                        ${escapeHTML(item.category_name || 'Khác')}
                    </span>

                </div>

            </article>

        `;

    });

    postsContainer.innerHTML = html;

}


// ==========================================
// RENDER PAGINATION
// ==========================================

function renderPagination(pagination) {

    const currentPage = Number(pagination.current_page);
    const totalPages = Number(pagination.total_pages);

    // No pagination needed
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <div class="col-12 d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination">
    `;

    // Previous button
    if (currentPage > 1) {

        html += `
            <li class="page-item">
                <a href="#" class="page-link"
                   data-page="${currentPage - 1}">
                    &laquo; Trước
                </a>
            </li>
        `;

    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {

        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a href="#" class="page-link" data-page="${i}">
                    ${i}
                </a>
            </li>
        `;

    }

    // Next button
    if (currentPage < totalPages) {

        html += `
            <li class="page-item">
                <a href="#" class="page-link"
                   data-page="${currentPage + 1}">
                    Sau &raquo;
                </a>
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


// ==========================================
// SEARCH AND FILTER EVENTS
// ==========================================

// When submitting search form
searchForm.addEventListener('submit', function(event) {

    event.preventDefault();

    loadItems(1);

});


// When clicking pagination
paginationContainer.addEventListener('click', function(event) {

    const link = event.target.closest('a[data-page]');

    if (!link) return;

    event.preventDefault();

    const page = Number(link.dataset.page);

    if (Number.isInteger(page) && page > 0) {
        loadItems(page);
    }

});


// Restore filters from browser URL
function restoreFilters() {

    const params = new URLSearchParams(window.location.search);

    document.getElementById('keyword').value =
        params.get('keyword') || '';

    document.getElementById('type').value =
        params.get('type') || '';

    document.getElementById('category_id').value =
        params.get('category_id') || '';

    document.getElementById('location_id').value =
        params.get('location_id') || '';

}


// Handle browser Back / Forward buttons
window.addEventListener('popstate', function() {

    restoreFilters();

    const params = new URLSearchParams(window.location.search);

    const page = Math.max(1, Number(params.get('page')) || 1);

    loadItemsFromCurrentURL(page);

});


// Load data without changing browser history
function loadItemsFromCurrentURL(page) {

    const keyword = document.getElementById('keyword').value.trim();
    const type = document.getElementById('type').value;
    const category_id = document.getElementById('category_id').value;
    const location_id = document.getElementById('location_id').value;

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (type) params.append('type', type);
    if (category_id) params.append('category_id', category_id);
    if (location_id) params.append('location_id', location_id);

    params.append('page', page);

    // Fetch without pushing another browser history entry
    fetch(API_URL + '?' + params.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error: ' + response.status);
            }

            return response.json();
        })
        .then(result => {

            if (!result.success) {
                throw new Error(result.message || 'API error');
            }

            renderPosts(result.data);
            renderPagination(result.pagination);

        })
        .catch(error => {

            console.error('API Error:', error);

            postsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <h4 class="text-danger">
                        Không thể tải bài đăng!
                    </h4>
                </div>
            `;

        });

}


// ==========================================
// INITIALIZE PAGE
// ==========================================

restoreFilters();

const initialPage = Math.max(
    1,
    Number(new URLSearchParams(window.location.search).get('page')) || 1
);

loadItems(initialPage);

</script>

</body>
</html>
