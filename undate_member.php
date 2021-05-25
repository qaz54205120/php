<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';
ob_start(); 

//!!! 院內註冊 !!!
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $update_result = update_member( $_POST['na'],$_POST['un'],$_POST['un'],$_POST['pw'],$_POST['mail'] ,$_POST['tele'],$_POST['wk'],$_POST['rk'],$_POST['func']);

    if($update_result)
    {
        //若為true 代表新增成功，印出yes
        $pr_res =  'success';
    }
    else
    {
        //若為 null 或者 false 代表失敗
        $pr_res =  'fail--'.$update_result;
    }
}else if($_POST['func'] == 'forget'){
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $update_result = update_member( $_POST['na'],$_POST['un'],'',$_POST['pw'],'','','','',$_POST['func']);

    if($update_result)
    {
        //若為true 代表新增成功，印出yes
		//取得信件內容
		$mstr = GetMailStr("UpdateUserPWD", $_POST['na'],'',$_POST['un'], $_POST['pw'] );
		//寄送Email通知已註冊
		$re= SendMailFun("UpdateUserPWD", "密碼重新設定完成", $_POST['mail'] ,$_POST['na'], $mstr);
        $pr_res =  'success';
    }
    else
    {
        //若為 null 或者 false 代表失敗
        $pr_res =  'fail';
    }
}
else
{
    //若為 null 或者 false 代表失敗
    $pr_res =   'no';
}
ob_clean(); 
ob_end_flush(); 

echo $pr_res ;
?>
