<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';

ob_start(); 
$func=$_POST['func'];
if ($func =="register")
{
	//執行檢查有無使用者的方法。
	$check = check_has_email($_POST['email'],$func);

	if($check)
	{
		//若為true 代表有使用者以重複
		echo 'repeat';
	}
	else
	{
		//若為 null 或者 false 代表沒有使用者，可以註冊
		echo 'norepeat';
		}
}

if ($func =="SendVerPASSMail")
{
	@session_start();
	//開啟email檢測功能
	$_SESSION['email_verify'] = true;
	$_SESSION['forget_mail'] = $_POST['mail'];

	$files = forget_data($_POST['mail']);
	
	if (count($files)>0){
		foreach($files as $row)
		{
			foreach($row as $key => $value)
			{
				if ($key==0)$name=$value;
				if ($key==1)$uid=$value;
				if ($key==2)$email=$value;
				if ($key==3)$pwd=$value;
				 //echo $value."<br />";
			}
		}
		
		$mstr = GetMailStr("SendVerPASSMail", $name,'', $uid, $pwd );
		//寄送Email通知已註冊
		$re= SendMailFun("HNUser", "豐禾婦產科後端系統- 重置密碼", $email ,$name, $mstr);
		
		$re= '已發送密碼重置信件至 ['.$email.']';
		
	}
	else{
		$re = "Error: 查無此Email或帳號尚未審核通過, 請跟診所人員聯繫協助查詢, 謝謝";
	}
	ob_clean(); 
	ob_end_flush(); 
	echo $re;
}

?>
