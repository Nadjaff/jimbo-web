<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

// GET=
$app->get('/notifications', 'authenticate', function()  use ($app){
	
	global $current_user_id;
	$response = array();
	$db = new DbNotifications();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');
	$test = $app->request->get('test');

	// fetching all user items;
	$result = $db->get_notifications($current_user_id, $newerthan_id, $olderthan_id, $count,$test);

	$response["error"] = 0;
	$response["notifications"] = $result;

	echoResponse(200, $response);
});
$app->get('/notifications/:id', 'authenticate', function($nid)  use ($app){
	global $current_user_id;
	$db = new DbNotifications();
	$result = $db->read_notification($current_user_id, $nid);
	
	$response = array();

	$response["error"] = 0;
	$response["read"] = 1;

	echoResponse(200, $response);
});
$app->get('/notifications/update', function()  use ($app){
	
	$db = new DbNotifications();
	$result = $db->send_update();
	
	$response = array();

	$response["error"] = 0;
	$response["notifications"] = $result;

	echoResponse(200, $response);
});

		
?>