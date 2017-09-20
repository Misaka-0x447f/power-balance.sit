<meta charset="utf-8">
<style>
    html{
        font-family: Consolas, sans-serif;
    }
</style>
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

if(!$op->Login()){
    console::intStop("Login failure");
}

if(!$data = $op->GetEle(105409)){
    console::intStop("Data load failure");
}

//decode data
$data = json_decode($data);

unset($op);

$op = new dataOp();
$currTime = time();
if(gettype($data->ele_rest) == "string" and is_numeric($data->ele_rest) and is_numeric($currTime)){
    $op->push($currTime,$data->ele_rest);
}else{
    console::intStop("Write failure: data type dismatching");
}
