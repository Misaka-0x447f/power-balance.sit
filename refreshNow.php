<?php
/**
 * Created by IntelliJ IDEA.
 * User: Aozak
 * Date: 2017/3/24
 * Time: 15:28
 *
 * This program will refresh balance now.
 */
require("function.php");
require("lib/getFeeInfo.php");

//拉取电费余额的json
$op = new SIT("1610400440", "ptunlock233333");

//10 times trial to login
for($i=10;$i>0;$i--){
    if($op->Login()){
       break;
    }
    if($i == 1){
        exit("无法完成登录。<br/>");
    }
    echo "无法完成登录，正在重试<br/>";
    sleep(10);
}

//10 times trial to get data
for($i=10;$i>0;$i--){
    $data = $op->GetEle(105409);
    if($data != false){
        break;
    }
    if($i == 1){
        exit("无法获取数据。<br/>");
    }
    echo "无法获取数据，正在重试<br/>";
    sleep(10);
}

//decode data
$data = json_decode($data);

unset($op);

$op = new dataOp();
$op->push(time(),$data->ele_rest);