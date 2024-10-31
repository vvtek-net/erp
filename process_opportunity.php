<?php
// Bắt đầu session
session_start();

// import
include 'db_connection.php';
require 'function.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = (int)$_GET['customer_id'];
$account_id = (int)$_SESSION['user_id'];

$sql = "INSERT INTO opportunities (customer_id, account_id, updated_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $customer_id, $account_id);

if ($stmt->execute()) {
    $status = 'success';
    $successMessage = "Khách hàng đã được thêm thành công.";
} else {
    $status = 'error';
    if ($conn->errno == 1062) {
        $successMessage = "Lỗi: Số điện thoại này đã có trên hệ thống.";
    } else {
        $successMessage = "Lỗi: " . $conn->error;
    }
}

// load lại trang
header("Location: user_data.php?status=$status");
exit();
