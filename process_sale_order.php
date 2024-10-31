<?php

session_start();

include 'db_connection.php';

// Kiểm tra nếu có dữ liệu từ GET (form gửi sử dụng phương thức GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];
    $service_type = 'Thiết kế website'; // Dịch vụ hiện tại cố định
    $website_template_link = $_GET['website_template_link'];
    $service_duration = $_GET['service_duration'];
    $user_id = $_SESSION['user_id']; // Lấy ID của user từ session (người dùng đã đăng nhập)

    // Bắt đầu giao dịch để chèn sale order và ticket
    $conn->begin_transaction();

    try {
        // Tạo sale order mới
        $query = "INSERT INTO sale_orders (customer_id, service_type, website_template_link, service_duration, created_by) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        // Kiểm tra lỗi chuẩn bị câu lệnh SQL
        if (!$stmt) {
            die("Lỗi chuẩn bị câu lệnh SQL cho sale_orders: " . $conn->error);
        }

        $stmt->bind_param("issii", $customer_id, $service_type, $website_template_link, $service_duration, $user_id);

        // Thực thi câu lệnh và kiểm tra lỗi
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Lấy ID của Sale Order vừa tạo
        $sale_order_id = $stmt->insert_id;

        // Tự động tạo ticket tương ứng
        $query = "INSERT INTO tickets (sale_order_id, user_id, created_by, status) VALUES (?, ?, ?, 'initialization')";
        $stmt = $conn->prepare($query);

        // Kiểm tra lỗi chuẩn bị câu lệnh SQL cho tickets
        if (!$stmt) {
            die("Lỗi chuẩn bị câu lệnh SQL cho tickets: " . $conn->error);
        }

        $stmt->bind_param("iii", $sale_order_id, $user_id, $user_id);

        // Thực thi câu lệnh và kiểm tra lỗi
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Commit giao dịch
        $conn->commit();

        // Lấy ID của ticket vừa tạo
        $ticket_id = $stmt->insert_id;

        // Đóng statement
        $stmt->close();

        // Hiển thị popup thông báo và hỏi người dùng có muốn chuyển đến trang ticket không
        echo "<script>
            if (confirm('Sale Order đã tạo thành công. Bạn có muốn chuyển đến trang Ticket không?')) {
                window.location.href = 'ticket_details.php?ticket_id=$ticket_id';
            }
        </script>";
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        echo "Lỗi: " . $e->getMessage();
    }
}
