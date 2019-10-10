<?php

// define( 'ekfFolder', __DIR__  . './../ekf/' );
// define('FILE_TO_IMPORT', './../share/group1-1.json');

require __DIR__ . './../../vendor/autoload.php';
require __DIR__ . './../../core/functions.php';
require __DIR__ . './../../core/config.php';

// создание файлов для импорта
require './../ekf/getCatalog.php';


use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;


$woocommerce = new Client(
    'https://armakom.net',
    $consumerKey,
    $consumerSecret,
    [
        'wp_api' => true,
        'version' => 'wc/v3',
        'timeout' => 120,
//        'query_string_auth' => true
    ]
);




main($woocommerce);

function main($woocommerce)
{

    $importedProductsFile = 'importedProducts.txt';
    $attributesChangeFile = 'attributesChange.json';

// Получаем массив уже импортированных
// $importedProducts = json_decode(file_get_contents($importedProductsFile), true);
    $handle = fopen($importedProductsFile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $ids = explode(':',trim($line));
            $importedProducts[$ids[0]] = $ids[1];
        }
    }
    fclose($handle);

    // получаем массив автозамен
    $handle = fopen('autoAttributeChange.txt', "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $ids = explode(':',trim($line));
            $autoAttributeChange[$ids[0]] = $ids[1];
        }
    }
    fclose($handle);
    $lastAutoAttribute = $ids[1];
    $lastAutoAttributeNum = (int)explode('_',trim($lastAutoAttribute))[1];

// Получаем массив проблемных продуктов
    $handle = fopen('problemProducts.txt', "r");
    $problemArticles =[];
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $problemArticles[] = trim($line);
        }
    }
    fclose($handle);

    // открытие файла для импортированных id
    $importedProductsHandle = fopen($importedProductsFile, 'a');
    $autoAttributeChangeHandle = fopen('autoAttributeChange.txt', 'a');

    try {

        // получаем файлы для импорта
        $filesList = array_diff(scandir('../share/'), array('..', '.'));


        // Получаем массив замен
        $attributesChange = json_decode(file_get_contents($attributesChangeFile), true);
        // Получаем существующие аттрибуты
        $wooAttributes = $woocommerce->get('products/attributes');

        // цикл по файлам
        foreach ($filesList as $fileList) {

            // получаем данные товаров
            $json = parse_json('./../share/' . $fileList);
            $data = getProductFromJson($json);


            foreach ($data as $k => $product) {

                // подготовка данных для импорта\обновления
                if (!empty($product['attributes'])) {
                    foreach ($product['attributes'] as &$attribute) {
                        // print_r($attribute['name']);

                        $attributeId = getAttributeId($attribute['name'], $wooAttributes);
                        if (!empty($attributeId)) {
                            $attribute['id'] = $attributeId;
                            // print_r('$attributeId: ' . $attributeId);
                        } else {

                            // делаем замену имени аттрибута для слага
                            if (!empty($attributesChange[$attribute['name']])) {
                                $slug = $attributesChange[$attribute['name']];
                            } else {
                                if (!empty($autoAttributeChange[$attribute['name']])) {
                                    $slug = $autoAttributeChange[$attribute['name']];
                                } else {
                                    // если аттрибут длинный, формируем искусственный и записываем в файл
                                    if(mb_strlen($attribute['name']) > 23) {
                                        $lastAutoAttributeNum +=1;
                                        $slug = 'ekfAttr_' . $lastAutoAttributeNum;
                                        fwrite($autoAttributeChangeHandle, $attribute['name'] . ':' . $slug . PHP_EOL);
                                    } else {
                                        // $slug = $attribute['name'];
                                        $slug = str_replace(['(', ')'], '', $attribute['name']);
                                    }
                                }
                            }
                            // var_dump($slug);
                            $attribute_data = array(
                                'name' => $attribute['name'],
                                'slug' => 'pa_' . mb_strtolower($slug),
                                'type' => 'select',
                                'order_by' => 'menu_order',
                                'has_archives' => true
                            );

                            // добавление аттрибутов
                            $wc_attribute = addAttribute($woocommerce, $attribute_data);
                            // $wc_attribute = $woocommerce->post('products/attributes', $attribute_data);

                            if ($wc_attribute) {
                                $wooAttributes = $woocommerce->get('products/attributes');
                                status_message('Добавлен аттрибут : ' . $attribute['name']);
                                $attribute['id'] = $wc_attribute->id;


                            } else {
                                break 3;
                            }
                        }
                    }
                }

                // var_dump($product);
                // var_dump($woocommerce->get('products?sku=' . $product['sku'])[0]->id);

                // если товар в массиве проблемных, берём следующий
                if (in_array($product['sku'], $problemArticles)) {

                    continue;
                }
                // если товара нет - импортируем его
                // var_dump($importedProducts['bf7622b4-d9ff-11e4-bb78-005056b80040']);
                if (empty($importedProducts[$product['_product_id']])) {

                    //создание товара в WooCommerce

                    // var_dump($product);
                    $wc_product = $woocommerce->post('products', $product);

                    if ($wc_product) {
                        status_message('Добавлен товар с ID: ' . $wc_product->id);
                        // добавляем товар в массив импортированных
                        $importedProducts[$product['_product_id']] = $wc_product->id;
                        fwrite($importedProductsHandle, $product['_product_id'] . ':' . $wc_product->id . PHP_EOL);
                    }


                } else { // если товар есть - обновляем его

                    $wc_product = $woocommerce->put('products/' . $importedProducts[$product['_product_id']], $product);
                    status_message('Обновлен товар с ID: ' . $wc_product->id);

                    // добавляем товар в массив импортированных
                    $importedProducts[$product['_product_id']] = $wc_product->id;

                }

                // echo(date('G:i:s') . ' Импорт товара финиш') . PHP_EOL;

            }
        }

        // записываем файл с импортированными id
        // status_message('записываем файл с импортированными id');
        // fwrite($jsonFile, json_encode($importedProducts, JSON_PRETTY_PRINT));
        // fclose($jsonFile);

    } catch (HttpClientException $e) {
        $errorMsg = $e->getMessage();
        echo $errorMsg;
        var_dump($e->getTrace());


        // if ($errorMsg == 'JSON ERROR: Syntax error') {
        //     $problemArticle = $e->getTrace()[1]['args'][2]['sku'];
        //     echo 'Проблемный артикул: ' . $problemArticle . PHP_EOL;
        //     $problemFile = fopen('problemProducts.txt', 'a');
        //     fwrite($problemFile, $problemArticle . PHP_EOL);
        //     fclose($problemFile);
        //     main($woocommerce);
        // } else {
        //     echo $errorMsg . PHP_EOL;
        //     $e->getTrace();
        //     $e->getFile();
        //     $e->getLine();
        //     // echo 'piu';
        // }


        // echo $e->getMessage() . PHP_EOL; // Error message
        // var_dump($e->getLine());
        // var_dump($e->getFile());
    }

    fclose($importedProductsHandle);
}

/**
 * существует ли аттрибут в woo
 * @param $attr
 * @param $woocommerce
 * @return bool
 */
function attributeExist($attr, $woocommerce)
{
    // echo $attr;
    // echo 'attributeExist';
    // echo PHP_EOL;
    $attributes = $woocommerce->get('products/attributes');

    // echo 'attribute for';
    foreach ($attributes as $attribute) {
        // print_r($attribute->slug);
        if ($attribute->name == $attr) {
            var_dump(json_encode($attribute, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
            // print_r($attribute->slug);
            // echo $attribute->slug;
            // echo PHP_EOL;
            return true;
        }
        // } else {
        //     return 'false';
        // }
        // print_r($attribute->slug . PHP_EOL);
    }
    return false;
}

/**
 * получить id аттрибута или false
 * @param $attr
 * @param $woocommerce
 * @return mixed
 */
function getAttributeId($attr, $attributes)
{
    foreach ($attributes as $attribute) {
        // echo $attribute->name . PHP_EOL;
        // echo $attr . PHP_EOL;
        if ($attribute->name == $attr) {
            return $attribute->id;
        }
    }
    return false;
}

function addAttribute($woocommerce, $attribute_data)
{
    try {
        $wc_attribute = $woocommerce->post('products/attributes', $attribute_data);
        return $wc_attribute;

    } catch (HttpClientException $e) {
        echo $e->getMessage() . PHP_EOL; // Error message
        print_r('Имя файла: ' . $e->getFile() . PHP_EOL);
        print_r('Номер строки: ' . $e->getLine() . PHP_EOL);
        print_r('поймали эксепшен на добавление аттрибута' . PHP_EOL);
        // print_r($e->getTrace());
        return false;
    }
}


/**
 * Получение товаров из json
 * @param $json
 * @return array
 */
function getProductFromJson($json)
{
    $product = array();
    foreach ($json as $key => $pre_product) {
        $product[$key]['_product_id'] = (string)$pre_product['product_id'];

        $product[$key]['name'] = (string)$pre_product['name'];
        $product[$key]['sku'] = (string)$pre_product['article'];

        // $product[$key]['description'] = (string)$pre_product['description'];
        $product[$key]['regular_price'] = (string)$pre_product['regular_price'];
        $product[$key]['categories'][]['id'] = $pre_product['categories'][0]['id'];

        if(!empty($pre_product['attributes']))
        {
            foreach ($pre_product['attributes'] as $attribute) {
                // var_dump($attribute);
                $attribute_name = $attribute['attribute_name'];
                $attribute_value = $attribute['attribute_value'];
                $attribute_id = $attribute['id'];

                $product[$key]['attributes'][] = array(
                    // 'id' => (int)$added_attributes[$attribute_name]['id'],
                    'id' => (string)$attribute_id,
                    'name' => (string)$attribute_name,
                    'options' => ['0' => (string)$attribute_value],
                    'visible' => 1
                );
            }
        }

        if(!empty($pre_product['images'])) {
            foreach ($pre_product['images'] as $image) {
                $product[$key]['images'][] = array(
                    'name' => $image['name'],
                    'src' => $image['src'],
                    'alt' => $image['alt'],
                );
            }
        }
    }
    // debugLog($product);
    return $product;
}

/**
 * Merge products and variations together.
 * Used to loop through products, then loop through product variations.
 *
 * @param  array $product_data
 * @param  array $product_variations_data
 * @return array
 */
function merge_products_and_variations($product_data = array(), $product_variations_data = array())
{
    foreach ($product_data as $k => $product) :
        foreach ($product_variations_data as $k2 => $product_variation) :
            if ($product_variation['_parent_product_id'] == $product['_product_id']) :

                // Unset merge key. Don't need it anymore
                unset($product_variation['_parent_product_id']);

                $product_data[$k]['variations'][] = $product_variation;

            endif;
        endforeach;

        // Unset merge key. Don't need it anymore
        unset($product_data[$k]['_product_id']);
    endforeach;

    return $product_data;
}

/**
 * Get products from JSON and make them ready to import according WooCommerce API properties.
 *
 * @param  array $json
 * @param  array $added_attributes
 * @return array
 */
function get_products_and_variations_from_json($json, $added_attributes)
{

    $product = array();
    $product_variations = array();

    foreach ($json as $key => $pre_product) :

        if ($pre_product['type'] == 'simple') :
            $product[$key]['_product_id'] = (string)$pre_product['product_id'];

            $product[$key]['name'] = (string)$pre_product['name'];
            $product[$key]['description'] = (string)$pre_product['description'];
            $product[$key]['regular_price'] = (string)$pre_product['regular_price'];

            // Stock
            $product[$key]['manage_stock'] = (bool)$pre_product['manage_stock'];

            if ($pre_product['stock'] > 0) :
                $product[$key]['in_stock'] = (bool)true;
                $product[$key]['stock_quantity'] = (int)$pre_product['stock'];
            else :
                $product[$key]['in_stock'] = (bool)false;
                $product[$key]['stock_quantity'] = (int)0;
            endif;

            foreach ($pre_product['attributes'] as $attribute) {
                // var_dump($attribute);
                $attribute_name = $attribute['attribute_name'];
                $attribute_value = $attribute['attribute_value'];
                $attribute_id = $attribute['id'];

                $product[$key]['attributes'][] = array(
                    // 'id' => (int)$added_attributes[$attribute_name]['id'],
                    // 'id' => (string)$attribute_id,
                    // 'name' => (string)$attribute_name,
                    // 'option' => (string)$attribute_value
                );
            }


        elseif ($pre_product['type'] == 'variable') :
            $product[$key]['_product_id'] = (string)$pre_product['product_id'];

            $product[$key]['type'] = 'variable';
            $product[$key]['name'] = (string)$pre_product['name'];
            $product[$key]['description'] = (string)$pre_product['description'];
            $product[$key]['regular_price'] = (string)$pre_product['regular_price'];

            // Stock
            $product[$key]['manage_stock'] = (bool)$pre_product['manage_stock'];

            if ($pre_product['stock'] > 0) :
                $product[$key]['in_stock'] = (bool)true;
                $product[$key]['stock_quantity'] = (int)$pre_product['stock'];
            else :
                $product[$key]['in_stock'] = (bool)false;
                $product[$key]['stock_quantity'] = (int)0;
            endif;

            $attribute_name = $pre_product['attribute_name'];

            $product[$key]['attributes'][] = array(
                'id' => (int)$added_attributes[$attribute_name]['id'],
                'name' => (string)$attribute_name,
                'position' => (int)0,
                'visible' => true,
                'variation' => true,
                'options' => $added_attributes[$attribute_name]['terms']
            );

        elseif ($pre_product['type'] == 'product_variation') :

            $product_variations[$key]['_parent_product_id'] = (string)$pre_product['parent_product_id'];

            $product_variations[$key]['description'] = (string)$pre_product['description'];
            $product_variations[$key]['regular_price'] = (string)$pre_product['regular_price'];

            // Stock
            $product_variations[$key]['manage_stock'] = (bool)$pre_product['manage_stock'];

            if ($pre_product['stock'] > 0) :
                $product_variations[$key]['in_stock'] = (bool)true;
                $product_variations[$key]['stock_quantity'] = (int)$pre_product['stock'];
            else :
                $product_variations[$key]['in_stock'] = (bool)false;
                $product_variations[$key]['stock_quantity'] = (int)0;
            endif;

            $attribute_name = $pre_product['attribute_name'];
            $attribute_value = $pre_product['attribute_value'];

            $product_variations[$key]['attributes'][] = array(
                'id' => (int)$added_attributes[$attribute_name]['id'],
                'name' => (string)$attribute_name,
                'option' => (string)$attribute_value
            );

        endif;
    endforeach;

    $data['products'] = $product;
    $data['product_variations'] = $product_variations;

    return $data;
}

/**
 * Get attributes and terms from JSON.
 * Used to import product attributes.
 *
 * @param  array $json
 * @return array
 */
function get_attributes_from_json($json)
{
    $product_attributes = array();

    foreach ($json as $key => $pre_product) :
        // var_dump($pre_product);
        if (!empty($pre_product['attributes'][0]['attribute_name']) && !empty($pre_product['attributes'][0]['attribute_value'])) :
            $product_attributes[$pre_product['attribute_name']]['terms'][] = $pre_product['attribute_value'];
        endif;
    endforeach;

    // var_dump($product_attributes);
    // die();
    return $product_attributes;

}

/**
 * Parse JSON file.
 *
 * @param  string $file
 * @return array
 */
function parse_json($file)
{
    $json = json_decode(file_get_contents($file), true);

    if (is_array($json) && !empty($json)) :
        return $json;
    else :
        die('An error occurred while parsing ' . $file . ' file.');

    endif;
}

/**
 * Print status message.
 *
 * @param  string $message
 * @return string
 */
function status_message($message)
{
    // debugLog($message, 'import.log');
    echo $message . PHP_EOL;
}

