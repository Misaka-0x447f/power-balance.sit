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
    private $lengthLimit = 3000;
    private $filePointer;
    private $fileName = "eleBalance.csv";
    private function openFile($mode){
        $this->filePointer = fopen($this->fileName,$mode);
        if(!$this->filePointer){
            echo "用户警告：Failed to open file '" . $this->fileName . "' in mode '".$mode."'";
        }
    }
    private function closeFile(){
        if(!fclose($this->filePointer)){
            echo "用户警告：Failed to close file " . $this->fileName;
        }
    }
    public function push($time, $balance){
        $content = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取目标文件。将跳过空行，并且不会将换行符读入。
        if($content == false){ //epic bug
            $this->openFile("x+");
            $this->closeFile();
        }
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
    public function getBalance(){
        $opTable = $this->ls();
        return $opTable[count($opTable)-1][1];
    }
    public function getEstRem(){
        $opTable = $this->ls();
        $startTime = time() - 86400 * 3; //截取过去3天的记录作为预估依据

        /*
         * 数据结构：
         * tableSto = {
         *  {
         *      {timeA1,eleBalA1},
         *      {timeA2,eleBalA2},
         *      ......
         *      {timeAX,eleBalAX}
         *  },
         *  {
         *      {timeB1,eleBalB1},
         *      {timeB2,eleBalA2}
         *      ......
         *  }
         * }
         */

        $tableSto = Array(
            Array(

            )
        );

        //获取过去数天的历史记录并根据充值情况截断存储进行分析
        for($i=0;$i<count($opTable);$i++){
            //丢弃时间在范围外的记录
            if($opTable[$i][0] > $startTime){
                //如果不是第一条记录，就判断一下是不是该截断，否则直接推到第一列记录中
                if($i != 0){
                    //该截断的条件：这条记录的余额严格比上一条的余额高，即发生充值情况
                    if($opTable[$i][1] > $opTable[$i-1][1]){
                        array_push($tableSto,Array()); //先推一张新子表进主表，2行后再推新数据
                    }
                }
                array_push($tableSto[count($tableSto)-1],Array($opTable[$i][0],$opTable[$i][1])); //推一组数据进最新的子表
            }
        }

        //消耗速度预估：对消耗量和时间分别进行累积，再相除得到预计剩余时间。请注意此处时间是负值，消耗速度也是负值。
        $eleSum = 0;    //单位：kWh
        $timeSum = 0;   //单位：kWh/sec --> kWh/days

        //遍历所有子表
        for($i=0;$i<count($tableSto);$i++){
            $timeSum += ($tableSto[$i][0][0] - $tableSto[$i][count($tableSto[$i])-1][0]);
            $eleSum  += ($tableSto[$i][0][1] - $tableSto[$i][count($tableSto[$i])-1][1]);
            /*不需要遍历所有数据对，只需处理头尾
            //遍历所有数据对
            for($j=0;$j<count($tableSto[$i]) - 1;$i++){ //注意此处有-1;i为子表级别，j为数据对级别
                $deltaTime = $tableSto[$i][$j][0] - $tableSto[$i][$j+1][0]; // ΔT = t1 - t2
                $deltaEle  = $tableSto[$i][$j][1] - $tableSto[$i][$j+1][1]; // ΔEle = Ele1 - Ele2
                $timeSum  += $deltaTime;
                $eleSum   += $deltaEle;
            }
            */
        }

        //如果无法估算时间，就返回false等待处理
        if($timeSum == 0 or $eleSum == 0){
            return false;
        }

        //正在计算燃烧速度（顺便转换为千瓦时/天）
        $burnRate = $eleSum / ($timeSum / 86400);

        // 最新剩余量/燃烧速度的负值 = 剩余时间
        return $opTable[count($opTable)-1][1] / (-$burnRate);
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