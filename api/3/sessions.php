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

//if (!defined('HTTP_AUTHORIZATION')){
	//define('HTTP_AUTHORIZATION', "Authorization");
//}
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
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

$app->post('/sessions', function() use ($app) {
            // check for required params
            $device = $app->request()->post('device');
            $regid = $app->request()->post('regid');
			if ($regid == NULL){
				$regid = "";
			}
			
            $db = new DbSessions();
            $fbtoken = $app->request()->post('fbtoken');
			if ($fbtoken != NULL && $fbtoken != ""){
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
							echo "here";
							// get user primary key id
							$current_user_id = $id;
            				$dbuser = new DbUsers();
							$dbuser->user_put_fb($current_user_id,$fbuser["id"],$fbtoken);
							GAManager::trackPage("Page requested", $route);
							echoResponse(200, $fbuser);
							return;
						}
					}
				
					// api key is missing in header
					$session = $db->session_create_fb($fbuser["id"],$device, $regid);
					if ($session != NULL) {
						$session["error"] = 0;
						echoResponse(200, $session);
					}else{
						$fbuser["error"] = 0;
						echoResponse(200, $fbuser);
					}
					return;
						
				}
			}else{
				$fbtoken = "";
			}
            verifyRequiredParams(array('username', 'password'));

            // reading post params
            $username = $app->request()->post('username');
            $password = $app->request()->post('password');

            $response = array();
            // check for correct email and password
            if (($response = $db->session_create($username, $password, $device, $regid, $fbtoken)) != NULL) {
                // get the user by email
                $response["error"] = 0;
            } else {
                // user credentials are wrong
                $response['error'] = 1;	
                $response['message'] = 'Login failed. Incorrect credentials';
				
				//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
            }
            echoResponse(200, $response);
        });
		




/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->put('/sessions', 'authenticate', function() use ($app) {
            // check for required params
			global $current_user_id;

            // reading post params
            $fbtoken = $app->request()->put('fbtoken');
            $device = $app->request()->put("device");
            $regid = $app->request()->put('regid');
			$api_key = $_SERVER[HTTP_AUTHORIZATION];
			
			$db = new DbSessions();
			$response = array();	
			if ($device == "" || $device == 0){
				// check for correct email and password
				if (($response = $db->session_delete($api_key)) != NULL) {
					// get the user by email
					$response["error"] = 0;
				} else {
					// user credentials are wrong
					$response['error'] = 1;
					$response['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
			}else{
				if (($response = $db->session_put($api_key, $device, $regid, $fbtoken)) != NULL) {
					// get the user by email
					$response["error"] = 0;
				} else {
					// user credentials are wrong
					$response['error'] = 1;
					$response['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
			}
            echoResponse(200, $response);
        });
		
		
$app->delete('/sessions', 'authenticate', function() use ($app) {
			$api_key = $_SERVER[HTTP_AUTHORIZATION];
			
			$db = new DbSessions();
			$response = array();
				// check for correct email and password
				if (($response = $db->session_delete($api_key)) != NULL) {
					// get the user by email
					$response["error"] = 0;
				} else {
					// user credentials are wrong
					$response['error'] = 1;
					$response['message'] = 'Logout failed - incorrect credentials';
					
					//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
				}
            echoResponse(200, $response);
        });
?>