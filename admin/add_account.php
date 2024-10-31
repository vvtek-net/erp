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

$successMessage = "";

// Xử lý khi người dùng nhấn nút "Thêm tài khoản"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $role = $_POST['role'];

    $sql = "INSERT INTO accounts (username, password, fullname, role) VALUES ('$username', '$password', '$fullname', '$role')";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "Tài khoản đã được thêm thành công.";
    } else {
        $successMessage = "Lỗi: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Tài Khoản</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/add_account.css">

</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Thêm Tài Khoản</h2>
        <form method="post" action="">
            Tên đăng nhập: <input type="text" name="username" required><br>
            Mật khẩu: <input type="password" name="password" required><br>
            Tên đầy đủ: <input type="text" name="fullname" required><br>
            Vai trò:
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="merchant">Merchant</option>
            </select><br>
            <input type="submit" value="Thêm tài khoản">
        </form>
        <a class="back-link" href="manage_accounts.php">Quay lại</a>
        <?php if (!empty($successMessage)): ?>
            <div class="message"><?php echo $successMessage; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
