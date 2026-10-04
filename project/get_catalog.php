<?php
session_start();

function get_items() {
    $xmlFile = '../../data/goods.xml';
    if (file_exists($xmlFile)) {
        $xml = file_get_contents($xmlFile);
        // Remove any whitespace before or after the XML content
        $xml = trim($xml);
        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
    } else {
        header('HTTP/1.1 404 Not Found');
        echo 'XML file not found';
    }
}

function update_cart() {
    $xml = simplexml_load_file('../../data/goods.xml');
    $action = $_POST['action'];
    $itemNumber = $_POST['itemNumber'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $item = $xml->xpath("//good[id='$itemNumber']")[0];

    if ($action === 'add') {
        if ((int)$item->quantity_available > 0) {
            if (isset($_SESSION['cart'][$itemNumber])) {
                $_SESSION['cart'][$itemNumber]['quantity']++;
            } else {
                $_SESSION['cart'][$itemNumber] = [
                    'quantity' => 1,
                    'price' => (float)$item->price
                ];
            }
            $item->quantity_available = (int)$item->quantity_available - 1;
            $item->quantity_on_hold = (int)$item->quantity_on_hold + 1;
            $xml->asXML('../../data/goods.xml');
            echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sorry, this item is not available for sale']);
        }
    } elseif ($action === 'remove') {
        if (isset($_SESSION['cart'][$itemNumber])) {
            $quantity = $_SESSION['cart'][$itemNumber]['quantity'];
            unset($_SESSION['cart'][$itemNumber]);
            $item->quantity_available = (int)$item->quantity_available + $quantity;
            $item->quantity_on_hold = (int)$item->quantity_on_hold - $quantity;
            $xml->asXML('../../data/goods.xml');
            echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        }
    }
}

function process_purchase() {
    $xml = simplexml_load_file('../../data/goods.xml');
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $totalAmount = 0;
        foreach ($_SESSION['cart'] as $itemNumber => $item) {
            $xmlItem = $xml->xpath("//good[id='$itemNumber']")[0];
            $xmlItem->quantity_on_hold = (int)$xmlItem->quantity_on_hold - $item['quantity'];
            $xmlItem->quantity_sold = (int)$xmlItem->quantity_sold + $item['quantity'];
            $totalAmount += $item['quantity'] * $item['price'];
        }
        $xml->asXML('../../data/goods.xml');
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'totalAmount' => $totalAmount]);
    } elseif ($action === 'cancel') {
        foreach ($_SESSION['cart'] as $itemNumber => $item) {
            $xmlItem = $xml->xpath("//good[id='$itemNumber']")[0];
            $xmlItem->quantity_on_hold = (int)$xmlItem->quantity_on_hold - $item['quantity'];
            $xmlItem->quantity_available = (int)$xmlItem->quantity_available + $item['quantity'];
        }
        $xml->asXML('../../data/goods.xml');
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
    } elseif ($action === 'logout') {
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $itemNumber => $item) {
                $xmlItem = $xml->xpath("//good[id='$itemNumber']")[0];
                $xmlItem->quantity_on_hold = (int)$xmlItem->quantity_on_hold - $item['quantity'];
                $xmlItem->quantity_available = (int)$xmlItem->quantity_available + $item['quantity'];
            }
            $xml->asXML('../../data/goods.xml');
        }
        session_destroy();
        echo json_encode(['success' => true]);
    }
}

// Check if action is set, if not, set it to an empty string
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_items':
        get_items();
        break;
    case 'update_cart':
        update_cart();
        break;
    case 'process_purchase':
        process_purchase();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}