<?php
session_start();

header('Content-Type: text/html; charset=UTF-8');

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy dữ liệu từ GET
    $contract_customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
    $contract_service_id = isset($_GET['contract_service_id']) ? $_GET['contract_service_id'] : null;
    $contract_service_duration = isset($_GET['contract_service_duration']) ? $_GET['contract_service_duration'] : null;
    $contract_customer_phone = isset($_GET['contract_customer_phone']) ? $_GET['contract_customer_phone'] : null;
    $contract_addon_id = isset($_GET['contract_addon_id']) ? $_GET['contract_addon_id'] : null;
    $sale_order_id = isset($_GET['sale_order_id']) ? $_GET['sale_order_id'] : null; // lấy sale_order_id
    $user_id = $_SESSION['user_id'];
    // var_dump($contract_customer_id);
    // var_dump($contract_addon_id);
    // exit();
    try {
        // Tính tổng chi phí dịch vụ và addon
        $total_amount = 0;
        if (!empty($contract_service_id)) {
            $query = "SELECT price FROM services WHERE service_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $contract_service_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $total_amount += $result['price'];
        }

        if (!empty($contract_addon_id)) {
            $query = "SELECT price FROM addons WHERE addon_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $contract_addon_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $total_amount += $result['price'];
        }

        // Thêm hợp đồng, bao gồm cả sale_order_id
        $query = "INSERT INTO contracts (customer_id, account_id, sale_order_id, total_amount, status, signed_date, created_at, updated_at) VALUES (?, ?, ?, ?, 'draft', NOW(), NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $contract_customer_id, $user_id, $sale_order_id, $total_amount );
        $stmt->execute();

        $contract_id = $stmt->insert_id;

        // Điều hướng tới trang PDF sau khi tạo hợp đồng thành công
        header("Location: https://data.vvtek.net/contracts/generate_contract_pdf?contract_id=$contract_id");
        exit();
        
    } catch (Exception $e) {
        echo "Lỗi tạo hợp đồng: " . $e->getMessage();
    }
}
?>
