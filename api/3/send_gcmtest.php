<?php
if ((isset($_GET["user_id"]) || isset($_GET["regid"])) && isset($_GET["message"])) {
    $regid = $_GET["regid"];
    $user_id = $_GET["user_id"];
    $message = $_GET["message"];
    $action = $_GET["action"];
     
    include_once 'libs/GCM.php';
	//$message = "TEST";
	//$user_id = "1";
     
    $gcm = new GCM();
 
    //$user_ids = array("registration_ids" => $user_id);
    $regmessage = array("title" => "Notification", "message" => $message, "action" => $action);
 
	if (isset($_GET["user_id"])){
 		$result = $gcm->send_notification($user_id,"Notification",$message,$action);
	}else{
    	$result = $gcm->send_to_reg(array($regid), $regmessage);
	}
    echo $result;
}
?>