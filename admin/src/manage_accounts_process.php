<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Kết nối với cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý yêu cầu xóa tài khoản
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM accounts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: manage_accounts.php?msg=deleted");
        exit();
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

// Xử lý tìm kiếm theo fullname và username
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Phân trang và giới hạn số lượng hiển thị
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Số lượng hiển thị mặc định là 10
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM accounts WHERE fullname LIKE ? OR username LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$like_search = "%$search_query%";
$stmt->bind_param("ssii", $like_search, $like_search, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Đếm tổng số bản ghi để tạo phân trang
$sql_count = "SELECT COUNT(*) as total FROM accounts WHERE fullname LIKE ? OR username LIKE ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $like_search, $like_search);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$row_count = $count_result->fetch_assoc();
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

$conn->close();
?>