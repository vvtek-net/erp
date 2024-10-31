<?php

require 'send_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ form
    $ticket_id = $_POST['ticket_id'];
    $ticket_type_id = $_POST['ticket_type_id'];
    $merchant_id = $_POST['merchant_id'];
    $description = $_POST['description'];

    $role = $_POST['role'];

    // email data

    // Khởi tạo email template
    $resultMerchant = getAccount($conn, $merchant_id); // Pass the connection and merchant_id to getAccount
    // var_dump($resultMerchant);
    // exit();
    $emailTemplate = new EmailTemplate();
    $template = $emailTemplate->getTemplate('confirm_merchant');

    // Tạo URL xác nhận
    $confirmLink = 'https://vvtek.net/telesale/ticket_details.php?ticket_id=' . urlencode($ticket_id);

    // Thay thế {confirm_link} trong template bằng URL thực tế
    $template = str_replace('{confirm_link}', $confirmLink, $template);

    // Tiêu đề email
    $subject = 'Project Assigned';
    $merchantEmail = $resultMerchant['username'];
    $merchantName = $resultMerchant['fullname'];
    // $template = $template;

    // Cập nhật thông tin ticket
    if ($role == 'merchant') {
        // var_dump($description);
        // exit();
        $query = "UPDATE tickets SET user_id = ?, ticket_type_id = ?,  status = 'confirmed', user_id = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $merchant_id, $ticket_type_id, $merchant_id, $ticket_id);
        $stmt->execute();
        $stmt->close();
        // Quay lại trang chi tiết ticket
        header("Location: ticket_details.php?ticket_id=$ticket_id");
        exit();
    } else {
        // var_dump()
        $query = "UPDATE tickets SET user_id = ?, ticket_type_id = ?, description = ?, status = 'pending', user_id = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisii", $merchant_id, $ticket_type_id, $description, $merchant_id, $ticket_id);
        $stmt->execute();
        $stmt->close();
        sendEmail($merchantEmail, $merchantName, $template, $subject);

        // Hiển thị popup thông báo lưu thành công
        echo "<script type='text/javascript'>
            alert('Lưu thành công!');
            window.location.href = 'ticket_details.php?ticket_id=$ticket_id';
        </script>";
        exit();
    }

    // Gửi email



}
