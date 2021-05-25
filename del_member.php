<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';


//!!! 院內註冊 !!!
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $del_result = del_member( $_POST['username'],$_POST['func']);

    if($del_result)
    {
        //若為true 代表刪除成功，印出success
        echo 'success';
    }
    else
    {
        //若為 null 或者 false 代表失敗
        echo 'fail';
    }
}
else
{
    //若為 null 或者 false 代表失敗
    echo 'no';
}
?>
