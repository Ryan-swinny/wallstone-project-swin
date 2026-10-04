<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . "cancel_purchase.php: " . $message . PHP_EOL, 3, '/path/to/your/error.log');
}

if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Please login first"]);
    logError("Unauthenticated user attempted to cancel purchase");
    exit();
}

$customerId = $_SESSION['customer_id'];
$xmlFile = '../../data/goods.xml';

if (!file_exists($xmlFile)) {
    http_response_code(500);
    echo json_encode(["error" => "Product data does not exist"]);
    logError("goods.xml file does not exist");
    exit();
}

$dom = new DOMDocument();
if (!$dom->load($xmlFile)) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to read file"]);
    logError("Unable to read goods.xml file");
    exit();
}

$xpath = new DOMXPath($dom);

$updatedItems = [];
$totalCancelled = 0;

foreach ($xpath->query("//item") as $item) {
    $onHold = (int)$xpath->evaluate("quantity_on_hold", $item)->item(0)->nodeValue;
    if ($onHold > 0) {
        $available = (int)$xpath->evaluate("quantity_available", $item)->item(0)->nodeValue;
        $xpath->evaluate("quantity_available", $item)->item(0)->nodeValue = $available + $onHold;
        $xpath->evaluate("quantity_on_hold", $item)->item(0)->nodeValue = 0;
        $totalCancelled += $onHold;
        $updatedItems[] = [
            'item_number' => $xpath->evaluate("item_number", $item)->item(0)->nodeValue,
            'quantity_available' => $available + $onHold,
            'quantity_on_hold' => 0
        ];
    }
}

if ($dom->save($xmlFile)) {
    echo json_encode([
        "status" => "Success",
        "message" => "Purchase cancelled",
        "total_cancelled" => $totalCancelled,
        "updated_items" => $updatedItems
    ]);
    logError("User $customerId successfully cancelled purchase, cancelled quantity: $totalCancelled");
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save data"]);
    logError("Unable to save updated goods.xml file");
}
