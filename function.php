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
 *              列出完整的数据。
 *          Return
 *              如果执行成功，返回得到的数据。
 *              如果遇到意外错误，脚本将退出。
 */
class dataOpt{
    private $lengthLimit = 30;
    private $filePointer;
    private $fileName = "eleBalance.data";
    function __construct(){
        $this->filePointer = fopen($this->fileName,"a+");
        if($this->filePointer == false){
            exit("用户错误：Failed to open file '" . $this->fileName . "' in mode 'a+'");
        }
    }
    function __destruct(){
        if(fclose($this->filePointer)){
            exit("用户错误：Failed to open file " . $this->fileName);
        }
    }
    public function push($time, $balance){
        rewind($this->filePointer); //重置文件指针到文件头。
        $content = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取。将跳过空行，并且不会将换行符读入。
        rewind($this->filePointer); //重置文件指针到文件头，准备好写入。
        array_push($content, array($time, $balance));
        if(count($content) >= $this->lengthLimit){
            $pos = 1; //指定写入开始点:要丢弃第一行吗？
        }else{
            $pos = 0;
        }
        for(;$pos<count($content);$pos++){
            if(fprintf($this->filePointer, "%s\t%s\n", $content[$pos][0], $content[$pos][1]) == 0){
                echo "用户警告：Failed to write file " . $this->fileName;
            }
        }
        return true;
    }
    public function ls(){
        rewind($this->filePointer); //重置文件指针到文件头。
        $tableOfContent = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取。将跳过空行，并且不会将换行符读入。
        for($pos = 0;$pos < count($tableOfContent);$pos++){
            $tableOfContent[$pos] = explode("\t",$tableOfContent[$pos]);
        }
        return $tableOfContent;
    }
}