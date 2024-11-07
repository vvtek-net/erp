<?php
session_start();
include 'db_connection.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['current_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$query = "SELECT *, sale_orders.id as soId, customers.id as cusId
        FROM sale_orders
        LEFT JOIN services ON sale_orders.service_type = services.service_id
        LEFT JOIN customers ON sale_orders.customer_id = customers.id
        LEFT JOIN addons ON sale_orders.addon_type = addons.addon_id
        ORDER BY sale_orders.created_at DESC;";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh SQL cho merchant: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

$query = "SELECT * FROM services";
$result = $conn->query($query);
$services = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

$query = "SELECT * FROM addons";
$result = $conn->query($query);
$addons = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $addons[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editForm'])) {
    var_dump('OKE');
    exit();
}

$conn->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Sale Orders Management</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="assets/css/sale.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>

<body>
    <?php include('topmenu.php'); ?>

    <?php include 'toasts.php' ?>

    <div class="container mt-5">
        <h1 class="fw-bolder">Sale Orders Management</h1>
        <?php if ($_SESSION['role'] !== 'merchant') { ?> 
        <a class="btn btn-success mb-3" href="sale_order.php">Create New Sale Order</a>
        <?php } ?>
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Tên KH</th>
                    <th scope="col">Dịch Vụ</th>
                    <th scope="col">Tiện ích bổ sung</th>
                    <th scope="col">Duration (month)</th>
                    <th scope="col">Trạng Thái</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $index => $ticket): ?>
                <tr>
                    <th scope="row"><?php echo ++$index ?></th>
                    <td><?php echo $ticket['customer_name']; ?></td>
                    <td><?php echo $ticket['service_name'] ?></td>
                    <td><?php echo $ticket['addon_name'] ?></td>
                    <td><?php echo $ticket['service_duration'] ?></td>
                    <td>
                        <span class="badge rounded-pill 
                        <?php if ($ticket['status'] == 'confirmed') echo 'bg-primary'; else echo 'bg-warning'; ?>">
                        <?php echo $ticket['status']; 
                        ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-secondary" href="#" role="button" 
                           data-toggle="modal" data-target="#popupContractModal" data-sale-order-id="<?php echo ($ticket['soId']); ?>"
                           data-toggle="modal" data-target="#popupContractModal" data-customer-id="<?php echo ($ticket['cusId']); ?>"
                           data-toggle="modal" data-target="#popupContractModal" data-customer-name="<?php echo ($ticket['customer_name']); ?>"
                           data-toggle="modal" data-target="#popupContractModal" data-service-name="<?php echo ($ticket['service_id']); ?>"
                           data-toggle="modal" data-target="#popupContractModal" data-addon-name="<?php echo ($ticket['addon_id']); ?>"
                           data-toggle="modal" data-target="#popupContractModal" data-service-duration="<?php echo ($ticket['service_duration']); ?>"
                           >Xử Lý</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Popup Contract Modal -->
    <div class="modal fade" id="popupContractModal" tabindex="-1" role="dialog" aria-labelledby="popupContractModalLabel" aria-hidden="true" style="top: 150px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xử Lý</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="padding: 0; ">
                    <!-- Form ẩn để gửi dữ liệu khi in hợp đồng -->
                    <form id="contractForm" method="GET" action="create_contract.php" style="box-shadow: none;">
                        <!-- Input ẩn để lưu dữ liệu của hàng -->
                        <input type="hidden" name="sale_order_id" id="contract_sale_order_id">
                        <input type="hidden" name="customer_id" id="contract_customer_id">
                        <input type="hidden" name="customer_name" id="contract_customer_name">
                        <input type="hidden" name="contract_service_id" id="contract_service_name">
                        <input type="hidden" name="contract_addon_id" id="contract_addon_name">
                        <input type="hidden" name="service_duration" id="contract_service_duration">
                        
                        <!-- Nút "In Hợp Đồng" để submit form -->
                        <button id="printContractButton" type="submit" class="btn btn-primary mb-2">Tạo Hợp Đồng</button>
                    </form>
                    
                </div>
                <div class="modal-footer">
                    <button id="editInfoButton" type="button" class="btn btn-warning" data-dismiss="modal" data-toggle="modal" data-target="#editModal">Sửa Thông Tin</button>
                    <button id="deleteButton" type="button" class="btn btn-danger">Xóa</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Thông Tin -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Chỉnh sửa Thông Tin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 0px">
                    <!-- Nội dung của form chỉnh sửa thông tin -->
                    <form action="" method="POST" id="editForm">
                        <!-- Thêm các input hoặc select để người dùng chỉnh sửa -->
                        <input type="text" name="editCustomerID" id="editCustomerID" hidden>
                        <input type="text" name="editSaleOrderID" id="editSaleOrderID" hidden>
                        <div class="form-group">
                            <label for="editCustomerName">Tên Khách Hàng</label>
                            <input type="text" class="form-control" id="editCustomerName" name="editCustomerName">
                        </div>
                        <div class="form-group">
                            <label for="editServiceId">Dịch Vụ</label>
                            <select class="form-control" id="editServiceId" name="editServiceId">
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['service_id'] ?>"><?php echo $service['service_name'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editAddonId">Tiện Ích Bổ Sung</label>
                            <select class="form-control" id="editAddonId" name="editAddonId">
                                <?php foreach ($addons as $addon): ?>
                                    <option value="<?php echo $addon['addon_id'] ?>"><?php echo $addon['addon_name'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editServiceDuration">Thời Hạn (tháng)</label>
                            <input type="text" class="form-control" id="editServiceDuration" name="editServiceDuration">
                        </div>
                        <!-- <div class="form-group">
                            <label for="editStatus">Trạng Thái</label>
                            <input type="text" class="form-control" id="editStatus" name="editStatus">
                        </div> -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                            <button id="editBtnSubmit" name="editBtnSubmit" type="button" class="btn btn-primary">Lưu</button>
                        </div>
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
    $(document).ready(function() {

        // Hiển thị modal với dữ liệu của hàng
        $('#popupContractModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);

            // Lấy dữ liệu từ các thuộc tính data-* của nút "Xử Lý"
            var saleOrderID = button.data('sale-order-id');
            var customerID = button.data('customer-id');
            var customerName = button.data('customer-name');
            var serviceName = button.data('service-name');
            var addonName = button.data('addon-name');
            var serviceDuration = button.data('service-duration');

            // Gán dữ liệu vào form ẩn trong modal
            $('#contract_sale_order_id').val(saleOrderID);
            $('#contract_customer_id').val(customerID);
            $('#contract_customer_name').val(customerName);
            $('#contract_service_name').val(serviceName);
            $('#contract_addon_name').val(addonName);
            $('#contract_service_duration').val(serviceDuration);

            // Gán dữ liệu vào các nút trong modal để xử lý khi nhấn
            $('#printContractButton').data('sale-order-id', saleOrderID);

            // Gán dữ liệu vào modal edit
            $('#editInfoButton').data('sale-order-id', saleOrderID);
            $('#editInfoButton').data('customer-id', customerID);
            $('#editInfoButton').data('customer-name', customerName);
            $('#editInfoButton').data('service-id', serviceName);
            $('#editInfoButton').data('addon-id', addonName);
            $('#editInfoButton').data('service-duration', serviceDuration);

            // Gán dữ liệu vào modal delete
            $('#deleteButton').data('sale-order-id', saleOrderID);
        });

        // Xử lý nút "In Hợp Đồng" khi nhấn vào
        $('#printContractButton').click(function() {
            var saleOrderID = $(this).data('sale-order-id');
            window.location.href = "create_contract.php?sale_order_id=" + saleOrderID;
        });

        // Xử lý nút "Sửa Thông Tin" - mở modal chỉnh sửa
        $('#editModal').on('show.bs.modal', function (event) {

            var button = $(event.relatedTarget)

            // Xử lý để tải dữ liệu vào modal
            var saleOrderID = button.data('sale-order-id');
            var customerID = button.data('customer-id');
            var customerName = button.data('customer-name');
            var serviceID = button.data('service-id');
            var addonID = button.data('addon-id');
            var serviceDuration = button.data('service-duration');

            var modal = $(this)
            modal.find('.modal-body #editSaleOrderID').val(saleOrderID)
            modal.find('.modal-body #editCustomerID').val(customerID)
            modal.find('.modal-body #editCustomerName').val(customerName)
            modal.find('.modal-body #editServiceId').val(serviceID)
            modal.find('.modal-body #editAddonId').val(addonID)
            modal.find('.modal-body #editServiceDuration').val(serviceDuration)
            
            // Xử lý sự kiện submit khi người dùng nhấn nút "Submit" trong modal
            $('#editBtnSubmit').click(function(event) {
                event.preventDefault();
                $('#editForm').submit(); // Gửi form nếu cần
            });

        });

        // Xử lý nút "Xóa" - xóa đơn hàng
        $('#deleteButton').click(function() {
            var saleOrderID = $(this).data('sale-order-id');
            if (confirm("Bạn có chắc chắn muốn xóa đơn hàng này không?")) {
                $.ajax({
                    url: 'delete_order.php',
                    type: 'POST',
                    data: { sale_order_id: saleOrderID },
                    success: function(response) {
                        alert(response); // Hiển thị thông báo
                        location.reload(); // Tải lại trang sau khi xóa
                    }
                });
            }
        });
    });

    </script>
</body>
</html>
