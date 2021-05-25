<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';


//!!! 更換負責人 !!!
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $change_result = change_member($_POST['un']);

    if($change_result)
    {
        //若為true 代表新增成功，印出yes
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
