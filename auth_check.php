<?php
    // auth_check.php
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['tendangnhap'])) {
        // Sử dụng đường dẫn tuyệt đối từ thư mục gốc
        header("Location: /qlktx/login+register/pagelogin.php");
        exit();
    }
?>