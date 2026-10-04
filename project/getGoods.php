<?php
// getGoods.php
function get_goods()
{
    $url = '/home/students/accounts/s104188201/cos80021/www/data/goods.xml';
    $xml = simplexml_load_file($url);
    if ($xml === false) {
        // Processing error
        die('Error loading XML file');
    }
    header('Content-Type: application/xml');
    echo $xml->asXML();
}

get_goods();
