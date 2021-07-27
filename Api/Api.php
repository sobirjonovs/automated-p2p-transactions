<?php

namespace App\Api;

/**
 * Class Api
 */
abstract class Api
{
    /**
     * cURL options
     */
    public const
        TIMEOUT = 20,
        CONNECT_TIMEOUT = 20,
        POST_METHOD = 47,
        PUT_METHOD = 54,
        ENCODING = "gzip, deflate, br",
        RETURN_HEADER = 1,
        USER_AGENT = "Personal API",
        ENDPOINT_URL = "";
    /**
     * @var bool|string
     */
    public $content;

    /**
     * @param $url
     * @param $method
     * @param $params
     * @return bool|string
     */
    public function request($url, $method, $params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::ENDPOINT_URL . $url,
            CURLOPT_HEADER => self::RETURN_HEADER,
            CURLOPT_ENCODING => self::ENCODING,
            CURLOPT_HTTPHEADER => $params['header'] ?? '',
            $method => 1,
            CURLOPT_POSTFIELDS => $params['postfields'] ?? '',
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => self::USER_AGENT
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * @param string $url
     * @param array|string $postfields
     * @param array $headers
     * @return $this
     */
    protected function post(string $url, $postfields, array $headers = []): Api
    {
        $params = [
            'postfields' => $postfields,
            'header' => $headers
        ];
        $this->content = $this->request($url, self::POST_METHOD, $params);
        return $this;
    }

    /**
     * @param false $position
     * @return false|int|mixed
     */
    public function getContent(bool $position = false)
    {
        $line_position = strpos($this->content, "\r\n\r\n");
        if ($position) {
            return $line_position;
        }
        return json_decode(
            str_replace("\r\n\r\n", "", substr($this->content, $line_position)),
            true
        );
    }

    /**
     * @param $position
     * @return array
     */
    public function getHeader($position): array
    {
        $header = explode("\r\n", substr($this->content, 0, $position));
        $headers = [];
        foreach ($header as $item) {
            if (strpos($item, "HTTP") !== false) {
                $headers['status_code'] = 200;
                continue;
            }
            list($header_key, $header_value) = explode(": ", $item);
            $headers[$header_key] = $header_value;
        }
        return $headers;
    }
}