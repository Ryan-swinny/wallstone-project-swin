<?php
// processing.php

session_start();

if (!isset($_SESSION['manager_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$xmlFile = '../../data/goods.xml';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // processing order request
    $xml = simplexml_load_file($xmlFile);
    if (!$xml) {
        echo json_encode(['success' => false, 'message' => 'Failed to load XML file']);
        exit;
    }

    $items = [];
    foreach ($xml->good as $item) {
        if ((int)$item->quantity_sold > 0) {
            $items[] = [
                'id' => (string)$item->id,
                'name' => (string)$item->name,
                'price' => (float)$item->price,
                'quantity_available' => (int)$item->quantity_available,
                'quantity_on_hold' => (int)$item->quantity_on_hold,
                'quantity_sold' => (int)$item->quantity_sold
            ];
        }
    }

    echo json_encode(['success' => true, 'items' => $items]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_orders') {
    // processing
    $xml = simplexml_load_file($xmlFile);
    if (!$xml) {
        echo json_encode(['success' => false, 'message' => 'Failed to load XML file']);
        exit;
    }

    $updated = false;
    foreach ($xml->good as $key => $item) {
        if ((int)$item->quantity_sold > 0) {
            $item->quantity_available = (int)$item->quantity_available - (int)$item->quantity_sold;
            $item->quantity_sold = 0;
            $updated = true;
        }

        // To check if can remove 
        if ((int)$item->quantity_available == 0 && (int)$item->quantity_on_hold == 0) {
            unset($xml->good[$key]);
        }
    }

    if ($updated) {
        if ($xml->asXML($xmlFile)) {
            echo json_encode(['success' => true, 'message' => 'Orders processed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save XML file']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'No orders to process']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
