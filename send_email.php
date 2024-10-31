<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Bao gồm thư viện PHPMailer đã cài qua Composer
require 'vendor/autoload.php';

function sendEmail($email, $name, $content, $title)
{

    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Máy chủ SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'website.truongthanh@gmail.com'; // Địa chỉ Gmail của bạn
        $mail->Password   = 'qrwr hcnb pbna dfxn';           // Mật khẩu ứng dụng vừa tạo
        $mail->SMTPSecure = 'tls';                           // Sử dụng TLS
        $mail->Port       = 587;                             // Cổng SMTP cho TLS

        // Cấu hình thông tin người gửi và người nhận
        $mail->setFrom('website.truongthanh@gmail.com', 'TruongThanhWeb'); // Thay 'Your Name' bằng tên của bạn
        $mail->addAddress($email, $name); // Thay bằng địa chỉ người nhận

        // Cấu hình nội dung email
        $mail->isHTML(true);                                  // Email định dạng HTML
        $mail->Subject = $title;                        // Tiêu đề email
        $mail->Body    = '<p>' . $content . '</p>'; // Nội dung HTML
        // $mail->AltBody = 'This is a test email sent from PHP using PHPMailer.'; // Nội dung thay thế nếu không hỗ trợ HTML

        // Gửi email
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
