<?php
/**
 * Created by PhpStorm.
 * User: wangpenghai
 * Date: 2018/08/04
 * Time: 下午2:45
 */

namespace pfWechat\Code\Mp;

use pfWechat\Code\Support\BaseRequest;
/**
 * 获取用户基本信息(UnionID机制)
 */
class User extends BaseRequest{

    const API_GET = 'https://api.weixin.qq.com/cgi-bin/user/info';
    const API_BATCH_GET = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget';
    const API_LIST = 'https://api.weixin.qq.com/cgi-bin/user/get';
    const API_GROUP = 'https://api.weixin.qq.com/cgi-bin/groups/getid';
    const API_REMARK = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark';
    const API_OAUTH_GET = 'https://api.weixin.qq.com/sns/userinfo';
    const API_GET_BLACK_LIST = 'https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist';
    const API_BATCH_BLACK_LIST = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist';
    const API_BATCH_UNBLACK_LIST = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist';

    /**
     * 获取用户基本信息（包括UnionID机制）
     */
    public function get($openid, $lang='zh_CN')
    {
        $params = [
            'access_token' => $this->config['access_token'],
            'openid' => $openid,
            'lang' => $lang
        ];
        return $this->execute('get', self::API_GET, $params);
    }
}