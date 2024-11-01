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

// Lấy ID người dùng và tên đầy đủ từ session
$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Xử lý submit từ popup
if (isset($_POST['submit'])) {
    $data_id = intval($_POST['data_id']);
    $contact_status = $_POST['contact_status'];
    $customer_evaluation = $_POST['customer_evaluation'];

    // Insert vào bảng customers, thêm trường data_source để lưu thông tin nguồn
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, phone_number, created_by, created_at, contact_status, customer_evaluation, data_source) 
                            SELECT name, phone, ?, NOW(), ?, ?, (SELECT source_name FROM data_sources WHERE id = data.sources_id) FROM data WHERE data_id = ?");
    $stmt->bind_param("sssi", $fullname, $contact_status, $customer_evaluation, $data_id);
    if ($stmt->execute()) {
        // Cập nhật trạng thái trong bảng data thành "called"
        $stmt = $conn->prepare("UPDATE data SET status = 'called' WHERE data_id = ?");
        $stmt->bind_param("i", $data_id);
        $stmt->execute();
        header("Location: data_manager.php?msg=updated");
        exit();
    } else {
        echo "Lỗi khi thêm vào bảng customers: " . $conn->error;
    }
}

// Lấy dữ liệu từ bảng contact_statuses và customer_evaluations
$contact_statuses_query = "SELECT id, status_name FROM contact_statuses";
$contact_statuses = $conn->query($contact_statuses_query);

$customer_evaluations_query = "SELECT id, evaluation_name FROM customer_evaluations";
$customer_evaluations = $conn->query($customer_evaluations_query);

// Lấy nguồn data từ bảng data_sources
$sources_query = "SELECT id, source_name FROM data_sources";
$sources = $conn->query($sources_query);

// Xử lý phân trang
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20; // Số lượng bản ghi trên mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Trang hiện tại
$offset = ($page - 1) * $limit; // Vị trí bắt đầu của bản ghi trên trang hiện tại

// Xử lý tìm kiếm theo số điện thoại
$search_phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

if ($search_phone) {
    // Truy vấn tổng số lượng bản ghi khi tìm kiếm
    $total_sql = "SELECT COUNT(*) as total FROM data WHERE phone LIKE ? AND status = 'pending' AND user_id = ?";
    $search_phone_wildcard = '%' . $search_phone . '%';
    $stmt = $conn->prepare($total_sql);
    $stmt->bind_param("si", $search_phone_wildcard, $user_id);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // Truy vấn khi có số điện thoại tìm kiếm
    $sql = "SELECT * FROM data WHERE phone LIKE ? AND status = 'pending' AND user_id = ? ORDER BY data_id DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siii", $search_phone_wildcard, $user_id, $offset, $limit);
} else {
    // Truy vấn tổng số lượng bản ghi chỉ với trạng thái "pending" và user_id
    $total_sql = "SELECT COUNT(*) as total FROM data WHERE status = 'pending' AND user_id = ?";
    $stmt = $conn->prepare($total_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // Truy vấn dữ liệu khách hàng với trạng thái "pending" và user_id
    $sql = "SELECT d.*, ds.source_name FROM data d LEFT JOIN data_sources ds ON d.sources_id = ds.id WHERE d.status = 'pending' AND d.user_id = ? ORDER BY d.data_id DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $offset, $limit);
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
    <title>Quản lý dữ liệu của <?php echo htmlspecialchars($fullname); ?></title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/user_data.css">
</head>

<body>
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
                <input class="form-control mr-sm-2" type="search" placeholder="Tìm SĐT" name="phone" aria-label="Search" value="<?php echo htmlspecialchars($search_phone); ?>">
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
        <h2>Quản lý dữ liệu của <?php echo htmlspecialchars($fullname); ?></h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">Dữ liệu đã được cập nhật thành công!</div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>TT</th>
                    <th>Tên</th>
                    <th>Số điện thoại</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th>Nguồn dữ liệu</th>
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
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['source_name']); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm handle-btn" data-id="<?php echo $row['data_id']; ?>" data-toggle="modal" data-target="#handleModal">Xử lý</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Không có dữ liệu nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Hiển thị phân trang -->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="data_manager.php?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>">Trước</a></li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="data_manager.php?page=1&limit=' . $limit . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="data_manager.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="data_manager.php?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="data_manager.php?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>">Sau</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Modal Xử lý -->
    <div class="modal fade" id="handleModal" tabindex="-1" role="dialog" aria-labelledby="handleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="handleModalLabel">Xử lý khách hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="data_id" name="data_id" value="">
                        <div class="form-group">
                            <label for="contact_status">Trạng thái cuộc gọi</label>
                            <select class="form-control" id="contact_status" name="contact_status" required>
                                <?php while ($status = $contact_statuses->fetch_assoc()): ?>
                                    <option value="<?php echo $status['status_name']; ?>"><?php echo $status['status_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_evaluation">Đánh giá khách hàng</label>
                            <select class="form-control" id="customer_evaluation" name="customer_evaluation" required>
                                <?php while ($evaluation = $customer_evaluations->fetch_assoc()): ?>
                                    <option value="<?php echo $evaluation['evaluation_name']; ?>"><?php echo $evaluation['evaluation_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" name="submit" class="btn btn-primary">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.handle-btn').on('click', function() {
                var data_id = $(this).data('id');
                $('#data_id').val(data_id);
            });
        });
    </script>
</body>

</html>