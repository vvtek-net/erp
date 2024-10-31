<?php

// Kết nối tới cơ sở dữ liệu
$mysqli = new mysqli("localhost", "root", "", "telesale");

if ($mysqli->connect_error) {
    die("Kết nối tới cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
}

// Nhận dữ liệu ngày bắt đầu và ngày kết thúc từ form
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Truy vấn lấy tất cả created_by và số lượng dữ liệu nhập
$query_all_created_by = "
    SELECT created_by, COUNT(*) as total 
    FROM customers 
    WHERE created_at BETWEEN '$start_date' AND '$end_date' 
    GROUP BY created_by";
$result_all_created_by = $mysqli->query($query_all_created_by);
$all_created_by = [];
while($row = $result_all_created_by->fetch_assoc()) {
    $all_created_by[] = $row;
}

// Truy vấn thống kê khách hàng cơ hội cho từng người nhập
$evaluation_data = [];
foreach ($all_created_by as $created) {
    $created_by = $created['created_by'];
    $query_evaluation = "
        SELECT COUNT(*) as total 
        FROM customers 
        WHERE customer_evaluation = 'cơ hội' AND created_by = '$created_by' AND created_at BETWEEN '$start_date' AND '$end_date'";
    $result_evaluation = $mysqli->query($query_evaluation);
    $evaluation_data[$created_by] = $result_evaluation->fetch_assoc()['total'] ?? 0;
}

// Đóng kết nối
$mysqli->close();

// Trả dữ liệu JSON
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['created_by' => $all_created_by, 'evaluation' => $evaluation_data], JSON_UNESCAPED_UNICODE);
?>
