<?php
/**
 *字符串转十六进制函数
 *@pream string $str='abc';
 */
function strToHex($str) {
    $hex = "";
    for ($i = 0;$i < strlen($str);$i++){
        $hex.= dechex(ord($str[$i]));
    }
    $hex = strtoupper($hex);
    return $hex;
}

/**
 *十六进制转字符串函数
 *@pream string $hex='616263';
 */
function hexToStr($hex){
    $str = "";
    for($i=0;$i<strlen($hex)-1;$i+=2){
        $str.= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $str;
}

function strip_html_tags($tags,$str){
    $html=array();
    foreach ($tags as $tag) {
        $html[]='/<'.$tag.'.*?>([\s\S]*)<\/'.$tag.'>/is';
        $html[]='/<'.$tag.'.*?>/';
    }
//    print_r($html);
    $data=preg_replace($html,'',$str);
    return $data;
}


function  uuid(){
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ( $chars, 0, 8 ) . '-'. substr ( $chars, 8, 4 ) . '-' . substr ( $chars, 12, 4 ) . '-' . substr ( $chars, 16, 4 ) . '-' . substr ( $chars, 20, 12 );
    return $uuid;
}

function remote_file_exists($url){
    return(bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
}


function uniqueArr($array,$key='id'){
    $result = array();
    foreach($array as $k=>$val){
        $code = false;
        foreach($result as $_val){
            if($_val[$key] == $val[$key]){
                $code = true;
                break;
            }
        }
        if(!$code){
            $result[]=$val;
        }
    }
    return $result;
}

//价格小数位
function price_format($price,$len){
    if($len<=0){
        $len=0;
    }
    if($len>4){
        $len=4;
    }
    $price = round($price,$len);
    if(is_numeric($price)){
        $price = number_format($price,$len,'.','');
    }else{
        $price=0;
        $price=number_format($price,$len,'.','');
    }
    return $price;
}

//获取单个用户的一段时期消费总额
function Get_user_orderdata($user_id,$datatype=0,$monthkey='',$startdate='',$enddate=''){
    if(empty($user_id) or $user_id==''){
        $user_id=-1;
    }
    $where=array();
    $where['user_id']=$user_id;
    $where['order_status']=array('in','1,5,6');

    if($startdate!='' and $enddate!=''){
        $starttime=local_strtotime($startdate.' 00:00:00',0);
        $stoptime=local_strtotime($enddate.' 23:59:59',0);
        $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
    }elseif($startdate!=''){
        $starttime=local_strtotime($startdate.' 00:00:00',0);
        $where['add_time'] = array('egt',$starttime);
    }elseif($enddate!=''){
        $stoptime=local_strtotime($enddate.' 23:59:59',0);
        $where['add_time'] = array('elt',$stoptime);
    }else{
        if($monthkey!=''){
            $startdate=$monthkey.'-01';
//            $enddate=date('Y-m-'.date('t',local_strtotime($startdate . ' 00:00:00',0)),local_strtotime($startdate . ' 00:00:00',0));
            $enddate=date('Y-m-t',local_strtotime($startdate . ' 00:00:00',0));
            $starttime=local_strtotime($startdate.' 00:00:00',0);
            $stoptime=local_strtotime($enddate.' 23:59:59',0);
            $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
        }else{
            switch ($datatype){ //0全部 1 当天 2本周   3本月  4自定义
                case 1:
                    $today = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                    $starttime=local_strtotime($today.' 00:00:00',0);
                    $stoptime=local_strtotime($today.' 23:59:59',0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
                case 2:
                    $sdefaultDate = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                    $first=1;
                    $w = date('w', local_strtotime($sdefaultDate,0));
                    $week_start=date('Y-m-d 0:0:0', local_strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days',0));
                    $week_end=date('Y-m-d 23:59:59',local_strtotime("$week_start +6 days",0));
                    $starttime=local_strtotime($week_start,0);
                    $stoptime=local_strtotime($week_end,0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
                case 3:
                    $starttime=local_strtotime(date('Y-m-01 00:00:00', local_strtotime(date("Y-m-d"),0)),0);
                    $stoptime=local_strtotime(date('Y-m-d 23:59:59', time()),0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
            }
        }
    }
    $ordercount=M('order_info')->fetchSql(false)->where($where)->count('order_id');
    $orderamount=M('order_info')->fetchSql(false)->where($where)->sum('goods_amount+shipping_fee');
    $orderamount=intval($orderamount);

    return array(
      'ordercount'=>$ordercount,
      'orderamount'=>  $orderamount
    );
}

//获取一些用户的消费综合
function Get_users_orderdata($uids,$datatype=0,$monthkey='',$startdate='',$enddate=''){
    if(empty($uids)){
        $uids[]=-1;
    }
    $where=array();
    $where['user_id']=array('in',$uids);
    $where['order_status']=array('in','1,5,6');

    if($startdate!='' and $enddate!=''){
        $starttime=local_strtotime($startdate.' 00:00:00',0);
        $stoptime=local_strtotime($enddate.' 23:59:59',0);
        $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
    }elseif($startdate!=''){
        $starttime=local_strtotime($startdate.' 00:00:00',0);
        $where['add_time'] = array('egt',$starttime);
    }elseif($enddate!=''){
        $stoptime=local_strtotime($enddate.' 23:59:59',0);
        $where['add_time'] = array('elt',$stoptime);
    }else{
        if($monthkey!=''){
            $startdate=$monthkey.'-01';
//            $enddate=date('Y-m-'.date('t',local_strtotime($startdate . ' 00:00:00',0)),local_strtotime($startdate . ' 00:00:00',0));
            $enddate=date('Y-m-t',local_strtotime($startdate . ' 00:00:00',0));
            $starttime=local_strtotime($startdate.' 00:00:00',0);
            $stoptime=local_strtotime($enddate.' 23:59:59',0);
            $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
        }else{
            switch ($datatype){ //0全部 1 当天 2本周   3本月  4自定义
                case 1:
                    $today = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                    $starttime=local_strtotime($today.' 00:00:00',0);
                    $stoptime=local_strtotime($today.' 23:59:59',0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
                case 2:
                    $sdefaultDate = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                    $first=1;
                    $w = date('w', local_strtotime($sdefaultDate,0));
                    $week_start=date('Y-m-d 0:0:0', local_strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days',0));
                    $week_end=date('Y-m-d 23:59:59',local_strtotime("$week_start +6 days",0));
                    $starttime=local_strtotime($week_start,0);
                    $stoptime=local_strtotime($week_end,0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
                case 3:
                    $starttime=local_strtotime(date('Y-m-01 00:00:00', local_strtotime(date("Y-m-d"),0)),0);
                    $stoptime=local_strtotime(date('Y-m-d 23:59:59', time()),0);
                    $where['add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                    break;
            }
        }
    }
    $ordercount=M('order_info')->fetchSql(false)->where($where)->count('order_id');
    $orderamount=M('order_info')->fetchSql(false)->where($where)->sum('goods_amount+shipping_fee');
    $orderamount=intval($orderamount);

    return array(
        'ordercount'=>$ordercount,
        'orderamount'=>  $orderamount
    );
}

//获取下线USER列表
function Get_vip_userdata($user_id){
    $db = M('users');
    $where['parent_id']=$user_id;
    $where['user_rank']=0;
    $userlist = $db->fetchSql(false)
        ->field('user_id, yaoqing_code, parent_id, mobile_phone, user_name, user_rank, user_money, headimg, reg_time')
        ->where($where)->order('reg_time desc')->select();
    $user_ids = array();
    foreach ($userlist as $key=>$vo) {
        $user_ids[]=$vo['user_id'];
    }
    return array(
        'userlist'=>$userlist,
        'user_ids'=>  $user_ids,
        'user_id_count'=>  count($user_ids)
    );
}



//创建包裹日志
function createpkglog($pid,$staid=0,$staffid=0,$cid=0,$title,$logtype,$logcontent=''){
    $allow_logtype=array('create','update','createtrackno','outdepot','in','out','up','cancel','receive');
    if($pid<=0){
        return false;
    }
    if($title==''){
        return false;
    }
    if(!in_array($logtype,$allow_logtype)){
        return false;
    }
    //写入日志
    $data=array();
    $data['pid'] = $pid;
    $data['staid'] = $staid;
    $data['staffid'] = $staffid;
    $data['cid'] = $cid;
    $data['title'] = $title;
    $data['logtype'] = $logtype;
    $data['logtime'] = time();
    $data['logcontent'] = $logcontent;
    $logid = M('package_log')->add($data);
    if($logid){
        return true;
    }else{
        return false;
    }
}

//创建物流轨迹
function createwaybilltrack($billid,$trackno,$city,$context,$statuscode='Delivering',$createtime=0){
    if($billid<=0 and $trackno==''){
        return false;
    }
    if($billid<=0){
        $where=array();
        $where['trackno']=$trackno;
        $billid=M('waybill')->where($where)->limit(1)->getField('billid');
        if(empty($billid)){
            return false;
        }
    }
    if($createtime==0){
        $createtime=time();
    }
    //写入日志
    $data=array();
    $data['billid'] = $billid;
    $data['time'] = $createtime;
    $data['city'] = $city;
    $data['context'] = $context;
    $data['statuscode'] = $statuscode;
    $id = M('waybill_track')->add($data);
    $newdata=array(
        'updatetime'=>time(),
        'statuscode'=>$statuscode
    );
    M('waybill')->where('billid='.$billid)->setField($newdata);
    if($id){
        return true;
    }else{
        return false;
    }
}


function local_strtotime($str,$timezone=6){
    $time = strtotime($str) - $timezone * 3600;
    return $time;
}

function gmtime(){
    return (time() - date('Z'));
}

/*
 * PDF转png  用于物流标签转换，只转换第一张
 */
function Pdf2png($pdffile,$pngpath,$pngname='',$width=0,$height=0,$crop_x=0,$crop_y=0){
    if (!extension_loaded('imagick')) {
        $result['statuscode'] = 300;
        $result['message'] = '图像组件加载失败';
        return $result;
    }

    if (!file_exists('.'.$pdffile)) {
        $result['statuscode'] = 300;
        $result['message'] = '源文件读取失败';
        return $result;
    }
    if (!file_exists('.'.$pngpath)) {
        mkdir('.'.$pngpath, 0777, true);
    }
    if($pngname==''){
        $pngname=uniqid();
    }

    //加载组件
    $im = new \Imagick();
    $im->setResolution(150, 150);

    $im->setCompressionQuality(100);
    $im->readImage('.'.$pdffile);
    $pngfile='';
    foreach ($im as $k=>$v) {
        if($k==0){
            $v->setImageFormat('png');
            $v->setResolution(150, 150);
            $v->stripImage();
            $v->setCompressionQuality(100);

            //$v->trimImage(0);
            //$v->normalizeImage();
            //$v->unsharpMaskImage(0 , 0.5 , 1 , 0.05);
            $savepngfile = $pngpath.$pngname.'.png';
            if ($v->writeImage('.'.$savepngfile) == true) {
                $pngfile = $savepngfile;


            }
        }else{
            continue;
        }
    }
    $im->clear();
    $im->destroy();

    if($width>0 and $height>0){
        $image = new \Think\Image();
        $image->open('.'.$savepngfile);
        $image->crop($width, $height)->save('.'.$savepngfile);
    }

    if($pngfile!=''){
        if (file_exists('.'.$pngfile)) {
            $result['statuscode'] = 200;
            $result['message'] = 'OK';
            $result['pngfile'] = $pngfile;
            return $result;
        }else{
            $result['statuscode'] = 300;
            $result['message'] = 'PNG文件生成失败';
            return $result;
        }
    }else{
        $result['statuscode'] = 300;
        $result['message'] = 'PDF文件转换失败';
        return $result;
    }
}

function base_url(){
    return httptype().$_SERVER['HTTP_HOST'];
}

//判断http还是https
function httptype(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    if(stripos($host,'vmxian')!==false or stripos($host,'findhow')!==false){
        $http_type='https://';
    }
    return $http_type;
}

/*
日志
CreateUserLog();
*/
function CreateUserLog($uid,$logtype,$title,$script='',$memo=''){
    // register/login/logout/order/invite/recharge/other
    $allow_logtype=array('register','login','logout','order','invite','recharge','seriouserror','other','setmobile','account');
    $ip = get_client_ip();
    $ip = bindec(decbin(ip2long($ip)));
    if($uid<1){
        return false;
    }
    if(!in_array($logtype,$allow_logtype)){
        return false;
    }
    if(UID){
        if($uid!=UID){
            $logtype='seriouserror';
        }
    }

    if($logtype=='seriouserror' and UID>0){
        //预警处理
        session('user',null);
        M('user')-> where('uid='.UID)->setField('status',0);
        if($memo==''){
            $memo = '会员越权操作，封停账户。';
        }
    }
    if($script==''){
        $script = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    $data=array();
    $data['uid'] = $uid;
    $data['title'] = $title;
    $data['logtype'] = $logtype;
    $data['ip'] = $ip;
    $data['logtime'] = time();
    $data['script'] = $script;
    $data['memo'] = $memo;
    $savemsg = M('userlog')->add($data);
    return true;
}


/*
在用户未登录的情况操作用户日志
 * CreateUserAssetLog('邀请用户注册','join','profiting',1,$profit_join,'邀请用户注册，用户Email：'.$email);
 */
function CreateUserAssetLog($title,$assettype,$mode,$val,$tradeno,$memo=''){
    $allow_assettype=array('balance','point');
    if(session('?user.uid')){
        return false;
    }else{
        $uid = session('user.uid');
        if($uid<=0){
            return false;
        }
    }

    if(!in_array($assettype,$allow_assettype)){
        return false;
    }
    if($val<=0){
        return false;
    }

    //写入日志
    $data=array();
    $data['uid'] = $uid;
    $data['title'] = $title;
    $data['assettype'] = $assettype;
    $data['mode'] = $mode;
    $data['val'] = $val;
    $data['tradeno'] = $tradeno;
    $data['updatetime'] = time();
    $data['memo'] = $memo;
    $logid = M('user_assetlog')->add($data);

    if($logid){
        if($mode==-1){
            //减会员资产
            $asset_last=M('user')->where('uid='.$uid)->getField(''.$assettype.'');
            $assetval = round(bcsub($asset_last,$val,3),2);
            M('user')->where('uid='.$uid)->setField(''.$assettype.'',$assetval);

            //积分需要操作积分池，增加
            if($assettype=='point'){
                $balance_last = M('pointpool')->order('id desc')->limit(1)->getField('balance');
                $balance = $balance_last+$val;
                $tradeno = date('YmdHis',time()).rand_string(6,1);

                $data=array();
                $data['tradeno'] = $tradeno;
                $data['title'] = '【积分回收】'.$title;
                $data['mode'] = 1;
                $data['val'] = $val;
                $data['balance'] = $balance;
                $data['memo'] = '【积分回收】'.$memo;
                $data['auid'] = 0;
                $data['updatetime'] = time();
                $poolid = M('pointpool')->add($data);

                M('user_assetlog')->where('id='.$logid)->setField('tradeno',$tradeno);

            }
        }else{
            //加会员资产
            $asset_last=M('user')->where('uid='.$uid)->getField(''.$assettype.'');
            $assetval = round(bcadd($asset_last,$val,3),2);
            M('user')->where('uid='.$uid)->setField(''.$assettype.'',$assetval);

            //积分需要操作积分池，减少
            if($assettype=='point'){
                $balance_last = M('pointpool')->order('id desc')->limit(1)->getField('balance');
                $balance = $balance_last-$val;
                $tradeno = date('YmdHis',time()).rand_string(6,1);

                $data=array();
                $data['tradeno'] = $tradeno;
                $data['title'] = '【积分支出】'.$title;
                $data['mode'] = -1;
                $data['val'] = $val;
                $data['balance'] = $balance;
                $data['memo'] = '【积分支出】'.$memo;
                $data['auid'] = 0;
                $data['updatetime'] = time();
                $poolid = M('pointpool')->add($data);

                M('user_assetlog')->where('id='.$logid)->setField('tradeno',$tradeno);
            }
        }
        return true;
    }else{
        return false;
    }
}


/*
在用户未登录的情况操作用户日志
 * CreateUserAssetLogNotLogin('邀请用户注册','join','profiting',1,$profit_join,'邀请用户注册，用户Email：'.$email);
 */
function CreateUserAssetLogNotLogin($uid,$title,$assettype,$mode,$val,$tradeno,$memo=''){
    $allow_assettype=array('balance','point','credit');
    if($uid<=0){
        return false;
    }
    if(!in_array($assettype,$allow_assettype)){
        return false;
    }
    if($val<=0){
        return false;
    }

    //写入日志
    $data=array();
    $data['uid'] = $uid;
    $data['title'] = $title;
    $data['assettype'] = $assettype;
    $data['mode'] = $mode;
    $data['val'] = $val;
    $data['tradeno'] = $tradeno;
    $data['updatetime'] = time();
    $data['memo'] = $memo;
    $logid = M('user_assetlog')->add($data);

    if($logid){
        if($mode==-1){
            //减会员资产
            $asset_last=M('user')->where('uid='.$uid)->getField(''.$assettype.'');
            $assetval = round(bcsub($asset_last,$val,3),2);
            M('user')->where('uid='.$uid)->setField(''.$assettype.'',$assetval);

            //积分需要操作积分池，增加
            if($assettype=='point'){
                $balance_last = M('pointpool')->order('id desc')->limit(1)->getField('balance');
                $balance = $balance_last+$val;
                $tradeno = date('YmdHis',time()).rand_string(6,1);

                $data=array();
                $data['tradeno'] = $tradeno;
                $data['title'] = '【积分回收】'.$title;
                $data['mode'] = 1;
                $data['val'] = $val;
                $data['balance'] = $balance;
                $data['memo'] = '【积分回收】'.$memo;
                $data['auid'] = 0;
                $data['updatetime'] = time();
                $poolid = M('pointpool')->add($data);

                M('user_assetlog')->where('id='.$logid)->setField('tradeno',$tradeno);

            }
        }else{
            //加会员资产
            $asset_last=M('user')->where('uid='.$uid)->getField(''.$assettype.'');
            $assetval = round(bcadd($asset_last,$val,3),2);
            M('user')->where('uid='.$uid)->setField(''.$assettype.'',$assetval);

            //积分需要操作积分池，减少
            if($assettype=='point'){
                $balance_last = M('pointpool')->order('id desc')->limit(1)->getField('balance');
                $balance = $balance_last-$val;
                $tradeno = date('YmdHis',time()).rand_string(6,1);

                $data=array();
                $data['tradeno'] = $tradeno;
                $data['title'] = '【积分支出】'.$title;
                $data['mode'] = -1;
                $data['val'] = $val;
                $data['balance'] = $balance;
                $data['memo'] = '【积分支出】'.$memo;
                $data['auid'] = 0;
                $data['updatetime'] = time();
                $poolid = M('pointpool')->add($data);

                M('user_assetlog')->where('id='.$logid)->setField('tradeno',$tradeno);
            }
        }
        return true;
    }else{
        return false;
    }
}

/*
 * 在用户未登录的情况下操作用户的推广账户，主要用户佣金的获取，纯日志
 *
 */
function Createstationassetlog($staid,$staffid,$title,$assettype,$fromkey,$mode,$val,$tradeno,$memo=''){
    $allow_assettype=array('balance');
    if($staid<=0){
        return false;
    }
    if(!in_array($assettype,$allow_assettype)){
        return false;
    }
    if($val<=0){
        return false;
    }
    if($fromkey==''){
        $fromkey='system';
    }

    //写入日志
    $data=array();
    $data['staid'] = $staid;
    $data['staffid'] = $staffid;
    $data['title'] = $title;
    $data['assettype'] = $assettype;
    $data['fromkey'] = $fromkey;
    $data['mode'] = $mode;
    $data['val'] = $val;
    $data['tradeno'] = $tradeno;
    $data['updatetime'] = time();
    $data['memo'] = $memo;
    $logid = M('station_assetlog')->add($data);

    if($logid){
        if($mode==-1){
            //减会员资产
            $asset_last=M('station')->where('staid='.$staid)->getField(''.$assettype.'');
            $assetval = round(bcsub($asset_last,$val,3),2);
            M('station')->where('staid='.$staid)->setField(''.$assettype.'',$assetval);
        }else{
            //加会员资产
            $asset_last=M('station')->where('staid='.$staid)->getField(''.$assettype.'');
            $assetval = round(bcadd($asset_last,$val,3),2);
            M('station')->where('staid='.$staid)->setField(''.$assettype.'',$assetval);
        }
        return true;
    }else{
        return false;
    }
}

/*
包裹日志
*/
function Createpackagelog($pid,$logtype,$title,$memo='',$staid=0,$srverid=0){
    $allow_logtype=array('create','createtrackno','createpdflabel','createpnglabel','outdepot','in','out','cancel');
    $ip = get_client_ip();
    $ip = bindec(decbin(ip2long($ip)));
    if($pid<1){
        return false;
    }
    if(!in_array($logtype,$allow_logtype)){
        return false;
    }

//    if($staid<0){
//        return false;
//    }
//    if($srverid<0){
//        return false;
//    }
if(empty($memo) or $memo==''){
	$memo='';
}

    $data=array();
    $data['pid'] = $pid;
    $data['staid'] = $staid;
    $data['srverid'] = $srverid;
    $data['title'] = $title;
    $data['logtype'] = $logtype;
    $data['logtime'] = time();
    $data['logcontent'] = $memo;
    $savemsg = M('package_log')->add($data);
    return true;
}

/*
驿站日志
*/
function Createstationlog($staid,$logtype,$title,$script='',$memo='',$srverid=0){
    $allow_logtype=array('in','out','income','withdraw','login','logout','service','other');
    $ip = get_client_ip();
    $ip = bindec(decbin(ip2long($ip)));
    if($staid<1){
        return false;
    }
    if(!in_array($logtype,$allow_logtype)){
        return false;
    }
    if($srverid<0){
        return false;
    }

    if($script==''){
        $script = httptype().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    $data=array();
    $data['staid'] = $staid;
    $data['srverid'] = $srverid;
    $data['title'] = $title;
    $data['logtype'] = $logtype;
    $data['ip'] = $ip;
    $data['logtime'] = time();
    $data['script'] = $script;
    $data['memo'] = $memo;
    $savemsg = M('station_log')->add($data);
    return true;
}

/*
驿站包裹日志
*/
function Createstationpackagelog($pid,$staid,$srverid,$logtype){
    $allow_logtype=array('in','out');
    if($pid<1){
        return false;
    }
    if(!in_array($logtype,$allow_logtype)){
        return false;
    }
    if($staid<0){
        return false;
    }
    if($srverid<0){
        return false;
    }

    $data=array();
    $data['staid'] = $staid;
    $data['srverid'] = $srverid;
    $data['pid'] = $pid;
    $data['logtype'] = $logtype;
    $data['logtime'] = time();
    $savemsg = M('station_packagelog')->add($data);
    return true;
}

//获取图片的其他规格
function getimgurl($img,$width=0,$height=0){
    $img = preg_replace("/_.[0-9]*./",".",$img);
    // print_r(preg_replace("/_[0-9]*./",".",$str));
    // print_r(preg_replace("/_.{6}./",".",$str));

    if($width>0 and $height>0){
        $img = str_replace('.'.get_extension($img),'_'.$width.''.$height.'.'.get_extension($img),$img);
    }
    return $img;
}

/*
 * std数组转标准数组
 */
function stdToArray($obj){
    $rc = (array)$obj;
    foreach($rc as $key=>$item){
        $rc[$key]= (array)$item;
        foreach($rc[$key] as $keys=>$items){
            $rc[$key][$keys]= (array)$items;
        }
    }
    return $rc;
}
/*
 * std数组转标准数组
 */
function objectToArray($obj){
    $arr = '';
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($_arr)){
        foreach($_arr as $key => $val){
            $val = (is_array($val) || is_object($val)) ? $this->objectToArray($val) : $val;
            $arr[$key] = $val;
        }
    }
    return $arr;
}

/** php 接收流文件
 * @param  String  $file 接收后保存的文件名
 * @return boolean
 */
function receiveStreamFile($receiveFile,$file){
    if($receiveFile!=''){
        $ret = file_put_contents($receiveFile, $file, true);
    }else{
        $ret = false;
    }
    return $ret;
}

/**
 * 邮件发送函数
 */
function sendMail($to, $toname, $title, $content) {
    $result = array('statuscode' => 200, 'message' => '');
    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->SMTPSecure = "ssl";
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,$toname);
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    //$mail->Send();
    if (!$mail->Send()) {
        $result['statuscode'] = 300;
        $result['message'] = $mail->ErrorInfo;
    } else {
        $result['statuscode'] = 200;
        $result['message'] = '邮件发送至'.$toname.'['.$to.']成功';
    }
    return $result;
}

/**
 * php截取指定两个字符之间字符串，默认字符集为utf-8 Power by 大耳朵图图
 * @param string $begin  开始字符串
 * @param string $end    结束字符串
 * @param string $str    需要截取的字符串
 * @return string
 */
function cut($begin,$end,$str){
    $b = mb_strpos($str,$begin) + mb_strlen($begin);
    $e = mb_strpos($str,$end) - $b;

    return mb_substr($str,$b,$e);
}


/*
 * php截取指定两个字符之间字符串
 * */
function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $substr;

}


function Getgoodsallchildcate($cateid,$outtype=1){
    //初始化ID数组
    $array[] = $cateid;
    do {
        $ids = '';
        $where['parentid'] = array('in',$cateid);
        $cate = M('goods_category')->where($where)->select();
        foreach ($cate as $k=>$v) {
            $array[] = $v['cateid'];
            $ids .= ',' . $v['cateid'];
        }
        $ids = substr($ids, 1, strlen($ids));
        $cateid = $ids;
    } while (!empty($cate));
    $ids = implode(',', $array);
    if($outtype==1){
        return $array; //返回数组
    }else{
        return $ids;	//	返回字符串
    }
}

function getAllJfChildcateIds($categoryID){
    //初始化ID数组
    $array[] = $categoryID;
    do {
        $ids = '';
        $where['parentid'] = array('in',$categoryID);
        $cate = M('jfgoods_category')->where($where)->select();
        foreach ($cate as $k=>$v) {
            $array[] = $v['cateid'];
            $ids .= ',' . $v['cateid'];
        }
        $ids = substr($ids, 1, strlen($ids));
        $categoryID = $ids;
    } while (!empty($cate));
    $ids = implode(',', $array);
    //return $ids;	//	返回字符串
    return $array; //返回数组
}

//获取指定分类的所有子分类ID号
function Getarticlecateids($categoryID){
    //初始化ID数组
    $array[] = $categoryID;
    do {
        $ids = '';
        $where['parentid'] = array('in',$categoryID);
        $cate = M('article_category')->where($where)->select();
        foreach ($cate as $k=>$v) {
            $array[] = $v['cateid'];
            $ids .= ',' . $v['cateid'];
        }
        $ids = substr($ids, 1, strlen($ids));
        $categoryID = $ids;
    } while (!empty($cate));
    $ids = implode(',', $array);
    //return $ids;	//	返回字符串
    return $array; //返回数组
}

//过滤微信昵称的特殊字符
function filterEmoji($str){
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);

    return $str;
}

function removeEmoji($nickname) {
    $clean_text = "";
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $nickname);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);

    return $clean_text;
}

function emoji_encode($nickname){
    $strEncode = '';
    $length = mb_strlen($nickname,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($nickname,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

function jsonName($str) {
    if($str){
        $tmpStr = json_encode($str);
        $tmpStr2 = preg_replace("#(\\\ue[0-9a-f]{3})#ie","",$tmpStr);
        $tmpStr2 = preg_replace("#(\\\ud[0-9a-f]{3})#ie","",$tmpStr2);
        $return = json_decode($tmpStr2);
        if(!$return){
            return jsonName($return);
        }
    }else{
        $return = '微信用户-'.time();
    }
    return $return;
}


function priceformat($pricestr,$cssext=''){
    $pricestr = (string)$pricestr;
    $newstr='';
    for($i=0;$i<strlen($pricestr);$i++){
        $tempstr = $pricestr[$i];
        switch ($tempstr){
            case '1':
                $tempstr=str_replace('1','<i class="icon icon-finyear'.$cssext.'">&#xe600;</i>',$tempstr);
                break;
            case '2':
                $tempstr=str_replace('2','<i class="icon icon-finyear'.$cssext.'">&#xe601;</i>',$tempstr);
                break;
            case '3':
                $tempstr=str_replace('3','<i class="icon icon-finyear'.$cssext.'">&#xe602;</i>',$tempstr);
                break;
            case '4':
                $tempstr=str_replace('4','<i class="icon icon-finyear'.$cssext.'">&#xe603;</i>',$tempstr);
                break;
            case '5':
                $tempstr=str_replace('5','<i class="icon icon-finyear'.$cssext.'">&#xe604;</i>',$tempstr);
                break;
            case '6':
                $tempstr=str_replace('6','<i class="icon icon-finyear'.$cssext.'">&#xe605;</i>',$tempstr);
                break;
            case '7':
                $tempstr=str_replace('7','<i class="icon icon-finyear'.$cssext.'">&#xe60A;</i>',$tempstr);
                break;
            case '8':
                $tempstr=str_replace('8','<i class="icon icon-finyear'.$cssext.'">&#xe606;</i>',$tempstr);
                break;
            case '9':
                $tempstr=str_replace('9','<i class="icon icon-finyear'.$cssext.'">&#xe607;</i>',$tempstr);
                break;
            case '0':
                $tempstr=str_replace('0','<i class="icon icon-finyear'.$cssext.'">&#xe608;</i>',$tempstr);
                break;
            case '.':
                $tempstr=str_replace('.','<i class="icon icon-finyear'.$cssext.' icon-finyeard'.$cssext.'">&#xe609;</i>',$tempstr);
                break;
        }
        $newstr.=$tempstr;
    }
    return $newstr;
}

function getnumcode($num){
    switch ($num){
        case 1:
            return '&#xe600;';
            break;
        case 2:
            return '&#xe601;';
            break;
        case 3:
            return '&#xe602;';
            break;
        case 4:
            return '&#xe603;';
            break;
        case 5:
            return '&#xe604;';
            break;
        case 6:
            return '&#xe605;';
            break;
        case 7:
            return '&#xe60A;';
            break;
        case 8:
            return '&#xe606;';
            break;
        case 9:
            return '&#xe607;';
            break;
        case 0:
            return '&#xe608;';
            break;
    }
}

function getmoncode($num){
    switch ($num){
        case 1:
            return '&#xe602;';
            break;
        case 2:
            return '&#xe603;';
            break;
        case 3:
            return '&#xe604;';
            break;
        case 4:
            return '&#xe605;';
            break;
        case 5:
            return '&#xe606;';
            break;
        case 6:
            return '&#xe607;';
            break;
        case 7:
            return '&#xe608;';
            break;
        case 8:
            return '&#xe609;';
            break;
        case 9:
            return '&#xe60A;';
            break;
        case 0:
            return '&#xe601;';
            break;
    }
}


//加密AES
function jiami($str){
    $privateKey = C('AES_CONFIG.AES_KEY');
    $iv = C('AES_CONFIG.AES_IV');
    return openssl_encrypt($str, 'aes-256-cbc', $privateKey, 0,$iv);
    /*
     * $t1=base64_encode(openssl_encrypt($str, 'AES-128-CBC', 'boundary', OPENSSL_RAW_DATA, $iv));
        var_dump($t1);
        $t=openssl_decrypt(base64_decode($tstr), 'AES-128-CBC', 'boundary', OPENSSL_RAW_DATA, $iv);
     */
}
//解密
function jiemi($str){
    $privateKey = C('AES_CONFIG.AES_KEY');
    $iv = C('AES_CONFIG.AES_IV');
    return openssl_decrypt($str, 'aes-256-cbc', $privateKey, 0, $iv);
}

//获取指定分类的所有子分类ID号
function getChildcateId($categoryID){
    //初始化ID数组
    $array[] = $categoryID;
    do {
        $ids = '';
        $where['parentid'] = array('in',$categoryID);
        $cate = M('goods_category')->where($where)->select();
        foreach ($cate as $k=>$v) {
            $array[] = $v['cateid'];
            $ids .= ',' . $v['cateid'];
        }
        $ids = substr($ids, 1, strlen($ids));
        $categoryID = $ids;
    } while (!empty($cate));
    $ids = implode(',', $array);
    //return $ids;	//	返回字符串
    return $array; //返回数组

    // goods_category cateid, catename, rootid, path, parentid, depth, ordernum, goodscount
}

//获取指定分类的所有子分类ID号
function getArticleChildcateId($categoryID){
    //初始化ID数组
    $array[] = $categoryID;
    do {
        $ids = '';
        $where['parentid'] = array('in',$categoryID);
        $cate = M('article_category')->where($where)->select();
        foreach ($cate as $k=>$v) {
            $array[] = $v['cateid'];
            $ids .= ',' . $v['cateid'];
        }
        $ids = substr($ids, 1, strlen($ids));
        $categoryID = $ids;
    } while (!empty($cate));
    $ids = implode(',', $array);
    //return $ids;	//	返回字符串
    return $array; //返回数组

    // goods_category cateid, catename, rootid, path, parentid, depth, ordernum, goodscount
}

function Showqrerrimg(){
    header('Content-Type: image/png');
    readfile('./'.__IMAGES__.'/qrcodeerror.png');
    exit;
}

function Showbcerrimg(){
    header('Content-Type: image/png');
    readfile('./'.__IMAGES__.'/barerror.png');
    exit;
}

function convertText($text) {
    $text = stripslashes($text);
    if (function_exists('mb_convert_encoding')) {
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    return $text;
}

function CheckEAN13($code){
    $chkcode=(string)$code;
    $chknum=(($chkcode[1]+$chkcode[3]+$chkcode[5]+$chkcode[7]+$chkcode[9]+$chkcode[11])*3+$chkcode[0]+$chkcode[2]+$chkcode[4]+$chkcode[6]+$chkcode[8]+$chkcode[10])%10;

    $chknum=$chknum==0?0:10-$chknum;
    return array('code'=>substr($chkcode,0,12).$chknum,'chknum'=>$chknum);
}

function CheckEAN8($code){
    $chkcode=(string)$code;
    $chkcode='00000'.$chkcode;
    $chknum=(($chkcode[1]+$chkcode[3]+$chkcode[5]+$chkcode[7]+$chkcode[9]+$chkcode[11])*3+$chkcode[0]+$chkcode[2]+$chkcode[4]+$chkcode[6]+$chkcode[8]+$chkcode[10])%10;

    $chknum=$chknum==0?0:10-$chknum;
    return array('code'=>substr($chkcode,0,12).$chknum,'chknum'=>$chknum);
}

function isbn_sum($isbn, $len){
    /*
     * 该函数用于计算ISBN加权和
     * 参数说明：
     *   $isbn : isbn码
     *   $len  : isbn码长度
     */
    $sum = 0;

    if ($len == 10)
    {
        for ($i = 0; $i < $len-1; $i++)
        {
            $sum = $sum + (int)$isbn[$i] * ($len - $i);
        }
    }
    elseif ($len == 13)
    {
        for ($i = 0; $i < $len-1; $i++)
        {
            if ($i % 2 == 0)
                $sum = $sum + (int)$isbn[$i];
            else
                $sum = $sum + (int)$isbn[$i] * 3;
        }
    }
    return $sum;
}

function isbn_compute($isbn, $len){
    /*
    * 该函数用于计算ISBN末位校验码
    * 参数说明：
    *   $isbn : isbn码
    *   $len  : isbn码长度
    */

    if ($len == 10)
    {
        $digit = 11 - $this->isbn_sum($isbn, $len) % 11;

        if ($digit == 10)
            $rc = 'X';
        else if ($digit == 11)
            $rc = '0';
        else
            $rc = (string)$digit;
    }
    else if($len == 13)
    {
        $digit = 10 - $this->isbn_sum($isbn, $len) % 10;

        if ($digit == 10)
            $rc = '0';
        else
            $rc = (string)$digit;
    }

    return $rc;
}

function is_isbn($isbn){
    /*
     * 该函数用于判断是否为ISBN号
     * 参数说明：
     *    $isbn : isbn码
     */
    $len = strlen($isbn);

    if ($len!=10 && $len!=13)
        return 0;

    $rc = $this->isbn_compute($isbn, $len);

    if ($isbn[$len-1] != $rc)   /* ISBN尾数与计算出来的校验码不符 */
        return 0;
    else
        return 1;
}

/*
 * 图片base64编码
 */
function base64EncodeImage ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

function base64EncodeImage_simple ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = chunk_split(base64_encode($image_data));
    return $base64_image;
}

function Gisformat($str){
    $arrprov=array('北京','天津','上海','重庆','安徽','福建','甘肃','广东','贵州','河北','黑龙江','河南','湖北','湖南','吉林','江西','江苏','辽宁','山东','陕西','山西','四川','云南','浙江','青海','海南','广西','内蒙古','宁夏','新疆','西藏','澳门','台湾','香港');
    $data=array();
    $ischk=false;
    foreach ($arrprov as $key=>$vo) {
        //  mb_strlen($name,'UTF8')   mb_substr(strip_tags($vo['speciality']),0,45,'utf-8')
        $len=mb_strlen($vo,'UTF8');
        $prov=mb_substr($str,0,$len,'utf-8');
        if($prov==$vo){
            $data['country']='中国';
            $data['prov']=$vo;
            $data['city']=str_replace($vo,'',$str);
            $ischk=true;
        }
    }
    if(!$ischk){
        $data['country']='国外';
        $data['prov']=$str;
        $data['city']='';
    }
    return $data;
}

//获取所有控制器名称
function getController($module){
    if(empty($module)) return null;
    $module_path = APP_PATH . '/' . $module . '/Controller/';  //控制器路径
    if(!is_dir($module_path)) return null;
    $module_path .= '/*.class.php';
    $ary_files = glob($module_path);
    foreach ($ary_files as $file) {
        if (is_dir($file)) {
            continue;
        }else {
            $files[] = basename($file, C('DEFAULT_C_LAYER').'.class.php');
        }
    }
    return $files;
}
//获取所有方法名称
function getAction($controller){
    if(empty($controller)) return null;
    $con = A($controller);
    $functions = get_class_methods($con);
    //排除部分方法
    $inherents_functions = array('_initialize','__construct','getActionName','isAjax','display','show','fetch','buildHtml','assign','__set','get','__get','__isset','__call','error','success','ajaxReturn','redirect','__destruct', '_empty');
    foreach ($functions as $func){
        if(!in_array($func, $inherents_functions)){
            $customer_functions[] = $func;
        }
    }
    return $customer_functions;
}



/**
 * 简单对称加密算法之加密
 * @param String $string 需要加密的字串
 * @param String $skey 加密EKY
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
function encode($string = '', $skey = 'hzh369') {
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
}

/**
 * 简单对称加密算法之解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
function decode($string = '', $skey = 'hzh369') {
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
}

/*
 * 校验是否授权，授权继续，没授权转到授权页面
 * */
function check_wx_auth(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $Wechat =new \My\Wechatauth();
    $auth_url = $Wechat->get_authorize_url($http_type.$_SERVER['HTTP_HOST'].U('/Wechat/Index/Wxreturnurl'),'1','snsapi_base');
    $auth_access_token = session('authtoken');

    if(!session('?logincount')){
        $logincount=0;
    }else{
        $logincount=session('logincount');
        $logincount=(int)$logincount;
    }
    $logincount++;
    session('logincount',$logincount);

    if (!empty($auth_access_token)) {
        $auth_state = $Wechat->check_auth_access_token($auth_access_token['access_token'],$auth_access_token['openid']); //检验token是否可用(成功的信息："errcode":0,"errmsg":"ok")

        $access_token = S('access_token');
        if(empty($access_token)){
            $access_token_data = $Wechat->get_access_token();
            $access_token = $access_token_data['access_token'];
            S('access_token',$access_token);
        }
        $user_info = $Wechat->get_user_info($access_token,$auth_access_token['openid']);
        if($user_info['errcode']>0){
            S('access_token',null);
            $access_token = S('access_token');
            if(empty($access_token)){
                $access_token_data = $Wechat->get_access_token();
                $access_token = $access_token_data['access_token'];
                S('access_token',$access_token);
            }
            $user_info = $Wechat->get_user_info($access_token,$auth_access_token['openid']);
        }

        if($user_info['subscribe'] == 0){
            session('subscribe','no');
        }else{
            session('subscribe','yes');
        }

        redirect($auth_url, 0, '系统正在授权中...');
        /*
        if($auth_state['errcode']==0){
            session('wxauth.openid',$auth_access_token['openid']);
        }else{
            redirect($auth_url, 0, '系统正在授权中...');
        }
         */
    }else{
        redirect($auth_url, 0, '系统正在授权中...');
    }
}

function check_srvwx_auth(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $Wechat =new \My\Wechatauth();
    $auth_url = $Wechat->get_authorize_url($http_type.$_SERVER['HTTP_HOST'].U('/Wechat/Index/Wxsrvreturnurl'),'1','snsapi_base');
    $auth_access_token = session('authtoken');

    if (!empty($auth_access_token)) {
        $auth_state = $Wechat->check_auth_access_token($auth_access_token['access_token'],$auth_access_token['openid']); //检验token是否可用(成功的信息："errcode":0,"errmsg":"ok")

        $access_token = S('access_token');
        if(empty($access_token)){
            $access_token_data = $Wechat->get_access_token();
            $access_token = $access_token_data['access_token'];
            S('access_token',$access_token);
        }
        $user_info = $Wechat->get_user_info($access_token,$auth_access_token['openid']);
        if($user_info['errcode']>0){
            S('access_token',null);
            $access_token = S('access_token');
            if(empty($access_token)){
                $access_token_data = $Wechat->get_access_token();
                $access_token = $access_token_data['access_token'];
                S('access_token',$access_token);
            }
            $user_info = $Wechat->get_user_info($access_token,$auth_access_token['openid']);
        }

        if($user_info['subscribe'] == 0){
            session('subscribe','no');
        }else{
            session('subscribe','yes');
        }

        redirect($auth_url, 0, '系统正在授权中...');
        /*
        if($auth_state['errcode']==0){
            session('wxauth.openid',$auth_access_token['openid']);
        }else{
            redirect($auth_url, 0, '系统正在授权中...');
        }
         */
    }else{
        redirect($auth_url, 0, '系统正在授权中...');
    }
}

//获取多媒体文件的MediaID
function GetMediaID($img,$mediatype){
    $Wechat =new \My\Wechatauth();

    if(class_exists('\CURLFile')){
        $postdata=array('media'=>new \CURLFile(realpath($img)));
    }else{
        $postdata=array('media'=>'@'.realpath($img));
    }

    $access_token = S('access_token');
    if(empty($access_token)){
        $access_token_data = $Wechat->get_access_token();
        $access_token = $access_token_data['access_token'];
        S('access_token',$access_token);
    }
    $posturl = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$mediatype;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $posturl);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (class_exists('\CURLFile')) {
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
    } else {
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

    $response = curl_exec($ch);

    if ($response  === FALSE) {
        curl_close($ch);
        $result['type'] = $mediatype;
        $result['media_id'] = '';
        $result['created_at'] = time();
        return json_encode($result);
    }
    curl_close($ch);
    return $response;
}

//下载微信头像
function downloadImageFromWeiXin($url,$config,$openid=''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    //return array_merge(array('body' => $package), array('header' => $httpinfo));

    $heads = get_headers($url);

    $fileType = strtolower(strrchr($heads[4], '/'));
    $fileType = str_replace('/','.',$fileType);
    if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
        $fileType = strtolower(strrchr($heads[2], '/'));
        $fileType = str_replace('/','.',$fileType);
        if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
            $data=array(
                'state'=>'链接contentType不正确',
            );
            return $data;
        }
    }

    $dirname=$config['rootPath'] . $config['savePath'];
    $file['filesize']=strlen($package);
    if($config['savePath']=='userqr/'){
        if($openid!=''){
            $file['name']= $openid .  $fileType;
        }else{
            $file['name']= uniqid() .  $fileType;
        }

    }elseif($config['savePath']=='user/wxqrcode/'){
        if($openid!=''){
            $file['name']= 'qrcode_'.$openid .  $fileType;
        }else{
            $file['name']= uniqid() .  $fileType;
        }

    }else{
        $file['name']= uniqid() .  $fileType;
    }

    $file['fullName']=$dirname . $file['name'];
    $fullName=$file['fullName'];

    $FileUtil =new \My\FileUtil();
    $FileUtil->connect();
    //移动文件
    if (!$FileUtil->writeFile($fullName, $package)) { //移动失败
        $data=array(
            'state'=>'写入文件内容错误',
        );
    } else { //移动成功
        $data=array(
            'state'=>'SUCCESS',
            'url'=>$FileUtil->encodeUrl($file['fullName']),
        );
    }
    return $data;
}

function savewxface($config, $imgUrl,$openid=''){
    $imgUrl = htmlspecialchars($imgUrl);
    $imgUrl = str_replace("&amp;", "&", $imgUrl);

    //http开头验证
    if (strpos($imgUrl, "http") !== 0 and strpos($imgUrl, "https") !== 0) {
        $data=array(
            'state'=>'链接不是http链接',
        );
        return $data;
    }
    //获取请求头并检测死链
    $heads = get_headers($imgUrl);
    if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
        $data=array(
            'state'=>'链接不可用',
        );
        return $data;
    }
    $Typestr = '';
    $Lengthstr='';
    foreach ($heads as $headitem) {
        $headitem=trim($headitem,' ');
        if(stripos($headitem,'Content-Type')===0){
            $Typestr=$headitem;
        }
        if(stripos($headitem,'Content-Length')===0){
            $Lengthstr=$headitem;
        }
    }

    //格式验证(扩展名验证和Content-Type验证)
    $fileType = strtolower(strrchr($Typestr, '/'));
    $fileType = str_replace('/','.',$fileType);
    if (!in_array($fileType, $config['allowFiles'])) {
        $data=array(
            'state'=>'链接contentType不正确'.$fileType,
        );
        return $data;
    }

    //初始化CURL并获取远程图片
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $imgUrl);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $img=curl_exec($curl);
    curl_close($curl);

    preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

    $dirname=$config['rootPath'] . $config['savePath'];
    $file['oriName']=$m ? $m[1]:"";
    $file['filesize']=strlen($img);
    if($openid!=''){
        $file['name']= $openid .  $fileType;
    }else{
        $file['name']= uniqid() .  $fileType;
    }

    $file['fullName']=$dirname . $file['name'];
    $fullName=$file['fullName'];

    //检查文件大小是否超出限制
    if ($file['filesize'] >= ($config["maxSize"])) {
        $data=array(
            'state'=>'文件大小超出网站限制',
        );
        return $data;
    }

    $FileUtil =new \My\FileUtil();
    $FileUtil->connect();
    //移动文件
    if (!$FileUtil->writeFile($fullName, $img)) { //移动失败
        $data=array(
            'state'=>'写入文件内容错误',
        );
        return $data;
    } else { //移动成功
        $data=array(
            'state'=>'SUCCESS',
            'url'=>$FileUtil->encodeUrl($file['fullName']),
        );
    }
    return $data;
}

function saveRemote($config, $imgUrl,$openid=''){
    $imgUrl = htmlspecialchars($imgUrl);
    $imgUrl = str_replace("&amp;", "&", $imgUrl);

    //http开头验证
    if (strpos($imgUrl, "http") !== 0 and strpos($imgUrl, "https") !== 0) {
        $data=array(
            'state'=>'链接不是http链接',
        );
        return $data;
    }
    //获取请求头并检测死链
    $heads = get_headers($imgUrl);
    if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
        $data=array(
            'state'=>'链接不可用',
        );
        return $data;
    }
    $Typestr = '';
    $Lengthstr='';
    foreach ($heads as $headitem) {
        $headitem=trim($headitem,' ');
        if(stripos($headitem,'Content-Type')===0){
            $Typestr=$headitem;
        }
        if(stripos($headitem,'Content-Length')===0){
            $Lengthstr=$headitem;
        }
    }

    print_r($Typestr);
    print_r($Lengthstr);

   /*
    *  [4] => Content-Type: image/png
    [5] => Content-Length: 249880

    */


    //格式验证(扩展名验证和Content-Type验证)
    $fileType = strtolower(strrchr($Typestr, '/'));
    $fileType = str_replace('/','.',$fileType);
    if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
        $fileType = strtolower(strrchr($heads[2], '/'));
        $fileType = str_replace('/','.',$fileType);
        if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
            $data=array(
                'state'=>'链接contentType不正确'.$fileType,
            );
            return $data;
        }
    }
    exit;
    //初始化CURL并获取远程图片

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $imgUrl);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $img=curl_exec($curl);
    curl_close($curl);

    preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

    $dirname=$config['rootPath'] . $config['savePath'];
    $file['oriName']=$m ? $m[1]:"";
    $file['filesize']=strlen($img);
    if($openid!=''){
        $file['name']= $openid .  $fileType;
    }else{
        $file['name']= uniqid() .  $fileType;
    }

    $file['fullName']=$dirname . $file['name'];
    $fullName=$file['fullName'];

    //检查文件大小是否超出限制
    if ($file['filesize'] >= ($config["maxSize"])) {
        $data=array(
            'state'=>'文件大小超出网站限制',
        );
        return $data;
    }

    $FileUtil =new \My\FileUtil();
    $FileUtil->connect();
    //移动文件
    if (!$FileUtil->writeFile($fullName, $img)) { //移动失败
        $data=array(
            'state'=>'写入文件内容错误',
        );
        return $data;
    } else { //移动成功
        $data=array(
            'state'=>'SUCCESS',
            'url'=>$FileUtil->encodeUrl($file['fullName']),
        );
    }
    return $data;
}

function saveRemoteFix($config, $imgUrl,$openid=''){
    $imgUrl = htmlspecialchars($imgUrl);
    $imgUrl = str_replace("&amp;", "&", $imgUrl);

    //http开头验证
    if (strpos($imgUrl, "http") !== 0) {
        $data=array(
            'state'=>'链接不是http链接',
        );
        return $data;
    }
    //获取请求头并检测死链
    $heads = get_headers($imgUrl);
    if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
        $data=array(
            'state'=>'链接不可用',
        );
        return $data;
    }

    //格式验证(扩展名验证和Content-Type验证)
    $fileType = strtolower(strrchr($heads[3], '/'));
    $fileType = str_replace('/','.',$fileType);
    if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
        $fileType = strtolower(strrchr($heads[2], '/'));
        $fileType = str_replace('/','.',$fileType);
        if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
            $data=array(
                'state'=>'链接contentType不正确',
            );
            return $data;
        }
    }

    $img = file_get_contents($imgUrl);

    preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

    $dirname=$config['rootPath'] . $config['savePath'];
    $file['oriName']=$m ? $m[1]:"";
    $file['filesize']=strlen($img);
    if($openid!=''){
        $file['name']= $openid .  $fileType;
    }else{
        $file['name']= uniqid() .  $fileType;
    }
    $file['fullName']=$dirname . $file['name'];
    $fullName=$file['fullName'];

    //检查文件大小是否超出限制
    if ($file['filesize'] >= ($config["maxSize"])) {
        $data=array(
            'state'=>'文件大小超出网站限制',
        );
        return $data;
    }

    $FileUtil =new \My\FileUtil();
    $FileUtil->connect();
    //移动文件
    if (!$FileUtil->writeFile($fullName, $img)) { //移动失败
        $data=array(
            'state'=>'写入文件内容错误',
        );
        return $data;
    } else { //移动成功
        $data=array(
            'state'=>'SUCCESS',
            'url'=>$FileUtil->encodeUrl($file['fullName']),
        );
    }
    return $data;
}


/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function islogin(){
    $uid = session('user.uid');
    if (empty($uid)) {
        return 0;
    } else {
        return $uid;
    }
}

/**
 * 检测专员是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function issrverlogin(){
    $srverid = session('srver.srverid');
    if (empty($srverid)) {
        return 0;
    } else {
        return $srverid;
    }
}

/*
检测管理员是否登录
*/
function is_administrator(){
    $auid = session('admin.uid');
    if (empty($auid)) {
        return 0;
    } else {
        return $auid;
    }
}

/*
模拟单选
*/
function IsActive($p,$n){
    if($p==$n){
        return ' active';
    }else{
        return '';
    }
}
/*
模拟单选扩展
*/
function IsActive_exp($p,$n){
    if(in_array($p,$n)){
        return ' active';
    }else{
        return '';
    }
}
/*
下拉框是否选中
*/
function IsSelected($p,$n){
	if($p==$n){
		return ' selected';
	}else{
		return '';
	}
}

function IsSelected_exp($p,$n){
    if(in_array($p,$n)){
        return ' selected';
    }else{
        return '';
    }
}
/*
单选 复选是否选中
*/
function IsChecked($p,$n){
	if($p==$n){
		return ' checked="checked"';
	}else{
		return '';
	}
}

/*
模拟单选
*/
function IsDisplay($p,$n){
    if($p==$n){
        return '';
    }else{
        return ' style="display:none"';
    }
}

function IsDisplay_exp($p,$n){
    if(in_array($p,$n)){
        return '';
    }else{
        return ' style="display:none"';
    }
}

function shtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = shtmlspecialchars($val);
        }
    } else {
        $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
            str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
    }
    return $string;
}

/* 验证邮箱 */
function is_email($email){
    return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/",$email);
}

/* 验证URL */
function is_url($str){
    return preg_match("/^(https:|http:)\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\"])*$/", $str);
}

/* 验证英文 */
function is_en($str){
    return preg_match("/^[a-zA-Z\s]+$/", $str);
}

/* 验证非中文 */
function is_notcn($str){
    return preg_match("/^[a-zA-Z0-9\s]+$/", $str);
}

/* 验证数字 */
function is_number($str){
    return preg_match("/^[0-9\s]+$/", $str);
}

/* 验证中文 */
function is_cn($str){
    return preg_match("/^[\x7f-\xff]+$/", $str);
}

/* 验证手机 */
function is_mobile($str){
//    return preg_match("/^((\(\d{3}\))|(\d{3}\-))?1\d{10}$/", $str);
    return preg_match("/^((\(\d{3}\))|(\d{3}\-))?1\d{10}|7\d{9}$/", $str);
}

/* 验证电话 */
function is_tel($str){
    return preg_match("/^([0-9]{3,4}-)?[0-9]{7,8}$/",$str);
}

/* 验证文件夹 */
function is_folder($str){
    return preg_match("/^[0-9a-zA-Z\/]{1,50}$/",$str);
}
function is_filename($str){
    return preg_match("/^[A-Za-z0-9]+\.(html|htm|shtml)$/", $str);
}

/*
生成签名 xml+apikey
*/
function build_sign($x,$k){
	$sign = substr(md5($x.$k),8,16);
	return $sign;
}

/*
距离格式化
 */
function formatdistance($me,$unit=''){
    if($me>=1000){
        $fme=round($me/1000,2);
        if($unit!=''){
            $fme=$fme.$unit;
        }
    }else{
        $fme=$me;
        if($unit!=''){
            if(is_en($unit)){
                $fme=$fme.'M';
            }else{
                $fme=$fme.'米';
            }
        }
    }
    return $fme;
}

/*
重建时间，将字串时间
*/
function build_time($str){
	if($str==''){
		return date('Y-m-d H:i:s',time());
	}
	return date('Y-m-d H:i:s',mktime(substr($str,8,2),substr($str,10,2),substr($str,12,2),substr($str,4,2),substr($str,6,2),substr($str,0,4)));
}
/*
 * 将秒数转为时分秒格式的时间
 */
function sec2time($str){
    if($str>=3600){
        $hour = floor($str/3600);
        if(fmod($str,3600)>=60){
            $min=floor(fmod($str,3600)/60);
            if(fmod(fmod($str,3600),60)>0){
                $sec=fmod(fmod($str,3600),60);
            }else{
                $sec=0;
            }
        }else{
            $min=0;
            $sec = fmod($str,3600);
        }
    }elseif($str>=60){
        $hour=0;
        $min=floor($str/60);
        if(fmod($str,60)>0){
            $sec=fmod($str,60);
        }else{
            $sec=0;
        }
    }else{
        $hour=0;
        $min=0;
        $sec=$str;
    }
    if($hour>0){
        $time=$hour.'时';
    }
    if($min>0){
        $time.=$min.'分';
    }
    if($sec>0){
        $time.=$sec.'秒';
    }
    return $time;
}

/*
if($vo['videotime']>=3600){
    $list[$key]['videotime'] = floor($vo['videotime']/3600)."时".floor(fmod($vo['videotime'],3600)/60)."分".fmod(fmod($vo['videotime'],3600),60)."秒";
}elseif($vo['videotime']>=60){
    $list[$key]['videotime'] = floor(($vo['videotime'])/60)."分".fmod($vo['videotime'],60)."秒";
}else{
    $list[$key]['videotime'] = $vo['videotime']."秒";
}
 */

/*
问候
*/
function Hello($str=''){
	$h=date("H"); 
	if($h<6){
		return "凌晨好！";
	}elseif($h<9){
		return "早上好！";
	}elseif($h<12){
		return "上午好！";
    }elseif($h<14){
        return "中午好！";
    }elseif($h<18){
        return "下午好！";
    }elseif($h<19){
        return "傍晚好！";
	}else{
		return "晚上好！"; 
	}
}

/* 计算时间函数 */
function DateAdd($part, $number, $date){
	$date_array = getdate(strtotime($date));
	$hor = $date_array["hours"];
	$min = $date_array["minutes"];
	$sec = $date_array["seconds"];
	$mon = $date_array["mon"];
	$day = $date_array["mday"];
	$yar = $date_array["year"];
	switch($part){
		case "y": $yar += $number; break;
		case "q": $mon += ($number * 3); break;
		case "m": $mon += $number; break;
		case "w": $day += ($number * 7); break;
		case "d": $day += $number; break;
		case "h": $hor += $number; break;
		case "n": $min += $number; break;
		case "s": $sec += $number; break;
	}
	return date("Y-m-d H:i:s", mktime($hor, $min, $sec, $mon, $day, $yar));
}
/* 计算时间差 */
function DateDiff($part, $begin, $end,$type=0){
    if($type==0){
        $diff = $end - $begin;
    }else{
        $diff = strtotime($end) - strtotime($begin);
    }
	switch($part){
		case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
		case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
		case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
		case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
		case "h": $retval = bcdiv($diff, (60 * 60)); break;
		case "n": $retval = bcdiv($diff, 60); break;
		case "s": $retval = $diff; break;
	}
	return $retval;
}

/*
post方式发送数据
*/
function send_post($url,$post_data){
    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

function https_post($url,$data=null){
    $curl=curl_init();

    //$this_header = array('content-type:application/x-www-form-urlencoded;charset=gb2312');

    //curl_setopt($curl,CURLOPT_HTTPHEADER,$this_header);



    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

function SendSMS($tempid,$mobile,$k1,$k2='',$k3=''){
    $url=U('/Syn/Sms/Sendtemp');
    $pdata = array(
        'tempid' => $tempid,
        'mobile' => $mobile,
        'k1' => $k1,
        'k2' => $k2,
        'k3' => $k3,
    );
    $url = 'http://'.$_SERVER['HTTP_HOST'].$url;

    $send_result = https_post($url,$pdata);

    //print_r($send_result);

    //exit;
    //$pdata = json_encode($pdata);
    //$pdata = http_build_query($pdata);
    //$url = 'http://'.$_SERVER['HTTP_HOST'].$url.'?'.$pdata;
    //$resultx = file_get_contents($url);

    //https_post($url,$pdata);

}

function Refund($paynum,$amount_total,$amount_refund){
    if($paynum==''){
        $result['message'] = '订单提交退款失败，请重试！';
        $result['statuscode'] = 300;
        return $result;
    }
    if($amount_total<=0){
        $result['message'] = '订单提交退款失败，请重试！';
        $result['statuscode'] = 300;
        return $result;
    }
    if($amount_refund<=0){
        $result['message'] = '订单提交退款失败，请重试！';
        $result['statuscode'] = 300;
        return $result;
    }
    if($amount_total<$amount_refund){
        $result['message'] = '订单提交退款失败，请重试！';
        $result['statuscode'] = 300;
        return $result;
    }



    $url=U('/Pay/Index/Refund');
    $pdata = array(
        'paynum' => $paynum,
        'amount_total' => $amount_total,
        'amount_refund' => $amount_refund,
    );
    $url = 'http://'.$_SERVER['HTTP_HOST'].$url;
    $send_result = https_post($url,$pdata);
    $send_result = json_decode($send_result,true);
    return $send_result;
}
/*
 * $paynum = I('param.paynum/s','','strip_tags,htmlspecialchars');
$amount_total = I('param.amount_total/f',0);
$amount_refund = I('param.amount_refund/f',0);

n Refund(){
$result = array('statuscode' => 200, 'message' => '');
        $paynum = I('param.paynum/s','','strip_tags,htmlspecialchars');
        $amount_total = I('param.amount_total/f',0);
        $amount_refund = I('param.amount_refund/f',0);

 */

function SendNotice($access_token,$sleep,$userwx,$template_id,$viewurl,$first,$key1='',$key2='',$key3='',$key4='',$key5='',$key6='',$remark=''){
    $template = C($template_id);

    $notice_data = str_replace('@viewurl@',$viewurl,str_replace('@template_id@',$template_id,str_replace('@userwx@',$userwx,$template)));
    $notice_data = str_replace('@first@',$first,$notice_data);
    $notice_data = str_replace('@key1@',$key1,$notice_data);
    $notice_data = str_replace('@key2@',$key2,$notice_data);
    $notice_data = str_replace('@key3@',$key3,$notice_data);
    $notice_data = str_replace('@key4@',$key4,$notice_data);
    $notice_data = str_replace('@key5@',$key5,$notice_data);
    $notice_data = str_replace('@key6@',$key6,$notice_data);
    $notice_data = str_replace('@remark@',$remark,$notice_data);

    $notice_url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
    if($sleep>0){
        sleep($sleep);
    }
    $notice_result = https_post($notice_url,$notice_data);
    $notice_info = json_decode($notice_result,true);
    if($notice_info['errcode']==0){
        return 'ok';
    }else{
        return 'err';
    }
}

/* 
# 函数功能：计算身份证号码中的检校码 
# 函数名称：idcard_verify_number 
# 参数表 ：string $idcard_base 身份证号码的前十七位 
# 返回值 ：string 检校码 
# 更新时间：Fri Mar 28 09:50:19 CST 2008 
*/  
function idcard_verify_number($idcard_base){  
if (strlen($idcard_base) != 17){  
   return false;  
}  
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //debug 加权因子  
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); //debug 校验码对应值  
    $checksum = 0;  
    for ($i = 0; $i < strlen($idcard_base); $i++){  
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];  
    }  
    $mod = $checksum % 11;  
    $verify_number = $verify_number_list[$mod];  
    return $verify_number;  
}  
/* 
# 函数功能：将15位身份证升级到18位 
# 函数名称：idcard_15to18 
# 参数表 ：string $idcard 十五位身份证号码 
# 返回值 ：string 
# 更新时间：Fri Mar 28 09:49:13 CST 2008 
*/  
function idcard_15to18($idcard){  
    if (strlen($idcard) != 15){  
        return false;  
    }else{// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码  
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){  
            $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 15);  
        }else{  
            $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 15);  
        }  
    }  
    $idcard = $idcard . idcard_verify_number($idcard);  
    return $idcard;  
}  
/* 
# 函数功能：18位身份证校验码有效性检查 
# 函数名称：idcard_checksum18 
# 参数表 ：string $idcard 十八位身份证号码 
# 返回值 ：bool 
# 更新时间：Fri Mar 28 09:48:36 CST 2008 
*/  
function idcard_checksum18($idcard){  
    if (strlen($idcard) != 18){ return false; }  
    $idcard_base = substr($idcard, 0, 17);  
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){  
        return false;  
    }else{  
        return true;  
    }  
}  
/* 
# 函数功能：身份证号码检查接口函数 
# 函数名称：check_id 
# 参数表 ：string $idcard 身份证号码 
# 返回值 ：bool 是否正确 
# 更新时间：Fri Mar 28 09:47:43 CST 2008 
*/
function check_id($idcard) {
    if(strlen($idcard) == 15 || strlen($idcard) == 18){
        if(strlen($idcard) == 15){
            $idcard = idcard_15to18($idcard);
        }
        if(idcard_checksum18($idcard)){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

//验证车牌
function isCarLicense($license){
    if (empty($license)) {
        return false;
    }
//#匹配民用车牌和使馆车牌
//# 判断标准
//# 1，第一位为汉字省份缩写
//# 2，第二位为大写字母城市编码
//# 3，后面是5位仅含字母和数字的组合

    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }


//#匹配特种车牌(挂,警,学,领,港,澳)
//#参考 https://wenku.baidu.com/view/4573909a964bcf84b9d57bc5.html

    $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }


//#匹配武警车牌
//#参考 https://wenku.baidu.com/view/7fe0b333aaea998fcc220e48.html

    $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }


//#匹配军牌
//#参考 http://auto.sina.com.cn/service/2013-05-03/18111149551.shtml

    $regular = "/[A-Z]{2}[0-9]{5}$/";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }

//#匹配新能源车辆6位车牌
//#参考 https://baike.baidu.com/item/%E6%96%B0%E8%83%BD%E6%BA%90%E6%B1%BD%E8%BD%A6%E4%B8%93%E7%94%A8%E5%8F%B7%E7%89%8C

    #小型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #大型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    return false;
}

/*
汉字转拼音
*/
function Pinyin($_String, $_Code='UTF8'){ //GBK页面可改为gb2312，其他随意填写为UTF8
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha". 
                        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|". 
                        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er". 
                        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui". 
                        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang". 
                        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang". 
                        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue". 
                        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne". 
                        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen". 
                        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang". 
                        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|". 
                        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|". 
                        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu". 
                        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you". 
                        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|". 
                        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo"; 
        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990". 
                        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725". 
                        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263". 
                        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003". 
                        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697". 
                        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211". 
                        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922". 
                        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468". 
                        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664". 
                        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407". 
                        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959". 
                        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652". 
                        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369". 
                        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128". 
                        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914". 
                        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645". 
                        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149". 
                        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087". 
                        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658". 
                        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340". 
                        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888". 
                        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585". 
                        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847". 
                        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055". 
                        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780". 
                        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274". 
                        "|-10270|-10262|-10260|-10256|-10254"; 
        $_TDataKey   = explode('|', $_DataKey); 
        $_TDataValue = explode('|', $_DataValue);
        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data); 
        reset($_Data);
        if($_Code!= 'gb2312') $_String = _U2_Utf8_Gb($_String); 
        $_Res = ''; 
        for($i=0; $i<strlen($_String); $i++) { 
                $_P = ord(substr($_String, $i, 1)); 
                if($_P>160) { 
                        $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
                } 
                $_Res .= _Pinyin($_P, $_Data); 
        } 
        return preg_replace("/[^a-z0-9]*/", '', $_Res); 
} 
function _Pinyin($_Num, $_Data){ 
        if($_Num>0 && $_Num<160 ){
                return chr($_Num);
        }elseif($_Num<-20319 || $_Num>-10247){
                return '';
        }else{ 
                foreach($_Data as $k=>$v){ if($v<=$_Num) break; } 
                return $k; 
        } 
}
function _U2_Utf8_Gb($_C){ 
        $_String = ''; 
        if($_C < 0x80){
                $_String .= $_C;
        }elseif($_C < 0x800) { 
                $_String .= chr(0xC0 | $_C>>6); 
                $_String .= chr(0x80 | $_C & 0x3F); 
        }elseif($_C < 0x10000){ 
                $_String .= chr(0xE0 | $_C>>12); 
                $_String .= chr(0x80 | $_C>>6 & 0x3F); 
                $_String .= chr(0x80 | $_C & 0x3F); 
        }elseif($_C < 0x200000) { 
                $_String .= chr(0xF0 | $_C>>18); 
                $_String .= chr(0x80 | $_C>>12 & 0x3F); 
                $_String .= chr(0x80 | $_C>>6 & 0x3F); 
                $_String .= chr(0x80 | $_C & 0x3F); 
        } 
        return iconv('UTF-8', 'GB2312', $_String); 
}

function mbstringtoarray($str,$charset) {
    $strlen=mb_strlen($str);
    while($strlen){
        $array[]=mb_substr($str,0,1,$charset);
        $str=mb_substr($str,1,$strlen,$charset);
        $strlen=mb_strlen($str);
    }
    return $array;
}

/**
 * 根据文件后缀获取mime类型
 * @param  string $ext 文件后缀
 * @return string      mime类型
 */
function get_mime_type($ext){
    static $mime_types = array (
        'apk'     => 'application/vnd.android.package-archive',
        '3gp'     => 'video/3gpp', 
        'ai'      => 'application/postscript', 
        'aif'     => 'audio/x-aiff', 
        'aifc'    => 'audio/x-aiff', 
        'aiff'    => 'audio/x-aiff', 
        'asc'     => 'text/plain', 
        'atom'    => 'application/atom+xml', 
        'au'      => 'audio/basic', 
        'avi'     => 'video/x-msvideo', 
        'bcpio'   => 'application/x-bcpio', 
        'bin'     => 'application/octet-stream', 
        'bmp'     => 'image/bmp', 
        'cdf'     => 'application/x-netcdf', 
        'cgm'     => 'image/cgm', 
        'class'   => 'application/octet-stream', 
        'cpio'    => 'application/x-cpio', 
        'cpt'     => 'application/mac-compactpro', 
        'csh'     => 'application/x-csh', 
        'css'     => 'text/css', 
        'dcr'     => 'application/x-director', 
        'dif'     => 'video/x-dv', 
        'dir'     => 'application/x-director', 
        'djv'     => 'image/vnd.djvu', 
        'djvu'    => 'image/vnd.djvu', 
        'dll'     => 'application/octet-stream', 
        'dmg'     => 'application/octet-stream', 
        'dms'     => 'application/octet-stream', 
        'doc'     => 'application/msword',
        'docx'     => 'application/msword',
        'dtd'     => 'application/xml-dtd', 
        'dv'      => 'video/x-dv', 
        'dvi'     => 'application/x-dvi', 
        'dxr'     => 'application/x-director', 
        'eps'     => 'application/postscript', 
        'etx'     => 'text/x-setext', 
        'exe'     => 'application/octet-stream', 
        'ez'      => 'application/andrew-inset', 
        'flv'     => 'video/x-flv', 
        'gif'     => 'image/gif', 
        'gram'    => 'application/srgs', 
        'grxml'   => 'application/srgs+xml', 
        'gtar'    => 'application/x-gtar', 
        'gz'      => 'application/x-gzip', 
        'hdf'     => 'application/x-hdf', 
        'hqx'     => 'application/mac-binhex40', 
        'htm'     => 'text/html', 
        'html'    => 'text/html', 
        'ice'     => 'x-conference/x-cooltalk', 
        'ico'     => 'image/x-icon', 
        'ics'     => 'text/calendar', 
        'ief'     => 'image/ief', 
        'ifb'     => 'text/calendar', 
        'iges'    => 'model/iges', 
        'igs'     => 'model/iges', 
        'jnlp'    => 'application/x-java-jnlp-file', 
        'jp2'     => 'image/jp2', 
        'jpe'     => 'image/jpeg', 
        'jpeg'    => 'image/jpeg', 
        'jpg'     => 'image/jpeg', 
        'js'      => 'application/x-javascript', 
        'kar'     => 'audio/midi', 
        'latex'   => 'application/x-latex', 
        'lha'     => 'application/octet-stream', 
        'lzh'     => 'application/octet-stream', 
        'm3u'     => 'audio/x-mpegurl', 
        'm4a'     => 'audio/mp4a-latm', 
        'm4p'     => 'audio/mp4a-latm', 
        'm4u'     => 'video/vnd.mpegurl', 
        'm4v'     => 'video/x-m4v', 
        'mac'     => 'image/x-macpaint', 
        'man'     => 'application/x-troff-man', 
        'mathml'  => 'application/mathml+xml', 
        'me'      => 'application/x-troff-me', 
        'mesh'    => 'model/mesh', 
        'mid'     => 'audio/midi', 
        'midi'    => 'audio/midi', 
        'mif'     => 'application/vnd.mif', 
        'mov'     => 'video/quicktime', 
        'movie'   => 'video/x-sgi-movie', 
        'mp2'     => 'audio/mpeg', 
        'mp3'     => 'audio/mpeg', 
        'mp4'     => 'video/mp4', 
        'mpe'     => 'video/mpeg', 
        'mpeg'    => 'video/mpeg', 
        'mpg'     => 'video/mpeg', 
        'mpga'    => 'audio/mpeg', 
        'ms'      => 'application/x-troff-ms', 
        'msh'     => 'model/mesh', 
        'mxu'     => 'video/vnd.mpegurl', 
        'nc'      => 'application/x-netcdf', 
        'oda'     => 'application/oda', 
        'ogg'     => 'application/ogg', 
        'ogv'     => 'video/ogv', 
        'pbm'     => 'image/x-portable-bitmap', 
        'pct'     => 'image/pict', 
        'pdb'     => 'chemical/x-pdb', 
        'pdf'     => 'application/pdf', 
        'pgm'     => 'image/x-portable-graymap', 
        'pgn'     => 'application/x-chess-pgn', 
        'pic'     => 'image/pict', 
        'pict'    => 'image/pict', 
        'png'     => 'image/png', 
        'pnm'     => 'image/x-portable-anymap', 
        'pnt'     => 'image/x-macpaint', 
        'pntg'    => 'image/x-macpaint', 
        'ppm'     => 'image/x-portable-pixmap', 
        'ppt'     => 'application/vnd.ms-powerpoint', 
        'ps'      => 'application/postscript', 
        'qt'      => 'video/quicktime', 
        'qti'     => 'image/x-quicktime', 
        'qtif'    => 'image/x-quicktime', 
        'ra'      => 'audio/x-pn-realaudio', 
        'ram'     => 'audio/x-pn-realaudio', 
        'ras'     => 'image/x-cmu-raster', 
        'rdf'     => 'application/rdf+xml', 
        'rgb'     => 'image/x-rgb', 
        'rm'      => 'application/vnd.rn-realmedia', 
        'roff'    => 'application/x-troff', 
        'rtf'     => 'text/rtf', 
        'rtx'     => 'text/richtext', 
        'sgm'     => 'text/sgml', 
        'sgml'    => 'text/sgml', 
        'sh'      => 'application/x-sh', 
        'shar'    => 'application/x-shar', 
        'silo'    => 'model/mesh', 
        'sit'     => 'application/x-stuffit', 
        'skd'     => 'application/x-koan', 
        'skm'     => 'application/x-koan', 
        'skp'     => 'application/x-koan', 
        'skt'     => 'application/x-koan', 
        'smi'     => 'application/smil', 
        'smil'    => 'application/smil', 
        'snd'     => 'audio/basic', 
        'so'      => 'application/octet-stream', 
        'spl'     => 'application/x-futuresplash', 
        'src'     => 'application/x-wais-source', 
        'sv4cpio' => 'application/x-sv4cpio', 
        'sv4crc'  => 'application/x-sv4crc', 
        'svg'     => 'image/svg+xml', 
        'swf'     => 'application/x-shockwave-flash', 
        't'       => 'application/x-troff', 
        'tar'     => 'application/x-tar', 
        'tcl'     => 'application/x-tcl', 
        'tex'     => 'application/x-tex', 
        'texi'    => 'application/x-texinfo', 
        'texinfo' => 'application/x-texinfo', 
        'tif'     => 'image/tiff', 
        'tiff'    => 'image/tiff', 
        'tr'      => 'application/x-troff', 
        'tsv'     => 'text/tab-separated-values', 
        'txt'     => 'text/plain', 
        'ustar'   => 'application/x-ustar', 
        'vcd'     => 'application/x-cdlink', 
        'vrml'    => 'model/vrml', 
        'vxml'    => 'application/voicexml+xml', 
        'wav'     => 'audio/x-wav', 
        'wbmp'    => 'image/vnd.wap.wbmp', 
        'wbxml'   => 'application/vnd.wap.wbxml', 
        'webm'    => 'video/webm', 
        'wml'     => 'text/vnd.wap.wml', 
        'wmlc'    => 'application/vnd.wap.wmlc', 
        'wmls'    => 'text/vnd.wap.wmlscript', 
        'wmlsc'   => 'application/vnd.wap.wmlscriptc', 
        'wmv'     => 'video/x-ms-wmv', 
        'wrl'     => 'model/vrml', 
        'xbm'     => 'image/x-xbitmap', 
        'xht'     => 'application/xhtml+xml', 
        'xhtml'   => 'application/xhtml+xml', 
        'xls'     => 'application/vnd.ms-excel',
        'xlsx'     => 'application/vnd.ms-excel',
        'xml'     => 'application/xml', 
        'xpm'     => 'image/x-xpixmap', 
        'xsl'     => 'application/xml', 
        'xslt'    => 'application/xslt+xml', 
        'xul'     => 'application/vnd.mozilla.xul+xml', 
        'xwd'     => 'image/x-xwindowdump', 
        'xyz'     => 'chemical/x-xyz', 
        'zip'     => 'application/zip' 
    );
    
    return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
}
/*
 * 获取文件的后缀名
 * */
function get_extension($file) {
    return pathinfo($file, PATHINFO_EXTENSION);
}

/*
 * 格式化文件大小
 */
function getfilesize($size) {
    $p = 0;
    $lastsize=$size;
    if($size<1048576){
        $p=1;
        $format='KB';
    }elseif($size<1073741824){
        $p=2;
        $format='MB';
    }elseif($size<1099511627776){
        $p=3;
        $format='GB';
    }
    $lastsize /= pow(1024, $p);
    $result=array(
        'orgsize'=>$size,
        'size'=>number_format($lastsize, 2),
        'format'=>$format,
    );
    return $result;
}

/*
 * 截取字符串，一个中文算两个
 */
function mbsub($str, $length = 0,$ext = "..."){

    if($length < 1){
        return $str;
    }

    //计算字符串长度
    $strlen = (strlen($str) + mb_strlen($str,"UTF-8")) / 2;
    if($strlen < $length){
        return $str;
    }

    if(mb_check_encoding($str,"UTF-8")){
        $str = mb_strcut(mb_convert_encoding($str, "GBK","UTF-8"), 0, $length, "GBK");
        $str = mb_convert_encoding($str, "UTF-8", "GBK");

    }else{

        return "不支持的文档编码";
    }

    $str = rtrim($str," ,.。，-——（【、；‘“??《<@");
    return $str.$ext;
}

/**
 * 生成缩略图
 * @author yangzhiguo0903@163.com
 * @param string     源图绝对完整地址{带文件名及后缀名}
 * @param string     目标图绝对完整地址{带文件名及后缀名}
 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
 * @param int        是否裁切{宽,高必须非0}
 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
 * @return boolean
 */
function img2thumb($src_img, $dst_img, $width = 200, $height = 200, $cut = 0, $proportion = 0){
    if(!is_file($src_img))
    {
        return false;
    }
    $ot = get_extension($dst_img);
    $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
    $srcinfo = getimagesize($src_img);
    $src_w = $srcinfo[0];
    $src_h = $srcinfo[1];
    $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
    $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

    $dst_h = $height;
    $dst_w = $width;
    $x = $y = 0;

    /**
     * 缩略图不超过源图尺寸（前提是宽或高只有一个）
     */
    if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
    {
        $proportion = 1;
    }
    if($width> $src_w)
    {
        $dst_w = $width = $src_w;
    }
    if($height> $src_h)
    {
        $dst_h = $height = $src_h;
    }

    if(!$width && !$height && !$proportion)
    {
        return false;
    }
    if(!$proportion)
    {
        if($cut == 0)
        {
            if($dst_w && $dst_h)
            {
                if($dst_w/$src_w> $dst_h/$src_h)
                {
                    $dst_w = $src_w * ($dst_h / $src_h);
                    $x = 0 - ($dst_w - $width) / 2;
                }
                else
                {
                    $dst_h = $src_h * ($dst_w / $src_w);
                    $y = 0 - ($dst_h - $height) / 2;
                }
            }
            else if($dst_w xor $dst_h)
            {
                if($dst_w && !$dst_h)  //有宽无高
                {
                    $propor = $dst_w / $src_w;
                    $height = $dst_h  = $src_h * $propor;
                }
                else if(!$dst_w && $dst_h)  //有高无宽
                {
                    $propor = $dst_h / $src_h;
                    $width  = $dst_w = $src_w * $propor;
                }
            }
        }
        else
        {
            if(!$dst_h)  //裁剪时无高
            {
                $height = $dst_h = $dst_w;
            }
            if(!$dst_w)  //裁剪时无宽
            {
                $width = $dst_w = $dst_h;
            }
            $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
            $dst_w = (int)round($src_w * $propor);
            $dst_h = (int)round($src_h * $propor);
            $x = ($width - $dst_w) / 2;
            $y = ($height - $dst_h) / 2;
        }
    } else {
        $proportion = min($proportion, 1);
        $height = $dst_h = $src_h * $proportion;
        $width  = $dst_w = $src_w * $proportion;
    }

    $src = $createfun($src_img);
    $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    if(function_exists('imagecopyresampled'))
    {
        imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    else
    {
        imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    $otfunc($dst, $dst_img);
    imagedestroy($dst);
    imagedestroy($src);
    return true;
}

/*
 * 将本地地址转换为当前环境地址
 * @param string $url 要转换的地址。
 * @return string
*/
function encode_Url($url){
    if(preg_match('|^'.__ROOT__.'|i', $url)){
        return $url;
    }else{
        $url=trim($url,'./');
        return __ROOT__.'/'.$url;
    }
}

function HtmlEncode($fString)
{
if($fString!="")
{
     $fString = str_replace( '>', '&gt;',$fString);
     $fString = str_replace( '<', '&lt;',$fString);
     $fString = str_replace( chr(32), '&nbsp;',$fString);
     $fString = str_replace( chr(13), ' ',$fString);
     $fString = str_replace( chr(10) & chr(10), '<br>',$fString);
     $fString = str_replace( chr(10), '<BR>',$fString);
}
     return $fString;
}
function EncodeHtml($fString)
{
if($fString!="")
{
     $fString = str_replace("&gt;" , ">", $fString);
     $fString = str_replace("&lt;", "<", $fString);
     $fString = str_replace("&quot;", "\"", $fString);
		 $fString = str_replace("&amp;", "&", $fString);
     $fString = str_replace("&nbsp;",chr(32),$fString);
     $fString = str_replace("",chr(13),$fString);
     $fString = str_replace("<br>",chr(10) & chr(10),$fString);
     $fString = str_replace("<BR>",chr(10),$fString);
}
     return $fString;
}

/*
 * 获取用户的地址信息，转为名称
 *
 *
 * */
function GetRegionData($country,$prov,$city,$area=0){
    //dict_region region_id, parent_id, region_name, region_type, agency_id, enable
    $country = (int)($country);
    $prov = (int)($prov);
    $city = (int)($city);
    $area = (int)($area);
    if(!is_int($country) or !is_int($prov) or !is_int($city)){
        return array(
            'country'=>'中国',
            'prov'=>'',
            'city'=>'',
            'area'=>'',
        );
        exit;
    }
    if($country>0){
        $countrycode = M('dict_region')->where('region_id='.$country)->getField('region_name');
        if(empty($countrycode)){
            $countrycode = '中国';
        }
    }else{
        $countrycode = '中国';
    }
    if($prov>0){
        $provcode = M('dict_region')->where('region_id='.$prov)->getField('region_name');
        if(empty($provcode)){
            $provcode = '';
        }
    }else{
        $provcode = '';
    }
    if($city>0){
        $citycode = M('dict_region')->where('region_id='.$city)->getField('region_name');
        if(empty($citycode)){
            $citycode = '';
        }
    }else{
        $citycode = '';
    }

    if($area>0){
        $areacode = M('dict_region')->where('region_id='.$area)->getField('region_name');
        if(empty($areacode)){
            $areacode = '';
        }
    }else{
        $areacode = '';
    }
    return array(
        'country'=>$countrycode,
        'prov'=>$provcode,
        'city'=>$citycode,
        'area'=>$areacode,
    );
}

/*
 * 获取用户的地址信息，转为ID
 * */
function GetRegionID($country,$prov,$city='',$area=''){
    if(!($country&$prov)){
        return array(
            'country'=>1,
            'prov'=>0,
            'city'=>0,
            'area'=>0,
        );
        exit;
    }
    if($country!=''){
        $countryid = M('dict_region')->where('parent_id=0 AND region_name="'.$country.'"')->getField('region_id');
        if(empty($countryid)){
            $countryid = 0;
        }
    }else{
        $countryid = 0;
    }
    if($prov!='' AND $countryid>0){
        $provid = M('dict_region')->where('parent_id='.$countryid.' AND region_name="'.$prov.'"')->getField('region_id');
        if(empty($provid)){
            $provid = 0;
        }
    }else{
        $provid = 0;
    }
    if($city!='' AND $provid>0){
        $cityid = M('dict_region')->where('parent_id='.$provid.' AND region_name="'.$city.'"')->getField('region_id');
        if(empty($cityid)){
            $cityid = 0;
        }
    }else{
        $cityid = 0;
    }

    if($area!='' AND $cityid>0){
        $areaid = M('dict_region')->where('parent_id='.$cityid.' AND region_name="'.$area.'"')->getField('region_id');
        if(empty($areaid)){
            $areaid = 0;
        }
    }else{
        $areaid = 0;
    }

    return array(
        'country'=>$countryid,
        'prov'=>$provid,
        'city'=>$cityid,
        'area'=>$areaid,
    );
}


function xml2arr($xml){
    $obj  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_encode($obj);
    $arr  = json_decode($json, true);
    return $arr;
}

/**
+----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
+----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
+----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
    $str = '';
    switch ($type) {
        case 0 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1 :
            $chars = str_repeat('0123456789',4);
            break;
        case 2 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3 :
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4 :
            $chars = 'abcdefghijklmnopqrstuvwxyz1234567890' . $addChars;
            break;
        case 5 :
            $chars = 'ABCDEFG1234567890' . $addChars;
            break;
        case 6 :
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZ1234567890' . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) { //位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );
    }
    if ($type != 5) {
        $chars = str_shuffle ( $chars );
        $str = substr ( $chars, 0, $len );
        while (strlen($str)<$len) {
            $chars = str_shuffle ( $chars );
            $str = substr ( $chars, 0, $len );
        }
    }else{
        // 中文随机字
        for($i = 0; $i < $len; $i ++) {
            $str .= mb_substr ( $chars, floor ( mt_rand ( 0, mb_strlen ( $chars, 'utf-8' ) - 1 ) ), 1 );
        }
    }
    return $str;
}

function ismobileclient() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function iswxclient() {
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('MicroMessenger');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    return false;
}

function isappclient() {
    // Mozilla/5.0 (Linux; Android 8.0.0; MIX 2 Build/OPR1.170623.027; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/62.0.3202.84 Mobile Safari/537.36 hellowap2app Html5Plus/1.0 (Immersed/24.0)
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('Html5Plus');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    return false;
}





//当前项目废弃函数


//单个订单通过ID检索该订单的归属以及佣金数据的归属
function Checkorderprofit($id){
    $result = array('statuscode' => 200, 'message' => '');
    if($id<=0){
        $result['statuscode'] = 300;
        $result['message'] = '参数错误，无法检索订单信息！';
        return $result;
    }

    $depth_one=M('config')->where(array('code'=>'depth_one'))->limit(1)->getField('value');
    $depth_two=M('config')->where(array('code'=>'depth_two'))->limit(1)->getField('value');
    $depth_thr=M('config')->where(array('code'=>'depth_thr'))->limit(1)->getField('value');
    $arrprofit=array($depth_one,$depth_two,$depth_thr);

    $where=array();
    $where['id']=$id;
    $where['status']=array('in','0,1,2,3');
    $where['profit_status']=1;
    $orderdata=M('order')->field('id, orderid, buyeraccount, amount, profitout_dist, profit_status, saletime, uid, status')->where($where)->order('saletime asc')->limit(1)->find();
    if(empty($orderdata)){
        $result['statuscode'] = 300;
        $result['message'] = '无法检索到订单信息或者订单佣金已经被认领！';
        return $result;
    }
    $nwhere=array();
    if($orderdata['uid']==0){
        $nwhere['aliexpressaccount']=$orderdata['buyeraccount'];
        $chkuser=M('user')->fetchSql(false)->field('uid')->where($nwhere)->limit(1)->find();
        if($chkuser){
            M('order')->where('id='.$id)->setField('uid',$chkuser['uid']);
            $orderdata['uid']=$chkuser['uid'];
        }else{
            $result['statuscode'] = 300;
            $result['message'] = '该订单无法检索到对应的平台会员信息，无法进行佣金归属判断，请点击相应的按钮进行订单信息的更新！';
            return $result;
        }
    }

    $orderinfo='';
    //获取订单概要信息
    $orderitem=M('order_item')->field('goodsname, storesku')->where('mainid='.$id)->order('id desc')->select();
    foreach ($orderitem as $kk=>$vv) {
        $orderinfo==''?$orderinfo=$vv['goodsname'].'['.$vv['storesku'].']':$orderinfo.=','.$vv['goodsname'].'['.$vv['storesku'].']';
    }

    if($orderdata['uid']>0){
        //写入用户订单表
        $data=array();
        $data['uid']=$orderdata['uid'];
        $data['oid']=$id;
        $data['orderid']=$orderdata['orderid'];
        $data['orderinfo']=$orderinfo;
        $data['aliexpressaccount']=$orderdata['buyeraccount'];
        $data['amount'] = $orderdata['amount'];
        if($orderdata['saletime']<=0){
            $saletime=0;
        }else{
            $saletime=$orderdata['saletime'];
        }
        $data['updatetime'] = $saletime;
        $data['status'] = $orderdata['status'];
        $savemsg = M('user_order')->add($data);
    }

    //获取订单用于分销的总额
    $profitout_dist=$orderdata['profitout_dist'];

    //检查当前订单用户的推荐path
    $invitepath=M('user_dist')->where('uid='.$orderdata['uid'])->limit(1)->getField('path');
    $invitepath=explode(',',$invitepath);
    $arrinvitepath = array_reverse($invitepath);

    //根据path返用
    foreach ($arrinvitepath as $kk=>$vv) {
        $uid=$vv;
        $profit = round(bcmul($profitout_dist,$arrprofit[$kk],4),2);
        $depth = $kk+1;
        $status=1; //代表该笔订单计入待结佣金

        //从资金池扣除该订单佣金
        $transactionid = fundpooloption('订单佣金支付',-1,$profit,'订单“'.$orderdata['orderid'].'”佣金支出$'.$profit.'，支付给UID为'.$uid.'的会员。',0);

        //写资产记录表
        UserAssetOptionNotLogin($uid,'订单佣金收入','profiting',1,$profit,'推荐的用户UID为'.$orderdata['uid'].'的订单佣金，订单金额为：$'.$orderdata['amount'].'。');

        //写收益日志表
        $data=array();
        $data['uid'] = $uid;
        $data['oid'] = $orderdata['id'];
        $data['orderid'] = $orderdata['orderid'];
        $data['orderuid'] = $orderdata['uid'];
        $data['orderinfo'] = $orderinfo;
        $data['amount'] = $orderdata['amount'];
        $data['profit'] = $profit;
        $data['depth'] = $depth;
        $data['transactionid'] = $transactionid;
        $data['updatetime'] = time();
        $data['status'] = $status;
        $savemsg = M('user_profitlog')->add($data);
    }

    M('order')->where('id='.$id)->setField('profit_status',2);

    $result['statuscode'] = 200;
    $result['message'] = '订单佣金归属判断成功，点击确定后请查看最新信息！';
    return $result;
}

//批量判断未认领佣金订单的佣金数据归属
function Bathcheckorderprofit(){
    $result = array('statuscode' => 200, 'message' => '');

    $depth_one=M('config')->where(array('code'=>'depth_one'))->limit(1)->getField('value');
    $depth_two=M('config')->where(array('code'=>'depth_two'))->limit(1)->getField('value');
    $depth_thr=M('config')->where(array('code'=>'depth_thr'))->limit(1)->getField('value');
    $arrprofit=array($depth_one,$depth_two,$depth_thr);

    $where=array();
    $where['status']=array('in','0,1,2,3');
    $where['profit_status']=1;
    $orderlist=M('order')->field('id, orderid, buyeraccount, amount, profitout_dist, profit_status, saletime, uid, status')->where($where)->order('saletime asc')->select();
    if(empty($orderlist)){
        $result['statuscode'] = 300;
        $result['message'] = '无法检索到符合要求的订单信息！';
        return $result;
    }
    $syncount=0;
    $profittotal=0;
    foreach ($orderlist as $key=>$vo) {
        $orderinfo='';
        //获取订单概要信息
        $orderitem=M('order_item')->field('goodsname, storesku')->where('mainid='.$vo['id'])->order('id desc')->select();
        foreach ($orderitem as $kk=>$vv) {
            $orderinfo==''?$orderinfo=$vv['goodsname'].'['.$vv['storesku'].']':$orderinfo.=','.$vv['goodsname'].'['.$vv['storesku'].']';
        }

        $nwhere=array();
        if($vo['uid']==0){
            $nwhere['aliexpressaccount']=$vo['buyeraccount'];
            $chkuser=M('user')->fetchSql(false)->field('uid')->where($nwhere)->limit(1)->find();
            if($chkuser){
                M('order')->where('id='.$vo['id'])->setField('uid',$chkuser['uid']);
                $vo['uid']=$chkuser['uid'];
            }
        }

        if($vo['uid']>0){
            //写入用户订单表
            $data=array();
            $data['uid']=$vo['uid'];
            $data['oid']=$vo['id'];
            $data['orderid']=$vo['orderid'];
            $data['orderinfo']=$orderinfo;
            $data['aliexpressaccount']=$vo['buyeraccount'];
            $data['amount'] = $vo['amount'];
            if($vo['saletime']<=0){
                $saletime=0;
            }else{
                $saletime=$vo['saletime'];
            }
            $data['updatetime'] = $saletime;
            $data['status'] = $vo['status'];
            $savemsg = M('user_order')->add($data);

            //获取订单用于分销的总额
            $profitout_dist=$vo['profitout_dist'];

            //检查当前订单用户的推荐path
            $invitepath=M('user_dist')->where('uid='.$vo['uid'])->limit(1)->getField('path');
            $invitepath=explode(',',$invitepath);
            $arrinvitepath = array_reverse($invitepath);

            //根据path返用
            foreach ($arrinvitepath as $kk=>$vv) {
                $uid=$vv;
                $profit = round(bcmul($profitout_dist,$arrprofit[$kk],4),2);
                $depth = $kk+1;
                $status=1; //代表该笔订单计入待结佣金

                //从资金池扣除该订单佣金
                $transactionid = fundpooloption('订单佣金支付',-1,$profit,'订单“'.$vo['orderid'].'”佣金支出$'.$profit.'，支付给UID为'.$uid.'的会员。',0);

                $profittotal+=$profit;

                //写资产记录表
                UserAssetOptionNotLogin($uid,'订单佣金收入','profiting',1,$profit,'推荐的用户UID为'.$vo['uid'].'的订单佣金，订单金额为：$'.$vo['amount'].'。');

                //写收益日志表
                $data=array();
                $data['uid'] = $uid;
                $data['oid'] = $vo['id'];
                $data['orderid'] = $vo['orderid'];
                $data['orderuid'] = $vo['uid'];
                $data['orderinfo'] = $orderinfo;
                $data['amount'] = $vo['amount'];
                $data['profit'] = $profit;
                $data['depth'] = $depth;
                $data['transactionid'] = $transactionid;
                $data['updatetime'] = time();
                $data['status'] = $status;
                $savemsg = M('user_profitlog')->add($data);
            }

            M('order')->where('id='.$vo['id'])->setField('profit_status',2);

            $syncount++;
        }
    }
    $ordercount=count($orderlist);

    $profittotal=round($profittotal,2);

    $result['statuscode'] = 200;
    $result['message'] = '未认领佣金订单批量处理完成，本次处理订单共计：'.$ordercount.'条，更新佣金状态共计：'.$syncount.'条，共计发放佣金：$'.$profittotal.'。';
    $result['ordercount'] = $ordercount;
    $result['syncount'] = $syncount;
    $result['profittotal'] = $profittotal;
    return $result;
}

//单个订单更新状态及佣金结算
function Checkorderstatus($id){
    $result = array('statuscode' => 200, 'message' => '');
    if($id<=0){
        $result['statuscode'] = 300;
        $result['message'] = '请勿非法操作！';
        return $result;
    }else{
        //读取订单基本信息
        $orderdata=M('order')->field('id, accountcode, orderid, buyeraccount, buyername, buyercountry, buyercity, amount, currency, profit_total, profitout_account, profitout_dist, profit_status, saletime, paytime, delivertime, overtime, uid, status')->where('id='.$id)->limit(1)->find();
        if(empty($orderdata)){
            $result['statuscode'] = 300;
            $result['message'] = '检索不到当前订单的信息，请刷新重试！';
            return $result;
        }else{
            if($orderdata['status']==-1){
                $result['statuscode'] = 300;
                $result['message'] = '该订单状态为已取消订单，无法再进行状态的更新！';
                return $result;
            }
        }
    }
    if($orderdata['status']==4){
        if($orderdata['uid']>0){
//如果已经完成，则判断是否超过15天，进行返利的计算
            if($orderdata['overtime']<=0){
                $result['statuscode'] = 300;
                $result['message'] = '订单状态为已完成，但是订单完成时间出现未知错误，无法再进行状态的更新！';
                return $result;
            }else{
                if(time()-$orderdata['overtime']>1296000){
                    //关联设置
                    //佣金状态设置为已领取
                    $where=array();
                    $where['id']=$id;
                    M('order')->where($where)->setField('profit_status',3);

                    $where=array();
                    $where['oid']=$id;
                    $loglist = M('user_profitlog')->field('id, uid, oid, orderid, orderuid, orderinfo, amount, profit, depth, transactionid, updatetime, status')->where($where)->select();
                    foreach ($loglist as $kk=>$vv) {
                        //设置佣金收益表状态为已生效
                        M('user_profitlog')->where('oid='.$vv['oid'])->setField('status',2);
                        $uid=$vv['uid'];
                        $profit=$vv['profit'];
                        //更新佣金状态
                        //写资产记录表
                        if($orderdata['uid']==$uid){
                            UserAssetOptionNotLogin($uid,'待结佣金转已结佣金','profiting',-1,$profit,'您的订单'.$vv['orderid'].'已经完成，待结佣金转已结佣金。');
                            UserAssetOptionNotLogin($uid,'订单佣金结算','profitover',1,$profit,'您的订单'.$vv['orderid'].'已经完成，结算佣金。');
                        }else{
                            UserAssetOptionNotLogin($uid,'待结佣金转已结佣金','profiting',-1,$profit,'您推荐的用户的订单'.$vv['orderid'].'已经完成，待结佣金转已结佣金。');
                            UserAssetOptionNotLogin($uid,'订单佣金结算','profitover',1,$profit,'您推荐的用户的的订单'.$vv['orderid'].'已经完成，结算佣金。');
                        }
                    }
                    $result['statuscode'] = 200;
                    $result['message'] = '订单已完成且超过15天，按照系统规则佣金已经结算！';
                    return $result;
                }else{
                    $result['statuscode'] = 300;
                    $result['message'] = '订单状态为已完成，但尚未超过15天，无法对佣金进行更新处理。';
                    return $result;
                }
            }
        }else{
            $result['statuscode'] = 300;
            $result['message'] = '当前订单为检索到对应的平台会员信息，因此佣金结算数据无需更新！';
            return $result;
        }
    }else{
        $currentstatus=$orderdata['status'];
        $saleDateFrom = '2018-01-01 00:00:00';
        $saleDateTo = date('Y-m-d H:i:s',time());
        $payDateFrom = '2018-01-01 00:00:00';
        $payDateTo = date('Y-m-d H:i:s',time());
        $accountCode = $orderdata['accountcode'];
        $orderId = $orderdata['orderid'];

        $syndata=M('order_synconfig')->field('token, merchantid')->order('id asc')->limit(1)->find();
        $token = $syndata['token'];
        $merchantId = $syndata['merchantid'];
        $orderStatus='';
        $platformCode='';

        $pdata = array(
            'token' => $token,
            'data' => array(
                'payDateFrom'=>$payDateFrom,
                'saleDateFrom'=>$saleDateFrom,
                'merchantId'=>$merchantId,
                'payDateTo'=>$payDateTo,
                'orderStatus'=>$orderStatus,
                'orderId'=>$orderId,
                'saleDateTo'=>$saleDateTo,
                'platformCode'=>$platformCode,
                'accountCode'=>$accountCode,
            )
        );
        $pdata=json_encode($pdata);
        $url = 'https://openapi.tongtool.com/process/resume/openapi/tongtool/ordersQuery';
        $postdata = array(
            'q' => $pdata
        );
        $orderquery = https_post($url,$postdata);
        $orderquery=json_decode($orderquery,true);

        if($orderquery['ack']=='Success'){
            $ordererp=$orderquery['data']['array'][0];


            if($ordererp['saleTime']==''){
                $saletime=0;
            }else{
                $saletime=strtotime($ordererp['saleTime']);
            }
            if($ordererp['paidTime']==''){
                $paytime=0;
            }else{
                $paytime=strtotime($ordererp['paidTime']);
            }
            if($ordererp['delivertime']==''){
                $delivertime=0;
            }else{
                $delivertime=strtotime($ordererp['delivertime']);
            }
            if($ordererp['overtime']==''){
                $overtime=0;
            }else{
                $overtime=strtotime($ordererp['overtime']);
            }

            switch ($ordererp['orderStatus']){
                case 'payedErr':
                    $status=-1;
                    $result['message'] = '订单数据同步更新成功，当前最新状态为：已取消！';
                    break;
                case 'waitPacking':
                    $result['message'] = '订单数据同步更新成功，当前最新状态为：已下单！';
                    $status=1;
                    break;
                case 'outOfStock':
                case 'waitPrinting':
                    $result['message'] = '订单数据同步更新成功，当前最新状态为：已支付！';
                    $status=2;
                    break;
                case 'waitingDespatching':
                    $result['message'] = '订单数据同步更新成功，当前最新状态为：已发货！';
                    $status=3;
                    break;
                case 'despatche':
                    $result['message'] = '订单数据同步更新成功，当前最新状态为：已完成！';
                    $status=4;
                    break;
            }
            if($currentstatus==$status){
                $result['statuscode'] = 300;
                $result['message'] = '当前订单状态没有发生变化，无需更新！';
                return $result;
            }

            $data=array();
            $data['saletime'] = $saletime;
            $data['paytime'] = $paytime;
            $data['delivertime'] = $delivertime;
            $data['overtime'] = $overtime;
            $data['status'] = $status;
            $savemsg = M('order')->where('id='.$id)->save($data);

            //更新用户订单数据的状态信息
            M('user_order')->where('oid='.$id)->setField('status',$status);

            $result['statuscode'] = 200;
            return $result;
        }else{
            $result['statuscode'] = 300;
            $result['message'] = '通途ERP订单获取失败！';
            return $result;
        }
    }
}

//批量更新订单状态及佣金结算
function Bathcheckorderstatus(){
    $result = array('statuscode' => 200, 'message' => '');

    $where['status']=array('egt',0);
    $where['profit_status']=array('neq',3);
    $list=M('order')->field('id, accountcode, orderid, buyeraccount, buyername, buyercountry, buyercity, amount, currency, profit_total, profitout_account, profitout_dist, profit_status, saletime, paytime, delivertime, overtime, uid, status')->where($where)->select();
    if(empty($list)){
        $result['statuscode'] = 300;
        $result['message'] = '检测不到符合要求的订单！';
        return $result;
    }
    $syncount=0;
    foreach ($list as $key=>$vo) {
        if($vo['status']==4){
            if($vo['uid']>0){
                //如果已经完成，则判断是否超过15天，进行返利的计算
                if($vo['overtime']>0 and time()-$vo['overtime']>1296000){
                    //关联设置
                    //佣金状态设置为已领取
                    $where=array();
                    $where['id']=$vo['id'];
                    M('order')->where($where)->setField('profit_status',3);

                    $where=array();
                    $where['oid']=$vo['id'];
                    $loglist = M('user_profitlog')->field('id, uid, oid, orderid, orderuid, orderinfo, amount, profit, depth, transactionid, updatetime, status')->where($where)->select();
                    foreach ($loglist as $kk=>$vv) {
                        //设置佣金收益表状态为已生效
                        M('user_profitlog')->where('oid='.$vv['oid'])->setField('status',2);
                        $uid=$vv['uid'];
                        $profit=$vv['profit'];
                        //更新佣金状态
                        //写资产记录表
                        if($vo['uid']==$uid){
                            UserAssetOptionNotLogin($uid,'待结佣金转已结佣金','profiting',-1,$profit,'您的订单'.$vv['orderid'].'已经完成，待结佣金转已结佣金。');
                            UserAssetOptionNotLogin($uid,'订单佣金结算','profitover',1,$profit,'您的订单'.$vv['orderid'].'已经完成，结算佣金。');
                        }else{
                            UserAssetOptionNotLogin($uid,'待结佣金转已结佣金','profiting',-1,$profit,'您推荐的用户的订单'.$vv['orderid'].'已经完成，待结佣金转已结佣金。');
                            UserAssetOptionNotLogin($uid,'订单佣金结算','profitover',1,$profit,'您推荐的用户的的订单'.$vv['orderid'].'已经完成，结算佣金。');
                        }
                    }
                    $syncount++;
                }
            }
        }else{
            $currentstatus=$vo['status'];
            $saleDateFrom = '2018-01-01 00:00:00';
            $saleDateTo = date('Y-m-d H:i:s',time());
            $payDateFrom = '2018-01-01 00:00:00';
            $payDateTo = date('Y-m-d H:i:s',time());
            $accountCode = $vo['accountcode'];
            $orderId = $vo['orderid'];

            $syndata=M('order_synconfig')->field('token, merchantid')->order('id asc')->limit(1)->find();
            $token = $syndata['token'];
            $merchantId = $syndata['merchantid'];
            $orderStatus='';
            $platformCode='';

            $pdata = array(
                'token' => $token,
                'data' => array(
                    'payDateFrom'=>$payDateFrom,
                    'saleDateFrom'=>$saleDateFrom,
                    'merchantId'=>$merchantId,
                    'payDateTo'=>$payDateTo,
                    'orderStatus'=>$orderStatus,
                    'orderId'=>$orderId,
                    'saleDateTo'=>$saleDateTo,
                    'platformCode'=>$platformCode,
                    'accountCode'=>$accountCode,
                )
            );
            $pdata=json_encode($pdata);
            $url = 'https://openapi.tongtool.com/process/resume/openapi/tongtool/ordersQuery';
            $postdata = array(
                'q' => $pdata
            );
            $orderquery = https_post($url,$postdata);
            $orderquery=json_decode($orderquery,true);

            if($orderquery['ack']=='Success'){
                $ordererp=$orderquery['data']['array'][0];
                if($ordererp['saleTime']==''){
                    $saletime=0;
                }else{
                    $saletime=strtotime($ordererp['saleTime']);
                }
                if($ordererp['paidTime']==''){
                    $paytime=0;
                }else{
                    $paytime=strtotime($ordererp['paidTime']);
                }
                if($ordererp['delivertime']==''){
                    $delivertime=0;
                }else{
                    $delivertime=strtotime($ordererp['delivertime']);
                }
                if($ordererp['overtime']==''){
                    $overtime=0;
                }else{
                    $overtime=strtotime($ordererp['overtime']);
                }

                switch ($ordererp['orderStatus']){
                    case 'payedErr':
                        $status=-1;
                        $result['message'] = '订单数据同步更新成功，当前最新状态为：已取消！';
                        break;
                    case 'waitPacking':
                        $result['message'] = '订单数据同步更新成功，当前最新状态为：已下单！';
                        $status=1;
                        break;
                    case 'outOfStock':
                    case 'waitPrinting':
                        $result['message'] = '订单数据同步更新成功，当前最新状态为：已支付！';
                        $status=2;
                        break;
                    case 'waitingDespatching':
                        $result['message'] = '订单数据同步更新成功，当前最新状态为：已发货！';
                        $status=3;
                        break;
                    case 'despatche':
                        $result['message'] = '订单数据同步更新成功，当前最新状态为：已完成！';
                        $status=4;
                        break;
                }
                if($currentstatus!=$status){
                    $data=array();
                    $data['saletime'] = $saletime;
                    $data['paytime'] = $paytime;
                    $data['delivertime'] = $delivertime;
                    $data['overtime'] = $overtime;
                    $data['status'] = $status;
                    $savemsg = M('order')->where('id='.$vo['id'])->save($data);

                    //更新用户订单数据的状态信息
                    M('user_order')->where('oid='.$vo['id'])->setField('status',$status);

                    $syncount++;
                }
            }
        }
    }

    $ordercount=count($list);

    $result['statuscode'] = 200;
    $result['message'] = '订单状态更新批量处理完成，本次处理订单共计：'.$ordercount.'条，更新订单状态共计：'.$syncount.'条。';
    $result['ordercount'] = $ordercount;
    $result['syncount'] = $syncount;
    return $result;
}



function CreateQr($qrcode_logo,$qrcode_path,$content,$qrname,$qrfix,$qrsize=20,$marginsize=1,$corrlevel='H'){
    /*
    $domain = 'http://'.$_SERVER['HTTP_HOST'];
        $script = U('/Qradv');
        $param = '?supplier_id=1';
        $url = $domain.$script.$param;

        $content = $url;

        $qrcode_logo='./qrlogo.png';

        $qrcode_path='./uploads/store/qrcode/';
        //$qrcode_path='./';
        $qrname = 'qrcode_1';
        $qrfix = 'png';
     */
    Vendor('phpqrcode.phpqrcode');
    $object = new \QRcode();
    $file_tmp_name='';
    $errors=array();

    if(!is_dir($qrcode_path)){
        mkdir($qrcode_path, 0777, true);
    }
    $qrcode_path=$qrcode_path.$qrname.'.'.$qrfix;

    $helper = new \My\Helper();
    if($helper->getOS()=='Linux'){
        $mv = move_uploaded_file($file_tmp_name, $qrcode_path);
    }else{
        //解决windows下中文文件名乱码的问题
        $save_path = $helper->safeEncoding($qrcode_path,'GB2312');
        if(!$save_path){
            $errors[]='上传失败，请重试！';
        }
        $mv = move_uploaded_file($file_tmp_name, $qrcode_path);
    }

    if(empty($errors)){
        $errorCorrectionLevel = $corrlevel;//容错级别
        $matrixPointSize = $qrsize;//生成图片大小
        $matrixMarginSize = $marginsize;//边距大小
        //生成二维码图片
        $object::png($content,$qrcode_path, $errorCorrectionLevel, $matrixPointSize, $matrixMarginSize);
        $QR = $qrcode_path;
        $logo = $qrcode_logo;

        if (file_exists($logo)) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            if (imageistruecolor($QR)) imagetruecolortopalette($QR, false, 65535);
            if (imageistruecolor($logo)) imagetruecolortopalette($logo, false, 65535);
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 4;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;

            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_width, $logo_height, $logo_width, $logo_height);

            //输出图片
            //header("Content-type: image/png");
            imagepng($QR,$qrcode_path);
            imagedestroy($QR);
        }
        $qrcode=trim($qrcode_path,'.');
    }else{
        $qrcode='';
    }
    return $qrcode;
}


function CreateWeQr(){


    /*
    $Wechat =new \My\Wechatauth();
    $access_token = S('access_token');
    if(empty($access_token)){
        $access_token_data = $Wechat->get_access_token();
        $access_token = $access_token_data['access_token'];
        S('access_token',$access_token);
    }

    $openid = session('user.openid');

    $user_info = $Wechat->get_user_info($access_token,$openid);
    if($user_info['errcode']>0){
        S('access_token',null);
        $access_token = S('access_token');
        if(empty($access_token)){
            $access_token_data = $Wechat->get_access_token();
            $access_token = $access_token_data['access_token'];
            S('access_token',$access_token);
        }
        $user_info = $Wechat->get_user_info($access_token,$openid);
    }
    if($user_info['errcode']>0){
        $result['statuscode'] = 302;
        $result['message'] = '数据读取失败，请关闭后重新进入，给您带来的不便，敬请谅解！';
        $result['comeurl']='';
        $this->ajaxReturn($result);
    }
    $user_info['subscribe_time'] = date('Y-m-d H:i:s',$user_info['subscribe_time']);
    if($user_info['subscribe']==0){
        $result['statuscode'] = 303;
        $result['message'] = '您还没有关注公众号';
        $result['comeurl']='';
        $this->ajaxReturn($result);
    }
    $qrdata = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "fromsupplier_'.$sid.'"}}}';
    $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;

    $wxresult = https_post($url,$qrdata);
    $ticketinfo = json_decode($wxresult,true);
    $ticket = $ticketinfo['ticket'];
    $qrUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
    $config=array(
        'rootPath' => './uploads/',
        'savePath' => 'store/wxqrcode/',
        'maxSize'=>1024000,
        'allowFiles'=>array('.jpg','.jpeg','.png','.gif'),
    );

    $imageInfo = downloadImageFromWeiXin($qrUrl,$config,$sid);
    $wxqrcode = $imageInfo["url"];
    $wxqrcode=trim($wxqrcode,'.');
    M('supplier')->where('supplier_id='.$sid)->setField('wxqrcode',$wxqrcode);
     */


}



//写入会员邀请表
function Userinviteoption($uid,$invite_uid){
    if($uid<=0 or $invite_uid<=0){
        return false;
    }
    $data=M('user_inviteconfig')->field('profit_join, profit_order')->where('id=1')->limit(1)->find();
    if(empty($data)){
        return false;
    }else{
        $profit_join = $data['profit_join'];
        $profit_order = $data['profit_order'];
    }
    $data=array();
    $data['uid'] = $uid;
    $data['invite_uid'] = $invite_uid;
    $data['regtime'] = time();
    $data['ordertime'] = 0;
    $data['profit_join'] = $profit_join;
    $data['profit_order'] = $profit_order;
    $data['status'] = 0;
    $chk = M('user_invitelog')->add($data);
    return true;
}

//资金池操作
function fundpooloption($title,$mode,$val,$memo='',$auid=0){
    if($title=='' or $mode=='' or $val<=0){
        return false;
    }
    $transactionid = date('YmdHis',time()).rand_string(8,1);
    $balance=M('fundpool')->order('id desc')->limit(1)->getField('balance');
    if(empty($balance)){
        $balance=0;
    }
    if($mode==-1){
        $balance=round(bcsub($balance,$val,5),2);
    }else{
        $balance=round(bcadd($balance,$val,5),2);
    }
    $data['transactionid'] = $transactionid;
    $data['title'] = $title;
    $data['mode'] = $mode;
    $data['val'] = $val;
    $data['balance'] = $balance;
    $data['memo'] = $memo;
    $data['auid'] = $auid;
    $data['updatetime'] = time();
    $chk = M('fundpool')->add($data);
    return $transactionid;
}
