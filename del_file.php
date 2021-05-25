<?php
/*尚未上傳之前檔案皆不存在，所以應該確認是否有選擇上傳檔案路徑來判斷刪除檔案路徑*/
//路徑存在就刪除
if(isset($_POST['file']))
{
    echo "success";

}
else {
    echo "fail"; //檔案不存在
}

//unlink(filename,context);
//deletes a file
/*
if(unlink($_POST['file'])){
    echo "success";
}
else {
    echo "刪除失敗";
}
*/

?>
