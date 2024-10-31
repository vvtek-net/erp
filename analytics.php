<?php
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
    <title>Tìm Kiếm Số Điện Thoại || Index</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
    <a class="navbar-brand" href="dashboard.php">
    <img src="assets/img/logo.png" alt="" style="height: 40px; width: 150px;">
</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"></li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="post" action="">
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone_number" aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form>
            
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h2 class="text-center">Thống Kê Dữ Liệu Của <?php echo $_SESSION['fullname'] ?></h2>

            <!-- Form chọn ngày bắt đầu và kết thúc và Dropdown list -->
            <form method="post" action="" id="filterForm">
                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" onchange="document.getElementById('filterForm').submit();">
                </div>
                <div class="form-group">
                    <label for="end_date">Ngày kết thúc:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" onchange="document.getElementById('filterForm').submit();">
                </div>

                <!-- Dropdown List -->
                <div class="form-group">
                    <label for="data_filter">Chọn tiêu chí:</label>
                    <select id="data_filter" name="data_filter" class="form-control" onchange="document.getElementById('filterForm').submit();">
                        <option value="data_source" <?php if ($data_filter == 'data_source') echo 'selected'; ?>>Nguồn data</option>
                        <option value="contact_status" <?php if ($data_filter == 'contact_status') echo 'selected'; ?>>Trạng thái tiếp xúc</option>
                        <option value="customer_evaluation" <?php if ($data_filter == 'customer_evaluation') echo 'selected'; ?>>Đánh giá khách hàng</option>
                    </select>
                </div>
            </form>

            <!-- Biểu đồ -->
            <canvas id="dataChart" width="400" height="200"></canvas>

            <script>
                var ctx = document.getElementById('dataChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php foreach ($data as $row) {
                                echo '"' . $row['filter_value'] . '",';
                            } ?>
                        ],
                        datasets: [{
                            label: 'Số lượng dữ liệu',
                            data: [
                                <?php foreach ($data as $row) {
                                    echo $row['total_records'] . ',';
                                } ?>
                            ],
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            fill: false
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            </script>
        <?php endif; ?>

        <!-- Modal Popup Tìm kiếm -->
        <div class="modal fade" id="phoneNumberModal" tabindex="-1" role="dialog" aria-labelledby="phoneNumberModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="phoneNumberModalLabel">Kết quả tìm kiếm</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo $modal_content; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

        <script>
            // Hiển thị modal khi có kết quả tìm kiếm
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone_number'])): ?>
                $(document).ready(function() {
                    $('#phoneNumberModal').modal('show');
                });
            <?php endif; ?>
        </script>
        <h2 style="text-align: center"><?php if (!isset($_SESSION['user_id'])) {
                                            echo $h2;
                                        } ?></h2>
    </div>
</body>

</html>