<?php

/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
$app->post('/items/{id}/bids', function($request, $response, $args){
	postbids($request, $response, $args);
})->add($authentication);	
$app->get('/items/{id}/bids', function ($request, $response, $args) {
	getbids($request, $response, $args,"");
})->add($authentication);

function postbids($request, $response, $args){
	global $current_user_id;
	global $r;
            // check for required params
            $bid = $request->getParsedBodyParam('bid','');
            $max_bid = $request->getParsedBodyParam('max_bid','');
			
            $db = new DbBids();
			
            $r = array();
			//print_r($bid); exit();
			$item_id=(int)$args['id'];
			//print_r($item_id); exit();
            // check for correct email and password
			if ($bid != NULL){
				
				$r = $db->bid_create($current_user_id, $item_id, $bid);
			}else if ($max_bid != NULL){
				
				$r = $db->max_bid_create($current_user_id, $item_id, $max_bid);
			}else{
        		verifyRequiredParams($request,$response,array('bid'));
				return;
			}
			return $response->withStatus(200);
				//echoResponse(200, $response);
        }
		
function getbids($request, $response, $args){
	global $current_user_id;
	global $r;
	$r=array();
	$db = new DbBids();
	$item_id=(int)$args['id'];
	$newerthan_id =$request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
    $count = $request->getParsedBodyParam('count',null);
	//echoResponse(200,$db->item_get_bids($current_user_id, $item_id, $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));	
	$r = $db->item_get_bids($current_user_id, $item_id,$newerthan_id,$olderthan_id,$count);
      return $response->withStatus(200);
	}
?>