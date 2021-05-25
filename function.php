<?php
include_once 'dbAPI.php';

	// Import PHPMailer classes into the global namespace
	// These must be at the top of your script, not inside a function
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

// 設定時區
date_default_timezone_set("Asia/Taipei");
//啟動session
@session_start();
ob_start(); 

$conn = new database();     //建立資料庫物件
$conn->Connect();   //進行資料庫連線
$connect = $conn->getConnect(); //取得連線對象

//chec file folder exists, if not then build
$fileroot_path = $_SERVER['DOCUMENT_ROOT']."/backend_web/UploadFiles/";
//正式機---$fileroot_path = $_SERVER['DOCUMENT_ROOT']."/UploadFiles/";
check_filefolder($fileroot_path);
	
$json = file_get_contents('php://input');
$obj = json_decode($json);
$funres = "";
if (isset($obj)){
	$Loginid ="";
	$Func = $obj->{'Func'};
	if ($Func !="APPregistered"){
		$Loginid = $obj->{'Loginid'};
		$LoginPWD = $obj->{'LoginPWD'};
	
	//$Loginid = "U111111111";
	//$Func = "GetReport";

	//echo $Func."   ".$Loginid."   ".$LoginPWD;
		if($Loginid !=""){
			switch ($Func){
				case "GetReport":
					$result = get_one_all_file_blood($Loginid);
					$funres = json_encode($result);
					break;
				case "GetUserprofile":
					$result = get_one_client_data($Loginid);
					$funres = json_encode($result);
					break;
				case "verifylogin":
					$state = $obj->{'state'};
					$result = verify_user($Loginid, $LoginPWD,$state);
					//if ($result== 3) 
					$funres = $result;
					//[{"reportno":"2","0":"2","reportname":"2020-12-18-15-43-18"}]
					break;
				case "APPchangeuserinfo":
					$uemail = $obj->{'uemail'};
					$state = $obj->{'state'};
					$uphone = $obj->{'uphone'};	
					$result = update_member( $Loginid ,$LoginPWD,$uemail ,$uphone,'APPchangeuserinfo');
					

					if ($result) $funres ='success' ;
					else $funres = "faild";
					break;
			}
			
			ob_clean(); 
			ob_end_flush(); 
			echo $funres;
		}
		else {
			echo "faild -- no loginid.";
		}

		exit;
	}
}


function check_filefolder($foldername){
	if (!file_exists($foldername)) {
		mkdir($foldername, 0777, true);
		//echo "123".$foldername;
	}
}

// 新增客戶  	
function add_user($name,$uid, $username ,$password,$email,$telephone,$state,$workplace, $rank,$exist,$func){
    //宣告要回傳的結果
    $result = null;
    //先把密碼加密
    $password = hash('sha256', $password);

    try {

        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("INSERT INTO `account` (`name` ,`uid`,`username` , `password`,`email`,`telephone`, `authority`) VALUE (:name,:uid,:username , :password, :email,:telephone,:rank);");
        $sth->bindParam(':name', $name, PDO::PARAM_STR, 100);
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->bindParam(':username', $username, PDO::PARAM_STR, 12);
        $sth->bindParam(':password', $password, PDO::PARAM_STR, 256);
        $sth->bindParam(':email', $email, PDO::PARAM_STR, 256);
        $sth->bindParam(':telephone', $telephone, PDO::PARAM_STR, 256);
        $sth->bindParam(':rank', $rank, PDO::PARAM_INT);

        $sth->execute();

        $row = $sth->rowCount();    // 改動筆數
        if ($row == 1)              // 新增一筆,改動單筆
            $result = true;
        else                        //無改動筆數,代表新增失敗
            $result = false;
			
		if($func == 'APPregistered' && $result){

            $sth = $GLOBALS['connect']->prepare("INSERT INTO `client_info` (`client_uid`,`clientstate`) VALUE (:client_uid,:clientstate);");
            $sth->bindParam(':client_uid', $uid, PDO::PARAM_STR, 12);
            $sth->bindParam(':clientstate', $state, PDO::PARAM_INT);

            $sth->execute();

            $row = $sth->rowCount();    // 改動筆數
            if ($row == 1)              // 新增一筆,改動單筆
                $result = true;
            else                        //無改動筆數,代表新增失敗
                $result = false;

        }

    }catch (PDOException $e){
        $result= "add_user failed: " . $e->getMessage();
		echo $result;
    }
    //回傳結果
    return $result;
}

/*-------------------登入檢驗使用者身分--------------------------*/
function verify_user($username,$password,$state){

    //結果輸出顯示
    $result = null;
    //取得連線對象(試用session失敗)
    //$connect = $_SESSION['conn']->getConnect();
    //初設使用者尚未連線
    $_SESSION['user_is_login'] = false;
    $password = hash('sha256',$password);

    try {
        $sth = $GLOBALS['connect']->prepare("SELECT `uid`,`name`,`authority` FROM account WHERE `uid` = :username AND `password` = :password;");
        $sth->bindParam(':username', $username, PDO::PARAM_STR, 12);
        $sth->bindParam(':password', $password, PDO::PARAM_STR, 256);
        $sth->execute();
        //$count = $sth->fetchColumn(0);
        //echo 'count'. $count;
        $user =$sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料
        //echo print_r($user);
        // 判斷是否有資料
        if ($user) {
            $_SESSION['rank'] = $user['authority'];
            $result = $_SESSION['rank'];
			$uuname="";
            if ($result < 3 && $result >= 0) {

                $sth = $GLOBALS['connect']->prepare("SELECT `uid`,`name`,`authority`,`workplace`,`placename` FROM account LEFT JOIN `clinic_info` ON account.uid = clinic_uid LEFT JOIN `workplace` ON clinic_info.workplace = workplace.no WHERE `uid` = :username AND `exist` = 1;");
                $sth->bindParam(':username', $username, PDO::PARAM_STR, 12);

                $sth->execute();

                $huser = $sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料

            }else 
				if($result == 3){
                $sth = $GLOBALS['connect']->prepare("SELECT `uid`,`name`,`authority` FROM account LEFT JOIN `client_info` ON account.uid = client_uid WHERE `uid` = :username AND `clientstate` = 1;");
                $sth->bindParam(':username', $username, PDO::PARAM_STR, 12);

                $sth->execute();

                $client = $sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料

                if(!$client)
                    $result = -1;
                else{
                    $_SESSION['user_is_login'] = true;
                    $_SESSION['login_account'] = $user['uid'];
                    $_SESSION['login_name'] = $user['name'];
					$uuname = $user['name'];
                }
            }

			$result = "[{'reportno':'user','username':'".$uuname."'}]";

        } else {
            $result = -1;
        }
        //print_r($count);  //輸出測試用
    }catch (PDOException $e){
        echo "verify_user failed: " . $e->getMessage();
    }

    //echo 'username'.$username;
    //print_r($user);
    //echo 'result:'.$result;

    return $result;
}

/*---------------nurse function----------------------*/

//檢驗報告所有取得
function get_file(){
    //宣告空的陣列
    $datas = array();
    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `inspectreport` LEFT JOIN `workplace` ON inspectreport.inspectionid = workplace.no LEFT JOIN `account` ON inspectreport.subjectid = account.uid ORDER BY `date` DESC, `state` ASC;");
        $sth->execute();

        $datas = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        echo "get_file failed: " . $e->getMessage();
    }

    return $datas;
}

function client_account_edit($uid,$state){
ob_start(); 
    // 執行結果
    $result = null;
    try {
        if($state == 1) {       // 審查通過,則更新狀態
            //PDO 語法
            $sth = $GLOBALS['connect']->prepare("UPDATE `client_info` SET `clientstate`=:state WHERE `client_uid`=:uid;");
            $sth->bindParam(':uid',$uid,PDO::PARAM_STR,12);
            $sth->bindParam(':state',$state,PDO::PARAM_INT);
            $sth->execute();

            $result = true;
        }else if($state == 0){      //審查不合格,則刪除
						
            //PDO 語法
            $sth = $GLOBALS['connect']->prepare("UPDATE `client_info` SET `clientstate`= 3 WHERE `client_uid`=:uid;");
            $sth->bindParam(':uid',$uid,PDO::PARAM_STR,12);
            //$sth->bindParam(':state',$state,PDO::PARAM_INT);
            $sth->execute();

            $result = false;
        }
    }catch (PDOException $e){
        echo "client_account_edit failed: " . $e->getMessage();
    }

	SendMailtoClient($uid, $result);
	ob_clean(); 
	ob_end_flush();
	
    return $result;
}

//取得及時看診訊息
function get_now_registered($worknum = 'A01',$room)
{

    $_SESSION['selectCLi'] = $worknum;

    //建立陣列
    $datas = array();
    $datas = array();
    //宣告要回傳的結果
    $result = null;
    try {
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `treat` WHERE `clinicid` = :worknum AND `roomid` = :room;");
        $sth->bindParam(':worknum',$worknum,PDO::PARAM_STR,10);
        $sth->bindParam(':room',$room,PDO::PARAM_INT);
        $sth->execute();

        $datas = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        echo "get_now_registered failed: " . $e->getMessage();
    }

    //回傳結果
    return $datas;
}

// 看診列表
function get_all_registered($room,$now=null)
{
    //建立陣列
    $datas = array();
    //宣告要回傳的結果
    $result = null;

    $create_date = date("Y-m-d");

    try {
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `treatlist` WHERE `roomid` = :room AND `date`= :datenow ;");
        $sth->bindParam(':room', $room, PDO::PARAM_INT);
        $sth->bindParam(':datenow', $create_date, PDO::PARAM_STR, 40);

        $sth->execute();
        $datas = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        echo "get_all_registered failed: " . $e->getMessage();
    }

    //回傳結果
    return $datas;
}


// 0630診間資訊，含科別醫師跟診
function person_report_func($room,$now=null)
{
    //建立陣列
    $datas = array();
    //宣告要回傳的結果
    $result = null;

    $create_date = date("Y-m-d");

    try {
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `clinicdata` WHERE `roomid` = :room ;");
        $sth->bindParam(':room', $room, PDO::PARAM_INT);
        
        $sth->execute();
        $datas = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        echo "person_report_func failed: " . $e->getMessage();
    }

    //回傳結果
    return $datas;
}


// 取得單個會員的檢驗報告
function get_one_file_blood($id)
{
    //宣告要回傳的結果
    $result = null;

    try {
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `inspectreport` WHERE `reportno` = :id;");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();

        $row = $sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料
        if ($row)
            $result = $row;
        else
            $result = $row;
    }catch (PDOException $e){
        echo "get_one_file_blood failed: " . $e->getMessage();
    }

    //回傳結果
    return $result;
}

//取得單個會員的超音波
function get_one_video($id)
{
    //宣告要回傳的結果
    $result = null;

    try {
        //將查詢語法當成字串，記錄在$sql變數中
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `movie` WHERE `videono` = :id;");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();

        $row = $sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料
        if ($row)
            $result = $row;
        else
            $result = $row;
    }catch (PDOException $e){
        echo "get_one_video failed: " . $e->getMessage();
    }
    //回傳結果
    return $result;
}

/*---------------user function--------------*/
//取得單個會員的所有檢報
function get_one_all_file_blood($uid)
{
    //建立陣列
    $datas = array();
    //宣告要回傳的結果
    $result = null;

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `inspectreport` LEFT JOIN `workplace` ON inspectreport.inspectionid = workplace.no WHERE `subjectid`=:uid ORDER BY `date` DESC,`state` DESC;");
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $datas = $sth->fetchAll();
    }catch (PDOException $e){
        echo "get_one_all_file_blood failed: " . $e->getMessage();
    }

    //回傳結果
    return $datas;
}

//取得單個會員的所有超音波影片
function get_one_all_video($uid)
{
    //建立陣列
    $datas = array();
    //宣告要回傳的結果
    $result = null;

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `movie` LEFT JOIN `workplace` ON movie.clinicid = workplace.no WHERE `subjectid`=:uid ORDER BY `date` DESC;");
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $datas = $sth->fetchAll();
    }catch (PDOException $e){
        echo "get_one_all_video failed: " . $e->getMessage();
    }

    //回傳結果
    return $datas;
}

//驗證會員及取得該會員的檢報
function get_verify_file_blood($id,$uid)
{
    //宣告要回傳的結果
    $result = null;

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `inspectreport` WHERE `reportno` = :id AND `subjectid` = :uid;");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_ASSOC);   // 取得一筆資料;
    }catch (PDOException $e){
        echo "get_verify_file_blood failed: " . $e->getMessage();
    }
    //回傳結果
    return $result;
}

//驗證會員及取得該會員的超音波影片
function get_verify_video($id,$uid)
{
    //宣告要回傳的結果
    $result = null;

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `movie` WHERE `videono` = :id AND `subjectid` = :uid;");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_ASSOC); // 取得一筆資料;
    }catch (PDOException $e){
        echo "get_verify_video failed: " . $e->getMessage();
    }
    //回傳結果
    return $result;
}

//取得單個客戶的帳戶資料
function get_one_client_data($uid)
{
    //宣告要回傳的結果
    $result = array();

    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT * FROM `account` WHERE `username` = :uid;");
        $sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
        $sth->execute();

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        echo "get_one_client_data failed: " . $e->getMessage();
    }

    //回傳結果
    return $result;
}

// 更新工作人員帳號/客戶
function update_member($uid,$password,$email,$telephone,$func)
{
    //宣告要回傳的結果
    $result = null;
    //$password = md5($password);
    $password = hash('sha256', $password);
    try {
		
		//修改密碼只更新密碼
		if ($func == 'forget')
		{
			//更新語法
			$sth = $GLOBALS['connect']->prepare("UPDATE `account` SET  `password` = :password WHERE `uid` = :uid;");
			$sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
			$sth->bindParam(':password', $password, PDO::PARAM_STR, 256);
		}
		//修改個人資料時全部更新
		else
		{
			//更新語法
			$sth = $GLOBALS['connect']->prepare("UPDATE `account` SET `password` = :password,`email` = :email,`telephone`=:telephone WHERE `uid` = :uid;");
			
			$sth->bindParam(':uid', $uid, PDO::PARAM_STR, 12);
			$sth->bindParam(':password', $password, PDO::PARAM_STR, 256);
			$sth->bindParam(':email', $email, PDO::PARAM_STR, 256);
			$sth->bindParam(':telephone', $telephone, PDO::PARAM_STR, 256);
		}
		$sth->execute();

        $row = $sth->rowCount();    // 改動筆數
        if ($row == 1)              // 改動單筆
            $result = true;
        else                        //尚未改動
            $result = false;
    }catch (PDOException $e){
        echo "update_member failed: " . $e->getMessage();
    }

    //回傳結果
    return $result;
}


//取得單個客戶的帳戶資料--忘記密碼
function forget_data($mail)
{
    //宣告要回傳的結果
    $result = array();
	
    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT `name`,`uid`,`email`, `password` FROM `account`  , `client_info`  where `account`.uid = client_uid and `email` = :mail AND `clientstate`=1;");
        $sth->bindParam(':mail', $mail, PDO::PARAM_STR, 256);
        $sth->execute();

        $result = $sth->fetchAll(PDO::FETCH_NUM);
    }catch (PDOException $e){
        $result= "forget_data failed: " . $e->getMessage();
		
    }

    //回傳結果
	//echo $result;
    return $result;
}

//取得單個客戶的帳戶資料--重置密碼
function Reset_PWD($ver)
{
	$Uid = substr($ver, 9, 10);
	$VER = substr($ver, 19, 99);
    //宣告要回傳的結果
    $result = array();
	
    try {
        //PDO 語法
        $sth = $GLOBALS['connect']->prepare("SELECT `name`,`uid`,`email`, `password` FROM `account`  , `client_info`  where `account`.uid = client_uid and `uid` = :uid AND `clientstate`=1 and `password`= :ver ; ");
        $sth->bindParam(':uid', $Uid , PDO::PARAM_STR, 256);
        $sth->bindParam(':ver', $VER , PDO::PARAM_STR, 256);
        $sth->execute();

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    }catch (PDOException $e){
        $result= "Reset_PWD failed: " . $e->getMessage();
		
    }

    //回傳結果
	//echo $result;
    return $result;
}

//通知帳號審核結果_For User
function SendMailtoClient($cuid, $examresult)
{
	if ($examresult){
		try {
			//PDO 語法
			$sth = $GLOBALS['connect']->prepare("SELECT name,email FROM `account` WHERE `uid` = :uid ");
			$sth->bindParam(':uid', $cuid, PDO::PARAM_STR, 256);
			$sth->execute();

			$result = $sth->fetchAll(PDO::FETCH_NUM);
			foreach($result as $row)
			{
				foreach($row as $key => $value)
				{
					if ($key==0){$name=$value;}	else{$email=$value;}
				}
			}
		}
		catch (PDOException $e){
			echo "Send Mail to Client failed: " . $e->getMessage();
		}
	}

	//取得信件內容
	$mstr = GetMailStr("checkaccess", $name,'',$cuid, $examresult);
	//寄送Email通知已註冊
	$re= SendMailFun("HNUser", "帳號審核結果通知", $email  ,$name, $mstr);
    
    //回傳結果
    return $re;
}

//通知客戶有更新檢驗報告可查看
function MailClientCheckReport($rid)
{
	//通知檢驗報告審核完成, 先取得客戶資料
	try {
        //PDO 語法
		$sth = $GLOBALS['connect']->prepare("SELECT `name`,`uid`,`email`  FROM `account` , `inspectreport`  where `reportno` = :rid and `account`.`uid`=`inspectreport`.`subjectid`;");
		
        $sth->bindParam(':rid', $rid, PDO::PARAM_STR, 256);
        $sth->execute();

        $result = $sth->fetchAll(PDO::FETCH_NUM);
		
		if ($result){
			foreach($result as $row)
			{
				foreach($row as $key => $value)
				{
					if ($key==0)$name=$value;
					if ($key==1)$uid=$value;
					if ($key==2)$email=$value;
				}
			}
			$mstr = GetMailStr("MailClientCReport", $name, $uid, '','');
			SendMailFun("MailClientCReport", "檢驗報告更新通知", $email ,$name, $mstr);
		
		}
		
    }catch (PDOException $e){
        echo "Send Mail to Client failed: " . $e->getMessage();
    }
}

//通知檢驗所更新被退件的檢驗報告
function MailinspectionChangeReport($rid)
{
	
	//通知檢驗報告審核完成, 先取得客戶資料
	try {
        //PDO 語法
		$sth = $GLOBALS['connect']->prepare("SELECT `name`,`uid`,`email`  FROM `account` , `inspectreport`  where `reportno` = :rid and `account`.`uid`=`inspectreport`.`subjectid`;");
		
		$sth->bindParam(':rid', $rid, PDO::PARAM_STR, 256);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_NUM);
		
		if ($result){
			foreach($result as $row)
		{
			foreach($row as $key => $value)
			{
				if ($key==0)$name=$value;
				if ($key==1)$uid=$value;
				if ($key==2)$email=$value;
			}
		}
		$mstr = GetMailStr("MailinspectionChangeReport", $name, $uid, '','');
		SendMailFun("MailInspecChangeReport", "檢驗報告退件通知", $email ,$name, $mstr);
		}
		
    }catch (PDOException $e){
        echo "Send Mail to Client failed: " . $e->getMessage();
    }
	
}

//寄信通用
function SendMailFun($fun , $mailsub ,  $mailaddr, $toName, $mailstr)
{
	// Load Composer's autoloader
	require 'vendor/autoload.php';

	// Instantiation and passing `true` enables exceptions
	$mail = new PHPMailer(true);
	
	$re="";
	
	//Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
	// Enable verbose debug output
    $mail->isSMTP();                                            
	// Send using SMTP
    $mail->Host       = "ssl://smtp.gmail.com"; //設定SMTP主機                  
	// Set the SMTP server to send through
    $mail->SMTPAuth   = true;                  

	// 登入	-- Cino申請的臨時帳號
	// Enable SMTP authentication
    $mail->Username   = 'forher019@gmail.com';                     
	// SMTP username
    $mail->Password   = 'ww1324--';                               
	// SMTP password
	
	
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
	// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 465;                                    
	// TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients   "" 
    $mail->setFrom('forher019@gmail.com', '豐禾婦產科');
	$mail->CharSet = "utf-8"; //設定郵件編碼   
	 	 
	 // 郵件資訊
	$mail->Subject = $mailsub; //設定郵件標題    
	$mail->IsHTML(true); //設定郵件內容為HTML   
	
	$mailfooter = '<br><br>若有任何建議，歡迎來電。<br><br>謝謝您。<br><br>敬祝 身體健康、萬事順心如意<br><br><br>豐禾婦產科<br>地址：新北市板橋區中山路一段279號3樓<br>聯絡電話：(02)8951-0666<br>官網：http://www.forher.com.tw/<br>FB：https://www.facebook.com/forherob/<br><br>';
	$mail->Body = $mailstr.$mailfooter; //設定郵件內容
	$mail->AddAddress($mailaddr , $toName); //設定收件者郵件及名稱   
	$mail->addBCC('cino522@gmail.com', 'cino');
	if(!$mail->Send()) {   
		$re= "Error: " . $mail->ErrorInfo;   
	} else {   
		$re= "Success";   
	}
	
	return $re;
	
}

function GetMailStr($func, $una, $rk, $striA, $striB)
{
	$res = "";
	
	//管理者代院內成員或檢驗所註冊帳號後, 發送可登入通知
	if ($func=="ReginSideUser")
	{
		$rk =($rk == '1')?'診所成員':'檢驗所';//0超級使用者 1診所 2檢驗所
		$res = '親愛的 '.$una.' 您好,<br><br>院內註冊成功, 身份權限為"'.$rk.'"<br>請 <a href="http://118.150.126.45/backend_web" >連結登入</a> 查看您的檢驗資料: <br><br>帳號: '.$striA.'<br>密碼: '.$striB.'<br><br><b>註: 首次登入後建議先至 [個人資料] 裡修改密碼</b>'; 
	}
	//診所代客戶註冊帳號(直接核准), 發送可登入通知
	elseif ($func=="RegNUser")
	{
		$res = '親愛的 '.$una.' 您好,<br><br>我們已經為您開通您所申請的帳號，歡迎加入會員系統。<br><br>
		請 <a href="http://118.150.126.45/backend_web" >連結登入</a> 並使用以下資訊登入查看您的檢驗資料: <br><br>帳號: '.$striA.'<br>密碼: '.$striB;
	}
	//客戶自行註冊帳號後通知待審核
	elseif ($func=="CRegNUser")
	{
		$res = '親愛的 <b>'.$una.'<b> 您好,<br><br>歡迎加入會員系統，我們系統將為您提供最完善的資訊服務。<br>目前您的帳號[ '.$striA .' ]審核中，我們會用最快速度幫您完成審核程序。';
	}
	//帳號審核結果通知
	elseif ($func=="checkaccess")
	{
		if ($striB){
			/*$res = '親愛的 '.$una.' 您好,<br><br>，我們已經為您開通您所申請的帳號，<br><br>請您使用您所申請的帳號密碼登入系統，<br>登入網址為：<br><br>祝福您使用愉快。';*/
			$res = '親愛的 '.$una.' 您好,<br><br>我們已經為您開通您所申請的帳號, 歡迎加入會員系統<br><br>請您使用您所申請的帳號密碼 登入系統, <a href="http://118.150.126.45/backend_web" >連結登入</a> <br><br>祝福您使用愉快。';
		}
		else{
			$res = '親愛的 '.$una.' 您好,<br><br>謝謝您申請加入會員系統，<br><br>但很抱歉，因為您填寫的資料有誤,所以我們暫時無法為您開通帳號。<br><br>麻煩您重新填寫資料進行申請帳號， <a href="http://118.150.126.45/backend_web" >點此進入申請帳號</a> ';
		}
	}
	//通知客戶有更新檢驗報告可查看
	elseif ($func == 'MailClientCReport')
	{
		$res = '親愛的 '.$una.' 您好,<br><br>您的檢驗報告已經上線，請您登入系統查看報告。<br><br> <a href="http://118.150.126.45/backend_web" >連結登入</a> ';
	}
	//通知請檢驗所更新被退件的檢驗報告
	elseif ($func == 'MailinspectionChangeReport')
	{
		$res = ' '.$una.' 您好,<br><br>您上傳[ '.$rk .' ]的檢驗報告被退件, 麻煩更新後重新上傳, 謝謝';
	}
	elseif ($func == 'SendVerPASSMail')
	{
		
		$res = '親愛的 '.$una.' 您好,<br><br>我們收到您在豐禾診所檢驗報告上傳系統申請重新設定密碼, 請點選以下連結進入更改密碼! <BR><BR>如果並非您本人申請, 請忽略此封通知, 謝謝<BR><BR>http://118.150.126.45/backend_web/forget_password.php?verifyid='.$striA.$striB.'<br><br>';
	}
	//修改密碼完成通知--忘記密碼
	elseif ($func == 'UpdateUserPWD')
	{
		
		$res = '親愛的 '.$una.' 您好,<br><br>您在豐禾診所檢驗報告上傳系統重新設定密碼已成功更新, 請您使用新的密碼登入會員系統。<br> <a href="http://118.150.126.45/backend_web" >連結登入</a> 祝福您使用愉快。<BR><BR><BR>P.S. 如果並非您本人做的設定, 請儘快聯絡我們, 謝謝<BR><BR>';
	}
	else
	{
		$res = '信件內容';
	}
	
	
	return $res;
}


?>
