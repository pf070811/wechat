<?php
namespace pfWechat\Code\Mp;

use pfWechat\Code\Support\BaseRequest;
/**
 * 客服消息
 */
class Message extends BaseRequest{

    //客服消息
    const API_SEND = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
    //模板消息
    const API_TEMPLATE_SEND = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

    /**
     * 客服接口-发消息
     */
    public function sendText($openId, $content)
    {
        $accessToken = $this->config['access_token'];
        $params = '{"touser":"'.$openId.'","msgtype":"text","text":{"content":"'.$content.'"}}';
        $url = self::API_SEND .'?access_token=' . $accessToken;
        return $this->execute('POST', $url , $params);
    }

    /**
     * 客服接口-发图片消息
     */
    public function sendImage($openId, $mediaid)
    {
        $params = [
            'touser' => $openId,
            'msgtype' => 'image',
            'image' => [
                'media_id' => $mediaid,
            ],
        ];
        $accessToken = $this->config['access_token'];
        $url = self::API_SEND .'?access_token=' . $accessToken;
        return $this->execute('POST', $url , $params);
    }

    /**
     * 发模板消息
     */
    public function sendTemplate($params)
    {
        $accessToken = $this->config['access_token'];
        $url = self::API_TEMPLATE_SEND .'?access_token=' . $accessToken;
        return $this->execute('POST', $url , $params);
    }
}