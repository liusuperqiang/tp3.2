<?php
namespace Pay\Controller;
class EpayController extends PayBaseController {
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

        }

        $merchant_certificate_id=C('epay_MERCHANT_CERTIFICATE_ID');
        $merchant_name=C('epay_MERCHANT_NAME');
        $private_key_fn=C('epay_PRIVATE_KEY_FN');
        $private_key_pass=C('epay_PRIVATE_KEY_PASS');
        $public_key_fn=C('epay_PUBLIC_KEY_FN');
        $merchant_id=C('epay_MERCHANT_ID');
        $language=C('epay_Language');
        $template=C('epay_template');
        $card_name=C('epay_card_name');
        $currency=C('epay_currency');
        $backlink=C('epay_BackLink');
        $postlink=C('epay_PostLink');
        $failurebacklink=C('epay_FailureBackLink');
        $gatewayurl=C('epay_gatewayUrl');

        $this->assign('gatewayurl',$gatewayurl);

        $epaytool =new \My\Epaytool($private_key_fn,$private_key_pass);

        $orderdata=array(
            'order_id'=>$order_sn,
            'amount'=>$total_amount,
            'email'=>'',
            'Language'=>$language,
            'BackLink'=>$backlink,
            'PostLink'=>$postlink,
            'FailureBackLink'=>$failurebacklink,
            'template'=>$template,
            'card_name'=>$card_name,
        );

        $merchant=<<<merchantdata
<merchant cert_id="{$merchant_certificate_id}" name="{$merchant_name}"><order order_id="{$orderdata['order_id']}" amount="{$orderdata['amount']}" currency="{$currency}"><department merchant_id="{$merchant_id}" amount="{$orderdata['amount']}"/></order></merchant>
merchantdata;

        $merchant_sign = '<merchant_sign type="RSA">'.$epaytool->sign64($merchant).'</merchant_sign>'; //拼接template字符串
        $xml = "<document>".$merchant.$merchant_sign."</document>";
        $orderdata['Signed_Order_B64']=base64_encode($xml);

//file_put_contents("./notify/epay_data_".date('Ymd',time()).".txt", var_export($orderdata,true)."\n", FILE_APPEND);

//检验RSA字符串是否一致
        $check_sign = $epaytool->check_sign($merchant,base64_decode($epaytool->sign64($merchant)),$public_key_fn);

        $this->assign('orderdata',$orderdata);

        $pagetitle = L('pay-ing');
        $this->assign('pagetitle',$pagetitle);
        $this->display();
    }

    //支付返回
    public function Notify(){
        //接收返回数据

        $response = $_POST['response'];
        $response = stripslashes($response);
        $responsedata = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        \Think\Log::write('epay返回数据：'.$response,'EPAY');

        $order_sn = $responsedata['bank']['customer']['merchant']['order']['@attributes']['order_id'];
        $amount = $responsedata['bank']['customer']['merchant']['order']['@attributes']['amount'];
        $response_code = $responsedata['bank']['results']['payment']['@attributes']['response_code'];
        $reference = $responsedata['bank']['results']['payment']['@attributes']['reference'];
        \Think\Log::write('epay返回数据解析：$order_sn='.$order_sn.' $amount='.$amount.' $response_code='.$response_code.' $reference='.$reference,'EPAY');

        //判断基本状态
        if ($response_code == '00'){
            \Think\Log::write('['.$order_sn.']支付返回成功','EPAY');
            //读取订单数据
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                echo '1';
                \Think\Log::write('['.$order_sn.']支付失败：订单数据不存在','EPAY');
            }else{
                if($orderdata['pay_status']!=0){
                    echo '1';
                    \Think\Log::write('['.$order_sn.']支付失败：订单状态不正确','EPAY');
                }else{
                    $order_id = $orderdata['order_id'];
                    //读取匹配支付记录
                    $paydata=M('pay_log')->field('log_id, order_id, order_amount, order_type, is_paid')->where('order_id='.$order_id)->limit(1)->find();
                    if($paydata){
                        \Think\Log::write('['.$order_sn.']进入支付判断','EPAY');
                        if($paydata['is_paid']==0){

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

                                    if($total_amount==$amount){
                                        \Think\Log::write('['.$order_sn.']支付判断：支付金额与返回金额一致','EPAY');
                                        //更新订单主表状态
                                        $time = gmtime();
                                        $data=array();
                                        $data['order_status']=1;
                                        $data['confirm_time']=$time;
                                        $data['pay_status']=2;
                                        $data['pay_time']=$time;
                                        $data['money_paid']=$order_amount;
                                        $data['order_amount']=0;
                                        $data['pay_trade_no']=$reference;
                                        $data['pay_method']='epay';
                                        $data['real_pay_money']=$amount;
                                        M('order_info')->where('order_id='.$order_id)->save($data);
                                        \Think\Log::write('['.$order_sn.']更新订单信息为已支付','EPAY');

                                        //更新订单支付数据表
                                        M('pay_log')->where('order_id='.$order_id)->setField('is_paid',1);
                                        \Think\Log::write('['.$order_sn.']更新支付记录为已支付','EPAY');

                                        //更新fan佣金表
                                        M('yeji')->where('order_id='.$order_id)->setField('pay_time',$time);
                                        M('fan_goods')->where('order_id='.$order_id)->setField('is_pay',1);
                                        \Think\Log::write('['.$order_sn.']更新佣金表','EPAY');

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
                                        \Think\Log::write('['.$order_sn.']更新订单日志','EPAY');


                                        echo '0';
                                    }else{
                                        echo '1';
                                        \Think\Log::write('['.$order_sn.']支付判断：支付金额与返回金额不一致','EPAY');
                                    }
                                    break;
                                case 1:
                                    break;
                                case 3:
                                    break;
                                case 7:
                                    break;
                                default:
                                    echo '1';
                                    break;
                            }
                        }else{
                            \Think\Log::write('['.$order_sn.']支付失败：已支付，无需重复支付','EPAY');
                            echo '1';
                        }
                    }else{
                        \Think\Log::write('['.$order_sn.']支付失败：无关联支付记录','EPAY');
                        echo '1';
                    }
                }
            }
        }else{
            \Think\Log::write('支付失败1','EPAY');
            echo 'fail';
        }

    }


    //特殊处理订单
    public function Diy(){
        //接收返回数据

        $response = '<document><bank name="Halyk Saving Bank JSC" bik="HSBKKZKX"><customer name="Roza" mail="tamika15@bk.ru" phone="7 (775) 202-77-66"><merchant cert_id="b338e9936774208d" name="srcshop.kz"><order order_id="122509130133" amount="6999" currency="398"><department merchant_id="92339821" amount="6999"/></order></merchant><merchant_sign type="RSA"/></customer><customer_sign type="RSA"/><results timestamp="2020-12-25 09:17:56"><payment merchant_id="92339821" card="516949-XX-XXXX-5198" amount="6999.00" reference="036078198019" approval_code="597861" response_code="00" Secure="No" card_bin="KAZ" c_hash="D1CA5A86CE77BC923CEB6B4F84755E4F" exp_date="11/2021"/></results></bank><bank_sign cert_id="00c183d6c3" type="SHA/RSA">gFcaTUtDrr16KGLRJyLl828fv8LMbhg/AiCk2rjKSKmj1MebRPJ+A59docpenYMOTsXyZc/owhXlEqpu2pyiCoruz47NAJZQpI9bNzBOf+EPAnTbWZ2SCndbB03JelLMVwrw9j9MYivZFCQ3tG0bu7tNpyYi4+sK2vN/UE+iEj4=</bank_sign></document>';

        $response = stripslashes($response);
        $responsedata = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        \Think\Log::write('epay返回数据：'.$response,'EPAY');

        $order_sn = $responsedata['bank']['customer']['merchant']['order']['@attributes']['order_id'];
        $amount = $responsedata['bank']['customer']['merchant']['order']['@attributes']['amount'];
        $response_code = $responsedata['bank']['results']['payment']['@attributes']['response_code'];
        $reference = $responsedata['bank']['results']['payment']['@attributes']['reference'];
        \Think\Log::write('epay返回数据解析：$order_sn='.$order_sn.' $amount='.$amount.' $response_code='.$response_code.' $reference='.$reference,'EPAY');

        //判断基本状态
        if ($response_code == '00'){
            \Think\Log::write('['.$order_sn.']支付返回成功','EPAY');
            //读取订单数据
            $where['order_sn']=$order_sn;
            $orderdata=M('order_info')->field('order_id, order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, postscript, shipping_id, shipping_name, pay_id, pay_name, goods_amount, shipping_fee, money_paid, order_amount, add_time, invoice_no, is_pickup, pickup_point, minday, maxday')->where($where)->limit(1)->find();
            if(empty($orderdata)){
                echo 'fail';
                \Think\Log::write('['.$order_sn.']支付失败：订单数据不存在','EPAY');
            }else{
                if($orderdata['pay_status']!=0){
                    echo 'fail';
                    \Think\Log::write('['.$order_sn.']支付失败：订单状态不正确','EPAY');
                }else{
                    $order_id = $orderdata['order_id'];
                    //读取匹配支付记录
                    $paydata=M('pay_log')->field('log_id, order_id, order_amount, order_type, is_paid')->where('order_id='.$order_id)->limit(1)->find();
                    if($paydata){
                        \Think\Log::write('['.$order_sn.']进入支付判断','EPAY');
                        if($paydata['is_paid']==0){

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

                                    if($total_amount==$amount){
                                        \Think\Log::write('['.$order_sn.']支付判断：支付金额与返回金额一致','EPAY');
                                        //更新订单主表状态
                                        $time = gmtime();
                                        $data=array();
                                        $data['order_status']=1;
                                        $data['confirm_time']=$time;
                                        $data['pay_status']=2;
                                        $data['pay_time']=$time;
                                        $data['money_paid']=$order_amount;
                                        $data['order_amount']=0;
                                        $data['pay_trade_no']=$reference;
                                        $data['pay_method']='epay';
                                        $data['real_pay_money']=$amount;
                                        M('order_info')->where('order_id='.$order_id)->save($data);
                                        \Think\Log::write('['.$order_sn.']更新订单信息为已支付','EPAY');

                                        //更新订单支付数据表
                                        M('pay_log')->where('order_id='.$order_id)->setField('is_paid',1);
                                        \Think\Log::write('['.$order_sn.']更新支付记录为已支付','EPAY');

                                        //更新fan佣金表
                                        M('yeji')->where('order_id='.$order_id)->setField('pay_time',$time);
                                        M('fan_goods')->where('order_id='.$order_id)->setField('is_pay',1);
                                        \Think\Log::write('['.$order_sn.']更新佣金表','EPAY');

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
                                        \Think\Log::write('['.$order_sn.']更新订单日志','EPAY');


                                        echo 'success';
                                    }else{
                                        echo 'fail';
                                        \Think\Log::write('['.$order_sn.']支付判断：支付金额与返回金额不一致','EPAY');
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
                            \Think\Log::write('['.$order_sn.']支付失败：已支付，无需重复支付','EPAY');
                            echo 'fail';
                        }
                    }else{
                        \Think\Log::write('['.$order_sn.']支付失败：无关联支付记录','EPAY');
                        echo 'fail';
                    }
                }
            }
        }else{
            \Think\Log::write('支付失败1','EPAY');
            echo 'fail';
        }

    }


    //失败
    public function Fail(){
        \Think\Log::write('访问了 Fail','EPAY');
    }


}