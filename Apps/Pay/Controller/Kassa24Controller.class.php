<?php

namespace Pay\Controller;


use Pay\Controller\PayBaseController;
use Think\Log;

class Kassa24Controller extends PayBaseController
{
    public function _initialize() {
        parent:: _initialize();
    }

    /*js支付*/
    public function Payorder(){
        $order_sn = I('param.order_sn/s','','strip_tags,htmlspecialchars');
        if($order_sn==''){
            $this->error(L('pay-err-base'),'',3);
        }else{
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                $this->error(L('pay-err-order'),'',3);
            }
            if($orderdata['pay_status']!=0){
                $this->error(L('pay-err-ispaid'),'',3);
            }
            $total_amount=$orderdata['goods_amount']+$orderdata['shipping_fee'];
            $order_id = $orderdata['order_id'];
            $user_id = $orderdata['user_id'];

            $ordergoods=M('order_goods')
                ->field('ecs_order_goods.rec_id, ecs_order_goods.goods_id, ecs_order_goods.goods_name, ecs_order_goods.goods_sn, ecs_order_goods.product_id, ecs_order_goods.goods_number, ecs_order_goods.goods_price, ecs_order_goods.split_money, ecs_order_goods.goods_attr, ecs_order_goods.goods_attr_id, ecs_goods.goods_thumb, ecs_goods.cang_id')
                ->join('LEFT JOIN ecs_goods ON ecs_goods.goods_id = ecs_order_goods.goods_id')
                ->where('ecs_order_goods.order_id='.$order_id)->limit(1)->find();
            $qty=$ordergoods['goods_number'];
            $goods_sn = $ordergoods['goods_sn'];
            $goods_name = $ordergoods['goods_name'];
            $goods_price = $ordergoods['goods_price'];
        }

        $ks_gatewayurl=C('ks_gatewayurl');
        $ks_merchantid=C('ks_merchantid');
        $ks_login=C('ks_login');
        $ks_pass=C('ks_pass');
        $ks_demo=C('ks_demo');
        $ks_notify_url=C('ks_notify_url');
        $ks_return_url=C('ks_return_url');

        $metadata=array(
            'order_sn'=>$order_sn,
            'user_id'=>$user_id
        );

        $post_order_sn=$order_sn.'-'.date('dHis');

        $postdata=array(
            'merchantId'=>      $ks_merchantid,
            'orderId'   =>      $post_order_sn,
            'amount'  =>        (int)$total_amount*100,
            'description'=>     'SRCSHOP:'.$order_sn,
            'demo'      =>      $ks_demo,
            'callbackUrl'=>     $ks_notify_url,
            'returnUrl' =>      $ks_return_url,
            'metadata'=>        $metadata
        );
        $postdata = json_encode ($postdata, JSON_UNESCAPED_UNICODE);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($ks_login.':'.$ks_pass),
            'Content-Length: ' . strlen($postdata)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $ks_gatewayurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');

        $output = curl_exec($ch);
        curl_close($ch);

        $output=json_decode($output,true);
        $pay_trade_no = $output['id'];
        $payment_page_url = $output['url'];

        //更新订单信息
        $where=array();
        $where['order_sn']=$order_sn;
        $data['pay_trade_no']=$pay_trade_no;
        $data['pay_method']='kassa24';
        $data['real_pay_money']=$total_amount;
        M('order_info')->where($where)->save($data);


//        redirect($payment_page_url,0,'');
        $result['message'] = 'OK';
        $result['statuscode'] = 200;
        $result['url'] = $payment_page_url;
        $this->ajaxReturn($result);

    }

    //支付返回
    public function Notify(){
        //接收返回数据
        $putdata = file_get_contents('php://input');
        $returndata = json_decode($putdata, true);

        $status= $returndata['status'];
        $order_sn= $returndata['metadata']['order_sn'];
        $total_fee= $returndata['amount']/100;


        \Think\Log::write('==============123456790=============================');
        Log::write(json_encode($returndata));

        //判断基本状态
        if ($status == 1){
            \Think\Log::write('【'.$order_sn.'】支付数据返回成功','KASSA24');
            //读取订单数据
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                echo 'fail';
                \Think\Log::write('【'.$order_sn.'】支付失败：订单数据不存在','KASSA24');
            }else{
                if($orderdata['pay_status']!=0){
                    if($orderdata['pay_status']==1){
                        echo 'fail';
                        \Think\Log::write('【'.$order_sn.'】支付失败：该订单为货到付款，无需支付，请检查数据','KASSA24');
                    }elseif($orderdata['pay_status']==2){
                        echo 'fail';
                        \Think\Log::write('【'.$order_sn.'】支付失败：该订单已支付，无需重复支付','KASSA24');
                    }
                }else{
                    $order_id = $orderdata['order_id'];
                    $user_id = $orderdata['user_id'];
                    $mobile = $orderdata['mobile'];
                    $add_time = $orderdata['add_time'];
                    $maxday = $orderdata['maxday'];

                    //读取匹配支付记录
                    $paydata=M('pay_log')->field('log_id, order_id, order_amount, order_type, is_paid')->where('order_id='.$order_id)->limit(1)->find();
                    if($paydata){
                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：支付记录存在，继续','KASSA24');
                        if($paydata['is_paid']==0){
                            \Think\Log::write('【'.$order_sn.'】支付成功业务流：支付记录为待支付，继续','KASSA24');
                            switch ($paydata['order_type']){
                                case 0:
                                    $mobile_phone = M('users')->where('user_id='.$user_id)->limit(1)->getField('mobile_phone');
                                    //判断订单金额
                                    $total_amount=$orderdata['goods_amount']+$orderdata['shipping_fee'];
                                    $order_amount=$orderdata['order_amount'];

                                    if($total_fee==$order_amount){
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：金额匹配，继续','KASSA24');
                                        //更新订单主表状态
                                        $time = time();
                                        $data=array();
                                        $data['order_status']=1;
                                        $data['confirm_time']=$time;
                                        $data['pay_status']=2;
                                        $data['pay_time']=$time;
                                        $data['money_paid']=$order_amount;
                                        $data['order_amount']=0;
                                        M('order_info')->where('order_id='.$order_id)->save($data);
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单状态，继续','KASSA24');

                                        //更新订单支付数据表
                                        M('pay_log')->where('order_id='.$order_id)->setField('is_paid',1);
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单关联支付数据状态，继续','KASSA24');

                                        //更新fan佣金表
                                        M('yeji')->where('order_id='.$order_id)->setField('pay_time',$time);
                                        M('fan_goods')->where('order_id='.$order_id)->setField('is_pay',1);
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新佣金表，继续','KASSA24');

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
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单日志，继续','KASSA24');

                                        \Think\Log::write('【'.$order_sn.'】支付成功','KASSA24');
                                        header( 'HTTP/1.1 200 OK' );
                                        echo '{"accepted":true}';
                                    }else{
                                        echo 'fail';
                                        \Think\Log::write('【'.$order_sn.'】支付失败：订单金额与支付金额不匹配','KASSA24');
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
                        \Think\Log::write('【'.$order_sn.'】支付失败：订单匹配支付日志不存在','KASSA24');
                    }
                }
            }
        }else{
            \Think\Log::write('【'.$order_sn.'】支付失败：返回支付失败','KASSA24');
            echo 'fail';
        }

    }
}