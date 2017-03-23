<?php
/* class dataOpt
 * 向预先定义的文件(eleBalance.data)写入数据或读取数据。
 * 每行包括两个数据：记录时间和电费余额。
 * 数据的最大行数已在$lengthLimit中定义。
 * 接口函数列表：
 * push -- 将输入插入到文件尾。
 * ls -- 列出数据。
 */
class dataOpt{
    private $lengthLimit = 30;
    private $f;
    function __construct(){
        $this->f = fopen("eleBalance.data","r+");
        if($this->f == false){
            exit("用户错误：failed to open file 'eleBalance.data' in mode 'r+'");
        }
    }
    function __destruct(){
        if(fclose($this->f)){
            exit("用户错误：failed to open file eleBalance.data");
        }
    }
    public function push($time, $balance){
        //将新数据push到文件中。如果超过限制，丢弃最早的一行。
        $content = file($this->f);
        array_push($content, array($time, $balance));
        if(count($content) >= $this->lengthLimit){
            $pos = 1; //指定写入开始点:要丢弃第一行吗？
        }else{
            $pos = 0;
        }
        for(;$pos<count($content);$pos++){
            fprintf($this->f, "%s\t%s\n", $content[$pos][0], $content[$pos][1]);
        }
        return true;
    }
    public function ls(){
        //incomplete
//        $this->tableOfBalance = fscanf($this->f, "%s\t%s");
    }
}