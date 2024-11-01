<?php
// Bắt đầu session
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối với cơ sở dữ liệu
include('db_connection.php');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy danh sách các đơn nghỉ phép của người dùng hiện tại
$account_id = $_SESSION['user_id'];
$sql = "SELECT leave_type, start_date, end_date, reason, status, days_used, created_at FROM leaves WHERE account_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Xin Nghỉ</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

        .table-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <!-- <li class="nav-item"><a href="sale_order.php" class="nav-link text-white px-2 link-secondary">Sale Orders</a></li>
                <li class="nav-item"><a href="sale_order.php" class="nav-link text-white px-2 link-secondary">Sale Orders</a></li> -->
            </ul>
        </div>
    </nav>

    <div class="container">
    <a href="leave_request.php" class="btn btn-primary">Tạo Đơn</a>
        <div class="table-container">
            <h2 class="text-center">Danh Sách Đơn Xin Nghỉ</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Loại nghỉ phép</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Lý do</th>
                        <th>Số ngày</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
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
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có đơn xin nghỉ nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>

</html>