<?php
/**
* urgentcargus short summary.
*
* urgentcargus description.
*
* @version 1.1
*/
class UrgentCargusClass {
    private $key;
    private $curl;
    public $url;

    function __construct() {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    function SetKeys($url, $key) {
        $this->url = $url;
        $this->key = $key;
    }

    function CallMethod($function, $parameters = '', $verb, $token = null) {
        $json = json_encode($parameters);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($this->curl, CURLOPT_URL, $this->url . '/' . $function);

        if ($function == 'LoginUser') {
            $headers = array (
                'Ocp-Apim-Subscription-Key: '.$this->key,
                'Ocp-Apim-Trace: true',
                'Content-Type: application/json',
                'ContentLength: '.strlen($json)
            );
        } else {
            $headers = array (
                'Ocp-Apim-Subscription-Key: '.$this->key,
                'Ocp-Apim-Trace: true',
                'Authorization: Bearer '.$token,
                'Content-Type: application/json',
//                'Content-Length: '.strlen($json)
            );
            if ($function == 'Awbs' && $verb == 'POST') {
                $headers[] = 'path: WP';
            }
        }

        curl_setopt(
            $this->curl,
            CURLOPT_HTTPHEADER,
            $headers
        );

        $result = curl_exec($this->curl);
        $header = curl_getinfo($this->curl);

        $data = json_decode($result, true);
        $status = $header['http_code'];

        if ($status == '200') {
            if (is_array($data) && isset($data['message'])) {
                return $data['message'];
            } else {
                return $data;
            }
        } else {
            return $data;
        }
    }
}