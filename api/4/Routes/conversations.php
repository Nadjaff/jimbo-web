<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------USERS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->get('/conversations', function($request, $response, $args)  {
	getConversations($request, $response, $args,"");
})->add($authentication);

$app->get('/conversation/{id}/{item_id}', function($request, $response, $args){
	getConversation($request, $response, $args);
})->add($authentication);

$app->get('/conversations/{id}', function($request, $response, $args)  {
	getConversationsId($request, $response, $args,"");	
})->add($authentication);

$app->post('/conversation', function($request, $response, $args)  {
	postConversation($request, $response, $args);			
})->add($authentication);
$app->post('/conversations', function ($request, $response, $args) {	
	postConversations($request, $response, $args);	
})->add($authentication);

$app->post('/conversations/{id}',  function ($request, $response, $args) {
	postConversationId($request, $response, $args);
	
})->add($authentication);
$app->put('/conversations/{id}/{message_id}',  function ($request, $response, $args){
	putConversations($request, $response, $args);
	
})->add($authentication);

	function getConversationsId($request, $response, $args)
{
	 $conversation_id = (int)$args['id'];
	global $r;
	global $current_user_id;
	$r = array();
	$db = new DbConversations();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);
	$r= $db->conversation_get($current_user_id, $conversation_id,$newerthan_id,$olderthan_id,$count);
	if ($r["error"] == 0) {
	return	$response->withStatus(200);
	} else {
		$r["title"] = "Error";
		return $response->withStatus(404);
	}
	
}	
function getConversations($request, $response, $args)
{
	global $r;
	global $current_user_id;
	$r=array();
	$db = new DbConversations();
	
	$newerthan_id = $request->getParsedBodyParam('newerthan_id','');
	$olderthan_id = $request->getParsedBodyParam('olderthan_id','');
	$item_id = $request->getParsedBodyParam('item_id','');
	$count = $request->getParsedBodyParam('count','');

	// fetching all user items
	$r = $db->conversations_get_all($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);
	
	if ($r != NULL){
		$r["error"] = 0;
	}else{
		$r = array();
		$r["error"] = 1;
		$r["conversations"] = "Could not load conversations. Please try again later";
	}
     return $response->withStatus(200);
	
}

function postConversations($request, $response, $args){
	global $current_user_id;
	global $r;

	$db = new DbConversations();
	
    if(verifyRequiredParams($request,$response,array('message','type','item_id')) == null)
	{
		return $response->withStatus(400);
	}
	
	$message = $request->getParsedBodyParam('message','');
	$type =$request->getParsedBodyParam('type','');
	$item_id = $request->getParsedBodyParam('item_id','');
	$user_id = $request->getParsedBodyParam('user_id',null);
	$price = $request->getParsedBodyParam('price','');
	$currency = $request->getParsedBodyParam('currency','');
	if ($price == NULL || $price == "") $price = -1;
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
         if( verifyRequiredParams($request,$response,array('user_id')) == null)
	{
		return $response->withStatus(400);
	}
	
	}
	$r = $db->conversation_post($current_user_id,NULL,$item_id,$user_id,$price,$currency,$message,$type);
	if ($r != NULL || $r["error"] == 0) {
		return $response->withStatus(201);
	} else {
	   return $response->withStatus(200);
	}
}
function postConversationId($request, $response, $args){
	 global $current_user_id;
	 global $r;
	$db = new DbConversations();
	$conversation_id = (int)$args['id'];
    verifyRequiredParams($request, $response,array('message','type'));
	
	$message = $request->getParsedBodyParam('message','');
	$type = $request->getParsedBodyParam('type','');
	$price = $request->getParsedBodyParam('price','');
	$currency = $request->getParsedBodyParam('currency','');
	if ($price == NULL || $price == "") $price = -1;
	$r = $db->conversation_post($current_user_id,$conversation_id,NULL,NULL,$price, $currency,$message,$type);
	if ($r != NULL) {
		$response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 1;
		$r["title"] = "Error";
		$r["message"] = "Failed to send message. Please try again";
		$response->withStatus(200);
	}
}
function putConversations($request, $response, $args){ 
    global $current_user_id;
    global $r;
	$r = array();
	$db = new DbConversations();	
	$conversation_id = (int)$args['id'];
	$message_id = (int)$args['message_id'];
    verifyRequiredParams($request, $response,array('action'));
	$action = $request->getParsedBodyParam('action','');	
	$r = $db->conversation_put_message($current_user_id,$conversation_id,$message_id,$action);	
	if ($r != NULL) {
    	return	$response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 1;
		$r["title"] = "Error";
		$r["message"] = "Operation failed. Please try again";
	    return  $response->withStatus(200);
	}
}
function getConversation($request, $response, $args){
	global $current_user_id;
	global $r ;
	$r = array();
	$db = new DbConversations();	
	//$user_id = $request->getParsedBodyParam('user_id','');
	//$item_id = $request->getParsedBodyParam('item_id','');
	$user_id = (int)$args['id'];
	$item_id = (int)$args['item_id'];
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);
    verifyRequiredParams($request,$response,array('user_id'));
	// fetching all user items
	$r = $db->conversations_message_user($current_user_id, $user_id, $item_id,$newerthan_id,$olderthan_id,$count);
	if ($r != NULL){
		$r["error"] = 0;
	}else{
		$r = array();
		$r["error"] = 1;
		$r["conversations"] = "Could not load conversation. Please try again later";
	}
	   return  $response->withStatus(200);
}

function postConversation($request, $response, $args)
{
	global $r ;
	global $current_user_id;
	$r = array();
	$db = new DbConversations();	
    if(verifyRequiredParams($request, $response, array('message','type')) ==null){
	   return $response->withStatus(400);
	}		
	$message = $request->getParsedBodyParam('message','');
	$type = $request->getParsedBodyParam('type','');
	$item_id = $request->getParsedBodyParam('item_id','');
	$user_id = $request->getParsedBodyParam('user_id','');
	$price = $request->getParsedBodyParam('price','');
	$currency = $request->getParsedBodyParam('currency','');
	if ($price == NULL || $price == "") $price = -1;
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
       if( verifyRequiredParams($request, $response, array('user_id','item_id'))==null){
	     return $response->withStatus(400);
	   }
	}
	$r = $db->conversation_post($current_user_id,NULL,$item_id,$user_id,$price,$currency,$message,$type);
       
	if ($r != NULL || $r["error"] == 0) {
		 return  $response->withStatus(200);
	} else {
		 return  $response->withStatus(200);
	}	
}
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