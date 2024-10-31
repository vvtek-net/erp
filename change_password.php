<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
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

$errorMessage = $successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $errorMessage = "Mật khẩu mới và mật khẩu xác nhận không khớp.";
    } else {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT password FROM accounts WHERE id = $user_id";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if ($current_password == $user['password']) {
            $sql = "UPDATE accounts SET password = '$new_password' WHERE id = $user_id";
            if ($conn->query($sql) === TRUE) {
                $successMessage = "Mật khẩu đã được đổi thành công.";
            } else {
                $errorMessage = "Đã xảy ra lỗi. Vui lòng thử lại.";
            }
        } else {
            $errorMessage = "Mật khẩu hiện tại không đúng.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đổi mật khẩu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/changePass.css">
</head>
<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="index.php"><img src="assets/img/logo.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <!-- <a class="nav-link" href="index.php">Home</a> -->
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="index.php">
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone_number" aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://img.icons8.com/ios-glyphs/30/ffffff/user.png" alt="User Icon"/>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a>
                        <a class="dropdown-item" href="create.php">Thêm mới khách hàng</a>
                        <a class="dropdown-item" href="user_data.php">Quản lý data</a>
                        <a class="dropdown-item" href="logout.php">Đăng xuất</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Đổi mật khẩu</h2>
        <form method="post" action="">
            Mật khẩu hiện tại: <input type="password" name="current_password" required><br>
            Mật khẩu mới: <input type="password" name="new_password" required><br>
            Xác nhận mật khẩu mới: <input type="password" name="confirm_password" required><br>
            <input type="submit" value="Đổi mật khẩu">
        </form>
        <?php if ($errorMessage): ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php elseif ($successMessage): ?>
            <div class="success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <a href="javascript:void(0);" onclick="history.back();" style="padding: 10px 20px; background-color: gray; color: white; text-decoration: none; border-radius: 4px;">Quay lại</a>

    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
