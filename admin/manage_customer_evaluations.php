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

// Thêm dữ liệu Đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['evaluation_name'])) {
    $evaluation_name = $_POST['evaluation_name'];
    $sql = "INSERT INTO customer_evaluations (evaluation_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $evaluation_name);
    $stmt->execute();
}

// Xóa dữ liệu
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM customer_evaluations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

// Lấy dữ liệu
$sql = "SELECT * FROM customer_evaluations";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đánh giá</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css"> <!-- Sidebar CSS -->
</head>
<body>
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Quản lý Đánh giá</h2>
        
        <form method="POST" action="manage_customer_evaluations.php">
            <div class="form-group">
                <label for="evaluation_name">Thêm Đánh giá:</label>
                <input type="text" class="form-control" name="evaluation_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Đánh giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['evaluation_name']); ?></td>
                        <td>
                            <a href="manage_customer_evaluations.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
