<?php

session_start();

include 'db_connection.php';
require 'function.php';

$customers = getCustomers($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];
    $account_id = $_SESSION['user_id'];
    // $data_source = $_POST['data_source'];
    // $contact_status = $_POST['contact_status'];
    // $customer_evaluation = $_POST['customer_evaluation'];
    // $created_by = $_SESSION['fullname']; // Lấy tên đầy đủ từ session

    $sql = "INSERT INTO opportunities (customer_id, account_id, updated_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $customer_id, $account_id);

    if ($stmt->execute()) {
        $successMessage = "Khách hàng đã được thêm thành công.";
        $isUpdated = true; // Cập nhật biến này khi dữ liệu được thêm thành công
    } else {
        if ($conn->errno == 1062) { // 1062 là mã lỗi MySQL cho trùng lặp dữ liệu
            $successMessage = "Lỗi: Số điện thoại này đã có trên hệ thống.";
        } else {
            $successMessage = "Lỗi: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Khách Hàng || Update</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/update.css">
</head>

<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="index.php"><img src="assets/img/logo.png" alt="" height="40px"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"></li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="">
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone_number_search" aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://img.icons8.com/ios-glyphs/30/ffffff/user.png" alt="User Icon" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a>
                        <a class="dropdown-item" href="user_data.php">Quản lý data</a>
                        <a class="dropdown-item" href="data_manager.php">Telesale</a>
                        <a class="dropdown-item" href="sale_order.php">Sale Orders</a>
                        <a class="dropdown-item" href="ticket_management.php">Help Desk</a>
                        <a class="dropdown-item" href="logout.php">Đăng xuất</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Thêm Khách Hàng</h2>
        <form method="post" action="">
            Tên khách hàng:
            <select name="customer_id" id="customer_id">
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo $customer['id']; ?>"><?php echo $customer['customer_name']; ?></option>
                <?php endforeach; ?>
            </select>
            Số điện thoại: <input type="text" name="phone_number"><br>
            <input type="submit" value="Thêm khách hàng" name="submit">
        </form>
    </div>

    <div class="popup" id="successPopup">
        <div class="popup-content">
            <h3>Nhập dữ liệu thành công!</h3>
            <div class="popup-buttons">
                <button class="btn-continue" id="continueButton">Nhập tiếp</button>
                <button class="btn-ok" id="okButton">Xem dữ liệu</button>
            </div>
        </div>
    </div>

    <div class="popup" id="searchPopup">
        <div class="popup-content">
            <?php if ($searchResult !== ""): ?>
                <h3>Kết quả tìm kiếm</h3>
                <p><?php echo $searchResult; ?></p>
            <?php elseif ($searchMessage !== ""): ?>
                <h3>Thông báo</h3>
                <p><?php echo $searchMessage; ?></p>
            <?php endif; ?>
            <div class="popup-buttons">
                <button class="btn-ok" id="closeSearchPopup">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        // Hiển thị popup nếu có thông báo thành công
        var successMessage = "<?php echo $successMessage; ?>";
        var isUpdated = "<?php echo $isUpdated; ?>";

        if (successMessage !== "" && isUpdated) {
            document.getElementById("successPopup").style.display = "flex";
        }

        // Điều hướng về trang user_data.php khi nhấn nút OK
        document.getElementById("okButton").onclick = function() {
            window.location.href = 'user_data.php';
        };

        // Ở lại trang hiện tại khi nhấn nút Nhập tiếp
        document.getElementById("continueButton").onclick = function() {
            document.getElementById("successPopup").style.display = "none";
        };

        // Hiển thị popup kết quả tìm kiếm
        var searchResult = "<?php echo $searchResult; ?>";
        var searchMessage = "<?php echo $searchMessage; ?>";

        if (searchResult !== "" || searchMessage !== "") {
            document.getElementById("searchPopup").style.display = "flex";
        }

        // Đóng popup tìm kiếm
        document.getElementById("closeSearchPopup").onclick = function() {
            document.getElementById("searchPopup").style.display = "none";
        };
    </script>
</body>

</html>