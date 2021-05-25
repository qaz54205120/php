<?php
$ke= $_POST['un'].'-----';
$blood_path = $_SERVER['DOCUMENT_ROOT']."/backend_web/UploadFiles/".$_POST['un']."/blood/";
$video_path = $_SERVER['DOCUMENT_ROOT']."/backend_web/UploadFiles/".$_POST['un']."/video/";
$user_path = $_SERVER['DOCUMENT_ROOT']."/backend_web/UploadFiles/".$_POST['un'];

//建資料夾
if(!file_exists($blood_path) || !file_exists($video_path) || !file_exists($user_path)) {
    $old = umask(0);
	
    //$ur= mkdir($user_path,0775,true);
	/*if (!@mkdir($user_path)) {
		$error = error_get_last();
		$ur= $error['message'];
	}*/
	
    mkdir($user_path,0775,true);
    mkdir($blood_path,0775,true);
    mkdir($video_path,0775,true);
    umask($old);
}

//$me=`whoami`;
//echo $ke.$ur.'----'.$user_path.'**'.$me;
echo 'success';		
//回傳成功訊息
//return 'success';

?>
