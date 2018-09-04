<?php
/**
 * Created by PhpStorm.
 * User: wangpenghai
 * Date: 2018/08/01
 * Time: 上午11:22
 */
namespace pfWechat\Code;


class Api {

    const API_REFRESH_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';
    //获取第三方平台component_access_token
    const API_COMPONENT_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    //授权注册页面扫码授权
    const API_COMPONENTLOGINPAGE = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage';
    //获取预授权码pre_auth_code
    const API_CREATE_PREAUTHCODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
    //使用授权码换取公众号或小程序的接口调用凭据和授权信息
    const API_QUERY_AUTH = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';
    //获取授权方的帐号基本信息
    const API_GET_AUTHORIZER_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
    //5、获取（刷新）授权公众号或小程序的接口调用凭据（令牌）
    const API_AUTHORIZER_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';
}