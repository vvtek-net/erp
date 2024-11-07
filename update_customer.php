<?php
// Bắt đầu session
session_start();

include 'admin/src/features.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
    exit();
}

// Kết nối với cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit();
}

// Lấy dữ liệu từ GET
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;
$data_source = isset($_GET['data_source']) ? trim($_GET['data_source']) : null;
$contact_status = isset($_GET['contact_status']) ? trim($_GET['contact_status']) : null;
$customer_evaluation = isset($_GET['customer_evaluation']) ? trim($_GET['customer_evaluation']) : null;
$address = isset($_GET['address']) ? trim($_GET['address']) : null;
$identity_person = isset($_GET['identity_person']) ? trim($_GET['identity_person']) : null;
$birthday = isset($_GET['birthday']) ? convertFormatDate(trim($_GET['birthday'])) : null;
$tax = isset($_GET['tax']) ? trim($_GET['tax']) : null;
$founding_date = isset($_GET['founding_date']) ? convertFormatDate(trim($_GET['founding_date'])) : null;
$agent_name = isset($_GET['agent_name']) ? trim($_GET['agent_name']) : null;
$agent_position = isset($_GET['agent_position']) ? trim($_GET['agent_position']) : null;
if (isset($_GET['customer_type']) && $_GET['customer_type'] === 'individual') {
    $customer_name = isset($_GET['customer_name_individual']) ? trim($_GET['customer_name_individual']) : null;
    $phone_number = isset($_GET['phone_number_individual']) ? trim($_GET['phone_number_individual']) : null;
    $email = isset($_GET['email_individual']) ? trim($_GET['email_individual']) : null;
} else if (isset($_GET['customer_type']) && $_GET['customer_type'] === 'business') {
    $customer_name = isset($_GET['customer_name_business']) ? trim($_GET['customer_name_business']) : null;
    $phone_number = isset($_GET['phone_number_business']) ? trim($_GET['phone_number_business']) : null;
    $email = isset($_GET['email_business']) ? trim($_GET['email_business']) : null;
}

// Kiểm tra nếu người dùng có quyền sửa thông tin này
$fullname = $_SESSION['fullname'];
if ($customer_id && $customer_name && $phone_number && $data_source && $contact_status && $customer_evaluation) {
    $sql_check = "SELECT * FROM customers WHERE id = ? AND created_by = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $customer_id, $fullname);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Cập nhật thông tin khách hàng
        $sql_update = "UPDATE customers SET customer_name = ?, phone_number = ?, email = ?, address = ?, identity_person = ?, birthday = ?, founding_date = ?, agent_name = ?, agent_position = ?, tax = ?, data_source = ?, contact_status = ?, customer_evaluation = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssssssssssi", $customer_name, $phone_number, $email, $address, $identity_person, $birthday, $founding_date, $agent_name, $agent_position, $tax, $data_source, $contact_status, $customer_evaluation, $customer_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cập nhật thất bại.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền sửa thông tin khách hàng này.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.']);
}

$conn->close();
?>
