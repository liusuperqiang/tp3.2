<?php

namespace My;
class Ocr {
    function __construct(){

    }
    /**
     * 打印日志
     *
     * @param log 日志内容
     */
    function showlog($log){
        if($this->enabeLog){
            fwrite($this->Handle,$log."\n");
        }
    }

    /**
     * 发起HTTPS请求
     */
    function curl_post($url,$header,$method){
        $host = $this->Host;
        //初始化curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec ($curl);
        curl_close($curl);
        return $result;
    }



    /**
     * 接口OCR
     * @param $imgurl 图片地址
     */
    function OcrQuery($imgurl){

        // ?query=
        $result = array('statuscode' => 200, 'message' => '');
        if($imgurl==''){
            $result['statuscode'] = 300;
            $result['message'] = '请上传正确的图片！[ERROR70001]';
            return $result;
        }

        $method = 'GET';
        $host = 'http://pic.sogou.com/pic/ocr/ocrOnline.jsp';
        // 生成头
        $header = array();
        // 拼接请求包
        $querys = 'query='.$imgurl;
        $url = $host.'?'.$querys;
        // 发送请求
        $result = $this->curl_post($url,$header,$method);

        $result = json_decode($result,true);
        return $result;
    }
}
?>
