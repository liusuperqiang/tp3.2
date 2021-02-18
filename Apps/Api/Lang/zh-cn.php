<?php
return array(

    //订单列表
    'order_pay_money'=>'立即支付',
    'order_cancel'=>'取消',
    'order_view_wuliu'=>'查看物流',
    'order_comment_shaidan'=>'晒单',
    'order_view_order'=>'详情',
    'order_refunddetail'=>'退款详情',
    'order_refund'=>'退款',
    'order_status_cancel'=>'取消',
    'order_status_invalid'=>'无效',
    'order_status_refund'=>'退货',

    //消息格式  ACTION_msg_type   错误用  ACTION_error_type
    'global_error_api'=>'API签名校验失败',
    'global_error_user'=>'用户信息获取失败',



    'getcoupon_error_couponerr'=>'该优惠券不存在或者已被删除，请刷新后再次领取',
    'getcoupon_msg_null'=>'您来晚了，该优惠券已被领完了，下次领券要早哦！',
    'getcoupon_msg_limitnum'=>'该优惠券每人限领 {$limitnum} 张，您已经领取了 {$usergetcount} 张，无法继续领取哦！',
    'getcoupon_msg_limitold'=>'该优惠券仅限老用户可以领取，您还没有下过订单，无法领取哦！',
    'getcoupon_msg_limitnew'=>'该优惠券仅限新用户可以领取，您已经是SRCSHOP的老朋友了，无法领取哦！',
    'getcoupon_msg_limitrank'=>'该优惠券仅限用户等级为 {$for_rank_name} 的会员可以领取，您无法领取哦！',
    'getcoupon_msg_success'=>'恭喜您成功领取一张价值 {$amount} тг. 的优惠券，您可以去领券中心查看已领取的优惠券或者立即使用哦！',


    //订单页面优惠券
    'order_error_goodsnull'=>'商品信息不完整，请返回重新提交订单',
    'order_error_nostrat'=>'该优惠券开始使用时间：{$starttime}',
    'order_error_stop'=>'该优惠券已过期',
    'order_error_limitamount'=>'该优惠券需订单符合条件的商品总额超过{$limitamount}тг.方可使用',
    'order_error_all_limitamount'=>'该优惠券需订单总额超过{$limitamount}тг.方可使用',
    'order_error_nocoupon'=>'暂无可用优惠券',


);
