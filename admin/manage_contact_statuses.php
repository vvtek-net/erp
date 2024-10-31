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

// Thêm dữ liệu trạng thái tiếp xúc
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status_name'])) {
    $status_name = $_POST['status_name'];
    $sql = "INSERT INTO contact_statuses (status_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_name);
    $stmt->execute();
}

// Xóa dữ liệu
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM contact_statuses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

// Lấy dữ liệu
$sql = "SELECT * FROM contact_statuses";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Trạng thái tiếp xúc</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css"> <!-- Sidebar CSS -->
</head>
<body>
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Quản lý Trạng thái tiếp xúc</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="status_name">Thêm Trạng thái tiếp xúc:</label>
                <input type="text" class="form-control" name="status_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trạng thái tiếp xúc</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['status_name']); ?></td>
                        <td>
                            <a href="manage_contact_statuses.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
