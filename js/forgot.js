
const API_FORGOT = 'api/auth/forgot.php';
const API_VERIFY = 'api/auth/verify-reset.php';
const API_RESET = 'api/auth/reset-password.php';

const messageBox = document.getElementById('message');

const emailForm = document.getElementById('email-form');
const codeForm = document.getElementById('code-form');
const passwordForm = document.getElementById('password-form');

const stepEmail = document.getElementById('step-email');
const stepCode = document.getElementById('step-code');
const stepPassword = document.getElementById('step-password');

function showMessage(message, type = 'danger') {
    messageBox.textContent = message;
    messageBox.className = `alert alert-${type}`;
}

function hideMessage() {
    messageBox.textContent = '';
    messageBox.className = 'alert d-none';
}

function showStep(step) {
    stepEmail.classList.add('d-none');
    stepCode.classList.add('d-none');
    stepPassword.classList.add('d-none');

    if (step === 'email') {
        stepEmail.classList.remove('d-none');
    } else if (step === 'code') {
        stepCode.classList.remove('d-none');
    } else if (step === 'password') {
        stepPassword.classList.remove('d-none');
    }

    hideMessage();
}


async function callAPI(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify(data)
    });

    // Read raw response first
    const rawResponse = await response.text();

    let result;

    try {
        result = JSON.parse(rawResponse);
    } catch (error) {
        console.error('API URL:', url);
        console.error('HTTP status:', response.status);
        console.error('Raw server response:', rawResponse);

        throw new Error(
            'Máy chủ không trả về JSON hợp lệ. Hãy kiểm tra Console.'
        );
    }

    if (!response.ok || !result.success) {
        throw new Error(
            result.message || `Lỗi HTTP ${response.status}`
        );
    }

    return result;
}

// STEP 1: Request reset code
emailForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    hideMessage();

    const email = document.getElementById('email').value.trim();
    const button = document.getElementById('send-code-btn');

    if (!email) {
        showMessage('Vui lòng nhập email.');
        return;
    }

    button.disabled = true;
    button.textContent = 'Đang tạo mã...';

    try {
        const result = await callAPI(API_FORGOT, { email });

        // Display generated code
        document.getElementById('generated-code').textContent =
            result.data.code;

        document.getElementById('verification-code').value = '';

        showStep('code');

        showMessage(
            'Mã xác nhận đã được tạo. Mã có hiệu lực trong 5 phút.',
            'success'
        );

    } catch (error) {
        showMessage(error.message);
    } finally {
        button.disabled = false;
        button.textContent = 'Tạo mã xác nhận';
    }
});

// STEP 2: Verify code
codeForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    hideMessage();

    const code = document.getElementById('verification-code')
        .value.trim().toUpperCase();

    const button = document.getElementById('verify-code-btn');

    if (!/^[A-Z0-9]{6}$/.test(code)) {
        showMessage('Mã xác nhận phải gồm đúng 6 ký tự.');
        return;
    }

    button.disabled = true;
    button.textContent = 'Đang xác nhận...';

    try {
        await callAPI(API_VERIFY, { code });

        showStep('password');

        showMessage(
            'Xác nhận thành công. Bạn có thể đặt mật khẩu mới.',
            'success'
        );

    } catch (error) {
        showMessage(error.message);
    } finally {
        button.disabled = false;
        button.textContent = 'Xác nhận mã';
    }
});

// STEP 3: Reset password
passwordForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    hideMessage();

    const password = document.getElementById('new-password').value;
    const confirmPassword =
        document.getElementById('confirm-password').value;

    const button = document.getElementById('reset-password-btn');

    if (password.length < 8) {
        showMessage('Mật khẩu phải có ít nhất 8 ký tự.');
        return;
    }

    if (password !== confirmPassword) {
        showMessage('Hai mật khẩu không khớp.');
        return;
    }

    button.disabled = true;
    button.textContent = 'Đang cập nhật...';

    try {
        await callAPI(API_RESET, {
            password: password,
            confirm_password: confirmPassword
        });

        showMessage(
            'Đổi mật khẩu thành công! Đang chuyển về trang đăng nhập...',
            'success'
        );

        passwordForm.reset();

        setTimeout(function () {
            window.location.href = 'login.php';
        }, 2000);

    } catch (error) {
        showMessage(error.message);
    } finally {
        button.disabled = false;
        button.textContent = 'Đổi mật khẩu';
    }
});

// Return to email step
document.getElementById('back-to-email-btn')
    .addEventListener('click', function () {
        showStep('email');
    });
