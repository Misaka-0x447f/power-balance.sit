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
$op->Login();
print_r($op->GetEle(105409));

//$op = new dataOp();
//unset($op);