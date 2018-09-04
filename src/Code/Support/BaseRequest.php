<?php

/**
 * Created by PhpStorm.
 * User: wangpenghai
 * Date: 2018/8/2
 * Time: 上午11:54
 */
namespace pfWechat\Code\Support;

use pfWechat\Application;

class BaseRequest
{
    /**
     * Guzzle client default settings.
     *
     * @var array
     */
    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->httpClient = $app->httpRequest();
        $this->config = $app->getConfig();
    }

    /**
     * 请求
     */
    public function execute($method, $url, $params)
    {
        $method = strtoupper($method);
        if (!in_array($method, ['POST', 'GET']))
        {
            throw new Exception('只支持 POST GET 请求！');
        }

        $options = [];
        if (!empty($params))
        {
            if ($method === 'POST')
            {
                $body = is_array($params)?json_encode($params, JSON_UNESCAPED_UNICODE):$params;
                $options = [
                    'body' => $body,
                    'headers' => ['content-type' => 'application/json;charset=utf-8'],
                ];
            } else {
                $options = ['query' => $params];
            }
        }

        $options = array_merge(self::$defaults, $options);
        $response = $this->httpClient->request($method, $url, $options);
        $code = $response->getStatusCode();
        if ($code !== 200)
        {
            throw new Exception('http:' . $code);
        }
        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * 上传附件
     */
    public function executeFormData($url, $params)
    {
        $options = [];
        if (is_array($params) && !empty($params))
        {
            $options = ['multipart' => $params];
        }
        $response = $this->httpClient->request('POST', $url, $options);
        $code = $response->getStatusCode();
        if ($code !== 200)
        {
            throw new Exception('http:' . $code);
        }
        $body = $response->getBody();

        return json_decode($body, true);
    }

}