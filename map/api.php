<?php
/*
@ name: API Request Data
@ author: api88.net
@ link: https://api88.net
@ requires: https://www.tjit.net
* 输出访客的经纬度JS数据
* 前端直接以JS调用本接口
*/
require 'map.php';
$file =  dirname(__FILE__) . "/data/" . date("Y-m-d") . ".txt";
$data = @file_get_contents($file);
$array = explode("\n", $data);
foreach ($array as $arr => $value) {
    $obg = json_decode($value, true);
    if ($obg['from']['lat']) {
        $body[$arr]['from']['lat'] = $obg['from']['lat'];
        $body[$arr]['from']['lng'] = $obg['from']['lng'];
        $body[$arr]['to']['lat'] = $obg['to']['lat'];
        $body[$arr]['to']['lng'] = $obg['to']['lng'];
    }
}
$data = json_encode($body);
header('content-type:application/javascript');
echo '/*
@ name: API Request Data
@ author: api88.net
@ link: https://api88.net
@ update: ' . date("Y-m-d H:i:s") . '
@ version: 1.0.0.1
@ requires: https://www.tjit.net
*/
var arcData = ' . $data . ';';
