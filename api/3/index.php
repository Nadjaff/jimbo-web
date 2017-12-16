<?php
//ini_set ("display_errors", "1");

//error_reporting(E_ALL);
include "img/img.php";
require_once 'DbHandler.php';
require_once 'db/db.php';
require_once 'db/db_users.php';
require_once 'db/db_categories.php';
require_once 'db/db_sessions.php';
require_once 'db/db_bids.php';
require_once 'db/db_cart.php';
require_once 'db/db_images.php';
require_once 'db/db_items.php';
require_once 'db/db_conversations.php';
require_once 'db/db_tokens.php';
require_once 'db/db_notifications.php';
require_once 'include/PassHash.php';
include("include/Config.php");
require 'libs/Slim/Slim.php';
require __DIR__ . '/vendor/autoload.php';	

use Slim\Exception;
use Slim\Exception\ValidationException;
use Slim\Exception\LoginException;

\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim(array(
    'mode' => 'development'
));

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'log.level' => \Slim\Log::DEBUG,
        'debug' => true
    ));
});
// Get log writer
$log = $app->getLog();


// User id from db - Global Variable
$user_id = NULL;

require_once "ga.php";
GAManager::initialize();

/*phpinfo();
define('FACEBOOK_SDK_V4_SRC_DIR', 'facebook-php-sdk-v4-4.0-dev/src/Facebook/');
require 'facebook-php-sdk-v4-4.0-dev/autoload.php';
use Facebook\FacebookSession;*/

include("sessions.php");
include("bids.php");
include("cart.php");
include("categories.php");
include("users.php");
include("items.php");
include("images.php");
include ("conversations.php");
include ("notifications.php");
include ("errors.php");
$app->run();

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
	$error_fields = substr($error_fields, 0, -2);

    if ($error) {
        $app = \Slim\Slim::getInstance();
		$response["error"] = 1;
		$response["fieldlist"] = $error_fields;
		$response["description"] = 'Required field(s) ' . $error_fields . ' are missing or empty';
        echoResponse(400, $response);
        $app->stop();
		$ga->trackEvent("required",$error_fields,$route);
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    	$app = \Slim\Slim::getInstance();
		$response["error"] = 1;
		$response["fieldlist"] = $error_fields;
		$response["description"] = 'Please enter a valid email address';
        echoResponse(400, $response);
        $app->stop();
		$ga->trackEvent("required",$error_fields,$route);
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');
	
	
	if (($useragent = $app->request->getUserAgent()) != ""){
	$ag = explode("/",$useragent);
	if (count($ag) > 1){
	$ag = explode(" -",$ag[1]);
	if ((int)$ag[0] > 10){
		$response["update"] = 0;
	}else{
		$response["update"] = 1;
	}
	}
	}

    echo json_encode($response);
}
function echoSuccess($status_code, $error, $message) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');
	$response = array();
	$repsonse["error"] = $error;
	$response["message"] = $message;
    echo json_encode($response);
}

?>