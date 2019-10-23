<?php
/**
 * Created by PhpStorm.
 * User: Pavel Morozov moroz0@gmail.com
 * Date: 14.10.2019
 * Time: 10:52
 *
 * Добавление фоток к товарам без них
 */


require __DIR__ . './../../vendor/autoload.php';
require __DIR__ . './../../core/functions.php';
require __DIR__ . './../../core/config.php';
require __DIR__ . './../../app/ekf/ekfClass.php';

use Automattic\WooCommerce\Client;
// use ekfClass;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$woocommerce = new Client(
    'https://ipro.armakom.net',
    $consumerKey,
    $consumerSecret,
    [
        'wp_api'  => true,
        'version' => 'wc/v3',
        'timeout' => 120,
    ]
);

$ekfObject = new ekfClass($apiKey);

// $wooProducts = $woocommerce->get('products?sku=mcb4763-6-1-16D-pro');
// var_dump(json_encode($wooProducts, 480));
// die();

// 686 pages
//page: 424
for ($page = 601; $page <= 690; $page++) {
echo 'page: ' . $page . PHP_EOL;
    $wooProducts = $woocommerce->get('products?page=' . $page);
    foreach ($wooProducts as $wooProduct) {
        if(empty($wooProduct->images)) {
            $sku = $wooProduct->sku;
            $wooId = $wooProduct->id;
            $productFiles = $ekfObject->getProductFiles($sku);
            $i = 0;
            $images = [];
            if (empty($productFiles->data[0]->files)) {
                echo 'EKF has no foto for this sku: ' . $sku . PHP_EOL;
            } else {
                foreach ($productFiles->data[0]->files as $key => $file) {
                    if ($file->name == 'Основное изображение' || $file->name == 'Дополнительное изображение') {
                        $images[$i]['src']  = $file->file;
                        $images[$i]['name'] = $file->name;
                        $images[$i]['alt']  = $file->name;
                        $i++;
                    }
                }
                $data['images'] = $images;
                // print_r($woocommerce->put('products/' . $wooId, $data));
                try {
                    $woocommerce->put('products/' . $wooId, $data);
                    echo $sku . ' images updated' . PHP_EOL;
                } catch (HttpClientException $e) {
                    $errorMsg = $e->getMessage();
                    echo $errorMsg . PHP_EOL;
                }
            }

        }
    }
}
// var_dump(json_encode($wooProducts, 480));
// print_r(count($wooProducts));