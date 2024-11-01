<?php

include_once '../../db_connection.php';

$query = "SELECT * FROM tasks";
$stmt = $conn->prepare($query);
$stmt->execute();

var_dump($_GET['exportType']);
exit();

$result = [];
$rows = $stmt->get_result();
while ($row = $rows->fetch_assoc()) {
    $result[] = $row;
}

// if (isset($GET["exportType"])) {
//     $target = $GET["exportType"];
//     switch ($target) {
//         case 'export-to-excel':
//             $filename = "data_tasks_" . date('Ymd') . ".xlsx";
//             header("Content-Type: application/vnd.ms-excel");
//             // header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//             header("Content-Disposition: attachment; filename=\"$filename\"");
//             ExportFile($result);
//             exit();
//         default: 
//             die("Unknown action: " . $GET["exportType"]);
//     }
// } else {
//     echo "No";
// }

function ExportFile($record)
{
    $heading = false;
    if (!empty($record)) {
        foreach ($record as $row) {
            if (!$heading) {
                echo implode("\t", array_keys($row)) ."\n";
                $heading = true;
            }
            echo implode("\t", array_values($row)) ."\n";
        }
    }
    exit();
}