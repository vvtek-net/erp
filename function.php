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