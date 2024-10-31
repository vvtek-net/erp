<?php
// Bắt đầu session
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
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
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM data WHERE data_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: telesale_manage.php?msg=deleted");
        exit();
    } else {
        $msg = "Lỗi khi xóa dữ liệu: " . $conn->error;
    }
}

// Thiết lập phân trang
$records_per_page = 10;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = intval($_GET['page']);
} else {
    $current_page = 1; // Trang mặc định là 1
}

$start_from = ($current_page - 1) * $records_per_page;

// Truy vấn dữ liệu từ bảng data để hiển thị
$sql = "SELECT d.data_id, d.name, d.phone, d.description, d.status, ds.source_name, a.fullname 
        FROM data d
        LEFT JOIN data_sources ds ON d.sources_id = ds.id
        LEFT JOIN accounts a ON d.user_id = a.id ORDER BY data_id DESC 
        LIMIT $start_from, $records_per_page";
$result = $conn->query($sql);

// Lấy tổng số bản ghi
$sql_total_records = "SELECT COUNT(*) FROM data";
$result_total = $conn->query($sql_total_records);
$total_records = $result_total->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dữ liệu telesale</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css">
</head>
<body>
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Quản lý dữ liệu telesale</h2>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Dữ liệu đã được xóa thành công!</div>
        <?php elseif (isset($msg)): ?>
            <div class="alert alert-danger"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Nút tạo mới -->
        <div class="mb-3">
            <a href="telesale_create.php" class="btn btn-success">Tạo mới</a>
        </div>

        <!-- Hiển thị dữ liệu đã nhập -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th>Nguồn data</th>
                    <th>Người dùng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): 
                    $rank = ($current_page - 1) * $records_per_page;
                    ?>
                    
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ++$rank; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['source_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td>
                                <a href="telesale_edit.php?data_id=<?php echo $row['data_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="telesale_manage.php?delete_id=<?php echo $row['data_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" class="btn btn-danger btn-sm">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Không có dữ liệu nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($i == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="telesale_manage.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>
</html>
