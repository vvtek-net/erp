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

// Lấy ID dữ liệu từ URL
$data_id = intval($_GET['data_id']);

// Xử lý cập nhật dữ liệu sau khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $source_id = $_POST['source_id'];
    
    // Cập nhật dữ liệu
    $stmt = $conn->prepare("UPDATE data SET name = ?, phone = ?, description = ?, status = ?, sources_id = ? WHERE data_id = ?");
    $stmt->bind_param("sssssi", $name, $phone, $description, $status, $source_id, $data_id);
    if ($stmt->execute()) {
        $msg = "Dữ liệu đã được cập nhật thành công!";
    } else {
        $msg = "Lỗi khi cập nhật dữ liệu: " . $conn->error;
    }
    $stmt->close();
}

// Truy vấn để lấy thông tin hiện tại của dữ liệu
$data_query = "SELECT * FROM data WHERE data_id = ?";
$stmt = $conn->prepare($data_query);
$stmt->bind_param("i", $data_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Truy vấn các nguồn dữ liệu
$source_query = "SELECT id, source_name FROM data_sources";
$sources = $conn->query($source_query);

$conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa dữ liệu telesale</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css">
</head>
<body>
<?php include('sidebar.php'); ?>
    <div class="container">
        <h2>Chỉnh sửa dữ liệu khách hàng</h2>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($msg)): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Tên khách hàng</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <!-- <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($data['status']); ?>" required> -->
                
                <select class="form-control" id="status" name="status">
                    <option value="called" <?php if ($data["status"] == "called") echo "selected" ?>>called</option>
                    <option value="pending" <?php if ($data["status"] == "pending") echo "selected" ?>>pending</option>
                </select>
            </div>

            <div class="form-group">
                <label for="source_id">Nguồn dữ liệu</label>
                <select class="form-control" id="source_id" name="source_id" required>
                    <?php while($source = $sources->fetch_assoc()): ?>
                        <option value="<?php echo $source['id']; ?>" <?php if ($source['id'] == $data['sources_id']) echo 'selected'; ?>>
                            <?php echo $source['source_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="telesale_manage.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>
</html>
