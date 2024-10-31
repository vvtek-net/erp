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

// Lấy ID khách hàng từ URL
$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Xử lý khi người dùng nhấn nút "Cập nhật"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $phone_number = $_POST['phone_number'];

    // Cập nhật thông tin khách hàng
    $sql = "UPDATE customers SET customer_name = ?, phone_number = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $customer_name, $phone_number, $customer_id);

    if ($stmt->execute()) {
        header("Location: manage_data.php?msg=updated");
        exit();
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

// Truy vấn dữ liệu khách hàng hiện tại
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Không tìm thấy dữ liệu khách hàng.";
    exit();
}

$customer = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin khách hàng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/edit_data.css">

</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Chỉnh sửa thông tin khách hàng</h2>
        <form method="POST" action="">
            Tên khách hàng: <input type="text" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required><br>
            Số điện thoại: <input type="text" name="phone_number" value="<?php echo htmlspecialchars($customer['phone_number']); ?>" required><br>
            <input type="submit" value="Cập nhật">
        </form>
        <a href="manage_data.php" class="btn btn-secondary back-link">Quay lại</a>
    </div>
</body>
</html>
