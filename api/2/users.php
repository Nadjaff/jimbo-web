<?php

/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------USERS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->get('/users', 'authenticate', function()  use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	
	$q = $app->request->get('q');
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->users_get_all($current_user_id, $q, $newerthan_id, $olderthan_id, $count);
	
	$response["error"] = (int)!($result != NULL);
	$response["users"] = $result;

	echoResponse(200, $response);
});


$app->get('/users/:id', 'authenticate', function($user_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	$response = $db->user_get($current_user_id, $user_id, $newerthan_id, $olderthan_id, $count);

	if ($response != NULL) {
		$response["error"] = 0;
		echoResponse(200, $response);
	} else {
		$response = array();
		$response["error"] = 1;
		$response["title"] = "Error";
		$response["message"] = "The requested user doesn't exist.";
		echoResponse(404, $response);
	}
});


$app->get('/users/:id/settings', 'authenticate', function($user_id) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	if ($user_id == $current_user_id){
		$response = $db->user_get_settings($current_user_id, $user_id);

		if ($response != NULL) {
			$response["error"] = 0;
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

$app->get('/users/:id/following', 'authenticate', function($user_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	$result = $db->user_get_following($user_id, $newerthan_id, $olderthan_id, $count);

	if ($result != NULL) {
		echoResponse(200, $result);
	} else {
		$response = array();
		$response["error"] = 2003;
		$response["title"] = "Error";
		$response["message"] = "An error occurred. Please try again later.";
		echoResponse(404, $response);
	}
	
});
$app->get('/users/:id/followers', 'authenticate', function($user_id) use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	$result = $db->user_get_followers($user_id, $newerthan_id, $olderthan_id, $count);

	if ($result != NULL) {
		echoResponse(200, $result);
	} else {
		$response = array();
		$response["error"] = 2003;
		$response["title"] = "Error";
		$response["message"] = "An error occurred. Please try again later.";
		echoResponse(404, $response);
	}
	
});









	
$app->post('/users', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'username', 'email', 'password'));

            $response = array();

            // reading post params
            $name = $app->request->post('name');
            $username = $app->request->post('username');
            $email = $app->request->post('email');
            $password = $app->request->post('password');
            $lat = $app->request->post('lat');
            $long = $app->request->post('long');
            $phone = $app->request->post('phone');
            $fbid = $app->request->post('fbid');
			
			if ( is_uploaded_file($_FILES['image']['tmp_name']) ) {
				move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/profile/upload' . rand() . '.jpg');
			}

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->users_post($name, $username, $email, $password, $lat, $long, $phone, $fbid, $imagedata);
			
            $response["error"] = $res["error"];
            if ($res["error"] == 0) {
                $response["title"] = "Success";
				$response["api_key"] = $res["api_key"];
                $response["message"] = "You are successfully registered";
				//resendMobile($res["id"]);
            } else if ($res["error"] == 1){
                $response["title"] = "Error";
                $response["message"] = "Oops! An error occurred while registering";
            } else if ($res["error"] == 3) {
                $response["title"] = "Error";
                $response["message"] = "Sorry, this email address already exists";
            }else if ($res["error"] == 2) {
                $response["title"] = "Error";
                $response["message"] = "Sorry, this username is already in use.";
            }else {
                $response["title"] = "Error";
                $response["message"] = "Oops! An error occurred while registering";
			}
			
            // echo json response
            echoResponse(201, $response);
        });
		
		
		
$app->post('/users/forgotpassword', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username'));
            $response = array();

            // reading post params
            $username = $app->request->post('username');
			
            $db = new DbHandler();
            $res = $db->getUserEmail($username);
			if ($res != NULL){
				$response["error"] = 0;
				$response["title"] = "Check your email";
				$response["message"] = "A link to reset your password has been sent to the email address associated with this account.";
				if ($res["email"] != "stephen2earth@gmail.com"){
					require_once 'libs/Mandrill.php';
					try {
						$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
						$message = array(
							'html' => '<p>Please see the following link to reset your password</p>',
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
				}
           		echoResponse(200, $response);
			}else{
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "This user could not be found.";
				echoResponse(200, $response);
			}
        });
		
		
$app->post('/users/resendmobile', 'authenticate', function() use ($app) {
		// check for required params
	global $current_user_id;
	$response = resendMobile($current_user_id);
	echoResponse(200, $response);
});
function resendMobile($current_user_id){
	$db = new DbHandler();
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
	$db = new DbHandler();
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

            $db = new DbHandler();
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
            $db = new DbHandler();
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

            $db = new DbHandler();
            $res = $db->user_follow($current_user_id, $follow_id,$follow);
			$response["error"] = 0;
			if ($res == 0) {
				$response["status"] = 0;
			} else {
				$response["status"] = 1;
			}
			echoResponse(200, $response);
        });
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
$app->put('/users/:id', 'authenticate', function($user_id) use($app) {
			verifyRequiredParams(array('username','name','bio','phone','email','address','gender'));
            // check for required params
            global $current_user_id;         
			   
			$username = $app->request->put('username');
			$name = $app->request->put('name');
			$bio = $app->request->put('bio');
			$image = $app->request->put('image');
			$phone = $app->request->put('phone');
			$email = $app->request->put('email');
			$address = $app->request->put('address');
			$gender = $app->request->put('gender');
			
			$password = $app->request->put('password');
			$newpassword = $app->request->put('newpassword');

            $db = new DbHandler();
            $response = array();

            // updating item
            $result = $db->user_put($current_user_id, $user_id, $username, $name, $bio, $image, $phone, $email, $address, $gender, $password, $newpassword);
            echoResponse(200, $result);
        });

$app->put('/users/:id/settings', 'authenticate', function($user_id) use($app) {
	verifyRequiredParams(array("notify_message","notify_offer","notify_comment","notify_review","notify_tag","notify_followed","notify_friendjoins","currency","save_originals"));
	global $current_user_id;
	$response = array();
	
	$notify_message = $app->request->put('notify_message');
	$notify_offer = $app->request->put('notify_offer');
	$notify_comment = $app->request->put('notify_comment');
	$notify_review = $app->request->put('notify_review');
	$notify_tag = $app->request->put('notify_tag');
	$notify_followed = $app->request->put('notify_followed');
	$notify_friendjoins = $app->request->put('notify_friendjoins');
	$currency = $app->request->put('currency');
	$save_originals = $app->request->put('save_originals');
			
	$db = new DbHandler();
	if ($user_id == $current_user_id){
		$success = $db->user_put_settings($current_user_id, $user_id, $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals);
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

            $db = new DbHandler();
            $response = array();
            $result = $db->user_delete($current_user_id, $user_id);
            if ($result) {
                $response["error"] = 0;
                $response["message"] = "Account deactivated";
            } else {
                $response["error"] = 2005;
                $response["message"] = "There was an error while deactivating this account. Please try again later.";
            }
            echoResponse(200, $response);
        });
?>