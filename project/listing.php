<?php
session_start();

if (!isset($_SESSION['manager_id'])) {
    echo "Unauthorized access";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemNumber = $_POST['itemNumber'];
    $itemName = $_POST['itemName'];
    $itemPrice = $_POST['itemPrice'];
    $itemQuantity = $_POST['itemQuantity'];
    $itemDescription = $_POST['itemDescription'];

    // Load XML file using correct path
    $xml = simplexml_load_file('../../data/goods.xml');

    if (!$xml) {
        // If file doesn't exist, create new XML structure
        $xml = new SimpleXMLElement('<goods></goods>');
    }

    // Check if item already exists
    foreach ($xml->good as $good) {
        if ((string)$good->id == $itemNumber) {
            echo "ID already exists";
            exit;
        }
    }

    // Create new item node
    $newItem = $xml->addChild('good');
    $newItem->addChild('id', $itemNumber);
    $newItem->addChild('name', $itemName);
    $newItem->addChild('price', $itemPrice);
    $newItem->addChild('quantity_available', $itemQuantity);
    $newItem->addChild('quantity_on_hold', 0);
    $newItem->addChild('quantity_sold', 0);
    $newItem->addChild('description', $itemDescription);

    // Save updated XML
    if ($xml->asXML('../../data/goods.xml')) {
        echo "Inventory successfully updated: " . $itemNumber;
    } else {
        echo "Save XML error";
    }
} else {
    echo "Invalid request";
}
