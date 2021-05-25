<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';


//!!! 院內註冊 !!!
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
    //執行新增使用者的方法，直接把整個 $_POST個別的照順序變數丟給方法。
    $del_result = del_video( $_GET['id']);

    if($del_result)
    {
        //若為true 代表刪除成功，印出success
        switch ($_SESSION['rank']) {
            case 0:
                echo "<script>alert('刪除成功'); location.href = '../admin_liu/video_list.php';</script>";
                break;
            case 1:
                echo "<script>alert('刪除成功'); location.href = '../nurse_staff/video_list.php';</script>";
                break;
            case 2:
                echo "<script>alert('刪除成功'); location.href = '../inspector_staff/video_list.php';</script>";
                break;
            case 3:
                echo "<script>alert('刪除成功'); location.href = '../user/video_list.php';</script>";
                break;
        }
    }
    else
    {
        switch ($_SESSION['rank']) {
            case 0:
                echo "<script>alert('刪除失敗'); location.href = '../admin_liu/video_list.php';</script>";
                break;
            case 1:
                echo "<script>alert('刪除失敗'); location.href = '../nurse_staff/video_list.php';</script>";
                break;
            case 2:
                echo "<script>alert('刪除失敗'); location.href = '../inspector_staff/video_list.php';</script>";
                break;
            case 3:
                echo "<script>alert('刪除失敗'); location.href = '../user/video_list.php';</script>";
                break;
        }
    }
}
else
{
    //若為 null 或者 false 代表失敗
    echo 'no';
}
?>
