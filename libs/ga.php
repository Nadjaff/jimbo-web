<?php

$gatid = "UA-55626576-1"; // Put your own Analytics ID in here
$gacid = gaGenUUID();
// Generate UUID v4 function - needed to generate a CID when one isn't available
function gaGenUUID() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

    // 16 bits for "time_mid"
    mt_rand( 0, 0xffff ),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand( 0, 0x0fff ) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand( 0, 0x3fff ) | 0x8000,

    // 48 bits for "node"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}

function sendPageView($title, $route) {
	// Send PageView hit
	$data = array(
	  'v' => 1,
	  'tid' => $gatid,
	  'cid' => $gacid,
	  't' => 'pageview',
	  'dt' => $title,
	  'dp' => $route
	);
	
	forkAnalytics($data);
}
  
function sendEvent($category, $action, $label, $value=1){

    $data = array(
	  'v' => 1,
	  'tid' => $gatid,
	  'cid' => $gacid,
      't' => 'event',
      'ec' => $category,
      'ea' => $action,
      'el' => $label,
      'ev' => $value
    );

    forkAnalytics($data);
}

function forkAnalytics($data) {
  $cmd = "curl -X POST -H 'Content-type: application/x-www-form-urlencoded'";
  $cmd.= " -d '' '" . 'https://ssl.google-analytics.com/collect?payload_data&' . utf8_encode(http_build_query($data)) . "'";

  //if (!$this->debug()) {
    $cmd .= " > /dev/null 2>&1 &";
  //}

  exec($cmd, $output, $exit);
  return $exit == 0;
}
?>