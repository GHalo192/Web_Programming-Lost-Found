
<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Tạo CSRF token nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Kiểm tra item ID
$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$itemId || $itemId <= 0) {
    header('Location: itemself.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chỉnh sửa bài đăng - Campus Lost&Found</title>

    <link rel="stylesheet" href="fontawesome/css/all.min.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap"
        rel="stylesheet"
    >

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-xtra-blog.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <style>
        .edit-form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .image-preview {
            width: 100%;
            max-width: 400px;
            height: 250px;
            object-fit: contain;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .required-mark {
            color: red;
        }

        .form-group label {
            font-weight: 600;
        }
    </style>
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
                            <i class="fas fa-home"></i>
                            Trang Chủ
                        </a>
                    </li>

                    <li class="tm-nav-item active">
                        <a href="itemself.php" class="tm-nav-link">
                            <i class="fas fa-folder"></i>
                            Đồ của tôi
                        </a>
                    </li>

                    <li class="tm-nav-item">
                        <a href="itempost.php" class="tm-nav-link">
                            <i class="fas fa-plus"></i>
                            Báo nhặt / mất đồ
                        </a>
                    </li>

                </ul>
            </nav>

            <!-- ACCOUNT INFORMATION -->
            <div class="tm-mb-65 user-auth-box">

                <div class="user-profile-info p-3 mb-2 bg-light rounded text-dark">

                    <div class="font-weight-bold">
                        <i class="fas fa-user-circle mr-1"></i>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION['fullname'] ?? 'Thành viên'
                        );
                        ?>
                    </div>

                    <div class="text-muted small">
                        <i class="fas fa-envelope mr-1"></i>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION['email'] ?? ''
                        );
                        ?>
                    </div>

                </div>

                <a href="logout.php"
                   class="btn btn-outline-danger btn-block btn-sm">

                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất

                </a>

            </div>

            <h2 class="tm-mb-80 pr-5 text-white">
                Tìm lại đồ thất lạc trong Campus
            </h2>

        </div>

    </header>
    <!-- END SIDEBAR -->


    <!-- MAIN CONTENT -->
    <div class="container-fluid">

        <main class="tm-main">

            <div class="row mb-4">

                <div class="col-12">

                    <h2 class="tm-color-primary">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa bài đăng
                    </h2>

                    <p class="text-muted">
                        Cập nhật thông tin bài đăng của bạn bên dưới.
                    </p>

                </div>

            </div>


            <!-- LOADING -->
            <div id="loadingMessage" class="text-center py-5">

                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>

                <p class="mt-3">
                    Đang tải thông tin bài đăng...
                </p>

            </div>


            <!-- ERROR MESSAGE -->
            <div id="errorMessage"
                 class="alert alert-danger"
                 style="display: none;">
            </div>


            <!-- EDIT FORM -->
            <div id="formContainer" style="display: none;">

                <div class="edit-form-container">

                    <form id="editForm"
                          enctype="multipart/form-data">

                        <!-- TITLE -->
                        <div class="form-group">

                            <label for="title">
                                Tiêu đề
                                <span class="required-mark">*</span>
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="title"
                                name="title"
                                maxlength="255"
                                required
                            >

                        </div>


                        <!-- DESCRIPTION -->
                        <div class="form-group">

                            <label for="description">
                                Mô tả chi tiết
                                <span class="required-mark">*</span>
                            </label>

                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="5"
                                required
                            ></textarea>

                        </div>


                        <div class="row">

                            <!-- TYPE -->
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="type">
                                        Loại bài đăng
                                        <span class="required-mark">*</span>
                                    </label>

                                    <select
                                        class="form-control"
                                        id="type"
                                        name="type"
                                        required
                                    >

                                        <option value="LOST">
                                            Báo mất đồ
                                        </option>

                                        <option value="FOUND">
                                            Nhặt được đồ
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <!-- CATEGORY -->
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="category_id">
                                        Danh mục
                                        <span class="required-mark">*</span>
                                    </label>

                                    <select
                                        class="form-control"
                                        id="category_id"
                                        name="category_id"
                                        required
                                    >

                                        <option value="">
                                            Đang tải danh mục...
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- LOCATION -->
                        <div class="form-group">

                            <label for="location_id">
                                Khu vực xảy ra sự việc
                                <span class="required-mark">*</span>
                            </label>

                            <select
                                class="form-control"
                                id="location_id"
                                name="location_id"
                                required
                            >

                                <option value="">
                                    Đang tải khu vực...
                                </option>

                            </select>

                        </div>


                        <!-- DATE EVENT -->
                        <div class="form-group">

                            <label for="date_event">
                                Thời gian xảy ra sự việc
                                <span class="required-mark">*</span>
                            </label>

                            <input
                                type="datetime-local"
                                class="form-control"
                                id="date_event"
                                name="date_event"
                                required
                            >

                        </div>


                        <div class="row">

                            <!-- CONTACT PHONE -->
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="contact_phone">
                                        Số điện thoại liên hệ
                                        <span class="required-mark">*</span>
                                    </label>

                                    <input
                                        type="tel"
                                        class="form-control"
                                        id="contact_phone"
                                        name="contact_phone"
                                        maxlength="20"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- ALT CONTACT -->
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label for="alt_contact">
                                        Liên hệ bổ sung
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="alt_contact"
                                        name="alt_contact"
                                        placeholder="Facebook, Zalo, Email..."
                                    >

                                </div>

                            </div>

                        </div>


                        <hr>


                        <!-- IMAGE -->
                        <div class="form-group">

                            <label>
                                Hình ảnh bài đăng
                            </label>

                            <div class="mb-3">

                                <img
                                    id="imagePreview"
                                    src="img/default.png"
                                    alt="Image preview"
                                    class="image-preview"
                                    onerror="this.src='img/default.png';"
                                >

                            </div>

                            <label for="image">
                                Chọn ảnh mới
                            </label>

                            <input
                                type="file"
                                class="form-control-file"
                                id="image"
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >

                            <small class="form-text text-muted">
                                Chấp nhận JPG, PNG, GIF và WEBP.
                                Dung lượng tối đa 5 MB.
                                Nếu không chọn ảnh mới, ảnh hiện tại sẽ được giữ nguyên.
                            </small>

                        </div>


                        <hr>


                        <!-- STATUS INFORMATION -->
                        <div class="alert alert-info">

                            <i class="fas fa-info-circle"></i>

                            Trạng thái bài đăng được quản lý riêng.
                            Chỉnh sửa thông tin tại đây sẽ không thay đổi
                            trạng thái hiện tại của bài đăng.

                        </div>


                        <!-- BUTTONS -->
                        <div class="d-flex flex-wrap justify-content-between">

                            <a href="itemself.php"
                               class="btn btn-outline-secondary mb-2">

                                <i class="fas fa-arrow-left"></i>
                                Quay lại

                            </a>

                            <button
                                type="submit"
                                id="saveButton"
                                class="btn btn-primary mb-2"
                            >

                                <i class="fas fa-save"></i>
                                Lưu thay đổi

                            </button>

                        </div>

                    </form>

                </div>

            </div>


            <!-- FOOTER -->
            <footer class="row tm-row">

                <hr class="col-12">

                <div class="col-md-6 col-12 tm-color-gray">

                    Design:
                    <a href="https://templatemo.com"
                       target="_parent"
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


    <script src="custom.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/templatemo-script.js"></script>


    <!-- REST API -->
    <script>

        // --------------------------------------------------
        // 1. CONFIGURATION
        // --------------------------------------------------

        const API_URL = 'api/items/item.php';

        const ITEM_ID = <?= json_encode($itemId) ?>;

        const CSRF_TOKEN = <?= json_encode(
            $csrfToken,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?>;


        // --------------------------------------------------
        // 2. DOM ELEMENTS
        // --------------------------------------------------

        const editForm = document.getElementById('editForm');

        const loadingMessage = document.getElementById('loadingMessage');

        const errorMessage = document.getElementById('errorMessage');

        const formContainer = document.getElementById('formContainer');

        const imageInput = document.getElementById('image');

        const imagePreview = document.getElementById('imagePreview');

        const saveButton = document.getElementById('saveButton');


        // --------------------------------------------------
        // 3. HELPER FUNCTIONS
        // --------------------------------------------------

        function showError(message) {

            errorMessage.textContent = message;

            errorMessage.style.display = 'block';

        }


        function hideLoading() {

            loadingMessage.style.display = 'none';

        }


        function escapeHTML(value) {

            return String(value ?? '').replace(/[&<>"']/g, function(char) {

                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];

            });

        }


        // --------------------------------------------------
        // 4. POPULATE DROPDOWN
        // --------------------------------------------------

        function populateSelect(
            elementId,
            records,
            idKey,
            nameKey,
            selectedValue
        ) {

            const select = document.getElementById(elementId);

            select.innerHTML = `
                <option value="">-- Vui lòng chọn --</option>
            `;

            records.forEach(function(record) {

                const option = document.createElement('option');

                option.value = record[idKey];

                option.textContent = record[nameKey];

                select.appendChild(option);

            });

            select.value = selectedValue;

        }


        // --------------------------------------------------
        // 5. LOAD ITEM DATA
        // --------------------------------------------------

        async function loadItem() {

            try {

                const response = await fetch(
                    `${API_URL}?id=${ITEM_ID}`,
                    {
                        method: 'GET',
                        credentials: 'same-origin'
                    }
                );

                const result = await response.json();

                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }

                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message || 'Không thể tải bài đăng.'
                    );

                }

                const item = result.data;


                // Điền thông tin cơ bản
                document.getElementById('title').value =
                    item.title ?? '';

                document.getElementById('description').value =
                    item.description ?? '';

                document.getElementById('type').value =
                    item.type ?? 'LOST';

                document.getElementById('date_event').value =
                    item.date_event
                        ? String(item.date_event).slice(0, 16).replace(' ', 'T')
                        : '';

                document.getElementById('contact_phone').value =
                    item.contact_phone ?? '';

                document.getElementById('alt_contact').value =
                    item.alt_contact ?? '';


                // Điền danh mục từ API
                populateSelect(
                    'category_id',
                    result.categories,
                    'category_id',
                    'category_name',
                    item.category_id
                );


                // Điền khu vực từ API
                populateSelect(
                    'location_id',
                    result.locations,
                    'location_id',
                    'location_name',
                    item.location_id
                );


                // Hiển thị ảnh hiện tại
                imagePreview.src =
                    item.image_url || 'img/default.png';


                // Hiển thị form
                hideLoading();

                formContainer.style.display = 'block';

            } catch (error) {

                console.error(error);

                hideLoading();

                showError(error.message);

            }

        }


        // --------------------------------------------------
        // 6. IMAGE PREVIEW
        // --------------------------------------------------

        imageInput.addEventListener('change', function() {

            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp'
            ];

            const maxSize = 5 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {

                alert('Vui lòng chọn ảnh JPG, PNG, GIF hoặc WEBP.');

                this.value = '';

                return;
            }

            if (file.size > maxSize) {

                alert('Dung lượng ảnh không được vượt quá 5 MB.');

                this.value = '';

                return;
            }

            // Hiển thị ảnh mới trước khi upload
            imagePreview.src = URL.createObjectURL(file);

        });


        // --------------------------------------------------
        // 7. UPDATE ITEM
        // --------------------------------------------------

        editForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            if (!editForm.reportValidity()) {
                return;
            }

            saveButton.disabled = true;

            saveButton.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                Đang lưu...
            `;

            try {

                // Lấy toàn bộ dữ liệu form
                const formData = new FormData(editForm);

                // Method override: POST được API hiểu là PATCH
                formData.append('_method', 'PATCH');


                const response = await fetch(
                    `${API_URL}?id=${ITEM_ID}`,
                    {
                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {
                            'X-CSRF-Token': CSRF_TOKEN
                        },

                        // Không tự đặt Content-Type.
                        // Browser sẽ tự tạo multipart/form-data boundary.
                        body: formData
                    }
                );


                const result = await response.json();

                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }

                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message || 'Không thể cập nhật bài đăng.'
                    );

                }


                alert('Cập nhật bài đăng thành công!');

                window.location.href = 'itemself.php';


            } catch (error) {

                console.error(error);

                alert(error.message);

                saveButton.disabled = false;

                saveButton.innerHTML = `
                    <i class="fas fa-save"></i>
                    Lưu thay đổi
                `;

            }

        });


        // --------------------------------------------------
        // 8. INITIALIZE
        // --------------------------------------------------

        document.addEventListener('DOMContentLoaded', function() {

            loadItem();

        });

    </script>

</body>
</html>
