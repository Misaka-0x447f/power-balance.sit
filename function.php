<?php
/* class dataOpt
 *  Description
 *      向预先定义的文件(eleBalance.data)写入数据或读取数据。
 *      每行包括两个数据：记录时间和电费余额。
 *      数据的最大行数已在$lengthLimit中定义。
 *  Interface
 *      push
 *          Description
 *              以行(line)为单位接受关于电费的时间和费用数据，然后保存到文件。
 *              如果文件中的记录行数已超过限制，将丢弃最旧的一行，然后保存最新的数据。
 *          Param
 *              $time 获得该电费余额数据的时间。应当为标准unix格式。
 *              $balance 电费余额。
 *          Return
 *              如果执行成功，返回true。
 *              如果遇到意外错误，脚本将退出。
 *      ls
 *          Description
 *              列出完整的30个数据。
 *          Return
 *              如果执行成功，返回true。
 *              如果遇到意外错误，脚本将退出。
 */
class dataOpt{
    private $lengthLimit = 30;
    private $f;
    function __construct(){
        $this->f = fopen("eleBalance.data","r+");
        if($this->f == false){
            exit("用户错误：Failed to open file 'eleBalance.data' in mode 'r+'");
        }
    }
    function __destruct(){
        if(fclose($this->f)){
            exit("用户错误：Failed to open file eleBalance.data");
        }
    }
    public function push($time, $balance){
        $content = file($this->f,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取。将跳过空行，并且不会将换行符读入。
        rewind($this->f); //重置文件指针到文件头，准备好写入。
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