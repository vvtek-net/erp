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

// Gán ngày bắt đầu và ngày kết thúc mặc định
$default_start_date = date('Y-m-d', strtotime('-1 day'));
$default_end_date = date('Y-m-d');

$start_date = isset($_POST['start_date']) ? $_POST['start_date'] . " 00:00:00" : $default_start_date . " 00:00:00";
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] . " 23:59:59" : $default_end_date . " 23:59:59";

// Truy vấn dữ liệu số lượng theo từng người nhập
$where_clause = "WHERE created_at BETWEEN '$start_date' AND '$end_date'";

$sql = "SELECT created_by, COUNT(*) as total_entries
        FROM customers 
        $where_clause
        GROUP BY created_by
        ORDER BY created_by ASC";

$result = $conn->query($sql);

$totalDataPerUser = [];
while ($row = $result->fetch_assoc()) {
    // Kiểm tra nếu 'total_entries' tồn tại trong kết quả trả về
    if (isset($row['total_entries'])) {
        $totalDataPerUser[$row['created_by']] = $row['total_entries'];
    } else {
        $totalDataPerUser[$row['created_by']] = 0; // Gán giá trị mặc định nếu không có
    }
}

// Truy vấn dữ liệu khách hàng theo tất cả các đánh giá trong customer_evaluation
$evaluation_sql = "SELECT customer_evaluation, COUNT(*) as total_count
                   FROM customers
                   $where_clause
                   GROUP BY customer_evaluation
                   ORDER BY customer_evaluation ASC";

$evaluation_result = $conn->query($evaluation_sql);

$evaluationData = [];
while ($row = $evaluation_result->fetch_assoc()) {
    $evaluationData[$row['customer_evaluation']] = $row['total_count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Thống kê nhập liệu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Thống kê nhập liệu theo người dùng</h2>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars(substr($start_date, 0, 10)); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Ngày kết thúc:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars(substr($end_date, 0, 10)); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Xem thống kê</button>
            </div>
        </form>
        <br><br>
        <div class="chart-container">
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="opportunityChart"></canvas>
        </div>
        <script>
            (function() {
                const ctx = document.getElementById('barChart').getContext('2d');
                const labels = <?php echo json_encode(array_keys($totalDataPerUser)); ?>;
                const data = <?php echo json_encode(array_values($totalDataPerUser)); ?>;
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Tổng số lượng nhập liệu',
                            data: data,
                            backgroundColor: 'rgba(149, 131, 180, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10
                                }
                            }
                        }
                    }
                });

                // Sử dụng biểu đồ thứ 2 để hiển thị tất cả các trạng thái đánh giá của khách hàng
                const evaluationCtx = document.getElementById('opportunityChart').getContext('2d');
                const evaluationLabels = <?php echo json_encode(array_keys($evaluationData)); ?>;
                const evaluationData = <?php echo json_encode(array_values($evaluationData)); ?>;
                
                new Chart(evaluationCtx, {
                    type: 'bar',
                    data: {
                        labels: evaluationLabels,
                        datasets: [{
                            label: 'Đánh giá khách hàng',
                            data: evaluationData,
                            backgroundColor: 'rgba(74, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10
                                }
                            }
                        }
                    }
                });
            })();
        </script>
    </div>
</body>
</html>
