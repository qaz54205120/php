<?php

include_once 'functions.php';

$json = file_get_contents('php://input');
	$obj = json_decode($json);
	if (isset($obj)){
		$state = $obj->{'state'};
		$Loginid = $obj->{'Loginid'};
		$LoginPWD = $obj->{'LoginPWD'};
	}

//檢查身份回應相對代號
switch (verify_user($Loginid, $LoginPWD,$state)){
    case 0:
        echo 'superuser';
        break;
    case 1:
        echo 'nurse';
        break;
    case 2:
        echo 'inspector';
        break;
    case 3:
        echo 'user';
        break;
    default:
        echo 'fail';
}

?>
