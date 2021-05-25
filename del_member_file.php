<?php
/*尚未上傳之前檔案皆不存在，所以應該確認是否有選擇上傳檔案路徑來判斷刪除檔案路徑*/
//路徑存在就刪除
    $path ="../files/".$_POST['username']."/";

    function deldir($path){
        //如果是目錄則繼續
        if(is_dir($path)){
        //掃描一個資料夾內的所有資料夾和檔案並返回陣列
            $p = scandir($path);
            foreach($p as $val){
                //排除目錄中的.和..
                if($val !="." && $val !=".."){
                    //如果是目錄則遞迴子目錄，繼續操作
                    if(is_dir($path.$val)){
                        //子目錄中操作刪除資料夾和檔案
                        deldir($path.$val.'/');
                        //目錄清空後刪除空資料夾
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是檔案直接刪除
                        unlink($path.$val);
                    }
                }
            }
        }
    }

    //判斷資料夾檔案存不存在
    if(isset($path)) {
        //呼叫函式，傳入路徑
        deldir($path);
        rmdir($path);

        echo "success";
    }else{
        echo "success";
    }

?>
