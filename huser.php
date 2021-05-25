<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';
ob_start(); 

//!!! 院內註冊 !!!
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
    
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $add_result = add_user( $_POST['na'],$_POST['un'],$_POST['un'], $_POST['pw'],$_POST['mail'],$_POST['tele'],null ,$_POST['wk'],$_POST['rk'],$_POST['exist'],$_POST['func']);


    if($add_result)
    {
		if ($_POST['func'] =='huser'){
			//取得信件內容
			$mstr = GetMailStr("ReginSideUser", $_POST['na'],$_POST['rk'],$_POST['un'], $_POST['pw'] );
			//寄送Email通知已註冊
			$re= SendMailFun("HNUser", "院內帳號代註冊成功通知", $_POST['mail'] ,$_POST['na'], $mstr);
		}else{
			//取得信件內容
			$mstr = GetMailStr("RegNUser", $_POST['na'],$_POST['rk'],$_POST['un'], $_POST['pw'] );
			//寄送Email通知已註冊
			$re= SendMailFun("HNUser", "帳號註冊成功通知", $_POST['mail'] ,$_POST['na'], $mstr);
		}
		
        //若為true 代表新增成功，印出yes
        $pr_res=  'success';
    }
    else
    {
        //若為 null 或者 false 代表失敗
        $pr_res = 'fail';
    }
}
else
{
    //若為 null 或者 false 代表失敗
    $pr_res = 'no';
}
ob_clean(); 
ob_end_flush(); 

echo $pr_res;
?>
