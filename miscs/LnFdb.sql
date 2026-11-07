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
    user_id INT DEFAULT NULL,                      
    category_id INT DEFAULT NULL,                  
    location_id INT DEFAULT NULL,                  
    
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('LOST', 'FOUND') NOT NULL,     
    image_url VARCHAR(255) DEFAULT NULL,
    date_event DATETIME NOT NULL,                 
    
    contact_phone VARCHAR(20) NOT NULL,           
    alt_contact TEXT DEFAULT NULL,                 
    
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
-- 1
(1, 1, 9, 'Thất lạc laptop Dell XPS 13 màu xám', 'Mình có để quên một chiếc laptop Dell XPS 13 inch màu xám trong túi chống sốc màu đen tại bàn học tầng 2 thư viện Tòa G. Bên trong có nhiều tài liệu học tập quan trọng, ai nhặt được cho mình xin lại.', 'LOST', '2026-03-20 10:30:00', '0901234567', 'Facebook: fb.com/nguyenvana / Zalo: 0901234567', 'OPEN'),
-- 2
(2, 2, 3, 'Nhặt được thẻ sinh viên và CCCD', 'Nhặt được một ví da màu nâu bên trong có thẻ sinh viên khoa CNTT và CCCD mang tên Trần Văn B tại sảnh nhà A4.', 'FOUND', '2026-03-22 14:15:00', '0988776655', 'Liên hệ qua Văn phòng Đoàn trường', 'OPEN'),
-- 3
(1, 3, 12, 'Mất chìa khóa xe máy Honda', 'Làm rơi một chùm chìa khóa có dán sticker mèo màu trắng, gồm chìa khóa xe Vision và chìa khóa phòng trọ quanh khu vực bãi giữ xe sinh viên.', 'LOST', '2026-03-23 08:00:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 4
(2, 4, 15, 'Nhặt được bình giữ nhiệt Lock&Lock', 'Bình giữ nhiệt màu xanh ngọc để quên tại phòng học B202 sau buổi học lý thuyết môn Cấu trúc dữ liệu.', 'FOUND', '2026-03-24 17:30:00', '0912345678', 'Facebook: fb.com/admin', 'OPEN'),
-- 5
(1, 5, 5, 'Mất áo khoác dù màu đen có logo trường', 'Áo khoác gió mùa đông size L treo ở ghế đá sân bóng rổ, quay lại tìm thì không thấy.', 'LOST', '2026-03-25 18:00:00', '0901234567', 'Zalo: 0901234567', 'RESOLVED'),
-- 6
(2, 6, 8, 'Nhặt được tai nghe Bluetooth AirPods Pro', 'Tìm thấy một hộp sạc AirPods Pro kèm tai nghe rơi ở hàng ghế chờ căng tin trung tâm.', 'FOUND', '2026-03-26 12:10:00', '0933445566', 'Nhận lại tại quầy bảo vệ căng tin', 'OPEN'),
-- 7
(1, 7, 18, 'Mất giáo trình Xác suất Thống kê', 'Sách có ghi chú tên và lớp ở trang bìa, để quên ở phòng tự học nhà C6.', 'LOST', '2026-03-27 15:45:00', '0901234567', 'Facebook: fb.com/nguyenvana', 'OPEN'),
-- 8
(2, 8, 2, 'Nhặt được ô gấp gọn tự động màu đen', 'Bỏ quên ở hành lang tầng 3 nhà hiệu bộ sau cơn mưa chiều.', 'FOUND', '2026-03-28 16:20:00', '0977889900', 'Zalo: 0977889900', 'OPEN'),
-- 9
(1, 1, 11, 'Mất máy tính bảng iPad Gen 9', 'Để trong balo nhưng bị rách khóa nhẹ và rơi mất lúc di chuyển qua khu vực nhà thi đấu đa năng.', 'LOST', '2026-03-29 09:10:00', '0901234567', 'Số điện thoại: 0901234567', 'OPEN'),
-- 10
(2, 2, 20, 'Nhặt được thẻ ngân hàng Techcombank', 'Nhặt được thẻ ATM đứng tên NGUYEN THI C tại cây ATM khu vực Ký túc xá.', 'FOUND', '2026-03-30 11:00:00', '0911223344', 'Liên hệ bảo vệ KTX', 'OPEN'),
-- 11
(1, 3, 4, 'Mất chìa khóa phòng thí nghiệm', 'Chìa khóa có gắn móc khóa hình con gấu bông nhỏ, đánh rơi gần khu thí nghiệm Hóa học.', 'LOST', '2026-04-01 13:30:00', '0901234567', 'Zalo: 0901234567', 'CLOSED'),
-- 12
(2, 4, 14, 'Nhặt được một chiếc ví da màu đen', 'Bên trong không có tiền mặt nhưng có một số giấy tờ tùy thân và thẻ xe buýt.', 'FOUND', '2026-04-02 14:00:00', '0905556677', 'Facebook: fb.com/admin', 'OPEN'),
-- 13
(1, 5, 7, 'Mất mũ bảo hiểm 3/4 màu xám', 'Mũ sơn nhám, có kính chắn gió phía trước, để ở xe máy tại bãi xe nhà E và bị mất.', 'LOST', '2026-04-03 17:00:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 14
(2, 6, 1, 'Nhặt được cáp sạc điện thoại Type-C', 'Cáp sạc hãng Baseus màu đen nhặt được tại phòng máy tính thực hành 102.', 'FOUND', '2026-04-04 10:15:00', '0966778899', 'Nhận tại phòng trực lab', 'OPEN'),
-- 15
(1, 7, 19, 'Mất tập tài liệu đồ án tốt nghiệp', 'Tập bìa cứng màu xanh dương chứa bản vẽ và báo cáo đồ án ngành Công nghệ thông tin.', 'LOST', '2026-04-05 08:30:00', '0901234567', 'Zalo/Phone: 0901234567', 'OPEN'),
-- 16
(2, 8, 10, 'Nhặt được một chiếc lắc tay bạc', 'Nhặt được ở khu vực bồn hoa trước sân hội trường lớn.', 'FOUND', '2026-04-06 15:20:00', '0944332211', 'Liên hệ Đoàn trường để xác nhận', 'OPEN'),
-- 17
(1, 1, 6, 'Thất lạc chuột không dây Logitech', 'Chuột màu trắng sữa, mã Pebble M350 bị rơi ra khỏi túi xách đoạn đường từ nhà xe lên giảng đường.', 'LOST', '2026-04-07 13:00:00', '0901234567', 'Facebook: fb.com/nguyenvana', 'OPEN'),
-- 18
(2, 2, 13, 'Nhặt được thẻ giữ xe tháng Ký túc xá', 'Thẻ xe số thứ tự #412 để quên tại bàn ghế đá sân KTX.', 'FOUND', '2026-04-08 21:00:00', '0988112233', 'Gặp ban quản lý KTX', 'OPEN'),
-- 19
(1, 3, 21, 'Mất chìa khóa tủ locker cá nhân', 'Chìa khóa số 42 ở khu vực thay đồ nhà thi đấu thể thao.', 'LOST', '2026-04-09 16:45:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 20
(2, 4, 16, 'Nhặt được hộp đựng kính cận', 'Hộp kính màu hồng phấn bên trong có khăn lau kính, nhặt được tại sảnh tòa nhà Khoa học Tự nhiên.', 'FOUND', '2026-04-10 09:50:00', '0919998877', 'Inbox Facebook quản trị viên', 'OPEN'),
-- 21
(1, 5, 17, 'Mất áo hoodie màu xanh navy', 'Áo có chữ in ở ngực trái, để quên trên ghế phòng học A301.', 'LOST', '2026-04-11 11:20:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 22
(2, 6, 9, 'Nhặt được máy tính cầm tay Casio FX-580VN X', 'Máy tính màu đen còn mới, nhặt tại bàn học phòng tự học thư viện tầng 3.', 'FOUND', '2026-04-12 14:40:00', '0922334455', 'Nhận lại tại quầy thông tin thư viện', 'RESOLVED'),
-- 23
(1, 7, 5, 'Mất sổ tay ghi chép cá nhân', 'Sổ tay bìa da màu nâu nhạt, ghi chép các thuật toán và ý tưởng code quan trọng.', 'LOST', '2026-04-13 15:10:00', '0901234567', 'Facebook: fb.com/nguyenvana', 'OPEN'),
-- 24
(2, 8, 3, 'Nhặt được đồng hồ đeo tay điện tử', 'Đồng hồ mặt vuông dây nhựa màu đen rơi gần cửa ra vào phòng hội thảo.', 'FOUND', '2026-04-14 18:00:00', '0933221144', 'Liên hệ phòng công tác sinh viên', 'OPEN'),
-- 25
(1, 1, 12, 'Thất lạc ổ cứng di động Transcend 1TB', 'Ổ cứng màu đen chống sốc, đựng source code các đồ án năm ngoái.', 'LOST', '2026-04-15 10:00:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 26
(2, 2, 8, 'Nhặt được thẻ BHYT sinh viên', 'Thẻ bảo hiểm y tế mang tên Lê Thị D, nhặt được ở khu vực hành lang căng tin.', 'FOUND', '2026-04-16 12:30:00', '0977112233', 'Nhận lại tại văn phòng khoa', 'OPEN'),
-- 27
(1, 3, 2, 'Mất khóa tủ cá nhân văn phòng khoa', 'Chìa khóa nhỏ đi kèm thẻ tên cán bộ sinh viên.', 'LOST', '2026-04-17 14:20:00', '0901234567', 'Zalo: 0901234567', 'CLOSED'),
-- 28
(2, 4, 11, 'Nhặt được bình nước thể thao nhựa trong suốt', 'Bình nước dung tích 1L có vạch chia ml, bỏ quên tại sảnh nhà thi đấu.', 'FOUND', '2026-04-18 16:15:00', '0912233445', 'Inbox qua fanpage trường', 'OPEN'),
-- 29
(1, 5, 10, 'Mất găng tay chống nắng màu đen', 'Đôi găng tay vải thun lạnh để trong cốp xe nhưng lúc lấy ra bị rơi mất một chiếc dọc đường.', 'LOST', '2026-04-19 13:40:00', '0901234567', 'Zalo: 0901234567', 'OPEN'),
-- 30
(2, 6, 7, 'Nhặt được sạc dự phòng Xiaomi 10000mAh', 'Sạc dự phòng màu bạc, nhặt được tại khu vực ghế đá bãi giữ xe giáo viên.', 'FOUND', '2026-04-20 08:50:00', '0955667788', 'Liên hệ bác bảo vệ bãi xe', 'OPEN'),
-- 31
(1, 6, 7, 'Nhân phẩm của thằng admin', 'Trả tiền lại cho tui vro. Anh bạn nợ tôi lâu lắm rồi đấy.', 'LOST', '2026-04-20 08:50:00', '1234567890', 'Gọi điện cho tui lẹ đi ông cố', 'OPEN');

