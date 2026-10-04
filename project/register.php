<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set the content type to JSON
header('Content-Type: application/json');

// Define the path to the XML file
$xmlFile = '../../data/customer.xml';

// Function to generate a customer ID
function generateCustomerId()
{
    return 'RL' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

// Function to check if an email is unique
function isEmailUnique($email, $xml)
{
    foreach ($xml->customer as $customer) {
        if ((string)$customer->email === $email) {
            return false;
        }
    }
    return true;
}

// Main logic
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : '';
        $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';

        // Server-side validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phone)) {
            throw new Exception('All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }

        if (!preg_match('/^0\d\s\d{8}$/', $phone)) {
            throw new Exception('Invalid phone number format.');
        }

        // Load or create XML
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
        } else {
            $xml = new SimpleXMLElement('<customers></customers>');
        }

        if (!isEmailUnique($email, $xml)) {
            throw new Exception('Email address already in use.');
        }

        $customerId = generateCustomerId();

        $newCustomer = $xml->addChild('customer');
        $newCustomer->addChild('customerId', $customerId);
        $newCustomer->addChild('firstName', $firstName);
        $newCustomer->addChild('lastName', $lastName);
        $newCustomer->addChild('email', $email);
        $newCustomer->addChild('password', $password);
        $newCustomer->addChild('phone', $phone);

        // Save XML file
        if ($xml->asXML($xmlFile)) {
            echo json_encode(array('success' => true, 'customerId' => $customerId));
        } else {
            throw new Exception('Error saving customer information.');
        }
    } else {
        throw new Exception('Invalid request method.');
    }
} catch (Exception $e) {
    error_log('Error in register.php: ' . $e->getMessage());
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
