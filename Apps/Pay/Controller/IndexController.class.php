<?php
namespace Pay\Controller;
class IndexController extends PayBaseController {
    public function _initialize() {
        parent:: _initialize();
    }

    /*js支付*/
    public function Payorder(){
        $order_sn = I('param.o/s','','strip_tags,htmlspecialchars');
        if($order_sn==''){
            $this->error(L('pay-err-base'),'',3);
        }else{
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                $this->error(L('pay-err-order'),'',3);
            }
            if($orderdata['pay_status']!=0){
                $this->error(L('pay-err-ispaid'),'',3);
            }
            $total_amount=$orderdata['goods_amount']+$orderdata['shipping_fee'];
            $order_id = $orderdata['order_id'];


            $ordergoods=M('order_goods')
                ->field('ecs_order_goods.rec_id, ecs_order_goods.goods_id, ecs_order_goods.goods_name, ecs_order_goods.goods_sn, ecs_order_goods.product_id, ecs_order_goods.goods_number, ecs_order_goods.goods_price, ecs_order_goods.split_money, ecs_order_goods.goods_attr, ecs_order_goods.goods_attr_id, ecs_goods.goods_thumb, ecs_goods.cang_id')
                ->join('LEFT JOIN ecs_goods ON ecs_goods.goods_id = ecs_order_goods.goods_id')
                ->where('ecs_order_goods.order_id='.$order_id)->limit(1)->find();
            $qty=$ordergoods['goods_number'];
            $goods_sn = $ordergoods['goods_sn'];
            $goods_name = $ordergoods['goods_name'];
            $goods_price = $ordergoods['goods_price'];


            //读取汇率
            $config=M('setting')->where("type='system_config'")->limit(1)->getField('value');
            $config=unserialize($config);
            $dollar_huilv = $config['huilv_config']['dollar_huilv'];
            $dollar_huilv=0.0023757;
            $pay_amount_usd = round($total_amount*$dollar_huilv,2);
        }
        
        $paymerchantid=C('TL_paymerchantid');
        $SecureKey=C('TL_SecureKey');
        $CurrencyType=C('TL_CurrencyType');
        $NotifyUrl=C('TL_NotifyUrl');
        $ReturnUrl=C('TL_ReturnUrl');
        $Language=C('TL_Language');
        $PayPageStyle=C('TL_PayPageStyle');
        $gatewayUrl=C('TL_gatewayUrl');

        $this->assign('paymerchantid',$paymerchantid);
        $this->assign('CurrencyType',$CurrencyType);
        $this->assign('NotifyUrl',$NotifyUrl);
        $this->assign('ReturnUrl',$ReturnUrl);
        $this->assign('Language',$Language);
        $this->assign('PayPageStyle',$PayPageStyle);
        $this->assign('gatewayUrl',$gatewayUrl);

        $email = $order_sn.'@srcshop.kz';
        $this->assign('order_sn',$order_sn);
        $this->assign('amount',$pay_amount_usd);
        $this->assign('email',$email);

        $this->assign('sku',$goods_sn);
        $this->assign('productname',$goods_name);
        $this->assign('price',$goods_price);
        $this->assign('qty',$qty);

        $this->assign('ShippingFirstName',$orderdata['consignee']);
        $this->assign('ShippingLastName',$orderdata['consignee']);
        $this->assign('ShippingAddress1',$orderdata['address']);
        $this->assign('ShippingAddress2',$orderdata['address']);
        $this->assign('ShippingCity',$orderdata['city']);
        $this->assign('ShippingCountry',$orderdata['country']);
        $this->assign('ShippingState',$orderdata['province']);
        $this->assign('ShippingZipcode','000000');
        $this->assign('ShippingTelephone',$orderdata['mobile']);

        $this->assign('BillingFirstName',$orderdata['consignee']);
        $this->assign('BillingLastName',$orderdata['consignee']);
        $this->assign('BillingAddress1',$orderdata['address']);
        $this->assign('BillingAddress2',$orderdata['address']);
        $this->assign('BillingCity',$orderdata['city']);
        $this->assign('BillingCountry',$orderdata['country']);
        $this->assign('BillingState',$orderdata['province']);
        $this->assign('BillingZipcode','000000');
        $this->assign('BillingTelephone',$orderdata['mobile']);

        $Signature =md5($paymerchantid.$order_sn.$email.$CurrencyType.$pay_amount_usd.$SecureKey);
        $this->assign('Signature',$Signature);

        $pagetitle = L('pay-ing');
        $this->assign('pagetitle',$pagetitle);
        $this->display();
    }

    //支付返回
    public function Notify(){
        //接收返回数据
        $putdata = file_get_contents("php://input");
        parse_str($putdata , $returndata);
        $order_sn= $returndata['OrderId'];
        $pay_trade_no= $returndata['TransactionId'];
        $status= $returndata['Status'];
        $code= $returndata['ReasonCode'];
        $currencytype= $returndata['CurrencyType'];
        $total_fee= $returndata['Amount'];
        $total_fee = str_replace(',','.',$total_fee);
        $pay_method = 'tonglian['.$currencytype.']';
        \Think\Log::write('通联支付返回数据','PAY');
        //判断基本状态
        if ($status == 'Success'  and $code == 'Success'){
            \Think\Log::write('支付成功','PAY');
            //读取订单数据
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                echo 'fail';
                \Think\Log::write('支付失败：订单数据不存在','PAY');
            }else{
                if($orderdata['pay_status']!=0){
                    echo 'fail';
                    \Think\Log::write('支付失败：订单状态不正确','PAY');
                }else{
                    $order_id = $orderdata['order_id'];
                    //读取匹配支付记录
                    $paydata=M('pay_log')->field('log_id, order_id, order_amount, order_type, is_paid')->where('order_id='.$order_id)->limit(1)->find();
                    if($paydata){
                        \Think\Log::write('支付失败：无关联支付记录','PAY');
                        if($paydata['is_paid']==0){
                            \Think\Log::write('进入支付判断','PAY');
                            switch ($paydata['order_type']){
                                case 0:
                                    $user_id = $orderdata['user_id'];
                                    $mobile = $orderdata['mobile'];
                                    $add_time = $orderdata['add_time'];
                                    $maxday = $orderdata['maxday'];
                                    $mobile_phone = M('users')->where('user_id='.$user_id)->limit(1)->getField('mobile_phone');
                                    //判断订单金额
                                    $total_amount=$orderdata['goods_amount']+$orderdata['shipping_fee'];
                                    $order_amount=$orderdata['order_amount'];
                                    //读取汇率
                                    $config=M('setting')->where("type='system_config'")->limit(1)->getField('value');
                                    $config=unserialize($config);
                                    $dollar_huilv = $config['huilv_config']['dollar_huilv'];
                                    $dollar_huilv=0.0023757;
                                    $pay_amount_usd = round($total_amount*$dollar_huilv,2);
                                    if(abs($pay_amount_usd-$total_fee)<0.05){
                                        \Think\Log::write('支付判断：金额误差允许','PAY');
                                        //更新订单主表状态
                                        $time = gmtime();
                                        $data=array();
                                        $data['order_status']=1;
                                        $data['confirm_time']=$time;
                                        $data['pay_status']=2;
                                        $data['pay_time']=$time;
                                        $data['money_paid']=$order_amount;
                                        $data['order_amount']=0;
                                        $data['pay_trade_no']=$pay_trade_no;
                                        $data['pay_method']=$pay_method;
                                        $data['real_pay_money']=$total_fee;
                                        M('order_info')->where('order_id='.$order_id)->save($data);
                                        \Think\Log::write('更新订单信息为已支付','PAY');

                                        //更新订单支付数据表
                                        M('pay_log')->where('order_id='.$order_id)->setField('is_paid',1);
                                        \Think\Log::write('更新支付记录为已支付','PAY');

                                        //更新fan佣金表
                                        M('yeji')->where('order_id='.$order_id)->setField('pay_time',$time);
                                        M('fan_goods')->where('order_id='.$order_id)->setField('is_pay',1);
                                        \Think\Log::write('更新佣金表','PAY');

                                        //插入操作日志
                                        $data=array();
                                        $data['order_id'] = $order_id;
                                        $data['action_user'] = $mobile_phone;
                                        $data['order_status'] = 1;
                                        $data['shipping_status'] = 0;
                                        $data['pay_status'] = 2;
                                        $data['action_place'] = 0;
                                        $data['action_note'] = L('pay-actioninfo');
                                        $data['log_time'] = $time;
                                        M('order_action')->add($data);
                                        \Think\Log::write('更新订单日志','PAY');

                                        //发送短信
                                        $longurl=base_url().U('/Mobile/Index/Orderresult').'?o='.$order_sn;
                                        // $posturl='https://goo.su/api/convert';
                                        // $postdata=array(
                                        //     'url' => $longurl,
                                        //     'token' => 'mzBjyuI2ntSgAwFcqRO2UNHRdzzbFH46TWFvVMQShGNrtym1KhbTs5iEpBz9'
                                        // );
                                        // $shorturldata = https_post($posturl,$postdata);
                                        // $shorturldata=json_decode($shorturldata,true);
                                        // if($shorturldata['succes']){
                                        //     $shorturl=$shorturldata['short_url'];
                                        // }else{
                                        //     $shorturl = $longurl;
                                        // }
                                        // \Think\Log::write('生成短链接='.$shorturl,'SMS');
                                        $shorturl = S('shorturl');
                                        if(empty($shorturl) or $shorturl==''){
                                            $shorturl=$longurl;
                                        }
                                        $recvdate=strtotime("+$maxday day",$add_time);
                                        $recvdate = date('mdY',$recvdate);
                                        $snum = 'd52e8sr9';
                                        $Sms = A('Api/Kzsms');
                                        $sendresult = $Sms->Send('+7'.$mobile,$snum,array('param1'=>$order_sn,'param2'=>$recvdate,'param3'=>$shorturl,'param4'=>'+7 700 1138433'));
                                        \Think\Log::write('发送短信数据：'.var_export($sendresult,true),'PAY');
                                        echo 'success';
                                    }else{
                                        echo 'fail';
                                        \Think\Log::write('支付判断：金额误差超额，支付失败','PAY');
                                    }
                                    break;
                                case 1:
                                    break;
                                case 3:
                                    break;
                                case 7:
                                    break;
                                default:
                                    echo 'fail';
                                    break;
                            }
                        }else{
                            echo 'fail';
                        }
                    }else{
                        echo 'fail';
                        \Think\Log::write('支付失败','PAY');
                    }
                }
            }
        }else{
            \Think\Log::write('支付失败1','PAY');
            echo 'fail';
        }

    }

    //订单检测
    public function CheckPayResult(){
        $result = array('statuscode' => 200, 'message' => '');
        $orderid = I('param.orderid/d',0);

        if(!UID){
            $result['statuscode'] = 999;
            $result['message'] = '请登录';
        }else{
            $uid = UID;
        }

        //paylog id, pay_id, paynum, orderid, amount, trade_no, updatetime, status
        $db=M('paylog');
        $where['orderid']=$orderid;
        $paydata = $db->field('trade_no, status')->where($where)->limit(1)->find();

        if($paydata['trade_no']=='' or $paydata['status']==0){
            $comeurl = U('/Home');
            $result['statuscode'] = 300;
            $result['message'] = '尚未支付成功';
            $result['comeurl'] = urldecode($comeurl);
        }else{
            $comeurl = U('/User/Myorder/Detail').'?orderid='.$orderid;
            $result['statuscode'] = 200;
            $result['message'] = '支付成功！';
            $result['comeurl'] = urldecode($comeurl);
        }
        $this->ajaxReturn($result);
    }


}