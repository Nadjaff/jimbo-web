<?php
global $gg;
$gg = "GUIDNOTGENERATED";
function swapGUID($haystack){
	global $gg;
	$pos = strpos($haystack,"GUID_");
			
	while ($pos !== false) {
		if (strpos($haystack,"_NEWGUID_") !== false && strpos($haystack,"_NEWGUID_") < $pos-1){
			$needle = "_NEWGUID_";
			$replace = ($gg = uniqid());
			$pos = strpos($haystack,"_NEWGUID_");
		}else{
			$needle = "_GUID_";
			$replace = $gg;
			$pos = $pos-1;
		}
		$haystack = substr_replace($haystack,$replace,$pos,strlen($needle));
		$pos = strpos($haystack,"GUID_");
	}
	return $haystack;
}
function runTest($baseurl,$type,$url,$headers,$payload, $attachment){
	//$baseurl = "http://jimbo.co/api/2/";
	$payload = swapGUID($payload);
	if ($headers == ""){
		$headers = "Authorization: None";
	}
	$headers = explode("\n",swapGUID($headers));
	parse_str($payload,$newpayload);
	$ch = curl_init();
	switch($type){
		case "POST":
			if ($attachment != ""){
					$newpayload["image"] = '@testuploads/' . $attachment;
			}
		//$payload = "name=Stephen&username=stephenasdfea";
			curl_setopt($ch, CURLOPT_URL, $baseurl .  $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $newpayload);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
		break;
		case "GET":
			curl_setopt($ch, CURLOPT_URL, $baseurl .  $url . "?" . $payload);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
		break;
		case "DELETE":
			curl_setopt($ch, CURLOPT_URL, $baseurl .  $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
		break;
		case "UPDATE":
			$newpayload = $payload;
			if ($attachment != ""){
					$newpayload .= '&image=@testuploads/' . $attachment;
			}
			curl_setopt($ch, CURLOPT_URL, $baseurl .  $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $newpayload);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		break;

	}
	/*global $gg;*/
	$return = curl_exec($ch);
	global $gg;
	$return = array(str_replace($gg,"_GUID_",$return), curl_getinfo ($ch, CURLINFO_TOTAL_TIME));
	//$return = array("asdf",$attachment);
	curl_close($ch);
	return $return;
}
?>