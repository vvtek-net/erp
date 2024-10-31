<?php
session_start();
// Bật hiển thị lỗi để kiểm tra
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit;
}

// Kết nối với cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "telesale");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . $conn->connect_error]);
    exit;
}

// Kiểm tra hành động gửi đến
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'update') {
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $status = isset($_POST['task_status']) ? trim($_POST['task_status']) : '';
    $note = isset($_POST['task_note']) ? $conn->real_escape_string(trim($_POST['task_note'])) : '';

    if ($task_id <= 0 || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'ID và trạng thái của task là bắt buộc']);
        exit;
    }

    // Cập nhật trạng thái và ghi chú của task
    $sql = "UPDATE tasks SET status = ?, note = ?, updated_at = NOW() WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $note, $task_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Task đã được cập nhật thành công']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật task: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
}

$conn->close();
?>
