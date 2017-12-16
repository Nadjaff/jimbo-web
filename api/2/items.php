<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

// GET
$app->get('/items', function()  use ($app){
	getItems($app,"");
});
$app->get('/items/purchased', 'authenticate', function()  use ($app){
	getItems($app,"purchased");
});
$app->get('/items/sold', 'authenticate', function()  use ($app){
	getItems($app,"sold");
});
$app->get('/items/favorites', 'authenticate', function()  use ($app){
	getItems($app,"favorites");
});
$app->get('/items/home', 'authenticate', function()  use ($app){
	getItems($app,"home");
});
$app->get('/items/explore', 'authenticate', function()  use ($app){
	getItems($app,"explore");
});



function getItems($app,$filter){
            global $current_user_id;
            $response = array();
            $db = new DbHandler();
            $q = $app->request->get('q');
            $newerthan_id = $app->request->get('newerthan_id');
            $olderthan_id = $app->request->get('olderthan_id');
            $count = $app->request->get('count');

            // fetching all user items;
            $result = $db->getAllItems($current_user_id, $filter, $q, $newerthan_id, $olderthan_id, $count);

            $response["error"] = 0;
            $response["items"] = $result;

            echoResponse(200, $response);
        }





$app->get('/items/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $db = new DbHandler();

            // fetch item
            $result = $db->item_get($current_user_id, $item_id);

            if ($result != NULL) {
                $result["error"] = 0;				
                $result["images"] = array();
            	//$images = $db->images_get($item_id);
				$images = explode(",",$result["image"]);
				if (count($images) == 0){
            		$result = array();
					$result["error"] = 1;
					$result["message"] = "Sorry, an unknown error occurred.";
					echoResponse(404,$result);
					return;
				}
				$result["images"] = $images;
				$result["image"] = $images[0];
				
            } else {
            	$result = array();
				$result["error"] = 1;
				$result["message"] = "Sorry, this item no longer exists.";
				echoResponse(404,$result);
				return;
            }
			echoResponse(200,$result);
        });
$app->get('/items/:id/likes', 'authenticate', function($item_id)  use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_likes($item_id, $newerthan_id, $olderthan_id, $count);

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
$app->get('/items/:id/stars', 'authenticate', function($item_id)   use ($app){
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_likes($item_id, $newerthan_id, $olderthan_id, $count);

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
$app->get('/items/:id/comments', 'authenticate', function($item_id)  use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_comments($item_id, $newerthan_id, $olderthan_id, $count);

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






/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
$app->post('/items', 'authenticate', function() use ($app) {
	
			// Create an item from whatever data we have
			
            // check for required params
            verifyRequiredParams(array('title','price','quantity','images','location_id'));

            $response = array();
            $title = $app->request->post('title');
            $price = $app->request->post('price');
            $description = $app->request->post('description');
            $quantity = $app->request->post('quantity');
            $images = $app->request->post('images');
            $status = $app->request->post('status');
            $location_id = $app->request->post('location_id');

            global $current_user_id;
            $db = new DbHandler();
			/*$image1 = $db->image_get($current_user_id,$images[0]);
			if ($image1["i"] == -1){
				$response["error"] = 3;
                $response["message"] = "Image unavailable. Please try again.";
                echoResponse(404, $response);
				return;
			}
			if ($image1["item_id"] != 0){
				$response["error"] = 2;
                $response["message"] = "Image unavailable. Please try again.";
                echoResponse(404, $response);
				return;
			}*/
			if ($status == NULL || $status == ""){
				$status = 1;
			}
			if ($description == NULL){
				$description = 1;
			}
			$success = true;
			if ($images != NULL){
				$item_id = $db->items_post($current_user_id, $title,$price,$description,$quantity,$images, $status,$location_id); // Note that if status is 1, this will be ignored until published.
				/*if ($item_id != NULL) {
					$count_images = count($images);
					for($i=0;$i<$count_images;$i++){
						$success = $db->associateImageWithItem($current_user_id, $item_id, $images[$i],$i+1);
						if ($success == false){
							break;
						}
					}
					if ($status == 1 && $success != false){
						$published = $db->publishItem($current_user_id, $item_id);
					}
				}*/
			}
            if ($item_id != NULL) {
                $response["error"] = 0;
                $response["item_id"] = $item_id;
                echoResponse(201, $response);
				return;
            } else {
				$response["error"] = 4;
				$response["message"] = "Failed to create item. Please try again.";
				echoResponse(404, $response);
				return;
            }
        });





/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
		
$app->post('/items/:id/stars', 'authenticate', function($item_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $value = $app->request->post('value');

            $db = new DbHandler();
            $res = $db->item_like($current_user_id, $item_id,$value);
			if ($res == $value) {
				$response["error"] = 0;
				$response["result"] = $res;
				$response["message"] = "Like succeeded.";
			} else {
				$response["error"] = 0;
				$response["result"] = $res;
				$response["message"] = "Like failed.";
			}
			echoResponse(200, $response);
        });
$app->post('/items/:id/likes', 'authenticate', function($item_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $value = $app->request->post('value');

            $db = new DbHandler();
            $res = $db->item_like($current_user_id, $item_id,$value);
			if ($res == $value) {
				$response["error"] = 0;
				$response["result"] = $res;
				$response["message"] = "Like succeeded.";
			} else {
				$response["error"] = 0;
				$response["result"] = $res;
				$response["message"] = "Like failed.";
			}
			echoResponse(200, $response);
        });
		
$app->post('/items/:id/comments', 'authenticate', function($item_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $value = $app->request->post('value');

            $db = new DbHandler();
            $res = $db->item_comment($current_user_id, $item_id,$value);
			if ($res == 0) {
				$response["error"] = 1;
				$response["message"] = "Comment failed.";
			} else {
				$response["error"] = 0;
				$response["id"] = $res;
				$response["message"] = "Comment succeeded.";
			}
			echoResponse(200, $response);
        });
		
		

$app->post('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $value = $app->request->post('value');

            $db = new DbHandler();
            $res = $db->item_offer($current_user_id, $item_id,$value);
			if ($res == 0) {
				$response["error"] = 1;
				$response["message"] = "Offer failed.";
			} else {
				$response["error"] = 0;
				$response["id"] = $res;
				$response["message"] = "Offer succeeded.";
			}
			echoResponse(200, $response);
        });

$app->post('/items/:id/offers/:offerid', 'authenticate', function($item_id, $offer_id) use ($app) {
            // check for required params
            verifyRequiredParams(array('value'));
			global $current_user_id;

            $response = array();

            // reading post params
            $value = $app->request->post('value');

            $db = new DbHandler();
            $res = $db->item_offer($current_user_id, $item_id,$value);
			if ($res == 1) {
				$response["error"] = 1;
				$response["message"] = "Offer failed.";
			} else {
				$response["error"] = 0;
				$response["message"] = "Offer succeeded.";
			}
			echoResponse(200, $response);
        });
		

$app->get('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_offers($item_id, $newerthan_id, $olderthan_id, $count);

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
$app->post('/items/:id/report', 'authenticate', function($item_id) use ($app) {
			$response["error"] = 0;
			$response["id"] = $item_id;
			$response["message"] = "Reporting succeeded.";
			echoResponse(200, $response);
        });
/**
 * Listing single item of particual user
 * method GET
 * url /items/:id
 * Will return 404 if the item doesn't belongs to user
 */



/**
 * Updating existing item
 * method PUT
 * params item, status
 * url - /items/:id
 */
$app->put('/items/:id', 'authenticate', function($item_id) use($app) {
            // check for required params
            verifyRequiredParams(array('title','price','description','quantity','images','location_id'));

            global $current_user_id;            
			
            $title = $app->request->post('title');
            $price = $app->request->post('price');
            $description = $app->request->post('description');
            $quantity = $app->request->post('quantity');
            $image1 = $app->request->post('images');
            $status = $app->request->put('status');
            $location_id = $app->request->put('location_id');

            $db = new DbHandler();
            $response = array();

            // updating item
            $result = $db->updateItem($current_user_id, $item_id, $title, $price, $description, $quantity, $images, $status, $location_id);
            if ($result) {
                // item updated successfully
				$response["error"] = 0;
				$response["message"] = "Item updated successfully";
            } else {
                // item failed to update
				$response["error"] = 1;
				$response["message"] = "Item failed to update. Please try again.";
            }
			echoResponse(200,$response);
        });


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/items/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteItem($current_user_id, $item_id);
            if ($result) {
                // item updated successfully
				$response["error"] = 0;
				$response["message"] = "Item updated successfully";
				$httpcode = 204;
            } else {
                // item failed to update
				$response["error"] = 1;
				$response["message"] = "Item failed to update. Please try again.";
				$httpcode = 200;
            }
			echoResponse($httpcode,$response);
        });
		
?>