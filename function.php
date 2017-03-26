<?php
/* class dataOp
 *  Description
 *      向预先定义的文件写入数据或读取数据。
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
class dataOp{
    private $lengthLimit = 30;
    private $filePointer;
    private $fileName = "eleBalance.csv";
    private function openFile($mode){
        $this->filePointer = fopen($this->fileName,$mode);
        if(!$this->filePointer){
            exit("用户错误：Failed to open file '" . $this->fileName . "' in mode '"."'");
        }
    }
    private function closeFile(){
        if(!fclose($this->filePointer)){
            exit("用户错误：Failed to close file " . $this->fileName);
        }
    }
    public function push($time, $balance){
        $content = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取目标文件。将跳过空行，并且不会将换行符读入。
        array_push($content, $time.",".$balance); //插入最新数据
        if(count($content) >= $this->lengthLimit){
            $pos = 1; //指定写入开始点:要丢弃第一行吗？
        }else{
            $pos = 0;
        }
        $this->openFile("w");
        for(;$pos<count($content);$pos++){
            if(!fprintf($this->filePointer, "%s\n", $content[$pos])){
                echo "用户警告：Failed to write file " . $this->fileName;
            }
        }
        $this->closeFile();
        return true;
    }
    public function ls(){
        $tableOfContent = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取。将跳过空行，并且不会将换行符读入。
        for($pos = 0;$pos < count($tableOfContent);$pos++){
            $tableOfContent[$pos] = explode(",",$tableOfContent[$pos]);
        }
        return $tableOfContent;
    }
}
/* class webOp
 *  Description
 *      通过curl发送简单的web请求。
 *  Interface
 *      post
 *          Description
 *              发送post请求。
 *          Param
 *              仅接受一个数组。
 *              "url" => 要请求的url
 *          Return
 *              如果成功，返回True
 *              如果失败，抛出一个异常。
 */
class webOp{
    private $ch;
    function __construct(){
        $this->ch = curl_init();
    }
    function __destruct()
    {
        curl_close($this->ch);
    }
    public function post($argArray){
        if(count($argArray) == 1 and isset($argArray["url"])){
            return $this->simple_post($argArray["url"]);
        }else{
            throw new Exception("webOp.post异常：没有为此调用设置重载");
        }
    }
    private function simple_post($url){
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ));
        return curl_exec($this->ch);
    }
}