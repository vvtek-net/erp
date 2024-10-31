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

// Chỉ lấy những user có role là 'user'
$user_query = "SELECT id, fullname FROM accounts WHERE role = 'user'";
$users = $conn->query($user_query);

$source_query = "SELECT id, source_name FROM data_sources";
$sources = $conn->query($source_query);

// Xử lý form gửi dữ liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    $user_id = $_POST['user_id'];
    $source_id = $_POST['source_id'];
    $status = 'pending'; // Mặc định trạng thái là pending

    // Thêm dữ liệu vào bảng data
    $stmt = $conn->prepare("INSERT INTO data (name, phone, description, user_id, status, sources_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisi", $name, $phone, $description, $user_id, $status, $source_id);
    if ($stmt->execute()) {
        $msg = "Dữ liệu đã được thêm thành công!";
    } else {
        $msg = "Lỗi khi thêm dữ liệu: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dữ liệu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css">
</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Thêm mới dữ liệu khách hàng</h2>

        <?php if (isset($msg)): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Tên khách hàng</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="user_id">Người dùng</label>
                <select class="form-control" id="user_id" name="user_id" required>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['fullname']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="source_id">Nguồn dữ liệu</label>
                <select class="form-control" id="source_id" name="source_id" required>
                    <?php while($source = $sources->fetch_assoc()): ?>
                        <option value="<?php echo $source['id']; ?>"><?php echo $source['source_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Thêm dữ liệu</button>
        </form>
    </div>
</body>
</html>
