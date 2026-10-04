<?php
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

$customersXML = simplexml_load_file('../../data/customer.xml');

$response = array('success' => false);

foreach ($customersXML->customer as $customer) {
    if ((string)$customer->email == $email && (string)$customer->password == $password) {
        $_SESSION['customer_id'] = (string)$customer->customer_id;
        $response['success'] = true;
        $response['email'] = $email; //return email address
        break;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
