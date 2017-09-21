<?php

namespace YaMoney\Client;


use YaMoney\Common\Exceptions\ApiConnectionException;
use YaMoney\Common\Exceptions\AuthorizeException;
use YaMoney\Common\ResponseObject;

/**
 * Class StreamClient
 * @package YaMoney\Client
 */
class StreamClient implements ApiClientInterface
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @var int
     */
    private $timeout = 30;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $shopPassword;

    /**
     * @var string
     */
    public $responseHeaders;

    /**
     * @var string
     */
    public $responseBody;

    /**
     * @inheritdoc
     */
    public function call($path, $method, $queryParams, $httpBody = null, $headers = array())
    {
        $url = $this->getUrl() . $path;

        $options = array(
            'http' => array(
                'method' => $method,
                'header' => $this->prepareHeaders($headers),
                'content' => $httpBody,
                'timeout' => $this->timeout,
                'ignore_errors' => true
            ),
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
            ),
        );

        $this->initStream($options);
        if ($this->sendRequest($url)) {
            $httpHeaders = array_map(function ($header) {
                return explode(':', $header);
            }, $this->responseHeaders);
            $httpBody = $this->responseBody;

            return new ResponseObject(array(
                'headers' => $httpHeaders,
                'body' => $httpBody
            ));
        } else {
            throw new ApiConnectionException('Stream returned an empty response', 660);
        }
    }

    /**
     * @param $url
     * @return bool
     */
    public function sendRequest($url)
    {
        $response = $this->fileGetContents($url);
        $this->setData($response);
        return (bool)$response;
    }

    /**
     * @param array $headers
     * @return string
     */
    public function prepareHeaders($headers)
    {
        $headers = array_merge($this->getDefaultHeaders(), $headers);

        $headersPrepared = array_map(function ($key, $value) {
            return $key . ":" . $value;
        }, array_keys($headers), $headers);

        return implode("\r\n", $headersPrepared);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param array $options
     */
    public function initStream($options)
    {
        $this->stream = stream_context_create($options);
    }

    /**
     * @param $url
     * @return bool|string
     */
    private function fileGetContents($url)
    {
        $fp = fopen($url, 'rb', false, $this->stream);
        $metadata = stream_get_meta_data($fp);
        $response = stream_get_contents($fp);
        return $response;
    }

    /**
     * @param $response
     */
    private function setData($response)
    {
        $this->responseBody = $response;
        $this->responseHeaders = $http_response_header ?: array();
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        $config = $this->config;
        return $config['url'];
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopPassword()
    {
        return $this->shopPassword;
    }

    /**
     * @param string $shopPassword
     * @return $this
     */
    public function setShopPassword($shopPassword)
    {
        $this->shopPassword = $shopPassword;
        return $this;
    }

    /**
     * @return array
     */
    private function getDefaultHeaders()
    {
        return array(
            'Authorization' => $this->generateAuth(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        );
    }

    /**
     * @return string
     * @throws AuthorizeException
     */
    private function generateAuth()
    {
        if (!$this->shopId || !$this->shopPassword) {
            throw new AuthorizeException('shopId or shopPassword not set');
        }
        return 'Basic ' . base64_encode($this->shopId . $this->shopPassword);
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}