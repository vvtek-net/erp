<?php
session_start();
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "telesale");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$discussion_id = isset($_GET['discussion_id']) ? intval($_GET['discussion_id']) : 0;

// Lấy các tin nhắn từ cơ sở dữ liệu
$sql = "SELECT dc.comment, a.fullname, dc.created_at 
        FROM discussion_comments dc 
        JOIN accounts a ON dc.user_id = a.id 
        WHERE dc.discussion_id = ? 
        ORDER BY dc.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $discussion_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();

// Trả về kết quả dạng JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>
