<?php

// Bắt đầu session
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['current_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Kết nối với cơ sở dữ liệu
include('db_connection.php');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$fullname = $_SESSION['fullname'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Lấy danh sách các task
$sql = "SELECT task_id, title, description, status, start_date, end_date, created_at 
        FROM tasks 
        WHERE assign_id = $user_id
        ORDER BY created_at DESC";
$result = $conn->query($sql);

// update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_status']) && isset($_POST['task_id'])) {
    $task_status = $_POST['task_status'];
    $task_id = $_POST['task_id'];
    $task_note = $_POST['task_note'];

    $query = "UPDATE tasks SET status = ?, description = ?, updated_at = NOW() WHERE task_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $task_status, $task_note, $task_id);
    $stmt->execute();
    $stmt->close();

    header("Location: task_list.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

        .table-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            border-radius: 10px;
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
            <ul class="navbar-nav mr-auto"></ul>
        </div>
    </nav>

    <div class="container">
        <div class="table-container">
            <h2 class="text-center">Danh Sách Task</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td>
                                    <?php
                                    switch ($row['status']) {
                                        case 'pending':
                                            echo '<span class="badge text-bg-warning">Đang chờ</span>';
                                            break;
                                        case 'in_progress':
                                            echo '<span class="badge text-bg-primary">Đang thực hiện</span>';
                                            break;
                                        case 'completed':
                                            echo '<span class="badge text-bg-success">Hoàn thành</span>';
                                            break;
                                        case 'canceled':
                                            echo '<span class="badge text-bg-danger">Hủy bỏ</span>';
                                            break;
                                        default:
                                            echo '<span class="badge text-bg-secondary">Không xác định</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d-m-Y', strtotime($row['start_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['end_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#processModal" data-task-id="<?php echo $row['task_id']; ?>" data-task-title="<?php echo htmlspecialchars($row['title']); ?>" data-task-description="<?php echo htmlspecialchars($row['description']); ?>" data-task-status="<?php echo $row['status']; ?>">Xử lý</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Bạn chưa có nhiệm vụ, vui lòng đợi phân công.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal xử lý task -->
    <div class="modal fade" id="processModal" tabindex="-1" role="dialog" aria-labelledby="processModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header justify-content-between">
                    <h5 class="modal-title" id="processModalLabel">Xử lý Task</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                        <!-- <span aria-hidden="true">&times;</span> -->
                    </button>
                </div>
                <div class="modal-body">
                    <form id="taskForm" method="POST">
                        <input type="hidden" name="task_id" id="task_id">
                        <input type="hidden" name="action" value="update">
                        <div class="form-group">
                            <label for="task_status">Trạng thái</label>
                            <select class="form-control" id="task_status" name="task_status" required>
                                <option value="pending">Đang chờ</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="canceled">Hủy bỏ</option>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label for="task_note">Ghi chú</label>
                            <textarea class="form-control" id="task_note" name="task_note" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Lưu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        // Gắn dữ liệu vào modal khi click vào nút "Xử lý"
        $('#processModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('task-id');
            var taskTitle = button.data('task-title');
            var taskDescription = button.data('task-description');
            var taskStatus = button.data('task-status');

            var modal = $(this);
            modal.find('.modal-title').text('Xử lý Task: ' + taskTitle);
            modal.find('#task_id').val(taskId);
            modal.find('#task_note').val(taskDescription);
            modal.find('#task_status').val(taskStatus);
        });

        // Gửi form qua AJAX
        // $('#taskForm').on('submit', function (e) {
        //     e.preventDefault();

        //     $.ajax({
        //         url: 'process_task.php',
        //         type: 'POST',
        //         data: $(this).serialize(),
        //         dataType: 'json',
        //         success: function (response) {
        //             console.log(response); // Kiểm tra phản hồi từ server
        //             if (response.status === 'success') {
        //                 alert(response.message);
        //                 $('#processModal').modal('hide');
        //                 location.reload(); // Tải lại trang sau khi cập nhật thành công
        //             } else {
        //                 alert('Lỗi: ' + response.message);
        //             }
        //         },
        //         error: function (xhr, status, error) {
        //             alert('Có lỗi xảy ra: ' + error);
        //             console.log(xhr.responseText); // In ra lỗi chi tiết
        //         }
        //     });
        // });
    </script>
</body>
</html>
