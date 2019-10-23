<?php
/**
 * Created by PhpStorm.
 * User: Pavel Morozov moroz0@gmail.com
 * Date: 15.10.2019
 * Time: 16:22
 */

class ekfClass
{
    private $apiKey;

    function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * получение файлов продукта: фотографии, документация и прочее
     * @param $vendorCode
     * @return mixed
     */
    public function getProductFiles($vendorCode)
    {
        $url   = 'https://ekfgroup.com/api/v1/ekf/catalog/files';
        $param = '?vendorCode=' . $vendorCode;

        // $properties = $this->getCurl($url . $param);

        // var_dump($properties->data[0]->properties[3]);
        return $this->getCurl($url . $param);
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
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => array(
                "Accept: application/json",
                "Authorization: Bearer $this->apiKey"
            ),
        ));


        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);


        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
}