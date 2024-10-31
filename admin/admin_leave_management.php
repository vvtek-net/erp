<?php
session_start();
// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Cập nhật trạng thái đơn nghỉ phép
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leave_id']) && isset($_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $status = ($action == 'approve') ? 'approved' : 'rejected';

    $sql = "UPDATE leaves SET status = ? WHERE leave_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $leave_id);
    $stmt->execute();
}

// Lấy danh sách các đơn nghỉ phép
$sql = "SELECT l.leave_id, l.leave_type, l.start_date, l.end_date, l.reason, l.status, l.days_used, l.created_at, a.fullname 
        FROM leaves l 
        INNER JOIN accounts a ON l.account_id = a.id 
        ORDER BY l.created_at DESC";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn Xin Nghỉ</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css"> <!-- Sidebar CSS -->
</head>
<body>
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2 class="mt-4">Quản lý Đơn Xin Nghỉ</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên nhân viên</th>
                    <th>Loại nghỉ phép</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Lý do</th>
                    <th>Số ngày</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['leave_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $row['leave_type'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['start_date'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['end_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo $row['days_used']; ?> ngày</td>
                            <td>
                                <?php 
                                switch ($row['status']) {
                                    case 'approved':
                                        echo '<span class="badge badge-success">Đã duyệt</span>';
                                        break;
                                    case 'pending':
                                        echo '<span class="badge badge-warning">Đang chờ</span>';
                                        break;
                                    case 'rejected':
                                        echo '<span class="badge badge-danger">Bị từ chối</span>';
                                        break;
                                    default:
                                        echo '<span class="badge badge-secondary">Không xác định</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="leave_id" value="<?php echo $row['leave_id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn duyệt đơn này?');">Duyệt</button>
                                    </form>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="leave_id" value="<?php echo $row['leave_id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn từ chối đơn này?');">Từ chối</button>
                                    </form>
                                <?php elseif ($row['status'] == 'approved'): ?>
                                    <span class="text-success">Đã duyệt</span>
                                <?php elseif ($row['status'] == 'rejected'): ?>
                                    <span class="text-danger">Đã từ chối</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">Không có đơn xin nghỉ nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

