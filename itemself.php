
<?php
session_start();

// Người chưa đăng nhập không được truy cập trang này
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My items</title>

    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body>

    <!-- SIDEBAR -->
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

                    <li class="tm-nav-item active">
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

            <!-- ACCOUNT INFORMATION -->
            <div class="tm-mb-65 user-auth-box">

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

            </div>

            <h2 class="tm-mb-80 pr-5 text-white">
                Tìm lại đồ thất lạc trong Campus
            </h2>

            <p class="tm-mb-80 pr-5 text-white">
                Hệ thống hỗ trợ kết nối người nhặt được đồ và người làm mất đồ trong khuôn viên nhà trường.
            </p>

        </div>
    </header>
    <!-- END SIDEBAR -->


    <!-- MAIN CONTENT -->
    <div class="container-fluid">
        <main class="tm-main">

            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="tm-color-primary">
                        <i class="fas fa-user-edit"></i>
                        Quản lý bài đăng của tôi
                    </h2>

                    <p class="text-muted">
                        Dưới đây là danh sách tất cả các tin báo mất / nhặt đồ do bạn tạo ra.
                    </p>
                </div>
            </div>


            <!-- SEARCH FORM -->
            <div class="row tm-row mb-4">
                <div class="col-12">

                    <form id="searchForm"
                          class="form-inline tm-search-form bg-white p-3 border rounded">

                        <div class="row w-100 g-2">

                            <div class="col-md-2">
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
                                <select id="status" name="status" class="form-control w-100">
                                    <option value="">-- Trạng thái --</option>
                                    <option value="OPEN">Đang mở</option>
                                    <option value="RESOLVED">Đã giải quyết</option>
                                    <option value="CLOSED">Đã đóng</option>
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


            <!-- POSTS -->
            <div class="row tm-row" id="postsContainer">

                <div class="col-12 text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-3">Đang tải bài đăng...</p>
                </div>

            </div>
            <!-- END POSTS -->


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
    <!-- END MAIN CONTENT -->


    <script src="custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>


    <!-- REST API -->
    <script>

        // API endpoint lấy bài đăng của tài khoản hiện tại
        const API_URL = 'api/users/me/items.php';

        const postsContainer = document.getElementById('postsContainer');
        const searchForm = document.getElementById('searchForm');


        // Escape dữ liệu trước khi đưa vào innerHTML
        function escapeHTML(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];
            });
        }


        // Kiểm tra đường dẫn ảnh
        function getImageURL(imageURL) {

            if (!imageURL) {
                return 'img/default.png';
            }

            const value = String(imageURL);

            if (
                value.startsWith('http://') ||
                value.startsWith('https://') ||
                value.startsWith('/') ||
                value.startsWith('./') ||
                value.startsWith('../')
            ) {
                return value;
            }

            return 'img/default.png';
        }


        // Chuyển ngày từ database sang định dạng dd/mm/yyyy hh:mm
        function formatDate(dateString) {

            if (!dateString) {
                return 'Không rõ';
            }

            const date = new Date(String(dateString).replace(' ', 'T'));

            if (Number.isNaN(date.getTime())) {
                return 'Không rõ';
            }

            return date.toLocaleString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }


        // Hiển thị badge loại bài đăng
        function renderTypeBadge(type) {

            if (type === 'LOST') {
                return `
                    <span class="badge badge-danger p-2"
                          style="position: absolute; top: 10px; left: 10px;">
                        <i class="fas fa-exclamation-circle"></i> BÁO MẤT
                    </span>
                `;
            }

            return `
                <span class="badge badge-success p-2"
                      style="position: absolute; top: 10px; left: 10px;">
                    <i class="fas fa-hand-holding-heart"></i> NHẶT ĐƯỢC
                </span>
            `;
        }


        // Hiển thị badge trạng thái
        function renderStatusBadge(status) {

            switch (status) {

                case 'OPEN':
                    return `
                        <span class="badge badge-primary p-2"
                            style="position: absolute; top: 10px; right: 10px;">
                            <i class="fas fa-eye"></i> ĐANG MỞ
                        </span>
                    `;

                case 'RESOLVED':
                    return `
                        <span class="badge badge-success p-2"
                            style="position: absolute; top: 10px; right: 10px;">
                            <i class="fas fa-check-circle"></i> ĐÃ GIẢI QUYẾT
                        </span>
                    `;

                case 'CLOSED':
                    return `
                        <span class="badge badge-secondary p-2"
                            style="position: absolute; top: 10px; right: 10px;">
                            <i class="fas fa-lock"></i> ĐÃ ĐÓNG
                        </span>
                    `;

                default:
                    return '';
            }
        }

        // Tạo HTML cho một bài đăng
        function renderItem(item) {

            const itemId = Number(item.item_id);

            if (!Number.isInteger(itemId) || itemId <= 0) {
                return '';
            }

            const imageURL = escapeHTML(getImageURL(item.image_url));
            const title = escapeHTML(item.title);
            const location = escapeHTML(item.location_name || 'Khác');
            const createdAt = escapeHTML(formatDate(item.created_at));

            const typeBadge = renderTypeBadge(item.type);
            const statusBadge = renderStatusBadge(item.status);

            // Chỉ hiển thị nút "Đã xong" khi bài đăng đang APPROVED
            const resolvedButton = item.status === 'OPEN'
                ? `
                    <a href="update_status.php?id=${itemId}&status=RESOLVED"
                       class="btn btn-sm btn-outline-success"
                       onclick="return confirm('Xác nhận đã tìm thấy / trao trả thành công đồ vật này?');">
                        <i class="fas fa-check"></i> Đã xong
                    </a>
                `
                : '';

            return `
                <article class="col-12 col-md-6 tm-post mb-5">

                    <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between shadow-sm">

                        <div>

                            <div class="position-relative mb-3">

                                <img
                                    src="${imageURL}"
                                    alt="Image"
                                    class="img-fluid rounded"
                                    style="height: 200px; width: 100%; object-fit: cover;"
                                    onerror="this.src='img/default.png';"
                                >

                                ${typeBadge}
                                ${statusBadge}

                            </div>

                            <h4 class="tm-color-primary font-weight-bold">
                                ${title}
                            </h4>

                            <p class="small text-muted mt-2">

                                <strong>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    Địa điểm:
                                </strong>
                                ${location}

                                <br>

                                <strong>
                                    <i class="fas fa-clock text-info"></i>
                                    Ngày đăng:
                                </strong>
                                ${createdAt}

                            </p>

                        </div>


                        <!-- ACTION BUTTONS -->
                        <div class="pt-3 border-top mt-3">

                            <div class="btn-group w-100 mb-2" role="group">

                                <a href="postedit.php?id=${itemId}"
                                class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Chỉnh sửa
                                </a>

                                ${resolvedButton}

                                <a href="delete_post.php?id=${itemId}"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>

                            </div>

                        </div>

                    </div>

                </article>
            `;
        }


        // Hiển thị danh sách bài đăng
        function renderItems(items) {

            if (!Array.isArray(items) || items.length === 0) {

                postsContainer.innerHTML = `
                    <div class="col-12 text-center py-5">

                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>

                        <h4>Bạn chưa tạo bài đăng nào!</h4>

                        <a href="create_post.php" class="btn btn-primary mt-3">
                            Tạo bài đăng ngay
                        </a>

                    </div>
                `;

                return;
            }

            postsContainer.innerHTML = items
                .map(item => renderItem(item))
                .join('');
        }


        // Gọi REST API
        async function loadItems() {

            postsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-3">Đang tải bài đăng...</p>
                </div>
            `;

            try {

                // Lấy dữ liệu từ form
                const formData = new FormData(searchForm);

                const params = new URLSearchParams();

                for (const [key, value] of formData.entries()) {

                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }

                const queryString = params.toString();

                const url = queryString
                    ? `${API_URL}?${queryString}`
                    : API_URL;


                // Gửi GET request đến API
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin'
                });

                const result = await response.json();


                // Kiểm tra lỗi đăng nhập
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Không thể tải bài đăng.');
                }


                // Nhận JSON và hiển thị
                renderItems(result.data);

            } catch (error) {

                console.error('API Error:', error);

                postsContainer.innerHTML = `
                    <div class="col-12 text-center py-5">

                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>

                        <h4>Không thể tải danh sách bài đăng.</h4>

                        <p class="text-muted">
                            ${escapeHTML(error.message)}
                        </p>

                        <button class="btn btn-outline-primary mt-2"
                                onclick="loadItems()">
                            Thử lại
                        </button>

                    </div>
                `;
            }
        }


        // Ngăn form tải lại trang
        searchForm.addEventListener('submit', function (event) {

            event.preventDefault();

            loadItems();

        });


        // Tải danh sách khi mở trang
        document.addEventListener('DOMContentLoaded', function () {

            loadItems();

        });

    </script>

</body>
</html>
