<?php

session_start();
require '../send_email.php';
require '../function.php';
require '../mail_template.php';
require 'src/features.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// get all nhân viên
$sql = "SELECT * FROM accounts 
        WHERE role = 'user'
        ORDER BY fullname" ;
$result = $conn->query($sql);
$employees = [];
if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }

// Thêm task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $assign_id = $_POST['assign_id'];
    $checkboxSendEmail = isset($_POST['check-send-email']) ? true : false;
    
    $sql = "INSERT INTO tasks (title, description, status, assign_id, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", $title, $description, $status, $assign_id, $start_date, $end_date);
    $stmt->execute();

    // checkbox send email
    if ($checkboxSendEmail) {

        $employee = getAccount($conn, $assign_id);

        // Tạo email template
        $emailTemplate = new EmailTemplate();
        $template = $emailTemplate->getTemplate('assign_tasks');

        $template = str_replace('{user_name}', $employee['fullname'], $template);

        // gửi email
        sendEmail($employee['username'], $employee['fullname'], $template, $title);
    }
    

    header("Location: admin_task_management.php");
}

// Cập nhật task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $sql = "UPDATE tasks SET title = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $title, $description, $status, $start_date, $end_date, $task_id);
    $stmt->execute();
    
    header("Location: admin_task_management.php");
}

// Xóa task
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    header("Location: admin_task_management.php");
}

// Lấy danh sách task
$sql = "SELECT * FROM tasks 
    LEFT JOIN accounts ON tasks.assign_id = accounts.id
    ORDER BY tasks.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error); // This will output the error message if the query fails
}

// upload
if(isset($_POST['SubmitButton'])){ //check if form was submitted

$target_dir = 'uploads/';

 // Kiểm tra và tạo thư mục upload nếu chưa tồn tại
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$target_file = $target_dir . basename($_FILES["filepath"]["name"]);

// Dòng này lấy phần mở rộng (extension) của file, ví dụ như jpg, png, xlsx,... từ đường dẫn $target_file.
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

move_uploaded_file($_FILES["filepath"]["tmp_name"], $target_file);

// Đọc file
// Đọc file Excel
        try {
            // Mở file Excel
            $spreadsheet = IOFactory::load($target_file);

            // Lấy dữ liệu từ sheet đầu tiên
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // INSERT INTO
            $indexStart = 1;
            $i = 0;
            foreach ($sheetData as $index => $row) {
                if ($index >= $indexStart) {

                    // check format date
                    if (!validateDate($row[5]) || !validateDate($row[6])) {
                        $date1 = new DateTime($row[5]);
                        $date2 = new DateTime($row[6]);
                        $row[5] = $date1->format("Y-m-d");
                        $row[6] = $date2->format("Y-m-d");
                    }

                    $sql = "INSERT INTO tasks values('', ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
                    $stmt->execute();
                    
                }
            }
        } catch (Exception $e) {
            echo "Lỗi khi đọc file Excel: " . $e->getMessage();
        }

        header("Location: admin_task_management.php");

}  

// Xử lý phân trang
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Số lượng bản ghi trên mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Trang hiện tại
$offset = ($page - 1) * $limit; // Vị trí bắt đầu của bản ghi trên trang hiện tại

// Truy vấn tổng số lượng bản ghi khi tìm kiếm
    $total_sql = "SELECT COUNT(*) as total FROM tasks";
    $stmt = $conn->prepare($total_sql);
    $stmt->execute();

    $total_result = $stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM tasks LEFT JOIN accounts ON tasks.assign_id = accounts.id  ORDER BY task_id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Task</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="assets/css/manage_data.css">
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
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2>Quản lý Task</h2>

        <div class="row mb-3 p-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addTaskModal">Thêm Task</button>

            <button class="btn btn-secondary buttons-collection btn-outline-secondary waves-effect waves-light ml-3" 
                type="button" 
                id="uploadButton" 
                data-toggle="modal" 
                data-target="#uploadModal">
                <span>
                    <i class="fa-solid fa-upload"></i>
                    <span class="d-none d-sm-inline-block">Upload</span>
                </span>
            </button>
                
            <div class="dropdown">
                <button class="btn btn-secondary ml-3 buttons-collection btn-outline-secondary waves-effect waves-light dropdown-toggle" 
                    type="button" 
                    id="exportButtonDropdown" 
                    data-toggle="dropdown" 
                    aria-haspopup="true" 
                    aria-expanded="false">
                    <span>
                        <i class="fa-solid fa-file-export"></i>
                        <span class="d-none d-sm-inline-block">Export</span>
                    </span>
                </button>
                <div id="export-menu" class="dropdown-menu mt-2" aria-labelledby="exportButtonDropdown" style="min-width: 8rem;">
                    <a id="export-to-excel" class="dropdown-item" href="#">
                        <i class="fa-regular fa-file-excel mr-2"></i>
                        Excel
                    </a>
                    <a id="export-to-pdf" class="dropdown-item" href="#">
                        <i class="fa-regular fa-file-pdf mr-2"></i>
                        Pdf
                    </a>
                </div>
                <form action="src/generate_file.php" method="GET" id="export-form">
                    <input type="hidden" name="exportType" id="hidden-type" value="">
                </form>
            </div>

        </div>
        
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>TT</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Nhân viên</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <?php
                                    switch ($row['status']) {
                                        case 'pending':
                                            echo '<span class="badge badge-warning">Đang chờ</span>';
                                            break;
                                        case 'in_progress':
                                            echo '<span class="badge badge-primary">Đang thực hiện</span>';
                                            break;
                                        case 'completed':
                                            echo '<span class="badge badge-success">Hoàn thành</span>';
                                            break;
                                        case 'canceled':
                                            echo '<span class="badge badge-danger">Hủy bỏ</span>';
                                            break;
                                        default:
                                            echo '<span class="badge badge-secondary">Không xác định</span>';
                                            break;
                                    }
                                    ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['start_date'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['end_date'])); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" 
                                data-toggle="modal" data-target="#editTaskModal" 
                                data-task-id="<?php echo $row['task_id']; ?>" 
                                data-title="<?php echo htmlspecialchars($row['title']); ?>" 
                                data-description="<?php echo htmlspecialchars($row['description']); ?>" 
                                data-status="<?php echo $row['status']; ?>" 
                                data-assign-id="<?php echo $row['assign_id'] ?>"
                                data-start-date="<?php echo $row['start_date']; ?>" 
                                data-end-date="<?php echo $row['end_date']; ?>">Sửa</button>
                                <a href="admin_task_management.php?delete_id=<?php echo $row['task_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Phân trang -->
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="admin_task_management.php?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>">Trước</a></li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="admin_task_management.php?page=1&limit=' . $limit . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="admin_task_management.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="admin_task_management.php?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>"><?php echo $total_pages; ?></a></li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="admin_task_management.php?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>">Sau</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal Thêm Task -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Thêm Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="title">Nhân viên</label>
                            <select class="form-control" name="assign_id">
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee["id"] ?>"><?php echo $employee["fullname"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Mô tả</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select class="form-control" name="status">
                                <option value="pending">Đang chờ</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="canceled">Hủy bỏ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Ngày bắt đầu</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Ngày kết thúc</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="check-send-email" name="check-send-email">
                            <label class="form-check-label" for="check-send-email">Gửi Email</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Task -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Sửa Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="task_id" id="edit_task_id">
                        <div class="form-group">
                            <label for="title">Nhân viên</label>
                            <select class="form-control" name="assign_id" id="edit_assign_id">
                                <option value=""></option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee["id"] ?>"><?php echo $employee["fullname"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Mô tả</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="pending">Đang chờ</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="canceled">Hủy bỏ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Ngày bắt đầu</label>
                            <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Ngày kết thúc</label>
                            <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal upload file -->
     <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">
                        <i class="mr-2 fa-solid fa-upload"></i>
                        Upload
                        <small>(.xlsx .csv)</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="file" name="filepath" id="filepath"/>
                    </div>
                    <div class="modal-footer">
                        <input class="btn btn-primary" type="submit" name="SubmitButton"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        // Gắn dữ liệu vào modal khi click vào nút "Sửa"
        $('#editTaskModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('task-id');
            var title = button.data('title');
            var description = button.data('description');
            var status = button.data('status');
            var assignId = button.data('assign-id');
            var startDate = button.data('start-date');
            var endDate = button.data('end-date');

            var modal = $(this);
            modal.find('#edit_task_id').val(taskId);
            modal.find('#edit_title').val(title);
            modal.find('#edit_description').val(description);
            modal.find('#edit_status').val(status);
            modal.find('#edit_assign_id').val(assignId);
            modal.find('#edit_start_date').val(startDate);
            modal.find('#edit_end_date').val(endDate);
        });
    </script>

    <!-- export -->
    <script type="text/javascript">
        $(document).ready(function() {
            jQuery('#export-menu a').bind("click", function() {
                
                var target = $(this).attr('id');
                console.log(target)
                switch (target) {
                    case 'export-to-excel':
                        $('#hidden-type').val(target);
                        $('#export-form').submit();
                        break;
                }
            })
        })
    </script>

</body>
</html>
