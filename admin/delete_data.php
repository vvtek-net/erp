<?php
// Kết nối tới SQL Server và MySQL
$serverName = "DESKTOP-FABUPOB\\MSSQLSERVERS";
$connectionInfo = array("Database" => "salepos", "CharacterSet" => "UTF-8");
$connSQLServer = sqlsrv_connect($serverName, $connectionInfo);
$connMySQL = new mysqli("localhost", "root", "", "telesale");

if ($connMySQL->connect_error || !$connSQLServer) {
    die("Database connection failed.");
}

if (isset($_GET['selectedDataIds'])) {
    $selectedDataIds = explode(",", $_GET['selectedDataIds']);

    // Xóa dữ liệu trong SQL Server
    foreach ($selectedDataIds as $data_id) {
        $sqlDeleteSQLServer = "DELETE FROM mainData WHERE id = ?";
        $params = array($data_id);
        sqlsrv_query($connSQLServer, $sqlDeleteSQLServer, $params);
    }

    // Xóa dữ liệu trong MySQL nếu cần
    foreach ($selectedDataIds as $data_id) {
        $sqlDeleteMySQL = "DELETE FROM data WHERE data_id = ?";
        $stmtMySQL = $connMySQL->prepare($sqlDeleteMySQL);
        $stmtMySQL->bind_param("i", $data_id);
        $stmtMySQL->execute();
    }
}

// Đóng kết nối và chuyển hướng
sqlsrv_close($connSQLServer);
$connMySQL->close();
header("Location: manage_business_data.php");
exit();
?>
