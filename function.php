<?php
/* class dataOp
 *  Description
 *      向预先定义的文件写入数据或读取数据。
 *      每行包括两个数据：记录时间和电费余额。
 *      数据的最大行数已在$lengthLimit中定义。
 *      除特殊说明外
 *          成员函数执行正常均返回功能描述中的值。
 *          成员函数如果遇到意外情况均返回false。
 *  Interface
 *      push
 *          Description
 *              以行(line)为单位接受关于电费的时间和费用数据，然后保存到文件。
 *              如果文件中的记录行数已超过限制，将丢弃最旧的一行，然后保存最新的数据。
 *              每一次push都会检查buffer使用量，如果快炸了就调用private purge();(就是压缩一下，清除无用数据。嗯。)
 *          Param
 *              $time 获得该电费余额数据的时间。应当为标准unix格式。
 *              $balance 电费余额。
 *          Return
 *              如果遇到意外错误，脚本将退出。
 *      ls
 *          Description
 *              列出完整的数据。
 *          Return
 *              如果遇到意外错误，脚本将继续试图返回数据并抛出异常。
 *      getBalance
 *          Description
 *              获取最新电量余额。
 *      getEstBalance
 *          Description
 *              获取估计电量余额。
 *      getEstRem
 *          Description
 *              获取估计剩余时间。
 *      getPrevBalance
 *          Description
 *              获取上一个与当前余额不同的余额。
 *      getBurnRate
 *          Description
 *              获取电量瞬时燃烧速度。"瞬时"由变量$updateInterval定义。
 *      getAvgBurnRate
 *          Description
 *              获取电量7天平均燃烧速度。
 *          Return
 *              如果不足7天，返回最大平均值。
 *
 */

class console{
    static $scriptStartTime;
    static function getRunTimerF(){
        //return a string that formatted as unix format
        return number_format(microtime(true) - console::$scriptStartTime,6);
    }
    static function getAttemptTimesF($attemptCount, $maxAttempt){
        return substr("000000000" . ($attemptCount+1), -strlen((string)(int)$maxAttempt)) . "/" . $maxAttempt;
    }
    static public function getNormalRunLog($attemptCount, $maxAttempt, $realProgress){
        return console::getAttemptTimesF($attemptCount,$maxAttempt) . " " . $realProgress;
    }
    static public function intErr($string){
        if(error_reporting() != 0){
            console::intInfo("Warning: " . $string);
        }//else{
        //enable when debugging
        //console::intInfo("Silenced Warning: " . $string);
        //console::toLogFileWithTimeStamp("Silenced Warning: " . $string);
        //}
        return true;
    }

    static public function intInfo($string){
        $string = str_replace(" ", "&nbsp;", $string);
        echo "<br/>" . console::getRunTimerF() . "&nbsp;" . $string;
        if(error_reporting() != 0) {
            console::toLogFileWithTimeStamp($string);
        }
        return true;
    }

    static public function intStop($string, $count = "***"){
        console::intInfo("" . $count . "/*** " . $string);
        if(error_reporting() != 0) {
            console::toLogFileWithTimeStamp("" . $count . "/*** " . $string);
        }
        exit;
    }

    static function echoNetStatus($ch){
        $sto = curl_getinfo($ch);
        console::intInfo("    Target URL          " . $sto["url"]);
        console::intInfo("    Status code         " . $sto["http_code"]);
        console::intInfo("    Network traffic     " . "↓ " . $sto["size_download"] / 1024 . " KiB "
            . "↑ " . $sto["size_upload"] / 1024 . " KiB ");
        console::intInfo("    Timing              ");
        console::intInfo("        Total time      " . $sto["total_time"]);
        console::intInfo("        Name lookup     " . $sto["namelookup_time"]);
        console::intInfo("        Redirect        " . $sto["redirect_time"]);
        console::intInfo("        Connecting      " . $sto["connect_time"]);
        console::intInfo("        Start transfer  " . $sto["starttransfer_time"]);
        console::intInfo("    Content type        " . $sto["content_type"]);
    }

    static function toLogFileWithTimeStamp($string){
        $logFileOpt = @new fileOpt();
        @$logFileOpt->fileSelect("temp/latest.log");
        if(!@$logFileOpt->fileExist() or @$logFileOpt->fileEmpty()){
            if(!@$logFileOpt->fileExist()){
                @$logFileOpt->fileCreate();
            }
            for($i = 0 ; $i < 30 ; $i++){
                @$logFileOpt->jsonFileWrite((string)($i), "empty");
            }
        }
        for($i = 28 ; $i >= 0 ; $i--){
            //1.read $i
            @$info = $logFileOpt->jsonFileRead((string)$i);
            if($info === false){
                //wipe down log file
                @$logFileOpt->fileUnlink();
            }
            //2.write to $i+1
            @$logFileOpt->jsonFileWrite((string)($i+1), $info);
        }
        @$logFileOpt->jsonFileWrite("0", console::getRunTimerF() . " " . $string);
        return true;
    }
}


class fileOpt{
    /*
     * do fileSelect/Read/Write/Create/Unlink and check Exist
     */
    private $fileName;
    private $filePointer;
    public function fileSelect($fileName){
        $this->fileName = $fileName;
    }
    private function fileOpen($mode){
        /*
         * file read modes:
         *
         * r  Read  only at BOF
         * w  Write only at BOF / Create if not exist / Erase if exist
         * a  Write only at EOF / Create if not exist
         * x  Create and Write only at BOF / On exist return false and show warning
         * c  Create and Write only at BOF
         *
         * r+ Read write at BOF
         * w+ Read write at BOF / Create if not exist / Erase if exist
         * a+ Read write at EOF / Create if not exist
         * x+ Create and Read write at BOF / On exist return false and show warning
         * c+ Create and Read write at BOF
         */
        $this->filePointer = fopen($this->fileName, $mode);
        if(!$this->filePointer){
            if($this->fileExist()){
                console::intErr("failed to open file \"" . $this->fileName . "\" in mode \"".$mode."\"");
            }else{
                console::intErr("failed to open file \"" . $this->fileName . "\" in mode \"".$mode."\": file does not exist");
            }
            return false;
        }
        return $this->filePointer;
    }
    private function fileClose(){
        if(!fclose($this->filePointer)){
            console::intErr("failed to close file \"" . $this->fileName . "\"");
            return false;
        }
        return true;
    }
    public function fileRead(){
        if(!$this->fileExist()){
            console::intErr("\"" . $this->fileName . "\" does not exist in file system");
            return false;
        }
        $file = $this->fileOpen("r");
        if($file === false){
            console::intErr("\"" . $this->fileName . "\" cannot be read from file system.123123");
            return false;
        }
        if($this->fileEmpty()){
            console::intInfo("reading file \"" . $this->fileName . "\" is empty.");
            return "";
        }
        $fileContent = fread($file, filesize($this->fileName));
        if($fileContent === false){
            console::intErr("file content is false");
        }

        $this->fileClose();
        if($fileContent === false){
            console::intErr("failed to read file " . $this->fileName);
        }
        return $fileContent;
    }
    public function jsonFileRead($targetName = false){
        if(($fileReadContent = $this->fileRead()) !== false){
            if($fileReadContent === ""){
                return "";
            }
            if(NULL === json_decode($fileReadContent)){
                console::intErr("json cannot be decode");
                return false;
            }
            if($targetName === false){
                return json_decode($fileReadContent);
            }else{
                return json_decode($fileReadContent)->$targetName;
            }
        }
        return false;
    }
    public function jsonFileOverwrite($contentArray){
        if(!is_object($contentArray) and !is_array($contentArray)){
            console::intErr("Cannot write file \"" . $this->fileName . "\": not an object or array");
            return false;
        }
        $this->fileOverwrite(json_encode($contentArray));
        return true;
    }
    public function jsonFileWrite($name, $value){
        if(($someArray = $this->jsonFileRead()) === false){
            console::intErr("Cannot read file \"" . $this->fileName . "\" in json format: content is \"" . $someArray . "\"");
        }

        @$someArray->$name = (string)$value;
        $this->jsonFileOverwrite($someArray);
        return true;
    }
    public function jsonFileHasOwnProperty($key){
        return !is_null(((array)$this->jsonFileRead())[$key]);
    }
    public function fileOverwrite($content){
        if(!$this->fileExist()){
            return false;
        }
        $file = $this->fileOpen("w");
        if($file === false){
            return false;
        }
        $writeCount = fwrite($file, $content);
        $this->fileClose();
        if($writeCount === false){
            console::intErr("failed to write file " . $this->fileName);
            return false;
        }
        if($writeCount != strlen($content)){
            console::intErr(strlen($content) . "byte write excepted, " . $writeCount . "written");
        }
        return $writeCount;
    }
    public function fileEmpty(){
        clearstatcache();
        if(!$this->fileExist()){
            return false;
        }
        if(filesize($this->fileName) === 0){
            return true;
        }
        return false;
    }
    public function fileExist(){
        clearstatcache();
        return file_exists($this->fileName);
    }
    public function fileCreate(){
        $result = @$this->fileOpen("x"); //error control
        @$this->fileClose();
        return $result;
    }
    public function fileUnlink(){
        return unlink($this->fileName);
    }
    public function fileLockRO(){
        $fp = $this->fileOpen("r+");
        return flock($fp, LOCK_EX);
    }
    public function fileLocked(){
        $fp = @$this->fileOpen("r+");
        @stream_set_blocking($fp, 0);
        if($fp === false){
            //file not exist, terminated
            return false;
        }
        if(!flock($fp, LOCK_EX|LOCK_NB, $wouldBlock)){
            if($wouldBlock){
                // another process holds the lock
                return true;
            }else{
                // couldn't lock for another reason, e.g. no such file
                return false;
            }
        }else{
            // lock obtained
            flock($fp, LOCK_UN);
            return false;
        }
    }
    public function getFileLines(){
        $counter = 1;
        $filePointer = $this->fileOpen("r");
        while(!feof($filePointer)){
            $line = fgets($filePointer, 4096);
            $counter = $counter + substr_count($line, PHP_EOL);
        }
        $this->fileClose();
        return $counter;
    }
    public function getFileContentsByLineNumber($lineNumber){
        //$lineNumber is 1-based
        $file = new SplFileObject($this->fileName);
        $file->seek($lineNumber - 1); // seek to line $lineNumber - 1 (0-based)
        return $file->current();
    }
}


class dataOp{
    private $updateInterval = 86400;
    public  $lengthLimit    = 5000;
    public  $purgeThreshold = 0.9;
    private $filePointer;
    private $fileName       = "eleBalance.csv";
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
    private function balanceLastModifiedAt(){
        $opTable = $this->ls();
        for($i=count($opTable)-1;$i>0;$i--){
            if($opTable[$i][1] != $opTable[$i-1][1]){
                return $opTable[$i][0];
            }
        }
        return false;
    }
    public function push($time, $balance){
        $content = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取目标文件。将跳过空行，并且不会将换行符读入。
        if($content == false){ //epic bug
            $this->openFile("x+");
            $this->closeFile();
        }
        echo "New data: " . $time . " " . $balance . "<br/>";
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
        if($this->purge()){
            echo "Purge complete";
        }else{
            echo "No need to purge";
        }
        return true;
    }
    private function purge(){
        $content = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $temp = []; //分析用的数组
        $writeList = [];
        if(count($content) > $this->lengthLimit * $this->purgeThreshold){
            //判断是不是数字，如果不是就扔了
            for($pos = 0;$pos < count($content);$pos++) {
                array_push($temp, explode(",", $content[$pos]));
                if(!is_numeric($temp[count($temp)-1][0]) || !is_numeric($temp[count($temp)-1][1])){
                    unset($temp[count($temp)-1]);
                }
            }
            unset($content); //$content现在可以扔掉了
            //此时我们得到了一个parsed的数组temp，接下来开始分析以确定哪些数据有用，有用的话，就把源数据里对应的行导入写入队列。
            for($i=count($temp)-1;$i>0;$i--){
                if($temp[$i][1] != $temp[$i-1][1]){
                    array_push($writeList, $temp[$i]);
                }
            }
            array_push($writeList, $temp[0]); //别忘了把第0行写进去，刚才没写
            //啥，你说如果数据大小为0？没数据还净化个毛线
            unset($temp); //temp可以扔了
            //重新格式化为csv
            for($i=0;$i<count($writeList);$i++){
                $writeList[$i] = $writeList[$i][0] . "," . $writeList[$i][1];
            }
            //接下来就可以写入啦，记得要反着写，因为之前翻转过一次
            $this->openFile("w");
            for($pos=count($writeList)-1;$pos>=0;$pos--){
                if(!fprintf($this->filePointer, "%s\n", $writeList[$pos])){
                    echo "用户警告：Failed to write file " . $this->fileName;
                }
            }
            $this->closeFile();
            return true;
        }
        return false;
    }
    public function ls(){
        $tableOfContent = file($this->fileName,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES); //逐行读取。将跳过空行，并且不会将换行符读入。
        $temp = [];
        for($pos = 0;$pos < count($tableOfContent);$pos++) {
            $pushSto = explode(",", $tableOfContent[$pos]);
            if(is_numeric($pushSto[0]) && is_numeric($pushSto[1])){
                array_push($temp, $pushSto);
            }
        }
        return $temp;
    }
    public function getBalance(){
        $opTable = $this->ls();
        if(count($opTable)>=1){
            return $opTable[count($opTable)-1][1];
        }else{
            return false;
        }
    }
    public function getPrevBalance(){
        $opTable = $this->ls();
        for($i=count($opTable)-1;$i>1;$i--){
            if($opTable[$i][1]!=$opTable[$i-1][1]){
                return $opTable[$i-1][1];
            }
        }
        return false;
    }
    public function getBurnRate(){
        $opTable = $this->ls();
        $readCounter = 0;
        $deltaBal = 0;
        $deltaTime = 0;
        for($i=count($opTable)-1;$i>1;$i--){
            if($opTable[$i][1]!=$opTable[$i-1][1]){
                $readCounter++;
                if($readCounter == 1){
                    $deltaBal = $opTable[$i][1];
                    $deltaTime = $opTable[$i][0];
                }elseif($readCounter == 2){
                    $deltaBal = $deltaBal - $opTable[$i][1];
                    $deltaTime = $deltaTime - $opTable[$i][0];
                    return $deltaBal / ($deltaTime / 86400);
                }
            }
        }
        return false;
    }
    public function getAvgBurnRate(){
        $opTable = $this->ls();
        $startTime = time() - 86400 * 7; //截取过去7天的记录作为预估依据

        //消耗速度预估：对消耗量和时间差分别进行累积，再相除得到预计剩余时间。请注意此处时间是负值，消耗速度也是负值。
        $eleSum = 0;    //单位：kWh
        $timeSum = 0;   //单位：kWh/sec --> kWh/days

        $dataSto = Array();

        //获取过去数天的历史记录并稍后进行分析
        for($i=0;$i<count($opTable)-1;$i++){
            //丢弃时间在范围外的记录
            if($opTable[$i][0] >= $startTime){
                //读取所有有价值的点并塞到数据表里
                if($opTable[$i+1][1] != $opTable[$i][1]){
                    array_push($dataSto, Array($opTable[$i+1][0], $opTable[$i+1][1]));
                }
            }
        }

        //处理点并进行累加
        for($i=0;$i<count($dataSto)-1;$i++){
            if($dataSto[$i+1][1] - $dataSto[$i][1] < 0){
                $eleSum += $dataSto[$i+1][1] - $dataSto[$i][1];
                $timeSum += $dataSto[$i+1][0] - $dataSto[$i][0];
            }
        }

        //如果无法估算时间，就返回false等待处理
        if($timeSum == 0 or $eleSum == 0){
            return false;
        }

        //正在计算燃烧速度（顺便转换为千瓦时/天），应该为负值
        return $eleSum / ($timeSum / 86400);
    }
    public function getEstBalance(){
        // 最新余额-燃烧速度的负值*(当前时间-最后更新时间)/86400 = 估计余额
        return $this->getBalance()+$this->getAvgBurnRate()*(time()-$this->balanceLastModifiedAt())/$this->updateInterval;
    }
    public function getEstRem(){
        // 估计余额/燃烧速度的负值 = 估计剩余时间
        if(!$this->getAvgBurnRate() == 0){
            return $this->getEstBalance() / (-$this->getAvgBurnRate());
        }else{
            return false;
        }
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
        console::echoNetStatus($this->ch);
        return curl_exec($this->ch);
    }
}