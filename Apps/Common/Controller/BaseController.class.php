<?php
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller {
	protected function _initialize(){
//		define('UID',islogin());  // 获判断用户是否登录

        S(array(
            'type'=>'File',
            'prefix'=>'knc',
            'expire'=>7200
        ));

	}
}