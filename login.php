<?php
// Bắt đầu session
session_start();

// Kết nối với cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$errorMessage = "";
$loginStatus = "";  // Biến lưu trạng thái đăng nhập
$redirectPage = "";  // Biến lưu trang đích sau khi đăng nhập

// Xử lý khi người dùng nhấn nút "Đăng nhập"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sử dụng prepared statement để tránh SQL Injection
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $account = $result->fetch_assoc();

        // Kiểm tra mật khẩu
        if ($password == $account['password']) {
            // Lưu thông tin người dùng vào session
            $_SESSION['user_id'] = $account['id'];
            $_SESSION['fullname'] = $account['fullname'];
            $_SESSION['role'] = $account['role'];

            // Kiểm tra vai trò và điều hướng đến trang phù hợp
            if ($account['role'] === 'admin') {
                $redirectPage = "admin/admin.php";  // Trang dành cho admin
            } else if ($account['role'] === 'merchant') {
                if (isset($_SESSION['current_url'])) {
                    $redirectPage = $_SESSION['current_url'];
                } else {
                    $redirectPage = "ticket_management.php";
                }
            } else {
                $redirectPage = "dashboard.php";  // Trang dành cho user thông thường
            }

            $loginStatus = "success";  // Đăng nhập thành công
        } else {
            $loginStatus = "error";  // Sai mật khẩu
            $errorMessage = "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    } else {
        $loginStatus = "error";  // Tên đăng nhập không đúng
        $errorMessage = "Tên đăng nhập hoặc mật khẩu không đúng.";
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
    <title>Đăng nhập || Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="assets/css/login.css"> -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
     
</head>

<body>
<!-- HTML -->
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>

        </div>
    </div>
</nav>

<!-- CSS -->
<style>
    .custom-navbar {
        background-color: #007bff !important; /* Màu xanh như trong hình */
    }

    .custom-navbar .navbar-brand,
    .custom-navbar .nav-link {
        color: #fff !important; /* Màu trắng cho chữ */
    }

    .custom-navbar .btn-light {
        background-color: #fff;
        border: none;
    }

    .custom-navbar .btn-light:hover {
        background-color: #f0f0f0;
    }
</style>


    <!-- Login Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Đăng nhập</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tài khoản hoặc email</label>
                                <input type="text" name="username" class="form-control" id="username" placeholder="Nhập tài khoản hoặc email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Nhập mật khẩu" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            </div>
                        </form>
                        <br>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger text-center"><?php echo $errorMessage; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Modal -->
    <div class="modal" tabindex="-1" id="loginPopup">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="popupMessage"></p>
                </div>
                <div class="modal-footer">
                    <button id="okButton" class="btn btn-primary" style="display: none;">OK</button>
                    <button id="retryButton" class="btn btn-danger" style="display: none;">Thử lại</button>
                    <button id="backButton" class="btn btn-secondary" style="display: none;">Quay lại</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var loginStatus = "<?php echo $loginStatus; ?>";
        var loginPopup = new bootstrap.Modal(document.getElementById("loginPopup"));
        var popupMessage = document.getElementById("popupMessage");
        var okButton = document.getElementById("okButton");
        var retryButton = document.getElementById("retryButton");
        var backButton = document.getElementById("backButton");

        if (loginStatus !== "") {
            if (loginStatus === "success") {
                popupMessage.innerText = "Đăng nhập thành công!";
                okButton.style.display = "inline-block";
                okButton.onclick = function() {
                    window.location.href = "<?php echo $redirectPage; ?>";
                };
            } else {
                popupMessage.innerText = "Đăng nhập thất bại. Vui lòng thử lại.";
                retryButton.style.display = "inline-block";
                backButton.style.display = "inline-block";
                retryButton.onclick = function() {
                    window.location.href = "login.php";
                };
                backButton.onclick = function() {
                    window.location.href = "index.php";
                };
            }
            loginPopup.show();
        }
    </script>
</body>

</html>
