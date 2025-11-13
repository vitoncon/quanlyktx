<?php
session_start();
include('../config.php');

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['tendangnhap'])) {
    header("Location: /qlktx/pagehome/pagehome.php");
    exit();
}

$error = "";
$tendangnhap_value = "";

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $tendangnhap = trim($_POST["tendangnhap"] ?? '');
    $matkhau = $_POST["matkhau"] ?? '';
    $nhaplaimatkhau = $_POST["nhaplaimatkhau"] ?? '';

    // Kiểm tra dữ liệu rỗng
    if (empty($tendangnhap) || empty($matkhau) || empty($nhaplaimatkhau)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
        $tendangnhap_value = $tendangnhap;
    }
    // Kiểm tra mật khẩu nhập lại
    elseif ($matkhau !== $nhaplaimatkhau) {
        $error = "Mật khẩu nhập lại không khớp!";
        $tendangnhap_value = $tendangnhap;
    }
    // Kiểm tra độ dài mật khẩu (tùy chọn)
    elseif (strlen($matkhau) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
        $tendangnhap_value = $tendangnhap;
    }
    else {
        // Kiểm tra trùng tài khoản
        $kiemtra = $conn->prepare("SELECT * FROM taikhoan WHERE tendangnhap = ?");
        $kiemtra->bind_param("s", $tendangnhap);
        $kiemtra->execute();
        $ketqua = $kiemtra->get_result();

        if ($ketqua->num_rows > 0) {
            $error = "Tên tài khoản đã tồn tại!";
            $tendangnhap_value = "";
        } else {
            // Mã hóa mật khẩu (nếu cần)
            // $matkhaumahoa = password_hash($matkhau, PASSWORD_DEFAULT);
            $matkhaumahoa = $matkhau; // Hoặc dùng trực tiếp nếu không mã hóa

            // Thêm tài khoản mới
            $them = $conn->prepare("INSERT INTO taikhoan (tendangnhap, matkhau, vaitro) VALUES (?, ?, 'user')");
            $them->bind_param("ss", $tendangnhap, $matkhaumahoa);

            if ($them->execute()) {
                echo "<script>alert('Đăng ký thành công! Vui lòng đăng nhập.'); window.location.href='pagelogin.php';</script>";
                exit();
            } else {
                $error = "Lỗi khi đăng ký: " . $conn->error;
                $tendangnhap_value = $tendangnhap;
            }

            $them->close();
        }
        $kiemtra->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="login+register.css">
</head>
<body>
<form action="pageregister.php" method="POST" autocomplete="off">
    <h3>Đăng ký</h3>

    <!-- Hiển thị thông báo lỗi -->
    <?php if (!empty($error)): ?>
        <div class="error-message" style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 5px; border: 1px solid #ef5350; margin-bottom: 15px; text-align: center; font-weight: bold;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <label for="tendangnhap">Tên đăng nhập</label>
    <input type="text" name="tendangnhap" placeholder="Tên đăng nhập" id="tendangnhap" required autocomplete="off" value="<?php echo htmlspecialchars($tendangnhap_value); ?>">

    <label for="matkhau">Mật khẩu</label>
    <input type="password" name="matkhau" placeholder="Mật khẩu" id="matkhau" required autocomplete="new-password" value="">

    <label for="nhaplaimatkhau">Nhập lại mật khẩu</label>
    <input type="password" name="nhaplaimatkhau" placeholder="Nhập lại mật khẩu" id="nhaplaimatkhau" required autocomplete="new-password" value="">

    <button type="submit" name="register">Đăng ký</button>

    <p class="login-link">
        Nếu bạn đã có tài khoản, hãy 
        <a href="pagelogin.php">Đăng nhập</a>.
    </p>
</form>

<script>
// JavaScript để clear form khi trang được load (trừ tên đăng nhập khi có lỗi)
document.addEventListener('DOMContentLoaded', function() {
    // Chỉ clear mật khẩu, giữ lại tên đăng nhập nếu có lỗi
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
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

// Kiểm tra mật khẩu nhập lại real-time
document.getElementById('nhaplaimatkhau').addEventListener('input', function() {
    const matkhau = document.getElementById('matkhau').value;
    const nhaplaimatkhau = this.value;
    
    if (nhaplaimatkhau && matkhau !== nhaplaimatkhau) {
        this.style.borderColor = '#ef5350';
    } else {
        this.style.borderColor = '';
    }
});
</script>
</body>
</html>