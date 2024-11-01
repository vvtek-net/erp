<?php
// Bắt đầu session
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối với cơ sở dữ liệu
include('db_connection.php');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$successMessage = "";
$isUpdated = false;

// Tính tổng số ngày nghỉ phép năm
$total_annual_leave_days = 12;

// Lấy số ngày nghỉ phép đã sử dụng
$account_id = $_SESSION['user_id'];
$sql = "SELECT SUM(days_used) as total_days_used FROM leaves WHERE account_id = ? AND leave_type = 'annual_leave'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$days_used = $row['total_days_used'] ? $row['total_days_used'] : 0;
$remaining_days = $total_annual_leave_days - $days_used;

// Xử lý khi người dùng nhấn nút "Tạo đơn xin nghỉ"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['leave_type'])) {
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];

    // Tính số ngày nghỉ
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days_requested = $end->diff($start)->days + 1;

    // Kiểm tra nếu là nghỉ phép năm và không đủ số ngày còn lại
    if ($leave_type == 'annual_leave' && $remaining_days <= 0) {
        $successMessage = "Lỗi: Bạn đã hết số ngày nghỉ phép năm. Vui lòng chọn nghỉ không lương.";
    } elseif ($leave_type == 'annual_leave' && $days_requested > $remaining_days) {
        $successMessage = "Lỗi: Bạn không đủ số ngày nghỉ phép năm còn lại.";
    } else {
        // Thêm đơn xin nghỉ vào cơ sở dữ liệu
        $sql = "INSERT INTO leaves (account_id, leave_type, start_date, end_date, reason, days_used) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssi", $account_id, $leave_type, $start_date, $end_date, $reason, $days_requested);

        if ($stmt->execute()) {
            $successMessage = "Đơn xin nghỉ phép đã được tạo thành công.";
            $isUpdated = true;
            // Cập nhật lại số ngày nghỉ còn lại
            $remaining_days -= $days_requested;
        } else {
            $successMessage = "Lỗi: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Đơn Xin Nghỉ</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }
        .form-container {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center">Tạo Đơn Xin Nghỉ</h2>
            <form method="post" action="">
                <div class="form-group">
                    <label for="leave_type">Loại nghỉ phép:</label>
                    <select name="leave_type" id="leave_type" class="form-control" required>
                        <option value="">Chọn loại nghỉ phép...</option>
                        <option value="annual_leave" <?php echo ($remaining_days <= 0) ? 'disabled' : ''; ?>>
                            Nghỉ phép năm (Tổng: 12 ngày - Còn lại: <?php echo $remaining_days; ?> ngày)
                        </option>
                        <option value="funeral_leave">Nghỉ tang chế</option>
                        <option value="marriage_leave">Nghỉ cưới hỏi</option>
                        <option value="unpaid_leave">Nghỉ không lương</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_date">Ngày kết thúc:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="reason">Lý do nghỉ:</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Tạo đơn xin nghỉ</button>
            </form>

            <?php if ($isUpdated): ?>
                <div class="alert alert-success mt-3">
                    <?php echo $successMessage; ?>
                </div>
            <?php elseif ($successMessage): ?>
                <div class="alert alert-danger mt-3">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
