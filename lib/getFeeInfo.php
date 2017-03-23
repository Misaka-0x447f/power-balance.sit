<?php

class SIT
{
    //IntelliJ Warning: Unused private field $UA
    //private $UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)
    //               Chrome/57.0.2987.98 Safari/537.36";

    private $UserName;

    private $Password;

    private $Cookies = array();

    private $LoginUrl = "http://my.sit.edu.cn/userPasswordValidate.portal";

    private $Goto = "http://my.sit.edu.cn/loginSuccess.portal";

    private $GotoFailed = "http://my.sit.edu.cn/loginFailure.portal";

    private $IndexUrl = "http://my.sit.edu.cn/index.portal";

    private $EleUrl = "http://card.sit.edu.cn/dk_xxmh.jsp";

    function __construct($UserName, $Password)
    {
        $this->UserName = $UserName;
        $this->Password = $Password;
    }

    private function _login_init()
    {
        $ch = curl_init("http://my.sit.edu.cn");

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true
        ));

        $data = curl_exec($ch);
        curl_close($ch);

        preg_match_all("/(?<=JSESSIONID=).*?(?=;)/", $data, $match);
        $match = $match[0];

        if (count($match) == 2) {
            $cookie = $match[1];
        } else if (count($match) == 1) {
            $cookie = $match[0];
        } else {
            die("获取cookie错误");
        }
        $this->Cookies["JSESSIONID"] = $cookie;
    }

    public function Login()
    {
        $this->_login_init();

        $ch = curl_init($this->LoginUrl);

        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_HTTPHEADER => array(
                "Host" => "my.sit.edu.cn",
                "Origin" => "http://my.sit.edu.cn",
                "Pragma" => "no-cache",
                "Referer" => "http://my.sit.edu.cn/",
                "Upgrade-Insecure-Requests" => 1),
            CURLOPT_COOKIE => "JSESSIONID=" . $this->Cookies["JSESSIONID"] . ";",
            CURLOPT_POSTFIELDS => "Login.Token1=" . $this->UserName . "&Login.Token2=" . $this->Password
                . "&goto=" . urlencode($this->Goto) . "&gotoOnFail=" . $this->GotoFailed
        ));

        $data = curl_exec($ch);
        curl_close($ch);

        preg_match("/(?<=iPlanetDirectoryPro=).*?(?=;)/", $data, $match);
        if (count($match) < 1) {
            die("登录失败。可能用户名或密码错误");
        }
        $this->Cookies["iPlanetDirectoryPro"] = $match[0];
    }

    public function GetIndex()
    {
        $ch = curl_init($this->IndexUrl);

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_HTTPHEADER => array(
                "Host" => "my.sit.edu.cn",
                "Origin" => "http://my.sit.edu.cn",
                "Pragma" => "no-cache",
                "Referer" => "http://my.sit.edu.cn/",
                "Upgrade-Insecure-Requests" => 1),
            CURLOPT_COOKIE => "JSESSIONID=" . $this->Cookies["JSESSIONID"] . "; iPlanetDirectoryPro=" . $this->Cookies["iPlanetDirectoryPro"]
        ));

        $data = curl_exec($ch);

        print_r($data);
    }

    public function GetEle($room)
    {
        $ch = curl_init($this->EleUrl . "?actionType=init&selectstate=on&fjh=" . $room);

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_COOKIE => "JSESSIONID=" . $this->Cookies["JSESSIONID"] . "; iPlanetDirectoryPro=" . $this->Cookies["iPlanetDirectoryPro"]
        ));

        $data = curl_exec($ch);
        preg_match_all("/(?<=center\">)[0-9\.]+/", $data, $match);

        if (count($match) <= 0) {
            die("获取电费失败");
        }
        $match = $match[0];
 
        $rest = $match[1];                // 存款余额
        $butie_rest = $match[2];         // 电补余额(元)
        $total_rest = $match[3];        // 合计余额(元)
        $ele_rest = $match[4];          //可用电量(度)
        return json_encode(array(
            "rest" => $rest,
            "butie_rest" => $butie_rest,
            "total_rest" => $total_rest,
            "ele_rest" => $ele_rest
        ));
    }
}


$c = new SIT("1610400440", "ptunlock233333");

$c->Login();
print_r($c->GetEle(105409));