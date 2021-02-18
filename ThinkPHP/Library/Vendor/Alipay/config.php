<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018042460068102",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAx0c95SZAbh2nsGVt7F2ZtqeXqyE1gEWqPM8Qj4RgK/LQ12WVapecamx5I8S58SGXDPiJWrA0iDYWkQ49c30+xtBQXMla56GkXs4PtajaqwnV8pLNG0DsUVezNVuB8uH+T2Jv9jg0lnb49V1nzsCCd5YWv/YBACHt+27sJcZFlBrChTBVsVs1HkefoGbfHfPP1Uw8lC7jojJeSSEhKfiAmHanNH33BlTe4saZhTlOdEzRpXvIF4+MZoGrWcpDonqEZIOW997KHzJLaHJZ4goy+4KH8saCWkVvZdjltlUJLf7DpnQPawwm2KYL5DK89cA4E6PSRgkKw9Trp3girdERAwIDAQABAoIBAQCrxOK7JE+hGvqx95vag4u9f5QiNAvTtzjYFXum2Wm0OCZ/o9Y4/NocecepZycHi/aRfaGXhA6j4kttWSLq62gzIthCQmWTByeReHjeEt/5Ug8SeitFdJ6+NqQIOAo4+0ej0avERgo+L+rERHD4K8PJpJuTd07BeH2Tnrb6kH7k+uyWc60L0SRZg8Y15TlXDMWnpSUAn0mALXrO2fm8o3tPQSR0hySZxv0JWZ0WlCTzBDc7yuls5iyTb/chVlDItP07u7g13MuNhLKuQxM/QU0HZjHCkjGMwH+O9q84ZWnvBSA1mUTB8I/3r+8nNUvesbMV+AM8//34l+olUnYzHMvxAoGBAPniYu3slRc1GFPWrsiShUuv1tbyatHJ5I6SVsYFRsypr/so/1vWnzNIlGhQYg525/d5ZxYkPSbW+IHLXmL1SkEFJtMe9ldLo0eYv8uAfeWYTPEPbB9tg3phDY7ZjNZlqDcIRuKVzR5ljieRzvM/n3+Ea1XpUPxMwtItnpLBj7FFAoGBAMwnymhXigQDagLPS7WncAR+ThE7sN0NW4d1OJ7atkDElUuOSuAZ99JNWjJdnLXrY3I9i69Zn70GlUTG/6Ph9SIBnR3StDfLSFF7SixpYGEIvZCKRtX21gpf+PbiMvvPZyFItaS2CyKyTY/69fNwqFukNPRgjBpalaBc+sznhQmnAoGAJYLUcv7mypsP0dYSWs2T8O6ug4nUezWhd72EBSnZnPOEFWHsI28uatZkYNxgO+ehnsa8sNgzzdbLa0CKJLTJtsT9NQga1rPmCONnNzdMruCT/EPiaT1ZVU+oZ19FAxIzlD9L1MvEBOId2tbKiD1uxgeszTK/E4C6Zi6u9TzVQzkCgYEAlQVei/eKOQ+87DwgiYKlE8KWfaHSoijD7Qa1w87mRjS4vaQqJ5cM/KeV9Tm70Fb4CkW+lsqW/UW247NWm2uuoZoH8+L/xdJPAZs7M1vgDDRzGvS0bC53Yq9Xbe8DRCfKodMPNCHl0vfQAg8wRPixC+O6+78VxY7ltV7pe3Rr+fUCgYAj2RLPvwbpD2SCeKxXPeKWzDUQuCy5EXrQvDO4+vFg+KYWe6hHnoQ4qnO/8O7HhH1NlYTcVXnafBiNTptik2XKAX+BSWYE0AMhktPuzVsrvE2iDmNXqp0sBTcy9h20kBgmGZOk8pEm6XqbWoliNQd5OvlSMMm1S5/5tEHQEdjv9A==",
		
		//异步通知地址
		'notify_url' => "http://derunweb.kmdns.net:81/alipay/notify_url.php",
		
		//同步跳转
		'return_url' => "http://zfb.029vip.cn/pay/alipay/returnurl.html",

    //同步跳转
    'return_url' => "http://zfb.029vip.cn/pay/alipay/returnurl.html",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzp1eafODxFNuyiHaTlbHJl5/77Qxw/acZGV2JpUyMP9M3wU8j41b96hZcFJyqsBOmUf+0d5lbG0nXdoLKcOFNd3t6qZP0XSJz5e4O7xorolhqu4ATvXF8cvSrkEii1L4mU7QAc6pxE6nGOzG8pC/StBxygqzmA226hu5AV5BDIHyL/t332H8IapWhEtZRMk9cROZwNZdfS6xFSMUxytHomtUDrcTuKMdsK3f/d6L6Mp8Nfp/eo6e4WMXJaw2uGYeHbzJJRkDIdr//+i5Yls6JGItUcJAcm0rU5uOR/lKx6nReMZq/X5X3EAYAC1NlXZPhuApbGFukOZyQ/eAlc1jYQIDAQAB",
		
	
);