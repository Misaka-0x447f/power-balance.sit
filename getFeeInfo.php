<?php
/*
 * This program is designed for returning post request of balance and estimated time to balance used up to webPages
 */

require("function.php");

$op = new dataOp();
$data = Array(
    "bal" => $op->getBalance(),
    "est" => $op->getEstRem(),
    "prg" => 0
);
if($data["est"] == false){
    $data["est"] = "---";
    $data["prg"] = $data["bal"] / 100; //满值为100度
}else{
    $data["prg"] = $data["est"] / 30; //满值为30天
}
if($data["prg"] > 1){
    $data["prg"] = 1;
}
echo json_encode($data);
unset($op);

//How did it done?
//It's magic!