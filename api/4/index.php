<?php
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

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

// // Only invoked if mode is "production"
// $app->configureMode('production', function () use ($app) {
//     $app->config(array(
//         'log.enable' => true,
//         'debug' => false
//     ));
// });

// // Only invoked if mode is "development"
// $app->configureMode('development', function () use ($app) {
//     $app->config(array(
//         'log.enable' => true,
//         'log.level' => \Slim\Log::DEBUG,
//         'debug' => true
//     ));
// });

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
include("Routes/sessions.php");
include("Routes/ga.php");
include("Routes/bids.php");
include("Routes/cart.php");
include("Routes/categories.php");
include("Routes/users.php");
include("Routes/items.php");
include("Routes/images.php");
include("Routes/errors.php");
include("Routes/conversations.php");
include("Routes/notifications.php");
// Get log writer
//$log = $app->MyLogWriter();

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->get('/tickets', function (Request $request, Response $response) {
    $this->logger->addInfo("Ticket list");
    $mapper = new TicketMapper($this->db);
    $tickets = $mapper->getTickets();
    $response->getBody()->write(var_export($tickets, true));
    return $response;
});

$app->run();

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($request, $response, $required_fields) {
    $error = false;
    $error_fields = "";
    // Handling PUT request params
    if ($request->getMethod() == 'PUT' || $request->getMethod() == 'POST') {
        $request_params = $request->getParsedBody();
        //parse_str($app->request()->getBody(), $request_params);
    }else{
        $request_params = $request->getQueryParams();
    }

    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }else{
            if ($field == "email"){
                if (!filter_var($request_params[$field], FILTER_VALIDATE_EMAIL)) {
                    $error = true;
                    $error_fields .= $field . ', ';
                }
            }
        }
    }
    $error_fields = substr($error_fields, 0, -2);

    if ($error) {
        global $r;
        $r["error"] = 1;
        $r["fieldlist"] = $error_fields;
        $r["description"] = 'Required field(s) ' . $error_fields . ' are missing or invalid';
        //$ga->trackEvent("required",$error_fields,$route);
        return null;
        //echoResponse(400, $r);
        //$app->stop();
    }else{
        return $request_params;
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
    global $app;
    // Http response code
    //$response->status($status_code);

    // setting response content type to json
    //$app->contentType('application/json');
    
    
    /*if (($useragent = $app->request->getUserAgent()) != ""){
    $ag = explode("/",$useragent);
    if (count($ag) > 1){
    $ag = explode(" -",$ag[1]);
    if ((int)$ag[0] > 10){
        $response["update"] = 0;
    }else{
        $response["update"] = 1;
    }
    }
    }*/
    print_r($app);
    return $response = $app->response()->withJson($response, $status_code);
    //echo $newResponse;

    //echo json_encode($response);
}
function echoSuccess($status_code, $error, $message) {
    $app = Slim::getInstance();
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