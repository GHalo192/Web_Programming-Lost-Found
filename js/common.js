// ==========================================
// 1. HTML HELPERS
// ==========================================

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

// ==========================================
// 2. DATE HELPERS
// ==========================================

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
// 3. IMAGE HELPERS
// ==========================================

// Xử lý đường dẫn hình ảnh
function getImageURL(imageURL) {
    if (!imageURL) return 'img/default.png';

    const value = String(imageURL).trim();

    if (/^https?:\/\//i.test(value)) return value;

    if (
        value.startsWith('/') ||
        value.startsWith('./') ||
        value.startsWith('../') ||
        value.startsWith('uploads/')
    ) {
        return value;
    }

    return 'img/default.png';
}


// ==========================================
// 4. BADGE HELPERS
// ==========================================

// Hiển thị badge loại bài đăng
function renderTypeBadge(type) {
    if (type === 'LOST') {
        return `
            <span class="badge badge-danger p-2" style="font-size: 1rem;">
                <i class="fas fa-exclamation-circle"></i> BÁO MẤT ĐỒ
            </span>
        `;
    }

    return `
        <span class="badge badge-success p-2" style="font-size: 1rem;">
            <i class="fas fa-hand-holding-heart"></i> NHẶT ĐƯỢC ĐỒ
        </span>
    `;
}

// Hiển thị badge trạng thái
function renderStatusBadge(status) {

    switch (status) {

        case 'OPEN':
            return `
                <span class="badge badge-primary p-2">
                    <i class="fas fa-eye"></i> ĐANG MỞ
                </span>
            `;

        case 'RESOLVED':
            return `
                <span class="badge badge-success p-2">
                    <i class="fas fa-check-circle"></i> ĐÃ GIẢI QUYẾT
                </span>
            `;

        case 'CLOSED':
            return `
                <span class="badge badge-secondary p-2">
                    <i class="fas fa-lock"></i> ĐÃ ĐÓNG
                </span>
            `;

        default:
            return '';
    }
}













