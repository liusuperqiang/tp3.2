<?php
namespace My;

class Srcexpressapi {
    private $appid;
    private $appkey;
    private $createorder_url;
    private $updateorder_url;
    private $getdeliverstatus_url;
    private $gettrackdata_url;
    function __construct(){
        $appid = 'gt20188459641723';
        $appkey = '8Ae0CyIL2BDWneV62716YqV5jLJiZ5Qd';
        $createorder_url = 'https://vmxian.wicp.net/openapi/v1/createorder.html';
        $updateorder_url = 'https://vmxian.wicp.net/openapi/v1/updateorder.html';
        $getdeliverstatus_url = 'https://vmxian.wicp.net/openapi/v1/getdeliverstatus.html';
        $gettrackdata_url = 'https://vmxian.wicp.net/openapi/v1/gettrackdata.html';

        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->createorder_url = $createorder_url;
        $this->updateorder_url = $updateorder_url;
        $this->getdeliverstatus_url = $getdeliverstatus_url;
        $this->gettrackdata_url = $gettrackdata_url;
    }

    /**
     * 发起HTTPS请求
     */
    function curl_post($url,$xml,$second = 10){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //print_r($data);

        if($data){
            curl_close($ch);
            //$rsxml = simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);
            //$xmlArr = json_decode(json_encode($rsxml),true);
            return $data;
        }else{
            curl_close($ch);
            return curl_errno($ch);
        }
    }

//签名运算算法
    private function getSign($arr){
        ksort($arr); //按照键名排序
        $sign_raw = '';
        foreach($arr as $k => $v){
            if($v!==''){
                $sign_raw .= $k.'='.$v.'&';
            }
        }
        $appkey = $this->appkey;//系统分配的APPKEY，参与sign字段的运算，不直接传值  c3OPoDGoR0yOyYmndzJbaiHbCERVVX   yzfndLAqNn7Q2X8MG4z8YLKQEUWVZYnR
        $sign_raw .= 'key='.$appkey;
        //print_r($sign_raw);
        return strtoupper(md5($sign_raw));
    }

//将数组转换为XML
    private function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<".$key.">".$val."</".$key.">";
            }else{
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //生成随机字符串，默认32位
    private function create_noncestr($length = 32){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i <$length; $i++){
            $str .= substr($chars,mt_rand(0,strlen($chars)-1),1);
        }
        return $str;
    }

    function Getdeliverstatus($obj){
        $result = array('statuscode' => 200, 'message' => '');

        $obj['nonce_str'] = $this->create_noncestr();
        $obj['appid']  = $this->appid;
        $sign = $this->getSign($obj);
        $obj['sign'] = $sign;

        $postXml = $this->arrayToXml($obj);
        $responsedata = $this->curl_post($this->getdeliverstatus_url,$postXml);
        return $responsedata;
    }

    function Updateorder($obj){
        $result = array('statuscode' => 200, 'message' => '');

        $obj['nonce_str'] = $this->create_noncestr();
        $obj['appid']  = $this->appid;
        $sign = $this->getSign($obj);
        $obj['sign'] = $sign;

        $postXml = $this->arrayToXml($obj);
        $responsedata = $this->curl_post($this->updateorder_url,$postXml);
        return $responsedata;
    }

    function Gettrackdata($obj){
        $result = array('statuscode' => 200, 'message' => '');

        $obj['nonce_str'] = $this->create_noncestr();
        $obj['appid']  = $this->appid;
        $sign = $this->getSign($obj);
        $obj['sign'] = $sign;

        $postXml = $this->arrayToXml($obj);
        $responsedata = $this->curl_post($this->gettrackdata_url,$postXml);
        return $responsedata;
    }

}

