<?php

/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------HELPER FUNCTIONS---------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

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
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
	$route = $app->request()->getPathInfo();
	error_log((string)$route);
	trackPage("Page",$route);

    // Verifying Authorization Header
    if (isset($_SERVER[HTTP_AUTHORIZATION])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $_SERVER[HTTP_AUTHORIZATION];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = 1;
            $response["title"] = "Incorrect Credentials";
            $response["message"] = "Please sign in to continue";
            echoResponse(401, $response);
            $app->stop();
			trackEvent("login failed","Authentication Incorrect",$route);
        } else {
            global $current_user_id;
            // get user primary key id
            $current_user_id = $db->getUserId($api_key);
			trackPage("Page requested", $route);
        }
    } else {
        // api key is missing in header
		$response["error"] = 1;
		$response["title"] = "Please sign in";
		$response["message"] = "Please sign in to continue";
		echoResponse(401, $response);
        $app->stop();
		trackEvent("login failed","Authentication Missing",$route);
    }
}























/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->post('/sessions', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username', 'password'));

            // reading post params
            $username = $app->request()->post('username');
            $password = $app->request()->post('password');

            $db = new DbHandler();
            $response = array();
            // check for correct email and password
            if (($response = $db->session_create($username, $password)) != NULL) {
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
?>