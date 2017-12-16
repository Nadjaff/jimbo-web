<?php
//ini_set ("display_errors", "1");

	error_reporting(E_ALL);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

	$api = 'api/4/';
include $api . "img/img.php";
require_once $api . 'db/db.php';
require_once $api . 'db/db_users.php';
require_once $api . 'db/db_sessions.php';
require_once $api . 'db/db_tokens.php';
require_once $api . 'db/db_images.php';
require_once $api . 'db/db_items.php';
require_once $api . 'db/db_conversations.php';
require_once $api . 'db/db_notifications.php';
require_once $api . 'include/PassHash.php';
require './vendor/slim/php-view/src/PhpRenderer.php';
require './vendor/autoload.php';
//require 'libs/Slim/Slim.php';
$config = [
    'settings' => [
        // Slim Settings
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'jimbo',
            'username' => 'root',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]
    ],
];
//use Slim\Exception;
//use Slim\Exception\ValidationException;
//use Slim\Exception\LoginException;

// \Slim\Slim::registerAutoloader();
//require 'libs/Twig/Autoloader.php';
//Twig_Autoloader::register();

global $app;
$app = new \Slim\App(["settings" => $config]);
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c['response']->withStatus(500)
                             ->withHeader('Content-Type', 'text/html')
                             ->write('Something went wrong!' . $exception);
    };
};
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['pdo'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container[App\WidgetController::class] = function ($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $table = $c->get('db')->table('table_name');
    return new \App\WidgetController($view, $logger, $table);
};
 $container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('./templates');
};
/*$app = new \Slim\Slim(array(
    'mode' => 'development'
));*/

// Only invoked if mode is "production"
/*$app->configureMode('production', function () use ($app) {
    $app->config(array( 
        'log.enable' => true,
        'debug' => false
    ));
});*/

// Only invoked if mode is "development"
/*$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'log.level' => \Slim\Log::DEBUG,
        'debug' => true
    ));
});*/
// Get log writer
//$log = $app->getLog();


// User id from db - Global Variable
$user_id = NULL;

require_once "ga.php";
GAManager::initialize();

/*phpinfo();
define('FACEBOOK_SDK_V4_SRC_DIR', 'facebook-php-sdk-v4-4.0-dev/src/Facebook/');
require 'facebook-php-sdk-v4-4.0-dev/autoload.php';
use Facebook\FacebookSession;*/

include("views/items.php");
include("views/users.php");

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
	$ag = explode("/",$app->request->getUserAgent());
	$ag = explode(" -",$ag[1]);
	if ($ag[0] == "5"){
		$response["update"] = 0;
	}else{
		$response["update"] = 1;
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