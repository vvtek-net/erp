<?php
session_start();
include 'db_connection.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    die('Bạn phải đăng nhập để thêm log.');
}

// Lấy dữ liệu từ form
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Kiểm tra dữ liệu
if ($ticket_id <= 0 || empty($message)) {
    die('Dữ liệu không hợp lệ.');
}

$sender_id = $_SESSION['user_id'];  // ID của người gửi

// Chèn log vào bảng ticket_logs
$query = "INSERT INTO ticket_logs (ticket_id, sender_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
}

$stmt->bind_param("iis", $ticket_id, $sender_id, $message);

if ($stmt->execute()) {
    // Thành công, quay lại trang ticket_details.php với ticket_id
    header("Location: ticket_details.php?ticket_id=$ticket_id");
} else {
    // Lỗi chèn log
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
