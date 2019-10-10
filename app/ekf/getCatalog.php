<?php
/**
 * Created by PhpStorm.
 * User: moroz
 * Date: 13.02.19
 * Time: 21:18
 */

// require __DIR__ . './../../core/functions.php';

class Ekf
{

    private $apiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE1NDBmNGE1ZjI1NGNmNWFjYzcyYTdlYTE1ZmI5NTI1NzE5YzA2MmI0NDEyNTM2ZjUxMTkwZmU0MjI5ODgyYTE4ZmFiNjM5OTI4MWNhY2NlIn0.eyJhdWQiOiIxIiwianRpIjoiYTU0MGY0YTVmMjU0Y2Y1YWNjNzJhN2VhMTVmYjk1MjU3MTljMDYyYjQ0MTI1MzZmNTExOTBmZTQyMjk4ODJhMThmYWI2Mzk5MjgxY2FjY2UiLCJpYXQiOjE1NDk0MzU5NzMsIm5iZiI6MTU0OTQzNTk3MywiZXhwIjoxNTgwOTcxOTEwLCJzdWIiOiI4MyIsInNjb3BlcyI6W119.OmPbBrx1hn2YWN1jM7IwRJLF_YYLZSDRyl6_u54yp33aACW91CU1zguElipUKljrbsZJ12JyhHMVL0GF1DnUopdlYSrcubHKA3jQ3Vj6ehu6G2hkXObRINX84mzDJtOQedcnGBa1NZiU-cBGwSk3QG799zxIDAnFqwxRG7yGIpbZqC1KOcGrMnad1CxX1b0NTIDq0LFNqY-nSBrwj93uwUjP8J50Bt6AZXtnEvC_y13LbA83AnV2kadbAQluAup2Lh-DVN1Yox0sp4O0cv3jtfgu2KFvRthnS7KvK9a0C9ai7q6IF1px9dLrB2J7km1cSbO-2gTOJ3NkFr91xgE9GJpG_1Q9CpYTv7JuFGPe3vLqS-JgZhYrw47gJ2FUe8v8kbgPWeGY_SueZk9aO5ROlTd60zzvK0Xeuq6nFBsQSfjbQ_VDPwh3kQXJ5BZiosr3JQHf-AARXD_9JAm4fY6br3o4tCqwYZw-syjM_UnZtvcUfm_g4tJI2AEkErm0fzFZnfIrq2X1EWvTqdhk0wRgcpC8CHtBp6FKe7NZktq8rv4C0FD8KhY6nineiwbO0neNOS3W0f8XQoTThHICzfIdHmc5jTYDf0k5aJD_H5yfu0i2EPx9dBXe_Tn6vSpK4K6KCJqthUSwxXbAHrlMCWrV_v9a3FF3NzJxJ7zURs5yQ5s';

//$result = file_get_contents($url);


//$ch = curl_init();
//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $apiKey"));
//curl_setopt($ch, CURLOPT_URL, url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//$data = curl_exec($ch);
//curl_close($ch);
//if ($curl_errno == 0) {
//    // $data received in $data
//}


    /**
     * получение товара
     * @param $vendorCode
     * @return mixed
     */
    public function getProduct($vendorCode)
    {

        $url = 'https://ekfgroup.com/api/v1/ekf/catalog/products';
        $param = '?vendorCode=' . $vendorCode;

        return $this->getCurl($url . $param);

    }

    /**
     * получение характеристик товара
     * @param $vendorCode
     * @return mixed
     */
    public function getProductProperties($vendorCode)
    {
        $url = 'https://ekfgroup.com/api/v1/ekf/catalog/properties';
        $param = '?vendorCode=' . $vendorCode;

        // $properties = $this->getCurl($url . $param);

        // var_dump($properties->data[0]->properties[3]);
        return $this->getCurl($url . $param);
    }

    /**
     * получение файлов продукта: фотографии, документация и прочее
     * @param $vendorCode
     * @return mixed
     */
    public function getProductFiles($vendorCode)
    {
        $url = 'https://ekfgroup.com/api/v1/ekf/catalog/files';
        $param = '?vendorCode=' . $vendorCode;

        // $properties = $this->getCurl($url . $param);

        // var_dump($properties->data[0]->properties[3]);
        return $this->getCurl($url . $param);
    }

    public function getProductPrice($vendorCode)
    {
        $url = 'https://ekfgroup.com/api/v1/ekf/catalog/prices';
        $param = '?vendorCode=' . $vendorCode;

        // $properties = $this->getCurl($url . $param);

        // var_dump($properties->data[0]->properties[3]);
        return $this->getCurl($url . $param);
    }

    public function getGroup($id, $parentId, $name)
    {
        $url = 'https://ekfgroup.com/api/v1/ekf/catalog/product-groups?';

        $param = '';
        if (!empty($id)) {
            $param = 'id=' . $id;
        }
        if (!empty($parentId)) {
            $param = 'parentId=' . $parentId;
        }
        if (!empty($name)) {
            $param = 'name="' . $name . '"';
        }

        if (empty($param)) {
            return 'Give the param!';
        }

//        die($url . $param);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $param,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: Bearer $this->apiKey"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
//            echo $response;
            var_dump(json_decode($response));
        }
    }

    /**
     * Парсим файл с артикулами группы
     * @param $file
     */
    public function parseGroupFile($file)
    {

        // получаем id категории из имени файла

        preg_match('/(?<=cat)(.*?)(?=.txt)/m', $file, $match);
        $fileCategory = $match[1];
        // echo $fileCategory . PHP_EOL;
        // return false;

        $handle = fopen('./../ekf/products/' . $file, "r");
        $targetJsonFile = './../share/' . pathinfo($file)['filename'] . '.json';
        // echo  $targetJsonFile . PHP_EOL;
        // return false;
        // $targetJsonFile = 'group1-1.json';

        // unlink($targetJsonFile);
        $jsonFile = fopen($targetJsonFile, 'w');
        $data = [];
        $iteration = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $vendorCode = trim($line);
                $product = $this->getProduct($vendorCode);

                // если по этому артикулу отдают информацию
                if(!empty($product->data[0])) {
                    $properties = $this->getProductProperties($vendorCode);
                    $files = $this->getProductFiles($vendorCode);
                    $prices = $this->getProductPrice($vendorCode);
                    $data[$iteration]['product_id'] = $product->data[0]->id;
                    $data[$iteration]['article'] = $vendorCode;
                    $data[$iteration]['name'] = $product->data[0]->name;
                    $data[$iteration]['type'] = 'simple';
                    $data[$iteration]['categories'][]['id'] = $fileCategory;

                    foreach ($prices->data[0]->prices as $key => $price) {
                        if ($price->price_type->name == 'РРЦ') {
                            $data[$iteration]['regular_price'] = round_up($price->price, 0);
                            // $data[$iteration]['price'] = (string)$price->price;
                        }
                    }

                    // присваивание аттрибутов
                    foreach ($properties->data[0]->properties as $key => $property) {
                        $data[$iteration]['attributes'][$key]['attribute_name'] = $property->name;
                        $data[$iteration]['attributes'][$key]['attribute_value'] = $property->etim_value;
                        $data[$iteration]['attributes'][$key]['id'] = $property->id;
                    }

                    // присваивание файлов
                    $i = 0;
                    foreach ($files->data[0]->files as $key => $file) {
                        if ($file->name == 'Основное изображение' || $file->name == 'Дополнительное изображение') {
                            $data[$iteration]['images'][$i]['src'] = $file->file;
                            $data[$iteration]['images'][$i]['name'] = $file->name;
                            $data[$iteration]['images'][$i]['alt'] = $file->name;
                            $i++;
                        }
                    }
                }

                $iteration++;

            }

            fclose($handle);
        } else {
            echo 'error opening the file' . PHP_EOL;
            // error opening the file.
        }

        fwrite($jsonFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
        fclose($jsonFile);
        echo PHP_EOL;
    }

    /**
     * выполнение запроса
     * @param $url
     * @return mixed
     */
    public function getCurl($url)
    {
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
                "Authorization: Bearer $this->apiKey"
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

}




$ekfObj = new Ekf();
$filesList = array_diff(scandir( './../ekf/products'), array('..', '.', 'not'));
// // основной парсинг файла с группой артикулов
foreach ($filesList as $fileList) {
    $ekfObj->parseGroupFile($fileList);
}


// // $ekfObj->parseGroupFile('group_test.txt');
//
// // var_dump($filesList);
// // die();
//


// запись свойств продукта в файл
// $targetJsonFile = 'properties.json';
// $jsonFile = fopen($targetJsonFile, 'w');
// fwrite($jsonFile, json_encode($ekfObj->getProductProperties('mcb6-1-01B-av'), JSON_PRETTY_PRINT));
// fclose($jsonFile);
// echo PHP_EOL;


// $ekfObj->getProductProperties('mcb6-1-01B-av');


// var_dump($ekfObj->getProductPrice('mcb6-1-01B-av'));
//$ekfObj->getProduct('mcb6-1-16C-av');
//$ekfObj->getProduct(null);
//$ekfObj->getGroup(null, null, '01.01 Автоматические выключатели серии AV-6 AVERES');
//$ekfObj->getGroup(null, 'e9935bb2-c971-11e4-bfc3-005056b80040', null);
//$ekfObj->getGroup(null,'130f396f-ef8f-11d8-8882-505054503030', null);
//$ekfObj->getGroup(null,'7783dde3-608e-11e7-80cd-0cc47a0cbffe', null);
