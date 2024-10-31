<?php
include 'db_connection.php';

// Truy vấn danh sách khách hàng từ database
$query = "SELECT id, customer_name, phone_number FROM customers LIMIT 100";
$result = $conn->query($query);

$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Trả về dữ liệu dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($customers);
?>
