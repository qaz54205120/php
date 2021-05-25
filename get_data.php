<?php
//載入資料庫與處理的方法
require_once 'dbAPI.php';
require_once 'functions.php';

ConnectProcess();

header('Content-Type: application/json');

$result = array();

if (isset($_SERVER["QUERY_STRING"])){
	$str = $_SERVER["QUERY_STRING"] ;
	//echo substr($str,0,50)."<br><br>";
		
	$json = file_get_contents('php://input');
	$obj = json_decode($json);
	if (isset($obj)){
		$Func = $obj->{'Func'};
		$Loginid = $obj->{'Loginid'};
		$LoginPWD = $obj->{'LoginPWD'};
	}
		
		//$Loginid = "U111111111";
		//$Func = "GetReport";
		
		//echo $Func."   ".$Loginid."   ".$LoginPWD;
		if(isset($Loginid) && $Loginid !=""){
			switch ($Func){
				case "GetReport":
					$result = get_one_all_file_blood($Loginid);
					break;
			}
		}
		
		//json_encode($result);
		/*len = data.length;
        for(var i=0;i<len;i++) {
            date[i] = data[i].date;
            state[i] = data[i].state;
            report[i] = data[i].reportno;
        }
        //console.log(report[0]);
        //console.log(len);
        if(flag == 1) {
            createTable();
            setTable();
            flag = 0;
        }else {
            createTable();
            setTable();
        }*/
		echo (json_encode($result));
		exit;
}

// 依照其需求選擇某function執行
if(isset($_SESSION['user_is_login']) && $_SESSION['user_is_login']){
	//  switch部分皆為網站的funciton
    switch ($_POST['func']){
		//取得客戶個人檢驗報告清單
        case 'get_one_all_file_blood':
            $result = get_one_all_file_blood($_SESSION['login_account']);
            break;
		//取得客戶個人超音波清單
        case 'get_one_all_video':
            $result = get_one_all_video($_SESSION['login_account']);
            break;
		//取得待審核客戶清單
        case 'get_now_registered':
            $result = get_now_registered('A01',$_POST['room']);
            break;
		//取得退件清單(檢驗報告)
        case 'get_returned_file_data':
            $result = get_returned_file_data($_SESSION['worknum']);
            break;
		//取得全部待審核檢驗報告清單
        case 'get_all_wait_exam_file':
            $result = get_all_wait_exam_file();
            break;
		//取得全部超音波清單
        case 'get_all_video':
            $result = get_all_video();
            break;
		//取得全部檢驗報告清單(檢驗所更新)
        case 'get_all_file':
            $result = get_all_file($_SESSION['worknum']);
            break;
		//取得待審查客戶註冊清單
        case 'client_exam':
            $result = get_all_exam_client();
            break;
		//取得所有檢驗報告
        case 'get_wait_upload_file':
            $result = get_all_file($_SESSION['worknum']);
            break;
		//看診列表
        case 'nurse_registered_system':
            $result = get_all_registered($_POST['room'],$_POST['now']);
            break;
		//取得及時看診訊息
        case 'nurse_get_now_registered':
            $result = get_now_registered($_SESSION['worknum'],$_POST['room']);
            break;
			
        case 'information_func':
            $result = get_now_registered('A01',$_POST['room']);
            break;
		//取得所有檢驗報告
        case 'get_file':
            $result = get_file();
            break;
		//檢驗報告退件清單-檢驗所處理中
        case 'get_all_wait_inspection_file':
            $result = get_all_wait_inspection_file();
            break;
        case 'get_super_all_wait_upload_file':
            $result = get_super_all_wait_upload_file();
            break;
        case 'get_super_returned_file_data':
            $result = get_super_returned_file_data();
            break;
        default:
            echo 'wrong way';
            break;
    }

    echo json_encode($result);
}else if($_POST['func'] == 'information_func'){		// 資料看板的執行function
    $result = get_now_registered('A01',$_POST['room']);	//取得現在/等待/下一位的號碼
    echo json_encode($result);
}else if($_POST['func'] == 'nurse_registered_system'){	// 資料看板的執行function
    $result = get_all_registered($_POST['room'],$_POST['now']);		// 取得目前待診客戶資料
    echo json_encode($result);
}else if($_POST['func'] == 'person_report_func'){	// 0630資料看板的執行function
    $result = person_report_func($_POST['room'],$_POST['now']);		// 0630取得目前診間相關資料
    echo json_encode($result);
}
else
{
    //若為 null 或者 false 代表失敗
    echo 'no';
}
