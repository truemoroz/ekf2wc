<?php
/**
 * Created by PhpStorm.
 * User: Pavel Morozov moroz0@gmail.com
 * Date: 21.10.2019
 * Time: 9:36
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

for ($page = 21; $page <= 40; $page++) {
    echo 'page: ' . $page . PHP_EOL;
    $categories = $woocommerce->get('products/categories?page=' . $page);

    foreach ($categories as $category) {
        // echo $category->name . PHP_EOL;
        // var_dump($category->image);
        if (empty($category->image->src)) {
            echo 'category ' . $category->id . ': ' . $category->name . ' has no foto' . PHP_EOL;

            // получаем продукты категории
            $wooProducts = $woocommerce->get('products?category=' . $category->id);

            if (!empty($wooProducts)) {
                // получаем файлы первого продукта
                $productFiles = $ekfObject->getProductFiles($wooProducts[0]->sku);
                // создаём массив фото для обновления категории
                if (!empty($productFiles)) {
                    foreach ($productFiles->data[0]->files as $key => $file) {
                        if ($file->name == 'Основное изображение' || $file->name == 'Дополнительное изображение') {
                            $image['src']  = $file->file;
                            $image['name'] = $file->name;
                            $image['alt']  = $file->name;

                            $data = ['image' => $image];
                            try {
                                $woocommerce->put('products/categories/' . $category->id, $data);
                                $image = [];
                                echo 'category ' . $category->id . ': ' . $category->name . ' foto updated' . PHP_EOL;
                                break;
                            } catch (HttpClientException $e) {
                                $errorMsg = $e->getMessage();
                                echo $errorMsg . PHP_EOL;
                                continue;
                            }

                        }
                    }
                }
            } else {
                echo 'category ' . $category->id . ': ' . $category->name . ' has no goods' . PHP_EOL;
        }
        }
    }
}

// $wooProducts = $woocommerce->get('products?category=390');
//
// // var_dump($wooProducts);
// // die();
// foreach ($wooProducts as $wooProduct) {
//     var_dump($wooProduct);
//     die();
// }