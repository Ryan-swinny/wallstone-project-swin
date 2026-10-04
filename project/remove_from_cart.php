<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$itemId = $_POST['id'];

// Remove item from cart
$cart = &$_SESSION['cart'];
$itemIndex = array_search($itemId, array_column($cart, 'item_number'));
if ($itemIndex !== false) {
    $quantity = $cart[$itemIndex]['quantity'];
    unset($cart[$itemIndex]);
    $cart = array_values($cart);  // Reindex the array

    // Update XML
    $goodsXml = simplexml_load_file('../../data/goods.xml');
    if (!$goodsXml) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to load product data']);
        exit;
    }

    $item = $goodsXml->xpath("//item[item_number='$itemId']")[0];
    if ($item) {
        $item->quantity_available = (int)$item->quantity_available + $quantity;
        $item->quantity_on_hold = (int)$item->quantity_on_hold - $quantity;

        if ($goodsXml->asXML('../../data/goods.xml')) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update product data']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product does not exist']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Product is not in the cart']);
}
