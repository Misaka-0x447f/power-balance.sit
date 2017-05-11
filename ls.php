<?php
/**
 * Created by PhpStorm.
 * User: Aozak
 * Date: 2017/4/23
 * Time: 22:20
 */

require("function.php");

$op = new dataOp();
$dataBase = $op->ls();

?>
<meta charset="utf-8">
<style>
    td{
        text-align:right;
    }
    th{
        text-align:left;
    }
    .balance{
        padding-left:60px;
    }
</style>
<div id="storage">
    Buffer used: <?php echo round(count($dataBase)/$op->lengthLimit*100, 1); echo "%"; ?>
</div>
<table id="tableOfData">
    <tr>
        <th>
            Time
        </th>
        <th>
            Balance
        </th>
    </tr>
    <?php
    for($i=count($dataBase)-1;$i>=0;$i--){
        if($dataBase[$i][1] != $dataBase[$i-1][1]){
            echo "<tr>";
            echo "    <td class=\"time\">";
            echo "        " . date("M. d y H:i's\"", $dataBase[$i][0]);
            echo "    </td>";
            echo "    <td class=\"balance\">";
            echo "        " . $dataBase[$i][1] . " CNY";
            echo "    </td>";
            echo "</tr>";
        }
    }
    ?>
</table>
