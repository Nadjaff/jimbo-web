<?php
global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
$app->get('/users', function ($request, $response, $args) {
	getUsers($request, $response, $args,"");
})->add($public);

$app->get('/users/{id}', function ($request, $response, $args) {
	getUser($request, $response, $args,"");
})->add($authentication);
$app->get('/users/{id}/settings', function ($request, $response, $args) {
	getUserSettings($request, $response, $args,"");
})->add($authentication);
$app->get('/users/{id}/following', function ($request, $response, $args) {
	getUserFollowing($request, $response, $args,"");
})->add($authentication);
$app->get('/users/{id}/followers', function ($request, $response, $args) {
	getUserFollowers($request, $response, $args,"");
})->add($authentication);
$app->get('/users/{id}/reviews', function ($request, $response, $args) {
	getUserReviews($request, $response, $args,"");
})->add($authentication);
$app->get('/usersupdate', function ($request, $response, $args) {
	getUsersUpdate($request, $response, $args,"");
})->add($authentication);
$app->get('/contactsfb', function ($request, $response, $args) {
	getContactsFacebook($request, $response, $args,"");
})->add($authentication);
$app->get('/updateall', function ($request, $response, $args) {
	getUpdateAll($request, $response, $args,"");
})->add($authentication);

$app->post('/users', function ($request, $response, $args) {
	postUsers($request, $response, $args,"");
})->add($public);


$app->post('/users/forgotpassword', function ($request, $response, $args) {
	postUserForgotPassword($request, $response, $args,"");
})->add($public);

$app->post('/users/forgotpassword/{token}', function ($request, $response, $args) {
	postUserForgotPasswordWithToken($request, $response, $args,"");
})->add($public);

$app->get('/users/forgotpassword/{token}', function ($request, $response, $args) {
	getUserForgotPasswordWithToken($request, $response, $args,"");
})->add($public);

$app->post('/users/resendmobile', function ($request, $response, $args) {
	postUserResendMobile($request, $response, $args,"");
})->add($authentication);

$app->post('/users/confirmmobile', function ($request, $response, $args) {
	postUserConfirmMobile($request, $response, $args,"");
})->add($authentication);

$app->post('/users/exists', function ($request, $response, $args) {
	postUsersExists($request, $response, $args,"");
})->add($authentication);

$app->post('/users/follow', function ($request, $response, $args) {
	postUsersFollow($request, $response, $args,"");
})->add($authentication);

$app->post('/users/{id}/follow', function ($request, $response, $args) {
	postUserFollow($request, $response, $args,"");
})->add($authentication);

$app->post('/users/{id}/report', function ($request, $response, $args) {
	postUserReport($request, $response, $args,"");
})->add($authentication);

$app->post('/users/contacts', function ($request, $response, $args) {
	postUserContacts($request, $response, $args,"");
})->add($authentication);

$app->post('/users/{id}', function ($request, $response, $args) {
	postUser($request, $response, $args,"");
})->add($authentication);
$app->put('/users/{id}', function ($request, $response, $args) {
	putUser($request, $response, $args,"");
})->add($authentication);
$app->put('/users/{id}/password', function ($request, $response, $args) {
	putUserPassword($request, $response, $args,"");
})->add($authentication);
$app->put('/users/{id}/settings', function ($request, $response, $args) {
	putUserSettings($request, $response, $args,"");
})->add($authentication);
$app->delete('/users/{id}', function ($request, $response, $args) {
	deleteUser($request, $response, $args,"");
})->add($authentication);

function getUsers($request, $response, $args,$filter){
	
//$app->get('/users', 'authenticate', function()  use ($app){
	global $current_user_id;
	global $r;
	$db = new DbUsers();
    $q = $request->getQueryParam('q',"");
    $newerthan_id = $request->getQueryParam('newerthan_id',null);
    $olderthan_id = $request->getQueryParam('olderthan_id',null);
    $count = $request->getQueryParam('count',null);
    $test = $request->getQueryParam('test',0);
	$r = ($db->users_get_all($current_user_id, $q, $newerthan_id, $olderthan_id, $count, $test));

   global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   // . "This page was created in ".$totaltime." seconds"
   //$r = ($t);
    return $response->withStatus(200);   
}
function getUser($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
//$app->get('/users/:id', 'authenticate', function($user_id) use ($app) {
	global $current_user_id;
	global $r;
	$db = new DbUsers();
    $username = $request->getQueryParam('username',"");
    $q = $request->getQueryParam('q',"");
    $newerthan_id = $request->getQueryParam('newerthan_id',null);
    $olderthan_id = $request->getQueryParam('olderthan_id',null);
    $count = $request->getQueryParam('count',null);
    $test = $request->getQueryParam('test',null);
	$r = $db->user_get($current_user_id, $user_id, $username, $newerthan_id, $olderthan_id,$count,$test);
	//echo explode(" -",explode("/",$app->request->getUserAgent())[1])[0];
			//$info = get_browser();
			//echo $info->browser;
			//echo $info->version;
	if ($r["error"] == 0) {
    	return $response->withStatus(200);
	} else {
		//$r = array();
		$r["title"] = "Error";
		$r["message"] = "Sorry, this user doesn't exist";
    	return $response->withStatus(404);
	}
}


function getUserSettings($request, $response, $args,$filter){
//$app->get('/users/:id/settings', 'authenticate', function($user_id) {
	global $current_user_id;
	global $r;
	$user_id = (int)$args['id'];
	$r = array();
	$db = new DbUsers();
	if ($user_id == $current_user_id){
		$r = $db->user_get_settings($current_user_id, $user_id);
		if ($r["error"] != 0) {
			$r["title"] = "Error";
			$r["message"] = "An error occurred. Please try again later.";
		}
    	return $response->withStatus(200);
	}else{
		$r["error"] = 1;
		$r["message"] = "You are not authorized to access these settings";
    	return $response->withStatus(401);
		//echoResponse(401, $response);
	}
	
}


function getUserFollowing($request, $response, $args,$filter){
//$app->get('/users/:id/following', 'authenticate', function($user_id) use ($app) {

    $q = $request->getQueryParam('q',"");
    $newerthan_id = $request->getQueryParam('newerthan_id',null);
    $olderthan_id = $request->getQueryParam('olderthan_id',null);
    $count = $request->getQueryParam('count',null);
    $test = $request->getQueryParam('test',null);

	$user_id = (int)$args['id'];
	global $current_user_id;
	global $r;
	$db = new DbUsers();
		global $r;
	$r = ($db->user_get_following($current_user_id, $user_id, $newerthan_id, $olderthan_id, $count));
    	return $response->withStatus(200);	
}

function getUserFollowers($request, $response, $args,$filter){
//$app->get('/users/:id/followers', 'authenticate', function($user_id) use ($app){
    $q = $request->getQueryParam('q',"");
    $newerthan_id = $request->getQueryParam('newerthan_id',null);
    $olderthan_id = $request->getQueryParam('olderthan_id',null);
    $count = $request->getQueryParam('count',null);
    $test = $request->getQueryParam('test',null);

	$user_id = (int)$args['id'];
	global $current_user_id;
	$db = new DbUsers();
	global $r;
	$r = ($db->user_get_followers($current_user_id, $user_id, $newerthan_id, $olderthan_id, $count));	
    	return $response->withStatus(200);
}

function getUserReviews($request, $response, $args,$filter){
    $q = $request->getQueryParam('q',"");
    $newerthan_id = $request->getQueryParam('newerthan_id',null);
    $olderthan_id = $request->getQueryParam('olderthan_id',null);
    $count = $request->getQueryParam('count',null);
    $test = $request->getQueryParam('test',null);

//$app->get('/users/:id/reviews', 'authenticate', function($user_id) use ($app){
	global $current_user_id;
	$user_id = (int)$args['id'];
	$db = new DbUsers();
	global $r;
	$r = ($db->user_get_reviews($current_user_id, $user_id, $newerthan_id, $olderthan_id, $count));	
    	return $response->withStatus(200);
}

function getUsersUpdate($request, $response, $args,$filter){

//$app->get('/usersupdate', function() use ($app) {
	$db = new DbUsers();
	global $r;
	$r = ($db->users_update());	
    	return $response->withStatus(200);
}

function getContactsFacebook($request, $response, $args,$filter){
//$app->get('/contactsfb', 'authenticate', function() use ($app) {
	global $current_user_id;
	$db = new DbUsers();
	global $r;
	$r = ($db->user_get_facebook_friends($current_user_id));
    	return $response->withStatus(200);	
}

function getUpdateAll($request, $response, $args,$filter){
//$app->get('/updateall', function() use ($app) {
	$db = new DbUsers();
	global $r;
	$r = ($db->user_update_all());	
    	return $response->withStatus(200);
} 
function postUsers($request, $response, $args,$filter){
//$app->post('/users', function() use ($app) {
            // check for required params
	global $r;
	$p;
		    $r = array();
		    global $log;
            if (($p = verifyRequiredParams($request,$response,array('name','username', 'email', 'password'))) == null) {
        		return $response->withStatus(400);
            }
            // validating email address
			
            //validateEmail($app->request->post('email'));
            $img = "";
            if(isset($_FILES['image']) && isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'],0);
			if ($img == ""){
				$img = $request->getParsedBodyParam('image',"");
			}

				$name = $request->getParsedBodyParam('name',"");
				$firstname = $request->getParsedBodyParam('firstname',"");
				$lastname = $request->getParsedBodyParam('lastname',"");

				if ($firstname == "" && $lastname == ""){
					$namee = explode(" ",$name);
					$firstname = $namee[0];
					$log[] = "firstname = " . $name;
					array_shift($namee);
					$lastname = implode("",$namee);
				}

            $db = new DbUsers();

		    $test = $request->getParsedBodyParam('test',0);
            
            $r = $db->users_post(	$firstname,$lastname,
									$p['username'],
									$p['email'],
									$p['password'],
									$request->getParsedBodyParam('location_latitude',""),
									$request->getParsedBodyParam('location_longitude',""),
									$request->getParsedBodyParam('location_locality',""),
									$request->getParsedBodyParam('location_country',""),
									$request->getParsedBodyParam('location_admin',""),
									$request->getParsedBodyParam('phone',""),
									$request->getParsedBodyParam('fbid',""),
									$request->getParsedBodyParam('fbtoken',""),
									$img,
									generateMobileCode(),
									$test);
			
            if ($r["error"] == 0) {
                $r["title"] = "Success";
				//$res["api_key"] = $res["api_key"];
				$dbs = new DbSessions();
				$session = $dbs->session_create($p['username'],$p['password'],$p['device'],$p['regid']);
				//$session["api_key"] = $res["api_key"];
				$session["error"] = 0;
				$r = $session; 
                $r["message"] = "You are successfully registered";
				$dbTokens = new DbTokens();
				if(!isset($session["user_id"]))
					$session['user_id'] = null; // let's do this by default
				$dbTokens->sendEmail("jimbo-welcome",array("user_id"=>$session["user_id"], "email"=>$p['email'], "name"=>$p['firstname'] . " " . $p['lastname']));
				//resendMobile($res["id"]);
            }
            // echo json response
            //echoResponse(201, $res);
    		return $response->withStatus(201);
        }
		
function getUserForgotPasswordWithToken($request, $response, $args,$filter){
	$token = (int)$args['token'];
    $db = new DbUsers();
	global $r;
	 $r = $db->getToken($token);
           	
	//$r = ( $response);
    		return $response->withStatus(200);
}
function postUserForgotPasswordWithToken($request, $response, $args,$filter){
	
	$token = (int)$args['token'];
	global $r;
//$app->post('/users/forgotpassword/:token', function($token) use ($app) {
            $db = new DbUsers();
			$r = array();
			$pass = $request->getParsedBodyParam('password','');
			if ($pass != ""){
				
				$r = $db->useToken($token, $pass);
			}
	//$r = ( $response);
    		return $response->withStatus(200);
}		
function postUserForgotPassword($request, $response, $args,$filter){
//$app->post('/users/forgotpassword', function() use ($app) {
            // check for required params
           if($v =  verifyRequiredParams($request,$response,array('username'))==null){
		     return $v;
		   }
          //  $response = array();

            // reading post params
            $username = $request->getParsedBodyParam('username','');
			/****************** Commented by Sri *********************/
		/*	$db = new DbTokens();
			$res = $db->sendEmail("jimbo-reset-password",array("username"=>$username));*/
			/****************** End Commented by Sri *********************/
            	/*$db = new DbUsers();
            //$res = $db->user_reset_password($username);
            $res = $db->createToken($username);
			if ($res != NULL){	
				$response["error"] = 0;
				$response["title"] = "Check your email";
				$response["message"] = "A link to reset your password has been sent to the email address associated with this account.";
				//if ($res["email"] != "stephen2earth@gmail.com"){
					require_once 'libs/Mandrill.php';
					try {
						$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
						$message = array(
							'html' => '<p>Please click the link below to reset your password or copy and paste it into your browser.</p><a href="http://jimbo.co/users/forgotpassword/' . $res["token"] . '">http://jimbo.co/users/forgotpassword/' . $res["token"] . '</a></p>',
							'subject' => 'Your password has been reset',
							'from_email' => 'noreply@jimbo.co',
							'from_name' => 'The Jimbo Team',
							'to' => array(
								array(
									'email' => $res["email"],
									'name' => $res["name"],
									'type' => 'to'
								)
							),
							'tags' => array('password-resets'),
							'metadata' => array('Jimbo' => 'www.jimbo.co'),
							'recipient_metadata' => array(
								array(
									'rcpt' => $res["email"]
								)
							)
						);
						$async = true;
						$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
					} catch(Mandrill_Error $e) {
						echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
						throw $e;
					}
					//mail($email,"Your password has been reset", "Please see the following link to reset your password","From: admin@jimbo.co\n");
				//}
           		global $r;
	$r = ( $response);
			}else{
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "This user could not be found.";
				global $r;
	$r = ( $response);
			}*/
			global $r;
	$r = ($res);
    		return $response->withStatus(200);
        }
		
		
function postUserResendMobile($request, $response, $args,$filter){
//$app->post('/users/resendmobile', 'authenticate', function() use ($app) {
		// check for required params
	global $current_user_id;
	global $r;
	
	$r = resendMobile($current_user_id);
	
	//$r = ( $response);
    		return $response->withStatus(200);
}
function resendMobile($current_user_id){
	$db = new DbUsers();
	$res = $db->getMobileInfo($current_user_id);
	if ($res != NULL){
		$response["error"] = 0;
		$response["message"] = "Check your mobile";
		
		if ($res["email"] != "stephen2earth@gmail.com"){
			require_once 'libs/Mandrill.php';
				/******************** Commented by Sri *************/
			/*try {
				$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
				$message = array(
					'html' => '<p>Please see the your mobile confirmation code below</p><b>' . $res["mobile_code"] . "</b>",
					'subject' => 'Your mobile confirmation code',
					'from_email' => 'noreply@jimbo.co',
					'from_name' => 'The Jimbo Team',
					'to' => array(
						array(
							'email' => $res["email"],
							'name' => $res["firstname"] . " " . $res["lastname"],
							'type' => 'to'
						)
					),
					'tags' => array('password-resets'),
					'metadata' => array('Jimbo' => 'www.jimbo.co'),
					'recipient_metadata' => array(
						array(
							'rcpt' => $res["email"]
						)
					)
				);
				$async = true;
				if(!isset($ip_pool))
					$ip_pool = null;
				if(!isset($send_at))
					$send_at = null;
				$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
			} catch(Mandrill_Error $e) {
				echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				throw $e;
			}*/
			/******************** End Commented by Sri *************/
			//mail($email,"Your password has been reset", "Please see the following link to reset your password","From: admin@jimbo.co\n");
		}
	}else{
		$response["error"] = 1;
		$response["title"] = "Error";
		$response["message"] = "This user could not be found.";
	}
		return $response;
}

function postUserConfirmMobile($request, $response, $args,$filter){
//$app->post('/users/confirmmobile', 'authenticate', function() use ($app) {
	// check for required params	
	if($v =  verifyRequiredParams($request,$response,array('code'))==null){
		     return $v;
		   }
	global $r;
	$r = array();
	// reading post params
	$code = $request->getParsedBodyParam('code','');
	$r["error"] = 0;
     global $current_user_id;
	$db = new DbUsers();
	if ($db->confirmMobile($current_user_id,$code) == false) {
		// user credentials are wrong
		$r['error'] = 1;
		$r['title'] = "Incorrect Code";
		$r['message'] = 'Incorrect code. Tap resend to send again.';
    return $response->withStatus(401);
		//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
	}else{
		$r['error'] = 0;
		$r['message'] = 'Your mobile has been confirmed.';
	}
    return $response->withStatus(200);
}


		
function postUserExists($request, $response, $args,$filter){
//$app->post('/users/exists', function() use ($app) {
            // check for required params
            verifyRequiredParams($request,$response,array('username'));
            global $r;
            $r = array();

            // reading post params
            $username = $app->request->post('username');

            $db = new DbUsers();
            $res = $db->isUserExists($username);
			$r["error"] = 0;
			if ($res == 0) {
				$r["status"] = 0;
			} else {
				$r["status"] = 1;
			}
    return $response->withStatus(200);
        }


function postUsersFollow($request, $response, $args,$filter){
	//$user_id = (int)$args['id'];
//$app->post('/users/follow', 'authenticate', function() use ($app) {
            // check for required params
            verifyRequiredParams($request,$response,array('follow'));
			global $current_user_id;
			global $r;
            $r = array();

            // reading post params
            $follow_ids = $request->getParsedBodyParam('follow','');
			$ids = explode(",",$follow_ids);
			$c = count($ids);
            $db = new DbUsers();
			for ($i=0;$i<$c;$i++){
            	$res = $db->user_follow($current_user_id, $ids[$i],1);
			}
			$r["error"] = 0;
    return $response->withStatus(200);
        }
		
		
function postUserFollow($request, $response, $args,$filter){
	         $follow_id = (int)$args['id'];
//$app->post('/users/:id/follow', 'authenticate', function($follow_id) use ($app) {
            // check for required params		
            if($v=verifyRequiredParams($request,$response,array('value'))==null){
				return $v;
			}
			global $current_user_id;
			global $r;

            $r = array();

            // reading post params
            $follow = $request->getParsedBodyParam('value','');

            $db = new DbUsers();
            $res = $db->user_follow($current_user_id, $follow_id,$follow);
			$r["error"] = 0;
			if ($res == 0) {
				$r["status"] = 0;
			} else {
				$r["status"] = 1;
			}
    return $response->withStatus(200);
        }
		
function postUserReport($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
//$app->post('/users/:id/report', 'authenticate', function($user_id) use ($app) {
            if($v=verifyRequiredParams($request,$response,array('comment'))==null){
			   return $v;
			}
			global $current_user_id;
			global $r;
            $db = new DbUsers();
			$r = ( $db->user_report($current_user_id, $user_id,$request->getParsedBodyParam('comment','')));
           return $response->withStatus(200);
        }
		
function postUserContacts($request, $response, $args,$filter){
//$app->post('/users/contacts', 'authenticate', function() use ($app) {
			global $current_user_id;
			global $r;
            // check for required params
			$arr = array();
			$uids = array();
            $json = json_decode(file_get_contents('php://input'),true);
			/*$arr["json"] = $json;
			$arr["error"] = 0;
            echoResponse(201, $arr);
			return;*/
			
            $db = new DbUsers();
			foreach ($json["contacts"] as $contact){
				//$arr["json"] = $json; 
			
				$res = $db->getUserEmailFollowing($current_user_id, $contact["data"]);
				if ($res != NULL){
					if (!in_array($res["id"],$uids)){
						array_push($arr,$res);
						array_push($uids,$res["id"]);
					}
				}
				/*if ($res2 != NULL && ($res == NULL || $res2["id"] != $res["id"])){
					array_push($arr,$res2);
				}*/
			}
			$db->store_contacts($current_user_id,$json["contacts"]);
			
			$r = array();
			$r["users"] = $arr;
			$r["error"] = 0;
					
            // echo json response
    		return $response->withStatus(200);
        }	
		
/*$app->post('/users/:id', 'authenticate', function($user_id) use($app) {
		
            global $current_user_id; 
			
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);
			if ($img == "" && $app->request->post('image') != ""){
				$img = $app->request->post('image');
			}
			if ($img == ""){
				verifyRequiredParams($request,$response,array('username','name','bio','phone','email','gender','website'));
			}       
            $db = new DbUsers();
            $result = $db->user_put($current_user_id, $user_id, $app->request->put('username'), $app->request->put('name'), $app->request->put('bio'),$app->request->put('website'), $app->request->put('phone'), $app->request->put('email'), $app->request->put('location_description'), $app->request->put('gender'), $img);
            $r = ( $result);
        }	*/
				
function postUser($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
            global $current_user_id; 
            global $r; 
            $db = new DbUsers();
            $img = '';
			if ($request->getParsedBodyParam('password','') != ""){
				if ($request->getParsedBodyParam('new_password','') != ""){
					$np = $request->getParsedBodyParam('new_password','');
				}else{
					$np =$request->getParsedBodyParam('newpassword','');
				}
              $r = $db->user_put_password($current_user_id, $user_id, $request->getParsedBodyParam('password',''), $np);
			  if($r['error']!=0){ return $response->withStatus(200); }
			}	
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);

			if ($img == "" && $request->getParsedBodyParam('image','') != ""){
				$img = $request->getParsedBodyParam('image','');
			}
			
			if ($request->getParsedBodyParam('username','') == "" && $request->getParsedBodyParam('fbid','') == "" && $request->getParsedBodyParam('fbtoken','') == "" && $img == ""){
				$r = $db->user_clear_fb($current_user_id);
    			return $response->withStatus(200);
			}
			if ($img == ""){
				verifyRequiredParams($request,$response,array('username','firstname','lastname','email'));
			}       
            $r = $db->user_put($current_user_id, $user_id, $request->getParsedBodyParam('username',''), $request->getParsedBodyParam('firstname',''), $request->getParsedBodyParam('lastname',''), $request->getParsedBodyParam('bio',''),$request->getParsedBodyParam('website',''), $request->getParsedBodyParam('phone'), $request->getParsedBodyParam('email',''), $request->getParsedBodyParam('location_description',''), $request->getParsedBodyParam('gender',''), $img);
			return $response->withStatus(200);
        }
		
function putUser($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
//$app->put('/users/:id', 'authenticate', function($user_id) use($app) {
            global $current_user_id; 
            global $r;
            $r = array(); 
            $db = new DbUsers();
            $img = '';
			if ($request->getParsedBodyParam('password','') != ""){
				if ($request->getParsedBodyParam('new_password','') != ""){
					$np = $request->getParsedBodyParam('new_password','');
				}else{
					$np = $request->getParsedBodyParam('newpassword','');
				}
            $r = $db->user_put_password($current_user_id, $user_id,  $request->getParsedBodyParam('password',''), $np);
				if($r['error']!=0)
				{
					return $response->withStatus(200);
				}
			}	
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);

			if ($img == "" && $request->getParsedBodyParam('image') != ""){
				$img = $request->getParsedBodyParam('image','');
			}
			if ($request->getParsedBodyParam('username') == "" && $request->getParsedBodyParam('fbid') == "" && $request->getParsedBodyParam('fbtoken') == "" && $img == ""){
				$r = $db->user_clear_fb($current_user_id);
    			return $response->withStatus(200);
			}
			if ($img == ""){
				verifyRequiredParams($request,$response,array('username','firstname','lastname','email'));
			}       
            $r = $db->user_put($current_user_id, $user_id, $request->getParsedBodyParam('username',''),$request->getParsedBodyParam('firstname',''), $request->getParsedBodyParam('lastname',''), $request->getParsedBodyParam('bio',''),$request->getParsedBodyParam('website',''), $request->getParsedBodyParam('phone',''), $request->getParsedBodyParam('email',''), $request->getParsedBodyParam('location_description',''), $request->getParsedBodyParam('gender',''), $img);
    		return $response->withStatus(200);
        }
		
function putUserPassword($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
//$app->put('/users/:id/password', 'authenticate', function($user_id) use($app) {
			verifyRequiredParams($request,$response,array('password','newpassword'));
            global $current_user_id;
            global $r;
            $db = new DbUsers();
            $r = $db->user_put_password($current_user_id, $user_id, $app->request->put('password'), $app->request->put('newpassword'));
    return $response->withStatus(200);
        }

function putUserSettings($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
//$app->put('/users/:id/settings', 'authenticate', function($user_id) use($app) {
	verifyRequiredParams($request,$response,array("notify_message","notify_comment","notify_tag","notify_followed","notify_friendjoins","currency","save_originals"));
	global $current_user_id;
	global $r;
	$r = array();
			
	$db = new DbUsers();
	if ($user_id == $current_user_id){
		$success = $db->user_put_settings($current_user_id, $user_id, $app->request->put('notify_message'), $app->request->put('notify_offer'), $app->request->put('notify_comment'), $app->request->put('notify_review'), $app->request->put('notify_tag'), $app->request->put('notify_followed'), $app->request->put('notify_friendjoins'), $app->request->put('currency'), $app->request->put('save_originals'));
		
		if ($success == true) {
			$r["error"] = 0;
			$r["message"] = "Successfully updated user settings";
    return $response->withStatus(200);
		} else {
			$r = array();
			$r["error"] = 2003;
			$r["title"] = "Error";
			$r["message"] = "An error occurred. Please try again later.";
    return $response->withStatus(404);
		}
	}else{
		$r["error"] = 1;
		$r["message"] = "You are not authorized to access these settings";
    return $response->withStatus(401);
	}
	
}			

function deleteUser($request, $response, $args,$filter){
	$user_id = (int)$args['id'];
	//$app->delete('/users/:id', 'authenticate', function($user_id) use($app) {
	global $current_user_id;
	global $r;

	$db = new DbUsers();
	$r = array();
	$result = $db->user_deactivate($current_user_id, $user_id);
	if ($result) {
		$r["error"] = 0;
		$r["message"] = "Account deactivated";
	} else {
		$r["error"] = 2005;
		$r["message"] = "There was an error while deactivating this account. Please try again later.";
	}
    return $response->withStatus(200);
}
function generateMobileCode(){
	$digits = 5;
	//$mobile_code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
	return rand(0, pow(10, $digits)-1);
}
?>