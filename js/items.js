// ==========================================
// CONFIGURATION
// ==========================================

const PAGE_MODE = window.ITEMS_CONFIG.mode;
const API_URL = window.ITEMS_CONFIG.apiUrl;
const PAGE_STATUS = window.ITEMS_CONFIG.status;
const CSRF_TOKEN = window.ITEMS_CONFIG.csrfToken;

// ==========================================
// HTML ELEMENTS
// ==========================================

const postsContainer = document.getElementById('postsContainer');
const paginationContainer = document.getElementById('paginationContainer');
const searchForm = document.getElementById('searchForm');


// ==========================================
// LOAD ITEMS FROM REST API
// ==========================================

async function loadItems(page = 1, updateHistory = true) {

    postsContainer.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="mt-3">Đang tải bài đăng...</p>
        </div>
    `;

    const params = new URLSearchParams();

    // ======================================
    // PUBLIC MODE
    // ======================================

    if (PAGE_MODE === 'public') {

        const keyword = document.getElementById('keyword').value.trim();
        const type = document.getElementById('type').value;
        const category_id = document.getElementById('category_id').value;
        const location_id = document.getElementById('location_id').value;

        if (keyword) params.append('keyword', keyword);
        if (type) params.append('type', type);
        if (category_id) params.append('category_id', category_id);
        if (location_id) params.append('location_id', location_id);

        if (PAGE_STATUS !== '') {
            params.append('status', PAGE_STATUS);
        }        
        params.append('page', page);

    }

    // ======================================
    // SELF MODE
    // ======================================


    else if (PAGE_MODE === 'self') {

        const formData = new FormData(searchForm);

        for (const [key, value] of formData.entries()) {

            if (value.trim() !== '') {
                params.set(key, value);
            }

        }

        // Chỉ sử dụng PAGE_STATUS nếu trang có trạng thái cố định
        // Ví dụ: trang chỉ hiển thị bài đăng OPEN.
        if (PAGE_STATUS !== '') {
            params.set('status', PAGE_STATUS);
        }

        // Tham số phân trang
        params.set('page', page);

    }

    const queryString = params.toString();

// [CẬP NHẬT 2]: Cập nhật URL trên trình duyệt cho cả public và self
    if (updateHistory) {
        history.pushState(
            { page: page },
            '',
            window.location.pathname + '?' + queryString
        );
    }

    const url = queryString
        ? `${API_URL}?${queryString}`
        : API_URL;

    if (paginationContainer) {
        paginationContainer.innerHTML = '';
    }

    try {

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (response.status === 401) {
            window.location.href = 'login.php';
            return;
        }

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Không thể tải bài đăng.');
        }

        // Gọi function điều phối
        renderPosts(result.data);

        // [CẬP NHẬT 3]: Render phân trang bất kể mode nào (miễn là API trả về kết quả pagination)
        if (result.pagination) {
            renderPagination(result.pagination);
        }

    } catch (error) {

        console.error('API Error:', error); 

        postsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                <h4>Không thể tải danh sách bài đăng.</h4>
                <p class="text-muted">${escapeHTML(error.message)}</p>
                <button class="btn btn-outline-primary mt-2"
                        onclick="loadItems()">
                    Thử lại
                </button>
            </div>
        `;

    }
}

// ==========================================
// RENDER ITEMS
// ==========================================

function renderMyItem(item) {

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

    // Status action buttons
    let statusButtons = '';

    if (item.status === 'OPEN') {

        statusButtons = `
            <button
                type="button"
                class="btn btn-sm btn-outline-success"
                onclick="updateItemStatus(${itemId}, 'RESOLVED')">

                <i class="fas fa-check"></i> Đã xong

            </button>

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                onclick="updateItemStatus(${itemId}, 'CLOSED')">

                <i class="fas fa-lock"></i> Đóng

            </button>
        `;

    } else if (item.status === 'RESOLVED') {

        statusButtons = `
            <button
                type="button"
                class="btn btn-sm btn-outline-warning"
                onclick="updateItemStatus(${itemId}, 'OPEN')">

                <i class="fas fa-undo"></i> Mở lại

            </button>

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                onclick="updateItemStatus(${itemId}, 'CLOSED')">

                <i class="fas fa-lock"></i> Đóng

            </button>
        `;

    } else if (item.status === 'CLOSED') {

        statusButtons = `
            <button
                type="button"
                class="btn btn-sm btn-secondary"
                disabled>

                <i class="fas fa-lock"></i> Đã đóng

            </button>
        `;

    }

    return `
        <article class="col-12 col-md-6 tm-post mb-5">

            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between shadow-sm">

                <div>

                    <div class="position-relative mb-3">

                        <a href="about-post.php?id=${itemId}">
                            <img
                                src="${imageURL}"
                                alt="${title}"
                                class="img-fluid rounded"
                                style="height: 200px; width: 100%; object-fit: cover;"
                                onerror="this.src='img/default.png';"
                            >
                        </a>

                        ${typeBadge}
                        ${statusBadge}

                    </div>

                    <h4 class="tm-color-primary font-weight-bold">
                        <a href="about-post.php?id=${itemId}"
                        class="text-decoration-none">
                            ${title}
                        </a>
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

                        ${statusButtons}

                        <button
                            type="button"
                            class="btn btn-danger"
                            onclick="deleteItem(${itemId})">
                            Xóa bài đăng
                        </button>

                    </div>

                </div>

            </div>

        </article>
    `;
}


// ==========================================
// RENDER POSTS
// ==========================================

function renderPublicPosts(items) {

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

function renderMyPosts(items) {

    if (!Array.isArray(items) || items.length === 0) {

        postsContainer.innerHTML = `
            <div class="col-12 text-center py-5">

                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>

                <h4>Bạn chưa tạo bài đăng nào!</h4>

                <a href="itempost.php" class="btn btn-primary mt-3">
                    Tạo bài đăng ngay
                </a>

            </div>
        `;

        return;
    }

    postsContainer.innerHTML = items
        .map(item => renderMyItem(item))
        .join('');
}

function renderPosts(items) {

    if (PAGE_MODE === 'public') {
        renderPublicPosts(items);
    }

    else if (PAGE_MODE === 'self') {
        renderMyPosts(items);
    }

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
// USER ACTIONS
// ==========================================

// Delete item
async function deleteItem(itemId) {
    // Xác nhận trước khi xóa
    const confirmed = confirm(
        "Bạn có chắc chắn muốn xóa bài đăng này không?"
    );

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch(
            `api/items/item.php?id=${itemId}`,
            {
                method: "DELETE",
                headers: {
                    "X-CSRF-Token": CSRF_TOKEN
                }
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            alert(result.message || "Không thể xóa bài đăng.");
            return;
        }

        alert("Xóa bài đăng thành công!");

        // Tải lại danh sách bài đăng
        window.location.reload();

    } catch (error) {
        console.error("Delete error:", error);
        alert("Đã xảy ra lỗi khi kết nối đến máy chủ.");
    }
}

// Update item status

// Update item status
async function updateItemStatus(itemId, newStatus) {

    if (!confirm('Bạn có chắc chắn muốn thay đổi trạng thái bài đăng?')) {
        return;
    }

    try {
        const response = await fetch(
            `api/items/update-status.php?id=${encodeURIComponent(itemId)}`,
            {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    status: newStatus
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Could not update item status.'
            );
        }

        alert('Cập nhật trạng thái thành công!');

        // Reload danh sách bài đăng
        loadItems();

    } catch (error) {
        console.error('Update status error:', error);
        alert(error.message);
    }
}

// ==========================================
// SEARCH FORM EVENT
// ==========================================

if (searchForm) {

    searchForm.addEventListener('submit', function(e) {

        e.preventDefault();

        loadItems(1);

    });

}


// ==========================================
// PAGINATION EVENT
// ==========================================

if (paginationContainer) {

    paginationContainer.addEventListener('click', function(e) {

        const pageLink = e.target.closest('[data-page]');

        if (!pageLink) return;

        e.preventDefault();

        loadItems(Number(pageLink.dataset.page));

    });

}


// ==========================================
// BROWSER BACK / FORWARD
// ==========================================

window.addEventListener('popstate', function() {

    const params = new URLSearchParams(window.location.search);
    
    document.getElementById('keyword').value =
        params.get('keyword') || '';

    document.getElementById('type').value =
        params.get('type') || '';

    const statusSelect = document.getElementById('status');
        if (statusSelect) {
            statusSelect.value = params.get('status') || '';
    }

    document.getElementById('category_id').value =
        params.get('category_id') || '';

    document.getElementById('location_id').value =
        params.get('location_id') || '';

    const page = Number(params.get('page')) || 1;

    loadItems(page, false);

});


// ==========================================
// INITIAL LOAD
// ==========================================

loadItems(1);