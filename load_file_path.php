<?php
@session_start();
//上傳檔案後的暫存資料夾位置
//上傳檔案有限制大小 預設2M
// $_FILES["file"]["error"] == UPLOAD_ERR_OK 檢查檔案室否上傳成功, file_exists($_FILES['file']['tmp_name'] 伺服器暫存檔是否存在
if(file_exists($_FILES['file']['tmp_name']) && $_FILES["file"]["error"] == UPLOAD_ERR_OK)
{
    //先定義上傳的資料夾
    $target_folder = $_POST['save_path'];

    //取得檔案原來的名稱
    $file_name = $_FILES['file']['name'];

    //建資料夾
    if(!file_exists($target_folder))
        mkdir($target_folder);
	//判斸是否存在, 或建立blood資料夾(for 檢驗所報告
    if(!file_exists($target_folder."blood/"))
        mkdir($target_folder."blood/");

    //move_uploaded_file(file,newloc)
    //如果存在就 搬移檔案 move_uploaded_file 方法是將上傳的檔案，移動到網站資料夾正確定義的位置
    //第一個變數，通常是上傳後暫存的檔案位置，第二個變數，是搬移的目標檔案及位置
    // $target_folder . $file_name 其實是 files/blood/pdf檔名.pdf
    // 由於 work_save.php 這隻檔案在 php 資料夾中，
	//但圖檔是要上傳到「上一層裡找到 UploadFiles 資料夾」，所以搬移的上傳位置要加上 ../
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target_folder."blood/". $file_name))
    {
        rename($target_folder."blood/". $file_name,$target_folder."blood/". $_SESSION['timenow'].'.pdf');
        echo "success";
    }
    else
    {
		//如遇到權限設定有問題, 可能造成無法新增資料夾跟檔案
        echo "檔案搬移失敗，請確認{$_POST['save_path']}資料夾可寫入";
    }

    //由於有上傳圖檔，所以要存到資料庫的 post 資訊 要加上 image_path 欄位，其內容是搬移到的新位置，不用加上 ../ 唷

}
else
{
    //echo file_exists($_FILES['file']['tmp_name']);
    echo "暫存檔不存在，上傳失敗";
}
?>
