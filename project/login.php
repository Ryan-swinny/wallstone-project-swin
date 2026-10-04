<?php
session_start();
header('Content-Type: application/json');

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if POST method is used
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password']; // This is plaintext password

    // Read customer.xml file
    $xml = simplexml_load_file('/home/students/accounts/s104188201/cos80021/www/data/customer.xml');

    if ($xml === false) {
        echo json_encode(['success' => false, 'message' => 'Unable to read user data']);
        exit;
    }

    // Check user credentials
    foreach ($xml->customer as $customer) {
        if ((string)$customer->email == $email && (string)$customer->password == $password) {
            // Login successful
            $_SESSION['customer_id'] = (string)$customer->customerId;
            echo json_encode(['success' => true]);
            exit;
        }
    }

    // This block seems redundant and unreachable, but keeping it as per original code
    if ($loginSuccessful) {
        $_SESSION['customer_id'] = $customerId;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid login"]);
    }
}
