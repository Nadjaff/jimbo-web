<?php

/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------POST---------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->post('/items/:id/bids', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
            // check for required params
            $bid = $app->request()->post('bid');
            $max_bid = $app->request()->post('max_bid');
			
            $db = new DbBids();
			
            $response = array();
            // check for correct email and password
			if ($bid != NULL){
				$response = $db->bid_create($current_user_id, $item_id, $bid);
			}else if ($max_bid != NULL){
				$response = $db->max_bid_create($current_user_id, $item_id, $max_bid);
			}else{
        		verifyRequiredParams(array('bid'));
				return;
			}
				echoResponse(200, $response);
        });
		



		
$app->get('/items/:id/bids', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
	$db = new DbBids();
	echoResponse(200,$db->item_get_bids($current_user_id, $item_id, $app->request->get('newerthan_id'), $app->request->get('olderthan_id'), $app->request->get('count')));	
});
?>