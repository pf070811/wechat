<?php
namespace pfWechat\Code\Mp;

use pfWechat\Code\Support\BaseRequest;
/**
 * 素材
 */
class Media extends BaseRequest{

    //临时素材
    const API_TMP_MEDIA_UPLOAD = 'https://api.weixin.qq.com/cgi-bin/media/upload';

    /**
     * 上传临时素材
     * 媒体文件在微信后台保存时间为3天，即3天后media_id失效。
     */
    public function uploadTmp($mediaName, $mediaPath, $type='image')
    {
        $params[] = [
            'name' => $mediaName,
            'contents' => fopen($mediaPath, 'r'),
        ];
        $accessToken = $this->config['access_token'];
        $url = self::API_TMP_MEDIA_UPLOAD .'?access_token=' . $accessToken . '&type=' . $type;
        return $this->executeFormData($url , $params);
    }

}