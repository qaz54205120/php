<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';

//執行檢查有無使用者的方法。
$check = check_has_username($_POST['huser'],$_POST['func']);

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

?>