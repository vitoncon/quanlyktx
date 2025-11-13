<?php
session_start();
require_once('../auth_check.php');
include('../config.php');

if (!isset($conn)) {
    die("Lỗi: Không thể kết nối đến database. Vui lòng kiểm tra file config.php");
}

// Biến thông báo toast
$toast_message = '';
$toast_type = '';

// Xử lý các tham số filter
$toanha_filter = $_GET['toanha'] ?? '';
$phong_filter = $_GET['phong'] ?? '';
$trangthai_filter = $_GET['trangthai'] ?? '';
$search_keyword = $_GET['search'] ?? '';
$search_type = $_GET['search_type'] ?? 'tensinhvien';

// Xây dựng câu truy vấn - SỬA TÊN BẢNG thành 'sinhvien'
$sql = "SELECT * FROM sinhvien WHERE 1=1";
$params = [];

if (!empty($toanha_filter) && $toanha_filter != 'Tất cả') {
    $sql .= " AND toanha = ?";
    $params[] = $toanha_filter;
}

if (!empty($phong_filter) && $phong_filter != 'Tất cả') {
    $sql .= " AND phong = ?";
    $params[] = $phong_filter;
}

if (!empty($trangthai_filter) && $trangthai_filter != 'Tất cả') {
    $sql .= " AND tranquhai = ?";
    $params[] = $trangthai_filter;
}

if (!empty($search_keyword)) {
    if ($search_type == 'masinhvien') {
        $sql .= " AND `mashin/ven` LIKE ?";
    } else {
        $sql .= " AND `tensin/n/en` LIKE ?";
    }
    $params[] = "%$search_keyword%";
}

// Thêm ORDER BY để sắp xếp kết quả
$sql .= " ORDER BY id DESC";

try {
    // Thực thi truy vấn
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Xử lý thêm sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    try {
        $toanha = $_POST['toanha'];
        $phong = $_POST['phong'] ?? null;
        $mashinhvien = $_POST['mashinhvien'];
        $tensinhvien = $_POST['tensinhvien'] ?? null;
        $ngaysinh = $_POST['ngaysinh'] ?: null;
        $ngayvao = $_POST['ngayvao'];
        $trangthai = $_POST['trangthai'] ?? 'Dang ó';
        
        // SỬA TÊN BẢNG thành 'sinhvien'
        $insert_sql = "INSERT INTO sinhvien (toanha, phong, `mashin/ven`, `tensin/n/en`, ngaysinh, ngayvao, tranquhai) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$toanha, $phong, $mashinhvien, $tensinhvien, $ngaysinh, $ngayvao, $trangthai]);
        
        $_SESSION['toast_message'] = "Thêm sinh viên thành công!";
        $_SESSION['toast_type'] = "success";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['toast_message'] = "Lỗi khi thêm sinh viên: " . $e->getMessage();
        $_SESSION['toast_type'] = "error";
    }
}

// Xử lý xóa sinh viên
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        // SỬA TÊN BẢNG thành 'sinhvien'
        $delete_sql = "DELETE FROM sinhvien WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$delete_id]);
        
        $_SESSION['toast_message'] = "Xóa sinh viên thành công!";
        $_SESSION['toast_type'] = "success";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['toast_message'] = "Lỗi khi xóa sinh viên: " . $e->getMessage();
        $_SESSION['toast_type'] = "error";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Xử lý cập nhật sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    try {
        $id = $_POST['id'];
        $toanha = $_POST['toanha'];
        $phong = $_POST['phong'] ?? null;
        $mashinhvien = $_POST['mashinhvien'];
        $tensinhvien = $_POST['tensinhvien'] ?? null;
        $ngaysinh = $_POST['ngaysinh'] ?: null;
        $ngayvao = $_POST['ngayvao'];
        $trangthai = $_POST['trangthai'] ?? 'Dang ó';
        
        // SỬA TÊN BẢNG thành 'sinhvien'
        $update_sql = "UPDATE sinhvien SET toanha = ?, phong = ?, `mashin/ven` = ?, `tensin/n/en` = ?, 
                       ngaysinh = ?, ngayvao = ?, tranquhai = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$toanha, $phong, $mashinhvien, $tensinhvien, $ngaysinh, $ngayvao, $trangthai, $id]);
        
        $_SESSION['toast_message'] = "Cập nhật sinh viên thành công!";
        $_SESSION['toast_type'] = "success";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['toast_message'] = "Lỗi khi cập nhật sinh viên: " . $e->getMessage();
        $_SESSION['toast_type'] = "error";
    }
}

// Hiển thị thông báo toast nếu có
if (isset($_SESSION['toast_message'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_type = $_SESSION['toast_type'];
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sinh viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="sinhvien.css">
</head>
<body>
    <!-- Toast Notification -->
    <?php if ($toast_message): ?>
    <div class="toast <?= $toast_type ?>" id="toast">
        <?= htmlspecialchars($toast_message) ?>
    </div>
    <script>
        // Hiển thị toast
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        });
    </script>
    <?php endif; ?>

    <div class="student-page">
        <div class="header">
            <h1><i class="fa-solid fa-user-graduate"></i>Danh sách sinh viên</h1>
        </div>

        <form method="GET" action="" class="controls">
            <div class="filter-section">
                <div class="filter-group">
                    <label>Tòa nhà:</label>
                    <select name="toanha" class="filter-select">
                        <option value="Tất cả">Tất cả</option>
                        <option value="Tòa nhà A1" <?= ($toanha_filter == 'Tòa nhà A1') ? 'selected' : '' ?>>Tòa nhà A1</option>
                        <option value="Tòa nhà A2" <?= ($toanha_filter == 'Tòa nhà A2') ? 'selected' : '' ?>>Tòa nhà A2</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Phòng:</label>
                    <select name="phong" class="filter-select">
                        <option value="Tất cả">Tất cả</option>
                        <option value="Tầng 1" <?= ($phong_filter == 'Tầng 1') ? 'selected' : '' ?>>Tầng 1</option>
                        <option value="Tầng 2" <?= ($phong_filter == 'Tầng 2') ? 'selected' : '' ?>>Tầng 2</option>
                        <option value="Tầng 3" <?= ($phong_filter == 'Tầng 3') ? 'selected' : '' ?>>Tầng 3</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Trạng thái:</label>
                    <div class="status-filters">
                        <button type="button" class="status-btn <?= empty($trangthai_filter) || $trangthai_filter == 'Tất cả' ? 'active' : '' ?>" onclick="setStatusFilter('Tất cả')">Tất cả</button>
                        <button type="button" class="status-btn <?= $trangthai_filter == 'Dang ó' ? 'active' : '' ?>" onclick="setStatusFilter('Dang ó')"><i class="fa-solid fa-users"></i> Đang ở</button>
                        <button type="button" class="status-btn <?= $trangthai_filter == 'Đã trả phòng' ? 'active' : '' ?>" onclick="setStatusFilter('Đã trả phòng')"><i class="fa-solid fa-person-walking-arrow-right"></i> Đã rời đi</button>
                        <input type="hidden" name="trangthai" id="trangthai" value="<?= htmlspecialchars($trangthai_filter) ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Hành động:</label>
                    <div class="status-filters">
                        <button type="button" class="status-btn" onclick="showAddForm()"><i class="fa-solid fa-plus"></i> Thêm</button>
                        <button type="button" class="status-btn" onclick="editSelected()"><i class="fa-solid fa-pen-to-square"></i> Sửa</button>
                        <button type="button" class="status-btn" onclick="deleteSelected()"><i class="fa-solid fa-trash"></i> Xóa</button>
                    </div>
                </div>
            </div>

            <div class="search-section">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Tìm kiếm sinh viên" class="search-input" value="<?= htmlspecialchars($search_keyword) ?>">
                    <select name="search_type" class="search-filter">
                        <option value="tensinhvien" <?= $search_type == 'tensinhvien' ? 'selected' : '' ?>>Tên sinh viên</option>
                        <option value="masinhvien" <?= $search_type == 'masinhvien' ? 'selected' : '' ?>>Mã sinh viên</option>
                    </select>
                    <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>

        <div class="table-container">
            <table class="student-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Tòa nhà</th>
                        <th>Phòng</th>
                        <th>Mã sinh viên</th>
                        <th>Tên sinh viên</th>
                        <th>Ngày sinh</th>
                        <th>Ngày vào</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">Không có dữ liệu sinh viên</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?= $student['id'] ?>" class="student-checkbox"></td>
                        <td><?= htmlspecialchars($student['toanha']) ?></td>
                        <td><?= htmlspecialchars($student['phong'] ?? '') ?></td>
                        <td><?= htmlspecialchars($student['mashin/ven']) ?></td>
                        <td><span><?= htmlspecialchars($student['tensin/n/en'] ?? '') ?></span></td>
                        <td><?= $student['ngaysinh'] ? date('d/m/Y', strtotime($student['ngaysinh'])) : '' ?></td>
                        <td><?= date('d/m/Y', strtotime($student['ngayvao'])) ?></td>
                        <td>
                            <span class="status-badge <?= $student['tranquhai'] == 'Dang ó' ? 'pending' : 'completed' ?>">
                                <?= $student['tranquhai'] == 'Dang ó' ? 'Đang ở' : 'Đã trả phòng' ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn view" onclick="viewStudent(<?= $student['id'] ?>)">Xem</button>
                            <button class="action-btn edit" onclick="editStudent(<?= $student['id'] ?>)">Sửa</button>
                            <button class="action-btn delete" onclick="deleteStudent(<?= $student['id'] ?>)">Xóa</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal form thêm/sửa sinh viên -->
    <div id="studentModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Thêm sinh viên</h2>
            <form method="POST" action="" id="studentForm">
                <input type="hidden" name="id" id="studentId">
                <div class="form-group">
                    <label>Tòa nhà:</label>
                    <input type="text" name="toanha" id="toanha" required>
                </div>
                <div class="form-group">
                    <label>Phòng:</label>
                    <input type="text" name="phong" id="phong">
                </div>
                <div class="form-group">
                    <label>Mã sinh viên:</label>
                    <input type="text" name="mashinhvien" id="mashinhvien" required>
                </div>
                <div class="form-group">
                    <label>Tên sinh viên:</label>
                    <input type="text" name="tensinhvien" id="tensinhvien">
                </div>
                <div class="form-group">
                    <label>Ngày sinh:</label>
                    <input type="date" name="ngaysinh" id="ngaysinh">
                </div>
                <div class="form-group">
                    <label>Ngày vào:</label>
                    <input type="date" name="ngayvao" id="ngayvao" required>
                </div>
                <div class="form-group">
                    <label>Trạng thái:</label>
                    <select name="trangthai" id="trangthaiSelect">
                        <option value="Dang ó">Đang ở</option>
                        <option value="Đã trả phòng">Đã trả phòng</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_student" id="addBtn">Thêm</button>
                    <button type="submit" name="update_student" id="updateBtn" style="display:none;">Cập nhật</button>
                    <button type="button" onclick="closeModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function setStatusFilter(status) {
        document.getElementById('trangthai').value = status;
        document.querySelector('form').submit();
    }

    function showAddForm() {
        document.getElementById('modalTitle').textContent = 'Thêm sinh viên';
        document.getElementById('addBtn').style.display = 'block';
        document.getElementById('updateBtn').style.display = 'none';
        document.getElementById('studentModal').style.display = 'block';
        document.getElementById('studentForm').reset();
        document.getElementById('studentId').value = '';
    }

    function editStudent(id) {
        // Tạm thời chuyển hướng đến trang sửa hoặc hiển thị form
        alert('Chức năng sửa sẽ được triển khai sau. ID: ' + id);
        // TODO: Triển khai lấy dữ liệu sinh viên và điền vào form
    }

    function deleteStudent(id) {
        if (confirm('Bạn có chắc muốn xóa sinh viên này?')) {
            window.location.href = '?delete_id=' + id;
        }
    }

    function closeModal() {
        document.getElementById('studentModal').style.display = 'none';
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const modal = document.getElementById('studentModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Select all checkbox
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    </script>
</body>
</html>