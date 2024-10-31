<?php
class EmailTemplate
{
    private $templates = [];

    public function __construct()
    {
        // Khởi tạo các template
        $this->templates = [
            'confirm_merchant' => $this->getConfirmMerchantTemplate(),
            'welcome_user' => $this->getWelcomeUserTemplate(),
            'assign_tasks' => $this->getAssignTaskTemplate(),
            // Bạn có thể thêm nhiều template khác ở đây
        ];
    }

    // Lấy template theo tên
    public function getTemplate($name)
    {
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
    }

    // Template xác nhận merchant
    private function getConfirmMerchantTemplate()
    {
        return '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Xác nhận Merchant</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background-color: #4CAF50;
                    color: #ffffff;
                    text-align: center;
                    padding: 20px;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    color: #333333;
                }
                .email-body p {
                    line-height: 1.6;
                }
                .email-footer {
                    background-color: #f4f4f4;
                    color: #888888;
                    text-align: center;
                    padding: 20px;
                    font-size: 14px;
                }
                .button {
                    background-color: #4CAF50;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    display: inline-block;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    Xác nhận Merchant
                </div>
                <div class="email-body">
                    <p>Chào bạn,</p>
                    <p>Bạn được phân công dự án. Hãy ấn vào link bên dưới để xác nhận dự án:</p>
                    <a href="{confirm_link}" class="button" target="_blank">Xem dự án</a>
                    Link dự án: {confirm_link}
                    <p>Dự án sẽ tự động cancel nếu bạn không xác nhận trong vòng 2h.</p>
                    <p style = "color:red"><strong>Đây là email tự động, vui lòng không reply</strong></p>
                </div>
                <div class="email-footer">
                    <p>Trân trọng,<br>Đội ngũ hỗ trợ TruongThanhWeb</p>
                    <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email website.truongthanh@gmail.com.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    // Template chào mừng người dùng mới
    private function getWelcomeUserTemplate()
    {
        return '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Chào mừng người dùng mới</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background-color: #2196F3;
                    color: #ffffff;
                    text-align: center;
                    padding: 20px;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    color: #333333;
                }
                .email-body p {
                    line-height: 1.6;
                }
                .email-footer {
                    background-color: #f4f4f4;
                    color: #888888;
                    text-align: center;
                    padding: 20px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    Chào mừng bạn đến với dịch vụ của chúng tôi!
                </div>
                <div class="email-body">
                    <p>Chào {user_name},</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản với chúng tôi. Chúng tôi rất vui mừng được chào đón bạn.</p>
                    <p>Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với đội ngũ hỗ trợ của chúng tôi.</p>
                </div>
                <div class="email-footer">
                    <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
                    <p>Nếu bạn không muốn nhận các email như thế này nữa, vui lòng <a href="{unsubscribe_link}">hủy đăng ký</a>.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    // Template gửi mail thông báo cho nhân viên khi được phân tasks
    private function getAssignTaskTemplate() {
        return '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Task</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background-color: #2196F3;
                    color: #ffffff;
                    text-align: center;
                    padding: 20px;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    color: #333333;
                }
                .email-body p {
                    line-height: 1.6;
                }
                .email-footer {
                    background-color: #f4f4f4;
                    color: #888888;
                    text-align: center;
                    padding: 20px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    Bạn được phân công nhiệm vụ mới!
                </div>
                <div class="email-body">
                    <p>Chào {user_name},</p>
                    <p>Vui lòng ấn vào nút link đây để xem tasks.</p>
                    <a href="https://data.vvtek.net/task_list.php" target="_blank">Xem nhiệm vụ</a>
                </div>
                <div class="email-footer">
                    <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
                    <p>Nếu bạn không muốn nhận các email như thế này nữa, vui lòng <a href="{unsubscribe_link}">hủy đăng ký</a>.</p>
                </div>
            </div>
        </body>
        </html>';
    }

}
