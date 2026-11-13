
<?php
session_start();

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quên mật khẩu - Lost & Found</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="css/forgot.css">
</head>

<body>

<div class="container">
    <div class="forgot-container">

        <div class="text-center mb-4">
            <h2>Quên mật khẩu</h2>
            <p class="text-muted">
                Làm theo các bước bên dưới để đặt lại mật khẩu.
            </p>
        </div>

        <div id="message" class="alert d-none" role="alert"></div>

        <!-- Enter email -->
        <div id="step-email">

            <h5>Nhập email</h5>

            <p class="text-muted">
                Nhập email đã sử dụng khi đăng ký tài khoản.
            </p>

            <form id="email-form">

                <div class="form-group">
                    <label for="email">Email</label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        placeholder="example@gmail.com"
                        required
                    >
                </div>

                <button type="submit"
                        class="btn btn-primary btn-block"
                        id="send-code-btn">
                    Tạo mã xác nhận
                </button>

            </form>
        </div>

        <!-- Verify code -->
        <div id="step-code" class="d-none">

            <h5>Xác nhận mã</h5>

            <p class="text-muted">
                Hệ thống đã gửi mã xác nhận cho email của bạn.
                (no actual email verification)
            </p>

            <div class="generated-code-box">
                <span>Mã xác nhận của bạn:</span>
                <h2 id="generated-code"></h2>
            </div>

            <form id="code-form">

                <div class="form-group">
                    <label for="verification-code">
                        Nhập mã xác nhận
                    </label>

                    <input
                        type="text"
                        class="form-control text-center"
                        id="verification-code"
                        maxlength="6"
                        minlength="6"
                        placeholder="Nhập 6 ký tự"
                        autocomplete="off"
                        required
                    >
                </div>

                <button type="submit"
                        class="btn btn-primary btn-block"
                        id="verify-code-btn">
                    Xác nhận mã
                </button>

            </form>

            <button type="button"
                    class="btn btn-link btn-block"
                    id="back-to-email-btn">
                Quay lại
            </button>
        </div>

        <!-- Reset password -->
        <div id="step-password" class="d-none">

            <h5>Đặt mật khẩu mới</h5>

            <p class="text-muted">
                Mã xác nhận hợp lệ. Hãy nhập mật khẩu mới của bạn.
            </p>

            <form id="password-form">

                <div class="form-group">
                    <label for="new-password">
                        Mật khẩu mới
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="new-password"
                        minlength="8"
                        placeholder="Ít nhất 8 ký tự"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm-password">
                        Xác nhận mật khẩu mới
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="confirm-password"
                        minlength="8"
                        placeholder="Nhập lại mật khẩu"
                        required
                    >
                </div>

                <button type="submit"
                        class="btn btn-success btn-block"
                        id="reset-password-btn">
                    Đổi mật khẩu
                </button>

            </form>
        </div>

        <hr>

        <div class="text-center">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>

    </div>
</div>

<script>
    const CSRF_TOKEN = <?= json_encode(
        $csrfToken,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
</script>

<script src="js/forgot.js"></script>

</body>
</html>
