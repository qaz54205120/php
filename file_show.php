<?php

require_once 'dbAPI.php';
require_once 'functions.php';
//print_r($_SESSION); //查看目前session內容


//如過沒有 $_SESSION['admin_is_login'] 這個值，或者 $_SESSION['admin_is_login'] 為 false 都代表超級使用者沒登入
//require_once '/login_check.php';

//取得所有客戶帳號


if(isset($userid)) {
    //取得單個客戶的檢驗報告資料
    $member = get_one_file_blood($_GET['fid']);	
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!--文字編碼方式-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" /> <!--手機版網頁頭檔宣告-->
    <!--什麼版本IE 就用什麼版本的標準模式&&使用以下代碼強制IE 使用Chrome Frame-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

</head>
<body>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script>
    // 設定檔案高度
    $(document).ready(function() {
        var h = window.innerHeight;
        console.log("height:"+h);
        $("iframe").css({height:h});
    });
</script>

<!-- 加入警示標註及返回上頁功能 __Cino  -->
<div style="color: red; font-weight: bold;">
<button type="button" class="submit "
 onClick="javascript:history.back()">回上頁</button>
 
<!-- url 不能有中文等字元 -->
<?php if($member > 0){ ?>
    <iframe src="<?php echo '../UploadFiles/'.$member['subjectid'].'/blood/'.$member['reportname'].'.pdf';?>" style="width: 100%;">
        This browser does not support PDFs. Please download the PDF to view it: <a href="<?php echo '../UploadFiles/'.$member['subjectid'].'/'.$member['reportname'].'.pdf';?>">Download PDF</a>
    </iframe>
<?php }?>

</body>
</html>
