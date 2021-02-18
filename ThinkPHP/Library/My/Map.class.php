<?php
/*
*2014年7月17日 14:23:35
*ZhangHaiChao
*根据经纬度计算距离
*/
namespace My;
class Map{
    public function range($u_lat,$u_lon,$list){
        /*
        *u_lat 用户纬度
        *u_lon 用户经度
        *list sql语句
        */
        if(!empty($u_lat) && !empty($u_lon)){
            foreach ($list as $row) {
                $dist_m = $this->nearby_distance($u_lat, $u_lon, $row['latitude'], $row['longitude']);
                $row['dist'] = round($dist_m, 0);
                $res[] = $row;
            }
            if (!empty($res)) {
                foreach ($res as $user) {
                    $ages[] = $user['dist'];
                }
                array_multisort($ages, SORT_ASC, $res);
                return $res;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //计算经纬度两点之间的距离
    public function nearby_distance($from,$to) {
        $EARTH_RADIUS = 6378.137;
        $radLat1 = $this->rad($from['lat']);
        $radLat2 = $this->rad($to['lat']);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($from['lng']) - $this->rad($to['lng']);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s1 = $s * $EARTH_RADIUS;
        $s2 = round($s1 * 10000) / 10;
        return $s2;
        //print_r($s2);
    }

    private function rad($d) {
        return $d * 3.1415926535898 / 180.0;
    }

    /**
     * 判断一个坐标是否在一个多边形内（由多个坐标围成的）
     * 基本思想是利用射线法，计算射线与多边形各边的交点，如果是偶数，则点在多边形外，否则
     * 在多边形内。还会考虑一些特殊情况，如点在多边形顶点上，点在多边形边上等特殊情况。
     * @param $point 指定点坐标
     * @param $pts 多边形坐标 顺时针方向
     */
    public function ispointinpolygon($point, $pts) {
        $N = count($pts);
        $boundOrVertex = true; //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
        $intersectCount = 0;//cross points count of x
        $precision = 2e-10; //浮点类型计算时候与0比较时候的容差
        $p1 = 0;//neighbour bound vertices
        $p2 = 0;
        $p = $point; //测试点

        $p1 = $pts[0];//left vertex
        for ($i = 1; $i <= $N; ++$i) {//check all rays
            // dump($p1);
            if ($p['lng'] == $p1['lng'] && $p['lat'] == $p1['lat']) {
                return $boundOrVertex;//p is an vertex
            }

            $p2 = $pts[$i % $N];//right vertex
            if ($p['lat'] < min($p1['lat'], $p2['lat']) || $p['lat'] > max($p1['lat'], $p2['lat'])) {//ray is outside of our interests
                $p1 = $p2;
                continue;//next ray left point
            }

            if ($p['lat'] > min($p1['lat'], $p2['lat']) && $p['lat'] < max($p1['lat'], $p2['lat'])) {//ray is crossing over by the algorithm (common part of)
                if($p['lng'] <= max($p1['lng'], $p2['lng'])){//x is before of ray
                    if ($p1['lat'] == $p2['lat'] && $p['lng'] >= min($p1['lng'], $p2['lng'])) {//overlies on a horizontal ray
                        return $boundOrVertex;
                    }

                    if ($p1['lng'] == $p2['lng']) {//ray is vertical
                        if ($p1['lng'] == $p['lng']) {//overlies on a vertical ray
                            return $boundOrVertex;
                        } else {//before ray
                            ++$intersectCount;
                        }
                    } else {//cross point on the left side
                        $xinters = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];//cross point of lng
                        if (abs($p['lng'] - $xinters) < $precision) {//overlies on a ray
                            return $boundOrVertex;
                        }

                        if ($p['lng'] < $xinters) {//before ray
                            ++$intersectCount;
                        }
                    }
                }
            } else {//special case when ray is crossing through the vertex
                if ($p['lat'] == $p2['lat'] && $p['lng'] <= $p2['lng']) {//p crossing over p2
                    $p3 = $pts[($i+1) % $N]; //next vertex
                    if ($p['lat'] >= min($p1['lat'], $p3['lat']) && $p['lat'] <= max($p1['lat'], $p3['lat'])) { //p.lat lies between p1.lat & p3.lat
                        ++$intersectCount;
                    } else {
                        $intersectCount += 2;
                    }
                }
            }
            $p1 = $p2;//next ray left point
        }

        if ($intersectCount % 2 == 0) {//偶数在多边形外
            return false;
        } else { //奇数在多边形内
            return true;
        }
    }


    /*
    数据示例

    $point=[
            'lng'=>121.427417,
            'lat'=>31.20357
        ];
        $point=[
            'lng'=>121.257428,
            'lat'=>31.222481
        ];
        $arr=[
            [
                'lng'=>121.23036,
                'lat'=>31.218609
            ],
            [
                'lng'=>121.233666,
                'lat'=>31.210579
            ],
            [
                'lng'=>121.247177,
                'lat'=>31.206749
            ],
            [
                'lng'=>121.276353,
                'lat'=>31.190811
            ],
            [
                'lng'=>121.267442,
                'lat'=>31.237383
            ],
        ];

        $from['lng']= 109.076381;
        $from['lat']= 34.38805;
        $to['lng']= 109.072644;
        $to['lat']= 34.37077;
     */
}
?>