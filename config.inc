<?php

if (!defined('APP_ENV')) define('APP_ENV', 'live');

$env = APP_ENV;

if($env == 'local'){
	$baseurl = "localhost/jimbo-web/api/";
	$uploadbase = __DIR__ . "/images/uploads/";
	$upload_api_3_base = __DIR__ . "/api/3/tmp/tmpfile/";
	$testbot_testuploads = __DIR__ . "/testbot/testuploads/";
}
else{
	$baseurl = "http://jimboapi.herokuapp.com/api/";
	$uploadbase = __DIR__ . "/images/uploads/";
	$upload_api_3_base = __DIR__ . "/api/3/tmp/tmpfile/";
	$testbot_testuploads = __DIR__ . "/testbot/testuploads/";
}