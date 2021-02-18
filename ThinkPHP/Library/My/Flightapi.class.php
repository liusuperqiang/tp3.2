<?php

namespace My;
//use function GuzzleHttp\Psr7\str;

class Flightapi {
    private $host;
    private $querypath;
    private $appkey;
    private $appsecret;
    private $appcode;
    private $accountlimit;
    private $iplimit;
    private $unlimitedcode;
    function __construct(){
        $host= C('FlightHost');
        $querypath= C('FlightQueryPath');
        $appcode= C('FlightAppCode');
        $appkey= C('FlightAppKey');
        $appsecret=C('FlightAppSecret');
        $accountlimit=C('FlightAccountLimit');
        $iplimit=C('FlightIpLimit');
        $unlimitedcode=C('FlightUnlimitedcode');

        $this->Host = $host;
        $this->Querypath = $querypath;
        $this->Appkey = $appkey;
        $this->Appsecret = $appsecret;
        $this->Appcode = $appcode;
        $this->AccountLimit = $accountlimit;
        $this->IpLimit = $iplimit;
        $this->Unlimitedcode = $unlimitedcode;
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
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $result = curl_exec ($curl);
        curl_close($curl);
        return $result;
    }


    function Queryflight($flightno,$flightdate,$flighttype,$unlcode=''){
        $result = array('statuscode' => 200, 'message' => '');
        $allowflighttype=array('in','out');
        if(!in_array($flighttype,$allowflighttype)){
            $result['statuscode'] = 300;
            $result['message'] = '请选择正确的进出港类型';
            return $result;
        }else{
            if($flighttype=='in'){
                $chktypes=1;
            }else{
                $chktypes=2;
            }
        }
        $flightno=strtoupper($flightno);
        if(!preg_match("/^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]{3,7})$/", $flightno)){
            $result['statuscode'] = 300;
            $result['message'] = '请输入正确的航班，例如：MU2345';
            return $result;
        }else{
            //此处应该增加所有西安关联的航班的列表校验  flightdata_list id, types, flightno
            $chkhas=M('flightdata_list')->field('id,types')->where(array('flightno'=>$flightno))->limit(1)->find();
            if(empty($chkhas)){
                $result['statuscode'] = 300;
                $result['message'] = '请输入正确的航班，例如：MU2345，目前系统仅允许查询咸阳国际机场进出港的航班';
                return $result;
            }else{
                if($chktypes!=$chkhas['types']){
                    if($chkhas['types']==1){
                        $result['statuscode'] = 300;
                        $result['message'] = '您输入的航班'.$flightno.'为进港航班，和您选择的行程不符';
                        return $result;
                    }else{
                        $result['statuscode'] = 300;
                        $result['message'] = '您输入的航班'.$flightno.'为出港航班，和您选择的行程不符';
                        return $result;
                    }
                }
            }
        }
        if($flightdate==''){
            $result['statuscode'] = 300;
            $result['message'] = '航班日期不能为空';
            return $result;
        }else{
            if(date('Y-m-d', strtotime($flightdate))!=$flightdate){
                $result['statuscode'] = 300;
                $result['message'] = '请输入正确的航班日期，例如：2019-05-01';
                return $result;
            }else{
                $flightdateunix=strtotime($flightdate.' 00:00:00');
            }
        }

        //查询数据库
        $where=array();
        $where['flightno']=$flightno;
        $where['flightdate']=$flightdateunix;
        if($flighttype=='out'){
            $where['depcode']='XIY';
        }elseif($flighttype=='in'){
            $where['arrcode']='XIY';
        }
        $flightdata=M('flightdata')->fetchSql(false)->field('id, flightno, flightdate, rate, depcity, depcode, arrcity, arrcode, depport, arrport, depterminal, arrterminal, depscheduled, arrscheduled, depestimated, arrestimated, depactual, arractual, checkcount')->where($where)->limit(1)->find();
        //return $flightdata;
        if($flightdata){
            $result['statuscode'] = 200;
            $result['message'] = '本地航班数据查询成功';
            $result['flightno'] = $flightdata['flightno'];
            $result['flightdate'] = date('Y-m-d',$flightdata['flightdate']);
            $result['depcity'] = $flightdata['depcity'];
            $result['arrcity'] = $flightdata['arrcity'];
            $result['depport'] = $flightdata['depport'];
            $result['arrport'] = $flightdata['arrport'];
            $result['depterminal'] = $flightdata['depterminal'];
            $result['arrterminal'] = $flightdata['arrterminal'];
            $result['depscheduled_date'] = date('Y-m-d',$flightdata['depscheduled']);
            $result['depscheduled_time'] = date('H:i',$flightdata['depscheduled']);
            $result['arrscheduled_date'] = date('Y-m-d',$flightdata['arrscheduled']);
            $result['arrscheduled_time'] = date('H:i',$flightdata['arrscheduled']);
            $result['depestimated_date'] = date('Y-m-d',$flightdata['depestimated']);
            $result['depestimated_time'] = date('H:i',$flightdata['depestimated']);
            $result['arrestimated_date'] = date('Y-m-d',$flightdata['arrestimated']);
            $result['arrestimated_time'] = date('H:i',$flightdata['arrestimated']);
            $result['depactual_date'] = date('Y-m-d',$flightdata['depactual']);
            $result['depactual_time'] = date('H:i',$flightdata['depactual']);
            $result['arractual_date'] = date('Y-m-d',$flightdata['arractual']);
            $result['arractual_time'] = date('H:i',$flightdata['arractual']);

            M('flightdata')->where($where)->limit(1)->setInc('checkcount');
            return $result;
        }else{
            $accountlimit = $this->AccountLimit;
            $iplimit = $this->IpLimit;
            $unlimitedcode = $this->Unlimitedcode;

            //$result['statuscode'] = 200;
            //$result['message'] = '航班数据查询成功';
            //$result['depscheduled_time'] = date('H:i',time());
            //$result['arrscheduled_time'] = date('H:i',time());
            //return $result;

            //var_dump($unlimitedcode);
            //var_dump($unlcode);
           // exit;
            $ip = get_client_ip();
            $ip = bindec(decbin(ip2long($ip)));
            if(UID){
                $uid=UID;
            }else{
                $uid=0;
            }
            if($unlcode!=$unlimitedcode){

                $where=array();
                $where['ip']=$ip;
                if($uid>0){
                    $where['uid']=$uid;
                }
                $starttime=strtotime(date('Y-m-d',time()).' 00:00:00');
                $stoptime=strtotime(date('Y-m-d',time()).' 23:59:59');
                $where['createtime'] = array(array('egt',$starttime),array('elt',$stoptime));
                $requestcount = M('flightrequestlog')->where($where)->count();
                if($uid>0){
                    if($requestcount>$accountlimit){
                        $result['statuscode'] = 300;
                        $result['message'] = '每账户每天限制使用航班查询'.$accountlimit.'次';
                        return $result;
                    }
                }else{
                    if($requestcount>$iplimit){
                        $result['statuscode'] = 300;
                        $result['message'] = '每IP每天限制使用航班查询'.$accountlimit.'次';
                        return $result;
                    }
                }
            }

            $data=array();
            $data['uid'] = $uid;
            $data['ip'] = $ip;
            $data['createtime'] = time();
            M('flightrequestlog')->add($data);

            $method = 'POST';
            $appcode = $this->Appcode;
            $host = $this->Host;
            $querypath = $this->Querypath;
            $url = $host.$querypath;

            // 生成头
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");

            $bodysarr=array();
            $bodysarr['serviceID']='urn:cdif-io:serviceID:航班信息查询服务';
            $bodysarr['actionName']='起降时间查询';
            $bodysarr['input']['flightNo']=$flightno;
            $bodysarr['input']['date']=$flightdate;
            $bodys = json_encode($bodysarr);

            // 发送请求
            $response = $this->curl_post($url,$headers,$bodys,$method);
            $response = json_decode($response,true);
            if(!empty($response['output'])){
                $responsedata=$response['output']['result'];
                //print_r($responsedata);
                foreach ($responsedata as $key=>$vo) {

                    date_default_timezone_set('UTC');
                    $depScheduled=date('Y-m-d H:i:s',strtotime($vo['depScheduled']));
                    $arrScheduled=date('Y-m-d H:i:s',strtotime($vo['arrScheduled']));
                    $depEstimated=date('Y-m-d H:i:s',strtotime($vo['depEstimated']));
                    $arrEstimated=date('Y-m-d H:i:s',strtotime($vo['arrEstimated']));
                    $depActual=date('Y-m-d H:i:s',strtotime($vo['depActual']));
                    $arrActual=date('Y-m-d H:i:s',strtotime($vo['arrActual']));
                    date_default_timezone_set('PRC');
                    $depScheduled=strtotime($depScheduled);
                    $arrScheduled=strtotime($arrScheduled);
                    $depEstimated=strtotime($depEstimated);
                    $arrEstimated=strtotime($arrEstimated);
                    $depActual=strtotime($depActual);
                    $arrActual=strtotime($arrActual);
                    if($depScheduled<0)$depScheduled=0;
                    if($arrScheduled<0)$arrScheduled=0;
                    if($depEstimated<0)$depEstimated=0;
                    if($arrEstimated<0)$arrEstimated=0;
                    if($depActual<0)$depActual=0;
                    if($arrActual<0)$arrActual=0;

                    $data=array();
                    $data['flightno'] = $flightno;
                    $data['flightdate'] = strtotime($flightdate.' 00:00:00');
                    $data['rate'] = $vo['rate'];
                    $data['depcity'] = $vo['depCity'];
                    $data['depcode'] = $vo['depCode'];
                    $data['arrcity'] = $vo['arrCity'];
                    $data['arrcode'] = $vo['arrCode'];
                    $data['depport'] = $vo['depPort'];
                    $data['arrport'] = $vo['arrPort'];
                    $data['depterminal'] = $vo['depTerminal'];
                    $data['arrterminal'] = $vo['arrTerminal'];
                    $data['depscheduled'] = $depScheduled;
                    $data['arrscheduled'] = $arrScheduled;
                    $data['depestimated'] = $depEstimated;
                    $data['arrestimated'] = $arrEstimated;
                    $data['depactual'] = $depActual;
                    $data['arractual'] = $arrActual;
                    $data['checkcount'] = 1;
                    $data['createtime'] = time();
                    $data['updatetime'] = time();
                    M('flightdata')->add($data);
                }
                $where=array();
                $where['flightno']=$flightno;
                $where['flightdate']=$flightdateunix;
                if($flighttype=='out'){
                    $where['depcode']='XIY';
                }elseif($flighttype=='in'){
                    $where['arrcode']='XIY';
                }
                $flightdata=M('flightdata')->field('id, flightno, flightdate, rate, depcity, depcode, arrcity, arrcode, depport, arrport, depterminal, arrterminal, depscheduled, arrscheduled, depestimated, arrestimated, depactual, arractual, checkcount')->where($where)->limit(1)->find();
                if($flightdata){
                    $result['statuscode'] = 200;
                    $result['message'] = '航班数据查询成功';
                    $result['flightno'] = $flightdata['flightno'];
                    $result['flightdate'] = date('Y-m-d',$flightdata['flightdate']);
                    $result['depcity'] = $flightdata['depcity'];
                    $result['arrcity'] = $flightdata['arrcity'];
                    $result['depport'] = $flightdata['depport'];
                    $result['arrport'] = $flightdata['arrport'];
                    $result['depterminal'] = $flightdata['depterminal'];
                    $result['arrterminal'] = $flightdata['arrterminal'];
                    $result['depscheduled_date'] = date('Y-m-d',$flightdata['depscheduled']);
                    $result['depscheduled_time'] = date('H:i',$flightdata['depscheduled']);
                    $result['arrscheduled_date'] = date('Y-m-d',$flightdata['arrscheduled']);
                    $result['arrscheduled_time'] = date('H:i',$flightdata['arrscheduled']);
                    $result['depestimated_date'] = date('Y-m-d',$flightdata['depestimated']);
                    $result['depestimated_time'] = date('H:i',$flightdata['depestimated']);
                    $result['arrestimated_date'] = date('Y-m-d',$flightdata['arrestimated']);
                    $result['arrestimated_time'] = date('H:i',$flightdata['arrestimated']);
                    $result['depactual_date'] = date('Y-m-d',$flightdata['depactual']);
                    $result['depactual_time'] = date('H:i',$flightdata['depactual']);
                    $result['arractual_date'] = date('Y-m-d',$flightdata['arractual']);
                    $result['arractual_time'] = date('H:i',$flightdata['arractual']);
                    return $result;
                }else{
                    $result['statuscode'] = 300;
                    $result['message'] = '航班信息查询失败';
                    return $result;
                }
            }else{
                $result['statuscode'] = 300;
                $result['message'] = '航班实时信息查询失败';
                return $result;
            }
        }

        /*
         *
       (
    [topic] => device error
    [message] => 输入数据校验错误
    [fault] => Array
        (
            [reason] => 航班号格式错误
            [info] => should match pattern "^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]{3,7})$"
        )

)
Array
(
    [output] => Array
        (
            [result] => Array
                (
                    [0] => Array
                        (
                            [flightNo] => FM9467
                            [depCity] => 上海虹桥
                            [depCode] => SHA
                            [arrCity] => 兰州
                            [arrCode] => LHW
                            [depPort] => 上海虹桥国际机场
                            [arrPort] => 兰州中川国际机场
                            [depTerminal] => T2
                            [arrTerminal] => T2
                            [depScheduled] => 2019-05-04T12:10:00Z
                            [arrScheduled] => 2019-05-04T17:10:00Z
                            [depEstimated] => 2019-05-04T12:10:00Z
                            [arrEstimated] => 2019-05-04T17:10:00Z
                            [depActual] => 0001-01-01T00:00:00Z
                            [arrActual] => 0001-01-01T00:00:00Z
                            [flightState] => 计划
                            [depUtcOffset] => 8
                            [arrUtcOffset] => 8
                            [codeShares] => Array
                                (
                                )

                        )

                    [1] => Array
                        (
                            [flightNo] => FM9467
                            [depCity] => 上海虹桥
                            [depCode] => SHA
                            [arrCity] => 郑州
                            [arrCode] => CGO
                            [depPort] => 上海虹桥国际机场
                            [arrPort] => 郑州新郑国际机场
                            [depTerminal] => T2
                            [arrTerminal] =>
                            [depScheduled] => 2019-05-04T12:10:00Z
                            [arrScheduled] => 2019-05-04T14:20:00Z
                            [depEstimated] => 2019-05-04T12:10:00Z
                            [arrEstimated] => 2019-05-04T14:20:00Z
                            [depActual] => 0001-01-01T00:00:00Z
                            [arrActual] => 0001-01-01T00:00:00Z
                            [flightState] => 计划
                            [depUtcOffset] => 8
                            [arrUtcOffset] => 8
                            [codeShares] => Array
                                (
                                )

                        )

                    [2] => Array
                        (
                            [flightNo] => FM9467
                            [depCity] => 郑州
                            [depCode] => CGO
                            [arrCity] => 兰州
                            [arrCode] => LHW
                            [depPort] => 郑州新郑国际机场
                            [arrPort] => 兰州中川国际机场
                            [depTerminal] => T2
                            [arrTerminal] => T2
                            [depScheduled] => 2019-05-04T15:20:00Z
                            [arrScheduled] => 2019-05-04T17:10:00Z
                            [depEstimated] => 2019-05-04T15:20:00Z
                            [arrEstimated] => 2019-05-04T17:10:00Z
                            [depActual] => 0001-01-01T00:00:00Z
                            [arrActual] => 0001-01-01T00:00:00Z
                            [flightState] => 计划
                            [depUtcOffset] => 8
                            [arrUtcOffset] => 8
                            [codeShares] => Array
                                (
                                )

                        )

                )

        )

)
Array
(
    [output] => Array
        (
            [result] => Array
                (
                    [0] => Array
                        (

                                (
                                )

                        )

                )
         *
         *
         *
        $rescode=array();
        $rescode['showapi_res_code'] = $result['showapi_res_code'];
        $rescode['showapi_res_error'] = $result['showapi_res_error'];
        $rescode['flag'] = $result['showapi_res_body']['flag'];
        $rescode['status'] = $result['showapi_res_body']['status'];
        $rescode['expSpellName'] = $result['showapi_res_body']['expSpellName'];
        $rescode['expTextName'] = $result['showapi_res_body']['expTextName'];
        $rescode['msg'] = $result['showapi_res_body']['msg'];
        $data = $result['showapi_res_body']['data'];
        foreach ($data as $key=>$vo) {
            $data[$key]['unixtime']=strtotime($vo['time']);
        }
        $rescode['data'] = $data;
         */
    }
}
?>
