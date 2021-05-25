<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';
ob_start(); 

$json = file_get_contents('php://input');
$obj = json_decode($json);
if (isset($obj)){
	$state = $obj->{'state'};
	$uname = $obj->{'uname'};
	$uid = $obj->{'uid'};
	$upwd = $obj->{'upwd'};
	$umail = $obj->{'uemail'};
	$phone = $obj->{'uphone'};
	$rk = $obj->{'rk'};
	$Func = $obj->{'Func'};
}
	


//!!! 客戶註冊 !!!
if ($Func == "APPregistered"){
	$add_result = add_user( $uname,$uid,$uid, $upwd,$umail,$phone,$state ,null,$rk,null,$Func);
	
	if($add_result){
		//取得信件內容
		$mstr = GetMailStr("RegNUser", $uname,$rk,$uid, $upwd );
		//寄送Email通知已註冊
		$re= SendMailFun("HNUser", "帳號註冊成功通知", $umail ,$uname, $mstr);
		
		//若為true 代表新增成功，印出yes
		$pr_res =  'success';
	}else
	{
		//若為 null 或者 false 代表失敗
		$pr_res =  'fail';
	}
}	=
//取得單個客戶的帳戶資料 
else if ($Func == "GetUserprofile"){
	
    //宣告要回傳的結果
    $result = array();

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `account` WHERE `username` = :uid;");
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
		echo (json_encode($result));
		exit;
    }catch (PDOException $e){
        echo "get one client data failed: " . $e->getMessage();
    }

    //回傳結果
    return $result;

}
else
{
    //若為 null 或者 false 代表失敗
    $pr_res =  'no';
}

ob_clean(); 
ob_end_flush(); 

echo $pr_res ;
?>
