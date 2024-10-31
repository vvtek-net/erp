<?php
session_start();
// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "error: not_logged_in";
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "telesale");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discussion_id = intval($_POST['discussion_id']);
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['message']);

    if (empty($message)) {
        echo "error: empty_message";
        exit;
    }

    // Thêm tin nhắn vào bảng discussion_comments
    $sql = "INSERT INTO discussion_comments (discussion_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Lỗi chuẩn bị truy vấn: " . $conn->error); // Ghi lỗi vào log server
        echo "error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("iis", $discussion_id, $user_id, $message);
    if ($stmt->execute()) {
        echo "success";
    } else {
        error_log("Lỗi MySQL khi thực hiện truy vấn: " . $stmt->error); // Ghi lỗi vào log server
        echo "error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
