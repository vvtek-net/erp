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

// Kiểm tra nếu có ngày bắt đầu và ngày kết thúc
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] . " 00:00:00" : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] . " 23:59:59" : '';

$where_clause = "";
if ($start_date && $end_date) {
    $where_clause = "WHERE created_at BETWEEN '$start_date' AND '$end_date'";
}

// Truy vấn dữ liệu số lượng theo từng người nhập
$sql = "SELECT created_by, 
        COUNT(*) as total_entries, 
        SUM(CASE WHEN customer_evaluation = 'cơ hội' THEN 1 ELSE 0 END) as total_opportunity,
        SUM(CASE WHEN customer_evaluation = 'tiềm năng' THEN 1 ELSE 0 END) as total_potential,
        SUM(CASE WHEN customer_evaluation = 'chăm sóc sau bán' THEN 1 ELSE 0 END) as total_after_sale
        FROM customers 
        $where_clause
        GROUP BY created_by";

$result = $conn->query($sql);

$chartData = [];
$totalDataPerUser = [];
$totalOpportunity = [];
$totalPotential = [];
$totalAfterSale = [];

while ($row = $result->fetch_assoc()) {
    $chartData[$row['created_by']] = $row;
    $totalDataPerUser[$row['created_by']] = $row['total_entries'];
    $totalOpportunity[$row['created_by']] = $row['total_opportunity'];
    $totalPotential[$row['created_by']] = $row['total_potential'];
    $totalAfterSale[$row['created_by']] = $row['total_after_sale'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Thống kê nhập liệu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
}

.sidebar {
    width: 250px;
    background-color: #343a40;
    color: white;
    padding: 20px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    padding: 10px;
    margin-bottom: 10px;
    background-color: #495057;
    border-radius: 4px;
    text-align: center;
    transition: background-color 0.3s, box-shadow 0.3s;
}

.sidebar ul li a {
    color: white;
    text-decoration: none;
    display: block;
    font-size: 16px;
}

.sidebar ul li:hover {
    background-color: #6c757d;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.container {
    flex: 1;
    padding: 20px;
    margin-left: 250px;
    width: calc(100% - 250px);
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

h2 {
    margin-bottom: 20px;
    font-weight: 500;
    font-size: 24px;
    text-align: center; /* Canh giữa tiêu đề */
}

.form-row {
    justify-content: center; /* Căn giữa nội dung form */
    width: 100%; /* Đặt độ rộng form chiếm toàn bộ không gian */
    max-width: 800px; /* Đặt giới hạn độ rộng tối đa để không bị quá rộng */
    margin: 0 auto; /* Căn giữa form */
}

.form-group.col-md-5,
.form-group.col-md-2 {
    max-width: 100%;
    padding: 0 15px; /* Thêm khoảng cách cho các trường */
}

.form-group.col-md-5 {
    flex: 0 0 47%; /* Điều chỉnh độ rộng các trường ngày để chiếm đều không gian */
}

.form-group.col-md-2 {
    flex: 0 0 6%; /* Điều chỉnh độ rộng của nút submit */
}

.form-group.col-md-2 .btn-primary {
    height: 100%; /* Làm cho chiều cao nút khớp với các trường nhập */
    padding: 10px; /* Thêm khoảng cách vào nút */
    display: flex;
    align-items: center;
    justify-content: center; /* Canh giữa văn bản trong nút */
}

.chart-container {
    background-color: #fafafa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
    height: 400px;
    display: flex;
    justify-content: center;
    align-items: center;
}

canvas {
    width: 100% !important;
    height: 100% !important;
}

    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Thống kê nhập liệu</a></li>
            <li><a href="#">Quản lý tài khoản</a></li>
            <li><a href="#">Quản lý dữ liệu</a></li>
            <li><a href="../logout.php">Đăng xuất</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Thống kê nhập liệu</h2>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label for="start_date">Ngày bắt đầu</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="form-group col-md-5">
                    <label for="end_date">Ngày kết thúc</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Xem thống kê</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="totalChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="opportunityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="potentialChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="afterSaleChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dữ liệu từ PHP
        const totalDataPerUser = <?php echo json_encode($totalDataPerUser, JSON_UNESCAPED_UNICODE); ?>;
        const totalOpportunity = <?php echo json_encode($totalOpportunity, JSON_UNESCAPED_UNICODE); ?>;
        const totalPotential = <?php echo json_encode($totalPotential, JSON_UNESCAPED_UNICODE); ?>;
        const totalAfterSale = <?php echo json_encode($totalAfterSale, JSON_UNESCAPED_UNICODE); ?>;

        // Labels for charts
        const labels = Object.keys(totalDataPerUser);

        // Total data chart (Pie Chart)
        const totalChartCtx = document.getElementById('totalChart').getContext('2d');
        new Chart(totalChartCtx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tổng số lượng nhập liệu',
                    data: Object.values(totalDataPerUser),
                    backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        // Opportunity chart (Bar Chart)
        const opportunityChartCtx = document.getElementById('opportunityChart').getContext('2d');
        new Chart(opportunityChartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Khách hàng cơ hội',
                    data: Object.values(totalOpportunity),
                    backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(153, 102, 255, 0.2)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)', 'rgba(153, 102, 255, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        // Potential customer chart (Line Chart)
        const potentialChartCtx = document.getElementById('potentialChart').getContext('2d');
        new Chart(potentialChartCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Khách hàng tiềm năng',
                    data: Object.values(totalPotential),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // After sale customer chart (Radar Chart)
        const afterSaleChartCtx = document.getElementById('afterSaleChart').getContext('2d');
        new Chart(afterSaleChartCtx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Khách hàng chăm sóc sau bán',
                    data: Object.values(totalAfterSale),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: Math.max(...Object.values(totalAfterSale)) + 5
                    }
                }
            }
        });
    </script>
</body>
</html>
