<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $service_id = $_POST['service_id'];
    $addon_id = $_POST['addon_id'];
    $website_template_link = $_POST['website_template_link'];
    $service_duration = $_POST['service_duration'];
    $user_id = $_SESSION['user_id'];

    $conn->begin_transaction();

    try {
        // Tính tổng chi phí dịch vụ và addon
        $total_amount = 0;
        if (!empty($service_id)) {
            foreach ($service_id as $id) {
                $query = "SELECT price FROM services WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $total_amount += $result['price'];
            }
        }

        if (!empty($addon_id)) {
            foreach ($addon_id as $id) {
                $query = "SELECT price FROM addons WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $total_amount += $result['price'];
            }
        }

        // Thêm hợp đồng
        $query = "INSERT INTO contracts (customer_id, total_amount, status, created_at) VALUES (?, ?, 'draft', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("id", $customer_id, $total_amount);
        $stmt->execute();

        $contract_id = $stmt->insert_id;
        $conn->commit();

        // Tạo file PDF
        header("Location: generate_contract_pdf.php?contract_id=$contract_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
