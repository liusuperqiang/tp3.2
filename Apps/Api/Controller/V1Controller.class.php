<?php
namespace Api\Controller;
class V1Controller extends ApiBaseController {
    public function _initialize(){
        parent:: _initialize();

    }

    //获取首页信息
    public function Userinfo(){
        $result = array('statuscode' => 200, 'message' => '');

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');
        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        /*$hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }*/

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        //读取基本信息
        $where['ecs_users.user_id'] = $user_id;
        $userdata=M('users')->field('ecs_users.user_id, ecs_users.yaoqing_code, ecs_users.parent_id, ecs_users.mobile_phone, ecs_users.user_name, ecs_users.user_rank, ecs_user_rank.rank_name, ecs_users.user_money, ecs_users.headimg, ecs_users.reg_time')
            ->join('LEFT JOIN ecs_user_rank ON ecs_user_rank.rank_id = ecs_users.user_rank')
            ->where($where)->limit(1)->find();
        if(empty($userdata)){
            $result['statuscode'] = 300;
            $result['message'] = '用户数据读取失败';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        $userdata['user_money'] = price_format($userdata['user_money'],0);
        if($userdata['headimg']!=''){
            $userdata['headimg'] = $rooturl.$userdata['headimg'];
        }else{
            $userdata['headimg'] = '';
        }

        $user_rank = $userdata['user_rank'];
        $user_balance = $userdata['user_money'];

        //读取资产信息
        $where=array();
        $where['ecs_fan_goods.user_id'] = $user_id;
        $where['ecs_fan_goods.is_split'] = 0;
        $where['ecs_fan_goods.is_pay'] = 1;
        $where['ecs_order_info.pay_status'] = 2;
        $where['ecs_order_info.order_status'] = array('in','1,5,6');
        $split_money_pending=M('fan_goods')->fetchSql(false)
            ->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')
            ->where($where)->sum('ecs_fan_goods.split_money');
        $split_money_pending=intval($split_money_pending);
        $split_money_pending = price_format($split_money_pending,0);
        // 读取菜单
        $menu_list_config = array(
            array(
                'key' => 'myorder',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'enshrine',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'address',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'withdraw',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'teamorder',
                'min_rank' => 2,
                'rank' => -1,
            ),
            array(
                'key' => 'teamuser',
                'min_rank' => 2,
                'rank' => -1,
            ),
            array(
                'key' => 'inviteshare',
                'min_rank' => 2,
                'rank' => -1,
            ),
            array(
                'key' => 'viplistrank',
                'min_rank' => -1,
                'rank' => 2,
            ),
            array(
                'key' => 'teamvip',
                'min_rank' => 3,
                'rank' => -1,
            ),
            array(
                'key' => 'plussta',
                'min_rank' => 3,
                'rank' => -1,
            ),
            array(
                'key' => 'pluslistrank',
                'min_rank' => 3,
                'rank' => -1,
            ),
            array(
                'key' => 'leaveword',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'viphelp',
                'min_rank' => 2,
                'rank' => -1,
            ),
            array(
                'key' => 'help',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'coupon',
                'min_rank' => -1,
                'rank' => -1,
            ),
            array(
                'key' => 'privatemsg',
                'min_rank' => -1,
                'rank' => -1,
            )
        );
        $menu_list=array();
        foreach ($menu_list_config as $item) {
            if(($item['min_rank']==-1 or $item['min_rank']<=$user_rank) and ($item['rank']==-1 or $item['rank']==$user_rank)){
                $menu_list[]=$item['key'];
            }
        }
        //帮助中心菜单
        $helpcenter=array(
            'show'=>1,
            'help'=>array(
                'show'=>1,
                'name' => 'Помощь',
                'type'=>'innerurl',
                'url' => 'widget://html/home/article.html',
                'arg_name' => 'Помощь',
                'arg_id' => '32',
                'height'=>60,

//            'show'=>1,
//            'name' => 'помогать',
//            'type'=>'url',
//            'url' => 'https://api.whatsapp.com/send?phone=77023232791',
//            'arg_name' => 'Справочный(kg)центр',
//            'arg_id' => '10149',
//            'height'=>60,

                // 'show'=>1,
                // 'name' => 'Помощь',
                // 'type'=>'diy',
                // 'url' => '',
                // 'arg_name' => 'Помощь',
                // 'arg_id' => '10146',
                // 'height'=>60,

            ),
            'whatapp'=>array(
                'show'=>1,
                'name' => 'whatsApp',
                'url' => 'https://api.whatsapp.com/send?phone=77008360661',
                'title' => 'whatsApp',
                'height'=>60
            ),
            'ins'=>array(
                'show'=>0,
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/src_shop_official',
                'title' => 'Instagram',
                'height'=>60
            ),
            'call'=>array(
                'show'=>1,
                'name' => 'Позвонить',
                'num' => '87008360661',
                'height'=>60
            )
        );

        //邀请链接
        $user_id_key=base64_encode('user'.$user_id);
        $myregurl = $approoturl.'h5_reg.php?u='.$user_id_key;
        $myregurl = $rooturl.'miniapp/reg.html?u='.$user_id_key;

        //读取未读消息数量
        $where=array();
        $where['status']=1;
        $where['msgtype']=1;
        $where['user_id']=$user_id;
        $where['uidreadlist']=array('notlike','%,'.$user_id.',%');
        $pricount = M('new_user_message')->where($where)->count('id');
        $pricount = intval($pricount);

        $where=array();
        $where['status']=1;
        $where['msgtype']=2;
        $where['uidreadlist']=array('notlike','%,'.$user_id.',%');
        $pubcount = M('new_user_message')->fetchSql(false)->where($where)->count('id');
        $pubcount=intval($pubcount);
        $unreadcount=$pricount+$pubcount;
        $unreadcount = intval($unreadcount);

        $where=array();
        $where['userid']=$user_id;
        $where['status']=1;
        $stoptime=date('Y-m-d H:i:s',time());
        $stoptime=local_strtotime($stoptime,0);
        $where['stoptime'] = array('egt',$stoptime);
        $coupon_enable_count = M('new_coupon_logs')->fetchSql(false)->where($where)->count('id');
        $coupon_enable_count=intval($coupon_enable_count);

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['userdata'] = $userdata;
        $result['user_balance'] = $user_balance;
        $result['user_split_money_pending'] = $split_money_pending;
        $result['menu_list'] = $menu_list;
        $result['helpcenter'] = $helpcenter;
        $result['myregurl'] = $myregurl;
        $result['unreadcount'] = $unreadcount;
        $result['coupon_enable_count'] = $coupon_enable_count;
        $result['sign'] = $sign;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取用户未读信息
    public function Getuserunreadcount(){
        $result = array('statuscode' => 200, 'message' => '');

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');
        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        //读取未读消息数量
        $where=array();
        $where['status']=1;
        $where['msgtype']=1;
        $where['user_id']=$user_id;
        $where['uidreadlist']=array('notlike','%,'.$user_id.',%');
        $pricount = M('new_user_message')->where($where)->count('id');
        $pricount = intval($pricount);

        $where=array();
        $where['status']=1;
        $where['msgtype']=2;
        $where['uidreadlist']=array('notlike','%,'.$user_id.',%');
        $pubcount = M('new_user_message')->fetchSql(false)->where($where)->count('id');
        $pubcount = intval($pubcount);
        $unreadcount=$pricount+$pubcount;
        $unreadcount = intval($unreadcount);

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['unreadcount'] = $unreadcount;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取一级用户列表
    public function Getuser(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $datatype = I('param.datatype/d',0,'strip_tags,htmlspecialchars');//0全部 1 当天 2本周   3本月  4自定义
        $monthkey = I('param.monthkey/s','','strip_tags,htmlspecialchars');//月份   2020-08
        $startdate = I('param.startdate/s','','strip_tags,htmlspecialchars'); //自定义日期
        $enddate = I('param.enddate/s','','strip_tags,htmlspecialchars');//自定义日期
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }


//        $user_id=10098;
        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

        // $user_id   $datatype   $monthkey   $startdate   $enddate   $keyword
        //加载列表
        // ecs_users user_id, yaoqing_code, parent_id, mobile_phone, user_name, user_rank, user_money, headimg, reg_time
        $db = M('users');
        $where['parent_id']=$user_id;
        $where['user_rank']=0;
        if($keyword!=''){
            $where['mobile_phone|user_name|yaoqing_code'] =array('like','%'.$keyword.'%');
        }
        $list = $db->fetchSql(false)
            ->field('user_id, yaoqing_code, parent_id, mobile_phone, user_name, user_rank, user_money, headimg, reg_time')
            ->where($where)
            ->order('reg_time desc')
            ->limit($skip,$maxperpage)
            ->select();
        foreach($list as $key=>$vo){
            if($vo['headimg']!=''){
                $list[$key]['headimg'] = $rooturl.$vo['headimg'];
            }else{
                $list[$key]['headimg'] = '';
            }
            $list[$key]['user_money'] = price_format($vo['user_money'],0);
            $orderdata=Get_user_orderdata($vo['user_id'],$datatype,$monthkey,$startdate,$enddate);
            $list[$key]['buy_amount'] = price_format($orderdata['orderamount'],0);
            $list[$key]['buy_count'] = $orderdata['ordercount'];

            if($user_rank>2){
                $mobile = $vo['mobile_phone'];
            }else{
                if(strlen($vo['mobile_phone'])==11){
                    $pattern = "/(\d{3})\d{4}(\d{4})/";
                    $replacement = "\$1****\$2";
                    $mobile=preg_replace($pattern, $replacement, $vo['mobile_phone']);
                }else{
                    $pattern = "/(\d{3})\d{3}(\d{4})/";
                    $replacement = "\$1***\$2";
                    $mobile=preg_replace($pattern, $replacement, $vo['mobile_phone']);
                }
            }
            $list[$key]['mobile_phone'] = '+7 '.$mobile;

            if($user_rank>2){
                //判断是否再申请VIP   ecs_user_appvip id, plus_user_id, user_id, createtime, status
                $app_status=M('user_appvip')->fetchSql(false)->where('plus_user_id='.$user_id.' and user_id='.$vo['user_id'])->limit(1)->getField('status');
                if($app_status==='0' or $app_status>0){
                    $list[$key]['app_status'] = $app_status;
                }else{
                    $list[$key]['app_status'] = -1;
                }
            }else{
                $list[$key]['app_status'] = -1;
            }
        }
//        print_r($list);
//        exit;
        $arruserids=array();
        $pagecount = count($list);
        $alllist = $db->fetchSql(false)->field('user_id')->where($where)->select();
        foreach ($alllist as $key=>$vo) {
            $arruserids[]=$vo['user_id'];
        }
        $pagetotalcount=count($alllist);

        if(empty($arruserids)){
            $arruserids[]=-1;
        }

        //获取所有符合条件的用户的消费统计
        $userbuydata_total=Get_users_orderdata($arruserids,$datatype,$monthkey,$startdate,$enddate);
        $userbuy_total=price_format($userbuydata_total['orderamount'],0);

        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;

        $result['usercount_total'] = $pagetotalcount;
        $result['userbuy_total'] = $userbuy_total;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取佣金列表
    public function Getincomelist(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $statustype = I('param.statustype/d',0,'strip_tags,htmlspecialchars');//0待结算   1已结算   2无效   -1全部
        $datetype = I('param.datetype/d',3,'strip_tags,htmlspecialchars');//0全部   1当天 2本周   3本月  -1自定义
        $monthkey = I('param.monthkey/s','','strip_tags,htmlspecialchars');//月份   2020-08
        $startdate = I('param.startdate/s','','strip_tags,htmlspecialchars'); //自定义日期
        $enddate = I('param.enddate/s','','strip_tags,htmlspecialchars');//自定义日期
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        /*$hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }*/

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

//         $user_id   $statustype   $datetype   $monthkey   $startdate   $enddate   $keyword

        //加载列表
        $db = M('fan_goods');
        $where['ecs_fan_goods.user_id']=$user_id;
        $where['ecs_fan_goods.is_pay'] = 1;
        $where['ecs_fan_goods.split_money']=array('gt',0);
        if($keyword!=''){
            $where['ecs_order_info.order_sn|ecs_order_info.consignee|ecs_order_info.mobile'] =array(array('like','%'.$keyword.'%'),array('like','%'.$keyword.'%'),array('like','%'.$keyword.'%'),'_multi'=>true);
        }
        switch ($statustype){
            case 0:
                $where['ecs_fan_goods.is_split']=0;
                $where['ecs_order_info.pay_status']=2;
                $where['ecs_order_info.order_status']=array('in','1,5,6');
                break;
            case 1:
                $where['ecs_fan_goods.is_split']=1;
                break;
            case 2:
                $where['ecs_fan_goods.is_split']=2;
                break;
            case -1:
                $where['ecs_order_info.pay_status']=2;
                $where['ecs_order_info.order_status']=array('in','1,5,6');
                break;
        }

        if($startdate!='' and $enddate!=''){
            $starttime=local_strtotime($startdate.' 00:00:00',0);
            $stoptime=local_strtotime($enddate.' 23:59:59',0);
            $where['ecs_order_info.add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
        }elseif($startdate!=''){
            $starttime=local_strtotime($startdate.' 00:00:00',0);
            $where['ecs_order_info.add_time'] = array('egt',$starttime);
        }elseif($enddate!=''){
            $stoptime=local_strtotime($enddate.' 23:59:59',0);
            $where['ecs_order_info.add_time'] = array('elt',$stoptime);
        }else{
            if($monthkey!=''){
                $startdate=$monthkey.'-01';
//            $enddate=date('Y-m-'.date('t',local_strtotime($startdate . ' 00:00:00',0)),local_strtotime($startdate . ' 00:00:00',0));
                $enddate=date('Y-m-t',local_strtotime($startdate . ' 00:00:00',0));
                $starttime=local_strtotime($startdate.' 00:00:00',0);
                $stoptime=local_strtotime($enddate.' 23:59:59',0);
                $where['ecs_order_info.add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
            }else{
                switch ($datetype){ //0全部   1当天 2本周   3本月  -1自定义
                    case 1:
                        $today = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                        $starttime=local_strtotime($today.' 00:00:00',0);
                        $stoptime=local_strtotime($today.' 23:59:59',0);
                        $where['ecs_order_info.add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                        break;
                    case 2:
                        $sdefaultDate = date('Y-m-d',local_strtotime(date('Y-m-d 00:00:00'),0));
                        $first=1;
                        $w = date('w', local_strtotime($sdefaultDate,0));
                        $week_start=date('Y-m-d 0:0:0', local_strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days',0));
                        $week_end=date('Y-m-d 23:59:59',local_strtotime("$week_start +6 days",0));
                        $starttime=local_strtotime($week_start,0);
                        $stoptime=local_strtotime($week_end,0);
                        $where['ecs_order_info.add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                        break;
                    case 3:
                        $starttime=local_strtotime(date('Y-m-01 00:00:00', local_strtotime(date("Y-m-d"),0)),0);
                        $stoptime=local_strtotime(date('Y-m-d 23:59:59', time()),0);
                        $where['ecs_order_info.add_time'] = array(array('egt',$starttime),array('elt',$stoptime));
                        break;
                }
            }
        }

        $list = $db->fetchSql(false)
            ->field('ecs_fan_goods.id, ecs_fan_goods.xd_user_id, ecs_fan_goods.user_id, ecs_fan_goods.order_id, ecs_fan_goods.goods_id, ecs_fan_goods.goods_name, ecs_fan_goods.goods_num, ecs_fan_goods.pay_money, ecs_fan_goods.split_money, ecs_fan_goods.creat_time, ecs_fan_goods.ok_time, ecs_fan_goods.is_pay, ecs_fan_goods.is_split, ecs_users.user_name as xd_user_name, ecs_users.mobile_phone as xd_mobile_phone, ecs_order_info.order_sn, ecs_order_info.froms, ecs_order_info.order_status, ecs_order_info.shipping_status, ecs_order_info.pay_status, ecs_order_info.goods_amount, ecs_order_info.shipping_fee, ecs_order_info.add_time as order_add_time, ecs_order_info.confirm_time as order_confirm_time, ecs_order_info.pay_time as order_pay_time, ecs_order_info.shipping_time as order_shipping_time, ecs_order_info.ok_time as order_ok_time')
            ->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')
            ->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')
            ->where($where)
            ->order('ecs_fan_goods.id desc')
            ->limit($skip,$maxperpage)
            ->select();
        $timeformat=C('timeformat');
        foreach($list as $key=>$vo){
            //判断剩余时间
            $left_day = 0;
            if($vo['is_split']==1){
                $status_code = 'Начисленные';
            }elseif ($vo['is_split']==2){
                $status_code = 'Недействительные';
            }else{
                if($vo['order_status']==5 and $vo['shipping_status']==2 and $vo['pay_status']==2 and $vo['is_split']==0){
                    $over_day=strtotime('+14 day',$vo['order_ok_time']);
                    $left_day=floor(($over_day-time())/86400);
                    if($left_day<=1){
                        $status_code = 'менее суток';
                        $left_day='';
                    }else{
                        $status_code = 'Осталось дней:';
                        $left_day = $left_day;
                    }
                }else{
                    $status_code = 'Товар в пути';
                }
            }

            $list[$key]['left_day'] = $left_day;
            $list[$key]['status_code'] = $status_code;


            $list[$key]['creat_time'] = date($timeformat,$vo['creat_time']);
            if($vo['ok_time']>0){
                $list[$key]['ok_time'] = date($timeformat,$vo['ok_time']);
            }
            $list[$key]['order_add_time'] = date($timeformat,$vo['order_add_time']);
            if($vo['order_confirm_time']>0){
                $list[$key]['order_confirm_time'] = date($timeformat,$vo['order_confirm_time']);
            }
            if($vo['order_shipping_time']>0){
                $list[$key]['order_shipping_time'] = date($timeformat,$vo['order_shipping_time']);
            }
            if($vo['order_pay_time']>0){
                $list[$key]['order_pay_time'] = date($timeformat,$vo['order_pay_time']);
            }
            if($vo['order_ok_time']>0){
                $list[$key]['order_ok_time'] = date($timeformat,$vo['order_ok_time']);
            }

            $order_pay_goods = $vo['pay_money']*$vo['goods_num'];
            $list[$key]['order_pay_goods'] = price_format($order_pay_goods,0);
            $list[$key]['pay_money'] = price_format($vo['pay_money'],0);
            $list[$key]['split_money'] = price_format($vo['split_money'],0);



            if($vo['is_split']==2){
                if($vo['froms']=='h5'){
                    $invalid_res = 'Данные бонусы недействительны, из-за  отмены заказа пользователем.';
                }else{
                    if($vo['xd_user_id']==$vo['user_id']){
                        $invalid_res = 'Данные бонусы недействительны, из-за вашей отмены заказа.';
                    }else{
                        $invalid_res = 'Данные бонусы недействительны, по причине отмены заказа вашего подписчика.';
                    }
                }
            }elseif ($vo['is_split']==1){
                $invalid_res = 'Бонусы уже посчитаны, можете посмотреть в деталях счета.';
            }else{
                $invalid_res = 'Бонусы в процессе расчёта.';
            }
            $list[$key]['invalid_res'] = $invalid_res;
        }
//        print_r($list);
//        exit;

        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')
            ->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($where)->count('ecs_fan_goods.id');


        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $split_total=$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($where)->sum('ecs_fan_goods.split_money');
        $split_total_count = $pagetotalcount;

        $nwhere=$where;
        $nwhere['ecs_order_info.froms']='mobile';
        $nwhere['_string'] = 'ecs_fan_goods.xd_user_id!=ecs_fan_goods.user_id';
        $split_mobile =$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->sum('ecs_fan_goods.split_money');
        $split_mobile_count =$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->count('ecs_fan_goods.id');
        $nwhere=$where;
        $nwhere['ecs_order_info.froms']='h5';
        $split_h5=$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->sum('ecs_fan_goods.split_money');
        $split_h5_count=$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->count('ecs_fan_goods.id');

        $nwhere=$where;
        $nwhere['ecs_order_info.froms']='mobile';
        $nwhere['_string'] = 'ecs_fan_goods.xd_user_id=ecs_fan_goods.user_id';
        $split_self=$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->sum('ecs_fan_goods.split_money');
        $split_self_count=$db->fetchSql(false)->join('LEFT JOIN ecs_users ON ecs_users.user_id = ecs_fan_goods.xd_user_id')->join('LEFT JOIN ecs_order_info ON ecs_order_info.order_id = ecs_fan_goods.order_id')->where($nwhere)->count('ecs_fan_goods.id');

        $split_total=intval($split_total);
        $split_total = price_format($split_total,0);
        $split_mobile=intval($split_mobile);
        $split_mobile = price_format($split_mobile,0);
        $split_h5=intval($split_h5);
        $split_h5 = price_format($split_h5,0);
        $split_self=intval($split_self);
        $split_self = price_format($split_self,0);

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;

        $result['split_total'] = $split_total;
        $result['split_total_count'] = $split_total_count;
        $result['split_mobile'] = $split_mobile;
        $result['split_mobile_count'] = $split_mobile_count;
        $result['split_h5'] = $split_h5;
        $result['split_h5_count'] = $split_h5_count;
        $result['split_self'] = $split_self;
        $result['split_self_count'] = $split_self_count;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取一级VIP列表
    public function Getviplist(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $datetype = I('param.datetype/d',0,'strip_tags,htmlspecialchars');//0全部 1 当天 2本周   3本月  -1自定义
        $monthkey = I('param.monthkey/s','','strip_tags,htmlspecialchars');//月份   2020-08
        $startdate = I('param.startdate/s','','strip_tags,htmlspecialchars'); //自定义日期
        $enddate = I('param.enddate/s','','strip_tags,htmlspecialchars');//自定义日期
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }
        if($user_rank<=2){
            $result['statuscode'] = 300;
            $result['message'] = '您无权查看该信息';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        // $user_id   $datetype   $monthkey   $startdate   $enddate   $keyword

        //加载列表
        $db = M('users');
        $where['parent_id']=$user_id;
        $where['user_rank']=2;
        if($keyword!=''){
            $where['mobile_phone|user_name|yaoqing_code'] =array('like','%'.$keyword.'%');
        }
        $list = $db->fetchSql(false)
            ->field('user_id, yaoqing_code, parent_id, mobile_phone, user_name, user_rank, user_money, headimg, reg_time')
            ->where($where)
            ->order('reg_time desc')
            ->limit($skip,$maxperpage)
            ->select();
        foreach($list as $key=>$vo){
            if($vo['headimg']!=''){
                $list[$key]['headimg'] = $rooturl.$vo['headimg'];
            }else{
                $list[$key]['headimg'] = '';
            }
            $list[$key]['user_money'] = price_format($vo['user_money'],0);

            //获取团队USER
            $oneuserdata=Get_vip_userdata($vo['user_id']);
            $list[$key]['team_user_count'] = $oneuserdata['user_id_count'];
            $teamuser=$oneuserdata['user_ids'];
            $teamuser[]=$vo['user_id'];
            if(empty($teamuser)){
                $teamuser[]=-1;
            }

            $orderdata=Get_users_orderdata($teamuser,$datetype,$monthkey,$startdate,$enddate);
            $list[$key]['team_userbuy_amount'] = price_format($orderdata['orderamount'],0);
            $list[$key]['team_userbuy_count'] = $orderdata['ordercount'];

            if($user_rank>2){
                $mobile = $vo['mobile_phone'];
            }else{
                if(strlen($vo['mobile_phone'])==11){
                    $pattern = "/(\d{3})\d{4}(\d{4})/";
                    $replacement = "\$1****\$2";
                    $mobile=preg_replace($pattern, $replacement, $vo['mobile_phone']);
                }else{
                    $pattern = "/(\d{3})\d{3}(\d{4})/";
                    $replacement = "\$1***\$2";
                    $mobile=preg_replace($pattern, $replacement, $vo['mobile_phone']);
                }
            }
            $list[$key]['mobile_phone'] = '+7 '.$mobile;
        }

        $pagecount = count($list);

        $arruserids=array();
        $alllist = $db->fetchSql(false)->field('user_id')->where($where)->select();
        foreach ($alllist as $key=>$vo) {
            $oneuserdata=Get_vip_userdata($vo['user_id']);
            $arruserids=array_merge($arruserids,$oneuserdata['user_ids']);
            $arruserids[]=$vo['user_id'];
        }
        if(empty($arruserids)){
            $arruserids[]=-1;
        }

        $pagetotalcount=count($alllist);
        $vipcount_total = $pagetotalcount;

        //获取所有符合条件的用户的消费统计
        $vipteambuy_totaldata=Get_users_orderdata($arruserids,$datetype,$monthkey,$startdate,$enddate);
        $vipteambuy_total=price_format($vipteambuy_totaldata['orderamount'],0);

        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;

        $result['vipcount_total'] = $vipcount_total;
        $result['vipteambuy_total'] = $vipteambuy_total;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取用户订单
    public function Getuserorder(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $composite_status = I('param.composite_status/d',-1,'strip_tags,htmlspecialchars');//月份   2020-08
        $datetype = I('param.datetype/d',0,'strip_tags,htmlspecialchars');//0全部 1 当天 2本周   3本月  -1自定义
        $monthkey = I('param.monthkey/s','','strip_tags,htmlspecialchars');//月份   2020-08
        $startdate = I('param.startdate/s','','strip_tags,htmlspecialchars'); //自定义日期
        $enddate = I('param.enddate/s','','strip_tags,htmlspecialchars');//自定义日期
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');


        /*$hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }*/

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

        //加载列表
        $db = M('order_info');
        $where['user_id']=$user_id;
        if($keyword!=''){
            $where['order_sn|consignee|address|mobile'] =array('like','%'.$keyword.'%');
        }
        switch ($composite_status){
            case -1:
                break;
            case 100:
                $where['order_status']=0;
                $where['pay_status']=array('in','0,1');
                break;
            case 101:
                $where['order_status']=array('in','1,5,6');
                $where['pay_status']=array('in','1,2');
                $where['shipping_status']=array('in','0,1,3,5');
                break;
            case 105:
                $where['order_status']=array('in','1,5');
                $where['pay_status']=2;
                $where['shipping_status']=2;
                break;
            case 102:
                $where['order_status']=array('in','2,3,4');
                break;
        }
        $list = $db->fetchSql(false)
            ->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, mobile, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, (goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS order_total_amount, add_time, confirm_time, pay_time, shipping_time, ok_time, froms')
            ->where($where)
            ->order('add_time desc')
            ->limit($skip,$maxperpage)
            ->select();
        $timeformat=C('timeformat');
        foreach($list as $key=>$vo){
            $list[$key]['goods_amount'] = price_format($vo['goods_amount'],0);
            $list[$key]['shipping_fee'] = price_format($vo['shipping_fee'],0);
            $list[$key]['order_total_amount'] = price_format($vo['order_total_amount'],0);
            $list[$key]['mobile'] = '+7 '.$vo['mobile'];

            $list[$key]['add_time'] = date($timeformat,$vo['add_time']);
            if($vo['confirm_time']>0){
                $list[$key]['confirm_time'] = date($timeformat,$vo['confirm_time']);
            }
            if($vo['pay_time']>0){
                $list[$key]['pay_time'] = date($timeformat,$vo['pay_time']);
            }
            if($vo['shipping_time']>0){
                $list[$key]['shipping_time'] = date($timeformat,$vo['shipping_time']);
            }
            if($vo['ok_time']>0){
                $list[$key]['ok_time'] = date($timeformat,$vo['ok_time']);
            }
            $order_status = $vo['order_status'];
            $shipping_status = $vo['shipping_status'];
            $pay_status = $vo['pay_status'];
            $ok_time = $vo['ok_time'];
            $shipping_time = $vo['shipping_time'];

            //综合判断该订单有没有退货信息，主表没有货品信息，所以现行判断该订单是否有退货，然后在具体判断商品货品是否有退货
            $checkorderback=M('back_order')->field('back_id')->where('order_id='.$vo['order_id'].' and back_type=1')->select();
            $arrback_id=array();
            if($checkorderback){
                foreach ($checkorderback as $kk=>$vv) {
                    $arrback_id[]=$vv['back_id'];
                }
            }
            $arrback_id[]=-1;
            //读取订单商品
            $order_total_count = 0;
            $goodslist=M('order_goods')->field('ecs_order_goods.rec_id, ecs_order_goods.goods_id, ecs_order_goods.goods_name, ecs_order_goods.goods_sn, ecs_order_goods.product_id, ecs_order_goods.goods_number, ecs_order_goods.goods_price, ecs_order_goods.goods_attr, ecs_order_goods.goods_attr_id, ecs_order_goods.is_back, ecs_order_goods.promote_price, ecs_order_goods.comment_state, ecs_goods.goods_thumb, ecs_products.product_sn')
                ->join('LEFT JOIN ecs_goods ON ecs_goods.goods_id = ecs_order_goods.goods_id')
                ->join('LEFT JOIN ecs_products ON ecs_products.product_id = ecs_order_goods.product_id')
                ->where('order_id='.$vo['order_id'])->order('rec_id asc')->select();
            foreach ($goodslist as $kk=>$vv) {
                $goods_attr = str_replace("\n","",$vv['goods_attr']);
                $goods_attr = str_replace(chr(10),'',$goods_attr);
                $goodslist[$kk]['goods_attr'] = $goods_attr;

                if($vv['promote_price']>0){
                    $goods_price=$vv['promote_price'];
                }else{
                    $goods_price=$vv['goods_price'];
                }
                $goodslist[$kk]['goods_price'] = price_format($goods_price,0);
                $goodslist[$kk]['subtotal'] = price_format($goods_price*$vv['goods_number'],0);

                $goodslist[$kk]['goods_thumb'] = $rooturl.$vv['goods_thumb'];

                //是否能够退货
                $back_code='none';
                $subwhere=array();
                $subwhere['back_id']=array('in',$arrback_id);
                $subwhere['goods_id']=$vv['goods_id'];
                if($vv['product_id']>0){
                    //目前退货里面没有货品ID，所以还需要增加货品ID0的判断
                    $pids=array(0,$vv['product_id']);
                    $subwhere['product_id']=array('in',$pids);
                }
                $checkgoodsback=M('back_goods')->field('rec_id')->where($subwhere)->limit(1)->find();
                if($checkgoodsback){
                    $back_code='detail';
                }else{
                    $limitday=14;
                    $backlimittime=time()-$limitday*86400;
                    if(($ok_time>0 and $order_status==5 and $shipping_status==2 and $pay_status==2 and $ok_time>=$backlimittime) or ($shipping_time>0 and $order_status==5 and $shipping_status==1 and $pay_status==2)){
                        $back_code='can';
                    }
                }
                $goodslist[$kk]['back_code'] = $back_code;

                $order_total_count++;
            }

            $list[$key]['order_total_count'] = $order_total_count;
            $list[$key]['goodslist'] = $goodslist;

            //当前状态支持的操作
            $payurl='';
            $handler=array();
            if($vo['order_status']==0){//未支付状态  支付   取消
                if($vo['pay_status']==0){
                    $out_trade_no=M('pay_log')->where('order_id='.$vo['order_id'])->limit(1)->getField('log_id');
                    if($vo['pay_id']==1){
                        $payurl = $approoturl.'tonglian_pay/pay.php?out_trade_no='.$out_trade_no;
                    }elseif ($vo['pay_id']==3){
                        $payurl = $approoturl.'paybox_pay/pay.php?out_trade_no='.$out_trade_no;
                    }elseif ($vo['pay_id']==8){
                        $payurl = $approoturl.'kassa24_pay/pay.php?out_trade_no='.$out_trade_no;
                    }elseif ($vo['pay_id']==10){
                        $payurl = $approoturl.'epay_pay/pay.php?out_trade_no='.$out_trade_no;
                    }
                    $handler[] = array('btn'=>'pay_money','name'=>L('order_pay_money'),'info'=>array('out_trade_no'=>$out_trade_no));
                }

                $handler[] = array('btn'=>'cancel','name'=>L('order_cancel'));
                $handler[] = array('btn'=>'view_order','name'=>L('order_view_order'),'info'=>array());
            }elseif ($vo['order_status']==1 or $vo['order_status']==5 or $vo['order_status']==6){
                //已经确认或者已经发货
                if($vo['shipping_status']==1){//已发货  查看物流
                    $handler[] = array('btn'=>'view_wuliu','name'=>L('order_view_wuliu'));
                }elseif ($vo['shipping_status']==2){// 已签收   晒单   查看物流
                    foreach ($goodslist as $kk=>$vv) {
                        if (!$vv['comment_state']) {
                            $handler[] = array('btn'=>'comment_shaidan','name'=>L('order_comment_shaidan'),'info'=>array());
                            break;
                        }
                    }
                    $handler[] = array('btn'=>'view_wuliu','name'=>L('order_view_wuliu'));
                }else{
                    if ($vo['pay_status'] == 0) {
                        $out_trade_no=M('pay_log')->where('order_id='.$vo['order_id'])->limit(1)->getField('log_id');
                        if($vo['pay_id']==1){
                            $payurl = $approoturl.'tonglian_pay/pay.php?out_trade_no='.$out_trade_no;
                        }elseif ($vo['pay_id']==3){
                            $payurl = $approoturl.'paybox_pay/pay.php?out_trade_no='.$out_trade_no;
                        }elseif ($vo['pay_id']==8){
                            $payurl = $approoturl.'kassa24_pay/pay.php?out_trade_no='.$out_trade_no;
                        }elseif ($vo['pay_id']==10){
                            $payurl = $approoturl.'epay_pay/pay.php?out_trade_no='.$out_trade_no;
                        }
                        $handler[] = array('btn'=>'pay_money','name'=>L('order_pay_money'),'info'=>array('out_trade_no'=>$out_trade_no));
                    }elseif ($vo['pay_status'] == 2){
                        $checkrefund = M('back_order')->field('status_back,back_id')->where('order_id='.$vo['order_id'].' and back_type=4')->limit(1)->find();
                        if($checkrefund){
                            $handler[] = array('btn'=>'tk','name'=> L('order_refunddetail') ,'info'=>array('tkstatus' => 1,'back_id' => $checkrefund['back_id']));
                        }else{
                            $handler[] = array('btn'=>'tk','name'=> L('order_refund') ,'info'=>array('tkstatus' => 0));
                        }
                    }else{
                        $handler[] = array('btn'=>'cancel','name'=>L('order_cancel'));
                    }
                }
                $handler[] = array('btn'=>'view_order','name'=>L('order_view_order'),'info'=>array());
            }elseif($vo['order_status']==2){//取消
                $handler[] = array('btn'=>'view_order','name'=>L('order_status_cancel'),'info'=>array());
            }elseif($vo['order_status']==3){//无效
                $handler[] = array('btn'=>'view_order','name'=>L('order_status_invalid'),'info'=>array());
            }elseif($vo['order_status']==4){//退款
                $handler[] = array('btn'=>'view_order','name'=>L('order_status_refund'),'info'=>array());
            }


            $list[$key]['payurl'] = $payurl;
            $list[$key]['handler'] = $handler;
        }
        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->where($where)->count('order_id');

        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;
//        print_r($list);
//        exit;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取用户优惠券列表
    public function Getusercouponlist(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $showtype = I('param.showtype/d',1,'strip_tags,htmlspecialchars');//1可用   2已用   3过期
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        /*$hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }*/

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

        //加载列表
        $db = M('new_coupon_logs');
        $where['userid']=$user_id;
        switch ($showtype){
            case 1://可用
                $where['status']=1;
                $stoptime=date('Y-m-d H:i:s',time());
                $stoptime=local_strtotime($stoptime,0);
                $where['stoptime'] = array('egt',$stoptime);
                break;
            case 2://已用
                $where['status']=2;
                break;
            case 3://过期
                $stoptime=date('Y-m-d H:i:s',time());
                $stoptime=local_strtotime($stoptime,0);
                $where['_string'] = "status=0 or (status=1 and stoptime<=$stoptime)";
                break;
        }

        if($keyword!=''){
            $where['name|serialnum'] =array('like','%'.$keyword.'%');
        }

        $list = $db->fetchSql(false)
            ->field('id, couponid, name, serialnum, amount, limitamount, memo, starttime, stoptime, forrange, forcateid, forgoodsid, order_id, order_sn, gettime, usetime, status')
            ->where($where)
            ->order('id desc')
            ->limit($skip,$maxperpage)
            ->select();
        $timeformat=C('timeformat');
        $dateformat=C('dateformat');
        foreach($list as $key=>$vo){
            $list[$key]['amount'] = price_format($vo['amount'],0);
            $list[$key]['limitamount'] = price_format($vo['limitamount'],0);

            if($vo['forcateid']!=''){
                $forcateid = trim($vo['forcateid'],',');
                $arrforcateid = explode(',',$forcateid);
                $where=array();
                $where['cat_id'] = array('in',$arrforcateid);
                $forcatelist=M('category')->field('cat_id, cat_name')->where($where)->select();
                $list[$key]['forcatelist']=$forcatelist;
            }
            if($vo['forgoodsid']!=''){
                // ecs_ goods goods_id, goods_sn, goods_name
                $forgoodsid = trim($vo['forgoodsid'],',');
                $forgoodsid = str_replace("\r\n","",$forgoodsid);
                $forgoodsid = str_replace(chr(10),"",$forgoodsid);
                $forgoodsid = str_replace(chr(13),"",$forgoodsid);
                $forgoodsid = str_replace(chr(10).chr(13),"",$forgoodsid);
                $arrforgoodsid = explode(',',$forgoodsid);
                $hasmore=false;
                if(count($arrforgoodsid)>5){
                    $hasmore=true;
                }
                $where=array();
                $where['is_on_sale']=1;
                $where['goods_id'] = array('in',$arrforgoodsid);
                $forgoodslist=M('goods')->field('goods_id, goods_sn, goods_name')->where($where)->limit(5)->select();
                foreach ($forgoodslist as $kk=>$vv) {
                    $forgoodslist[$kk]['goods_name']=$vv['goods_name'];
                    unset($forgoodslist[$kk]['goods_sn']);
                }
                $list[$key]['forgoodslist']=$forgoodslist;
                $list[$key]['hasmore']=$hasmore;
            }

            $list[$key]['gettime_show']=date($timeformat,$vo['gettime']);
            if($vo['usetime']>0){
                $list[$key]['usetime_show']=date($timeformat,$vo['usetime']);
            }else{
                $list[$key]['usetime_show']='';
            }
            $list[$key]['starttime_show']=date($dateformat,$vo['starttime']);
            $list[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);
        }
//        print_r($list);
//        exit;

        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->where($where)->count('id');


        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取优惠券统计
    public function Getcoupondata(){
        $result = array('statuscode' => 200, 'message' => '');

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }


        $db = M('new_coupon_logs');

        $where['userid']=$user_id;
        $where['status']=1;
        $stoptime=date('Y-m-d H:i:s',time());
        $stoptime=local_strtotime($stoptime,0);
        $where['stoptime'] = array('egt',$stoptime);
        $count_enable = $db->fetchSql(false)->where($where)->count('id');
        $count_enable=intval($count_enable);

        $where=array();
        $where['userid']=$user_id;
        $where['status']=2;
        $count_used = $db->fetchSql(false)->where($where)->count('id');
        $count_used=intval($count_used);

        $where=array();
        $where['userid']=$user_id;
        $stoptime=date('Y-m-d H:i:s',time());
        $stoptime=local_strtotime($stoptime,0);
        $where['_string'] = "status=0 or (status=1 and stoptime<=$stoptime)";
        $count_expired = $db->fetchSql(false)->where($where)->count('id');
        $count_expired=intval($count_expired);

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['count_enable'] = $count_enable;
        $result['count_used'] = $count_used;
        $result['count_expired'] = $count_expired;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取优惠券列表
    public function Getcouponlist(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        if($user_id>0){
            $hmackey=C('hmackey');
            if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
                $checkstr=$timestamp.'-user-id-'.$user_id;
                $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                    if($checksign!=$sign){
                        $result['statuscode'] = 300;
                        $result['message'] = L('global_error_api');
                        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        /*
        ecs_ new_coupon couponid, name, cateid, serialnum, aesserialnum, amount, limitamount, memo, limitgetcount, leftgetcount, getcount, limitnum, limittype, limitdays, starttime, stoptime, forrange, forcateid, forgoodsid, pointset, pointamount, from_userid, for_new, for_rank, for_list, for_getway, is_top, is_hot, is_new, is_recommend, shortnum, createtime, status,
----------------------------------------------------------------
ecs_new_coupon_category cateid, catename, readme, limittype, limitdays, starttime, stoptime, from_userid, pointset,
----------------------------------------------------------------
ecs_new_coupon_logs id, userid, couponid, name, serialnum, aesserialnum, amount, limitamount, memo, starttime, stoptime, forrange, forcateid, forgoodsid, order_id, order_sn, getway, gettime, usetime, ip, status

ecs_goods goods_id, goods_sn, goods_name

        ecs_category cat_id, cat_name



         */
//        $user_id  $showtype  $keyword

        //加载列表
        $db = M('new_coupon');
        $where['status'] = 1;
//        $where['_string'] = '((limitgetcount>0 and leftgetcount>0) or limitgetcount=0)';
        $where['for_getway'] = array('in','0,1');
        $where['for_list']=1;
        $where['is_recommend']=0;
        $where['from_userid']=0;
        $where['_string']="(limitgetcount=0 or (limitgetcount>0 and leftgetcount>0))";
        $list = $db->fetchSql(false)
            ->field('couponid, name, serialnum, amount, limitamount, memo, limitnum, limittype, limitdays, starttime, stoptime, forrange, forcateid, forgoodsid, for_new, for_rank, is_top, is_hot, is_new, is_recommend')
            ->where($where)
            ->order('is_top desc,shortnum asc,couponid desc')
            ->limit($skip,$maxperpage)
            ->select();
        $timeformat=C('timeformat');
        $dateformat=C('dateformat');
        $linkapproot=C('linkapproot');
        foreach($list as $key=>$vo){
            $list[$key]['amount'] = price_format($vo['amount'],0);
            $list[$key]['limitamount'] = price_format($vo['limitamount'],0);

            if($vo['forcateid']!=''){
                $forcateid = trim($vo['forcateid'],',');
                $arrforcateid = explode(',',$forcateid);
                $where=array();
                $where['cat_id'] = array('in',$arrforcateid);
                $forcatelist=M('category')->field('cat_id, cat_name')->where($where)->select();
                $list[$key]['forcatelist']=$forcatelist;
            }

            if($vo['forgoodsid']!=''){
                // ecs_ goods goods_id, goods_sn, goods_name
                $forgoodsid = trim($vo['forgoodsid'],',');
                $forgoodsid = str_replace("\r\n","",$forgoodsid);
                $forgoodsid = str_replace(chr(10),"",$forgoodsid);
                $forgoodsid = str_replace(chr(13),"",$forgoodsid);
                $forgoodsid = str_replace(chr(10).chr(13),"",$forgoodsid);
                $arrforgoodsid = explode(',',$forgoodsid);
                $hasmore=false;
                if(count($arrforgoodsid)>5){
                    $hasmore=true;
                }
                $where=array();
                $where['is_on_sale']=1;
                $where['goods_id'] = array('in',$arrforgoodsid);
                $forgoodslist=M('goods')->field('goods_id, goods_sn, goods_name')->where($where)->limit(5)->select();
                foreach ($forgoodslist as $kk=>$vv) {
                    $forgoodslist[$kk]['goods_name']=$vv['goods_name'];
                    unset($forgoodslist[$kk]['goods_sn']);
                }
                $list[$key]['forgoodslist']=$forgoodslist;
                $list[$key]['hasmore']=$hasmore;
            }
            if($vo['starttime']>0){
                $list[$key]['starttime_show']=date($dateformat,$vo['starttime']);
            }
            if($vo['stoptime']>0){
                $list[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);
            }
        }

        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->where($where)->count('couponid');


        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }


        //如果是第一页加载广告及推荐优惠券
        if($skip==0){
            $where=array();
            $where['status'] = 1;
            $where['_string'] = '((limitgetcount>0 and leftgetcount>0) or limitgetcount=0)';
            $where['for_getway'] = array('in','0,1');
            $where['for_list']=1;
            $where['is_recommend']=1;
            $where['from_userid']=0;
            $reclist = $db->fetchSql(false)
                ->field('couponid, name, serialnum, amount, limitamount, memo, limitnum, limittype, limitdays, starttime, stoptime, forrange, forcateid, forgoodsid, for_new, for_rank, is_top, is_hot, is_new, is_recommend')
                ->where($where)->order('is_top desc,shortnum asc,couponid desc')->limit(10)->select();
            foreach($reclist as $key=>$vo){
                $reclist[$key]['amount'] = price_format($vo['amount'],0);
                $reclist[$key]['limitamount'] = price_format($vo['limitamount'],0);

                if($vo['forcateid']!=''){
                    $forcateid = trim($vo['forcateid'],',');
                    $arrforcateid = explode(',',$forcateid);
                    $where=array();
                    $where['cat_id'] = array('in',$arrforcateid);
                    $forcatelist=M('category')->field('cat_id, cat_name')->where($where)->select();
                    $reclist[$key]['forcatelist']=$forcatelist;
                }

                if($vo['forgoodsid']!=''){
                    // ecs_ goods goods_id, goods_sn, goods_name
                    $forgoodsid = trim($vo['forgoodsid'],',');
                    $forgoodsid = str_replace("\r\n","",$forgoodsid);
                    $forgoodsid = str_replace(chr(10),"",$forgoodsid);
                    $forgoodsid = str_replace(chr(13),"",$forgoodsid);
                    $forgoodsid = str_replace(chr(10).chr(13),"",$forgoodsid);
                    $arrforgoodsid = explode(',',$forgoodsid);
                    $hasmore=false;
                    if(count($arrforgoodsid)>5){
                        $hasmore=true;
                    }
                    $where=array();
                    $where['is_on_sale']=1;
                    $where['goods_id'] = array('in',$arrforgoodsid);
                    $forgoodslist=M('goods')->field('goods_id, goods_sn, goods_name')->where($where)->limit(5)->select();
                    foreach ($forgoodslist as $kk=>$vv) {
                        $forgoodslist[$kk]['goods_name']=$vv['goods_name'];
                        unset($forgoodslist[$kk]['goods_sn']);
                    }
                    $reclist[$key]['forgoodslist']=$forgoodslist;
                    $reclist[$key]['hasmore']=$hasmore;
                }
                if($vo['starttime']>0){
                    $reclist[$key]['starttime_show']=date($dateformat,$vo['starttime']);
                }
                if($vo['stoptime']>0){
                    $reclist[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);
                }
            }

            // $advlist
//            ecs_ new_adv advid, zoneid, advname, imgurl, starttime, stoptime, extras, extrastitle, visitcount, shortnum, createtime, updatetime, status
            $where=array();
            $where['zoneid'] = 1;
            $where['status'] = 1;
            $where['starttime'] = array('elt',time());
            $where['stoptime'] = array('egt',time());
            $advlist = M('new_adv')->field('advid, advname, imgurl, extras')->where($where)->order('shortnum asc,advid desc')->limit(10)->select();
            foreach ($advlist as $key=>$vo) {
                $advlist[$key]['imgurl'] = $linkapproot.$vo['imgurl'];
            }
        }

        $skip = $skip+$maxperpage;


        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;
        $result['reclist'] = $reclist;
        $result['advlist'] = $advlist;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取优惠券详情商品列表
    public function Getcoupondetail(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $couponid = I('param.couponid/d',0,'strip_tags,htmlspecialchars'); //主ID

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        if($user_id>0){
            $hmackey=C('hmackey');
            if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
                $checkstr=$timestamp.'-user-id-'.$user_id;
                $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                    if($checksign!=$sign){
                        $result['statuscode'] = 300;
                        $result['message'] = L('global_error_api');
                        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }

        $timeformat=C('timeformat');
        $dateformat=C('dateformat');
        $rooturl = C('mainroot');
        $approoturl = C('approot');

        if($couponid<=0){
            $result['statuscode'] = 300;
            $result['message'] = '优惠券信息读取失败，请重试';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $where['couponid']=$couponid;
            $where['status']=1;
            $coupondata=M('new_coupon')->field('couponid, name, amount, limitamount, limittype, limitdays, starttime, stoptime, forrange, forgoodsid')->where($where)->limit(1)->find();
            if(empty($coupondata)){
                $result['statuscode'] = 300;
                $result['message'] = L('getcoupon_error_couponerr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
            $coupondata['amount'] = price_format($coupondata['amount'],0);
            $coupondata['limitamount'] = price_format($coupondata['limitamount'],0);
            if($coupondata['starttime']>0){
                $coupondata['starttime_show']=date($dateformat,$coupondata['starttime']);
            }
            if($coupondata['stoptime']>0){
                $coupondata['stoptime_show']=date($dateformat,$coupondata['stoptime']);
            }

            $arrforgoodsid=array(0);
            if($coupondata['forrange']==2){
                $forgoodsid = trim($coupondata['forgoodsid'],',');
                $forgoodsid = str_replace("\r\n","",$forgoodsid);
                $forgoodsid = str_replace(chr(10),"",$forgoodsid);
                $forgoodsid = str_replace(chr(13),"",$forgoodsid);
                $forgoodsid = str_replace(chr(10).chr(13),"",$forgoodsid);
                $arrforgoodsid = explode(',',$forgoodsid);
            }
        }

        //加载列表
        $db = M('goods');
        $where=array();
        $where['ecs_goods.is_real'] = 1;
        $where['ecs_goods.is_on_sale']=1;
        $where['ecs_goods.goods_id'] = array('in',$arrforgoodsid);
        $list = $db->fetchSql(false)
            ->field('ecs_goods.goods_id, ecs_goods.goods_sn, ecs_goods.goods_name, ecs_goods.market_price, ecs_goods.shop_price, ecs_goods.is_promote, ecs_goods.promote_price, ecs_goods.promote_start_date, ecs_goods.promote_end_date, ecs_goods.goods_thumb, ecs_goods.cang_id, ecs_goods_cang.cang_name, ecs_goods_cang.keyname, ecs_goods_cang.showkeyname, ecs_goods_cang.keycolor, ecs_goods.subscript_sw, ecs_goods.subscript_url, ecs_goods.subscript_pos, ecs_goods.subscript_width, ecs_goods.sales_volume_base')
            ->join('LEFT JOIN ecs_goods_cang ON ecs_goods_cang.id = ecs_goods.cang_id')
            ->where($where)
            ->order('ecs_goods.last_update desc,ecs_goods.sort_order asc,ecs_goods.goods_id desc')
            ->limit($skip,$maxperpage)
            ->select();

        foreach($list as $key=>$vo){
            $list[$key]['goods_thumb'] = $rooturl.'/'.$vo['goods_thumb'];
            if($vo['subscript_url']!=''){
                $list[$key]['subscript_url'] = $rooturl.$vo['subscript_url'];
            }
            $list[$key]['market_price'] = price_format($vo['market_price'],0);
            $list[$key]['shop_price'] = price_format($vo['shop_price'],0);

            $promote_price=$vo['promote_price'];
            if($promote_price>0 and $vo['is_promote']==1){
                $currenttime = time();
                if ($currenttime < $vo['promote_start_date'] or $currenttime > $vo['promote_end_date']) {
                    $promote_price = 0;
                }
            }
            $list[$key]['promote_price'] = price_format($promote_price,0);
            $final_price = $vo['shop_price'];
            if($promote_price>0){
                $final_price=min($promote_price,$vo['shop_price']);
            }
            $list[$key]['final_price'] = price_format($final_price,0);

            $sale_num_true = M('order_goods')->where('goods_id='.$vo['goods_id'])->sum('goods_number');
            $sale_num_true = intval($sale_num_true);
            $sale_num = $sale_num_true+intval($vo['sales_volume_base']);
            $list[$key]['sale_num'] = $sale_num;
        }

        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->join('LEFT JOIN ecs_goods_cang ON ecs_goods_cang.id = ecs_goods.cang_id')->where($where)->count('ecs_goods.goods_id');


        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;
        $result['coupondata'] = $coupondata;

        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取商品可用的券
    public function Getgoodscoupon(){
        $result = array('statuscode' => 200, 'message' => '');
        $goods_id = I('param.goods_id/d',0,'strip_tags,htmlspecialchars');

        if($goods_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '无法读取商品信息，请重试';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $where=array();
            $where['is_real'] = 1;
            $where['is_on_sale']=1;
            $where['goods_id'] = $goods_id;
            $goodsdata=M('goods')->field('goods_id,cat_id')->where($where)->limit(1)->find();
            if(empty($goodsdata)){
                $result['statuscode'] = 300;
                $result['message'] = '当前商品不存在或者已下架';
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
            $cat_id = $goodsdata['cat_id'];
        }
        // ecs_new_coupon couponid, name, cateid, serialnum, aesserialnum, amount, limitamount, memo, limitgetcount, leftgetcount, getcount, limitnum, limittype, limitdays, starttime, stoptime, forrange, forcateid, forgoodsid, pointset, pointamount, from_userid, for_new, for_rank, for_list, for_getway, is_top, is_hot, is_new, is_recommend, shortnum, createtime, status


        //读取商品定向券
        $checkgoodsid=','.$goods_id.',';
        $couponlist=array();
        $timeformat=C('timeformat');
        $dateformat=C('dateformat');

        $where=array();
        $where['status']=1;
        $where['forrange']=2;
        $where['for_getway'] = array('in','0,1');
        $where['for_list']=1;
        $where['from_userid']=0;
        $where['forgoodsid']=array('like','%'.$checkgoodsid.'%');
        $where['_string']="(limitgetcount=0 or (limitgetcount>0 and leftgetcount>0))";
        $list1=M('new_coupon')->field('couponid, name, amount, limitgetcount, leftgetcount, getcount, limitamount, limittype, limitdays, starttime, stoptime')->where($where)->order('shortnum asc,couponid desc')->select();
        foreach ($list1 as $key=>$vo) {
            $list1[$key]['amount'] = price_format($vo['amount'],0);
            $list1[$key]['limitamount'] = price_format($vo['limitamount'],0);

            if($vo['starttime']>0){
                $list1[$key]['starttime_show']=date($dateformat,$vo['starttime']);
            }
            if($vo['stoptime']>0){
                $list1[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);
            }
        }
        //读取分类定向券
        $list2=array();

        //读取未定向券
        $where=array();
        $where['status']=1;
        $where['forrange']=0;
        $where['for_getway'] = array('in','0,1');
        $where['for_list']=1;
        $where['from_userid']=0;
        $where['_string']="(limitgetcount=0 or (limitgetcount>0 and leftgetcount>0))";
        $list3=M('new_coupon')->field('couponid, name, amount, limitgetcount, leftgetcount, getcount, limitamount, limittype, limitdays, starttime, stoptime')->where($where)->order('shortnum asc,couponid desc')->select();
        foreach ($list3 as $key=>$vo) {
            $list3[$key]['amount'] = price_format($vo['amount'],0);
            $list3[$key]['limitamount'] = price_format($vo['limitamount'],0);

            if($vo['starttime']>0){
                $list3[$key]['starttime_show']=date($dateformat,$vo['starttime']);
            }
            if($vo['stoptime']>0){
                $list3[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);
            }
        }

        $couponlist=array_merge($list1,$list2,$list3);
        if($couponlist){
            $result['statuscode'] = 200;
            $result['message'] = 'OK';
            $result['couponlist'] = $couponlist;
            $result['hascoupon'] = true;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $result['statuscode'] = 300;
            $result['message'] = 'NODATA';
            $result['couponlist'] = $couponlist;
            $result['hascoupon'] = false;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
    }

    //获取站内信
    public function Getusermsglist(){
        $result = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $keyword = I('param.keyword/s','','strip_tags,htmlspecialchars');
        $keyword = trim($keyword);

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
//        $user_id=10098;

        /*$hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }*/

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '请登陆后继续操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

        //加载列表
        $db = M('new_user_message');
        $where['_string'] = '((msgtype=1 and user_id='.$user_id.') or msgtype=2) and status=1';
        if($keyword!=''){
            $where['title|description|msgcontent'] =array('like','%'.$keyword.'%');
        }
        $list = $db->fetchSql(false)
            ->field('id, msgtype, user_id, title, description, extras, extratitle, isredirect, forcount, readcount, uidreadlist, createtime, status')
            ->where($where)
            ->order('id desc')
            ->limit($skip,$maxperpage)
            ->select();
        foreach($list as $key=>$vo){
            $list[$key]['createtime_show'] = $this->dateshow($vo['createtime']);
            $isread=0;
            $chkuid = ','.$user_id.',';
            $uidreadlist = $vo['uidreadlist'];
            if(stripos($uidreadlist,$chkuid)!==false){
                $isread=1;
            }
            $list[$key]['isread'] = $isread;
        }

        $pagecount = count($list);
        $pagetotalcount = $db->fetchSql(false)->where($where)->count('id');

        if($list){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount<$maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['skip'] = $skip;
        $result['pagecount'] = $pagecount;
        $result['pagetotalcount'] = $pagetotalcount;
        $result['list'] = $list;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }
    private function dateshow($time){
        $localdate=date('Y-m-d',time());
        $currenttime=time();
        $timestampDiff = $currenttime-$time;
        $today_start=local_strtotime($localdate.' 00:00:00',0);
        $today_end=local_strtotime($localdate.' 23:59:59',0);
        $timeformat = C('timeformat');
        if($timestampDiff<180){
            $str='момент';
        }elseif ($timestampDiff<3600){
            $str=floor($timestampDiff/60).' минут назад';
        }elseif ($time<=$today_end and  $time>=$today_start){
            $str=date('H:i',$time);
        }else{
            $str = date($timeformat, $time);
        }
        return $str;
    }

    //操作消息
    public function Usermsgoption(){
        $result = array('statuscode' => 200, 'message' => '');
        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        $action = I('param.action/s','','strip_tags,htmlspecialchars');
        $id = I('param.id/d',0,'strip_tags,htmlspecialchars'); //主ID

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '用户信息获取失败';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $userdata=M('users')->field('yaoqing_code, mobile_phone, user_name')->where('user_id='.$user_id)->limit(1)->find();
            if(empty($userdata)){
                $result['statuscode'] = 300;
                $result['message'] = '用户信息获取失败';
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $user_name = $userdata['user_name'];
                $user_mobile = $userdata['mobile_phone'];
            }
        }

        $allowaction=array('setisread');
        if(!in_array($action,$allowaction)){
            $result['statuscode'] = 300;
            $result['message'] = '请勿非法操作';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        if($id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '短消息参数不正确';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        switch ($action){
            case 'setisread':
                $msgdata=M('new_user_message')->field('id, readcount, uidreadlist')->where('id='.$id)->limit(1)->find();
                if(empty($msgdata)){
                    $result['statuscode'] = 300;
                    $result['message'] = '短消息不存在或者已被删除';
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
                $msg_readcount=$msgdata['readcount'];
                $msg_uidreadlist=$msgdata['uidreadlist'];

                $where['msg_id'] = $id;
                $where['user_id'] = $user_id;
                $checkread=M('new_user_message_readlist')->field('id,readcount')->where($where)->limit(1)->find();
                if($checkread){
                    $readcount = $checkread['readcount'];
                    $readcount++;
                    $read_id = $checkread['id'];
                    $newdata=array(
                        'readcount'=>$readcount,
                        'updatetime'=>time()
                    );
                    M('new_user_message_readlist')->where('id='.$read_id)->setField($newdata);
                }else{
                    $readtime = time();
                    $data=array();
                    $data['msg_id'] = $id;
                    $data['user_id'] = $user_id;
                    $data['user_name'] = $user_name;
                    $data['user_mobile'] = $user_mobile;
                    $data['readcount'] = 1;
                    $data['readtime'] = $readtime;
                    $data['updatetime'] = $readtime;
                    M('new_user_message_readlist')->add($data);

                    //写入主记录
                    $msg_readcount++;
                    $msg_uidreadlist==''?$msg_uidreadlist=','.$user_id.',':$msg_uidreadlist.=$user_id.',';

                    $newdata=array(
                        'readcount'=>$msg_readcount,
                        'uidreadlist'=>$msg_uidreadlist
                    );
                    M('new_user_message')->where('id='.$id)->setField($newdata);
                }
                $result['statuscode'] = 200;
                $result['message'] = 'OK';
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                break;
        }
    }

    //读取消息
    public function Getusermsgdata(){
        $result = array('statuscode' => 200, 'message' => '');

        $id = I('param.id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');



        $hmackey=C('hmackey');
        $timeformat = C('timeformat');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = '用户信息获取失败';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $userdata=M('users')->field('yaoqing_code, mobile_phone, user_name')->where('user_id='.$user_id)->limit(1)->find();
            if(empty($userdata)){
                $result['statuscode'] = 300;
                $result['message'] = '用户信息获取失败';
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
        }
        
        $where=array();
        $where['status']=1;
        $where['id']=$id;
        $where['_string'] = '(msgtype=1 and user_id='.$user_id.') or msgtype=2';
        $msgdata = M('new_user_message')->field('id, title, msgcontent, extras, extratitle, isredirect, createtime')->where($where)->limit(1)->find();
        if(empty($msgdata)){
            $result['statuscode'] = 300;
            $result['message'] = '当前短消息不存在或者已被删除';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        $msgdata['createtime'] = date($timeformat,$msgdata['createtime']);

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['msgdata'] = $msgdata;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //领取优惠券
    public function Receivecoupon(){
        $result = array('statuscode' => 200, 'message' => '');
        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');
        $couponid = I('param.couponid/d',0,'strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        $ip = get_client_ip();
        $ip = bindec(decbin(ip2long($ip)));

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = L('global_error_user');
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $userdata=M('users')->field('yaoqing_code, mobile_phone, user_name, user_rank')->where('user_id='.$user_id)->limit(1)->find();
            if(empty($userdata)){
                $result['statuscode'] = 300;
                $result['message'] = L('global_error_user');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $yaoqing_code = $userdata['yaoqing_code'];
                $user_mobile = $userdata['mobile_phone'];
                $user_name = $userdata['user_name'];
                $user_rank = $userdata['user_rank'];
            }
        }
        //判断用户类型
        $user_is_new=true;
        $where=array();
        $where['user_id']=$user_id;
        $checkhasorder=M('order_info')->where($where)->count('order_id');
        $checkhasorder=intval($checkhasorder);
        if($checkhasorder>0){
            $user_is_new=false;
        }
        $where=array();
        $where['couponid']=$couponid;
        $where['status']=1;
        $coupondata=M('new_coupon')->field('couponid, name, cateid, serialnum, aesserialnum, amount, limitamount, memo, limitgetcount, leftgetcount, getcount, limitnum, limittype, limitdays, starttime, stoptime, forrange, forcateid, forgoodsid, for_new, for_rank, for_list, for_getway')->where($where)->limit(1)->find();
        if(empty($coupondata)){
            $result['statuscode'] = 300;
            $result['message'] = L('getcoupon_error_couponerr');
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        if($coupondata['limitgetcount']>0){
            if($coupondata['leftgetcount']==0){
                $result['statuscode'] = 300;
                $result['message'] = L('getcoupon_msg_null');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
        }

        $limitgetcount = $coupondata['limitgetcount'];
        $leftgetcount = $coupondata['leftgetcount'];
        $getcount = $coupondata['getcount'];

        $limitnum = $coupondata['limitnum'];
        $for_new = $coupondata['for_new'];
        $for_rank = $coupondata['for_rank'];
        $for_rank_name='';
        if($for_rank>=0){
            $for_rank_name=M('user_rank')->where('rank_id='.$for_rank)->limit(1)->getField('rank_name');
        }

        //判断是否已经领取
        $where=array();
        $where['userid']=$user_id;
        $where['couponid']=$couponid;
        $usergetcount=M('new_coupon_logs')->where($where)->count('id');
        $usergetcount=intval($usergetcount);

        if($limitnum<=$usergetcount){
            $result['statuscode'] = 300;
            $result['message'] = L('getcoupon_msg_limitnum',array('limitnum'=>$limitnum,'usergetcount'=>$usergetcount));
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        if($for_new==0){
            if($user_is_new){
                $result['statuscode'] = 300;
                $result['message'] = L('getcoupon_msg_limitold');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
        }elseif ($for_new==1){
            if(!$user_is_new){
                $result['statuscode'] = 300;
                $result['message'] = L('getcoupon_msg_limitnew');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
        }
        if($for_rank>=0){
            if($user_rank!=$for_rank){
                $result['statuscode'] = 300;
                $result['message'] = L('getcoupon_msg_limitrank',array('for_rank_name'=>$for_rank_name));
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }
        }

        if($coupondata['limittype']==1){
            $starttime=local_strtotime(date('Y-m-d 00:00:00',time()),0);
            $stoptime = local_strtotime('+'.$coupondata['limitdays'].' day',0);
            $stoptime = local_strtotime(date('Y-m-d 23:59:59',$stoptime),0);
        }else{
            $starttime = $coupondata['starttime'];
            $stoptime = $coupondata['stoptime'];
        }

        //创建领取记录
        $data=array();
        $data['userid'] = $user_id;
        $data['couponid'] = $couponid;
        $data['name'] = $coupondata['name'];
        $data['serialnum'] = $coupondata['serialnum'];
        $data['aesserialnum'] = $coupondata['aesserialnum'];
        $data['amount'] = $coupondata['amount'];
        $data['limitamount'] = $coupondata['limitamount'];
        $data['memo'] = $coupondata['memo'];
        $data['starttime'] = $starttime;
        $data['stoptime'] = $stoptime;
        $data['forrange'] = $coupondata['forrange'];
        $data['forcateid'] = $coupondata['forcateid'];
        $data['forgoodsid'] = $coupondata['forgoodsid'];
        $data['order_id'] = 0;
        $data['order_sn'] = '';
        $data['getway'] = 1;
        $data['gettime'] = time();
        $data['usetime'] = 0;
        $data['ip'] = $ip;
        $data['status'] = 1;
        M('new_coupon_logs')->add($data);

        //更新领取数量
        $limitgetcount = $coupondata['limitgetcount'];
        $leftgetcount = $coupondata['leftgetcount'];
        $getcount = $coupondata['getcount'];
        if($limitgetcount>0){
            $leftgetcount--;
            $getcount++;
            $newdata=array(
                'leftgetcount'=>$leftgetcount,
                'getcount'=>$getcount
            );
        }else{
            $getcount++;
            $newdata=array(
                'getcount'=>$getcount
            );
        }
        M('new_coupon')->where('couponid='.$couponid)->setField($newdata);

        $result['statuscode'] = 200;
        $result['message'] = L('getcoupon_msg_success',array('amount'=>$coupondata['amount']));
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //获取订单匹配的优惠券
    public function Getuserordercouponlist(){
        $result = array('statuscode' => 200, 'message' => '');
        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars'); //主ID
        $goods_ids = I('param.sendgoods_ids/s','','strip_tags,htmlspecialchars');
        $goods_nums = I('param.sendgoods_nums/s','','strip_tags,htmlspecialchars');
        $goods_prices = I('param.sendgoods_prices/s','','strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
        $list=array();
        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = L('global_error_user');
            $result['list'] = $list;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $user_rank=M('users')->where('user_id='.$user_id)->limit(1)->getField('user_rank');
        }

        if($goods_ids==''){
            $result['statuscode'] = 300;
            $result['message'] = L('order_error_goodsnull');
            $result['list'] = $list;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $arrgoodsid=explode(',',$goods_ids);
            $arrgoodsnum=explode(',',$goods_nums);
            $arrgoodsprice=explode(',',$goods_prices);

            $arrgoods=array();
            foreach ($arrgoodsid as $key=>$goodsid) {
                $cat_id = M('goods')->where('goods_id='.$goodsid)->limit(1)->getField('cat_id');
                $subtotal=0;
                $subtotal=$arrgoodsnum[$key]*$arrgoodsprice[$key];
                $item=array(
                    'goods_id'=>$goodsid,
                    'cat_id'=>$cat_id,
                    'qty'=>$arrgoodsnum[$key],
                    'price'=>$arrgoodsprice[$key],
                    'subtotal'=>$subtotal,
                );
                $arrgoods[]=$item;
            }
        }
        //print_r($arrgoods);

        //加载列表
        $db = M('new_coupon_logs');
        $where['userid']=$user_id;
        $where['status']=1;
        $stoptime=date('Y-m-d H:i:s',time());
        $stoptime=local_strtotime($stoptime,0);
        $where['stoptime'] = array('egt',$stoptime);
        $list = $db->fetchSql(false)
            ->field('id, couponid, name, serialnum, amount, limitamount, starttime, stoptime, forrange, forcateid, forgoodsid, gettime')
            ->where($where)
            ->order('id desc')
            ->select();
        $timeformat=C('timeformat');
        $dateformat=C('dateformat');
        foreach($list as $key=>$vo){
            $list[$key]['amount'] = price_format($vo['amount'],0);
            $list[$key]['limitamount'] = price_format($vo['limitamount'],0);
            $list[$key]['starttime_show']=date($dateformat,$vo['starttime']);
            $list[$key]['stoptime_show']=date($dateformat,$vo['stoptime']);

            //判断是否可用   有效期    金额    商品
            $allowuse=1;
            $notuse_msg='';
            $currenttime=time();
            if($vo['starttime']>$currenttime){//是否开始使用
                $allowuse=0;
                $notuse_msg=L('order_error_nostrat',array('starttime'=>date($dateformat,$vo['starttime'])));
            }else{
                if($vo['forrange']==2){
                    $forgoodsid = trim($vo['forgoodsid'],',');
                    $arrallowgoods=explode(',',$forgoodsid);
                    $allowgoods_limitamount=0;
                    foreach ($arrallowgoods as $kk=>$allowgoodsid) {
                        foreach ($arrgoods as $kkk=>$vvv) {
                            if($allowgoodsid==$vvv['goods_id']){
                                $allowgoods_limitamount+=$vvv['subtotal'];
                            }
                        }
                    }
                    if($allowgoods_limitamount<$vo['limitamount']){
                        $allowuse=0;
                        $notuse_msg=L('order_error_limitamount',array('limitamount'=>$vo['limitamount']));
                    }
                }elseif ($vo['forrange']==0){
                    $allowgoods_limitamount=0;
                    foreach ($arrgoods as $kkk=>$vvv) {
                        $allowgoods_limitamount+=$vvv['subtotal'];
                    }
                    if($allowgoods_limitamount<$vo['limitamount']){
                        $allowuse=0;
                        $notuse_msg=L('order_error_all_limitamount',array('limitamount'=>$vo['limitamount']));
                    }
                }
            }

            $list[$key]['allowuse'] = $allowuse;
            $list[$key]['notuse_msg'] = $notuse_msg;
        }

        $res=array();
        foreach ($list as $key=>$row) {
            $res[] = $row;
        }
        foreach ($res as $user) {
            $ages[] = $user['allowuse'];
        }
        array_multisort($ages, SORT_DESC, $res);
        $list = $res;

        $has_enable_coupon = false;
        $has_coupon_tip = L('order_msg_has_enable_coupon');
        foreach ($list as $key=>$vo) {
            if($vo['allowuse']==1){
                $has_enable_coupon=true;
                break;
            }
        }


        // $list
//        print_r($list);
//        exit;

        if($list){
            $statuscode = 200;
            $message='Success';
        }else{
            $statuscode = 300;
            $message=L('order_error_nocoupon');
        }
        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['list'] = $list;
        $result['has_enable_coupon'] = $has_enable_coupon;
        $result['has_coupon_tip'] = $has_coupon_tip;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //百度境外POI-API更新
    public function Baidugeocodingpoicheck(){
        $result = array('statuscode' => 200, 'message' => '');
        $lng = I('param.lng/s','','strip_tags,htmlspecialchars');
        $lat = I('param.lat/s','','strip_tags,htmlspecialchars');

        // https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location=51.168393,71.468296

        $apiurl='https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location={$lat},{$lng}';

        // 1  3  36   Казахстан   Карагандинская область   Караганда
        // country  province   city
        // ecs_region region_id, parent_id, region_name, region_type, agency_id, ordernum, enkey

        $defa_country_code=1;
        $defa_province_code=3;
        $defa_city_code=36;
        $defa_country='Казахстан';
        $defa_province='Карагандинская область';
        $defa_city='Караганда';

        if($lng=='' or $lat==''){
            echo '基础经纬度信息数据有误';
            exit;
        }
        $apiurl=str_replace('{$lat}',$lat,$apiurl);
        $apiurl=str_replace('{$lng}',$lng,$apiurl);

        $res = file_get_contents($apiurl);
        $gisdata = json_decode($res,true);
        if($gisdata['status']===0){
            $address = $gisdata['result']['addressComponent'];
            $country_encode=$address['country'];
            $province_encode=$address['province'];
            $city_encode=$address['city'];

            if($country_encode=='' or $province_encode=='' or $city_encode==''){
                echo '当前经纬度信息未匹配城市信息';
                exit;
            }else{
                echo '当前经纬度英文地址信息：<br /><br />'.$province_encode.'@'.$city_encode.'<br /><br />请将上述代码复制到对应Excel中';
            }
        }else{
            echo '当前API返回错误';
            exit;
        }
    }

    //百度境外POI-API
    public function Baidugeocodingpoi(){
        $result = array('statuscode' => 200, 'message' => '');
        $lng = I('param.lng/s','','strip_tags,htmlspecialchars');
        $lat = I('param.lat/s','','strip_tags,htmlspecialchars');

        // https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location=51.168393,71.468296

        $apiurl='https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location={$lat},{$lng}';

        // 1  3  36   Казахстан   Карагандинская область   Караганда
        // country  province   city

        $defa_country_id=1;
        $defa_province_id=3;
        $defa_city_id=36;
        $defa_district_id=4130;
        $defa_country='Казахстан';
        $defa_province='Карагандинская область';
        $defa_city='Караганда';
        $defa_district='Казыбек би район';

        if($lng=='' or $lat==''){
            $result['statuscode'] = 200;
            $result['message'] = 'return default gis data.';
            $result['country_id'] = $defa_country_id;
            $result['province_id'] = $defa_province_id;
            $result['city_id'] = $defa_city_id;
            $result['district_id'] = $defa_district_id;
            $result['country'] = $defa_country;
            $result['province'] = $defa_province;
            $result['city'] = $defa_city;
            $result['district'] = $defa_district;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        $apiurl=str_replace('{$lat}',$lat,$apiurl);
        $apiurl=str_replace('{$lng}',$lng,$apiurl);

        $res = file_get_contents($apiurl);
        $gisdata = json_decode($res,true);
        if($gisdata['status']===0){
            $address = $gisdata['result']['addressComponent'];
            $country_en=$address['country'];
            $province_en=$address['province'];
            $city_en=$address['city'];
            $district_en=$address['district'];
            if($country_en=='' or $province_en=='' or $city_en==''){
                $result['statuscode'] = 200;
                $result['message'] = 'return default gis data.';
                $result['country_id'] = $defa_country_id;
                $result['province_id'] = $defa_province_id;
                $result['city_id'] = $defa_city_id;
                $result['district_id'] = $defa_district_id;
                $result['country'] = $defa_country;
                $result['province'] = $defa_province;
                $result['city'] = $defa_city;
                $result['district'] = $defa_district;
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                //判断国家
                $where['enkey']=$country_en;
                $where['region_type']=0;
                $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                if(empty($region)){
                    $result['statuscode'] = 200;
                    $result['message'] = 'return default gis data.';
                    $result['country_id'] = $defa_country_id;
                    $result['province_id'] = $defa_province_id;
                    $result['city_id'] = $defa_city_id;
                    $result['district_id'] = $defa_district_id;
                    $result['country'] = $defa_country;
                    $result['province'] = $defa_province;
                    $result['city'] = $defa_city;
                    $result['district'] = $defa_district;
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }else{
                    $country=$region['region_name'];
                    $country_id=$region['region_id'];
                    //州信息
                    $where=array();
                    $where['enkey']=$province_en;
                    $where['region_type']=1;
                    $where['parent_id']=$country_id;
                    $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                    if(empty($region)){
                        $result['statuscode'] = 200;
                        $result['message'] = 'return default gis data.';
                        $result['country_id'] = $defa_country_id;
                        $result['province_id'] = $defa_province_id;
                        $result['city_id'] = $defa_city_id;
                        $result['district_id'] = $defa_district_id;
                        $result['country'] = $defa_country;
                        $result['province'] = $defa_province;
                        $result['city'] = $defa_city;
                        $result['district'] = $defa_district;
                        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                    }else{
                        $province=$region['region_name'];
                        $province_id=$region['region_id'];

                        //城市信息
                        $where=array();
                        $where['enkey']=$city_en;
                        $where['region_type']=2;
                        $where['parent_id']=$province_id;
                        $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                        if(empty($region)){
                            $result['statuscode'] = 200;
                            $result['message'] = 'return default gis data.';
                            $result['country_id'] = $defa_country_id;
                            $result['province_id'] = $defa_province_id;
                            $result['city_id'] = $defa_city_id;
                            $result['district_id'] = $defa_district_id;
                            $result['country'] = $defa_country;
                            $result['province'] = $defa_province;
                            $result['city'] = $defa_city;
                            $result['district'] = $defa_district;
                            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                        }else{
                            $city=$region['region_name'];
                            $city_id=$region['region_id'];

                            $result['statuscode'] = 200;
                            $result['message'] = 'ok';
                            $result['country_id'] = $country_id;
                            $result['province_id'] = $province_id;
                            $result['city_id'] = $city_id;
                            $result['district_id'] = 0;
                            $result['country'] = $country;
                            $result['province'] = $province;
                            $result['city'] = $city;
                            $result['district'] = '';
                            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            }
        }else{
            $result['statuscode'] = 200;
            $result['message'] = 'return default gis data.';
            $result['country_id'] = $defa_country_id;
            $result['province_id'] = $defa_province_id;
            $result['city_id'] = $defa_city_id;
            $result['district_id'] = $defa_district_id;
            $result['country'] = $defa_country;
            $result['province'] = $defa_province;
            $result['city'] = $defa_city;
            $result['district'] = $defa_district;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
    }

    //根据经纬度获取城市信息，目前采用百度的接口
    public function Geocodingpoi(){
        $result = array('statuscode' => 200, 'message' => '');
        $lng = I('param.lng/s','','strip_tags,htmlspecialchars');
        $lat = I('param.lat/s','','strip_tags,htmlspecialchars');

        // https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location=51.168393,71.468296

        $apiurl='https://api.map.baidu.com/reverse_geocoding/v3/?ak=GUddqQbMClxlbl5GogpiioFC4lcZj988&output=json&coordtype=wgs84ll&language=en&location={$lat},{$lng}';

        // 1  3  36   Казахстан   Карагандинская область   Караганда
        // country  province   city

        $defa_country_id=1;
        $defa_province_id=3;
        $defa_city_id=36;
        $defa_district_id=4130;
        $defa_country='Казахстан';
        $defa_province='Карагандинская область';
        $defa_city='Караганда';
        $defa_district='Казыбек би район';

        if($lng=='' or $lat==''){
            $result['statuscode'] = 200;
            $result['message'] = 'return default gis data.';
            $result['country_id'] = $defa_country_id;
            $result['province_id'] = $defa_province_id;
            $result['city_id'] = $defa_city_id;
            $result['district_id'] = $defa_district_id;
            $result['country'] = $defa_country;
            $result['province'] = $defa_province;
            $result['city'] = $defa_city;
            $result['district'] = $defa_district;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        $apiurl=str_replace('{$lat}',$lat,$apiurl);
        $apiurl=str_replace('{$lng}',$lng,$apiurl);

        $res = file_get_contents($apiurl);
        $gisdata = json_decode($res,true);
        if($gisdata['status']===0){
            $address = $gisdata['result']['addressComponent'];
            $country_en=$address['country'];
            $province_en=$address['province'];
            $city_en=$address['city'];
            $district_en=$address['district'];
            if($country_en=='' or $province_en=='' or $city_en==''){
                $result['statuscode'] = 200;
                $result['message'] = 'return default gis data.';
                $result['country_id'] = $defa_country_id;
                $result['province_id'] = $defa_province_id;
                $result['city_id'] = $defa_city_id;
                $result['district_id'] = $defa_district_id;
                $result['country'] = $defa_country;
                $result['province'] = $defa_province;
                $result['city'] = $defa_city;
                $result['district'] = $defa_district;
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                //判断国家
                $where['enkey']=$country_en;
                $where['region_type']=0;
                $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                if(empty($region)){
                    $result['statuscode'] = 200;
                    $result['message'] = 'return default gis data.';
                    $result['country_id'] = $defa_country_id;
                    $result['province_id'] = $defa_province_id;
                    $result['city_id'] = $defa_city_id;
                    $result['district_id'] = $defa_district_id;
                    $result['country'] = $defa_country;
                    $result['province'] = $defa_province;
                    $result['city'] = $defa_city;
                    $result['district'] = $defa_district;
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }else{
                    $country=$region['region_name'];
                    $country_id=$region['region_id'];
                    //州信息
                    $where=array();
                    $where['enkey']=$province_en;
                    $where['region_type']=1;
                    $where['parent_id']=$country_id;
                    $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                    if(empty($region)){
                        $result['statuscode'] = 200;
                        $result['message'] = 'return default gis data.';
                        $result['country_id'] = $defa_country_id;
                        $result['province_id'] = $defa_province_id;
                        $result['city_id'] = $defa_city_id;
                        $result['district_id'] = $defa_district_id;
                        $result['country'] = $defa_country;
                        $result['province'] = $defa_province;
                        $result['city'] = $defa_city;
                        $result['district'] = $defa_district;
                        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                    }else{
                        $province=$region['region_name'];
                        $province_id=$region['region_id'];

                        //城市信息
                        $where=array();
                        $where['enkey']=$city_en;
                        $where['region_type']=2;
                        $where['parent_id']=$province_id;
                        $region=M('region')->field('region_id, region_name')->where($where)->limit(1)->find();
                        if(empty($region)){
                            $result['statuscode'] = 200;
                            $result['message'] = 'return default gis data.';
                            $result['country_id'] = $defa_country_id;
                            $result['province_id'] = $defa_province_id;
                            $result['city_id'] = $defa_city_id;
                            $result['district_id'] = $defa_district_id;
                            $result['country'] = $defa_country;
                            $result['province'] = $defa_province;
                            $result['city'] = $defa_city;
                            $result['district'] = $defa_district;
                            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                        }else{
                            $city=$region['region_name'];
                            $city_id=$region['region_id'];

                            $result['statuscode'] = 200;
                            $result['message'] = 'ok';
                            $result['country_id'] = $country_id;
                            $result['province_id'] = $province_id;
                            $result['city_id'] = $city_id;
                            $result['district_id'] = 0;
                            $result['country'] = $country;
                            $result['province'] = $province;
                            $result['city'] = $city;
                            $result['district'] = '';
                            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            }
        }else{
            $result['statuscode'] = 200;
            $result['message'] = 'return default gis data.';
            $result['country_id'] = $defa_country_id;
            $result['province_id'] = $defa_province_id;
            $result['city_id'] = $defa_city_id;
            $result['district_id'] = $defa_district_id;
            $result['country'] = $defa_country;
            $result['province'] = $defa_province;
            $result['city'] = $defa_city;
            $result['district'] = $defa_district;
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
    }

    //判断城市下是否有区一级信息
    public function Checkdistrict(){
        $result = array('statuscode' => 200, 'message' => '');
        $city_id = I('param.city_id/d',0,'strip_tags,htmlspecialchars');
        $district_id = I('param.district_id/d',0,'strip_tags,htmlspecialchars');

        //根据目前的规则，$city_id肯定>0，$district_id肯定等于0
        $where['region_id'] = $city_id;
        $city=M('region')->where($where)->limit(1)->getField('region_name');
        if(empty($city)){
            $result['statuscode'] = 301;
            $result['message'] = L('address-error-checkcityhasdistrict-null');
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        $where=array();
        $where['parent_id']=$city_id;
        $where['region_type']=3;
        $districtcount=M('region')->where($where)->count('region_id');
        $districtcount=intval($districtcount);
        if($districtcount>0){
            $result['statuscode'] = 300;
            $result['message'] = L('address-msg-checkcityhasdistrict',array('city'=>$city));
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $result['statuscode'] = 200;
            $result['message'] = 'OK';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
    }

    //获取用户地址列表
    public function Getuseraddress(){
        $result = array('statuscode' => 200, 'message' => '');

        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = L('global_error_user');
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        //加载列表
        $db = M('user_address');
        $where['user_id']=$user_id;
        $list = $db->fetchSql(false)
            ->field('address_id, consignee, country, province, city, district, address, zipcode, mobile')
            ->where($where)->order('address_id desc')->select();
        foreach($list as $key=>$vo){
            $is_default=0;
            $user_address_id=M('users')->where('user_id='.$user_id)->limit(1)->getField('address_id');
            if($user_address_id>0){
                if($user_address_id==$vo['address_id']){
                    $is_default=1;
                }
            }
            $list[$key]['is_default'] = $is_default;

            $arrconsignee = explode(' ',$vo['consignee']);
            $first_name = $arrconsignee[0];
            $last_name = $arrconsignee[1];
            $list[$key]['first_name'] = $first_name;
            $list[$key]['last_name'] = $last_name;

            $isok=true;
            if($vo['district']>0){
                $regionids=$vo['country'].','.$vo['province'].','.$vo['city'].','.$vo['district'];
            }else{
                $regionids=$vo['country'].','.$vo['province'].','.$vo['city'];

                // 判断是否需要完善地址
                $where=array();
                $where['parent_id']=$vo['city'];
                $where['region_type']=3;
                $districtcount=M('region')->where($where)->count('region_id');
                $districtcount=intval($districtcount);
                if($districtcount>0){
                    $isok=false;
                }
            }
            $region = M('region')->fetchSql(false)->field('region_id, region_name')
                ->where(array('region_id'=>array('in',''.$regionids.'')))->order("FIND_IN_SET(region_id,'".$regionids."')")->select();
            $list[$key]['country_name'] = $region[0]['region_name'];
            $list[$key]['province_name'] = $region[1]['region_name'];
            $list[$key]['city_name'] = $region[2]['region_name'];
            if($vo['district']>0){
                $list[$key]['district_name'] = $region[3]['region_name'];
            }else{
                $list[$key]['district_name'] = '';
            }
            $address = str_replace("\n","",$vo['address']);
            $address = str_replace("\r\n","",$address);
            $address = str_replace(chr(10),"",$address);
            $address = str_replace(chr(13),"",$address);
            $list[$key]['address']=$address;
            $list[$key]['mobile_show'] = '+7 '.$vo['mobile'];

            $list[$key]['isok']=$isok;
            $list[$key]['needmodify']=L('address-error-needmodify');
        }

        $addrcount = count($list);
        $message='Success';
        $statuscode=200;
        if($addrcount==0){
            $message=L('g-nodata');
            $statuscode=300;
        }

        $result['statuscode'] = $statuscode;
        $result['message'] = $message;
        $result['addrcount'] = $addrcount;
        $result['list'] = $list;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    //地址操作  读取  删除  默认
    public function Useraddressoption(){
        $result = array('statuscode' => 200, 'message' => '');

        $action = I('param.action/s','','strip_tags,htmlspecialchars');
        $user_id = I('param.user_id/d',0,'strip_tags,htmlspecialchars');
        $address_id = I('param.address_id/d',0,'strip_tags,htmlspecialchars');

        $timestamp = I('param.timestamp/d',0,'strip_tags,htmlspecialchars');
        $sign = I('param.sign/s','','strip_tags,htmlspecialchars');
        $allowaction=array('setdefault','deladdr','getaddritem','saveaddress');

        $first_name = I('param.first_name/s','','strip_tags,htmlspecialchars');
        $last_name = I('param.last_name/s','','strip_tags,htmlspecialchars');
        $country = I('param.country/d',0,'strip_tags,htmlspecialchars');
        $province = I('param.province/d',0,'strip_tags,htmlspecialchars');
        $city = I('param.city/d',0,'strip_tags,htmlspecialchars');
        $district = I('param.district/d',0,'strip_tags,htmlspecialchars');
        $address = I('param.address/s','','strip_tags,htmlspecialchars');
        $mobile = I('param.mobile/s','','strip_tags,htmlspecialchars');
        $zipcode = I('param.zipcode/s','','strip_tags,htmlspecialchars');
        $first_name=trim($first_name);
        $last_name=trim($last_name);
        $address=trim($address);
        $zipcode=trim($zipcode);


        if($action=='saveaddress'){
            if($address_id>0){
                $action='savemodify';
            }else{
                $action='saveadd';
            }
        }

        $hmackey=C('hmackey');
        if (function_exists('hash_hmac') and $user_id>0 and $timestamp>0 and $sign!='') {
            $checkstr=$timestamp.'-user-id-'.$user_id;
            $checksign = hash_hmac("sha1", $checkstr, $hmackey,false);
            if($checksign!=$sign){
                $checksign = hash_hmac("sha256", $checkstr, $hmackey,false);
                if($checksign!=$sign){
                    $result['statuscode'] = 300;
                    $result['message'] = L('global_error_api');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }

        if($user_id<=0){
            $result['statuscode'] = 300;
            $result['message'] = L('global_error_user');
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        if($action!='saveadd'){
            if($address_id<=0){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-datanull');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $where=array();
                $where['address_id']=$address_id;
                $where['user_id']=$user_id;
                $addressdata=M('user_address')->field('address_id, consignee, country, province, city, district, address, zipcode, mobile')->where($where)->limit(1)->find();
                if(empty($addressdata)){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-datanull');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }

                $is_default=0;
                $user_address_id=M('users')->where('user_id='.$user_id)->limit(1)->getField('address_id');
                if($user_address_id>0){
                    if($user_address_id==$address_id){
                        $is_default=1;
                    }
                }
                $addressdata['is_default'] = $is_default;
            }
        }

        if($action=='savemodify' or $action=='saveadd'){
            $address = str_replace("\r\n","",$address);
            $address = str_replace(chr(10),'',$address);
            $address = str_replace(chr(13),'',$address);
            $address = str_replace(chr(10).chr(10),'',$address);
            $address = str_replace(chr(13).chr(13),'',$address);

            if($first_name==''){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-firsterr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $arrfirst_name=explode(' ',$first_name);
                if(count($arrfirst_name)>1){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-firsterr');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }

            if($last_name==''){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-lasterr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $arrlast_name=explode(' ',$last_name);
                if(count($arrlast_name)>1){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-lasterr');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
            $consignee = $first_name.' '.$last_name;

            if(!is_mobile($mobile)){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-mobileerr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }

            if($address==''){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-addresserr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }

            if($country<=0 or $province<=0 or $city<=0){
                $result['statuscode'] = 300;
                $result['message'] = L('address-error-cityerr');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $where=array();
                $where['region_id'] = $city;
                $city_name=M('region')->where($where)->limit(1)->getField('region_name');
                if(empty($city_name)){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-cityerr');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }

                $where=array();
                $where['parent_id']=$city;
                $where['region_type']=3;
                $districtcount=M('region')->where($where)->count('region_id');
                $districtcount=intval($districtcount);
                if($districtcount>0 and $district==0){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-districterr',array('city'=>$city_name));
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }
            }
        }



        switch ($action){
            case 'setdefault':
                // ecs_ users user_id, address_id
                M('users')->where('user_id='.$user_id)->limit(1)->setField('address_id',$address_id);

                $result['statuscode'] = 200;
                $result['message'] = L('setdefault_address_success');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);

                break;
            case 'deladdr':
                //判断是不是默认地址，不允许删除
                if($addressdata['is_default']==1){
                    $result['statuscode'] = 300;
                    $result['message'] = L('address-error-notdel');
                    $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                }

                $where=array();
                $where['address_id']=$address_id;
                $where['user_id']=$user_id;
                M('user_address')->where($where)->delete();

                $result['statuscode'] = 200;
                $result['message'] = L('address-msg-delsuccess');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                break;
            case 'savemodify':
                $where=array();
                $data=array();
                $where['address_id'] = $address_id;
                $where['user_id'] = $user_id;

                $data['consignee'] = $consignee;
                $data['country'] = $country;
                $data['province'] = $province;
                $data['city'] = $city;
                $data['district'] = $district;
                $data['address'] = $address;
                $data['zipcode'] = $zipcode;
                $data['mobile'] = $mobile;
                $savemsg = M('user_address')->fetchSql(false)->where($where)->save($data);

                $result['statuscode'] = 200;
                $result['message'] = L('address-msg-savesuccess');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                break;
            case 'saveadd':
                $data=array();
                $data['address_id'] = $address_id;
                $data['address_name'] = '';
                $data['user_id'] = $user_id;
                $data['consignee'] = $consignee;
                $data['email'] = '';
                $data['country'] = $country;
                $data['province'] = $province;
                $data['city'] = $city;
                $data['district'] = $district;
                $data['address'] = $address;
                $data['zipcode'] = $zipcode;
                $data['tel'] = '';
                $data['mobile'] = $mobile;
                $data['sign_building'] = '';
                $data['best_time'] = '';
                M('user_address')->add($data);

                $result['statuscode'] = 200;
                $result['message'] = L('address-msg-savesuccess');
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
                break;
        }

        /*
         * $action='savemodify';
            }else{
                $action='saveadd';
         */
        // ecs_ user_address address_id, address_name, user_id, consignee, email, country, province, city, district, address, zipcode, tel, mobile, sign_building, best_time





    }

    //获取分类地址
    public function Getclassdata(){
        $result = array('statuscode' => 200, 'message' => '');
        $parent_id = I('param.parent_id/d',0,'strip_tags,htmlspecialchars');

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        $where['parent_id']=$parent_id;
        $where['is_show']=1;
        $list=M('category')->field('cat_id, cat_name, cat_img')->where($where)->order('sort_order asc,cat_id asc')->select();
        foreach ($list as $key=>$vo) {
            $cat_img= $vo['cat_img'];
            if($cat_img!=''){
                $cat_img = $rooturl.$cat_img;
            }
            $list[$key]['cat_img'] = $cat_img;
        }

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['list'] = $list;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);


        // ecs_ category cat_id, cat_name, parent_id, sort_order, is_show, cat_img



    }

    //获取所有分类地址
    public function Getallclassdata(){
        $result = array('statuscode' => 200, 'message' => '');

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        $where['parent_id']=0;
        $where['is_show']=1;
        $list=M('category')->field('cat_id, cat_name, cat_img')->where($where)->order('sort_order asc,cat_id asc')->select();
        foreach ($list as $key=>$vo) {
            $cat_img= $vo['cat_img'];
            if($cat_img!=''){
                $cat_img = $rooturl.$cat_img;
            }
            $list[$key]['cat_img'] = $cat_img;

            $subwhere=array();
            $subwhere['parent_id']=$vo['cat_id'];
            $subwhere['is_show']=1;
            $sublist=M('category')->field('cat_id, cat_name, cat_img')->where($subwhere)->order('sort_order asc,cat_id asc')->select();
            foreach ($sublist as $kk=>$vv) {
                $cat_img= $vv['cat_img'];
                if($cat_img!=''){
                    $cat_img = $rooturl.$cat_img;
                }
                $sublist[$kk]['cat_img'] = $cat_img;
            }
            $list[$key]['sub_cat_id'] = $sublist;
        }

        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['list'] = $list;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);


        // ecs_ category cat_id, cat_name, parent_id, sort_order, is_show, cat_img


        /*
         *     "categories":[
        {
            "id":"1634",
            "name":"Кухонная техника",
            "url":"category.php?id=1634",
            "img":"",
            "cat_img":"https://srcshop.029vip.cn:443/data/catcat_img/1599614546492144397.png",
            "cat_id":[
                {
                    "id":"1641",
                    "name":"Кофемашина",
                    "img":"",
                    "url":"category.php?id=1641",
                    "cat_img":"https://srcshop.029vip.cn:443/data/catcat_img/1598594648157653075.jpg",
                    "cat_id":[
                        {
         */

    }

    //Ajax发送
    public function Sendsms(){
        $result = array('statuscode' => 200, 'message' => '');
        $mobile = I('param.mobile/s','','strip_tags,htmlspecialchars');
        $verifycode = I('param.verifycode/s','','strip_tags,htmlspecialchars');
        $snum = I('param.snum/s','','strip_tags,htmlspecialchars');
        $fn = I('param.fn/s','','strip_tags,htmlspecialchars');

        $mobile=str_replace(' ','',$mobile);
        $mobile=str_replace('-','',$mobile);
        $mobile=str_replace('(','',$mobile);
        $mobile=str_replace(')','',$mobile);

        if(!is_mobile($mobile)){
            $result['statuscode'] = 300;
            $result['message'] = 'Failed to send [mobile error]!';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }else{
            $mobile='7'.$mobile;
//            $mobile = $mobile;
        }
        if($snum==''){
            $result['statuscode'] = 300;
            $result['message'] = 'Failed to send [template error]!';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }

        $smsc_login='srcshop';
        $smsc_password='511243';
        $smsc_charset = 'windows-1251';
        $smsc_smssign = '[SRCSHOP]';
//        $sendurl = 'https://smsc.kz/sys/send.php?login={$smsc_login}&psw={$smsc_password}&fmt=3&charset={$smsc_charset}&phones={$mobile}&mes={$smscontent}';
        $sendurl = 'https://smsc.kz/sys/send.php';
        $where=array();
        $where['sunm'] = $snum;
        $smscontent = M('smstemplate')->where($where)->limit(1)->getField('content');
        if(empty($smscontent)){
            $result['statuscode'] = 300;
            $result['message'] = 'Failed to send [template error]!';
            $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
        }
        $sendparams=array();
        switch ($fn){
            case 'mobileverify':
                $mobileverify = rand_string(4,1);
                $sendparams['param1']=$mobileverify;
                break;
        }

        if(stripos($smscontent,'{1}')===false){
            $smscontent=$smscontent;
        }else{
            if(empty($sendparams)){
                $result['statuscode'] = 300;
                $result['message'] = 'Failed to send [params error]!';
                $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
            }else{
                $smscontent=str_replace('{1}',$sendparams['param1'],$smscontent);
                $smscontent=str_replace('{2}',$sendparams['param2'],$smscontent);
                $smscontent=str_replace('{3}',$sendparams['param3'],$smscontent);
                $smscontent=str_replace('{4}',$sendparams['param4'],$smscontent);
                $smscontent=str_replace('{5}',$sendparams['param5'],$smscontent);
            }
        }

        $sendparams=array(
            'login'=>$smsc_login,
            'psw'=>$smsc_password,
            'fmt'=>3,
//            'charset'=>$smsc_charset,
            'phones'=>$mobile,
            'mes'=>$smscontent
        );

        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $sendurl );
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array("Expect:")
        ));
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //如果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $sendparams);
        curl_setopt( $ch, CURLOPT_TIMEOUT,60);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result['statuscode'] = 300;
            $result['message'] = 'Failed to send [server error]!';
        } else{
            $responsedata=$ret;
            $sendresult = json_decode($ret,true);
            if($sendresult['id']>0){
                $data=array();
                $data['mobile'] = $mobile;
                $data['smscontent'] = $smscontent;
                $data['responsedata'] = $responsedata;
                $data['updatetime'] = time();
                $data['status'] = 1;
                $id = M('smslog')->add($data);

                $result['statuscode'] = 200;
                $result['message'] = 'Sent successfully!';
                $result['logid'] = $id;
                if($fn=='mobileverify'){
                    var_dump(I('session.verify_'.$mobile.'_verifycode'));die;
                }
            }else{
                $data=array();
                $data['mobile'] = $mobile;
                $data['smscontent'] = $smscontent;
                $data['responsedata'] = $responsedata;
                $data['updatetime'] = time();
                $data['status'] = 0;
                $savemsg = M('smslog')->add($data);

                $result['statuscode'] = 300;
                $result['message'] = 'Failed to send!';
            }
        }
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

    /**
     * Notes:
     * User: src
     * DateTime: 2021/2/3 11:54
     */
    public function getSearchKeyword ()
    {
        $result = array('statuscode' => 200, 'message' => '');

        $where['type'] = 'system_config';
        $res = M('setting')->where($where)->getField('value');
        $res = unserialize($res);
        $keywords = explode(',', $res['base']['base_search_keyword']);

        $result['message'] = 'OK';
        $result['data'] = $keywords;
        $this->ajaxReturn($result,'JSON',JSON_UNESCAPED_UNICODE);
    }

}