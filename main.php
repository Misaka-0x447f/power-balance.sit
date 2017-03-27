<?php
/*
 * This program is designed for returning post request of balance and estimated time to balance used up to webPages
 */

require("function.php");

$op = new dataOp();
if($op->getEstRem() == false){
    echo $op->getBalance() . ",false";
}else{
    echo $op->getBalance() . "," . $op->getEstRem();
}
unset($op);

//How did it done?
//It's magic!