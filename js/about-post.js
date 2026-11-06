// ==========================================
// 1. CONFIGURATION
// ==========================================

const ITEM_ID = window.ABOUT_POST_CONFIG.itemId;
const CURRENT_USER_ID = window.ABOUT_POST_CONFIG.currentUserId;
const IS_ADMIN = window.ABOUT_POST_CONFIG.isAdmin;
const CSRF_TOKEN = window.ABOUT_POST_CONFIG.csrfToken;

const API_URL = 'api/items/detail.php';


// ==========================================
// 2. DOM ELEMENTS
// ==========================================

const postContainer = document.getElementById('postContainer');

// ==========================================
// 3. ACTION RENDERING FUNCTIONS
// ==========================================

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

                ${post.status === 'RESOLVED' ? `
                    <button class="btn btn-sm btn-outline-warning"
                            onclick="updatePostStatus('OPEN')">
                        <i class="fas fa-folder-open"></i> Mở lại
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

// ==========================================
// 4. RENDER FUNCTIONS
// ==========================================

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


// ==========================================
// 5. API / DATA FUNCTIONS
// ==========================================

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


// ==========================================
// 6. ACTION FUNCTIONS
// ==========================================

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


// ==========================================
// 7. INITIALIZATION
// ==========================================

// Cho phép các nút onclick trong HTML gọi được function
window.updatePostStatus = updatePostStatus;
window.deletePost = deletePost;


// ==========================================
// 8. FUNCTION CALLS
// ==========================================

// Chạy khi trang được tải
loadPost();