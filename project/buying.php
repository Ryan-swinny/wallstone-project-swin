<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.htm');
    exit();
}

// Loading items
$xmlFile = '../../data/goods.xml';
$xml = new DOMDocument();
$xml->load($xmlFile);

// processing XML
$catalogHtml = "";
$items = $xml->getElementsByTagName('item');
foreach ($items as $item) {
    $itemNumber = $item->getElementsByTagName('itemNumber')->item(0)->nodeValue;
    $name = $item->getElementsByTagName('itemName')->item(0)->nodeValue;
    $price = $item->getElementsByTagName('price')->item(0)->nodeValue;
    $quantity = $item->getElementsByTagName('quantityAvailable')->item(0)->nodeValue;
    $catalogHtml .= "<div class='product'>";
    $catalogHtml .= "<h3>$name</h3>";
    $catalogHtml .= "<p>Price: $$price</p>";
    $catalogHtml .= "<p>Available: $quantity</p>";
    $catalogHtml .= "<button onclick='addToCart(\"$itemNumber\", \"$name\", $price)'>加入購物車</button>";
    $catalogHtml .= "</div>";
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallstone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em;
        }

        main {
            display: flex;
            padding: 20px;
        }

        .products {
            flex: 3;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .product {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }

        .cart {
            flex: 1;
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-left: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Wallstone</h1>
        <p>Welcome, customer ID: <?php echo htmlspecialchars($_SESSION['customer_id']); ?></p>
    </header>
    <main>
        <section class="products">
            <?php echo $catalogHtml; ?>
        </section>
        <aside class="cart">
            <h2>Cart</h2>
            <div id="cart-items"></div>
            <div id="cart-summary">
                <p><strong>total: <span id="cart-total">$0.00</span></strong></p>
            </div>
            <button onclick="confirmPurchase()">checkout</button>
            <button onclick="cancelPurchase()">cancel</button>
            <a href="#" onclick="logout()">Logout</a>
        </aside>
    </main>
    <script src="buying.js"></script>
</body>

</html>