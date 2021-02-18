<?php
/*
 * 系统基本属性配置
 */
$system = array(
	'SHOW_PAGE_TRACE'     => false,
	'URL_HTML_SUFFIX'     => '.html',
	'SESSION_AUTO_START'  => true,
	'URL_MODEL'           => 2,
    'URL_CASE_INSENSITIVE' => true,
	'MODULE_ALLOW_LIST'   => array('Api', 'Home', 'Pay'),
	'DEFAULT_MODULE'      => 'Api',
	/* 模板相关配置 */
    'TMPL_CONTENT_TYPE'     =>  'text/html', // 默认模板输出类型
    'TMPL_ACTION_ERROR'     =>  TMPL_PATH.'Tpl/dispatch_jump_error.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  TMPL_PATH.'Tpl/dispatch_jump_success.tpl', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  TMPL_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件

	'TMPL_PARSE_STRING'   => array(
        '__IMAGES__'       => __ROOT__ .'/Public/images',
	),

    'URL_ROUTER_ON'   => false,
    'URL_MAP_RULES'=>array(

    ),
    'DEFAULT_FILTER'        => 'strip_tags,htmlspecialchars',
    'DEFAULT_TIMEZONE' => 'Asia/Almaty',
);

/*
 * 多语言配置，支持简体中文、俄文、英文
 */
$language = array(
    'LANG_SWITCH_ON'        => true,            // 开启语言包功能
    'LANG_AUTO_DETECT'      => false,            // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'             => 'zh-cn,ru',   // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'          => 'language',             // 默认语言切换变量
    'DEFAULT_LANG'          => 'ru',         // 默认语言

);

/*
 * Auth配置
 */
$auth = array(
    'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'knc_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'knc_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'knc_auth_rule', //权限规则表
        'AUTH_USER' => 'knc_admin'//用户信息表
    )
);

/*
 * AES配置
 */
$aes = array(
    'AES_CONFIG'   => array(
        'AES_KEY'	        => 's9h4z9u4',//$key：密钥（64bit：DES    128bit/256bit：AES） s9h4z9u4z9s5c9k5abcdefgh!@#$%^&*
        'AES_IV'		    => '{0xA,1,0xB,5,4,0xF,7,9,0x17,3,1,6,8,0xC,0xD,91}',//$iv：向量  s9h4z9u4z9s5c9k512345678abcdefgh
        'AES_MODE'          => 'cbc',//$mode：模式
    )
);

/*
 * 系统缓存配置
 */
$cache = array(
    'HTML_CACHE_ON'     =>    true, // 开启静态缓存
    'HTML_CACHE_TIME'   =>    0,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则
        //'*'=>array('{:controller}/{:action}', -1),
        //'Detail:'=>array('{:controller}/{:action}', -1),
        //'home'=>array('/Index/Index', -1),
        //'静态地址'    =>     array('静态规则', '有效期', '附加规则'),
        //{:module}/{:controller}_{:action}
    )
);

//Email配置
$emailconfig = array(
    'MAIL_HOST' =>'ssl://smtp.ym.163.com:994',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'service@029vip.cn',//你的邮箱名
    'MAIL_FROM' =>'service@029vip.cn',//发件人地址
    'MAIL_FROMNAME'=>'服务中心',//发件人姓名
    'MAIL_PASSWORD' =>'a85408355',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
);


$cookies = array(
    'COOKIE_EXPIRE'         =>  0,    // Cookie有效期
    'COOKIE_DOMAIN'         =>  '',      // Cookie有效域名
    'COOKIE_PATH'           =>  '/',     // Cookie路径
    'COOKIE_PREFIX'         =>  'knc_',      // Cookie前缀 避免冲突
    'COOKIE_HTTPONLY'       =>  '',     // Cookie的httponly属性 3.2.2新增
);

$payboxpay3 = [
    //支付网关地址
    'pg_gateway_url' => "https://api.paybox.money/init_payment.php",
    //支付商户号:
    'pg_merchant_id' => "530167",
    //支付密钥
    'pg_secure_key' => "VnOxCHtijEz9NxER",
    //授权许可货币
    'pg_currency' => "KZT",
    //支付渠道定义
    'pb_paymentsystem'=>'EPAYWEBKZT',
    //默认语言
    'pg_language' => "ru",
    //随机字符串
    'pg_salt' => 'SRCSHOP-PAYBOX',
    //异步通知地址
    'pg_result_url'=>'http://test.liuqiang5qqcom.yxnat.softdev.top/pay/paybox/notify.html',
    //成功跳转
    'pg_success_url'=>'http://test.liuqiang5qqcom.yxnat.softdev.top/home/index/paySuccess',
    //失败跳转
    'pg_failure_url'=>'http://test.liuqiang5qqcom.yxnat.softdev.top/index/orderresult_fail.html',
    //测试模式开启
    'pg_testing_mode' => 1
];

$tonglianpay = array (
    //支付商户号
    'TL_paymerchantid' => "0083599900084",
    //支付密钥
    'TL_SecureKey' => "PTQY7UQP2R",
    //授权许可货币
    'TL_CurrencyType' => "USD",
    //异步通知地址
    'TL_NotifyUrl' => "https://mall.silkroad.cool/miniapp/pay/index/notify.html",
    //同步跳转
    'TL_ReturnUrl' => "https://mall.silkroad.cool/miniapp/mobile/index/orderresult.html",
    //默认语言
    'TL_Language' => "ru",
    //支付页面样式自定义
    'TL_PayPageStyle'=>"Tiny",
    //支付网关地址 https://global.aipgateway.com/payment   https://aipgateway.com/payment

    'TL_gatewayUrl' => "https://paycdn.029vip.cn/payment",
);

$payboxpay = array (
    //支付网关地址
    'pb_gatewayurl' => "https://api.paybox.money/v4/payments",
    //支付商户号:
    'pb_merchantid' => "530167",
    //支付密钥
    'pb_secureKey' => "VnOxCHtijEz9NxER",
    //授权许可货币
    'pb_currencytype' => "KZT",
    //支付渠道定义
    'pb_paymentsystem'=>'EPAYWEBKZT',
    //默认语言
    'pb_language' => "ru",
    //异步通知地址
    'pb_result_url'=>'https://mall.silkroad.cool/miniapp/pay/paybox/notify.html',
    //成功跳转
    'pb_success_url'=>'https://mall.silkroad.cool/miniapp/mobile/index/orderresult_success.html',
    //失败跳转
    'pb_failure_url'=>'https://mall.silkroad.cool/miniapp/mobile/index/orderresult_fail.html',
    //未知链接
    'pb_check_url'=>'https://mall.silkroad.cool/miniapp/pay/paybox/checkurl.html',
    'pb_cancel_url'=>'https://mall.silkroad.cool/miniapp/pay/paybox/cancelurl.html',
    //未知链接
    'pb_back_url'=>'https://mall.silkroad.cool/miniapp/pay/paybox/backurl.html',
    'pb_capture_url'=>'https://mall.silkroad.cool/miniapp/pay/paybox/captureurl.html',
);

$kassa24pay = array (
    'ks_gatewayurl' => 'https://ecommerce.pult24.kz/payment/create',
    'ks_merchantid' => '11791137443676347',
    'ks_login' => '11791137443676347',
    'ks_pass' => '7VHDj3P9u7ksX8wgb8vZ',
    'ks_demo' => true,
//    'ks_notify_url' => 'https://mall.silkroad.cool/miniapp/pay/kassa24/notify.html',
    'ks_notify_url' => 'http://test.liuqiang4qqcom.yxnat.softdev.top/pay/kassa24/notify',
//    'ks_return_url'=>  'https://mall.silkroad.cool/miniapp/mobile/index/orderresult_success.html',
    'ks_return_url'=>  'http://test.liuqiang4qqcom.yxnat.softdev.top/home#/order',
);

$epaypay = array(
    //证书序列号
    'epay_MERCHANT_CERTIFICATE_ID' => 'b338e9936774208d',
    //商家名称
    'epay_MERCHANT_NAME' => 'srcshop.kz',
    //私钥路径
    'epay_PRIVATE_KEY_FN' => './epay/cert/cert.prv',
    //私钥密码私钥密码
    'epay_PRIVATE_KEY_PASS' => 'WDfUveEf9i3',
    //公钥路径
    'epay_PUBLIC_KEY_FN' => './epay/cert/kkbca.pem',
    //银行系统中的终端ID
    'epay_MERCHANT_ID' => '92339821',
    'epay_Language' => 'rus',
    'epay_template' => '',
    'epay_card_name' => '',
    'epay_currency'=>'398',

    //同步跳转
    'epay_BackLink' => 'https://mall.silkroad.cool/miniapp/mobile/index/orderresult_success.html',
    //异步通知地址
    'epay_PostLink' => 'https://mall.silkroad.cool/miniapp/pay/epay/notify.html',
    //同步跳转
    'epay_FailureBackLink' => 'https://mall.silkroad.cool/miniapp/pay/epay/fail.html',
    //支付网关地址
    'epay_gatewayUrl' => 'https://epay.kkb.kz/jsp/process/logon.jsp',
);


/*
 * 其他配置，比如二维码目录等
 */
$other = array(
    'Version' => '1.0',
    'timeformat'=>'d-m-Y H:i',
    'dateformat'=>'d-m-Y',
    'mainroot'=>'https://mall.silkroad.cool',
    'approot'=>'https://mall.silkroad.cool/app',
    'linkapproot'=>'https://shopadmin.163.kz',
    'hmackey'=>'s9h4z9u4~'
);

$db=require 'db.php';

return array_merge($system,$language,$auth,$aes,$cache,$other,$db,$cookies,$emailconfig,$kassa24pay,$payboxpay3);