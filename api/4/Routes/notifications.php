<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

// GET=
$app->get('/notifications', function($request, $response, $args) {
	getNotification($request, $response, $args);
	
})->add($authentication);

$app->get('/notifications/{id:[0-9]+}', function($request, $response, $args){
	getNotificationById($request, $response, $args);
	
})->add($authentication);
$app->get('/notifications/update', function($request, $response, $args){
	getNotificationUpdate($request, $response, $args);
	
})->add($authentication);
function getNotification($request, $response, $args){
	global $current_user_id;
	global $r;
	$r = array();
	$db = new DbNotifications();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);
	$test = $request->getParsedBodyParam('test',null);
	// fetching all user items;
	$result = $db->get_notifications($current_user_id, $newerthan_id, $olderthan_id, $count,$test);
	$r["error"] = 0;	
	$r["notifications"] = $result;
	$response->withStatus(200);
}
function getNotificationById($request, $response, $args){
    global $r;	
	global $current_user_id;
	$db = new DbNotifications();
	$nid=(int)$args['id'];
	$result = $db->read_notification($current_user_id, $nid);

	$r = array();

	$r["error"] = 0;
	$r["read"] = 1;
    $response->withStatus(200);
}
function getNotificationUpdate($request, $response, $args)	{
	$db = new DbNotifications();

	$result = $db->send_update();
	global $r;	
	$r = array();

	$r["error"] = 0;
	$r["notifications"] = $result;

	$response->withStatus(200);
}
?>