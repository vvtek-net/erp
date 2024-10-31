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

// Xử lý xóa dữ liệu
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_data.php");
    exit();
}

// Xử lý lọc dữ liệu và tìm kiếm
$filter_by_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_by_contact_status = isset($_GET['contact_status']) ? $_GET['contact_status'] : '';
$filter_by_evaluation = isset($_GET['evaluation']) ? $_GET['evaluation'] : '';
$filter_by_data_source = isset($_GET['data_source']) ? $_GET['data_source'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Xử lý phân trang
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Số lượng hiển thị mặc định là 10
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM customers WHERE 1=1";
$sqlCount = "SELECT count(*) as total FROM customers WHERE 1=1";

if ($filter_by_user != '') {
    $sql .= " AND created_by = ?";
    $sqlCount .= " AND created_by = ?";
}
if ($filter_by_contact_status != '') {
    $sql .= " AND contact_status = ?";
    $sqlCount .= " AND contact_status = ?";
}
if ($filter_by_evaluation != '') {
    $sql .= " AND customer_evaluation = ?";
    $sqlCount .= " AND customer_evaluation = ?";
}
if ($filter_by_data_source != '') {
    $sql .= " AND data_source = ?";
    $sqlCount .= " AND data_source = ?";
}
if ($search_query != '') {
    $sql .= " AND customer_name LIKE ?";
    $sqlCount .= " AND customer_name LIKE ?";
}

// Thêm ORDER BY, LIMIT, và OFFSET vào câu lệnh chính
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$sqlCount .= " ORDER BY created_at DESC";

// Chuẩn bị câu lệnh SQL
$stmt = $conn->prepare($sql);
$stmtCount = $conn->prepare($sqlCount);

$bind_types = '';
$bind_values = [];

// Thêm các tham số lọc vào bind_types và bind_values
if ($filter_by_user != '') {
    $bind_types .= 's';
    $bind_values[] = &$filter_by_user;
}
if ($filter_by_contact_status != '') {
    $bind_types .= 's';
    $bind_values[] = &$filter_by_contact_status;
}
if ($filter_by_evaluation != '') {
    $bind_types .= 's';
    $bind_values[] = &$filter_by_evaluation;
}
if ($filter_by_data_source != '') {
    $bind_types .= 's';
    $bind_values[] = &$filter_by_data_source;
}
if ($search_query != '') {
    $like_search = "%$search_query%";
    $bind_types .= 's';
    $bind_values[] = &$like_search;
}

// Bind các tham số cho lệnh đếm (count)
if ($bind_types) {
    $stmtCount->bind_param($bind_types, ...$bind_values);
}

// Thêm LIMIT và OFFSET vào bind_types và bind_values cho câu lệnh chính
$bind_types .= 'ii';
$bind_values[] = &$limit;
$bind_values[] = &$offset;

// Bind các tham số cho lệnh truy vấn dữ liệu (select)
$stmt->bind_param($bind_types, ...$bind_values);

// Thực thi câu lệnh SQL
$stmt->execute();
$result = $stmt->get_result();

$stmtCount->execute();
$resultCount = $stmtCount->get_result();

// Truy vấn danh sách người dùng để hiển thị trong bộ lọc
$sql_users = "SELECT DISTINCT created_by FROM customers";
$result_users = $conn->query($sql_users);

$conn->close();
?>