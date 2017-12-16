<?php
//ini_set ("display_errors", "1");

//error_reporting(E_ALL);
require_once 'DbHandler.php';
require_once 'include/PassHash.php';
require 'libs/Slim/Slim.php';
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

include("sessions.php");
include("users.php");
include("items.php");
include("images.php");
include ("conversations.php");
include ("errors.php");
include ("ga.php");
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
		trackEvent("required",$error_fields,$route);
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
		trackEvent("required",$error_fields,$route);
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