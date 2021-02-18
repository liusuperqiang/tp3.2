<?php
namespace Api\Controller;
use http\Env\Request;

class IndexController extends ApiBaseController {
    public function _initialize(){
        parent:: _initialize();

    }

    //默认接口
    public function Index(){
        $date = '2021-2';

        $firstday = date('Y-m-01', strtotime($date));

        $time = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
//        $time = strtotime($date);
        echo $time;
    }

    /**
     * 获得推荐商品
     *
     * @access  public
     * @param   string $type 推荐类型，可以是 best, new, hot
     * @return  array
     */
    public function get_index_best($limit = ''){

        $back = array('statuscode' => 200, 'message' => '');
        $skip = I('param.skip/d',0);
        $maxperpage = I('param.maxperpage/d',20);

        $rooturl = C('mainroot').'/';
        $approoturl = C('approot').'/';

        $checklast = isset($_REQUEST['last']) ? intval($_REQUEST['last']) : 0;
        $prevlist=array();
        $viewlist=array();
        $collectlist=array();
        $salelist=array();
        if($checklast===0 and 1===2){
            //取出浏览量前6的商品
            $sql = 'SELECT g.goods_id, g.goods_name,g.click_count, g.goods_name_style,g.sales_volume_base, g.market_price, g.shop_price AS org_price, g.promote_price,g.cang_id, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,  g.exclusive," .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, g.hits, g.collect, RAND() AS rnd " .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";

            $sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 AND g.is_best = "1" ';
            $sql .= ' ORDER BY g.hits desc, g.sort_order, g.last_update DESC limit 10';
            $result = $GLOBALS['db']->getAll($sql);

            foreach ($result AS $idx => $row) {
                $viewlist[$idx]['goods_id']=$row['goods_id'];
            }
            //取出收藏量前6的商品
            $sql = 'SELECT g.goods_id, g.goods_name,g.click_count, g.goods_name_style,g.sales_volume_base, g.market_price, g.shop_price AS org_price, g.promote_price,g.cang_id, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,  g.exclusive," .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, g.hits, g.collect, RAND() AS rnd " .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";

            $sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 AND g.is_best = "1" ';
            $sql .= ' ORDER BY g.collect desc, g.sort_order, g.last_update DESC limit 10';
            $result = $GLOBALS['db']->getAll($sql);
            foreach ($result AS $idx => $row) {
                $collectlist[$idx]['goods_id']=$row['goods_id'];
            }

            //取出销量前6的商品
            $end_date=gmtime();
            $start_date=strtotime('-5 day');
            $where = ' WHERE i.add_time >=' . $start_date . ' AND i.add_time <=' . $end_date
                . ' AND ((i.pay_id = 6 AND i.shipping_status = 2) OR (i.pay_id <> 6 AND i.pay_status = 2))';
            $sql = 'SELECT ecs_goods.goods_number as ku_cun,g.goods_id, g.goods_sn, g.goods_name, g.goods_attr, SUM(g.goods_number) sales_volume, FORMAT(SUM(g.goods_price*g.goods_number), 2) sales_money, '
                . 'FORMAT(AVG(g.goods_price), 2) average_price FROM '
                . $GLOBALS['ecs']->table('order_goods') . ' g, ecs_goods,' . $GLOBALS['ecs']->table('order_info') . ' i ' . $where
                . ' AND g.order_id = i.order_id AND ecs_goods.goods_id=g.goods_id GROUP BY g.goods_id, g.goods_sn, g.goods_name, g.goods_attr'
                . " ORDER BY sales_volume DESC LIMIT 10";
            $result = $GLOBALS['db']->getAll($sql);
            foreach ($result AS $idx => $row) {
                $salelist[$idx]['goods_id']=$row['goods_id'];
            }
        }
        $prevlist = array_merge($salelist,$collectlist,$viewlist);
        $ids=array();
        foreach ($prevlist as $key=>$vo) {
            $ids[]=$vo['goods_id'];
        }
        $arrids=array_unique($ids);
        $arrids=array_values($arrids);
        $prevgoods=array();
        $ids='';
        $f_sql='';
        if($arrids and 1===2){
            foreach ($arrids as $arrid) {
                $ids==''?$ids=$arrid:$ids.=','.$arrid;
            }
            $sql = 'SELECT g.goods_id, g.goods_name,g.click_count, g.goods_name_style,g.sales_volume_base, g.market_price, g.shop_price AS org_price, g.promote_price,g.cang_id, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,  g.exclusive," .
                "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";

            $sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 AND g.is_best = "1" AND g.goods_id IN ('.$ids.') ';
            $sql .= ' ORDER BY field(g.goods_id,'.$ids.')';
            $result = $GLOBALS['db']->getAll($sql);
            foreach ($result AS $idx => $row) {
                if ($row['promote_price'] > 0) {
                    $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    $prevgoods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
                } else {
                    $prevgoods[$idx]['promote_price'] = '';
                }

                $prevgoods[$idx]['id'] = $row['goods_id'];
                $prevgoods[$idx]['name'] = $row['goods_name'];
                $prevgoods[$idx]['brief'] = $row['goods_brief'];
                $prevgoods[$idx]['brand_name'] = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
                $prevgoods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);

                $prevgoods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                    sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
                $prevgoods[$idx]['short_style_name'] = add_style($prevgoods[$idx]['short_name'], $row['goods_name_style']);
                $prevgoods[$idx]['market_price'] = price_format($row['market_price']);
                $prevgoods[$idx]['shop_price'] = price_format($row['shop_price']);
                $prevgoods[$idx]['final_price'] = price_format(get_final_price($row['goods_id'], 1, false));
                $prevgoods[$idx]['is_exclusive'] = is_exclusive($row['exclusive'], get_final_price($row['goods_id']));
                $prevgoods[$idx]['thumb'] = getImage(get_image_path($row['goods_id'], $row['goods_thumb'], true));
                // $goods[$idx]['goods_img']    = getImage(get_pc_url().'/'. get_image_path($row['goods_id'], $row['goods_img'])) ;
                $prevgoods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
                $prevgoods[$idx]['sell_count'] = selled_count($row['goods_id']) + $row['sales_volume_base'];
                $prevgoods[$idx]['pinglun'] = get_evaluation_sum($row['goods_id']);
                $prevgoods[$idx]['count'] = selled_count($row['goods_id']);
                $prevgoods[$idx]['click_count'] = $row['click_count'];
                $sql = "select keyname from ecs_goods_cang where id = $row[cang_id]";
                $prevgoods[$idx]['keyname'] = $GLOBALS['db']->getOne($sql);
            }
            $f_sql=' AND g.goods_id NOT IN ('.$ids.') ';
        }

        //取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
        if (empty($_SESSION['user_id'])) {
            $userRank = 0;
            $discount = 1;
        } else {
            $userRank = $_SESSION[user_info][user_rank];
            $discount = $_SESSION[discount];
        }
        $sql = 'SELECT g.goods_id, g.goods_name,g.click_count, g.goods_name_style,g.sales_volume_base, g.market_price, g.shop_price AS org_price, g.promote_price,g.cang_id, g.subscript_sw, g.subscript_url, g.subscript_pos, g.subscript_width, ' .
            "IFNULL(mp.user_price, g.shop_price * $discount) AS shop_price,  g.exclusive," .
            "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " .
            'FROM ecs_goods AS g ' .
            "LEFT JOIN ecs_member_price AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = " . $userRank;

        $sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 AND g.is_best = "1" '.$f_sql;
        $sql .= ' ORDER BY g.sort_order, g.last_update DESC';
        $sql .= ' limit ' . $skip . ',' . $maxperpage;

//        $result = $GLOBALS['db']->getAll($sql);
        $result = M()->query($sql);

        foreach ($result AS $idx => $row) {
            if ($row['promote_price'] > 0) {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price, 0) : '';
            } else {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id'] = $row['goods_id'];
            $goods[$idx]['name'] = $row['goods_name'];
            $goods[$idx]['brief'] = $row['goods_brief'];
            $goods[$idx]['brand_name'] = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
            $goods[$idx]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);

            $goods[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price'], 0);
            $goods[$idx]['shop_price'] = price_format($row['org_price'], 0);
            $goods[$idx]['final_price'] = price_format(get_final_price($row['goods_id'], 1, false), 0);
            $goods[$idx]['is_exclusive'] = is_exclusive($row['exclusive'], get_final_price($row['goods_id']));
//            var_dump(get_image_path($row['goods_id'], $row['goods_thumb'], true));die;
            $goods[$idx]['thumb'] = $rooturl . get_image_path($row['goods_id'], $row['goods_thumb'], true);
            // $goods[$idx]['goods_img']    = getImage(get_pc_url().'/'. get_image_path($row['goods_id'], $row['goods_img'])) ;
            $goods[$idx]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
            $goods[$idx]['sell_count'] = selled_count($row['goods_id']) + $row['sales_volume_base'];
            $goods[$idx]['pinglun'] = get_evaluation_sum($row['goods_id']);
            $goods[$idx]['count'] = selled_count($row['goods_id']);
            $goods[$idx]['click_count'] = $row['click_count'];

//            $sql = "select keyname,showkeyname,keycolor from ecs_goods_cang where id = $row[cang_id]";
//            $cang_info = $GLOBALS['db']->getRow($sql);
            $cang_info = M('goods_cang')->where('id = ' . $row['cang_id'])->field('keyname,showkeyname,keycolor')->find();

            $goods[$idx]['keyname'] = $cang_info['keyname'];
            $goods[$idx]['showkeyname'] = $cang_info['showkeyname'];
            $goods[$idx]['keycolor'] = $cang_info['keycolor'];

            //角标
            $goods[$idx]['subscript_sw'] = $row['subscript_sw'];
            if($row['subscript_url']!=''){
                $goods[$idx]['subscript_url'] = base_url().trim($row['subscript_url']);
            }else{
                $goods[$idx]['subscript_url'] = '';
            }
            $goods[$idx]['subscript_pos'] = $row['subscript_pos'];
            $goods[$idx]['subscript_width'] = $row['subscript_width'];

            //判断是否有券
            $goods[$idx]['hascoupon'] = false;

        }
        if($prevgoods){
            $data = array_merge($prevgoods,$goods);
        }else{
            $data = $goods;
        }

        $pagecount = count($data);
        if($data){
            $statuscode = 200;
            //如果当页数量小于每页数量，则代表最后一页
            if($pagecount < $maxperpage){
                $message= 'Last';
            }else{
                $message='Success';
            }
        }else{
            $statuscode = 300;
            if($skip==0){
                $message=L('g-nodata');
            }else{
                $message='Last';
            }
        }
        $skip = $skip+$maxperpage;

        $result['statuscode'] = $statuscode;
        $back['message'] = $message;
        $back['statuscode'] = 200;
        $back['skip'] = $skip;
        $back['data'] = $data;
        $this->ajaxReturn($back);
    }
}