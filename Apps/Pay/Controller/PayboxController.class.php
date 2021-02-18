<?php
namespace Pay\Controller;
use Think\Log;

class PayboxController extends PayBaseController {
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
        
        /*$pb_gatewayurl=C('pb_gatewayurl');
        $pb_merchantid=C('pb_merchantid');
        $pb_secureKey=C('pb_secureKey');
        $pb_currencytype=C('pb_currencytype');
        $pb_paymentsystem=C('pb_paymentsystem');
        $pb_language=C('pb_language');
        $pb_result_url=C('pb_result_url');
        $pb_success_url=C('pb_success_url');
        $pb_failure_url=C('pb_failure_url');
        $pb_check_url=C('pb_check_url');
        $pb_cancel_url=C('pb_cancel_url');
        $pb_back_url=C('pb_back_url');
        $pb_capture_url=C('pb_capture_url');
        $expires_at = date('Y-m-d 23:59:59',strtotime('+1 day'));
        $ip = get_client_ip();

        $postdata=array(
            'order'=>$order_sn,
            'amount'=>$total_amount,
            'refund_amount'=>0,
            'currency'=>$pb_currencytype,
            // 'test'=>1,
            'description'=>'SRCSHOP:'.$order_sn,
            'payment_system'=>$pb_paymentsystem,
            'cleared'=>true,
            'expires_at'=>$expires_at,
            'language'=>$pb_language,
            'param1'=>$goods_name,
            'options'=>array(
                'callbacks'=>array(
                    'result_url'=>$pb_result_url,
                    'check_url'=>$pb_check_url,
                    'cancel_url'=>$pb_cancel_url,
                    'success_url'=>$pb_success_url,
                    'failure_url'=>$pb_failure_url,
                    'back_url'=>$pb_back_url,
                    'capture_url'=>$pb_capture_url
                ),
                'custom_params'=>array(
                ),
                'user'=>array(
                    'id'=>$user_id,
                    'email'=>'sales@src.kz',
                    'phone'=>'+7'.$orderdata['mobile'],
                    'ip'=>$ip
                ),
//        'card'=>array(
//            'id'=>''
//        )
            )
        );
        $postdata=json_encode($postdata,JSON_UNESCAPED_UNICODE);
//        print_r($postdata);
        $headers=array();
        $headers[] = 'Authorization: Basic '.base64_encode($pb_merchantid.':'.$pb_secureKey).';';
        $headers[] = 'X-Idempotency-Key: 332a6272-5328-4af7-8f97-'.md5($order_sn).';';
        $headers[] = 'Content-Type:application/json;';*/


        $request = [
            'pg_merchant_id'    => C('pg_merchant_id'),
            'pg_order_id'       => $order_sn,
            'pg_amount'         => $total_amount,
            'pg_description'    => 'SRCSHOP:' . $order_sn,
            'pg_testing_mode'   => C('pg_testing_mode'),
            'pg_salt'           => C('pg_salt'),
            'pg_result_url'     => C('pg_result_url'),
            'pg_success_url'    => C('pg_success_url'),
            'pg_failure_url'    => C('pg_failure_url'),
            'user_id'           => $user_id
        ];

        ksort($request);
        array_unshift ($request, 'init_payment.php');
        array_push($request, C('pg_secure_key'));

        $request['pg_sig'] = md5(implode(';', $request));

        unset($request[0], $request[1]);

        $pb_gatewayurl = C('pg_gateway_url');

        $postdata = json_encode($request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $pb_gatewayurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postdata)
        ));

        $output = curl_exec($ch);
        curl_close($ch);
        /*$output=json_decode($output,true);
        $pay_trade_no = $output['id'];
        $payment_page_url = $output['payment_page_url'];*/

        $response = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA);
        $jsonStr = json_encode($response);
        $jsonArray = json_decode($jsonStr,true);

        if ($jsonArray['pg_status'] == 'ok') {
            $pay_trade_no = $jsonArray['pg_payment_id'];
            $payment_page_url = $jsonArray['pg_redirect_url'];

            //更新订单信息
            $where=array();
            $where['order_sn']=$order_sn;
            $data['pay_trade_no']=$pay_trade_no;
            $data['pay_method']='paybox';
            $data['real_pay_money']=$total_amount;
            M('order_info')->where($where)->save($data);

            $result['message'] = 'OK';
            $result['statuscode'] = 200;
            $result['url'] = $payment_page_url;
            $this->ajaxReturn($result);
        } else {
            $this->error(L('pay-err-base'),'',3);
        }
    }

    //支付返回
    public function Notify(){
        //接收返回数据
        $putdata = $GLOBALS['HTTP_RAW_POST_DATA'];
        if ($putdata) {
            $returndata = json_decode($putdata, true);
            $val = var_export($returndata, true);
//            \Think\Log::write('Paybox返回数据：'.$val,'PAYBOX');
        }else{
            $putdata = file_get_contents("php://input");
            parse_str($putdata , $returndata);
        }
        /*$status= $returndata['status']['code'];
        $order_sn= $returndata['order'];
        $total_fee= $returndata['amount'];
        $pay_user_id=$returndata['options']['user']['id'];*/

        $status = $returndata['pg_result'];
        $order_sn = $returndata['pg_order_id'];
        $total_fee = $returndata['pg_amount'];
        $pay_user_id = $returndata['user_id'];

        //判断基本状态
        if ($status == '1'){
            \Think\Log::write('【'.$order_sn.'】支付数据返回成功','PAYBOX');
            //读取订单数据
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                echo 'fail';
                \Think\Log::write('【'.$order_sn.'】支付失败：订单数据不存在','PAYBOX');
            }else{
                if($orderdata['pay_status']!=0){
                    if($orderdata['pay_status']==1){
                        echo 'fail';
                        \Think\Log::write('【'.$order_sn.'】支付失败：该订单为货到付款，无需支付，请检查数据','PAYBOX');
                    }elseif($orderdata['pay_status']==2){
                        echo 'fail';
                        \Think\Log::write('【'.$order_sn.'】支付失败：该订单已支付，无需重复支付','PAYBOX');
                    }
                }else{
                    $order_id = $orderdata['order_id'];
                    $user_id = $orderdata['user_id'];
                    $mobile = $orderdata['mobile'];
                    $add_time = $orderdata['add_time'];
                    $maxday = $orderdata['maxday'];
                    if($pay_user_id!=$user_id){
                        echo 'fail';
                        \Think\Log::write('【'.$order_sn.'】支付失败：订单与支付绑定的USER不匹配','PAYBOX');
                        exit;
                    }
                    //读取匹配支付记录
                    $paydata=M('pay_log')->field('log_id, order_id, order_amount, order_type, is_paid')->where('order_id='.$order_id)->limit(1)->find();
                    if($paydata){
                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：支付记录存在，继续','PAYBOX');
                        if($paydata['is_paid']==0){
                            \Think\Log::write('【'.$order_sn.'】支付成功业务流：支付记录为待支付，继续','PAYBOX');
                            switch ($paydata['order_type']){
                                case 0:
                                    $mobile_phone = M('users')->where('user_id='.$user_id)->limit(1)->getField('mobile_phone');
                                    //判断订单金额
                                    $total_amount=$orderdata['goods_amount']+$orderdata['shipping_fee'];
                                    $order_amount=$orderdata['order_amount'];

                                    if($total_fee==$order_amount){
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：金额匹配，继续','PAYBOX');
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
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单状态，继续','PAYBOX');

                                        //更新订单支付数据表
                                        M('pay_log')->where('order_id='.$order_id)->setField('is_paid',1);
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单关联支付数据状态，继续','PAYBOX');

                                        //更新fan佣金表
                                        M('yeji')->where('order_id='.$order_id)->setField('pay_time',$time);
                                        M('fan_goods')->where('order_id='.$order_id)->setField('is_pay',1);
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新佣金表，继续','PAYBOX');

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
                                        \Think\Log::write('【'.$order_sn.'】支付成功业务流：更新订单日志，继续','PAYBOX');

                                        \Think\Log::write('【'.$order_sn.'】支付成功','PAYBOX');
                                        echo 'success';
                                    }else{
                                        echo 'fail';
                                        \Think\Log::write('【'.$order_sn.'】支付失败：订单金额与支付金额不匹配','PAYBOX');
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
                        \Think\Log::write('【'.$order_sn.'】支付失败：订单匹配支付日志不存在','PAYBOX');
                    }
                }
            }
        }else{
            \Think\Log::write('【'.$order_sn.'】支付失败：返回支付失败','PAYBOX');
            echo 'fail';
        }

    }

    //相关返回
    public function Checkurl(){
        \Think\Log::write('访问了Checkurl','PAYBOX');
    }
    public function Cancelurl(){
        \Think\Log::write('访问了Cancelurl','PAYBOX');
    }
    public function Backurl(){
        \Think\Log::write('访问了Backurl','PAYBOX');
    }
    public function Captureurl(){
        \Think\Log::write('访问了Captureurl','PAYBOX');
    }

}