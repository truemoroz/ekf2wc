<?php
require __DIR__ . './../../vendor/autoload.php';
require __DIR__ . './../../core/functions.php';
require __DIR__ . './../../core/config.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$woocommerce = new Client(
    'https://armakom.net',
    $consumerKey,
    $consumerSecret,
    [
        'wp_api'  => true,
        'version' => 'wc/v3',
        'timeout' => 120,
    ]
);

// читаем xml
$xml = simplexml_load_file('ymlcatalog.xml');
// $dom = new  DOMDocument('1.0', 'utf-8');
// var_dump(count($xml->shop->offers->offer));
// цикл по товарам
foreach ( $xml->shop->offers->offer as $item) {
    // $storeNode = dom_import_simplexml($item);
    $storeNode = dom_import_simplexml($item);
    // var_dump($item);
    $sku = $item['id'];
    // $price = (float)$item->price;
    $price = (int)round_up((float)$item->price, 0);
    var_dump($price);
    // var_dump($priceUp);

    die();
}