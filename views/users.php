<?php
$app->get('/users/forgotpassword/{token}',  function ($request, $response, $args) {
			$db = new DbTokens();
			$token=(int)$args['token'];
		   return  $this->view->render($response, "resetpassword.php",$db->getToken($token));
});
		$app->get('/users/verifyemail/:token', function($token) use ($app) {
			$db = new DbTokens();
			$app->render("verifyemail.php",$db->useTokenVerify($token));
});
$app->post('/users/forgotpassword/:token', function($token) use ($app) {
            $db = new DbTokens();
			$response = array();
			$pass = $app->request->post('password');
			if ($pass != ""){
				$response = $db->useTokenPassword($token, $pass);
			}
           	echoResponse(200, $response);
});
?>