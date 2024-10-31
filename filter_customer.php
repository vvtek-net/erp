<?php
include 'db_connection.php';

$query = $_GET['query'];

// Tìm kiếm khách hàng theo tên
$sql = "SELECT id, customer_name FROM customers WHERE customer_name LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode($customers);

$conn->close();
?>
