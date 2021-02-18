<?php

namespace My;
//use function GuzzleHttp\Psr7\str;

class Idcardapi {
    private $idcard_api;
    private $aliyunappkey;
    private $aliyunappsecret;
    private $aliyunappcode;
    private $id_peiqinghost;
    private $id_peiqingquerypath;
    private $id_aliyunhost;
    private $id_aliyunquerypath;
    function __construct(){
        $idcard_api = C('IDCARD_API');
        $aliyunappkey = C('AliyunAppKey');
        $aliyunappsecret = C('AliyunAppSecret');
        $aliyunappcode = C('AliyunAppCode');
        $id_peiqinghost = C('ID_PeiqingHost');
        $id_peiqingquerypath = C('ID_PeiqingQueryPath');
        $id_aliyunhost = C('ID_AliyunHost');
        $id_aliyunquerypath = C('ID_AliyunQueryPath');

        $this->idcard_api = $idcard_api;
        $this->aliyunappkey = $aliyunappkey;
        $this->aliyunappsecret = $aliyunappsecret;
        $this->aliyunappcode = $aliyunappcode;
        $this->id_peiqinghost = $id_peiqinghost;
        $this->id_peiqingquerypath = $id_peiqingquerypath;
        $this->id_aliyunhost = $id_aliyunhost;
        $this->id_aliyunquerypath = $id_aliyunquerypath;
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
