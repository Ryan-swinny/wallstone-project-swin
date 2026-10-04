<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Load XML file to get product details
$xml = simplexml_load_file('../data/goods.xml');
if (!$xml) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to load product data']);
    exit;
}

$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $item_id => $quantity) {
    $item = $xml->xpath("//item[id='$item_id']")[0];
    if ($item) {
        $price = (float)$item->price;
        $cart_items[] = [
            'id' => $item_id,
            'name' => (string)$item->name,
            'price' => $price,
            'quantity' => (int)$quantity,
            'subtotal' => $price * $quantity
        ];
        $total += $price * $quantity;
    }
}

$order = [
    'id' => uniqid(),
    'items' => $cart_items,
    'total' => $total,
    'date' => date('Y-m-d H:i:s')
];

echo json_encode([
    'status' => 'success',
    'data' => [
        'cart' => $_SESSION['cart'],
        'cart_details' => $cart_items,
        'order' => $order
    ]
]);
