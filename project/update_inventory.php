<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['item_number']) || !isset($data['quantity']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$item_number = $data['item_number'];
$quantity = intval($data['quantity']);
$action = $data['action'];

// Load XML file
$xml = simplexml_load_file('../data/goods.xml');
if (!$xml) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load XML file']);
    exit;
}

// Find and update item
$item = $xml->xpath("//item[item_number='$item_number']")[0];
if (!$item) {
    http_response_code(404);
    echo json_encode(['error' => 'Item not found']);
    exit;
}

switch ($action) {
    case 'add_to_cart':
        if ($item->quantity_available >= $quantity) {
            $item->quantity_available -= $quantity;
            $item->quantity_on_hold += $quantity;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Not enough inventory']);
            exit;
        }
        break;
    case 'remove_from_cart':
        $item->quantity_available += $quantity;
        $item->quantity_on_hold -= $quantity;
        break;
    case 'confirm_purchase':
        $item->quantity_on_hold -= $quantity;
        $item->quantity_sold += $quantity;
        break;
    case 'cancel_purchase':
        $item->quantity_available += $quantity;
        $item->quantity_on_hold -= $quantity;
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

// Save updated XML
if ($xml->asXML('../data/goods.xml')) {
    echo json_encode([
        'success' => true,
        'item' => [
            'item_number' => (string)$item->item_number,
            'quantity_available' => (int)$item->quantity_available,
            'quantity_on_hold' => (int)$item->quantity_on_hold,
            'quantity_sold' => (int)$item->quantity_sold
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update XML file']);
}
