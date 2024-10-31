<?php
session_start();
// Kết nối cơ sở dữ liệu
include 'db_connection.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id'])) {
    $_SESSION['current_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
require 'function.php';
require 'mail_template.php';
include 'confirm_ticket.php';

$role = $_SESSION['role'];

// Nhận ticket_id từ URL
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;

if ($ticket_id <= 0) {
    die('Lỗi: ticket_id không hợp lệ.');
}

// Lấy thông tin ticket và link template từ bảng sale_orders
$query = "SELECT tickets.*, sale_orders.website_template_link, sale_orders.service_duration, accounts.fullname AS user_name 
          FROM tickets
          LEFT JOIN sale_orders ON tickets.sale_order_id = sale_orders.id
          LEFT JOIN accounts ON tickets.user_id = accounts.id 
          WHERE tickets.id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
}
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();


// Lấy danh sách merchants (thay cho users)
$query = "SELECT * FROM accounts WHERE role = 'merchant'";
$result = $conn->query($query);
$merchants = $result->fetch_all(MYSQLI_ASSOC);

//  Lấy fullname của người tạo ticket
$query = "SELECT * FROM accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ticket['created_by']);
$stmt->execute();
$nameCreator = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy danh sách ticket types
$query = "SELECT * FROM ticket_types";
$result = $conn->query($query);
$ticket_types = $result->fetch_all(MYSQLI_ASSOC);

// Lấy lịch sử log từ bảng ticket_logs
$logs = [];
$query = "SELECT ticket_logs.*, accounts.fullname AS sender_name 
          FROM ticket_logs 
          LEFT JOIN accounts ON ticket_logs.sender_id = accounts.id 
          WHERE ticket_logs.ticket_id = ? 
          ORDER BY ticket_logs.created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!-- Giao diện -->
<!DOCTYPE html>
<html>

<head>
    <title>Ticket Details</title>
    <link rel="stylesheet" type="text/css" href="assets/css/sale.css">
    <link rel="stylesheet" href="assets/css/log.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/chatbox.css">
    <style>
        /* Tạo kiểu cho thẻ <a> */
        #log a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        #log a:hover {
            text-decoration: underline;
        }

        /* Tạo kiểu cho textarea */
        #log textarea {
            width: 100%;
            height: 100px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            resize: vertical;
            /* Cho phép thay đổi kích thước theo chiều dọc */
        }

        /* Tạo kiểu cho các đoạn văn bản log */
        #log p {
            font-size: 14px;
            margin-top: 5px;
        }

        #log p strong {
            color: #333;
        }

        #log p em {
            color: #777;
            font-style: italic;
            font-size: 12px;
        }

        /* Tạo kiểu chung cho các form-group */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Tạo khoảng cách giữa nhãn và ô nhập liệu */
        .form-group label {
            min-width: 200px;
            margin-right: 10px;
            text-align: right;
        }

        /* Đặt độ rộng mặc định cho các ô nhập liệu */
        .form-group .form-control {
            flex: 1;
        }

        /* Điều chỉnh độ rộng cho các select và textarea để nhất quán */
        .form-group select.form-control,
        .form-group textarea.form-control {
            width: auto;
        }

        /* Sắp xếp các nút bấm theo hàng ngang */
        button[type="submit"] {
            margin-top: 20px;
        }

        /* Điều chỉnh giao diện cho các ô form theo dạng cột nhỏ hơn */
        @media (max-width: 768px) {
            .form-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-group label {
                min-width: auto;
                text-align: left;
                margin-bottom: 5px;
            }

            .form-group .form-control {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include('topmenu.php'); ?>
    <div class="container mt-5">
        <h1>Ticket Details</h1>

        <form method="POST" action="">
            <input type="hidden" name="ticket_id" id="ticket_id" value="<?php echo $ticket['id']; ?>">
            <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">

            <div class="form-group">
                <label for="username">Assigned to:</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php
                                                                                                if (isset($merchantUsername)) echo $merchantUsername['fullname'];
                                                                                                else  echo $_SESSION['fullname']; ?>" disabled>
            </div>

            <!-- <label>Service:</label>
        <select name="service">
            <option value="Thiết kế website">Thiết kế website</option>
        </select> -->

            <div class="form-group mb-3">
                <label for="selected">Service:</label>
                <select class="form-control" id="service" name="service" <?php if ($ticket['status'] == 'confirmed') echo 'disabled'; ?>>
                    <option value="Thiết kế website">Thiết kế website</option>
                </select>
            </div>

            <div class="form-group">
                <label for="">Website Template Link:</label>
                <input type="text" class="form-control" id="website_template" name="website_template" value="<?php echo $ticket['website_template_link']; ?>" <?php if ($ticket['status'] == 'confirmed') echo 'disabled'; ?>>
            </div>

            <div class="form-group">
                <label for="">Service Duration (Months):</label>
                <input type="text" class="form-control" id="service_duration" name="service_duration" value="<?php echo $ticket['service_duration']; ?>" <?php if ($ticket['status'] == 'confirmed') echo 'disabled'; ?>>
            </div>


            <div class="form-group mb-3">
                <label for="merchant_id">Merchant:</label>
                <select class="form-control" id="" name="merchant_id" <?php if ($ticket['status'] == 'confirmed') echo 'disabled'; ?>>
                    <?php foreach ($merchants as $merchant): ?> <!-- Hiển thị danh sách merchants thay cho users -->
                        <option value="<?php echo $merchant['id']; ?>" <?php if ($ticket['user_id'] == $merchant['id']) echo 'selected'; ?>>
                            <?php echo $merchant['fullname']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="ticket_type_id">Ticket Type:</label>
                <select class="form-control" id="" name="ticket_type_id" <?php if ($ticket['status'] == 'confirmed') echo 'disabled'; ?>>
                    <?php foreach ($ticket_types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php if ($ticket['ticket_type_id'] == $type['id']) echo 'selected'; ?>>
                            <?php echo $type['type_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Customer Requirements:</label>
                <textarea name="description" class="form-control" id="description" rows="3" <?php if ($role == 'merchant') echo 'disabled'; ?>><?php echo $ticket['description']; ?></textarea>
            </div>

            <?php
            // Kiểm tra trạng thái ticket (pending, confirmed, deleted)
            if ($role == 'user') {
                if ($ticket['status'] == 'initialization') {
                    // Hiển thị nút Confirm Sale nếu ticket chưa được xác nhận (status == 'pending')
                    echo '<button class="btn btn-primary" type="submit" name="confirm_sale">Save</button>';
                }
                if ($ticket['status'] == 'pending') {
                    echo '<p class="alert alert-success" role="alert">Ticket đang chờ xác nhận.</p>';
                }
                if ($ticket['status'] == 'confirmed') {
                    echo '<p class="alert alert-success" role="alert">Ticket đã được xác nhận.</p>';
                }
            } else {
                if ($role == 'merchant' && $ticket['status'] == 'pending') {
                    echo '<button class="btn btn-primary" type="submit" name="confirm_sale">Confirm Project</button>';
                } else {
                    // Hiển thị thông báo nếu ticket đã xác nhận (status == 'confirmed')
                    echo '<p class="alert alert-success" role="alert">Ticket đã được xác nhận.</p>';
                }
            }
            ?>
        </form>
        <!-- Hiển thị log -->
        <!-- <h2 class="container">Ticket Log</h2> -->
        <h2>Ticket Log</h2>
        <form id="log" class="mb-3" style="max-width: 100%;" action="add_log.php" method="GET">
            <input type="text" id="ticket_id" class="form-control" hidden name="ticket_id" value="<?php echo $ticket['id']; ?>">
            <?php foreach ($logs as $log): ?>
                <p><strong><?php echo $log['sender_name']; ?>:</strong> <?php echo $log['message']; ?> <em>(<?php echo $log['created_at']; ?>)</em></p>
            <?php endforeach; ?>
            <a href="#" onclick="showTextarea(event)">Log Message</a>
            <textarea name="message" id="logTextarea" style="display: none;" class="mb-3"></textarea>
            <input type="submit" id="logBtn" class="btn btn-primary" value="Submit">
        </form>

        <script>
            function showTextarea(event) {
                event.preventDefault(); // Ngăn chặn hành động mặc định của thẻ <a>
                var textarea = document.getElementById('logTextarea');
                // Kiểm tra trạng thái hiển thị của textarea
                if (textarea.style.display === 'none' || textarea.style.display === '') {
                    textarea.style.display = 'block'; // Hiển thị textarea nếu đang bị ẩn
                } else {
                    textarea.style.display = 'none'; // Ẩn textarea nếu đang hiển thị
                }

                var input = document.getElementById('logBtn');
                // Kiểm tra trạng thái hiển thị của textarea
                if (input.style.display === 'none' || input.style.display === '') {
                    input.style.display = 'block'; // Hiển thị textarea nếu đang bị ẩn
                } else {
                    input.style.display = 'none'; // Ẩn textarea nếu đang hiển thị
                }
            }
        </script>

    </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatboxButton = document.querySelector(".convbot-button");
            const chatboxPopup = document.querySelector(".chatbox-popup");
            const btnClose = document.querySelector(".btn-close");

            // Mở khung chat
            chatboxButton.addEventListener("click", function() {
                chatboxPopup.classList.toggle("d-none");
                chatboxButton.classList.add("d-none");
            });

            // Đóng khung chat
            btnClose.addEventListener("click", function() {
                chatboxPopup.classList.add("d-none");
                chatboxButton.classList.remove("d-none");
            });
        });
    </script>

</body>

</html>