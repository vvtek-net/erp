<?php
// Kết nối tới MySQL
$connMySQL = new mysqli("localhost", "root", "", "telesale");

if ($connMySQL->connect_error) {
    die("Failed to connect to MySQL database: " . $connMySQL->connect_error);
}

// Kết nối tới SQL Server để lấy thông tin từ bảng mainData
$serverName = "DESKTOP-FABUPOB\\MSSQLSERVERS";
$connectionInfo = array("Database" => "salepos", "CharacterSet" => "UTF-8");
$connSQLServer = sqlsrv_connect($serverName, $connectionInfo);

if (!$connSQLServer) {
    echo "Failed to connect to SQL Server database.<br />";
    die(print_r(sqlsrv_errors(), true));
}

// Kiểm tra xem có nhận được dữ liệu GET không
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['selectedDataIds']) && isset($_GET['user_id'])) {
    // Lấy các ID đã chọn và ID của nhân viên
    $selectedDataIds = explode(",", $_GET['selectedDataIds']);
    $user_id = $_GET['user_id'];
    
    // Lấy tên nhân viên từ bảng accounts trong MySQL
    $sqlGetUser = "SELECT fullname FROM accounts WHERE id = ?";
    $stmtUser = $connMySQL->prepare($sqlGetUser);
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $stmtUser->bind_result($username);
    $stmtUser->fetch();
    $stmtUser->close();

    // Duyệt qua từng ID được chọn và lấy dữ liệu từ mainData để lưu vào data
    foreach ($selectedDataIds as $data_id) {
        // Truy vấn dữ liệu từ bảng mainData
        $sqlSelect = "SELECT * FROM mainData WHERE id = ?";
        $params = array($data_id);
        $stmt = sqlsrv_query($connSQLServer, $sqlSelect, $params);
        
        if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Lấy dữ liệu cần chèn vào bảng data
            $name = $row['tenCongTy'];
            $phone = $row['soDienThoai'];
            $description = "Data doanh nghiệp cần xử lý - MST: ". $row['mst']. " - Người đại diện: ". $row['nguoiDaiDien'];
            $status = "pending";
            $sources_id = 7;

            // Chuẩn bị câu lệnh chèn vào bảng data trong MySQL
            $sqlInsert = "INSERT INTO data (name, phone, description, user_id, status, update_at, sources_id) 
                          VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmtMySQL = $connMySQL->prepare($sqlInsert);
            $stmtMySQL->bind_param("sssisi", $name, $phone, $description, $user_id, $status, $sources_id);

            // Thực thi câu lệnh chèn dữ liệu
            if (!$stmtMySQL->execute()) {
                echo "Lỗi khi chèn dữ liệu vào MySQL: " . $stmtMySQL->error;
            }
            $stmtMySQL->close();

            // Cập nhật tên nhân viên vào cột users trong bảng mainData của SQL Server
            $sqlUpdate = "UPDATE mainData SET users = ? WHERE id = ?";
            $paramsUpdate = array($username, $data_id);
            $stmtUpdate = sqlsrv_query($connSQLServer, $sqlUpdate, $paramsUpdate);
            
            if ($stmtUpdate === false) {
                echo "Lỗi khi cập nhật tên nhân viên trong SQL Server: ";
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }
    echo "Dữ liệu đã được phân công thành công!";
}

// Đóng kết nối
sqlsrv_close($connSQLServer);
$connMySQL->close();

// Quay lại trang manage_business_data.php
header("Location: manage_business_data.php");
exit();
?>
