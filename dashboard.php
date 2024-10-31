<?php
session_start();
// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
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

// Thiết lập giá trị mặc định cho ngày bắt đầu và kết thúc
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : $yesterday;
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : $today;

// Nhận giá trị từ dropdown
$data_filter = isset($_POST['data_filter']) ? $_POST['data_filter'] : 'data_source';  // Giá trị mặc định là 'data_source'

$search_result = "";
$data = [];
$modal_content = ""; // Nội dung cho modal

if (isset($_SESSION['user_id']) && isset($_SESSION['fullname'])) {

    // Lấy user hiện tại từ session
    $user_id = $_SESSION['user_id'];
    $fullname = $_SESSION['fullname'];

    // Truy vấn dữ liệu dựa trên cột được chọn và điều kiện created_by
    $sql = "SELECT $data_filter AS filter_value, COUNT(*) as total_records
            FROM customers 
            WHERE created_by = ? AND DATE(customers.created_at) BETWEEN ? AND ?
            GROUP BY $data_filter";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullname, $start_date, $end_date);  // Thêm fullname vào điều kiện
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

if (!isset($_SESSION['user_id'])) {
    $h2 = "Nhập số điện thoại để tìm kiếm hoặc" . "<a href=\"login.php\"> Đăng Nhập </a>" . "để xem thống kê.";
}

// Tìm kiếm số điện thoại
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone_number'])) {
    $phone_number = $_POST['phone_number'];

    $sql = "SELECT * FROM customers WHERE phone_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $search_result = $result->fetch_assoc();
        // Nếu tìm thấy khách hàng, hiển thị thông tin chi tiết trong modal
        $modal_content = '
        <table class="table table-bordered">
            <tr><th>Tên khách hàng</th><td>' . $search_result['customer_name'] . '</td></tr>
            <tr><th>Số điện thoại</th><td>' . $search_result['phone_number'] . '</td></tr>
            <tr><th>Người nhập</th><td>' . $search_result['created_by'] . '</td></tr>
            <tr><th>Ngày nhập</th><td>' . $search_result['created_at'] . '</td></tr>
        </table>';
    } else {
        // Nếu không tìm thấy, hiển thị thông báo trong modal
        $modal_content = '<p>Số điện thoại này chưa tồn tại trong hệ thống.</p>';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"> -->
    <!-- <link rel="stylesheet" href="assets/css/index.css"> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <style>
        figcaption {
            color: rgb(1, 126, 132);
        }
    </style>
</head>

<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary justify-content-end">
    <a class="navbar-brand" href="dashboard.php" style="margin-right: auto;">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <button class="navbar-toggler ms-3" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse mx-3" id="navbarNav" style="flex-grow: 0;">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"></li>
            </ul>
            <!-- <form class="form-inline my-2 my-lg-0 d-flex" method="post" action="">
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone_number" aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form> -->
            <form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3">
                <input type="search" class="form-control form-control-dark" placeholder="Tìm số điện thoại" aria-label="Search" name="phone_number">
            </form>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://img.icons8.com/ios-glyphs/30/ffffff/user.png" alt="User Icon">
                    </a>
                    <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1" style="position: absolute; right: 5%;">
                        <li><a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a></li>
                        <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <section class="bg-200 o_colored_level pt-md-6 pb-7" data-oe-shape-data="{'shape':'illustration/doodle/03'}">
        <div class="o_we_shape o_illustration_doodle_03" style="left: -0.0999999px; right: -0.0999999px;"></div>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-xxl-8 text-center">
                    <div class="row mt-5 mt-md-6">

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./analytics.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/analytics.svg" alt="Odoo Analytics icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Analytics</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./user_data.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/data.svg" alt="Odoo Knowledge icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Customers</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./data_manager.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/sale.svg" alt="Odoo Sign icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Sales</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./ticket_management.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/helpdesk.svg" alt="Odoo CRM icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Helpdesk</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./template.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/website_template.svg" alt="Websites Template" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Website Templates</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="./leave_list.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/leaves.svg" alt="Odoo Rental icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Leaves</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="assets/img/dashboard/sale.svg" alt="Odoo Point of Sale icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Point of Sale</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="discuss.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/mail/static/description/icon.svg" alt="Odoo Discuss icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Discuss</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="task_list.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/documents/static/description/icon.svg" alt="Odoo Documents icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Tasks</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/project/static/description/icon.svg" alt="Odoo Project icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Project</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/hr_timesheet/static/description/icon.svg" alt="Odoo Timesheet icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Timesheets</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/industry_fsm/static/description/icon.svg" alt="Odoo Field Service icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Field Service</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/planning/static/description/icon.svg" alt="Odoo Planning icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Planning</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/social/static/description/icon.svg" alt="Odoo Social Marketing icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Social Marketing</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/mass_mailing/static/description/icon.svg" alt="Odoo Email Marketing icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Email Marketing</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/purchase/static/description/icon.svg" alt="Odoo Purchase icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Purchase</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/stock/static/description/icon.svg" alt="Odoo Inventory icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Inventory</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/mrp/static/description/icon.svg" alt="Odoo Manufacturing icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">Manufacturing</figcaption>
                            </figure>
                        </a>

                        <a class="x_wd_app_entry col-4 col-sm-3 col-lg-2 text-center mb-3 p-0 fw-bold text-decoration-none" href="404_not_found.php">
                            <figure>
                                <img width="80px" class="img-thumbnail rounded-1 mb-3" src="//download.odoocdn.com/icons/hr/static/description/icon.svg" alt="Odoo Employees icon" loading="lazy" style="">
                                <figcaption class="text-truncate small text-o-color-5">HR</figcaption>
                            </figure>
                        </a>
                        </a>
                        <!-- <div class="col-lg-6 pt40 d-none d-lg-block">
                            <div class="form-check form-switch w-auto text-start ps-3 x_wd_corner_highlight_04">
                                <input class="form-check-input m-0" type="checkbox" role="switch" id="x_wd_apps_switcher" style="width: 3rem; height: 1.5rem;">
                                <label class="form-check-label fw-bold text-o-color-1 px-3" for="x_wd_apps_switcher">Imagine without odoo</label>
                            </div>
                        </div>
                        <div class="col-lg-6 pt40 text-end">
                            <a href="/page/all-apps">View all Apps<img src="https://odoocdn.com/openerp_website/static/src/img/arrows/secondary_arrow_sm_03.svg" width="40px" class="align-top o_rtl_flip ms-2 me-3" alt="" loading="lazy" style=""></a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>

</html>