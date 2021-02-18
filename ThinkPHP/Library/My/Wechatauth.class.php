<?php
/*
 * 微信授权相关接口
 */
namespace My;
class Wechatauth {

    //高级功能-》开发者模式-》获取
    private $app_id;
    private $app_secret;
    private $url_access_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={APPID}&secret={APPSECRET}';
    private $url_get_userinfo = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token={ACCESS_TOKEN}&openid={OPENID}&lang=zh_CN';
    private $url_get_userlist = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token={ACCESS_TOKEN}&next_openid={NEXT_OPENID}';
    private $url_get_authorize = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid={APPID}&redirect_uri={REDIRECT_URL}&response_type=code&scope={SCOPE}&state={STATE}#wechat_redirect';
    private $url_check_auth_access_token = 'https://api.weixin.qq.com/sns/auth?access_token={AUTH_ACCESS_TOKEN}&openid={OPENID}&lang=zh_CN';
    private $url_auth_access_token = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid={APPID}&secret={APPSECRET}&code={CODE}&grant_type=authorization_code';
    private $url_get_auth_userinfo = 'https://api.weixin.qq.com/sns/userinfo?access_token={AUTH_ACCESS_TOKEN}&openid={OPEN_ID}&lang=zh_CN';

    public function __construct() {
        $this->app_id = C('APP_ID');
        $this->app_secret = C('APP_SECRET');
    }
    /**
     * 获取微信授权链接
     *主要是为了获取code
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     * &response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect  snsapi_base
     */
    public function get_authorize_url($redirect_uri = '', $state = '',$scope='snsapi_userinfo'){
        $redirect_uri = urlencode($redirect_uri);
        return str_replace('{STATE}',$state,str_replace('{SCOPE}',$scope,str_replace('{REDIRECT_URL}',$redirect_uri,str_replace('{APPID}',$this->app_id,$this->url_get_authorize))));
    }

    /**
     * 验证授权
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function check_auth_access_token($auth_access_token = '', $open_id = ''){
        if($auth_access_token && $open_id){
            $info_url = str_replace('{OPENID}',$open_id,str_replace('{AUTH_ACCESS_TOKEN}',$auth_access_token,$this->url_check_auth_access_token));
            $info_data = $this->http($info_url,'get');
            if($info_data[0] == 200){
                return json_decode($info_data[1], true);
            }
        }
        return false;
    }

    /**
     * 获取授权access_token
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_auth_access_token($app_id = '', $app_secret = '', $code = ''){
        $token_url = str_replace('{CODE}',$code,str_replace('{APPSECRET}',$this->app_secret,str_replace('{APPID}',$this->app_id,$this->url_auth_access_token)));
        $token_data = $this->http($token_url,'get');
        if($token_data[0] == 200){
            return json_decode($token_data[1], true);
        }
        return false;
    }

    /**
     * 获取全局access_token
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token(){
        $token_url = str_replace('{APPSECRET}',$this->app_secret,str_replace('{APPID}',$this->app_id,$this->url_access_token));
        $token_data = $this->http($token_url,'get');
        if($token_data[0] == 200){
            return json_decode($token_data[1], true);
        }
        return false;
    }

    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_info($access_token = '', $open_id = ''){
        if($access_token && $open_id) {
            $info_url = str_replace('{OPENID}',$open_id,str_replace('{ACCESS_TOKEN}',$access_token,$this->url_get_userinfo));
            $info_data = $this->http($info_url,'get');
            if($info_data[0] == 200) {
                return json_decode($info_data[1], true);
            }
        }
        return false;
    }

    /**   private $url_get_userlist = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token={ACCESS_TOKEN}&next_openid={NEXT_OPENID}';
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_list($access_token = '', $next_openid = ''){
        if($access_token) {
            $info_url = str_replace('{NEXT_OPENID}',$next_openid,str_replace('{ACCESS_TOKEN}',$access_token,$this->url_get_userlist));
            $info_data = $this->http($info_url,'get');
            if($info_data[0] == 200) {
                return json_decode($info_data[1], true);
            }
        }
        return false;
    }


    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_auth_user_info($auth_access_token = '', $open_id = ''){
        if($auth_access_token && $open_id) {
            $info_url = str_replace('{OPENID}',$open_id,str_replace('{AUTH_ACCESS_TOKEN}',$auth_access_token,$this->url_get_auth_userinfo));
            $info_data = $this->http($info_url,'get');
            if($info_data[0] == 200) {
                return json_decode($info_data[1], true);
            }
        }
        return false;
    }




    //curl
    public function http($url, $method, $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }

    public function checkSignature($signature,$timestamp,$nonce){
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}