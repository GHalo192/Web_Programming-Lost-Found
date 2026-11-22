# Hướng dẫn cài đặt và chạy Campus Lost & Found bằng XAMPP

Tài liệu này hướng dẫn cách chạy website Campus Lost & Found trên máy
tính cá nhân bằng **XAMPP**, sử dụng **Apache** để chạy PHP và **MySQL**
để lưu trữ dữ liệu.

## 1. Yêu cầu

-   Đã cài đặt XAMPP.
-   Có source code của dự án Campus Lost & Found.
-   Có trình duyệt web (Chrome, Edge, Firefox...).

Trong source code hiện tại, dự án sử dụng: - PHP. - MySQL thông qua
MySQLi. - Cơ sở dữ liệu tên `campus_lost_and_found`. - File SQL khởi
tạo: `miscs/LnFdb.sql`.

Không cần chạy `npm install` hoặc khởi động Node.js để chạy website này.

## 2. Đưa source code vào thư mục htdocs

1.  Mở thư mục cài đặt XAMPP. Thông thường trên Windows là:

    ``` text
    C:\xampp\
    ```

2.  Mở thư mục:

    ``` text
    C:\xampp\htdocs\
    ```

3.  Giải nén source code dự án vào bên trong `htdocs`.

    Ví dụ, nếu thư mục dự án có tên `Web_Programming-Lost-Found-main`,
    cấu trúc sẽ là:

    ``` text
    C:\xampp\htdocs\Web_Programming-Lost-Found-main\
    ```

4.  Kiểm tra để chắc chắn file `index.php` nằm trực tiếp trong thư mục
    dự án, không bị lồng thêm một thư mục cùng tên.

    Ví dụ cấu trúc đúng:

    ``` text
    htdocs/
    └── Web_Programming-Lost-Found-main/
        ├── index.php
        ├── db.php
        ├── login.php
        ├── api/
        ├── css/
        ├── js/
        ├── img/
        ├── miscs/
        │   └── LnFdb.sql
        └── uploads/
    ```

    Nếu người dùng đổi tên thư mục dự án, hãy sử dụng tên mới đó trong đường
    dẫn truy cập ở bước 5.

## 3. Khởi động Apache và MySQL

1.  Mở **XAMPP Control Panel**.
2.  Nhấn **Start** ở hai dịch vụ:
    -   **Apache**
    -   **MySQL**
3.  Chờ đến khi cả hai dịch vụ hiển thị trạng thái đang chạy (thường có
    màu xanh).

Apache chịu trách nhiệm tiếp nhận yêu cầu từ trình duyệt và thực thi các
file PHP. MySQL chịu trách nhiệm lưu trữ và truy xuất dữ liệu của
website.

Nếu Apache hoặc MySQL không khởi động được, hãy kiểm tra xem cổng dịch
vụ có đang bị một chương trình khác sử dụng hay không.

## 4. Tạo và nhập cơ sở dữ liệu

### 4.1. Mở phpMyAdmin

Khi MySQL đã chạy, mở trình duyệt và truy cập:

``` text
http://localhost/phpmyadmin/
```

Hoặc ấn nút admin của MySQL trong **XAMPP Control Panel**.

### 4.2. Tạo cơ sở dữ liệu

1. Mở http://localhost/phpmyadmin/.

2. Chọn tab Import.

3. Chọn file `miscs/LnFdb.sql`.

4. Nhấn **Import** hoặc **Go** ở cuối trang.

5. Chờ phpMyAdmin thông báo import thành công.

Sau khi import, cơ sở dữ liệu sẽ có các bảng và dữ liệu mẫu được định
nghĩa trong file SQL, bao gồm bảng `Users`, `Categories`, `Locations` và
`Items`.

> **Lưu ý quan trọng:** File `LnFdb.sql` có các lệnh
> `DROP DATABASE IF EXISTS` trước khi tạo database. Nếu chạy lại file này trên
> cơ sở dữ liệu đã có dữ liệu, các database tương ứng sẽ bị xóa và tạo lại,
> làm mất dữ liệu cũ. Chỉ import lại khi người dùng chấp nhận khởi tạo lại dữ
> liệu.

## 5. Kiểm tra cấu hình kết nối database

Mở file `db.php` trong thư mục gốc của dự án. Cấu hình hiện tại là:

``` php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "campus_lost_and_found";
```

Đây là cấu hình mặc định thường dùng với XAMPP: - Host: `localhost` -
Username: `root` - Password: để trống - Database:
`campus_lost_and_found`

Nếu cấu hình MySQL trên máy người dùng khác, hãy cập nhật các giá trị tương ứng
trong `db.php`.

Không cần thay đổi nếu người dùng đang sử dụng cấu hình XAMPP mặc định và đã
tạo đúng tên database ở bước 4.

## 6. Mở website

Mở trình duyệt và truy cập:

``` text
http://localhost/Web_Programming-Lost-Found-main/
```

Nếu người dùng đã đổi tên thư mục dự án, thay `Web_Programming-Lost-Found-main`
bằng tên thư mục mới.

Trang chủ của website sẽ được mở thông qua file `index.php`.

Một số trang có thể truy cập trực tiếp:

  -----------------------------------------------------------------------------------------------------
  Trang                               Đường dẫn
  ----------------------------------- -----------------------------------------------------------------
  Trang chủ                           `http://localhost/Web_Programming-Lost-Found-main/`

  Đăng nhập                           `http://localhost/Web_Programming-Lost-Found-main/login.php`

  Đăng ký                             `http://localhost/Web_Programming-Lost-Found-main/register.php`
  
  -----------------------------------------------------------------------------------------------------

## 7. Tài khoản mẫu

File `miscs/LnFdb.sql` có chèn sẵn hai tài khoản mẫu:

  Vai trò         Email                     Mật khẩu
  --------------- ------------------------- ----------
  Sinh viên       `student@campus.edu.vn`   `12345`
  Quản trị viên   `admin@campus.edu.vn`     `12345`

Đây là tài khoản dùng để kiểm tra website sau khi import database. người dùng
có thể đăng ký tài khoản mới bằng chức năng đăng ký trên trang web.

## 8. Kiểm tra chức năng upload hình ảnh

Website lưu hình ảnh bài đăng trong thư mục:

``` text
uploads/items/
```

Hãy đảm bảo thư mục này tồn tại trong source code. Khi chạy trên Windows
với XAMPP, thông thường Apache có thể ghi file vào thư mục dự án nếu
quyền truy cập hệ thống cho phép.

Nếu chức năng đăng ảnh không hoạt động:

1.  Kiểm tra thư mục `uploads/items/` có tồn tại hay không.
2.  Kiểm tra Apache có quyền ghi vào thư mục này hay không.
3.  Kiểm tra giới hạn upload trong cấu hình PHP của XAMPP (`php.ini`),
    đặc biệt là `upload_max_filesize` và `post_max_size`.
4.  Sau khi chỉnh `php.ini`, khởi động lại Apache.

## 9. Một số lỗi thường gặp

### Không truy cập được website

-   Kiểm tra Apache đã được Start trong XAMPP Control Panel chưa.
-   Kiểm tra source code có nằm trong `htdocs` không.
-   Kiểm tra URL có đúng tên thư mục dự án không.

### Lỗi kết nối cơ sở dữ liệu

-   Kiểm tra MySQL đã chạy chưa.
-   Kiểm tra database `campus_lost_and_found` đã được tạo và import
    chưa.
-   Kiểm tra thông tin kết nối trong `db.php`, đặc biệt là username,
    password và tên database.

### Website không hiển thị dữ liệu

-   Mở phpMyAdmin và kiểm tra các bảng đã được import chưa.
-   Kiểm tra các bảng có dữ liệu hay không.
-   Mở Developer Tools của trình duyệt (phím `F12`), chọn tab
    **Network** để kiểm tra các yêu cầu API và phản hồi nếu có lỗi.

### Không đăng nhập được bằng tài khoản mẫu

-   Đảm bảo đã import đúng file `miscs/LnFdb.sql`.
-   Kiểm tra email tài khoản mẫu trong bảng `Users`.
-   Nếu dữ liệu mẫu đã bị thay đổi hoặc xóa, hãy đăng ký tài khoản mới
    hoặc khởi tạo lại database (lưu ý việc import lại sẽ xóa dữ liệu
    cũ).

### Ảnh không tải lên được

-   Kiểm tra thư mục `uploads/items/`.
-   Kiểm tra quyền ghi của Apache.
-   Kiểm tra giới hạn kích thước file trong `php.ini`.

## 10. Tắt website sau khi sử dụng

Khi hoàn tất, mở XAMPP Control Panel và nhấn **Stop** ở: - Apache -
MySQL

người dùng không cần xóa source code hoặc database sau khi dừng XAMPP. Lần sau
muốn chạy lại website, chỉ cần khởi động lại hai dịch vụ này.

------------------------------------------------------------------------

**Tóm tắt quy trình chạy website:**

1.  Đặt source code vào `C:\xampp\htdocs\`.
2.  Mở XAMPP và Start Apache, MySQL.
3.  Truy cập phpMyAdmin và import file `miscs/LnFdb.sql`.
4.  Kiểm tra cấu hình trong `db.php`.
5.  Truy cập `http://localhost/Web_Programming-Lost-Found-main/`.
