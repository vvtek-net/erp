<?php

session_start();
include 'db_connection.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    die('Bạn phải đăng nhập trước khi tạo Sale Order.');
}

$fullname = $_SESSION['fullname'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// đẩy KH cơ hội từ opportunities sang
$customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
$customer_current = $customer_id ? getCustomer($conn, $customer_id) : null;

// Lấy toàn bộ danh sách khách hàng
// $query = "
//     SELECT c.id, c.customer_name, c.phone_number
//     FROM customers c
//     INNER JOIN opportunities o ON c.id = o.customer_id
//     WHERE o.account_id = '$user_id'";
// $result = $conn->query($query);
// if (!$result) {
//     die("Lỗi truy vấn khách hàng: " . $conn->error);
// }
// $all_customers = $result->fetch_all(MYSQLI_ASSOC);

// $customers = array_slice($all_customers, 0, 5); // Lấy 5 khách hàng đầu tiên
// Sau này sẽ đổi biến $fullname thành $user_id

// var_dump(getCustomersByUserId($conn, $fullname));
// exit();
$customers = array_slice(getCustomersByUserId($conn, $fullname), 0, 15);

// Lấy toàn bộ dịch vụ
$service_query = "SELECT * FROM services";
$service_result = $conn->query($service_query);
if (!$service_result) {
    die("Lỗi truy vấn dịch vụ: " . $conn->error);
}
$all_services = $service_result->fetch_all(MYSQLI_ASSOC);
$services = array_slice($all_services, 0, 5); // Lấy 5 dịch vụ đầu tiên

// Lấy toàn bộ addons
$addon_query = "SELECT * FROM addons";
$addon_result = $conn->query($addon_query);
if (!$addon_result) {
    die("Lỗi truy vấn addon: " . $conn->error);
}
$all_addons = $addon_result->fetch_all(MYSQLI_ASSOC);
$addons = array_slice($all_addons, 0, 5); // Lấy 5 addons đầu tiên

$sale_order_id = isset($_GET['sale_order_id']) ? $_GET['sale_order_id'] : null;

$conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sale Order</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/sale.css">
    <style>
        .search-popup {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            width: 80%;
            height: 60%;
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            z-index: 1000;
            display: none;
            overflow-y: auto;
        }
        .close-btn {
            float: right;
            cursor: pointer;
            font-size: 20px;
            margin-top: -15px;
        }
        .table-hover tbody tr:hover {
            background-color: #f9f9f9;
            cursor: pointer;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include('topmenu.php'); ?>
    <div class="container mt-5">
        <h1 class="h1 fw-bolder">Create Sale Order</h1>
        <form method="post" action="save_sale_order.php">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Select Customer:</label>
                <select id="customer_id" class="form-select" name="customer_id" required>
                    <?php if ($customer_id) { ?>
                        <option value="<?php echo $customer_id ?>"><?php echo $customer_current['customer_name']; ?></option>

                    <?php } else { ?>
                        <option value="">Select customers...</option>
                    <?php } ?>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>">
                            <?php echo $customer['customer_name']; ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="search_more_customers">Search More...</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="website_template_link" class="form-label">Website Template Link:</label>
                <input type="text" class="form-control" id="website_template_link" name="website_template_link" required>
            </div>

            <div class="mb-3">
                <label for="service_duration" class="form-label">Service Duration (Months):</label>
                <input type="number" class="form-control" id="service_duration" name="service_duration" required>
            </div>

            <div class="mb-3">
                <label for="service_id" class="form-label">Choose Services:</label>
                <select id="service_id" class="form-select" name="service_id" required>
                    <option value="">Select services...</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>">
                            <?php echo $service['service_name']; ?> - <?php echo number_format($service['price'], 0, ',', '.'); ?> VND
                        </option>
                    <?php endforeach; ?>
                    <option value="search_more_services">Search More...</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="addon_id" class="form-label">Choose Addons:</label>
                <select id="addon_id" class="form-select" name="addon_id" required>
                    <option value="">Select addons...</option>
                    <?php foreach ($addons as $addon): ?>
                        <option value="<?php echo $addon['id']; ?>">
                            <?php echo $addon['addon_name']; ?> - <?php echo number_format($addon['price'], 0, ',', '.'); ?> VND
                        </option>
                    <?php endforeach; ?>
                    <option value="search_more_addons">Search More...</option>
                </select>
            </div>

            <button class="btn btn-success" type="submit">Save</button>
        </form>

        <?php if ($sale_order_id): ?>
            <form method="post" action="create_contract.php" class="mt-3">
                <input type="hidden" name="sale_order_id" value="<?php echo $sale_order_id; ?>">
                <button class="btn btn-primary" type="submit">Print Contract</button>
            </form>
        <?php endif; ?>

        <!-- Popup tìm kiếm khách hàng -->
        <div id="customer_search_popup" class="search-popup">
            <span class="close-btn" onclick="closeCustomerSearch()">×</span>
            <h2>Search Customers</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody id="customer_search_results">
                    <?php foreach ($all_customers as $customer): ?>
                        <tr class="search-item" data-id="<?php echo $customer['id']; ?>">
                            <td><?php echo $customer['customer_name']; ?></td>
                            <td><?php echo $customer['phone_number']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Popup tìm kiếm dịch vụ -->
        <div id="service_search_popup" class="search-popup">
            <span class="close-btn" onclick="closeServiceSearch()">×</span>
            <h2>Search Services</h2>
            <div id="service_search_results">
                <?php foreach ($all_services as $service): ?>
                    <div class="search-item" data-id="<?php echo $service['id']; ?>" data-name="<?php echo $service['service_name']; ?>" data-price="<?php echo $service['price']; ?>">
                        <?php echo $service['service_name']; ?> - <?php echo number_format($service['price'], 0, ',', '.'); ?> VND
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Popup tìm kiếm addons -->
        <div id="addon_search_popup" class="search-popup">
            <span class="close-btn" onclick="closeAddonSearch()">×</span>
            <h2>Search Addons</h2>
            <div id="addon_search_results">
                <?php foreach ($all_addons as $addon): ?>
                    <div class="search-item" data-id="<?php echo $addon['id']; ?>" data-name="<?php echo $addon['addon_name']; ?>" data-price="<?php echo $addon['price']; ?>">
                        <?php echo $addon['addon_name']; ?> - <?php echo number_format($addon['price'], 0, ',', '.'); ?> VND
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('customer_id').addEventListener('change', function() {
            if (this.value === 'search_more_customers') {
                openCustomerSearch();
            }
        });

        document.getElementById('service_id').addEventListener('change', function() {
            if (this.value === 'search_more_services') {
                openServiceSearch();
            }
        });

        document.getElementById('addon_id').addEventListener('change', function() {
            if (this.value === 'search_more_addons') {
                openAddonSearch();
            }
        });

        function openCustomerSearch() {
            document.getElementById('customer_search_popup').style.display = 'block';
        }

        function closeCustomerSearch() {
            document.getElementById('customer_search_popup').style.display = 'none';
        }

        function openServiceSearch() {
            document.getElementById('service_search_popup').style.display = 'block';
        }

        function closeServiceSearch() {
            document.getElementById('service_search_popup').style.display = 'none';
        }

        function openAddonSearch() {
            document.getElementById('addon_search_popup').style.display = 'block';
        }

        function closeAddonSearch() {
            document.getElementById('addon_search_popup').style.display = 'none';
        }

        // Thêm sự kiện click để chọn từ bảng khách hàng, dịch vụ và addons
        document.querySelectorAll('#customer_search_results .search-item').forEach(item => {
            item.addEventListener('click', function() {
                const customerId = this.getAttribute('data-id');
                const customerName = this.children[0].textContent;

                const select = document.getElementById('customer_id');
                select.innerHTML += `<option value="${customerId}" selected>${customerName}</option>`;
                closeCustomerSearch();
            });
        });

        document.querySelectorAll('#service_search_results .search-item').forEach(item => {
            item.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-id');
                const serviceName = this.getAttribute('data-name');
                const servicePrice = this.getAttribute('data-price');

                const select = document.getElementById('service_id');
                select.innerHTML += `<option value="${serviceId}" selected>${serviceName} - ${new Intl.NumberFormat('vi-VN').format(servicePrice)} VND</option>`;
                closeServiceSearch();
            });
        });

        document.querySelectorAll('#addon_search_results .search-item').forEach(item => {
            item.addEventListener('click', function() {
                const addonId = this.getAttribute('data-id');
                const addonName = this.getAttribute('data-name');
                const addonPrice = this.getAttribute('data-price');

                const select = document.getElementById('addon_id');
                select.innerHTML += `<option value="${addonId}" selected>${addonName} - ${new Intl.NumberFormat('vi-VN').format(addonPrice)} VND</option>`;
                closeAddonSearch();
            });
        });
    </script>
</body>
</html>
