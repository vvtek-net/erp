<?php

session_start();
// include('src/manage_accounts_process.php');

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

// get all contact
$contacts = [];
$query = "SELECT * FROM contact_customer";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $contacts[] = $row;
}

// Xử lý yêu cầu xóa tài khoản
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM contact_customer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: contact_customer.php?msg=deleted");
        exit();
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý liên hệ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_accounts.css">
</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Quản lý liên hệ</h2>

        <div class="search-bar">
            <!-- <a href="add_account.php" class="btn btn-success">Thêm tài khoản</a> -->
            <form method="GET" action="manage_accounts.php" class="form-inline">
                <input type="text" name="search" placeholder="Tìm kiếm" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control mr-2">
                <select name="limit" onchange="this.form.submit()" class="form-control mr-2">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên Khách Hàng</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Gói Dịch Vụ</th>
                    <th>Mô Tả</th>
                    <th>Ngày tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($contacts != null) : 
                    foreach ($contacts as $index => $row): ?>
                    <tr>
                        <td><?php echo ++$index; ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_package']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <!-- <a href="edit_account.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Sửa</a> -->
                            <a href="contact_customer.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else:?>
                    <tr>
                        <td colspan="8" class="text-center">Chưa có khách hàng nào liên hệ.</td>
                    </tr>
                    <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo htmlspecialchars($search_query); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Tài khoản đã được xóa thành công!</div>
        <?php endif; ?>
    </div>
</body>
</html>


