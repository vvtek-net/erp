<?php
function getAccount($conn, $id) // Add $conn as a parameter
{
    // Check if the connection is valid
    if (!$conn || $conn->connect_errno) {
        die("Database connection error: " . $conn->connect_error);
    }

    // Prepare the query
    $query = "SELECT * FROM accounts WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Fetch the result
    $result = $stmt->get_result()->fetch_assoc();

    // Close the statement
    $stmt->close();

    return $result;
}

// JOIN opportunities, customers
function getAllOpportunities($conn)
{
    // Check if the connection is valid
    if (!$conn || $conn->connect_errno) {
        die("Database connection error: " . $conn->connect_error);
    }

    $query = "SELECT * FROM opportunities 
            LEFT JOIN customers ON opportunities.customer_id = customers.id
            LEFT JOIN accounts ON opportunities.account_id = accounts.id";
    $result = $conn->query($query);
    $opportunities = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $opportunities[] = $row;
        }
    }
    return $opportunities;
}

// get all customers
function getCustomersByUserId($conn, $fullname)
{
    $query = "SELECT * FROM customers where created_by = '$fullname'";
    $result = $conn->query($query);
    $customers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    return $customers;
}

// get a customer
function getCustomer($conn, $id)
{
    $query = "SELECT * FROM customers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

// convert number to word
function convert_number_to_words($number) {
    $hyphen      = ' ';
    $conjunction = ' ';
    $separator   = ' ';
    $negative    = 'âm ';
    $decimal     = ' phẩy ';
    $dictionary  = array(
        0                   => 'không',
        1                   => 'một',
        2                   => 'hai',
        3                   => 'ba',
        4                   => 'bốn',
        5                   => 'năm',
        6                   => 'sáu',
        7                   => 'bảy',
        8                   => 'tám',
        9                   => 'chín',
        10                  => 'mười',
        11                  => 'mười một',
        12                  => 'mười hai',
        13                  => 'mười ba',
        14                  => 'mười bốn',
        15                  => 'mười lăm',
        16                  => 'mười sáu',
        17                  => 'mười bảy',
        18                  => 'mười tám',
        19                  => 'mười chín',
        20                  => 'hai mươi',
        30                  => 'ba mươi',
        40                  => 'bốn mươi',
        50                  => 'năm mươi',
        60                  => 'sáu mươi',
        70                  => 'bảy mươi',
        80                  => 'tám mươi',
        90                  => 'chín mươi',
        100                 => 'trăm',
        1000                => 'nghìn',
        1000000             => 'triệu',
        1000000000          => 'tỷ',
        1000000000000       => 'nghìn tỷ',
        1000000000000000    => 'nghìn triệu triệu',
        1000000000000000000 => 'tỷ tỷ'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $digit) {
            $words[] = $dictionary[$digit];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

// SALE ORDERS
function getSaleOrder($conn, $id) {
    $query = "SELECT *, services.price as svsPrice, addons.price as adsPrice, customers.id as customer_id, sale_orders.customer_id as cusId
            FROM sale_orders 
            LEFT JOIN services on services.service_id = sale_orders.service_type
            LEFT JOIN addons on addons.addon_id = sale_orders.addon_type
            LEFT JOIN customers on customers.id = sale_order.customer.id
            WHERE id = $id";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}