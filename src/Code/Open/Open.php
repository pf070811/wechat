<?php
namespace pfWechat\Code\Open;

use pfWechat\Code\Support\BaseRequest;
use Exception;
use pfWechat\Code\Api;
use pfWechat\Core\Support\Encryptor;
/**
 * open 平台授权
 */

class Open extends BaseRequest{

    protected  $wechatNotifyParams = [];

    /**
     * 接受通知信息并解析
     */
    public function messageHandler($callBack)
    {
        $token = $this->config['token'];
        $AESKey = $this->config['aes_key'];
        $appId = $this->config['app_id'];

        $params = $this->wechatNotifyParams;

        $Encryptor = new Encryptor($appId, $token, $AESKey);
        $decodeMessage = $Encryptor->decryptMsg($params['msg_signature'], $params['nonce'], $params['timestamp'], $params['message']);

        if (is_object($callBack))
        {
            $callBack($decodeMessage, $params);
        } else {
            throw new Exception('callback is not object');
        }
    }
    /**
     * 获取参数
     */
    public function setWechatNotifyParams($inputData)
    {
        $params = [
            'encrypt_type' => '',
            'msg_signature' => '',
            'nonce' => '',
            'signature' => '',
            'timestamp' => '',
        ];
        $message = file_get_contents("php://input");
        $inputData['message'] = $message;

        $this->wechatNotifyParams = array_merge($params, $inputData);
    }

    /**
     * 获取第三方平台component_access_token
     */
    public function getComponentAccessToken($componentverifyTicket)
    {
        $params = [
            'component_appid' => $this->config['app_id'],
            'component_appsecret' => $this->config['secret'],
            'component_verify_ticket' => $componentverifyTicket,
        ];
        $url = Api::API_COMPONENT_TOKEN;
        $data = $this->execute('post', $url, $params);
        if (isset($data['errcode']) && $data['errcode'] > 0)
        {
            throw new Exception($data['errmsg']);
        }
        return $data;
    }
    /**
     * 获取预授权码pre_auth_code
     */
    public function getPreAuthCode($componentAccessToken)
    {
        $params = [
            'component_appid' => $this->config['app_id'],
        ];
        $url = Api::API_CREATE_PREAUTHCODE . '?component_access_token=' . $componentAccessToken;
        $data = $this->execute('post', $url, $params);
        return $data;
    }
    /**
     * 授权注册页面扫码授权
     */
    public function getAuthPageUrl($preAuthCode, $callbackUrl)
    {
        $url = Api::API_COMPONENTLOGINPAGE . "?component_appid=" . $this->config['app_id'];
        $url .= '&pre_auth_code=' . $preAuthCode;
        $url .= '&redirect_uri=' . $callbackUrl;
        $url .= '&auth_type=3';
        return $url;
    }
    /**
     * 使用授权码换取公众号或小程序的接口调用凭据和授权信息
     */
    public function getAuthInfo($authorizationCode, $componentAccessToken)
    {
        $params = [
            'component_appid' => $this->config['app_id'],
            'authorization_code' => $authorizationCode,
        ];
        $url = Api::API_QUERY_AUTH . '?component_access_token=' . $componentAccessToken;
        $data = $this->execute('post', $url, $params);
        return $data;
    }
    /**
     * 获取授权方的帐号基本信息
     */
    public function getAuthorizerInfo($authorizerAppid, $componentAccessToken)
    {
        $params = [
            'component_appid' => $this->config['app_id'],
            'authorizer_appid' => $authorizerAppid,
        ];
        $url = Api::API_GET_AUTHORIZER_INFO . '?component_access_token=' . $componentAccessToken;
        $data = $this->execute('post', $url, $params);
        return $data;
    }
    /**
     * 获取授权方的帐号基本信息
     */
    public function getAuthorizerToken($authorizerAppid, $authorizerRefreshToken, $componentAccessToken)
    {
        $params = [
            'component_appid' => $this->config['app_id'],
            'authorizer_appid' => $authorizerAppid,
            'authorizer_refresh_token' => $authorizerRefreshToken,
        ];
        $url = Api::API_AUTHORIZER_TOKEN . '?component_access_token=' . $componentAccessToken;
        $data = $this->execute('post', $url, $params);
        return $data;
    }
}
