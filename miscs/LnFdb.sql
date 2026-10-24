-- ============================================================
-- KHỞI TẠO CƠ SỞ DỮ LIỆU: campus_lost_and_found
-- ============================================================

DROP TABLE IF EXISTS Items;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Categories;
DROP TABLE IF EXISTS Locations;

-- ============================================================
-- 1. TẠO BẢNG USERS (Người dùng & Quản trị viên)
-- ============================================================
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(50) NOT NULL,
    role ENUM('STUDENT', 'ADMIN') DEFAULT 'STUDENT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TẠO BẢNG CATEGORIES (Danh mục đồ vật)
-- ============================================================
CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TẠO BẢNG LOCATIONS (Địa điểm / Khu vực trong Campus)
-- ============================================================
CREATE TABLE Locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TẠO BẢNG ITEMS (Bài đăng tin thất lạc / nhặt đồ)
-- ============================================================
CREATE TABLE Items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,                       -- Khóa ngoại (Nguồn đăng tin)
    category_id INT DEFAULT NULL,                   -- Khóa ngoại Danh mục
    location_id INT DEFAULT NULL,                   -- Khóa ngoại Địa điểm
    
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('LOST', 'FOUND') NOT NULL,            -- Phân loại: Mất đồ / Nhặt được đồ
    image_url VARCHAR(255) DEFAULT NULL,
    date_event DATETIME NOT NULL,                   -- Ngày giờ xảy ra sự việc (mất/nhặt)
    
    contact_phone VARCHAR(20) NOT NULL,             -- SĐT liên hệ riêng cho bài đăng (Mặc định lấy từ Users.phone)
    alt_contact TEXT DEFAULT NULL,                  -- Thông tin liên hệ thêm (Zalo, Facebook link, v.v.)
    
    status ENUM('OPEN', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN', 
    -- OPEN: Đã duyệt/Đang hiển thị công khai
    -- RESOLVED: Đã tìm thấy / Đã trao trả thành công
    -- CLOSED: Bài đăng bị đóng / Hết hạn
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Thiết lập khóa ngoại
    CONSTRAINT fk_items_users FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL,
    CONSTRAINT fk_items_categories FOREIGN KEY (category_id) REFERENCES Categories(category_id) ON DELETE SET NULL,
    CONSTRAINT fk_items_locations FOREIGN KEY (location_id) REFERENCES Locations(location_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. CHÈN DỮ LIỆU MẪU
-- ============================================================

INSERT INTO Users (fullname, email, phone, password, role) VALUES
('Nguyễn Văn A', 'student@campus.edu.vn', '0901234567', '827ccb0eea8a706c4c34a16891f84e7b', 'STUDENT'),
('Quản Trị Viên (Admin)', 'admin@campus.edu.vn', '0999999999', '827ccb0eea8a706c4c34a16891f84e7b', 'ADMIN');

INSERT INTO Categories (category_id, category_name) VALUES 
(1, 'Thiết bị điện tử'),
(2, 'Giấy tờ tùy thân'),
(3, 'Chìa khóa / Thiết bị mở khóa'),
(4, 'Tài chính / vật phẩm giá trị'),
(5, 'Vật dụng cá nhân'),
(6, 'Đồ dùng y tế'),
(7, 'Dụng cụ học tập'),
(8, 'Khác');

INSERT INTO Locations (location_id, location_name) VALUES 
(1, 'Tòa A'),
(2, 'Tòa B'),
(3, 'Tòa C'),
(4, 'Cầu A-B'),
(5, 'Cầu A-C'),
(6, 'Tòa D'),
(7, 'Tòa E'),
(8, 'Tòa F'),
(9, 'Tòa G (thư viện)'),
(10, 'Tòa H'),
(11, 'Tòa I'),
(12, 'Tòa K'),
(13, 'Tòa L'),
(14, 'Tòa M (tòa quân sự)'),
(15, 'Căng tin'),
(16, 'Sân vận động'),
(17, 'Nhà thi đấu'),
(18, 'Bãi xe tòa A'),
(19, 'Bãi xe tòa G'),
(20, 'Bãi xe nhà thi đấu'),
(21, 'Khác');

-- ============================================================
-- 6. CHÈN DỮ LIỆU MẪU CHO BẢNG ITEMS (Bài đăng tin thất lạc / nhặt đồ)
-- ============================================================

INSERT INTO Items (
    user_id, 
    category_id, 
    location_id, 
    title, 
    description, 
    type, 
    date_event, 
    contact_phone, 
    alt_contact, 
    status
) VALUES 
-- 1. Bài đăng MẤT ĐỒ (Loại: LOST, Trạng thái: OPEN)
(
    1, 
    1, 
    9, 
    'Thất lạc laptop Dell XPS 13 màu xám', 
    'Mình có để quên một chiếc laptop Dell XPS 13 inch màu xám trong túi chống sốc màu đen tại bàn học tầng 2 thư viện Tòa G. Bên trong có nhiều tài liệu học tập quan trọng, ai nhặt được cho mình xin lại.', 
    'LOST', 
    '2026-03-20 10:30:00', 
    '0901234567', 
    'Facebook: fb.com/nguyenvana / Zalo: 0901234567', 
    'OPEN'
),

-- 2. Bài đăng NHẶT ĐƯỢC ĐỒ (Loại: FOUND, Trạng thái: OPEN)
(
    2, 
    3, 
    15, 
    'Nhặt được chùm chìa khóa xe Honda có móc khóa Doraemon', 
    'Trưa nay lúc 11h45 mình ăn cơm ở Căng tin thì thấy chùm chìa khóa gồm 1 chìa xe Smartkey Honda và 2 chìa khóa phòng rớt ở ghế. Đã gửi lại cho cô quản lý căng tin hoặc liên hệ mình để xác nhận.', 
    'FOUND', 
    '2026-03-22 11:45:00', 
    '0999999999', 
    'Bàn số 4 Căng tin hoặc Phòng Bảo vệ Tòa A', 
    'OPEN'
),

-- 3. Bài đăng MẤT ĐỒ (Loại: LOST, Trạng thái: OPEN)
(
    1, 
    2, 
    18, 
    'Rớt ví tiền màu nâu chứa Căn cước công dân và Thẻ sinh viên', 
    'Mình bị rơi ví da nam màu nâu trong khoảng từ Bãi xe tòa A sang Tòa C. Bên trong có CCCD mang tên Nguyễn Văn A, 1 thẻ ATM BIDV và một ít tiền mặt. Mình xin nhận lại và hậu tạ ạ.', 
    'LOST', 
    '2026-03-24 07:15:00', 
    '0901234567', 
    'SĐT/Zalo: 0901234567', 
    'OPEN'
),

-- 4. Bài đăng NHẶT ĐƯỢC ĐỒ (Loại: FOUND, Trạng thái: RESOLVED - Đã trao trả thành công)
(
    2, 
    7, 
    1, 
    'Nhặt được tai thoại AirPods Pro 2 ở phòng A.203', 
    'Mình nhặt được hộp tai nghe AirPods Pro màu trắng còn trong case nhựa trong suốt trên bàn giáo viên phòng A.203 sau giờ học ca sáng.', 
    'FOUND', 
    '2026-03-15 11:30:00', 
    '0999999999', 
    'Liên hệ Phòng Quản lý Sinh viên', 
    'RESOLVED'
),

-- 5. Bài đăng MẤT ĐỒ (Loại: LOST, Trạng thái: OPEN)
(
    1, 
    5, 
    16, 
    'Quên áo khoác dù màu xanh đen tại Sân vận động', 
    'Chiều qua lúc đá bóng mình có để áo khoác dù màu xanh đen nhãn hiệu Nike ở khán đài B Sân vận động. Ai cầm nhầm cho mình xin lại nhé.', 
    'LOST', 
    '2026-03-25 17:30:00', 
    '0901234567', 
    'SMS/Call trực tiếp', 
    'OPEN'
),

-- 6. MẤT ĐỒ: Điện thoại
(
    1,
    4,
    5,
    'Làm rơi điện thoại Samsung Galaxy S24 màu đen',
    'Mình làm rơi điện thoại Samsung Galaxy S24 màu đen, có ốp lưng trong suốt và hình dán nhỏ phía sau. Lần cuối mình nhớ đã sử dụng điện thoại tại khu vực ghế đá gần giảng đường. Ai nhặt được vui lòng liên hệ giúp mình.',
    'LOST',
    '2026-03-26 14:20:00',
    '0901234567',
    'Zalo: 0901234567',
    'OPEN'
),

-- 7. NHẶT ĐƯỢC ĐỒ: Thẻ sinh viên
(
    2,
    2,
    7,
    'Nhặt được thẻ sinh viên gần giảng đường',
    'Mình nhặt được một thẻ sinh viên ở lối đi trước giảng đường vào khoảng 9 giờ sáng. Thẻ còn nguyên vẹn. Chủ nhân vui lòng liên hệ và cung cấp thông tin để xác nhận trước khi nhận lại.',
    'FOUND',
    '2026-03-27 09:05:00',
    '0999999999',
    'Có thể nhận tại Phòng Bảo vệ Tòa A',
    'OPEN'
),

-- 8. MẤT ĐỒ: Bình nước
(
    1,
    6,
    12,
    'Thất lạc bình nước giữ nhiệt màu trắng',
    'Mình để quên một bình nước giữ nhiệt màu trắng, dung tích khoảng 750ml, có nắp màu bạc tại khu vực tự học. Bình có dán một nhãn sticker hình mèo ở thân. Nếu ai thấy vui lòng báo giúp mình.',
    'LOST',
    '2026-03-28 15:40:00',
    '0901234567',
    'Facebook: fb.com/nguyenvana',
    'OPEN'
),

-- 9. NHẶT ĐƯỢC ĐỒ: Ô dù
(
    2,
    5,
    3,
    'Nhặt được ô dù gấp màu xanh tại hành lang',
    'Mình nhặt được một chiếc ô gấp màu xanh đậm bị bỏ quên trên ghế ở hành lang tầng 1. Mình đã giữ lại để tránh thất lạc. Chủ nhân vui lòng mô tả thêm đặc điểm của ô để xác nhận.',
    'FOUND',
    '2026-03-29 16:10:00',
    '0999999999',
    'Liên hệ qua Zalo: 0999999999',
    'OPEN'
),

-- 10. MẤT ĐỒ: Sách
(
    1,
    8,
    9,
    'Mất sách giáo trình Cấu trúc dữ liệu và giải thuật',
    'Mình bị thất lạc một quyển giáo trình Cấu trúc dữ liệu và giải thuật bìa xanh dương tại thư viện. Bên trong có ghi tên ở trang đầu và một số ghi chú bằng bút chì. Ai tìm thấy xin liên hệ giúp mình.',
    'LOST',
    '2026-03-30 10:00:00',
    '0901234567',
    'SĐT/Zalo: 0901234567',
    'OPEN'
),

-- 11. NHẶT ĐƯỢC ĐỒ: Chuột máy tính
(
    2,
    1,
    14,
    'Nhặt được chuột máy tính Logitech màu đen',
    'Mình nhặt được một con chuột máy tính Logitech màu đen ở phòng máy sau giờ thực hành buổi chiều. Chuột có dây và còn hoạt động bình thường. Chủ nhân vui lòng mô tả thêm để nhận lại.',
    'FOUND',
    '2026-04-01 16:45:00',
    '0999999999',
    'Liên hệ qua số điện thoại hoặc gặp tại phòng máy',
    'OPEN'
),

-- 12. MẤT ĐỒ: Tai nghe
(
    1,
    7,
    20,
    'Làm rơi tai nghe Bluetooth màu trắng',
    'Mình làm rơi một bên tai nghe Bluetooth màu trắng trong hộp đựng nhỏ. Có thể mình đã đánh rơi khi di chuyển từ khu vực căn tin đến bãi giữ xe. Ai nhặt được vui lòng liên hệ giúp mình.',
    'LOST',
    '2026-04-02 12:30:00',
    '0901234567',
    'Zalo: 0901234567',
    'CLOSED'
),

-- 13. NHẶT ĐƯỢC ĐỒ: Balo
(
    2,
    3,
    11,
    'Nhặt được balo màu xám tại khu vực tự học',
    'Mình nhặt được một chiếc balo màu xám đặt dưới gầm bàn tại khu vực tự học. Bên trong có một số đồ dùng học tập. Mình không đăng chi tiết các vật dụng để chủ nhân có thể xác nhận khi liên hệ.',
    'FOUND',
    '2026-04-03 18:00:00',
    '0999999999',
    'Có thể liên hệ để hẹn địa điểm nhận lại',
    'CLOSED'
),

-- 14. MẤT ĐỒ: Thẻ ngân hàng
(
    1,
    2,
    18,
    'Thất lạc thẻ ngân hàng tại khuôn viên trường',
    'Mình làm thất lạc một thẻ ngân hàng khi di chuyển trong khuôn viên trường. Mình không ghi thông tin số thẻ để đảm bảo an toàn. Nếu ai nhặt được, vui lòng liên hệ và mô tả địa điểm nhặt được để xác nhận.',
    'LOST',
    '2026-04-04 08:15:00',
    '0901234567',
    'Zalo: 0901234567',
    'RESOLVED'
),

-- 15. NHẶT ĐƯỢC ĐỒ: Hộp bút
(
    2,
    6,
    2,
    'Nhặt được hộp bút màu đen trong lớp học',
    'Mình nhặt được một hộp bút màu đen có khóa kéo ở ngăn bàn sau giờ học. Bên trong có bút bi và bút chì. Chủ nhân vui lòng liên hệ, mô tả thêm đặc điểm của hộp bút để nhận lại.',
    'FOUND',
    '2026-04-05 11:20:00',
    '0999999999',
    'Liên hệ trực tiếp qua số điện thoại',
    'RESOLVED'
);
