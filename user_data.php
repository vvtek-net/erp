<?php
// Bắt đầu session
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối với cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy tên đầy đủ của user từ session
$fullname = $_SESSION['fullname'];

// Lấy dữ liệu cho dropdown nguồn data
$data_sources = $conn->query("SELECT source_name FROM data_sources");

// Lấy dữ liệu cho dropdown trạng thái tiếp xúc
$contact_statuses = $conn->query("SELECT status_name FROM contact_statuses");

// Lấy dữ liệu cho dropdown đánh giá khách hàng
$customer_evaluations = $conn->query("SELECT evaluation_name FROM customer_evaluations");

// Tạo các biến lưu lựa chọn dropdown
$data_sources_options = '';
while ($row = $data_sources->fetch_assoc()) {
    $data_sources_options .= '<option value="' . $row['source_name'] . '">' . htmlspecialchars($row['source_name']) . '</option>';
}

$contact_statuses_options = '';
while ($row = $contact_statuses->fetch_assoc()) {
    $contact_statuses_options .= '<option value="' . $row['status_name'] . '">' . htmlspecialchars($row['status_name']) . '</option>';
}

$customer_evaluations_options = '';
while ($row = $customer_evaluations->fetch_assoc()) {
    $customer_evaluations_options .= '<option value="' . $row['evaluation_name'] . '">' . htmlspecialchars($row['evaluation_name']) . '</option>';
}

// Xử lý khi người dùng nhấn nút xóa
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM customers WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $delete_id, $fullname);
    if ($stmt->execute()) {
        header("Location: user_data.php?msg=deleted");
        exit();
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

// Xử lý phân trang
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10; // Số lượng bản ghi trên mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Trang hiện tại
$offset = ($page - 1) * $limit; // Vị trí bắt đầu của bản ghi trên trang hiện tại

// Xử lý tìm kiếm và lọc
$search_phone = isset($_GET['phone_number']) ? trim($_GET['phone_number']) : '';
$data_source_filter = isset($_GET['data_source_filter']) ? trim($_GET['data_source_filter']) : '';
$contact_status_filter = isset($_GET['contact_status_filter']) ? trim($_GET['contact_status_filter']) : '';
$customer_evaluation_filter = isset($_GET['customer_evaluation_filter']) ? trim($_GET['customer_evaluation_filter']) : '';

if ($search_phone) {
    // Truy vấn tổng số lượng bản ghi khi tìm kiếm
    $total_sql = "SELECT COUNT(*) as total FROM customers WHERE phone_number LIKE ?";
    $search_phone_wildcard = '%' . $search_phone . '%';
    $stmt = $conn->prepare($total_sql);
    $stmt->bind_param("s", $search_phone_wildcard);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // Truy vấn khi có số điện thoại tìm kiếm
    $sql = "SELECT * FROM customers WHERE phone_number LIKE ? ORDER BY id DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $search_phone_wildcard, $offset, $limit);
} else {
    // Truy vấn tổng số lượng bản ghi
    $total_sql = "SELECT COUNT(*) as total FROM customers WHERE created_by = ?";
    if (!empty($data_source_filter)) {
        $total_sql .= " AND data_source = ?";
    }
    if (!empty($contact_status_filter)) {
        $total_sql .= " AND contact_status = ?";
    }
    if (!empty($customer_evaluation_filter)) {
        $total_sql .= " AND customer_evaluation = ?";
    }

    $stmt = $conn->prepare($total_sql);
    $bind_params = [$fullname];
    if (!empty($data_source_filter)) {
        $bind_params[] = $data_source_filter;
    }
    if (!empty($contact_status_filter)) {
        $bind_params[] = $contact_status_filter;
    }
    if (!empty($customer_evaluation_filter)) {
        $bind_params[] = $customer_evaluation_filter;
    }
    $stmt->bind_param(str_repeat('s', count($bind_params)), ...$bind_params);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // Truy vấn dữ liệu khách hàng theo user đã đăng nhập với giới hạn cho phân trang
    $sql = "SELECT * FROM customers WHERE created_by = ?";
    if (!empty($data_source_filter)) {
        $sql .= " AND data_source = ?";
    }
    if (!empty($contact_status_filter)) {
        $sql .= " AND contact_status = ?";
    }
    if (!empty($customer_evaluation_filter)) {
        $sql .= " AND customer_evaluation = ?";
    }
    $sql .= " ORDER BY id DESC LIMIT ?, ?";

    $stmt = $conn->prepare($sql);
    $bind_params[] = $offset;
    $bind_params[] = $limit;
    $stmt->bind_param(str_repeat('s', count($bind_params)), ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dữ liệu khách hàng của <?php echo htmlspecialchars($fullname); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/user_data.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <style>
        .pe-auto {
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!--  toast -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast d-flex toast--success" style="
                  animation: 0.3s ease 0s 1 normal none running slideInLeft,
                  1s linear 5s 1 normal forwards running fadeOut;">
                <div class="toast__icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="toast__body">
                    <h3 class="toast__title mb-0">Khách hàng đã được thêm thành công!</h3>
                </div>
                <div class="toast__close" onclick="this.parentElement.style.display='none';">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast d-flex toast--error" style="
                  animation: 0.3s ease 0s 1 normal none running slideInLeft,
                  1s linear 5s 1 normal forwards running fadeOut;">
                <div class="toast__icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="toast__body">
                    <h3 class="toast__title mb-0"><?php echo htmlspecialchars($_GET['message']); ?></h3>
                </div>
                <div class="toast__close" onclick="this.parentElement.style.display='none';">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
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
            <form class="form-inline my-2 my-lg-0" method="get" action="">
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone_number" aria-label="Search" value="<?php echo htmlspecialchars($search_phone); ?>">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://img.icons8.com/ios-glyphs/30/ffffff/user.png" alt="User Icon" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="user_data.php">Quản lý data</a>
                        <a class="dropdown-item" href="data_manager.php">Telesale</a>
                        <a class="dropdown-item" href="sale_order.php">Sale Orders</a>
                        <a class="dropdown-item" href="opportunities.php">Khách hàng cơ hội</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Dữ liệu khách hàng của <?php echo htmlspecialchars($fullname); ?></h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">Khách hàng đã được cập nhật thành công!</div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Khách hàng đã được xóa thành công!</div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-3">
            <a href="create.php" class="btn btn-success">Thêm khách hàng</a>
            <form method="get" action="" class="form-inline">
                <label for="limit" class="mr-2">Hiển thị:</label>
                <select id="limit" name="limit" class="form-control" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                    <option value="500" <?php if ($limit == 500) echo 'selected'; ?>>500</option>
                </select>
            </form>
            <!-- Form bộ lọc -->
            <form method="get" action="" class="form-inline">
                <select id="data_source_filter" name="data_source_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">Nguồn data</option>
                    <?php echo $data_sources_options; ?>
                </select>

                <select id="contact_status_filter" name="contact_status_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">Trạng thái tiếp xúc...</option>
                    <?php echo $contact_statuses_options; ?>
                </select>

                <select id="customer_evaluation_filter" name="customer_evaluation_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">Đánh giá khách hàng...</option>
                    <?php echo $customer_evaluations_options; ?>
                </select>
            </form>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>SĐT</th>
                    <th>Nguồn data</th>
                    <th>Trạng thái</th>
                    <th>Đánh giá</th>
                    <th>Ngày nhập</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php
                    $i = 1;
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $i ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['data_source']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_evaluation']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td class="action-btns">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Xử lý
                                    </button>
                                    <div class="mt-2 dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item pe-auto" data-target="#confirmCreate" data-customer-id="<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" data-toggle="modal">Tạo KH cơ hội</a>
                                        <button type="button" class="dropdown-item pe-auto" class="btn btn-warning btn-sm edit-btn"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['customer_name']); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone_number']); ?>"
                                            data-source="<?php echo htmlspecialchars($row['data_source']); ?>"
                                            data-status="<?php echo htmlspecialchars($row['contact_status']); ?>"
                                            data-evaluation="<?php echo htmlspecialchars($row['customer_evaluation']); ?>"
                                            data-toggle="modal" data-target="#editCustomerModal">Sửa</button>
                                        <a class="dropdown-item pe-auto" href="user_data.php?delete_id=<?php echo $row['id']; ?>&data_source_filter=<?php echo $data_source_filter; ?>&contact_status_filter=<?php echo $contact_status_filter; ?>&customer_evaluation_filter=<?php echo $customer_evaluation_filter; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" class="btn btn-danger btn-sm">Xóa</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                        $i++;
                    endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Không có dữ liệu nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Hiển thị phân trang -->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="user_data.php?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&data_source_filter=<?php echo $data_source_filter; ?>&contact_status_filter=<?php echo $contact_status_filter; ?>&customer_evaluation_filter=<?php echo $customer_evaluation_filter; ?>">Trước</a></li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="user_data.php?page=1&limit=' . $limit . '&data_source_filter=' . $data_source_filter . '&contact_status_filter=' . $contact_status_filter . '&customer_evaluation_filter=' . $customer_evaluation_filter . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="user_data.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&data_source_filter=<?php echo $data_source_filter; ?>&contact_status_filter=<?php echo $contact_status_filter; ?>&customer_evaluation_filter=<?php echo $customer_evaluation_filter; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="user_data.php?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>&data_source_filter=<?php echo $data_source_filter; ?>&contact_status_filter=<?php echo $contact_status_filter; ?>&customer_evaluation_filter=<?php echo $customer_evaluation_filter; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="user_data.php?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&data_source_filter=<?php echo $data_source_filter; ?>&contact_status_filter=<?php echo $contact_status_filter; ?>&customer_evaluation_filter=<?php echo $customer_evaluation_filter; ?>">Sau</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Modal Sửa -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">Sửa Thông Tin Khách Hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCustomerForm" method="GET" action="update_customer.php">

                        <input type="hidden" id="editCustomerId" name="customer_id" value="">
                        <div class="form-group">
                            <label for="customer_name">Tên khách hàng</label>
                            <input type="text" class="form-control" id="editCustomerName" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Số điện thoại</label>
                            <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="data_source">Nguồn data</label>
                            <select class="form-control" id="editDataSource" name="data_source" required>
                                <option value="">Chọn nguồn data...</option>
                                <?php echo $data_sources_options; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contact_status">Trạng thái tiếp xúc</label>
                            <select class="form-control" id="editContactStatus" name="contact_status" required>
                                <option value="">Chọn trạng thái tiếp xúc...</option>
                                <?php echo $contact_statuses_options; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_evaluation">Đánh giá khách hàng</label>
                            <select class="form-control" id="editCustomerEvaluation" name="customer_evaluation" required>
                                <option value="">Chọn đánh giá...</option>
                                <?php echo $customer_evaluations_options; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tạo KH tiềm năng -->
    <div class="modal fade" id="confirmCreate" tabindex="-1" role="dialog" aria-labelledby="confirmCreate" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Xác Nhận Tạo Khách Hàng Tiềm Năng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn tạo khách hàng tiềm năng?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="btn-create" type="submit" class="btn btn-primary">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var phone = $(this).data('phone');
                var source = $(this).data('source');
                var status = $(this).data('status');
                var evaluation = $(this).data('evaluation');

                $('#editCustomerId').val(id);
                $('#editCustomerName').val(name);
                $('#editPhoneNumber').val(phone);
                $('#editDataSource').val(source);
                $('#editContactStatus').val(status);
                $('#editCustomerEvaluation').val(evaluation);
            });

            $('#editCustomerForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'GET',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editCustomerModal .modal-body').prepend(
                                '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                                '</div>'
                            );
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            $('#editCustomerModal .modal-body').prepend(
                                '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                                '</div>'
                            );
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Có lỗi xảy ra: ' + textStatus);
                    }
                });
            });
        
            $('#confirmCreate').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var customerId = button.data('customer-id');

                $('#btn-create').on('click', function() {

                    // Tạo một biểu mẫu ẩn
                    var form = $('<form action="process_opportunity.php" method="GET">' +
                        '<input type="hidden" name="customer_id" value="' + customerId + '">' +
                        '</form>');

                    // Thêm biểu mẫu vào body
                    $('body').append(form);

                    // Gửi biểu mẫu
                    form.submit();

                });
            })
        });
    </script>
</body>

</html>