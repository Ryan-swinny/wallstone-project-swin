<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . "confirm_purchase.php: " . $message . PHP_EOL, 3, '/path/to/your/error.log');
}

if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Please login first"]);
    logError("Unauthenticated user attempted to confirm purchase");
    exit();
}

if (!isset($_POST['cart']) || empty($_POST['cart'])) {
    http_response_code(400);
    echo json_encode(["error" => "Cart is empty"]);
    logError("Attempt to confirm empty cart");
    exit();
}

$cart = json_decode($_POST['cart'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid cart data"]);
    logError("Failed to parse cart JSON data: " . json_last_error_msg());
    exit();
}

$xmlFile = "../../data/goods.xml";
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

$total = 0;
$updatedItems = [];
$errors = [];

foreach ($cart as $itemNumber => $item) {
    $query = "//item[item_number='$itemNumber']";
    $xmlItem = $xpath->query($query)->item(0);

    if (!$xmlItem) {
        $errors[] = "Product $itemNumber does not exist";
        continue;
    }

    $quantityOnHold = (int)$xpath->evaluate("quantity_on_hold", $xmlItem)->item(0)->nodeValue;

    if ($quantityOnHold < $item['quantity']) {
        $errors[] = "Insufficient stock for product $itemNumber";
        continue;
    }

    $newQuantityOnHold = $quantityOnHold - $item['quantity'];
    $newQuantitySold = (int)$xpath->evaluate("quantity_sold", $xmlItem)->item(0)->nodeValue + $item['quantity'];

    $xpath->evaluate("quantity_on_hold", $xmlItem)->item(0)->nodeValue = $newQuantityOnHold;
    $xpath->evaluate("quantity_sold", $xmlItem)->item(0)->nodeValue = $newQuantitySold;

    $total += $item['price'] * $item['quantity'];
    $updatedItems[] = [
        'item_number' => $itemNumber,
        'quantity_sold' => $newQuantitySold,
        'quantity_on_hold' => $newQuantityOnHold
    ];
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["error" => "Purchase confirmation failed", "details" => $errors]);
    logError("Purchase confirmation failed: " . implode(", ", $errors));
    exit();
}

if ($dom->save($xmlFile)) {
    echo json_encode([
        "status" => "Success",
        "message" => "Purchase confirmed",
        "total" => $total,
        "updated_items" => $updatedItems
    ]);
    logError("Successfully confirmed purchase, total amount: $total");
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save data"]);
    logError("Unable to save updated goods.xml file");
}
