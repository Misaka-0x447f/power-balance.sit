<?php
/*
 * This program is designed for pulling balance and calculate estimated time to balance used up.
 *
 */
require("function.php");
require("lib/Requests.php");
Requests::register_autoloader(); //init requests.php
$op = new dataOpt();

unset($op);