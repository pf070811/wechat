<?php

/**
 * Created by PhpStorm.
 * User: wangpenghai
 */
namespace pfWechat;

use Exception;
use GuzzleHttp\Client;

class Application
{
    /**
     * 实例容器
     */
    protected $instanceMap = [];
    /**
     * 配置文件
     */
    protected $config = [
        'app_id' => '',
        'aes_key' => '',
        'token' => '',
        'access_token' => '',
    ];

    /**
     * @var array
     */
    protected $providers = [
        'Open' => Code\Open\Open::class,
        'User' => Code\Mp\User::class,
        'Media' => Code\Mp\Media::class,
        'Message' => Code\Mp\Message::class,
    ];

    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
        if (empty($this->config['access_token']))
        {
            $tokenInfo = $this->getTokenInfo();
            $token = $tokenInfo['access_token']??'';
            $this->setAccessToken($token);
        }
    }
    /**
     * Application setToken
     *
     * @return array
     */
    public function setAccessToken($token)
    {
        if (!empty($token))
        {
            $this->config['access_token'] = $token;
        }
    }
    /**
     * Application getTokenInfo
     *
     * @return array
     */
    public function getTokenInfo()
    {
        return $this->token->get();
    }
    /**
     * Application Config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->instanceMap[$key]) && is_object($this->instanceMap[$key]))
        {
            return $this->instanceMap[$key];
        }

        $provider = $this->providers[$key]??'';
        if (!empty($provider))
        {
            $this->instanceMap[$key] = new $provider($this);
            return $this->instanceMap[$key];
        } else {
            throw new Exception('not found ' . $key . "\n");
        }
    }

    public function httpRequest()
    {
        if (isset($this->instanceMap['httpRequest']) && is_array($this->instanceMap['httpRequest']))
        {
            return $this->instanceMap['httpRequest'];
        }
        return $this->instanceMap['httpRequest'] = new Client([
            'base_uri' => $this->config['oapi_host'],
        ]);
    }
}