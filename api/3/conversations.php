<?php

/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------USERS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->get('/conversations', 'authenticate', function()  use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$item_id = $app->request->get('item_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->conversations_get_all($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);
	
	if ($result != NULL){
		$result["error"] = 0;
	}else{
		$result = array();
		$result["error"] = 1;
		$result["conversations"] = "Could not load conversations. Please try again later";
	}

	echoResponse(200, $result);
});

$app->get('/conversation', 'authenticate', function()  use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
	$user_id = $app->request->get('user_id');
	$item_id = $app->request->get('item_id');
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');
    verifyRequiredParams(array('user_id'));

	// fetching all user items
	$result = $db->conversations_message_user($current_user_id, $user_id, $item_id,$newerthan_id,$olderthan_id,$count);
	
	if ($result != NULL){
		$result["error"] = 0;
	}else{
		$result = array();
		$result["error"] = 1;
		$result["conversations"] = "Could not load conversation. Please try again later";
	}

	echoResponse(200, $result);
});


$app->get('/conversations/:id', 'authenticate', function($conversation_id) use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	$response = $db->conversation_get($current_user_id, $conversation_id,$newerthan_id,$olderthan_id,$count);

	if ($response["error"] == 0) {
		echoResponse(200, $response);
	} else {
		$response["title"] = "Error";
		echoResponse(404, $response);
	}
});




$app->post('/conversation', 'authenticate', function() use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
    verifyRequiredParams(array('message','type'));
	
	$message = $app->request->post('message');
	$type = $app->request->post('type');
	$item_id = $app->request->get('item_id');
	$user_id = $app->request->get('user_id');
	$price = $app->request->post('price');
	$currency = $app->request->post('currency');
	if ($price == NULL || $price == "") $price = -1;
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
        verifyRequiredParams(array('user_id','item_id'));
	}
	$response = $db->conversation_post($current_user_id,NULL,$item_id,$user_id,$price,$currency,$message,$type);
       
	if ($response != NULL || $response["error"] == 0) {
		echoResponse(201, $response);
	} else {
		echoResponse(200, $response);
	}	
			
});



$app->post('/conversations', 'authenticate', function() use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
    verifyRequiredParams(array('message','type'));
	
	$message = $app->request->post('message');
	$type = $app->request->post('type');
	$item_id = $app->request->post('item_id');
	$user_id = $app->request->post('user_id');
	$price = $app->request->post('price');
	$currency = $app->request->post('currency');
	if ($price == NULL || $price == "") $price = -1;
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
        verifyRequiredParams(array('user_id'));
	}
	$response = $db->conversation_post($current_user_id,NULL,$item_id,$user_id,$price,$currency,$message,$type);
	if ($response != NULL || $response["error"] == 0) {
		echoResponse(201, $response);
	} else {
		echoResponse(200, $response);
	}
});
$app->post('/conversations/:id', 'authenticate', function($conversation_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
    verifyRequiredParams(array('message','type'));
	
	$message = $app->request->post('message');
	$type = $app->request->post('type');
	$price = $app->request->post('price');
	$currency = $app->request->post('currency');
	if ($price == NULL || $price == "") $price = -1;
	$response = $db->conversation_post($current_user_id,$conversation_id,NULL,NULL,$price, $currency,$message,$type);
	if ($response != NULL) {
		echoResponse(201, $response);
	} else {
		$response = array();
		$response["error"] = 1;
		$response["title"] = "Error";
		$response["message"] = "Failed to send message. Please try again";
		echoResponse(200, $response);
	}
});
$app->put('/conversations/:id/:messageid', 'authenticate', function($conversation_id,$message_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	
    verifyRequiredParams(array('action'));
	$action = $app->request->post('action');
	
	$response = $db->conversation_put_message($current_user_id,$conversation_id,$message_id,$action);
	
	if ($response != NULL) {
		echoResponse(200, $response);
	} else {
		$response = array();
		$response["error"] = 1;
		$response["title"] = "Error";
		$response["message"] = "Operation failed. Please try again";
		echoResponse(200, $response);
	}
});
		
		


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
/*$app->delete('/images/:id', 'authenticate', function($image_id) use($app) {
            global $current_user_id;

            $db = new DbConversations();
            $response = array();
            $result = $db->deleteImage($current_user_id, $image_id);
            if ($result) {
                // item deleted successfully
                $response["error"] = 0;
                $response["message"] = "Item deleted succesfully";
            } else {
                // item failed to delete
                $response["error"] = 1;
                $response["message"] = "Item failed to delete. Please try again!";
            }
            echoResponse(200, $response);
        });*/
?>