<?php

/**
 * 获取商品属性
 *
 * @param $goods_id
 * @return mixed
 */
function get_goods_properties($goods_id){
    /* 对属性进行重新排序和分组 */
    $grp = M('goods_type')
        ->field('ecs_goods_type.attr_group')
        ->join('LEFT JOIN ecs_goods ON ecs_goods.goods_type = ecs_goods_type.cat_id')
        ->where('ecs_goods.goods_id='.$goods_id)
        ->find();
    if (!empty($grp)) {
        $groups = explode("\n", strtr($grp, "\r", ''));
    }

    /* 获得商品的规格 */
    $list = M('goods_attr')->field('ecs_attribute.attr_id, ecs_attribute.attr_name, ecs_attribute.attr_group, ecs_attribute.is_linked, ecs_attribute.attr_type, ecs_goods_attr.goods_attr_id, ecs_goods_attr.attr_value, ecs_goods_attr.attr_price')
        ->join('LEFT JOIN ecs_attribute ON ecs_attribute.attr_id = ecs_goods_attr.attr_id')
        ->where('ecs_goods_attr.goods_id='.$goods_id)
        ->order('ecs_attribute.sort_order, ecs_goods_attr.attr_id,ecs_goods_attr.attr_price, ecs_goods_attr.goods_attr_id')->select();

    $arr['pro'] = array();     // 属性
    $arr['spe'] = array();     // 规格
    $arr['lnk'] = array();     // 关联的属性

    foreach ($list AS $row) {
        $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);
        if ($row['attr_type'] == 0) {
            $group = (isset($groups[$row['attr_group']])) ? $groups[$row['attr_group']] : '商品属性';

            $arr['pro'][$group][$row['attr_id']]['name'] = $row['attr_name'];
            $arr['pro'][$group][$row['attr_id']]['value'] = $row['attr_value'];
        } else {
            $arr['spe'][$row['attr_id']]['attr_id'] = $row['attr_id'];
            $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
            $arr['spe'][$row['attr_id']]['name'] = $row['attr_name'];
            $arr['spe'][$row['attr_id']]['values'][] = array(
                'label' => $row['attr_value'],
                'id' => $row['goods_attr_id']
            );
        }

        if ($row['is_linked'] == 1) {
            /* 如果该属性需要关联，先保存下来 */
            $arr['lnk'][$row['attr_id']]['name'] = $row['attr_name'];
            $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
        }
    }
    $arr['spe'] = array_values($arr['spe']);
    return $arr;
}

/**
 * 获取商品属性
 */
if (!function_exists('getGoodsAttr'))
{
     function getGoodsAttr ($goods_id)
     {
         /* 获得商品的规格 */
         $list = M('goods_attr')->field('ecs_attribute.attr_id, ecs_attribute.attr_name, ecs_attribute.attr_group, ecs_attribute.is_linked, ecs_attribute.attr_type, ecs_goods_attr.goods_attr_id, ecs_goods_attr.attr_value, ecs_goods_attr.attr_price')
             ->join('LEFT JOIN ecs_attribute ON ecs_attribute.attr_id = ecs_goods_attr.attr_id')
             ->where('ecs_goods_attr.goods_id='.$goods_id)
             ->order('ecs_goods_attr.goods_attr_id desc')
             ->select();

         $products = M('products')->field('product_id, goods_attr, product_sn, price, product_number')
             ->where('ecs_products.goods_id = ' . $goods_id)
//             ->order('product_id desc')
             ->select();
         foreach ($products as $k => $v) {
             $products[$k]['price'] = price_format($v['price'], 0);
         }

         $pec = [];
         $len = count($products);
         $pro_default = $products[$len - 1];

         foreach ($list as $v) {
             $pec[$v['attr_id']]['name'] = $v['attr_name'];
             $pec[$v['attr_id']]['values'][] = ['label' => $v['attr_value'], 'id' => $v['goods_attr_id']];
         }
         $products = array_column($products, null, 'goods_attr');

         $properties = [];
         $properties['default'] = $pro_default;
         $properties['products'] = $products;
         $properties['spe'] = array_values($pec);
         return $properties;
     }
}

/**
 * 查询地址名称
 */
if (!function_exists('getRegionName')) {
    function getRegionName ($region_id)
    {
        $row = M('region')->where('region_id = ' . $region_id)->field('region_name')->limit(1)->find();
        return $row['region_name'];
    }
}

/**
 * 省
 *
 * @return mixed
 */
function getAreaData ()
{
    //加载省州
    $where=array();
    $where['parent_id'] = 1;
    $list = M('region')
        ->fetchSql(false)
        ->field('region_id,region_name')
        ->where($where)->order('region_id asc')->select();
    return $list;
}

function calShipping ($city_id, $cang_id)
{
    $aging='';
    $shippingdata = M('shipping_days')->field('mindays, maxdays, logistics, shippingnote')->fetchSql(false)->where('cang_id='.$cang_id.' and region_id='.$city_id)->find();
    if($shippingdata){
        if($shippingdata['shippingnote']!=''){
            $aging=$shippingdata['shippingnote'];
        }else{
            if($shippingdata['mindays']>0 and $shippingdata['maxdays']>0){
                if($shippingdata['logistics']!=''){
                    $aging = L('shippingdata-aging-all',array('mindays'=>$shippingdata['mindays'],'maxdays'=>$shippingdata['maxdays'],'logistics'=>$shippingdata['logistics']));
                }else{
                    $aging = L('shippingdata-aging',array('mindays'=>$shippingdata['mindays'],'maxdays'=>$shippingdata['maxdays']));
                }
            }else{
                $aging = L('shippingdata-default-shipping');
            }
        }
    }else{
        $aging = L('shippingdata-default-shipping');
    }
    return $aging;
}

/**
 * 判断某个商品是否正在特价促销期
 *
 * @access  public
 * @param   float $price 促销价格
 * @param   string $start 促销开始日期
 * @param   string $end 促销结束日期
 * @return  float   如果还在促销期则返回促销价，否则返回0
 */
function bargain_price($price, $start, $end)
{
    if ($price == 0) {
        return 0;
    } else {
        $time = gmtime();
        if ($time >= $start && $time <= $end) {
            return $price;
        } else {
            return 0;
        }
    }
}

/**
 * 改变订单中商品库存
 * @param   int $order_id 订单号
 * @param   bool $is_dec 是否减少库存
 * @param   bool $storage 减库存的时机，1，下订单时；0，发货时；
 */
function change_order_goods_storage($order_id, $is_dec = true, $storage = 0){
    $list=M('order_goods')->field('rec_id, goods_id, goods_name, goods_sn, product_id, goods_number')->where('order_id='.$order_id.' and is_real=1')->select();
    foreach ($list as $key=>$vo) {
        if ($is_dec) {
            change_goods_storage($vo['goods_id'], $vo['product_id'], -$vo['goods_number']);
        } else {
            change_goods_storage($vo['goods_id'], $vo['product_id'], $vo['goods_number']);
        }
    }
}

/**
 * 商品库存增与减 货品库存增与减
 *
 * @param   int $good_id 商品ID
 * @param   int $product_id 货品ID
 * @param   int $number 增减数量，默认0；
 *
 * @return  bool               true，成功；false，失败；
 */
function change_goods_storage($good_id, $product_id, $number = 0){
    if ($number == 0) {
        return true; // 值为0即不做、增减操作，返回true
    }

    if (empty($good_id) || empty($number)) {
        return false;
    }

    $number = ($number > 0) ? '+ ' . $number : $number;

    /* 处理货品库存 */
    $products_query = true;
    if ($product_id>0) {
        $where=array();
        $where['goods_id'] = $good_id;
        $where['product_id'] = $product_id;
        $products_query = M('products')->where($where)->limit(1)->setInc('product_number',$number);
    }

    /* 处理商品库存 */
    $where=array();
    $where['goods_id'] = $good_id;
    $query = M('goods')->where($where)->limit(1)->setInc('goods_number',$number);

    if ($query && $products_query) {
        return true;
    } else {
        return false;
    }
}

function generate_username_by_mobile($mobile){
    $username = 'u' . substr($mobile, 0, 3);
    $charts = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    $max = strlen($charts);
    for ($i = 0; $i < 4; $i++) {
        $username .= $charts[mt_rand(0, $max)];
    }
    $username .= substr($mobile, -4);

    $checkhas = M('users')->field('user_id')->where(array('user_name'=>$username))->limit(1)->find();

    if ($checkhas) {
        return generate_username_by_mobile();
    }
    return $username;
}

/**
 * 添加商品名样式
 * @param   string $goods_name 商品名称
 * @param   string $style 样式参数
 * @return  string
 */
function add_style($goods_name, $style)
{
    $goods_style_name = $goods_name;

    $arr = explode('+', $style);

    $font_color = !empty($arr[0]) ? $arr[0] : '';
    $font_style = !empty($arr[1]) ? $arr[1] : '';

    if ($font_color != '') {
        $goods_style_name = '<font color=' . $font_color . '>' . $goods_style_name . '</font>';
    }
    if ($font_style != '') {
        $goods_style_name = '<' . $font_style . '>' . $goods_style_name . '</' . $font_style . '>';
    }
    return $goods_style_name;
}

/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param   string      $str        被截取的字符串
 * @param   int         $length     截取的长度
 * @param   bool        $append     是否附加省略号
 *
 * @return  string
 */
function sub_str($str, $length = 0, $append = true)
{
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength)
    {
        return $str;
    }
    elseif ($length < 0)
    {
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr'))
    {
        $newstr = mb_substr($str, 0, $length, EC_CHARSET);
    }
    elseif (function_exists('iconv_substr'))
    {
        $newstr = iconv_substr($str, 0, $length, EC_CHARSET);
    }
    else
    {
        //$newstr = trim_right(substr($str, 0, $length));
        $newstr = substr($str, 0, $length);
    }

    if ($append && $str != $newstr)
    {
        $newstr .= '...';
    }

    return $newstr;
}

/**
 * 是否为手机专享价
 * @param type $exclusive
 * @param type $finalPrice
 * @return boolean
 */
function is_exclusive($exclusive = 0,$finalPrice= 0){
    if($exclusive>0 && $exclusive <= $finalPrice){
        return true;
    }else{
        return false;
    }
}

/**
 * 取得商品最终使用价格
 *
 * @param   string  $goods_id      商品编号
 * @param   string  $goods_num     购买数量
 * @param   boolean $is_spec_price 是否加入规格价格
 * @param   mix     $spec          规格ID的数组或者逗号分隔的字符串
 *
 * @return  商品最终购买价格
 */
function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = array())
{
    $final_price   = '0'; //商品最终购买价格
    $volume_price  = '0'; //商品优惠价格
    $promote_price = '0'; //商品促销价格
    $user_price    = '0'; //商品会员价格
    $exclusive = '0';//手机专享价格

    //取得商品优惠价格列表
    $price_list   = get_volume_price_list($goods_id, '1');

    if (!empty($price_list))
    {
        foreach ($price_list as $value)
        {
            if ($goods_num >= $value['number'])
            {
                $volume_price = $value['price'];
            }
        }
    }

    //取得商品促销价格列表
    /* 取得商品信息 */
    $sql = "SELECT g.promote_price, g.promote_start_date, g.promote_end_date, ".
        "IFNULL(mp.user_price, g.shop_price * '" . $_SESSION['discount'] . "') AS shop_price, g.exclusive ".
        " FROM ecs_goods AS g ".
        " LEFT JOIN ecs_member_price AS mp ".
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . $_SESSION['user_rank']. "' ".
        " WHERE g.goods_id = '" . $goods_id . "'" .
        " AND g.is_delete = 0";
//    $goods = $GLOBALS['db']->getRow($sql);
    $goods = M()->query($sql);

    /* 计算商品的促销价格 */
    if ($goods['promote_price'] > 0)
    {
        $promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
    }
    else
    {
        $promote_price = 0;
    }

    //取得商品会员价格列表
    $user_price    = $goods['shop_price'];

    //比较商品的促销价格，会员价格，优惠价格
    if (empty($volume_price) && empty($promote_price))
    {
        //如果优惠价格，促销价格都为空则取会员价格
        $final_price = $user_price;
    }
    elseif (!empty($volume_price) && empty($promote_price))
    {
        //如果优惠价格为空时不参加这个比较。
        $final_price = min($volume_price, $user_price);
    }
    elseif (empty($volume_price) && !empty($promote_price))
    {
        //如果促销价格为空时不参加这个比较。
        $final_price = min($promote_price, $user_price);
    }
    elseif (!empty($volume_price) && !empty($promote_price))
    {
        //取促销价格，会员价格，优惠价格最小值
        $final_price = min($volume_price, $promote_price, $user_price);
    }
    else
    {
        $final_price = $user_price;
    }

    $exclusive = $goods['exclusive'];

    if(!empty($exclusive) && $exclusive >0){

        $final_price = min($final_price, $exclusive);
    }

    //如果需要加入规格价格
    if ($is_spec_price)
    {
        if (!empty($spec))
        {
            $spec_price   = spec_price($spec);

            $goods_attr_str = implode('|',$spec);
            $sql = "select price from ecs_products where goods_id=$goods_id and goods_attr = '$goods_attr_str'";
            $final_price = $GLOBALS['db']->getOne($sql);
            if(!$final_price||$final_price<=0){
                $final_price = $user_price;
            }

//            if($spec_price != 0){
//                $final_price = $spec_price;
//            }

        }
    }

    //返回商品最终购买价格
    return $final_price;
}

function getImage($img)
{
    if(!$img){
        return '';
    }
    if (substr($img, 0, 4) == 'http') {
        return $img;
    }

    $url = base_url();

    $url =  $url.'/'.ltrim($img, '/');
    return $url;
}


/**
 * 重写 URL 地址
 *
 * @access  public
 * @param   string  $app        执行程序
 * @param   array   $params     参数数组
 * @param   string  $append     附加字串
 * @param   integer $page       页数
 * @param   string  $keywords   搜索关键词字符串
 * @return  void
 */
function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
{
    static $rewrite = NULL;

    if ($rewrite === NULL)
    {
        $rewrite = intval($GLOBALS['_CFG']['rewrite']);
    }

    $rewrite =0;//屏蔽手机端的url重写伪静态

    $args = array('go'	  => '',
        'suppid'=> 0,
        'cid'   => 0,
        'gid'   => 0,
        'bid'   => 0,
        'acid'  => 0,
        'aid'   => 0,
        'sid'   => 0,
        'gbid'  => 0,
        'auid'  => 0,
        'sort'  => '',
        'order' => '',
    );

    extract(array_merge($args, $params));

    $uri = '';
    switch ($app)
    {
        case 'supplier':
            $go = empty($go) ? 'index':$go;
            if ($go == 'category' || $go == 'index')
            {
                if ($rewrite)
                {
                    $uri = $app.'-'.$go.'-'.$suppid.'-' . $cid;
                    if (isset($bid))
                    {
                        $uri .= '-b' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '-min'.$price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '-max'.$price_max;
                    }
                    if (isset($filter_attr))
                    {
                        $uri .= '-attr' . $filter_attr;
                    }
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = $app.'.php?go='.$go.'&amp;suppId='.$suppid.'&amp;id=' . $cid;
                    if (!empty($bid))
                    {
                        $uri .= '&amp;brand=' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '&amp;price_min=' . $price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '&amp;price_max=' . $price_max;
                    }
                    if (!empty($filter_attr))
                    {
                        $uri .='&amp;filter_attr=' . $filter_attr;
                    }

                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }elseif ($go == 'article')
            {
                $uri = $rewrite ? $app.'-article-'.$suppid.'-' . $aid : $app.'.php?go=article&suppId='.$suppid.'&id=' . $aid;
            }elseif($go == 'search')
            {
                if ($rewrite)
                {
                    $uri = $app.'-'.$go.'-'.$suppid;
                    if (isset($cid))
                    {
                        $uri .= '-c' . $cid;
                    }
                    if (isset($bid))
                    {
                        $uri .= '-b' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '-min'.$price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '-max'.$price_max;
                    }
                    if (isset($filter_attr))
                    {
                        $uri .= '-attr' . $filter_attr;
                    }
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '-' . $keywords;
                    }
                }
                else
                {
                    $uri = $app.'.php?go='.$go.'&amp;suppId='.$suppid;
                    if (!empty($cid))
                    {
                        $uri .= '&amp;cid=' . $cid;
                    }
                    if (!empty($bid))
                    {
                        $uri .= '&amp;brand=' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '&amp;price_min=' . $price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '&amp;price_max=' . $price_max;
                    }
                    if (!empty($filter_attr))
                    {
                        $uri .='&amp;filter_attr=' . $filter_attr;
                    }

                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '&amp;keywords=' . $keywords;
                    }
                }
            }

            break;
        case 'stores':
            if (empty($cid))
            {
                return false;
            }
            else
            {
                if ($rewrite)
                {
                    $uri = 'stores-' . $cid;
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                }
                else
                {
                    $uri = 'stores.php?id=' . $cid;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                }
            }

            break;
        case 'category':
            if (empty($cid))
            {
                return false;
            }
            else
            {
                if ($rewrite)
                {
                    $uri = 'category-' . $cid;
                    if (isset($bid))
                    {
                        $uri .= '-b' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '-min'.$price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '-max'.$price_max;
                    }
                    if (isset($filter))
                    {
                        $uri .= '-fil' . $filter;
                    }
                    if (isset($filter_attr))
                    {
                        $uri .= '-attr' . $filter_attr;
                    }
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = 'category.php?id=' . $cid;
                    if (!empty($bid))
                    {
                        $uri .= '&amp;brand=' . $bid;
                    }
                    if (isset($price_min))
                    {
                        $uri .= '&amp;price_min=' . $price_min;
                    }
                    if (isset($price_max))
                    {
                        $uri .= '&amp;price_max=' . $price_max;
                    }
                    if (isset($filter))
                    {
                        $uri .= '&amp;filter=' . $filter;
                    }
                    if (!empty($filter_attr))
                    {
                        $uri .='&amp;filter_attr=' . $filter_attr;
                    }

                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }

            break;
        case 'goods':
            if (empty($gid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
            }

            break;
        case 'brand':
            if (empty($bid))
            {
                return false;
            }
            else
            {
                if ($rewrite)
                {
                    $uri = 'brand-' . $bid;
                    if (isset($cid))
                    {
                        $uri .= '-c' . $cid;
                    }
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = 'brand.php?id=' . $bid;
                    if (!empty($cid))
                    {
                        $uri .= '&amp;cat=' . $cid;
                    }
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }

            break;
        case 'article_cat':
            if (empty($acid))
            {
                return false;
            }
            else
            {
                if ($rewrite)
                {
                    $uri = 'article_cat-' . $acid;
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '-' . $keywords;
                    }
                }
                else
                {
                    $uri = 'article_cat.php?id=' . $acid;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '&amp;keywords=' . $keywords;
                    }
                }
            }

            break;
        case 'article':
            if (empty($aid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
            }

            break;
        case 'group_buy':
            if (empty($gbid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
            }

            break;
        case 'auction':
            if (empty($auid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
            }

            break;
        case 'snatch':
            if (empty($sid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
            }

            break;
        case 'pro_search':
            break;
        case 'search':
            break;
        case 'exchange':
            if ($rewrite)
            {
                $uri = 'exchange-' . $cid;
                if (isset($price_min))
                {
                    $uri .= '-min'.$price_min;
                }
                if (isset($price_max))
                {
                    $uri .= '-max'.$price_max;
                }
                if (!empty($page))
                {
                    $uri .= '-' . $page;
                }
                if (!empty($sort))
                {
                    $uri .= '-' . $sort;
                }
                if (!empty($order))
                {
                    $uri .= '-' . $order;
                }
            }
            else
            {
                $uri = 'exchange.php?cat_id=' . $cid;
                if (isset($price_min))
                {
                    $uri .= '&amp;integral_min=' . $price_min;
                }
                if (isset($price_max))
                {
                    $uri .= '&amp;integral_max=' . $price_max;
                }

                if (!empty($page))
                {
                    $uri .= '&amp;page=' . $page;
                }
                if (!empty($sort))
                {
                    $uri .= '&amp;sort=' . $sort;
                }
                if (!empty($order))
                {
                    $uri .= '&amp;order=' . $order;
                }
            }

            break;
        case 'exchange_goods':
            if (empty($gid))
            {
                return false;
            }
            else
            {
                $uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?id=' . $gid . '&amp;act=view';
            }

            break;
        default:
            return false;
            break;
    }

    if ($rewrite)
    {
        if ($rewrite == 2 && !empty($append))
        {
            $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
        }

        $uri .= '.html';
    }
    if (($rewrite == 2) && (strpos(strtolower(EC_CHARSET), 'utf') !== 0))
    {
        $uri = urlencode($uri);
    }
    return $uri;
}

//获得订单数量
function selled_count($goods_id)
{
    return selled_wap_count($goods_id);
//     $sql= "select sum(goods_number) as count from ".$GLOBALS['ecs']->table('order_goods')."where goods_id ='".$goods_id."'";
//     $res = $GLOBALS['db']->getOne($sql);
//     if($res>0)
//     {
//     return $res;
//     }
//     else
//     {
//       return('0');
//     }
}

function selled_wap_count($goods_id)
{
    $sql = "select sum(goods_number) as count from ecs_order_goods where goods_id = $goods_id";
//    $res = $GLOBALS['db']->getOne($sql);
    $res = M()->query($sql);
    if ($res > 0) {
        return $res[0]['count'];
    } else {
        return ('0');
    }
}

function get_evaluation_sum($goods_id)
{
    $sql = "SELECT count(*) as count FROM ecs_comment WHERE status=1 and  comment_type =0 and id_value =" . $goods_id;//status=1表示通过了的评论才算  comment_type =0表示针对商品的评价
    $res = M()->query($sql);
    return $res[0]['count'];
}

/**
 * 取得商品优惠价格列表
 *
 * @param   string  $goods_id    商品编号
 * @param   string  $price_type  价格类别(0为全店优惠比率，1为商品优惠价格，2为分类优惠比率)
 *
 * @return  优惠价格列表
 */
function get_volume_price_list($goods_id, $price_type = '1')
{
    $volume_price = array();
    $temp_index   = '0';

    $sql = "SELECT `volume_number` , `volume_price`".
        " FROM ecs_volume_price".
        " WHERE `goods_id` = '" . $goods_id . "' AND `price_type` = '" . $price_type . "'".
        " ORDER BY `volume_number`";

//    $res = $GLOBALS['db']->getAll($sql);
    $res = M()->query($sql);

    foreach ($res as $k => $v)
    {
        $volume_price[$temp_index]                 = array();
        $volume_price[$temp_index]['number']       = $v['volume_number'];
        $volume_price[$temp_index]['price']        = $v['volume_price'];
        $volume_price[$temp_index]['format_price'] = price_format($v['volume_price'], 0);
        $temp_index ++;
    }
    return $volume_price;
}

/**
 * 重新获得商品图片与商品相册的地址
 *
 * @param int $goods_id 商品ID
 * @param string $image 原商品相册图片地址
 * @param boolean $thumb 是否为缩略图
 * @param string $call 调用方法(商品图片还是商品相册)
 * @param boolean $del 是否删除图片
 *
 * @return string   $url
 */
function get_image_path($goods_id, $image='', $thumb=false, $call='goods', $del=false)
{
    $url = empty($image) ? $GLOBALS['_CFG']['no_picture'] : $image;
    return $url;
}