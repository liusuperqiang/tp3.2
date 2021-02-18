<?php

namespace My;
//use function GuzzleHttp\Psr7\str;

class Tongtoolapi {
    private $accessKey;
    private $secretAccessKey;
    private $url_apptoken;
    private $url_openid;
    private $url_createorder;
    private $url_checkstock;
    private $url_checktrackno;

    function __construct(){
        $accessKey = C('tt_accessKey');
        $secretAccessKey = C('tt_secretAccessKey');
        $url_apptoken = C('tt_url_apptoken');
        $url_openid = C('tt_url_openid');
        $url_createorder = C('tt_url_createorder');
        $url_checkstock = C('tt_url_checkstock');
        $url_checktrackno = C('tt_url_checktrackno');

        $this->accessKey = $accessKey;
        $this->secretAccessKey = $secretAccessKey;
        $this->url_apptoken = $url_apptoken;
        $this->url_openid = $url_openid;
        $this->url_createorder = $url_createorder;
        $this->url_checkstock = $url_checkstock;
        $this->url_checktrackno = $url_checktrackno;
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
    function curl_post($url,$header,$bodys,$method){
        $idcard_api = $this->idcard_api;
        if($idcard_api=='peiqing'){
            $host = $this->id_peiqinghost;
        }else{
            $host = $this->id_aliyunhost;
        }

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
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $result = curl_exec ($curl);
        curl_close($curl);
        return $result;
    }

    function get_api_token(){
        $result = array('statuscode' => 200, 'message' => '');
        $url_apptoken=$this->url_apptoken.'?accessKey='.$this->accessKey.'&secretAccessKey='.$this->secretAccessKey;
        $res = file_get_contents($url_apptoken, false);
        $res = json_decode($res,true);
        if($res['success']){
            $app_token = $res['datas'];
            $result['statuscode'] = 200;
            $result['message'] = '';
            $result['app_token'] = $app_token;
        }else{
            $result['statuscode'] = 300;
            $result['message'] = $res['message'];
            $result['app_token'] = '';
        }
        return $result;
    }

    function get_mchid($app_token){
        $result = array('statuscode' => 200, 'message' => '');
        $timestamp = time();
        $signkey='app_token'.$app_token.'timestamp'.$timestamp.''.$this->secretAccessKey;
        $sign = md5($signkey);
        $url_openid=$this->url_openid.'?app_token='.$app_token.'&timestamp='.$timestamp.'&sign='.$sign;
        $res = file_get_contents($url_openid, false);
        $res = json_decode($res,true);
        if($res['success']){
            $merchantId=$res['datas'][0]['partnerOpenId'];
            $result['statuscode'] = 200;
            $result['message'] = '';
            $result['merchantId'] = $merchantId;
        }else{
            $result['statuscode'] = 300;
            $result['message'] = $res['message'];
            $result['merchantId'] = '';
        }
        return $result;
    }

    function get_limit_stock($skus,$warehouseName,$app_token,$merchantId){
        $result = array('statuscode' => 200, 'message' => '');

        $postdata=array(
            'merchantId'=>$merchantId,
            'pageNo'=>1,
            'pageSize'=>10000,
            'skus'=>$skus,
            'warehouseName'=>$warehouseName

        );
        $postdata=json_encode($postdata);

        $timestamp = time();
        $signkey='app_token'.$app_token.'timestamp'.$timestamp.''.$this->secretAccessKey;
        $sign = md5($signkey);
        $apiurl=$this->url_checkstock."?app_token=$app_token&timestamp=$timestamp&sign=$sign";
        $headers = array('Content-Type:application/json;');
        $ch = curl_init();
        //参数设置
        curl_setopt ($ch, CURLOPT_URL,$apiurl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        $res = curl_exec ($ch);
        curl_close($ch);
//        \Think\Log::write('监控点2：'.$res,'PAY');
        if($res == FALSE){
            $result['statuscode']=300;
            $result['message']='接口错误，请重试';
        }else{
            $res=json_decode($res,true);
            if($res['code']==200){
                $stockdata=$res['datas']['array'];
                $result['statuscode']=200;
                $result['message']='';
                $result['stockdata']=$stockdata;
            }elseif ($res['code']==523){
                $result['statuscode']=301;
                $result['message']=$res['message'];
            }else{
                $result['statuscode']=300;
                $result['message']=$res['message'];
            }
        }
        return $result;
    }

    function get_trackno_by_orderid($ids,$app_token,$merchantId){
        $result = array('statuscode' => 200, 'message' => '');

        $postdata=array(
            'merchantId'=>$merchantId,
            'pageNo'=>1,
            'pageSize'=>10000,
            'orderIds'=>$ids
        );
        $postdata=json_encode($postdata);

        $timestamp = time();
        $signkey='app_token'.$app_token.'timestamp'.$timestamp.''.$this->secretAccessKey;
        $sign = md5($signkey);
        $apiurl=$this->url_checktrackno."?app_token=$app_token&timestamp=$timestamp&sign=$sign";
        $headers = array('Content-Type:application/json;');
        $ch = curl_init();
        //参数设置
        curl_setopt ($ch, CURLOPT_URL,$apiurl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        $res = curl_exec ($ch);
//        print_r($res);
        curl_close($ch);
//        \Think\Log::write('监控点2：'.$res,'PAY');
        if($res == FALSE){
            $result['statuscode']=300;
            $result['message']='接口错误，请重试';
        }else{
            $res=json_decode($res,true);
            if($res['code']==200){
                $tracknodata=$res['datas']['array'];
                $result['statuscode']=200;
                $result['message']='';
                $result['tracknodata']=$tracknodata;
            }elseif ($res['code']==523){
                $result['statuscode']=301;
                $result['message']=$res['message'];
            }else{
                $result['statuscode']=300;
                $result['message']=$res['message'];
            }
        }
        return $result;
    }





    function Queryidcard($imgurl,$side){
        $result = array('statuscode' => 200, 'message' => '');

        if(!file_exists('.'.$imgurl)){
            $result['statuscode'] = 300;
            $result['message'] = '图片不存在';
            return $result;
        }
        $idcard_api = $this->idcard_api;

        if($idcard_api=='peiqing'){
            if($side=='face'){
                $side='front';
            }

            $method = 'POST';

            $aliyunappcode = $this->aliyunappcode;
            $id_peiqinghost = $this->id_peiqinghost;
            $id_peiqingquerypath = $this->id_peiqingquerypath;
            $url = $id_peiqinghost.$id_peiqingquerypath;

            // 生成头
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $aliyunappcode);
            array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");

            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $rootdomain=$http_type.$_SERVER['HTTP_HOST'];

            $bodys = 'image='.$rootdomain.$imgurl.'&idCardSide='.$side; //图片 + 正反面参数 默认正面，背面请传back
            //或者base64
            //$bodys = 'image=data:image/jpeg;base64,......'.'&idCardSide=front';  //jpg图片base64 + 正反面参数 默认正面，背面请传back
            //$bodys = 'image=data:image/png;base64,......'.'&idCardSide=front';   //png图片base64 +  正反面参数 默认正面，背面请传back


            // 发送请求
            $response = $this->curl_post($url,$headers,$bodys,$method);
            $response = json_decode($response,true);

            if($response['code']==1){
                if($side=='front'){
                    $result['statuscode'] = 200;
                    $result['message'] = '识别成功';
                    $birthday = $response['result']['birthday'];
                    $birthday = strtotime($birthday);
                    $birthday = date('Y-m-d',$birthday);
                    $result['address'] = $response['result']['address'];
                    $result['name'] = $response['result']['name'];
                    $result['code'] = $response['result']['code'];
                    $result['nation'] = $response['result']['nation'];
                    $result['birthday'] = $birthday;
                    $result['sex'] = $response['result']['sex'];
                }else{
                    $result['statuscode'] = 200;
                    $result['message'] = '识别成功';

                    $issueDate = $response['result']['issueDate'];
                    $issueDate = strtotime($issueDate);
                    $issueDate = date('Y-m-d',$issueDate);

                    $expiryDate = $response['result']['expiryDate'];
                    $expiryDate = strtotime($expiryDate);
                    $expiryDate = date('Y-m-d',$expiryDate);

                    $result['issue'] = $response['result']['issue'];
                    $result['issuedate'] = $issueDate;
                    $result['expirydate'] = $expiryDate;
                }
            }else{
                $result['statuscode'] = 300;
                $result['message'] = $response['msg'];
            }
            return $result;
        }else{
            if($side=='front'){
                $side='face';
            }

            $method = 'POST';

            $aliyunappcode = $this->aliyunappcode;
            $id_aliyunhost = $this->id_aliyunhost;
            $id_aliyunquerypath = $this->id_aliyunquerypath;
            $url = $id_aliyunhost.$id_aliyunquerypath;

            // 生成头
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $aliyunappcode);
            array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");

            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $rootdomain=$http_type.$_SERVER['HTTP_HOST'];


            $file = '.'.$imgurl;
            //如果输入带有 inputs , 设置为True，否则设为False
            $is_old_format = false;
            $config = array('side' => $side);

            if($fp = fopen($file, "rb", 0)) {
                $binary = fread($fp, filesize($file)); // 文件读取
                fclose($fp);
                $base64 = base64_encode($binary); // 转码
            }

            $request = array('image' => $base64);
            if(count($config) > 0){$request["configure"] = json_encode($config);}
            $body = json_encode($request);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            if (1 == strpos("$".$url, "https://")) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            $resultdata = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
            $rheader = substr($resultdata, 0, $header_size);
            $rbody = substr($resultdata, $header_size);
            curl_close($curl);

            if($httpCode == 200){
                $response = json_decode($rbody,true);
                if($response['success']){
                    $result['statuscode'] = 200;
                    $result['message'] = '识别成功';
                    if($side=='face'){
                        $birthday = $response['birth'];
                        $birthday = strtotime($birthday);
                        $birthday = date('Y-m-d',$birthday);
                        $result['address'] = $response['address'];
                        $result['name'] = $response['name'];
                        $result['code'] = $response['num'];
                        $result['nation'] = $response['nationality'];
                        $result['birthday'] = $birthday;
                        $result['sex'] = $response['sex'];
                    }else{
                        $issueDate = $response['start_date'];
                        $issueDate = strtotime($issueDate);
                        $issueDate = date('Y-m-d',$issueDate);

                        $expiryDate = $response['end_date'];
                        $expiryDate = strtotime($expiryDate);
                        $expiryDate = date('Y-m-d',$expiryDate);

                        $result['issue'] = $response['issue'];
                        $result['issuedate'] = $issueDate;
                        $result['expirydate'] = $expiryDate;
                    }
                }else{
                    $result['statuscode'] = 300;
                    $result['message'] = '识别失败';
                }
            }else{
                $result['statuscode'] = 300;
                $result['message'] = '识别失败';

                //printf("Http error code: %d\n", $httpCode);
                //printf("Error msg in body: %s\n", $rbody);
                //printf("header: %s\n", $rheader);
            }
            return $result;
        }
    }
}
?>
