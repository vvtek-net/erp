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

// Lấy thông tin tài khoản dựa trên ID
$account_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM accounts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Tài khoản không tồn tại.";
    exit();
}

$account = $result->fetch_assoc();

// Xử lý khi người dùng nhấn nút "Cập nhật"
$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $role = $_POST['role'];

    // Nếu mật khẩu được nhập, cập nhật mật khẩu
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $sql_update = "UPDATE accounts SET username = ?, password = ?, fullname = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssssi", $username, $password, $fullname, $role, $account_id);
    } else {
        $sql_update = "UPDATE accounts SET username = ?, fullname = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssi", $username, $fullname, $role, $account_id);
    }

    if ($stmt->execute()) {
        $successMessage = "Tài khoản đã được cập nhật thành công.";
    } else {
        $successMessage = "Lỗi: " . $conn->error;
    }

    // Cập nhật lại thông tin sau khi lưur 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa tài khoản</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/edit_account.css">

</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Chỉnh sửa tài khoản</h2>
        <form method="post" action="">
            Tên đăng nhập: <input type="text" name="username" value="<?php echo htmlspecialchars($account['username']); ?>" required><br>
            Mật khẩu mới (để trống nếu không đổi): <input type="password" name="password"><br>
            Tên đầy đủ: <input type="text" name="fullname" value="<?php echo htmlspecialchars($account['fullname']); ?>" required><br>
            Vai trò:
            <select name="role" required>
                <option value="user" <?php if ($account['role'] == 'user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if ($account['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            </select><br>
            <input type="submit" value="Cập nhật tài khoản">
        </form>
        <a class="back-link" href="manage_accounts.php">Quay lại</a>
        <?php if (!empty($successMessage)): ?>
            <div class="message"><?php echo $successMessage; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
