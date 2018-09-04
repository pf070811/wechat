<?php
/**
 * Created by PhpStorm.
 * User: wangpenghai
 * Date: 2018/08/04
 * Time: 下午2:45
 */

namespace pfWechat\Core\Mp;

use pfWechat\Code\Support\BaseRequest;
/**
 * 二维码
 */
class QrCode extends BaseRequest{

    const API_QRCODE_CREATE = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';

    const API_SHOW = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';

    /**
     * 临时二维码
     */
    public function createSceneQr($sceneId, $expireSeconds=1800)
    {
        $params = [
            'expire_seconds' => $expireSeconds,
            'action_name' => 'QR_SCENE',
            'action_info' => [
                'scene'=>['scene_id'=>$sceneId]
            ],
        ];
        $accessToken = $this->config['access_token'];
        $url = self::API_QRCODE_CREATE . '?access_token=' . $accessToken;
        return $this->execute('post',$url, $params);
    }

    /**
     * 二维码链接
     */
    public function url($ticket)
    {
        return self::API_SHOW."?ticket={$ticket}";
    }

}