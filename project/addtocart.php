<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Log in']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'invalid request']);
    exit;
}

$itemId = $_POST['id'];

// loading goods.XML
$goodsXml = simplexml_load_file('../../data/goods.xml');
if (!$goodsXml) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to reload']);
    exit;
}

$item = $goodsXml->xpath("//item[item_number='$itemId']")[0];
if (!$item) {
    echo json_encode(['status' => 'error', 'message' => 'Not existing']);
    exit;
}

if ((int)$item->quantity_available <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Sold out']);
    exit;
}

// update the XML
$item->quantity_available = (int)$item->quantity_available - 1;
$item->quantity_on_hold = (int)$item->quantity_on_hold + 1;

if ($goodsXml->asXML('../../data/goods.xml')) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to update']);
}
