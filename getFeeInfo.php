<?php
/*
 * This program is designed for returning post request of balance and estimated time to balance used up to webPages
 */

require("function.php");

$op = new dataOp();
$data = Array(
    "bal" => $op->getBalance(),
    "estBal" => $op->getEstBalance(),
    "burnRate" => $op->getBurnRate(),
    "est" => $op->getEstRem(),
    "prevBal" => $op->getPrevBalance(),
    "avgBurnRate" => $op->getAvgBurnRate()
);
foreach ($data as $key => $i){
    if($i == false){
        $data[$key] = "---.--";
    }
}
if($data["prg"] > 1){
    $data["prg"] = 1;
}
echo json_encode($data);
unset($op);

//How did it done?
//It's magic!