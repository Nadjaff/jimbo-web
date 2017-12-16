<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------HELPER FUNCTIONS---------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
if (!is_callable('apache_request_headers')){
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
           // do some nasty string manipulations to restore the original letter case
           // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }
}
if (!defined('HTTP_AUTHORIZATION')){
	define('HTTP_AUTHORIZATION', "Authorization");
}
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
$authentication = function ($request, $response, $next) {
    $route = $request->getUri()->getPath();
        global $log;
        $log = array();
	//error_log((string)$route);
	//GAManager::trackPage("Page",$route);
	
    // Verifying Authorization Header
    
    if (null !== ($request->getHeader("Authorization"))) {
        $db = new DbSessions();
		global $r;
		$r=array();
        // get the api key
		if(isset( $request->getHeader("Authorization")[0]))
		{ 
	     $api_key = $request->getHeader("Authorization")[0];
        // validating api key
		$id = $db->getUserId($api_key);
		}else{
			$id=0;
		}
        if ($id == 0) {
            // api key is not present in users table
		
            $r["error"] = 1;
            $r["title"] = "Incorrect Credentials";
            $r["message"] = "Please sign in to continue";
           return     $response->withJson($r,403);
       //     $app->stop();
	  //		trackEvent("login failed","Authentication Incorrect",$route);
        } else {
            global $current_user_id;
            // get user primary key id
            $current_user_id = $id;
			GAManager::trackPage("Page requested", $route);
        }
    } else {
        // api key is missing in header
		global $r;
		$r=array();
		$r["error"] = 1;
		$r["title"] = "Please sign in";
		$r["message"] = "Please sign in to continue";
		 return     $response->withJson($r,403);
     //   $app->stop();
		GAManager::trackEvent("login failed","Authentication Missing",$route);
    }
           
    $response = $next($request, $response);
      
    global $r;
    $r["log"] = $log;
    $response = $response->withJson($r);
    //$response->getBody()->write('AFTER');
    return $response;
};
$public = function ($request, $response, $next) {
	$route = $request->getUri()->getPath();
	error_log(($route));
	GAManager::trackPage("Page",$route);
        global $log;
        $log = array();
	global $r;
	
    // Verifying Authorization Header
    if (isset($_SERVER[HTTP_AUTHORIZATION])) {
        $db = new DbSessions();
        // get the api key
        $api_key = $_SERVER[HTTP_AUTHORIZATION];
        // validating api key
		$id = $db->getUserId($api_key);
        if ($id == 0) {
            // api key is not present in users table
            $r["error"] = 1;
            $r["title"] = "Incorrect Credentials";
            $r["message"] = "Please sign in to continue";
			return $response->withJson($r,403);
           // echoResponse(403, $response);
    //        $app->stop();
			trackEvent("login failed","Authentication Incorrect",$route);
        } else {
            global $current_user_id;
            // get user primary key id
            $current_user_id = $id;
			GAManager::trackPage("Page requested", $route);
        }
    }
    //$response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    //$response->getBody()->write('AFTER');
    global $r;
    // Http response code
    //$response->status($status_code);
    // setting response content type to json
    //$app->contentType('application/json');
    /*print_r($request->GetHeaders()["HTTP_USER_AGENT"][0]);
    echo "<br />";
    if (($useragent = $request->GetHeaders()["HTTP_USER_AGENT"][0]) != ""){
    $ag = explode("/",$useragent);
        echo "\n" . count($ag);
    if (count($ag) > 1){
    $ag = explode(" -",$ag[1]);
    echo "\n";
        print_r($ag);
    if ((int)$ag[0] > 10){
        $r["update"] = 0;
    }else{
        $r["update"] = 1;
    }
    }
    }*/
    $r["update"] = 0;
    $r["log"] = $log;
    $response1 = $response->withJson($r);
    return $response1;
};
function authenticate(\Slim\Route $route) {
    // Getting request headers
    //$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
	$route = $app->request()->getPathInfo();
	error_log((string)$route);
	GAManager::trackPage("Page",$route);
	
	
    // Verifying Authorization Header
    if (isset($_SERVER[HTTP_AUTHORIZATION])) {
        $db = new DbSessions();
        // get the api key
        $api_key = $_SERVER[HTTP_AUTHORIZATION];
        // validating api key
		$id = $db->getUserId($api_key);
        if ($id == 0) {
            // api key is not present in users table
            $response["error"] = 1;
            $response["title"] = "Incorrect Credentials";
            $response["message"] = "Please sign in to continue";
            echoResponse(403, $response);
            $app->stop();
			trackEvent("login failed","Authentication Incorrect",$route);
        } else {
            global $current_user_id;
            // get user primary key id
            $current_user_id = $id;
			GAManager::trackPage("Page requested", $route);
        }
    } else {
        // api key is missing in header
		$response["error"] = 1;
		$response["title"] = "Please sign in";
		$response["message"] = "Please sign in to continue";
		echoResponse(403, $response);
        $app->stop();
		GAManager::trackEvent("login failed","Authentication Missing",$route);
    }
}
function noauthenticate(\Slim\Route $route) {
    // Getting request headers
    //$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
	$route = $app->request()->getPathInfo();
	error_log((string)$route);
	GAManager::trackPage("Page",$route);
	
	
    // Verifying Authorization Header
    if (isset($_SERVER[HTTP_AUTHORIZATION])) {
        $db = new DbSessions();
        // get the api key
        $api_key = $_SERVER[HTTP_AUTHORIZATION];
        // validating api key
		$id = $db->getUserId($api_key);
        if ($id == 0) {
            // api key is not present in users table
            $response["error"] = 1;
            $response["title"] = "Incorrect Credentials";
            $response["message"] = "Please sign in to continue";
            echoResponse(403, $response);
            $app->stop();
			trackEvent("login failed","Authentication Incorrect",$route);
        } else {
            global $current_user_id;
            // get user primary key id
            $current_user_id = $id;
			GAManager::trackPage("Page requested", $route);
        }
    }
}
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
$app->post('/sessions', function ($request, $response, $args) {
    postSession($request, $response, $args,"");
})->add($public);
//$app->post('/sessions', function() use ($app) {
function postSession($request, $response, $args,$filter){
    global $r;
            // check for required params
            $device = $request->getParsedBodyParam('device',"");
            $regid = $request->getParsedBodyParam('regid',"");
			
            $db = new DbSessions();
            $fbtoken = $request->getParsedBodyParam('fbtoken',"");
			if ($fbtoken != ""){
				$fbuser = $db->facebook_login($fbtoken);
				if ($fbuser != NULL && $fbuser["id"] != NULL){
					
					if (isset($_SERVER[HTTP_AUTHORIZATION])) {
						$db = new DbSessions();
				
						// get the api key
						$api_key = $_SERVER[HTTP_AUTHORIZATION];
						// validating api key
						$id = $db->getUserId($api_key);
						if ($id != 0) {
							global $current_user_id;
							// get user primary key id
							$current_user_id = $id;
            				$dbuser = new DbUsers();
							$dbuser->user_put_fb($current_user_id,$fbuser["id"],$fbtoken);
							GAManager::trackPage("Page requested", $route);
                            $r = $fbuser;
                            return $response->withStatus(200);
						}
					}
				
					// api key is missing in header
					$session = $db->session_create_fb($fbuser["id"],$device, $regid);
					if ($session != NULL) {
						$session["error"] = 0;
                        $r = $session;
					}else{
						$fbuser["error"] = 0;
                        $r = $fbuser;
					}
                        return $response->withStatus(200);
						
				}
			}else{
				$fbtoken = "";
			}
            verifyRequiredParams($request, $response, array('username', 'password'));
            // reading post params
            $username = $request->getParsedBodyParam('username',"");
            $password = $request->getParsedBodyParam('password',"");
            $r2 = array();
            // check for correct email and password
            if (($r2 = $db->session_create($username, $password, $device, $regid, $fbtoken)) != NULL) {
                // get the user by email
                $r2["error"] = 0;
            } else {
                // user credentials are wrong
                $r2['error'] = 1;	
                $r2['message'] = 'Login failed. Incorrect credentials';
				
				//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
            }
            $r = $r2;
            return $response->withStatus(200);
        }
		
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->put('/sessions',  function ($request, $response, $args) {
	 putSession($request, $response, $args);
})->add($authentication);
function putSession($request, $response, $args){
            // check for required params
			global $current_user_id;
			global $r;
			$r=array();
            // reading post params
            $fbtoken = $request->getParsedBodyParam('fbtoken','');
            $device = $request->getParsedBodyParam("device",'');
            $regid = $request->getParsedBodyParam('regid','');
			if(isset($request->getHeader("Authorization")[0]))
			{
				$api_key = $request->getHeader("Authorization")[0];	
			}else{
				$api_key = '';
			}				
			$db = new DbSessions();
			//$response = array();	
			if ($device == "" || $device == 0){
				// check for correct email and password
				if (($r = $db->session_delete($api_key)) != NULL) {
					// get the user by email
					$r["error"] = 0;
				} else {
					// user credentials are wrong
					$r['error'] = 1;
					$r['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
			}else{				
				if (($r = $db->session_put($api_key, $device, $regid, $fbtoken)) != NULL) {
					// get the user by email
					$r["error"] = 0;
				} else {
					// user credentials are wrong
					$r['error'] = 1;
					$r['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
			}
           return $response->withStatus(200);
        }
		
$app->delete('/sessions', function($request, $response, $args)  {
	deleteSession($request, $response, $args);
})->add($authentication);

function deleteSession($request, $response, $args){
			if(isset($request->getHeader("Authorization")[0]))
			{
				$api_key = $request->getHeader("Authorization")[0];	
			}else{
				$api_key = '';
			}	
			global $r;
			$r=array();
			$db = new DbSessions();
			//$response = array();
				// check for correct email and password
				if (($r = $db->session_delete($api_key)) != NULL) {
					// get the user by email
					$r["error"] = 0;
				} else {
					// user credentials are wrong
					$r['error'] = 1;
					$r['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
            return $response->withStatus(200);
  }
?>