<?php
class dataOpt{
    private $lengthLimit = 30;
    private $f;
    public function push($time, $balance);
    public function ls();
    function __construct(){
        $f = fopen("eleBalance.data","r+");
        if($f == false){
            exit("用户错误：failed to open file 'eleBalance.data' in mode 'r+'");
        }
    }
    function __destruct(){
        if(fclose($f)){
            exit("用户错误：failed to open file eleBalance.data");
        }
    }
    public function push($time, $balance){
        //将新数据push到文件中。如果超过限制，丢弃最早的一行。
        $content = file($f);
        array_push($content, array($time, $balance));
        if(count($content) >= $lengthLimit){
            $pos = 1; //指定写入开始点:要丢弃第一行吗？
        }else{
            $pos = 0;
        }
        for(;$pos<count($content);$pos++){
            fprintf($f, "%s\t%s\n", $content[$pos][0], $content[$pos][1]);
        }
        return true;
    }
    public function ls(){
        $tableOfBalance = fscanf($f, "%s\t%s");
    }
}