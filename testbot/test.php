<?php
	$baseurl = "http://jimbo.co/api/2/";
	$headers = "Authorization: 2665c944a206bc21b3b1664e0ebdceca";
	$attachment = "test544b0ca19ee0c.jpg";
	$payload = "";
	parse_str($payload,$newpayload);
	$headers = explode("\n",$headers);
	$ch = curl_init();
	
			if ($attachment != ""){
					$newpayload["image"] = '@testuploads/' . $attachment;
			}
		//$payload = "name=Stephen&username=stephenasdfea";
			curl_setopt($ch, CURLOPT_URL, $baseurl .  "images");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $newpayload);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
	$return = curl_exec($ch);
	echo $return;
	echo curl_getinfo ($ch, CURLINFO_TOTAL_TIME);
	curl_close($ch);
?>