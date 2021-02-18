<?php

namespace My;
class Deliver {
    private $host;
    private $querypath;
    private $appkey;
    private $appsecret;
    private $appcode;
    function __construct(){
        $host= C('DeliverHost');
        $querypath= C('DeliverQueryPath');
        $appcode= C('DeliverAppCode');
        $appkey= C('DeliverAppKey');
        $appsecret=C('DeliverAppSecret');

        $this->Host = $host;
        $this->Querypath = $querypath;
        $this->Appkey = $appkey;
        $this->Appsecret = $appsecret;
        $this->Appcode = $appcode;
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
    function curl_post($url,$header,$method){
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
        $result = curl_exec ($curl);
        curl_close($curl);
        return $result;
    }



    /**
     * 查询物流信息
     * @param $com 物流公司编码
     * @param $nu 快递单号
     */
    function QueryDeliver($com,$nu){
        $arrallow=array('LIMINWL','XINTIAN','COE','henglu','klwl','meiguo','tnten','a2u','benteng','ahdf','timedg','ztong','xindan','bgpyghx','XFHONG','ALP','BFWL','SJWL','SHUNFAWL','TIANHEWL','YBWL','SWHY','TSWL','YUANYUANWL','BALIANGWL','ZGWL','JIAYU','SHHX','ande','ppbyb','dida','jppost','intmail','HENGCHENGWL','HENGFENGWL','gdems','xlyt','gjbg','uex','singpost','guangdongyouzhengwuliu','bht','cces','cloudexpress','dasu','pfcexpress','hjs','huilian','huanqiu','huada','htwd','hipito','hqtd','airgtc','haoyoukuai','hanrun','ccd','hfwuxi','Sky','hongxun','hongjie','httx56','lqht','jinguangsudikuaijian','junfengguoji','jiajiatong56','jrypex','jinchengwuliu','jgwl','pzhjst','ruexp','jmjss','lanhu','ltexp','lutong','ledii','lundao','mailikuaidi','mchy','meiquick','valueway','nuoyaao','euasia','pca','pingandatengfei','pjbest','qbexpress','quanxintong','quansutong','qinyuan','qichen','quansu','qzx56','qskdyxgs','runhengfeng','rytsd','ruidaex','shiyun','sfift','stkd','bgn','jiahuier','pingyou','yumeijie','meilong','guangtong','STARS','NANHANG','lanbiao','挂号信','baotongda','dashun','dada','fangfangda','hebeijianhua','haolaiyun','jinyue','kuaitao','peixing','hkpost','ytfh','zhongxinda','zhongtian','zuochuan','chengguang','cszx','chuanzhi','feibao','huiqiang','lejiedi','lijisong','minbang','ocs','santai','saiaodi','jingdong','zengyi','fanyu','fengda','coe','ees','disifang','rufeng','changtong','chengshi100','chuanxi','feibang','haosheng','yinsu','kuanrong','nell','tongcheng','tonghe','scs','zhima','ririshun','anxun','baiqian','chukouyi','diantong','dajin','feite','pingyou','gnxb','huacheng','huahan','hengyu','huahang','jiuyi','jiete','jingshi','kuayue','mengsu','nanbei','pingyou','pinganda','ruifeng','rongqing','suijia','simai','suteng','shengbang','suchengzhaipei','wuhuan','xingchengzhaipei','yinjie','gnxb','yanwen','zongxing','aae','dhl','fedex','feihu','shunfeng','spring','yidatong','PEWKEE','PHOENIXEXP','CNGLS','BHTEXP','B2B-1669519933','PEISI','SUNDAPOST','SUYUE','F5XM','GZWENJIE','yuancheng','dpex','anjie','jldt','yousu','wanbo','sure','sutong','JUNCHUANWL','guada','dsu','LONGSHENWL','abc','eyoubao','aol','jixianda','haihong','feiyang','rpx','zhaijisong','tiantian','yunwuliu','jiuye','bsky','higo','arke','zwsy','jxy','aramex','guotong','jiayi','longbang','minhang','quanyi','quanchen','usps','xinbang','yuanzhi','zhongyou','yuxin','cnpex','shengfeng','yuantong','jiayunmei','ywfex','xinfeng','wanxiang','menduimen','mingliang','fengxingtianxia','dhlen','gongsuda','zhongtong','quanritong','emsen','ems','wanjia','upsen','yuntong','feikuaida','haimeng','zhongsukuaidi','yuefeng','shunfengen','fedexus','shenghui','auspost','datian','quanjitong','longlangkuaidi','neweggozzo','fedexcn','lianbangkuaidien','shentong','haiwaihuanqiu','yad','jindawuliu','sevendays','tnt','huayu','lianhaotong','nengda','ruidianyouzheng','jingguang','youzhengguoji','LBWL','ontrac','feihang','bangsongwuliu','dhlde','huaxialong','emsguoji','ztwy','fkd','canpostfr','anxinda','quanfeng','canpost','shengan','jiaji','yunda','emsinten','ups','debang','yafeng','kuaijie','huitong','pingyou','aolau','anneng','auexpress','exfresh','bcwelt','youzhengguonei','xiaohongmao','lbbk','byht','idada','baitengwuliu','birdex','bsht','dayang','dechuangwuliu','donghanwl','dfpost','dongjun','dindon','dazhong','decnlh','dekuncn','eshunda','ewe','fedexuk','fox','rufengda','fandaguoji','hnfy','flysman','sccod','farlogistis','gsm','gaticn','gts','gangkuai','gtongsudi','gtsd','tiandihuayu','huangmajia','ucs','huoban','nedahm','huiwen','nmhuahe','hangyu','minsheng','riyu','sxhongmajia','syjiahuier','shlindao','shunjiefengda','subida','bphchina','sendtochina','suning','sihaiet','tianzong','chinatzx','nntengda','sd138','tongdaxing','tlky','youshuwuliu','ueq','weitepai','wtdchina','wzhaunyun','gswtkd','wotu','xiyoute','xilaikd','xsrd','xtb','xintianjie','xaetc','nsf','xianfeng','sunspeedy','xipost','sinatone','sunjex','yundaexus','yxwl','yitongda','yiqiguojiwuliu','yilingsuyun','yujiawuliu','gml','leopard','czwlyn','sdyoupei','yongchang','yufeng','yamaxunwuliu','yousutongda','yishunhang','yongwangda','ecmscn','yingchao','edlogistics','yyexpress','onehcang','ycgky','lineone','ypsd','vipexpress','el56','yyqc56','zhongtie','ZTKY','zzjh','zhongruisudi','zhongwaiyun','zengyisudi','sujievip','zhichengtongda','zhdwl','kuachangwuliu','topspeedex','otobv','zsky123','donghong','kuaiyouda','balunzhi','hutongwuliu','xianchenglian','youbijia','feiyuan','chengji','huaqi','yibang','citylink','meixi','acs','dpd','wuliu','efs','haiMiPai','cityYj','maxeed','giant','youYa','hzx','cnair');

        $result = array('showapi_res_code' => 0, 'showapi_res_error' => '');
        if(!in_array($com,$arrallow)){
            $result['showapi_res_code'] = 1;
            $result['showapi_res_error'] = '请传入正确的快递公司编码！[ERROR60001]';
            return $result;
        }
        if($nu==''){
            $result['showapi_res_code'] = 1;
            $result['showapi_res_error'] = '请传入正确的快递单号！[ERROR60002]';
            return $result;
        }

        $method = 'GET';
        $appcode = $this->Appcode;
        $host = $this->Host;
        $querypath = $this->Querypath;

        // 生成头
        $header = array("Authorization:APPCODE $appcode");
        // 拼接请求包
        $querys = 'com='.$com.'&nu='.$nu;
        $url = $host.$querypath.'?'.$querys;
        // 发送请求
        $result = $this->curl_post($url,$header,$method);

        $result = json_decode($result,true);
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
        return $rescode;
    }
}
?>
