<?php
namespace My;
class Spsrapi {

    protected $Orderurl = '';
    private $username;
    private $password;
    private $cnum;
    private $sid;

    function __construct(){
        $orderurl='http://api.spsr.ru/waExec/WAExec';
        $username= C('SPSR_LOGIN');
        $password= C('SPSR_PASS');
        $cnum= C('SPSR_CNUM');
        $sid = S('spsrsid');
        $this->username = $username;
        $this->password = $password;
        $this->cnum = $cnum;
        $this->sid = $sid;
        $this->Orderurl = $orderurl;
    }

    private function ConstructloginData(){
        $xml = '
		<root xmlns="http://spsr.ru/webapi/usermanagment/login/1.0">
		<p:Params Name="WALogin" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />
		<Login Login="'.$this->username.'" Pass="'.$this->password.'" UserAgent="Компания"/>
		</root>
		';

        //$soapData .= "</soapenv:Body>\r\n";
       // $soapData .= "</soapenv:Envelope>";
        return $xml;
    }

    private function ConstructorderData($data){
        $xml = '
<root xmlns="http://spsr.ru/webapi/xmlconverter/1.3">
 <Params Name="WAXmlConverter" Ver="1.3" xmlns="http://spsr.ru/webapi/WA/1.0" />
 <Login SID="'.$this->sid.'"/>
 <XmlConverter> 
 <GeneralInfo ContractNumber="'.$this->cnum.'">
   <Invoice Action="N" ShipRefNum="'.$data['ShipRefNum'].'" PickUpType="W" ProductCode="PelOn" PiecesCount="1" >
      <Receiver Country="'.$data['Country'].'" Region="'.$data['Region'].'" City="'.$data['City'].'" Address="'.$data['Address'].'" CompanyName="'.$data['CompanyName'].'" ContactName="'.$data['ContactName'].'" Phone="'.$data['Phone'].'" />
      <AdditionalServices COD="0" />   
      <Pieces>
      <Piece Description="16" Weight="'.$data['Weight'].'" Length="'.$data['Length'].'" Width="'.$data['Width'].'" Depth="'.$data['Depth'].'" />
      </Pieces>
   </Invoice>
 </GeneralInfo>
</XmlConverter>
</root>
		';
        return $xml;
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
        $result = array('statuscode' => 200, 'message' => '');
        $xml = $this->ConstructorderData($orderInfo);
        //print_r($xml);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_TIMEOUT,30);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $this->Orderurl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $resdata = curl_exec($curl);

        //print_r($resdata);
        curl_close($curl);
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($resdata, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $baserc=$values['Result']['@attributes']['RC'];

        //print_r($baserc.'****');
        if($baserc=='0'){
            $maindata=$values['Invoice']['@attributes'];
            if($maindata['Status']=='Created'){
                $result['statuscode']=200;
                $result['message']='spsr\'s order create success.';
                $result['orderid'] = $orderInfo['ShipRefNum'];
                $result['track_no'] = $maindata['InvoiceNumber'];
            }else{
                $msgdata=$values['Invoice']['Message']['@attributes']['Text'];
                $msgdata = iconv('utf-8', 'windows-1251', $msgdata);
                $result['statuscode']=300;
                $result['message']=$msgdata;
                $result['orderid'] = $orderInfo['ShipRefNum'];
            }
        }else{
            $msgdata='错误代码：'.$values['error']['@attributes']['ErrorNumber'].'，详细信息请联系客服经理';
            $result['statuscode']=300;
            $result['message']=$msgdata;
            $result['orderid'] = $orderInfo['ShipRefNum'];
        }
        return $result;
    }

    public function waLogin(){
        $result = array('statuscode' => 200, 'message' => '');
        $xml = $this->ConstructloginData();
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_TIMEOUT,30);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $this->Orderurl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $resdata = curl_exec($curl);
        curl_close($curl);
        libxml_disable_entity_loader(true);

        $resdata = json_decode(json_encode(simplexml_load_string($resdata, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $logincode=$resdata['Result']['@attributes'];
        $logincode = $logincode['RC'];
        if($logincode==0){
            $siddata=$resdata['Login']['@attributes'];
            $spsrsid = $siddata['SID'];
            $result['statuscode']=200;
            $result['message']='SPSR\'s SID is created.';
            $result['sid'] = $spsrsid;
            S('spsrsid',$spsrsid,array('type'=>'file','prefix'=>'knc','expire'=>86400));
        }else{
            $result['statuscode']=300;
            $result['message']='SPSR\'s SID create fail.';
            $result['sid'] = '';
        }
        return $result;
    }



}