<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    die('Bạn phải đăng nhập trước khi tạo Ticket.');
}

// Kết nối cơ sở dữ liệu
include 'db_connection.php';

// Lấy danh sách merchants (người nhận ticket)
$query = "SELECT id, fullname FROM accounts WHERE role = 'merchant'";
$result = $conn->query($query);
$merchants = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách loại ticket (ticket types)
$query = "SELECT id, type_name FROM ticket_types";
$result = $conn->query($query);
$ticket_types = $result->fetch_all(MYSQLI_ASSOC);

// Kiểm tra nếu có dữ liệu từ GET để tạo ticket
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['merchant_id'], $_GET['ticket_type_id'], $_GET['description'])) {
    
    $merchant_id = $_GET['merchant_id'];
    $ticket_type_id = $_GET['ticket_type_id'];
    $description = $_GET['description'];
    $user_id = $_SESSION['user_id']; // Người tạo ticket
    
    // Tạo ticket mới
    $query = "INSERT INTO tickets (user_id, ticket_type_id, description, created_by, status) 
              VALUES (?, ?, ?, ?, 'confirmed')";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh SQL cho tickets: " . $conn->error);
    }

    $stmt->bind_param("iisi", $merchant_id, $ticket_type_id, $description, $user_id);

    if ($stmt->execute()) {
        $ticket_id = $stmt->insert_id;
        echo "<script>
            alert('Ticket đã tạo thành công.');
            window.location.href = 'ticket_details.php?ticket_id=$ticket_id';
        </script>";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ticket</title>
    <link rel="stylesheet" type="text/css" href="assets/css/sale.css">
</head>
<body>
    <!-- Top Menu -->
    <?php include('topmenu.php'); ?>

    <h1>Create New Ticket</h1>

    <form method="GET" action="create_ticket.php">
        <!-- Merchant selection -->
        <label for="merchant_id">Select Merchant:</label>
        <select name="merchant_id" id="merchant_id" required>
            <?php foreach ($merchants as $merchant): ?>
            <option value="<?php echo $merchant['id']; ?>"><?php echo $merchant['fullname']; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Ticket Type selection -->
        <label for="ticket_type_id">Select Ticket Type:</label>
        <select name="ticket_type_id" id="ticket_type_id" required>
            <?php foreach ($ticket_types as $type): ?>
            <option value="<?php echo $type['id']; ?>"><?php echo $type['type_name']; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Description field -->
        <label for="description">Customer Requirements:</label>
        <textarea name="description" id="description" rows="5" placeholder="Enter the details..." required></textarea>

        <!-- Submit button -->
        <button type="submit">Create Ticket</button>
    </form>
</body>
</html>
