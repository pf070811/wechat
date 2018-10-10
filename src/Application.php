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
        'app_id'   => '',
        'secret'   => '',
        'token'    => '',
        'aes_key'  => '',
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
        'QrCode' => Code\Mp\QrCode::class,
    ];

    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Application Config
     *
     * @return array
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
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

    /**
     * http Client
     */
    public function httpRequest()
    {
        if (isset($this->instanceMap['httpRequest']) && is_array($this->instanceMap['httpRequest']))
        {
            return $this->instanceMap['httpRequest'];
        }
        return $this->instanceMap['httpRequest'] = new Client();
    }
}