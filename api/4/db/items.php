<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

// GET
$app->get('/items', 'noauthenticate', function()  use ($app){
	//getItems($app,"");
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
            $db = new DbItems();
            $q = $app->request->get('q');
            $newerthan_id = $app->request->get('newerthan_id');
            $olderthan_id = $app->request->get('olderthan_id');
            $count = $app->request->get('count');
            $test = $app->request->get('test');

            // fetching all user items;
            $result = $db->getAllItems($current_user_id, $filter, $q, $newerthan_id, $olderthan_id, $count,$test);
			if (count($result["items"]) == 0 && $newerthan_id != 0){
            	$result = $db->getAllItems($current_user_id, $filter, $q, 0, $olderthan_id, $count,$test);
			}

            $response["error"] = 0;
            $response["items"] = $result;

            echoResponse(200, $response);
        }





$app->get('/items/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $db = new DbItems();

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
				//$result["image"] = $images[0];
				
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
	$db = new DbItems();
	
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_likes($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

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
	$db = new DbItems();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_likes($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

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
	$db = new DbItems();
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
            verifyRequiredParams(array('title','price','quantity','images'));

            $response = array();
            $title = $app->request->post('title');
            $price = $app->request->post('price');
            $description = $app->request->post('description');
			if ($description == NULL){
				$description = "";
			}
            $negotiable = $app->request->post('negotiable');
			if ($negotiable == NULL || $negotiable == ""){
				$negotiable = 1;
			}
            $quantity = $app->request->post('quantity');
            $images = $app->request->post('images');
            $status = $app->request->post('status');
            $currency = $app->request->post('currency');
            $location_id = $app->request->post('location_id');
			$location = array("locality" => $app->request->post('location_locality'), "latitude" => $app->request->post('location_latitude'), "longitude" => $app->request->post('location_longitude'), "admin" => $app->request->post('location_admin'), "country" => $app->request->post('location_country'));
			
            $test = $app->request->post('test');
			
			if ($currency == NULL) $currency = "AUD";

            global $current_user_id;
            $db = new DbItems();
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
				$item_id = $db->items_post($current_user_id, $title,$price,$currency,$description,$quantity,$images, $status,$location_id, $location, $negotiable, $test); // Note that if status is 1, this will be ignored until published.
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
				echoResponse(200, $response);
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
            $db = new DbItems();
			echoResponse(200, $db->item_like($current_user_id, $item_id,$app->request->post('value')));
        });
$app->post('/items/:id/likes', 'authenticate', function($item_id) use ($app) {
            verifyRequiredParams(array('value'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_like($current_user_id, $item_id,$app->request->post('value')));
        });
		
$app->post('/items/:id/comments', 'authenticate', function($item_id) use ($app) {
            verifyRequiredParams(array('value'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_comment($current_user_id, $item_id,$app->request->post('value')));
        });
		
$app->post('/items/:id/report', 'authenticate', function($item_id) use ($app) {
            verifyRequiredParams(array('comment'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_report($current_user_id, $item_id,$app->request->post('comment')));
        });
$app->post('/items/wanted', 'authenticate', function() use ($app) {
            verifyRequiredParams(array('q'));
			global $current_user_id;
            $db = new DbItems();
			$price = $app->request->post('price');
			$currency = $app->request->post('currency');
			$comment = $app->request->post('comment');
			if ($price == NULL || $price == ""){
				$price = 0;
			}
			if ($currency == NULL){
				$currency = "";
			}
			if ($comment == NULL){
				$comment = "";
			}
			echoResponse(200, $db->item_wanted($current_user_id, $app->request->post('q'),$price,$currency, $comment));
        });
$app->post('/items/following', 'authenticate', function() use ($app) {
            verifyRequiredParams(array('q'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_following($current_user_id, $app->request->post('q')));
        });

$app->post('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbConversations();
	$message = $app->request->post('message');
	$price = $app->request->post('price');
	$currency = $app->request->post('currency');
	if ($price == NULL || $price == "") $price = -1;
	$type = $app->request->post('type');
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
        verifyRequiredParams(array('type'));
	}
	$response = $db->conversation_post($current_user_id,NULL,$item_id,NULL,$price, $currency, $message,$type);
	
	if ($response != NULL || $response["error"] == 0) {
		echoResponse(201, $response);
	} else {
		echoResponse(200, $response);
	}
});


$app->post('/items/:id/sales', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
	
	$response = array();
	$db = new DbItems();
	$quantity = $app->request->post('quantity');
	$comment = $app->request->post('comment');
	
	$response = $db->item_sales_post($current_user_id,$item_id,NULL, NULL, $quantity, $comment);
	
	if ($response != NULL || $response["error"] == 0) {
		echoResponse(201, $response);
	} else {
		echoResponse(200, $response);
	}
});



$app->post('/sales/:id/reviews', 'authenticate', function($sale_id) use ($app) {
	global $current_user_id;
	
	$response = array();
	$db = new DbItems();
	$rating = $app->request->post('rating');
	$comment = $app->request->post('comment');
	
	$response = $db->item_sales_reviews($current_user_id, $sale_id, $rating, $comment);
	
	if ($response != NULL || $response["error"] == 0) {
		echoResponse(201, $response);
	} else {
		echoResponse(200, $response);
	}
});



$app->post('/sales/:id/verifypayment', 'authenticate', function($sale_id) use ($app) {
	global $current_user_id;
	
	$response = array();
	$db = new DbItems();$response["error"] = false;
            $response["message"] = "Payment verified successfully";
            global $userId;
 
 
            require_once 'include/Config.php';
 
            try {
                $paymentId = $app->request()->post('paymentId');
                $payment_client = json_decode($app->request()->post('paymentClientJson'), true);
 
                $apiContext = new \PayPal\Rest\ApiContext(
                        new \PayPal\Auth\OAuthTokenCredential(
                        PAYPAL_CLIENT_ID, // ClientID
                        PAYPAL_SECRET      // ClientSecret
                        )
                );
 
                // Gettin payment details by making call to paypal rest api
                $payment = Payment::get($paymentId, $apiContext);
 
                // Verifying the state approved
                if ($payment->getState() != 'approved') {
                    $response["error"] = true;
                    $response["message"] = "Payment has not been verified. Status is " . $payment->getState();
                    echoResponse(200, $response);
                    return;
                }
 
                // Amount on client side
                $amount_client = $payment_client["amount"];
 
                // Currency on client side
                $currency_client = $payment_client["currency_code"];
 
                // Paypal transactions
                $transactions = $payment->getTransactions();
				$transaction = $transactions[0];
                // Amount on server side
                $amount_server = $transaction->getAmount()->getTotal();
                // Currency on server side
                $currency_server = $transaction->getAmount()->getCurrency();
                $sale_state = $transaction->getRelatedResources();
				$sale_state = $sale_state[0]->getSale()->getState();
 
                // Storing the payment in payments table
                $db = new DbItems();
                $response = $db->item_sales_store_payment($sale_id, $payment->getId(), $userId, $payment->getCreateTime(), $payment->getUpdateTime(), $payment->getState(), $amount_server, $amount_server);
 
                // Verifying the amount
                if ($amount_server != $amount_client) {
                    $response["error"] = true;
                    $response["message"] = "Payment amount doesn't matched.";
                    echoResponse(200, $response);
                    return;
                }
 
                // Verifying the currency
                if ($currency_server != $currency_client) {
                    $response["error"] = true;
                    $response["message"] = "Payment currency doesn't matched.";
                    echoResponse(200, $response);
                    return;
                }
 
                // Verifying the sale state
                if ($sale_state != 'completed') {
                    $response["error"] = true;
                    $response["message"] = "Sale not completed";
                    echoResponse(200, $response);
                    return;
                }
 
                echoResponse(200, $response);
            } catch (\PayPal\Exception\PayPalConnectionException $exc) {
                if ($exc->getCode() == 404) {
                    $response["error"] = true;
                    $response["message"] = "Payment not found!";
                    echoResponse(404, $response);
                } else {
                    $response["error"] = true;
                    $response["message"] = "Unknown error occurred!" . $exc->getMessage();
                    echoResponse(500, $response);
                }
            } catch (Exception $exc) {
                $response["error"] = true;
                $response["message"] = "Unknown error occurred!" . $exc->getMessage();
                echoResponse(500, $response);
            }
});

$app->put('/offers/:offerid', 'authenticate', function($offer_id) use ($app) {
            verifyRequiredParams(array('value'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_put_offer($current_user_id, $offer_id,$app->request->put('value'),$app->request->put('price'),$app->request->put('currency'),$app->request->put('message')));
        });

$app->post('/offers/:offerid', 'authenticate', function($offer_id) use ($app) {
            verifyRequiredParams(array('value'));
			global $current_user_id;
            $db = new DbItems();
			echoResponse(200, $db->item_put_offer($current_user_id, $offer_id,$app->request->post('value'),$app->request->post('price'),$app->request->post('currency'),$app->request->post('message')));
        });
		

$app->get('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
	$response = array();
	$db = new DbItems();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetching all user items
	$result = $db->item_get_offers($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

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
            global $current_user_id;    
            $db = new DbItems();
			
			if (!$app->request->post('status')){
           		verifyRequiredParams(array('title','price','description','quantity','images','location_id'));
			}

            // updating item
            $result = $db->updateItem($current_user_id, $item_id, $app->request->post('title'), $app->request->post('price'), $app->request->post('description'), $app->request->post('quantity'), $app->request->post('images'), $app->request->put('status'), $app->request->put('location_id'));
			// NOTE: If status is set, other fields will not be updated
            
			echoResponse(200,$result);
        });


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/items/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;

            $db = new DbItems();
            $response = array();
            $result = $db->deactivateItem($current_user_id, $item_id);
			//echo $result;
            if ($result) {
                // item updated successfully
				$response["error"] = 0;
				$response["message"] = "Item deleted successfully";
				$httpcode = 200;
            } else {
                // item failed to update
				$response["error"] = 1;
				$response["message"] = "Item failed to delete. Please try again.";
				$httpcode = 200;
            }
			echoResponse($httpcode,$response);
        });
		
?>