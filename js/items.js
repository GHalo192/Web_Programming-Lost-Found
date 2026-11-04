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

function getImageURL(imageURL) {

    if (!imageURL) {
        return 'img/default.png';
    }

    const value = String(imageURL).trim();

    if (
        value.startsWith('http://') ||
        value.startsWith('https://') ||
        value.startsWith('/') ||
        value.startsWith('./') ||
        value.startsWith('../') ||
        value.startsWith('uploads/')
    ) {
        return value;
    }

    return 'img/default.png';
}

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

    params.append('status', PAGE_STATUS);
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

