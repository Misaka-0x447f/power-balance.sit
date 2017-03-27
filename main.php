<?php
/*
 * This program is designed for returning post request of balance and estimated time to balance used up to webPages
 */

//require everything
require("function.php");

$op = new dataOp();
echo $op->getBalance() . "," . $op->getEstRem();
unset($op);