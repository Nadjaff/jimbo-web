<?php
global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 

$app->get('/users', 'authenticate', function()  use ($app){
	global $current_user_id;
	$db = new DbUsers();
	// fetching all user items
	if($app->request->get('test'))
		$test = $app->request->get('test');
	else
		$test = 0;
	$t = ($db->users_get_all($current_user_id, $app->request->get('q'), $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count'), $test));
global $starttime;
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   // . "This page was created in ".$totaltime." seconds"
   echoResponse(200,$t);
   
   
   
   
   
   
});


$app->get('/users/:id', 'authenticate', function($user_id) use ($app) {
	global $current_user_id;
	$db = new DbUsers();

	$response = $db->user_get($current_user_id, $user_id, $app->request->get('username'), $app->request->get('newerthan_id'), $app->request->get('olderthan_id'),$app->request->get('count'),$app->request->get('test'));
	//echo explode(" -",explode("/",$app->request->getUserAgent())[1])[0];
			//$info = get_browser();
			//echo $info->browser;
			//echo $info->version;
	if ($response["error"] == 0) {
		echoResponse(200, $response);
	} else {
		$response["title"] = "Error";
		$response["message"] = "The requested user doesn't exist.";
		echoResponse(404, $response);
	}
});


$app->get('/users/:id/settings', 'authenticate', function($user_id) {
	global $current_user_id;
	$response = array();
	$db = new DbUsers();
	if ($user_id == $current_user_id){
		$response = $db->user_get_settings($current_user_id, $user_id);
		if ($response["error"] != 0) {
			$response["title"] = "Error";
			$response["message"] = "An error occurred. Please try again later.";
		}
		echoResponse(200, $response);
	}else{
		$response["error"] = 1;
		$response["message"] = "You are not authorized to access these settings";
		echoResponse(401, $response);
	}
	
});

$app->get('/users/:id/following', 'authenticate', function($user_id) use ($app) {
	global $current_user_id;
	$db = new DbUsers();
	echoResponse(200,$db->user_get_following($current_user_id, $user_id, $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));	
});

$app->get('/users/:id/followers', 'authenticate', function($user_id) use ($app){
	global $current_user_id;
	$db = new DbUsers();
	echoResponse(200,$db->user_get_followers($current_user_id, $user_id, $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));	
});

$app->get('/users/:id/reviews', 'authenticate', function($user_id) use ($app){
	global $current_user_id;
	$db = new DbUsers();
	echoResponse(200,$db->user_get_reviews($current_user_id, $user_id, $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));	
});

$app->get('/usersupdate', function() use ($app) {
	$db = new DbUsers();
	echoResponse(200,$db->users_update());	
});

$app->get('/contactsfb', 'authenticate', function() use ($app) {
	global $current_user_id;
	$db = new DbUsers();
	echoResponse(200,$db->user_get_facebook_friends($current_user_id));	
});

$app->get('/updateall', function() use ($app) {
	$db = new DbUsers();
	echoResponse(200,$db->user_update_all());	
}); 








	
$app->post('/users', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'username', 'email', 'password'));
            // validating email address
			
            validateEmail($app->request->post('email'));
            $img = "";
            if(isset($_FILES['image']) && isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'],0);
			if ($img == ""){
				$img = $app->request->post('image');
			}

            $db = new DbUsers();
            if(($app->request->post('test')))
            	$test = $app->request->post('test');
            else
            	$test = 1;
            
            $res = $db->users_post(	$app->request->post('name'),
									$app->request->post('username'),
									$app->request->post('email'),
									$app->request->post('password'),
									$app->request->post('lat'),
									$app->request->post('long'),
									$app->request->post('phone'),
									$app->request->post('fbid'),
									$app->request->post('fbtoken'),
									$img,
									generateMobileCode(),
									$test);
			
            if ($res["error"] == 0) {
                $res["title"] = "Success";
				//$res["api_key"] = $res["api_key"];
				$dbs = new DbSessions();
				$session = $dbs->session_create($app->request->post('username'),$app->request->post('password'),$app->request->post('device'),$app->request->post('regid'));
				//$session["api_key"] = $res["api_key"];
				$session["error"] = 0;
				$res = $session; 
                $res["message"] = "You are successfully registered";
				$dbTokens = new DbTokens();
				if(!isset($session["user_id"]))
					$session['user_id'] = null; // let's do this by default
				$dbTokens->sendEmail("jimbo-welcome",array("user_id"=>$session["user_id"], "email"=>$app->request->post('email'), "name"=>$app->request->post('name')));
				//resendMobile($res["id"]);
            }
            // echo json response
            echoResponse(201, $res);
        });
		
$app->get('/users/forgotpassword/:token', function($token) use ($app) {
            $db = new DbUsers();
	$response = $db->getToken($token);
           		echoResponse(200, $response);
});
$app->post('/users/forgotpassword/:token', function($token) use ($app) {
            $db = new DbUsers();
			$response = array();
			$pass = $app->request->post('password');
			if ($pass != ""){
				$response = $db->useToken($token, $pass);
			}
           	echoResponse(200, $response);
});
		
$app->post('/users/forgotpassword', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username'));
            $response = array();

            // reading post params
            $username = $app->request->post('username');
			$db = new DbTokens();
			$res = $db->sendEmail("jimbo-reset-password",array("username"=>$username));
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
           		echoResponse(200, $response);
			}else{
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "This user could not be found.";
				echoResponse(200, $response);
			}*/
			echoResponse(200,$res);
        });
		
		
$app->post('/users/resendmobile', 'authenticate', function() use ($app) {
		// check for required params
	global $current_user_id;
	$response = resendMobile($current_user_id);
	echoResponse(200, $response);
});
function resendMobile($current_user_id){
	$db = new DbUsers();
	$res = $db->getMobileInfo($current_user_id);
	if ($res != NULL){
		$response["error"] = 0;
		$response["message"] = "Check your mobile";
		
		if ($res["email"] != "stephen2earth@gmail.com"){
			require_once 'libs/Mandrill.php';
			try {
				$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
				$message = array(
					'html' => '<p>Please see the your mobile confirmation code below</p><b>' . $res["mobile_code"] . "</b>",
					'subject' => 'Your mobile confirmation code',
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
				if(!isset($ip_pool))
					$ip_pool = null;
				if(!isset($send_at))
					$send_at = null;
				$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
			} catch(Mandrill_Error $e) {
				echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				throw $e;
			}
			//mail($email,"Your password has been reset", "Please see the following link to reset your password","From: admin@jimbo.co\n");
		}
	}else{
		$response["error"] = 1;
		$response["title"] = "Error";
		$response["message"] = "This user could not be found.";
	}
		return $response;
}

$app->post('/users/confirmmobile', 'authenticate', function() use ($app) {
	// check for required params
	verifyRequiredParams(array('code'));
	$response = array();

	// reading post params
	$code = $app->request->post('code');
	
	$response = array();
	$response["error"] = 0;
        global $current_user_id;
	$db = new DbUsers();
	if ($db->confirmMobile($current_user_id,$code) == false) {
		// user credentials are wrong
		$response['error'] = 1;
		$response['title'] = "Incorrect Code";
		$response['message'] = 'Incorrect code. Tap resend to send again.';
		echoResponse(401,$response);
		return;
		//trackEvent("login failed","Incorrect Username / Password",$app->router()->getCurrentRoute());
	}else{
		$response['error'] = 0;
		$response['message'] = 'Your mobile has been confirmed.';
		echoResponse(200,$response);
	}
});


		
$app->post('/users/exists', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username'));

            $response = array();

            // reading post params
            $username = $app->request->post('username');

            $db = new DbUsers();
            $res = $db->isUserExists($username);
			$response["error"] = 0;
			if ($res == 0) {
				$response["status"] = 0;
			} else {
				$response["status"] = 1;
			}
			echoResponse(200, $response);
        });


$app->post('/users/follow', 'authenticate', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('follow'));
			global $current_user_id;

            $response = array();

            // reading post params
            $follow_ids = $app->request->post('follow');
			$ids = explode(",",$follow_ids);
			$c = count($ids);
            $db = new DbUsers();
			for ($i=0;$i<$c;$i++){
            	$res = $db->user_follow($current_user_id, $ids[$i],1);
			}
			$response["error"] = 0;
			echoResponse(200, $response);
        });
		
		
$app->post('/users/:id/follow', 'authenticate', function($follow_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $follow = $app->request->post('value');

            $db = new DbUsers();
            $res = $db->user_follow($current_user_id, $follow_id,$follow);
			$response["error"] = 0;
			if ($res == 0) {
				$response["status"] = 0;
			} else {
				$response["status"] = 1;
			}
			echoResponse(200, $response);
        });
		
$app->post('/users/:id/report', 'authenticate', function($user_id) use ($app) {
            verifyRequiredParams(array('comment'));
			global $current_user_id;
            $db = new DbUsers();
			echoResponse(200, $db->user_report($current_user_id, $user_id,$app->request->post('comment')));
        });
		
$app->post('/users/contacts', 'authenticate', function() use ($app) {
			global $current_user_id;
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
			
			$res = array();
			$res["users"] = $arr;
			$res["error"] = 0;
					
            // echo json response
            echoResponse(201, $res);
        });	
		
		
		
		
		
		
		
		
		
		
		
		
		
/*$app->post('/users/:id', 'authenticate', function($user_id) use($app) {
		
            global $current_user_id; 
			
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);
			if ($img == "" && $app->request->post('image') != ""){
				$img = $app->request->post('image');
			}
			if ($img == ""){
				verifyRequiredParams(array('username','name','bio','phone','email','gender','website'));
			}       
            $db = new DbUsers();
            $result = $db->user_put($current_user_id, $user_id, $app->request->put('username'), $app->request->put('name'), $app->request->put('bio'),$app->request->put('website'), $app->request->put('phone'), $app->request->put('email'), $app->request->put('location_description'), $app->request->put('gender'), $img);
            echoResponse(200, $result);
        });	*/
				
$app->post('/users/:id', 'authenticate', function($user_id) use($app) {
            global $current_user_id;  
            $db = new DbUsers();
            $img = '';
			if ($app->request->put('password') != ""){
				if ($app->request->put('new_password') != ""){
					$np = $app->request->put('new_password');
				}else{
					$np = $app->request->put('newpassword');
				}
            $result = $db->user_put_password($current_user_id, $user_id, $app->request->put('password'), $np);
			}	
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);

			if ($img == "" && $app->request->put('image') != ""){
				$img = $app->request->put('image');
			}
			if ($app->request->put('username') == "" && $app->request->put('fbid') == "" && $app->request->put('fbtoken') == "" && $img == ""){
				$result = $db->user_clear_fb($current_user_id);
				echoResponse(200, $result);
				return;
			}
			if ($img == ""){
				verifyRequiredParams(array('username','name','email'));
			}       
            $result = $db->user_put($current_user_id, $user_id, $app->request->put('username'), $app->request->put('name'), $app->request->put('bio'),$app->request->put('website'), $app->request->put('phone'), $app->request->put('email'), $app->request->put('location_description'), $app->request->put('gender'), $img);
            echoResponse(200, $result);
        });
		
$app->put('/users/:id', 'authenticate', function($user_id) use($app) {
            global $current_user_id;  
            $db = new DbUsers();
            $img = '';
			if ($app->request->put('password') != ""){
				if ($app->request->put('new_password') != ""){
					$np = $app->request->put('new_password');
				}else{
					$np = $app->request->put('newpassword');
				}
            $result = $db->user_put_password($current_user_id, $user_id, $app->request->put('password'), $np);
			}	
			if(isset($_FILES['image']['tmp_name']))
				$img = uploadProfilePic($_FILES['image']['tmp_name'], $current_user_id);

			if ($img == "" && $app->request->put('image') != ""){
				$img = $app->request->put('image');
			}
			if ($app->request->put('username') == "" && $app->request->put('fbid') == "" && $app->request->put('fbtoken') == "" && $img == ""){
				$result = $db->user_clear_fb($current_user_id);
				echoResponse(200, $result);
				return;
			}
			if ($img == ""){
				verifyRequiredParams(array('username','name','email'));
			}       
            $result = $db->user_put($current_user_id, $user_id, $app->request->put('username'), $app->request->put('name'), $app->request->put('bio'),$app->request->put('website'), $app->request->put('phone'), $app->request->put('email'), $app->request->put('location_description'), $app->request->put('gender'), $img);
            echoResponse(200, $result);
        });
		
$app->put('/users/:id/password', 'authenticate', function($user_id) use($app) {
			verifyRequiredParams(array('password','newpassword'));
            global $current_user_id;
            $db = new DbUsers();
            $result = $db->user_put_password($current_user_id, $user_id, $app->request->put('password'), $app->request->put('newpassword'));
            echoResponse(200, $result);
        });

$app->put('/users/:id/settings', 'authenticate', function($user_id) use($app) {
	verifyRequiredParams(array("notify_message","notify_comment","notify_tag","notify_followed","notify_friendjoins","currency","save_originals"));
	global $current_user_id;
	$response = array();
			
	$db = new DbUsers();
	if ($user_id == $current_user_id){
		$success = $db->user_put_settings($current_user_id, $user_id, $app->request->put('notify_message'), $app->request->put('notify_offer'), $app->request->put('notify_comment'), $app->request->put('notify_review'), $app->request->put('notify_tag'), $app->request->put('notify_followed'), $app->request->put('notify_friendjoins'), $app->request->put('currency'), $app->request->put('save_originals'));
		
		if ($success == true) {
			$response["error"] = 0;
			$response["message"] = "Successfully updated user settings";
			echoResponse(200, $response);
		} else {
			$response = array();
			$response["error"] = 2003;
			$response["title"] = "Error";
			$response["message"] = "An error occurred. Please try again later.";
			echoResponse(404, $response);
		}
	}else{
		$response["error"] = 1;
		$response["message"] = "You are not authorized to access these settings";
		echoResponse(401, $response);
	}
	
});		
		
		
		
		
$app->delete('/users/:id', 'authenticate', function($user_id) use($app) {
	global $current_user_id;

	$db = new DbUsers();
	$response = array();
	$result = $db->user_deactivate($current_user_id, $user_id);
	if ($result) {
		$response["error"] = 0;
		$response["message"] = "Account deactivated";
	} else {
		$response["error"] = 2005;
		$response["message"] = "There was an error while deactivating this account. Please try again later.";
	}
	echoResponse(200, $response);
});
		
		
		
		
		
		
		
		
		
		
		
		
function generateMobileCode(){
	$digits = 5;
	//$mobile_code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
	return rand(0, pow(10, $digits)-1);
}
?>