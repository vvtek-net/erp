<?php
session_start();
// Kết nối cơ sở dữ liệu
include 'db_connection.php';
if (!isset($_SESSION['user_id'])) {
    $_SESSION['current_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
// Kiểm tra vai trò người dùng

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

if ($role == 'admin') {
    // Admin có thể xem tất cả ticket
    $query = "SELECT tickets.*, sale_orders.id AS sale_order_id, customers.customer_name, accounts.fullname AS merchant_name
              FROM tickets
              LEFT JOIN sale_orders ON tickets.sale_order_id = sale_orders.id
              LEFT JOIN customers ON sale_orders.customer_id = customers.id
              LEFT JOIN accounts ON tickets.user_id = accounts.id";
    $stmt = $conn->prepare($query); // Chuẩn bị câu truy vấn cho admin

    // Kiểm tra xem việc chuẩn bị câu truy vấn có thành công không
    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh SQL cho admin: " . $conn->error);
    }
} elseif ($role == 'merchant') {
    // Merchant chỉ xem những ticket được giao
    $query = "SELECT tickets.*, sale_orders.id AS sale_order_id, customers.customer_name, accounts.fullname AS merchant_name
              FROM tickets
              LEFT JOIN sale_orders ON tickets.sale_order_id = sale_orders.id
              LEFT JOIN customers ON sale_orders.customer_id = customers.id
              LEFT JOIN accounts ON tickets.user_id = accounts.id
              WHERE tickets.user_id = ? and tickets.status='pending' or tickets.status='confirmed'
                ORDER BY tickets.updated_at DESC";
    $stmt = $conn->prepare($query);

    // Kiểm tra xem việc chuẩn bị câu truy vấn có thành công không
    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh SQL cho merchant: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id); // Gán tham số cho merchant
} else {
    // User chỉ xem những ticket mà họ đã tạo
    $query = "SELECT tickets.*, sale_orders.id AS sale_order_id, customers.customer_name, accounts.fullname AS merchant_name
              FROM tickets
              LEFT JOIN sale_orders ON tickets.sale_order_id = sale_orders.id
              LEFT JOIN customers ON sale_orders.customer_id = customers.id
              LEFT JOIN accounts ON tickets.user_id = accounts.id
              WHERE tickets.created_by = ?";
    $stmt = $conn->prepare($query);

    // Kiểm tra xem việc chuẩn bị câu lệnh SQL có thành công không
    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh SQL cho user: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id); // Gán tham số cho user
}

$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

// var_dump($tickets);
// exit();

$stmt->close();
$conn->close();
?>

<!-- Giao diện -->
<!DOCTYPE html>
<html>

<head>
    <title>Ticket Management</title>
    <link rel="stylesheet" type="text/css" href="assets/css/sale.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include('topmenu.php'); ?>
    <div class="container mt-5">
        <h1 class="fw-bolder">Ticket Management</h1>
        <?php if ($_SESSION['role'] !== 'merchant') {
            ?> 
        <a class="btn btn-success mb-3" href="sale_order.php">Create New Ticket</a>

        <?php 
        } ?>
        <table class="table table-striped" border="1">
            <thead class="">
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Merchant</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $index => $ticket): ?>
                    <tr>
                        <th class="align-content-center" scope="row"><?php echo ++$index ?></th>
                        <td class="align-content-center"><?php echo $ticket['customer_name']; ?></td>
                        <td class="align-content-center">
                            <?php if ($ticket['status'] == 'confirmed') echo $ticket['merchant_name'];
                            else echo 'Chưa có đối tác....'; ?>
                        </td>
                        <td class="align-content-center">
                            <span class="badge rounded-pill 
                            <?php if ($ticket['status'] == 'confirmed') echo 'bg-primary';
                            else echo 'bg-warning'; ?> p-2"><?php echo $ticket['status']; ?>
                            </span>
                        </td>
                        <td class="align-content-center">
                            <a class="btn btn-secondary" href="ticket_details.php?ticket_id=<?php echo $ticket['id']; ?>">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>