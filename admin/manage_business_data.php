<?php
session_start();

// Kết nối tới SQL Server (mainData)
$serverName = "DESKTOP-FABUPOB\\MSSQLSERVERS";
$connectionInfo = array("Database" => "salepos", "CharacterSet" => "UTF-8");
$connSQLServer = sqlsrv_connect($serverName, $connectionInfo);

if (!$connSQLServer) {
    echo "failed to connect to SQL Server database.<br />";
    die(print_r(sqlsrv_errors(), true));
}

// Kết nối tới MySQL (accounts và data)
$connMySQL = new mysqli("localhost", "root", "", "telesale");

if ($connMySQL->connect_error) {
    die("failed to connect to MySQL database: " . $connMySQL->connect_error);
}

// Thiết lập phân trang
$limit = 10; // số lượng bản ghi mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy danh sách dữ liệu từ bảng mainData với giới hạn phân trang
// Đẩy các bản ghi chưa được phân công (users IS NULL) lên trên cùng
$sqlSelect = "SELECT * FROM mainData ORDER BY CASE WHEN users IS NULL THEN 0 ELSE 1 END, id OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
$params = array($offset, $limit);
$stmt = sqlsrv_query($connSQLServer, $sqlSelect, $params);

// Tính tổng số trang
$sqlCount = "SELECT COUNT(*) AS total FROM mainData";
$stmtCount = sqlsrv_query($connSQLServer, $sqlCount);
$totalRows = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC)['total'];
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách nhân viên (role = user) từ bảng accounts
$sqlUsers = "SELECT id, fullname FROM accounts WHERE role = 'user'";
$resultUsers = $connMySQL->query($sqlUsers);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dữ liệu doanh nghiệp</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_accounts.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include('sidebar.php'); ?>

    <div class="container mt-5">
        <h2 class="mb-4">Danh sách doanh nghiệp</h2>

        <!-- Nút xử lý duy nhất -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#assignModal">Xử lý</button>

        <div class="table-responsive">
            <form id="dataForm">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>STT</th>
                            <th>Mã số thuế</th>
                            <th>Tên công ty</th>
                            <th>Số điện thoại</th>
                            <th>Người đại diện</th>
                            <th>Ngày thành lập</th>
                            <th>Địa chỉ</th>
                            <th>Ngày</th>
                            <th>Nhân viên được phân công</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $stt = $offset + 1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td><input type="checkbox" name="dataIds[]" value="<?php echo $row['id']; ?>"></td>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo htmlspecialchars($row['mst']); ?></td>
                                <td><?php echo htmlspecialchars($row['tenCongTy']); ?></td>
                                <td><?php echo htmlspecialchars($row['soDienThoai']); ?></td>
                                <td><?php echo htmlspecialchars($row['nguoiDaiDien']); ?></td>
                                <td><?php echo htmlspecialchars($row['ngayThanhLap']); ?></td>
                                <td><?php echo htmlspecialchars($row['diaChi']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['users'] ?? 'Chưa phân công'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                    <?php if ($page > 3): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>

                <?php if ($page < $totalPages - 2): ?>
                    <?php if ($page < $totalPages - 3): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Popup phân công -->
        <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalLabel">Phân công xử lý dữ liệu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="GET" action="process_data.php">
                        <div class="modal-body">
                            <input type="hidden" name="selectedDataIds" id="selectedDataIds">
                            <div class="form-group">
                                <label for="userSelect">Chọn nhân viên</label>
                                <select class="form-control" id="userSelect" name="user_id" required>
                                    <option value="">-- Chọn nhân viên --</option>
                                    <?php while ($user = $resultUsers->fetch_assoc()): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['fullname']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Lưu</button>
                            <button type="button" class="btn btn-danger" onclick="deleteData()">Xóa</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Chọn tất cả các checkbox
            $("#selectAll").click(function() {
                $("input[name='dataIds[]']").prop('checked', this.checked);
            });

            // Khi mở popup, lấy danh sách các ID đã chọn
            $("#assignModal").on('show.bs.modal', function () {
                var selectedIds = [];
                $("input[name='dataIds[]']:checked").each(function() {
                    selectedIds.push($(this).val());
                });
                $("#selectedDataIds").val(selectedIds.join(","));
            });

            // Hàm xóa dữ liệu
            function deleteData() {
                var selectedIds = $("#selectedDataIds").val();
                if (selectedIds && confirm("Bạn có chắc chắn muốn xóa các dữ liệu đã chọn không?")) {
                    window.location.href = "delete_data.php?selectedDataIds=" + selectedIds;
                }
            }
        </script>

    </div>

</body>
</html>

<?php
// Đóng kết nối
sqlsrv_close($connSQLServer);
$connMySQL->close();
?>
