<?php
@session_start();

// 如果未登入的話,回登入畫面
if(!isset($_SESSION['user_is_login']) || !$_SESSION['user_is_login']){
    //直接轉跳到 login.php
    header("Location: ../login.php");
	
}/*else{
	//檢查權限跟所在頁面是否相符__20200922Cino
	$verarr= explode("/",$_SERVER['REQUEST_URI']);;
	//echo $verarr[1];
	//exit;
	//判斷權限等級，直接轉跳到 index.php 後端首頁
    switch ($_SESSION['rank']){
        case 0:
			if($verarr[1] == "admin_liu"){
				//權限不符轉跳到 login.php
				header("Location: index.php");
			}else{
								header("Location: ../login.php");

			}
				break;
        case 1:
			if($verarr[1]!=="nurse_staff"){
				//權限不符轉跳到 login.php
				header("Location: ../login.php");
			}
				break;
        case 2:
			if($verarr[1]!=="inspector_staff"){
				//權限不符轉跳到 login.php
				header("Location: ../login.php");
			}
				break;
        
        case 3:			
			if($verarr[1]!=="user"){
				//權限不符轉跳到 login.php
				header("Location: ../login.php");
			}
				break;
            
        default:
            echo 'error no rank';
    }*/
	//exit;


?>
