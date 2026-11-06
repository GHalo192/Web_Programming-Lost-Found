// ==========================================
// CONFIGURATION
// ==========================================

const PROFILE_ID = window.ABOUT_USER_CONFIG.profileId;
const API_URL = 'api/users/detail.php';


// ==========================================
// DOM ELEMENTS
// ==========================================

const profileContainer = document.getElementById('profileContainer');
const totalItems = document.getElementById('totalItems');
const itemsContainer = document.getElementById('itemsContainer');
const paginationContainer = document.getElementById('paginationContainer');


// ==========================================
// RENDER FUNCTIONS
// ==========================================

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

// Hiển thị thanh phân trang
function renderPagination(pagination) {
    if (!paginationContainer) return;

    const currentPage = Number(pagination.current_page);
    const totalPages = Number(pagination.total_pages);

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <div class="col-12 d-flex justify-content-center">
            <nav aria-label="Page navigation">
                <ul class="pagination">
    `;

    if (currentPage > 1) {
        html += `
            <li class="page-item">
                <a href="#" class="page-link" data-page="${currentPage - 1}">&laquo; Trước</a>
            </li>
        `;
    }

    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a href="#" class="page-link" data-page="${i}">${i}</a>
            </li>
        `;
    }

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


// ==========================================
// API / DATA FUNCTIONS
// ==========================================

async function loadProfile(page = 1) {
    if (PROFILE_ID <= 0) {
        profileContainer.innerHTML = `<div class="alert alert-danger">ID người dùng không hợp lệ.</div>`;
        itemsContainer.innerHTML = '';
        return;
    }

    itemsContainer.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="mt-3">Đang tải danh sách bài đăng...</p>
        </div>
    `;

    try {
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


// ==========================================
// EVENT LISTENERS
// ==========================================

if (paginationContainer) {
    paginationContainer.addEventListener('click', function(e) {
        const pageLink = e.target.closest('[data-page]');
        if (!pageLink) return;

        e.preventDefault();
        const selectedPage = Number(pageLink.dataset.page);
        loadProfile(selectedPage);
    });
}


// ==========================================
// INITIALIZATION
// ==========================================

loadProfile(1);