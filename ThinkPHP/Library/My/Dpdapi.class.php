<?php
namespace My;
class Dpdapi {

    protected $Orderurl = ''; // token
    protected $Labelurl = ''; // key

    function __construct(){
        $orderurl='http://ws.dpd.ru/services/order2?wsdl';;
        $labelurl= 'http://ws.dpd.ru/services/label-print?wsdl';

        $this->Orderurl = $orderurl;
        $this->Labelurl = $labelurl;
    }

    private function ConstructdpdData($action, $data){
        //  $req = $this->ConstructdpdData('createOrder',$reqinfo);

        $soapData = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        $soapData='';
        $soapData .= "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"http://dpd.ru/ws/order2/2012-04-04\">\r\n";
        $soapData .= "<soapenv:Header/>\r\n";
        $soapData .= "<soapenv:Body>\r\n";
        $soapData .= "<ns:$action>\r\n";
        $soapData .= "<orders>\r\n";
        //$soapData .= " <$action xmlns=\"$namespace\">\r\n";
        foreach ($data as $name => $value) {
            //$name = iconv("GBK", "UTF-8", $name);
            //$value = iconv("GBK", "UTF-8", $value);
            if(is_array($value)){
                $soapData .= "<$name>\r\n";
                foreach ($value as $key => $val) {
                    if(is_array($val)){
                        $soapData .= "<$key>\r\n";
                        foreach ($val as $kk => $vv){
                            if(is_array($vv)){
                                $soapData .= "<$kk>\r\n";
                                foreach ($vv as $ko => $vo){
                                    $soapData .= "<$ko>$vo</$ko>\r\n";
                                }
                                $soapData .= "</$kk>\r\n";
                            }else{
                                $soapData .= "<$kk>$vv</$kk>\r\n";
                            }
                        }
                        $soapData .= "</$key>\r\n";
                    }else{
                        $soapData .= "<$key>$val</$key>\r\n";
                    }
                }
                $soapData .= "</$name>\r\n";
            }else{
                $soapData .= "<$name>$value</$name>\r\n";
            }
        }
        //$soapData .= " </$action>\r\n";
        $soapData .= "</orders>\r\n";
        $soapData .= "</ns:$action>\r\n";
        $soapData .= "</soapenv:Body>\r\n";
        $soapData .= "</soapenv:Envelope>";
        return $soapData;
    }

    private function ConstructData($namespace, $action, $data){
        $soapData = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
        $soapData .= "<soap:Envelope xmlns:xsi=\http://www.w3.org/2001/XMLSchema-instance\ xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"\r\n'>http://schemas.xmlsoap.org/soap/envelope/\">\r\n";
        $soapData .= " <soap:Body>\r\n";
        $soapData .= " <$action xmlns=\"$namespace\">\r\n";
        foreach ($data as $name => $value) {
            $name = iconv("GBK", "UTF-8", $name);
            $value = iconv("GBK", "UTF-8", $value);
            $soapData .= " <$name>$value</$name>\r\n";
        }
        $soapData .= " </$action>\r\n";
        $soapData .= " </soap:Body>\r\n";
        $soapData .= "</soap:Envelope>";
        return $soapData;
    }
    /**
     * 禁止数组中有null
     *
     * @param unknown_type $arr
     * @return unknown string
     */
    private function arrFormat($arr){
        if(! is_array($arr)){
            return $arr;
        }
        foreach($arr as $k => $v){
            if(! isset($v)){
                $arr[$k] = '';
            }
        }
        return $arr;
    }

    public static function objectToArray($obj){
        $arr = '';
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if(is_array($_arr)){
            foreach($_arr as $key => $val){
                $val = (is_array($val) || is_object($val)) ? self::objectToArray($val) : $val;
                $arr[$key] = $val;
            }
        }
        return $arr;
    }


    public function createOrder($orderInfo){
        $client = new \SoapClient($this->Orderurl);
        $ret = $client->createOrder($orderInfo);
        $res = stdToArray($ret);
        $res=$res['return'];
        //return $res;
        $statuscode = $res['status'][0];
        $orderid = $res['orderNumberInternal'][0];
        if($statuscode=='OK'){
            $track_no = $res['orderNum'][0];
        }elseif($statuscode=='OrderPending'){
            $track_no='';
        }else{
            $result['statuscode'] = 300;
            $result['message'] = 'Error';
            return $result;
            exit;
        }
        $result['statuscode'] = 200;
        $result['message'] = 'OK';
        $result['orderid'] = $orderid;
        $result['track_no'] = $track_no;
        return $result;
    }

    public function getOrderStatus($orderInfo){
        $client = new \SoapClient($this->Orderurl);
        $ret = $client->getOrderStatus($orderInfo);
        $res = stdToArray($ret);
        //print_r($res);
        // exit;
        $res=$res['return'];
        $statuscode = $res['status'][0];
        $orderid = $res['orderNumberInternal'][0];
        if($statuscode=='OK'){
            $track_no = $res['orderNum'][0];
            $result['statuscode'] = 200;
            $result['message'] = 'OK';
            $result['orderid'] = $orderid;
            $result['track_no'] = $track_no;
            return $result;
        }else{
            $result['statuscode'] = 300;
            $result['message'] = 'Error';
            $result['orderid'] = $orderid;
            $result['track_no'] = '';
            return $result;
        }
    }

    public function getLabelFile($orderInfo){
        $track_no_ori =$orderInfo['getLabelFile']['order']['orderNum'];
        $client = new \SoapClient($this->Labelurl);
        $ret = $client->createLabelFile($orderInfo);
        $res = stdToArray($ret);
        $res=$res['return'];
        $statuscode = $res['order']['status'];
        $track_no = $res['order']['orderNum'];
        if($statuscode=='OK'){
            $file = $res['file'][0];
            $savepath='./uploads/pdflabel/'.date('Ymd',time()).'/';
            $receiveFile = $savepath.$track_no_ori.'.pdf';

            $FileUtil =new \My\FileUtil();
            $FileUtil->connect();
            if (!$FileUtil->writeFile($receiveFile, $file)) { //移动失败
                $result['statuscode'] = 300;
                $result['message'] = 'Error';
                $result['track_no'] = $track_no;
            } else { //移动成功
                $result['statuscode'] = 200;
                $result['message'] = 'OK';
                $result['track_no'] = $track_no;
                $result['label'] = $receiveFile;
            }
        }else{
            $result['statuscode'] = 300;
            $result['message'] = 'Error';
            $result['track_no'] = $track_no;
        }
        return $result;
    }


}