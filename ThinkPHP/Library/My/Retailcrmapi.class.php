<?php

namespace My;

class Retailcrmapi {
    private $apikey;
    private $site;
    private $apiroot;
    private $apipath_getorder;
    private $apipath_createorder;
    private $apipath_updateorder;
    private $apipath_getorderstatus;

    function __construct(){
        $apikey = 'cpZVppafXCp3yovckVQkjZzUBkwJKyVd';
        $site = 'instagram-com-src-shop-official-igshid-6t5ujsxmaizy';
        $apiroot = 'https://instagram736.retailcrm.ru/api/v5/';
        $apipath_getorder = 'orders';
        $apipath_createorder = 'orders/create';
        $apipath_updateorder = 'orders/{externalId}/edit';
        $apipath_getorderstatus = 'orders/statuses';

        $this->apikey = $apikey;
        $this->site = $site;
        $this->apiroot = $apiroot;
        $this->apipath_getorder = $apipath_getorder;
        $this->apipath_createorder = $apipath_createorder;
        $this->apipath_updateorder = $apipath_updateorder;
        $this->apipath_getorderstatus = $apipath_getorderstatus;
    }


    function createorder($obj){
        $apiurl=$this->apiroot.$this->apipath_createorder.'?apiKey='.$this->apikey;
        $site = $this->site;

        $arrproducts=array();
        foreach($obj['products'] as $key=>$item){
            $arrproducts[]=array(
                'initialPrice'=>$item['price'],
                'quantity'=>$item['qty'],
                'offer'=>array(
                    'externalId'=>$item['sku'],
                ),
                'productName'=>$item['name'].'['.$item['sku'].']',
                'externalId'=>$item['sku']
            );
        }

        $order=array(
            'number'=>$obj['ordernum'],
            'externalId'=>$obj['ordernum'],
            'firstName'=>$obj['consignee'],
            'phone'=>'8'.$obj['tele'],
            'customerComment'=>$obj['postscript'],
            'items'=>$arrproducts,
            'delivery'=>array(
                'code'=>'srcexpress',
                'address'=>array(
//            'index'=>'testaddr',//邮编
                    'countryIso'=>'KZ',
                    'region'=>$obj['province'],
                    'city'=>$obj['city'],
                    'street'=>$obj['address']
                )
            ),
        );
        $order=json_encode($order,JSON_UNESCAPED_UNICODE);
        $postdata=array(
            'site'=>$site,
            'order'=>$order,
        );
        $postdata = http_build_query($postdata);
        $headers=array();
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');

        $result = curl_exec ($ch);
        $resdata = $result;
        curl_close($ch);
        //连接失败
        $resresult=array('statuscode'=>300,'message'=>'');
        if($result == FALSE){
            $resresult['statuscode']=300;
            $resresult['message']='API error,please retry';
            return $resresult;
        }
        $postresult=json_decode($result,true);
        if($postresult['success']===true){
            $resresult['statuscode']=200;
            $resresult['message']='order cerate success,';
            $resresult['resdata']=$resdata;
        }else{
            if($postresult['errorMsg']=='Order already exists.'){
                $resresult['statuscode']=301;
                $resresult['message']='order cerate fail，['.$postresult['errorMsg'].']';
                $resresult['resdata']=$resdata;
            }else{
                $resresult['statuscode']=300;
                $resresult['message']='order cerate fail，['.$postresult['errorMsg'].']';
                $resresult['resdata']=$resdata;
            }

        }
        return $resresult;
    }

    function setcrmorderstatus($order_sn,$statuscode,$expdata=''){
        $apiurl=$this->apiroot.$this->apipath_updateorder.'?apiKey='.$this->apikey;
        $apiurl = str_replace('{externalId}',$order_sn,$apiurl);
        $site = $this->site;
        $order=array(
            'status'=>$statuscode,
        );
        if($expdata!=''){
            $expdata=json_decode($expdata,true);
            foreach ($expdata as $key=>$vo) {
                $order[''.$key.'']=$vo;
            }
        }

        $order=json_encode($order,JSON_UNESCAPED_UNICODE);
        $postdata=array(
            'by'=>'externalId',
            'site'=>$site,
            'order'=>$order,
        );
        $postdata = http_build_query($postdata);
        $headers=array();
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');

        $result = curl_exec ($ch);
        $resdata = $result;
        curl_close($ch);
        //连接失败
        $resresult=array('statuscode'=>300,'message'=>'');
        if($result == FALSE){
            $resresult['statuscode']=300;
            $resresult['message']='API error,please retry';
            return $resresult;
        }
        $postresult=json_decode($result,true);
        if($postresult['success']===true){
            $resresult['statuscode']=200;
            $resresult['message']='order sign status update success,';
            $resresult['resdata']=$resdata;
        }else{
            $resresult['statuscode']=300;
            $resresult['message']='order sign status update fail，['.$postresult['errorMsg'].']';
            $resresult['resdata']=$resdata;
        }
        return $resresult;
    }

    function getorderstatus($arr_order_sn){
        $apiurl=$this->apiroot.$this->apipath_getorderstatus.'?apiKey='.$this->apikey;
        $site = $this->site;
//        print_r($apiurl);

//        $order=array($order_sn);
//        $order=json_encode($order,JSON_UNESCAPED_UNICODE);
//        print_r($order);
        $postdata='';
        foreach ($arr_order_sn as $item) {
            $arr=array(
                'externalIds[]'=>$item,
            );
            $postdata .= '&'.http_build_query($arr);
        }
//        print_r($postdata);
//        $postdata=array(
//            'externalIds[]'=>$order_sn,
//        );
//        $postdata = http_build_query($postdata);
        $apiurl.=$postdata;
//        print_r($apiurl);
//        exit;
        $headers=array();
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');

        $result = curl_exec ($ch);
        $resdata = $result;
        curl_close($ch);
        //连接失败
        $resresult=array('statuscode'=>300,'message'=>'');
        if($result == FALSE){
            $resresult['statuscode']=300;
            $resresult['message']='API error,please retry';
            return $resresult;
        }
        $postresult=json_decode($result,true);
        if($postresult['success']===true){
            $formatorderdata = $postresult['orders'];
            $formatdata=array();
            foreach ($formatorderdata as $item) {
                $arr=array(
                    'ordernum'=>$item['externalId'],
                    'status'=>$item['status'],
                );
                $formatdata[]=$arr;
            }
//            print_r($formatdata);
            $resresult['statuscode']=200;
            $resresult['message']='order status get success,';
            $resresult['data']=$formatdata;
            $resresult['resdata']=$resdata;
        }else{
            $resresult['statuscode']=300;
            $resresult['message']='order status get fail，['.$postresult['errorMsg'].']';
            $resresult['resdata']=$resdata;
        }
        return $resresult;
    }

    function getordersinfo($arr_order_sn){
        $apiurl=$this->apiroot.$this->apipath_getorder.'?apiKey='.$this->apikey;
        $site = $this->site;
        $postdata='';
        foreach ($arr_order_sn as $item) {
            $arr=array(
                'filter[externalIds][]'=>$item,
            );
            $postdata .= '&'.http_build_query($arr);
        }
        $apiurl.='&limit=50';
        $apiurl.=$postdata;
        $headers=array();
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');

        $result = curl_exec ($ch);
        $resdata = $result;
        curl_close($ch);
        //连接失败
        $resresult=array('statuscode'=>300,'message'=>'');
        if($result == FALSE){
            $resresult['statuscode']=300;
            $resresult['message']='API error,please retry';
            return $resresult;
        }
        $postresult=json_decode($result,true);
//        print_r($resdata);
//        print_r($postresult);
//        exit;
        if($postresult['success']===true){
            $formatorderdata = $postresult['orders'];
            $formatdata=array();
            foreach ($formatorderdata as $item) {
                if($item['delivery']['code']=='kazpostsrc'){
                    $arr=array(
                        'ordernum'=>$item['externalId'],
                        'status'=>$item['status'],
                        'deliverycode'=>$item['delivery']['code'],
                        'deliveryicode'=>$item['delivery']['integrationCode'],
                        'deliverytrackno'=>$item['delivery']['data']['trackNumber'],
                        'deliverystatus'=>$item['delivery']['data']['status'],
                    );
                    $formatdata[]=$arr;
                }

            }
            if($formatdata){
                $resresult['statuscode']=200;
                $resresult['message']='order info get success';
                $resresult['data']=$formatdata;
                $resresult['resdata']=$resdata;
            }else{
                $resresult['statuscode']=300;
                $resresult['message']='order info get success, but no eligible data';
                $resresult['data']=$formatdata;
                $resresult['resdata']=$resdata;
            }
        }else{
            $resresult['statuscode']=300;
            $resresult['message']='order info get fail，['.$postresult['errorMsg'].']';
            $resresult['resdata']=$resdata;
        }
        return $resresult;
    }

}

