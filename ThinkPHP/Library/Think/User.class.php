<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 2015/3/20 编写 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Think;

class User{
    private $url_i;                          //文件url 
    public function  __construct($arr=array()){
        $this->arr=$arr;
    }

    public function Start(){
        $url_i='';
        include "Aesconfig.class.php";
        include "Xcrypt.class.php";
        $aes=new Xcrypt($local_aes['AES_KEY'],$local_aes['AES_MODE'],$local_aes['AES_IV']);
        if(file_exists("./ThinkPHP/license.php")){
            include "./ThinkPHP/license.php";
            $lic_time=$aes->decrypt($lic_limit,'base64');
            if($lic_time>time()){
                // 读取模块儿配置文件
                foreach ($this->arr as $key => $value) {
                    if($value==ture){
                         $mode_list.=$key.',';
                    }
                }
                $mode_str=substr($mode_list,0,strlen($mode_list)-1);
                $lic_str=$aes->encrypt($mode_str,'base64');
                if($lic_str==$lic_module){
                    return time();
                }else{
                    return "您的授权文件模块儿有误！";
                    exit;
                }
            }else{
                return "您的使用时间已超过授权时间，请联系运营商！";
                exit;
            }
        }else{
            return "您的授权文件不存在，请联系运营商！";
            exit;
        }
    }
}
