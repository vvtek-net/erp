<?php
// Bắt đầu session
session_start();

require 'admin/src/features.php';

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

$successMessage = "";
$isUpdated = false; // Biến này dùng để xác định xem dữ liệu đã được thêm mới hay chưa
$searchMessage = ""; // Biến này dùng để xác định kết quả tìm kiếm
$searchResult = ""; // Biến này lưu thông tin kết quả tìm kiếm

// Xử lý khi người dùng nhấn nút "Thêm khách hàng"
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['customer_name_individual']) || isset($_POST['customer_name_business']))) {

    $created_by = $_SESSION['fullname']; // Lấy tên đầy đủ từ session
    $address = $_POST['address'];

    $customerType = $_POST['customer_type'];
    $data_source = $_POST['data_source'];
    $contact_status = $_POST['contact_status'];
    $customer_evaluation = $_POST['customer_evaluation'];

    switch ($customerType) {
        case 'individual': {
            $customer_name = $_POST['customer_name_individual'];
            $birthday = convertFormatDate($_POST['birthday']);

            $phone_number = $_POST['phone_number_individual'];
            $email = $_POST['email_individual'];
            $identityPerson = $_POST['identity_person'];
            $sql = "INSERT INTO customers (customer_name, phone_number, birthday, email, address, identity_person, data_source, contact_status, customer_evaluation, created_by, customer_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'individual')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $customer_name, $phone_number, $birthday, $email, $address, $identityPerson, $data_source, $contact_status, $customer_evaluation, $created_by);
            break;
        }
        case 'business': {
            $customer_name = $_POST['customer_name_business'];
            $foundingDate = convertFormatDate($_POST['founding_date']);
            $tax = $_POST['tax'];
            $agentName = $_POST['agent_name'];
            $agentPosition = $_POST['agent_position'];
            $phone_number = $_POST['phone_number_business'];
            $email = $_POST['email_business'];
            $sql = "INSERT INTO customers (customer_name, phone_number, founding_date, tax, agent_name, agent_position, email, address, data_source, contact_status, customer_evaluation, created_by, customer_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'business')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssss", $customer_name, $phone_number, $foundingDate, $tax, $agentName, $agentPosition, $email, $address, $data_source, $contact_status, $customer_evaluation, $created_by);
            break;
        }
    }

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

// Xử lý khi người dùng tìm kiếm số điện thoại
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone_number_search'])) {
    $phone_number_search = $_POST['phone_number_search'];

    $sql_search = "SELECT * FROM customers WHERE phone_number = ?";
    $stmt_search = $conn->prepare($sql_search);
    $stmt_search->bind_param("s", $phone_number_search);
    $stmt_search->execute();
    $result_search = $stmt_search->get_result();

    if ($result_search->num_rows > 0) {
        $row = $result_search->fetch_assoc();
        $searchResult = "Tên khách hàng: " . $row['customer_name'] . "<br>Số điện thoại: " . $row['phone_number'] . "<br>Nguồn data: " . $row['data_source'] . "<br>Trạng thái tiếp xúc: " . $row['contact_status'] . "<br>Đánh giá khách hàng: " . $row['customer_evaluation'];
    } else {
        $searchMessage = "Số điện thoại chưa có trong hệ thống, vui lòng thêm mới.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Khách Hàng || Update</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/update.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>

<body>

    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
    <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>
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

    <div class="card w-50 mb-5" style="margin: auto; margin-top: 100px;">
        <div class="card-body">
            <h2 class="title mt-4 text-center font-weight-bold">Tạo Mới Khách Hàng</h2>
            <form method="POST" action="">
                <!-- check -->
                <div class="form-group">
                    <div class="row">
                        <legend class="col-form-label col-sm-12 pt-0">Loại Khách Hàng: <span style="color: red">*</span></legend>
                        <div class="col-sm-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_individual" value="individual" checked>
                                <label class="form-check-label" for="inlineRadio1">Cá Nhân</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_bussiness" value="business">
                                <label class="form-check-label" for="inlineRadio2">Doanh Nghiệp</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- individual -->
                <div id="individualForm" style="">
                    <div class="form-group">
                        <label for="customer_name">Tên Khách Hàng: <span style="color: red">*</span></label>
                        <input class="form-control" type="text" name="customer_name_individual" id="customer_name_individual" placeholder="Nhập tên khách hàng..." >
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label for="identity_person">Số CCCD/Số hộ chiếu: <span style="color: red">*</span></label>
                            <input class="form-control" type="text" name="identity_person" id="" placeholder="Nhập CCCD/passport..." >
                        </div>
                        <div class="col-6 form-group">
                            <label for="birthday">Ngày Sinh:</label>
                            <input class="form-control" type="date" name="birthday" id="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label for="phone_number_individual">Số Điện Thoại: <span style="color: red">*</span></label>
                            <input class="form-control" type="text" name="phone_number_individual" placeholder="Nhập số điện thoại..." >
                        </div>
                        <div class="col-6 form-group">
                            <label for="email_individual">Email: <span style="color: red">*</span></label>
                            <input class="form-control" type="email" name="email_individual" id="" placeholder="Nhập email..." >
                        </div>
                    </div>
                </div>

                <!-- business -->
                <div id="businessForm" style="display: none">
                    <div class="form-group">
                        <label for="customer_name">Tên Doanh Nghiệp: <span style="color: red">*</span></label>
                        <input class="form-control" type="text" name="customer_name_business" id="customer_name_business" placeholder="Nhập tên doanh nghiệp..." >
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label for="agent_name">Người Đại Diện Công Ty: <span style="color: red">*</span></label>
                            <input class="form-control" type="text" name="agent_name" placeholder="Nhập người đại diện..." >
                        </div>
                        <div class="col-6 form-group">
                            <label for="agent_position">Chức Vụ: <span style="color: red">*</span></label>
                            <input class=" form-control" type="text" name="agent_position" placeholder="Nhập chức vụ..." >
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label for="tax">Mã Số Thuế: <span style="color: red">*</span></label>
                            <input class="form-control" type="text" name="tax" placeholder="Nhập tax..." >
                        </div>
                        <div class="col-6 form-group">
                            <label for="birthday">Ngày Thành Lập: <span style="color: red">*</span></label>
                            <input class="form-control" type="date" name="founding_date" id="" >
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label for="phone_number">Số ĐT: <span style="color: red">*</span></label>
                            <input class="form-control" type="text" name="phone_number_business" placeholder="Nhập số điện thoại..." >
                        </div>
                        <div class="col-6 form-group">
                            <label for="email_business">Email: <span style="color: red">*</span></label>
                            <input class="form-control" type="email" name="email_business" id="" placeholder="Nhập email..." >
                        </div>
                    </div>
                </div>

                <!-- address -->
                <div class="form-group">
                    <label for="address">Địa chỉ: <span style="color: red">*</span></label>
                    <input class="form-control" type="address" name="address" id="" placeholder="Nhập địa chỉ..." required>
                </div>

                <!-- thông tin khác -->
                <div class="form-row">
                    <div class="col-6 form-group">
                        <label for="data_source">Nguồn data:</label>
                        <select class="form-control" name="data_source" required>
                            <option value="">Chọn nguồn data...</option>
                            <option value="Facebook (Lead)">Facebook (Lead)</option>
                            <option value="Ucall (Lead)">Ucall (Lead)</option>
                            <option value="Facebook Ads (Lead)">Facebook Ads (Lead)</option>
                            <option value="Google Ads (Lead)">Google Ads (Lead)</option>
                            <option value="Tự tìm kiếm">Tự tìm kiếm</option>
                        </select>
                    </div>
                    <div class="col-6 form-group">
                        <label for="customer_evaluation">Đánh giá khách hàng:</label>
                        <select class="form-control" name="customer_evaluation" required>
                            <option value="">Chọn đánh giá</option>
                            <option value="Chưa có đánh giá">Chưa có đánh giá</option>
                            <option value="Khách hàng mới tiếp xúc">Khách hàng mới tiếp xúc</option>
                            <option value="Khách hàng tiềm năng">Khách hàng tiềm năng</option>
                            <option value="Khách hàng cơ hội">Khách hàng cơ hội</option>
                            <option value="Khách hàng chăm sóc sau bán">Khách hàng chăm sóc sau bán</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact_status">Trạng thái tiếp xúc:</label>
                    <select class="form-control" name="contact_status" required>
                        <option value="">Chọn trạng thái tiếp xúc</option>
                        <option value="Đã gọi và gửi mail">Đã gọi và gửi mail</option>
                        <option value="Không nghe máy">Không nghe máy</option>
                        <option value="Thuê bao/Nhầm số">Thuê bao/Nhầm số</option>
                        <option value="Không nhu cầu">Không nhu cầu</option>
                        <option value="Đang cân nhắc suy nghĩ">Đang cân nhắc suy nghĩ</option>
                        <option value="Đang tư vấn">Đang tư vấn</option>
                        <option value="KH tiềm năng">KH tiềm năng</option>
                        <option value="Đã hẹn gặp">Đã hẹn gặp</option>
                        <option value="Đã demo">Đã demo</option>
                        <option value="Báo giá">Báo giá</option>
                        <option value="Chờ ký HĐ">Chờ ký HĐ</option>
                        <option value="Đã ký HĐTC">Đã ký HĐTC</option>
                        <option value="KH Đã có web">KH Đã có web</option>
                    </select><br>
                </div>

                <!-- button submit -->
                <input type="submit" value="Thêm khách hàng">
            </form>
        </div>
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

    <!-- check radio -->
    <script>
        $('input[name="customer_type"]').change(function() {
            if ($(this).val() === 'individual') {
                // Hiển thị form cá nhân và ẩn form doanh nghiệp
                $('#individualForm').show();
                $('#businessForm').hide();

                // reset
                $('#businessForm input, #businessForm select').val('')

            } else if ($(this).val() === 'business') {
                // Hiển thị form doanh nghiệp và ẩn form cá nhân
                $('#businessForm').show();
                $('#individualForm').hide();

                // reset
                $('#individualForm input, #individualForm select').val('')
            }
        });
    </script>

    <script>
        // Hiển thị popup nếu có thông báo thành công
        var successMessage = "<?php echo $successMessage; ?>";
        var isUpdated = "<?php echo $isUpdated; ?>";

        if (successMessage !== "" && isUpdated) {
            document.getElementById("successPopup").style.display = "flex";
        }

        // Điều hướng về trang user_data.php khi nhấn nút OK
        document.getElementById("okButton").onclick = function() {
            window.location.href = 'user_data.php?msg=success';
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