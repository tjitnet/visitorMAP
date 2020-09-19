<?php
/*
@ name: MAP API Server
@ author: api88.net
@ link: https://api88.net
@ requires: https://www.tjit.net
* 获取每个独立访客的IP地理位置的经纬度保存到txt文件
*/

$server_ip_arr = [ //定义你服务器公网IP的地理位置经纬度
    "server_lat" => 31.230355,
    "server_lng" => 121.473710
];
$API_key = '88888888888888888'; //接口互联API_KEY
$server_ip = '114.114.114.114'; //服务器公网IP地址
$ifcdn = true;  //当前网站是否启用了CDN，否false

$ret = map_add($ifcdn, $server_ip_arr, $server_ip, $API_key); //执行
if ($_GET['type'] == 'map') {
    var_dump($ret); // 浏览器地址访问 http://xxx.com/map.php?type=map  调试并打印结果
}

function map_add($ifcdn = false, $server_ip_arr = null, $server_ip = "", $API_key = "")
{
    $file =  dirname(__FILE__) . "/data/" . date("Y-m-d") . ".txt";
    if (!file_exists($file)) {
        @mkdir(dirname(__FILE__) . "/data", 0777, true);
        @file_put_contents($file, '');
    }
    $put_data = @file_get_contents($file);

    $use_ip = Get_User_ip();
    if (strpos($put_data, $use_ip) === false) {
        $data = map_curl_get($API_key, $use_ip);
        $arr = json_decode($data, true);
        $lat = $arr["data"]["result"]["location"]["lat"];
        $lng = $arr["data"]["result"]["location"]["lng"];
    } else {
        return '用户数据已存在';
    }

    $cdn_ip = $_SERVER['REMOTE_ADDR'];
    if ($ifcdn == true and $cdn_ip != $server_ip) {
        $server_ip = $cdn_ip;
        if (strpos($put_data, $cdn_ip) === false) {
            $data = map_curl_get($API_key, $cdn_ip);
            $arr = json_decode($data, true);
            $to_lat = $arr["data"]["result"]["location"]["lat"];
            $to_lng = $arr["data"]["result"]["location"]["lng"];
        } else {
            $arr = map_obg_data($put_data, $cdn_ip);
            $to_lat = $arr["to"]["lat"];
            $to_lng = $arr["to"]["lng"];
        }
    } else {
        $to_lat = $server_ip_arr["server_lat"];
        $to_lng = $server_ip_arr["server_lng"];
        $server_ip = 'server';
    }

    if (!empty($lat) and !empty($lng) and !empty($to_lat) and !empty($to_lng)) {
        $data_arr = array(
            "from" => array(
                "lat" => $lat,
                "lng" => $lng,
                "uip" => $use_ip
            ),
            "to" => array(
                "lat" => $to_lat,
                "lng" => $to_lng,
                "ip" => $server_ip
            ),
        );
        $put_file = json_encode($data_arr);
        if (strpos($put_data, $put_file) === false) {
            if (file_put_contents($file, $put_file . "\n", FILE_APPEND)) {
                return $put_file;
            } else {
                return '数据写入失败';
            }
        } else {
            return '数据重复未保存';
        }
    }
}

function map_curl_get($Api_key, $ip)
{
    $url = "https://api88.net/ips?key={$Api_key}&ip=" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function Get_User_ip()
{
    if ($_SERVER['HTTP_CLIENT_IP']) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif ($_SERVER['REMOTE_ADDR']) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function map_obg_data($data, $str)
{
    $array = explode("\n", $data);
    foreach ($array as $value) {
        if (strpos($value, $str) !== false) {
            $obg = $value;
            break;
        }
    }
    return json_decode($obg, true);
}
