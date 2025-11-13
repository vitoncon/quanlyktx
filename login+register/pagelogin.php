<?php
session_start();
include('../config.php');

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['tendangnhap'])) {
    header("Location: ../pagehome/pagehome.php");
    exit();
}

$error = "";

// Xử lý đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $tendangnhap = trim($_POST['tendangnhap'] ?? '');
    $matkhau = trim($_POST['matkhau'] ?? '');

    if (empty($tendangnhap) || empty($matkhau)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Sử dụng password_verify nếu mật khẩu được mã hóa
        $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE tendangnhap = ?");
        $stmt->bind_param("s", $tendangnhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu (giả sử lưu plaintext - nên dùng password_hash)
            if ($user['matkhau'] === $matkhau) {
                $_SESSION['tendangnhap'] = $user['tendangnhap'];
                $_SESSION['vaitro'] = $user['vaitro'] ?? 'user';
                
                // Đảm bảo không có output trước header
                if (!headers_sent()) {
                    header("Location: ../pagehome/pagehome.php");
                    exit();
                } else {
                    echo "<script>window.location.href = '../pagehome/pagehome.php';</script>";
                    exit();
                }
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tài khoản không tồn tại!";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="login+register.css">
</head>
<body>
<form action="pagelogin.php" method="POST" autocomplete="off">
    <h3>Đăng nhập</h3>

    <!-- Hiển thị thông báo lỗi -->
    <?php if (!empty($error)): ?>
        <div class="error-message" style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 5px; border: 1px solid #ef5350; margin-bottom: 15px; text-align: center; font-weight: bold;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <label for="tendangnhap">Tên đăng nhập</label>
    <input type="text" name="tendangnhap" placeholder="Tên đăng nhập" id="tendangnhap" required autocomplete="off" value="">

    <label for="matkhau">Mật khẩu</label>
    <input type="password" name="matkhau" placeholder="Mật khẩu" id="matkhau" required autocomplete="new-password" value="">

    <button type="submit" name="login">Đăng nhập</button>

    <p class="register-link">
            Nếu bạn chưa có tài khoản, hãy 
            <a href="pageregister.php">Đăng ký</a>.
        </p>
</form>

<script>
// JavaScript để clear form khi trang được load (sau khi submit sai)
document.addEventListener('DOMContentLoaded', function() {
    // Clear tất cả các input
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.value = '';
    });
    
    // Focus vào ô tên đăng nhập
    document.getElementById('tendangnhap').focus();
});

// Ngăn chặn trình duyệt tự động điền sau khi submit
window.onload = function() {
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
}
</script>
</body>
</html>