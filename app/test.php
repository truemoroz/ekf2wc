<?php
/**
 * Created by PhpStorm.
 * User: moroz
 * Date: 02.03.19
 * Time: 13:37
 */

require __DIR__ . './../vendor/autoload.php';

require __DIR__ . './../core/functions.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$apiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE1NDBmNGE1ZjI1NGNmNWFjYzcyYTdlYTE1ZmI5NTI1NzE5YzA2MmI0NDEyNTM2ZjUxMTkwZmU0MjI5ODgyYTE4ZmFiNjM5OTI4MWNhY2NlIn0.eyJhdWQiOiIxIiwianRpIjoiYTU0MGY0YTVmMjU0Y2Y1YWNjNzJhN2VhMTVmYjk1MjU3MTljMDYyYjQ0MTI1MzZmNTExOTBmZTQyMjk4ODJhMThmYWI2Mzk5MjgxY2FjY2UiLCJpYXQiOjE1NDk0MzU5NzMsIm5iZiI6MTU0OTQzNTk3MywiZXhwIjoxNTgwOTcxOTEwLCJzdWIiOiI4MyIsInNjb3BlcyI6W119.OmPbBrx1hn2YWN1jM7IwRJLF_YYLZSDRyl6_u54yp33aACW91CU1zguElipUKljrbsZJ12JyhHMVL0GF1DnUopdlYSrcubHKA3jQ3Vj6ehu6G2hkXObRINX84mzDJtOQedcnGBa1NZiU-cBGwSk3QG799zxIDAnFqwxRG7yGIpbZqC1KOcGrMnad1CxX1b0NTIDq0LFNqY-nSBrwj93uwUjP8J50Bt6AZXtnEvC_y13LbA83AnV2kadbAQluAup2Lh-DVN1Yox0sp4O0cv3jtfgu2KFvRthnS7KvK9a0C9ai7q6IF1px9dLrB2J7km1cSbO-2gTOJ3NkFr91xgE9GJpG_1Q9CpYTv7JuFGPe3vLqS-JgZhYrw47gJ2FUe8v8kbgPWeGY_SueZk9aO5ROlTd60zzvK0Xeuq6nFBsQSfjbQ_VDPwh3kQXJ5BZiosr3JQHf-AARXD_9JAm4fY6br3o4tCqwYZw-syjM_UnZtvcUfm_g4tJI2AEkErm0fzFZnfIrq2X1EWvTqdhk0wRgcpC8CHtBp6FKe7NZktq8rv4C0FD8KhY6nineiwbO0neNOS3W0f8XQoTThHICzfIdHmc5jTYDf0k5aJD_H5yfu0i2EPx9dBXe_Tn6vSpK4K6KCJqthUSwxXbAHrlMCWrV_v9a3FF3NzJxJ7zURs5yQ5s';
$woocommerce = new Client(
    'https://armakom.net',
    'ck_abfc6cfe0e890d228d8c40494c7e2bd03fa6fae6',
    'cs_32c39e1669c8fe8e9232f720e445f6c9d5582ffb',
    [
        'wp_api' => true,
        'version' => 'wc/v3',//        'query_string_auth' => true
    ]
);


// debugLog(json_encode($woocommerce->get('products/attributes'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT), 'attributes.txt');

// $attributes = $woocommerce->get('products/attributes');
//
// foreach ($attributes as $attribute) {
//     print_r($attribute->slug . PHP_EOL);
// }

function attributeExist($attr, $woocommerce)
{

    $attributes = $woocommerce->get('products/attributes');

    foreach ($attributes as $attribute) {
        // print_r($attribute->slug);
        if ($attribute->slug == $attr) {
            // print_r($attribute->slug);
            return true;
        }
        // } else {
        //     return 'false';
        // }
        // print_r($attribute->slug . PHP_EOL);
    }
    return false;
}

function getProduct($id, $woocommerce) {

    $product = $woocommerce->get('products/' . $id);

    var_dump($product);
}

// function getPrice($id, $woocommerce)
// {
//     $price = $woocommerce->get('products/' . $id);
// }

function array2File() {

    $data = [
        'key1' => 'value1',
        'key2' => 'value2'
    ];

    $jsonFile = fopen('testArray.json', 'w');
    fwrite($jsonFile, json_encode($data, 480));
    fclose($jsonFile);
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
        if ($attribute->name == $attr) {
            return $attribute->id;
        }
    }
    return false;
}

/**
 * выполнение запроса
 * @param $url
 * @return mixed
 */
function getCurl($url)
{
    $apiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE1NDBmNGE1ZjI1NGNmNWFjYzcyYTdlYTE1ZmI5NTI1NzE5YzA2MmI0NDEyNTM2ZjUxMTkwZmU0MjI5ODgyYTE4ZmFiNjM5OTI4MWNhY2NlIn0.eyJhdWQiOiIxIiwianRpIjoiYTU0MGY0YTVmMjU0Y2Y1YWNjNzJhN2VhMTVmYjk1MjU3MTljMDYyYjQ0MTI1MzZmNTExOTBmZTQyMjk4ODJhMThmYWI2Mzk5MjgxY2FjY2UiLCJpYXQiOjE1NDk0MzU5NzMsIm5iZiI6MTU0OTQzNTk3MywiZXhwIjoxNTgwOTcxOTEwLCJzdWIiOiI4MyIsInNjb3BlcyI6W119.OmPbBrx1hn2YWN1jM7IwRJLF_YYLZSDRyl6_u54yp33aACW91CU1zguElipUKljrbsZJ12JyhHMVL0GF1DnUopdlYSrcubHKA3jQ3Vj6ehu6G2hkXObRINX84mzDJtOQedcnGBa1NZiU-cBGwSk3QG799zxIDAnFqwxRG7yGIpbZqC1KOcGrMnad1CxX1b0NTIDq0LFNqY-nSBrwj93uwUjP8J50Bt6AZXtnEvC_y13LbA83AnV2kadbAQluAup2Lh-DVN1Yox0sp4O0cv3jtfgu2KFvRthnS7KvK9a0C9ai7q6IF1px9dLrB2J7km1cSbO-2gTOJ3NkFr91xgE9GJpG_1Q9CpYTv7JuFGPe3vLqS-JgZhYrw47gJ2FUe8v8kbgPWeGY_SueZk9aO5ROlTd60zzvK0Xeuq6nFBsQSfjbQ_VDPwh3kQXJ5BZiosr3JQHf-AARXD_9JAm4fY6br3o4tCqwYZw-syjM_UnZtvcUfm_g4tJI2AEkErm0fzFZnfIrq2X1EWvTqdhk0wRgcpC8CHtBp6FKe7NZktq8rv4C0FD8KhY6nineiwbO0neNOS3W0f8XQoTThHICzfIdHmc5jTYDf0k5aJD_H5yfu0i2EPx9dBXe_Tn6vSpK4K6KCJqthUSwxXbAHrlMCWrV_v9a3FF3NzJxJ7zURs5yQ5s';
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
            "Authorization: Bearer $apiKey"
        ),
    ));


    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);


    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return json_decode($response);
    }
}

// print_r(attributeExist('pa_nomin-tok', $woocommerce));
// print_r(PHP_EOL);

// array2File();

// getProduct(2311, $woocommerce);

// print_r(array_diff(scandir( './ekf/products'), array('..', '.', 'not')));
// print_r(date('G:i:s')) . PHP_EOL;
// print_r('test', true);


// $wooAttributes = $woocommerce->get('products/attributes');
// $attributeId = getAttributeId('Описание', $wooAttributes);
// var_dump($attributeId);

// print_r($woocommerce->get('products?sku=mcb4763-1-10B-pro'));

// Получаем массив уже импортированных
// $importedProducts = json_decode(file_get_contents($importedProductsFile), true);


$attribute_data = array(
    'name' => 'Защитный контакт заземления',
    'slug' => 'pa_' . 'Защитный контакт заземления',
    'type' => 'select',
    'order_by' => 'menu_order',
    'has_archives' => true
);

// добавление аттрибутов
// $wc_attribute = $woocommerce->post('products/attributes', $attribute_data);
//
// var_dump($wc_attribute);

// $url = 'https://ekfgroup.com/api/v1/ekf/catalog/prices';
// $param = '?vendorCode=rcbo6-1pn-1B-30-a-av';

// $properties = $this->getCurl($url . $param);

// var_dump($properties->data[0]->properties[3]);
// var_dump(getCurl($url . $param));

// print_r($woocommerce->get('products/categories/704'));
// print_r($woocommerce->get('products/attributes/519'));

// 512 596

$param = ['page' => 7, 'per_page' => 80];
$categories = $woocommerce->get('products/categories', $param);
foreach ($categories as $category) {
    $categoryId = $category->id;
    print_r($categoryId . PHP_EOL);
    print_r($category->name . PHP_EOL);
    $productParam = ['category' => $categoryId];
    $products = $woocommerce->get('products', $productParam);
    // print_r($products[0]->id . PHP_EOL);
    $imgSrc = (string)$products[0]->images[0]->src;
    // print_r($imgSrc . PHP_EOL);

    $data = [
        'image' => [
            'src' => $imgSrc
        ]
    ];
    // $data = [];
    // $data = [
    //     'description' => 'All kinds of clothes.'
    // ];
    $categoryString =  'products/categories/' . (string)$categoryId;
    $woocommerce->put($categoryString, $data);
    // print_r($woocommerce->put('products/categories/389', $data));
    // break;
}

// for ($i=521; $i<597; $i++) {
//     print_r($woocommerce->delete('products/attributes/' . $i));
// }
