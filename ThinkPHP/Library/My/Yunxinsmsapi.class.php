<?php

namespace My;

class Yunxinsmsapi {
    private $sendurl;
    private $account;
    private $password;
    function __construct(){
        $sendurl= C('YX_SENDURL');
        $account= C('YX_ACCOUNT');
        $password= C('YX_PASSWORD');

        $this->Sendurl = $sendurl;
        $this->Account = $account;
        $this->Password = md5($password);
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
     * 发送短信
     *
     * @param string $mobile 		手机号码
     * @param string $msg 			短信内容
     * @param string $needstatus 	是否需要状态报告
     */
    public function sendSMS( $mobile, $smstype, $msg ) {
        //云信接口参数
        $postArr = array (
            'smstype' =>$smstype,//短信发送发送
            'clientid'  =>  $this->Account,
            'password' => $this->Password,
            'mobile' => $mobile,
            'content' => $msg ,
            'sendtime'=>date('Y-m-d H:i:s'),
            'extend'=>'00',
            'uid'=>'00'
        );
        $result = $this->curlPost( $this->Sendurl, $postArr);
        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url  //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    private function curlPost($url,$postFields){
        $result = array('statuscode' => 200, 'message' => '');
        $postFields = json_encode($postFields);
//        echo $postFields.'<br>';
        //echo $postFields;
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Accept-Encoding: identity',
                'Content-Length: ' . strlen($postFields),
                'Accept:application/json',
                'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
            )
        );

        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //如果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,60);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );

        if (false == $ret) {
            $result['statuscode'] = 300;
            $result['message'] = 'sms failed to send!';
            $result['responsedata'] = $ret;
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);

            if (200 != $rsp) {
                $result['statuscode'] = 300;
                $result['message'] = 'sms failed to send!';
                $result['responsedata'] = $ret;
            } else {
                $sendresult = json_decode($ret,true);
                if($sendresult['total_fee']==0){
                    $result['statuscode'] = 300;
                    $result['message'] = 'sms failed to send!';
                    $result['responsedata'] = $ret;
                }else{
                    $data=$sendresult['data'];
                    $data=$data[0];
                    $code = $data['code'];
                    if($code==0){
                        $result['statuscode'] = 200;
                        $result['message'] = 'SMS sent successfully!';
                        $result['responsedata'] = $ret;
                    }else{
                        $result['statuscode'] = 300;
                        $result['message'] = 'sms failed to send!';
                        $result['responsedata'] = $ret;
                    }
                }
            }
        }
        return $result;
    }

}
