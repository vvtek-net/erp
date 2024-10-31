<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

// Kết nối với cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$search_phone = isset($_GET['phone_number']) ? trim($_GET['phone_number']) : '';

if ($search_phone) {
    $search_phone_wildcard = '%' . $search_phone . '%';
    $sql = "SELECT * FROM customers WHERE phone_number LIKE ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_phone_wildcard);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    echo json_encode($customers);
}

$conn->close();
?>
