<?php

//啟動session
@session_start();

class database{

    private $host = "localhost";
    private $dbname = "my_dbtest";
    private $username = "root";
    private $password = "ntueman123456";
	// conn設為靜態變數,避免存入Session時發生錯誤
    public static $conn;                

    /*--------------建立連線----------------*/
    public function Connect(){
        try{
            // MYSQL_ATTR_INIT_COMMAND 設定編碼, self:: 存取class的method or member
            self::$conn = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->username,
                $this->password,array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

            // 設定錯誤訊息提醒功能
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo 'connect success';
        }catch (PDOException $e){
			//連線失敗回傳錯誤訊息
            echo "Connection failed: " . $e->getMessage();
            self::$conn = null;
        }
    }

    /*--------------取得連線對象----------------*/
    public function getConnect(){
        return self::$conn;
    }

    /*--------------關閉連線----------------*/
    public function Close(){
        self::$conn = null;
    }
}

/*------------取得連線過程-------------*/
function ConnectProcess($ON = 0){
    $conn = new database();
    $conn->Connect();

    if($ON == 1)
        return $conn->getConnect();
    else
        return null;
}
?>
