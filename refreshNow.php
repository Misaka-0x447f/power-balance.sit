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

//3 times trial to login
for($i=3;$i>0;$i--){
    if($op->Login()){
       break;
    }
}

//3 times trial to get data
for($i=3;$i>0;$i--){
    $data = $op->GetEle(105409);
    if($data != false){
        break;
    }
}

//decode data
$data = json_decode($data);

unset($op);

$op = new dataOp();
$op->push(time(),$data->ele_rest);

//打印目前已有数据用于诊断
echo "dataInTable<br/>";
print_r($op->ls());
unset($op);