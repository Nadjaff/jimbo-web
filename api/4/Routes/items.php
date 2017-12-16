<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

// GET
$app->get('/items', function ($request, $response, $args) {
	getItems($request, $response, $args,"");
})->add($public);
$app->get('/items/purchased',  function ($request, $response, $args) {
	getItems($request, $response, $args,"purchased");
})->add($authentication);
$app->get('/items/sold', function ($request, $response, $args) {
	getItems($request, $response, $args,"sold");
})->add($authentication);
$app->get('/items/favorites', function ($request, $response, $args) {
	getItems($request, $response, $args,"favorites");
})->add($authentication);
$app->get('/items/home', function ($request, $response, $args) {
	getItems($request, $response, $args,"home");
})->add($authentication);
$app->get('/items/explore', function ($request, $response, $args) {
	getItems($request, $response, $args,"explore");
})->add($authentication);


// Item Details
$app->post('/items', function ($request, $response, $args) {
	postItem($request, $response, $args,"");
})->add($authentication);

$app->get('/items/{id}', function ($request, $response, $args) {
	getItem($request, $response, $args,"");
})->add($public);
$app->put('/items/{id}', function ($request, $response, $args) {
	putItem($request, $response, $args,"");
})->add($authentication);


$app->post('/items/{id}/likes', function ($request, $response, $args) {
	postItemLikes($request, $response, $args,"");
})->add($authentication);
$app->get('/items/{id}/likes', function ($request, $response, $args) {
	getItemLikes($request, $response, $args,"");
})->add($public);



$app->post('/items/{id}/stars', function ($request, $response, $args) {
	postItemStars($request, $response, $args,"");
})->add($authentication);
$app->get('/items/{id}/stars', function ($request, $response, $args) {
	getItemStars($request, $response, $args,"");
})->add($public);

$app->post('/items/{id}/comments', function ($request, $response, $args) {
	postItemComments($request, $response, $args,"");
})->add($authentication);
$app->get('/items/{id}/comments', function ($request, $response, $args) {
	getItemComments($request, $response, $args,"");
})->add($public);

$app->post('/items/{id}/offers', function ($request, $response, $args) {
	postItemOffers($request, $response, $args,"");
})->add($authentication);
$app->get('/items/{id}/offers', function ($request, $response, $args) {
	getItemOffers($request, $response, $args,"");
})->add($authentication);



$app->post('/items/{id}/report', function ($request, $response, $args) {
	postItemReports($request, $response, $args,"");
})->add($authentication);
/*$app->get('/items/{id}/report', function ($request, $response, $args) {
	getItemReports($request, $response, $args,"");
})->add($authentication);*/


$app->post('/items/{id}/sales', function ($request, $response, $args) {
	postItemSales($request, $response, $args,"");
})->add($authentication);
/*$app->get('/items/{id}/sales', function ($request, $response, $args) {
	getItemSales($request, $response, $args,"");
})->add($authentication);*/

$app->post('/items/wanted', function ($request, $response, $args) {
	postItemsWanted($request, $response, $args,"");
})->add($authentication);
/*$app->get('/items/wanted', function ($request, $response, $args) {
	getItemsWanted($request, $response, $args,"");
})->add($authentication);*/


$app->post('/items/following', function ($request, $response, $args) {
	postItemsFollowing($request, $response, $args,"");
})->add($authentication);
/*$app->get('/items/following', function ($request, $response, $args) {
	getItemsFollowing($request, $response, $args,"");
})->add($authentication);*/






function getItems($request, $response, $args,$filter){
    global $current_user_id;
	global $r;
    $db = new DbItems();
    $q = $request->getParsedBodyParam('q',"");
    $newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
    $olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
    $count = $request->getParsedBodyParam('count',null);
    $test = $request->getParsedBodyParam('test',null);

    // fetching all user items;
    $r2 = $db->getAllItems($current_user_id, $filter, $q, $newerthan_id, $olderthan_id, $count,$test);
	if (isset($r2['items']) && count($r2["items"]) == 0 && $newerthan_id != 0){
    	$r2 = $db->getAllItems($current_user_id, $filter, $q, 0, $olderthan_id, $count,$test);
	}

    $r = array();
    $r["error"] = 0;
    $r["items"] = $r2;

    return $response->withStatus(200);
}





function getItem($request, $response, $args,$filter){
    global $current_user_id;
	global $r;
    $db = new DbItems();
	$item_id = (int)$args['id'];


    $r = array();

    // fetch item
    $r = $db->item_get($current_user_id, $item_id);

    if ($r != NULL && $r['id'] != NULL) {
        $r["error"] = 0;				
        $r["images"] = array();
    	//$images = $db->images_get($item_id);
		$images = explode(",",$r["image"]);
		if (count($images) == 0){
			$r = array();
			$r["error"] = 1;
			$r["message"] = "Sorry, an unknown error occurred.";
    		return $response->withStatus(404);
		}
		$r["images"] = $images;
		//$r["image"] = $images[0];
		
    } else {
		$r = array();
		$r["error"] = 1;
		$r["message"] = "Sorry, this item no longer exists.";
    	return $response->withStatus(404);
    }
	return $response->withStatus(200);
}


//$app->get('/items/:id/likes', 'authenticate', function($item_id)  use ($app) {

function getItemLikes($request, $response, $args,$filter){
	global $current_user_id;
global $r;
	global $r;
	$item_id = (int)$args['id'];
	$r = array();
	$db = new DbItems();
	
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);

	// fetching all user items
	$r = $db->item_get_likes($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

	if ($r != NULL) {
		return $response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 2003;
		$r["title"] = "Error";
		$r["message"] = "An error occurred. Please try again later.";
		return $response->withStatus(404);
	}
}	
function getItemStars($request, $response, $args,$filter){
//$app->get('/items/:id/stars', 'authenticate', function($item_id)   use ($app){
	global $current_user_id;
	$item_id = (int)$args['id'];
    global $r;
	$r = array();
	$db = new DbItems();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);

	// fetching all user items
	$r = $db->item_get_likes($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

	if ($r != NULL) {
		return $response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 2003;
		$r["title"] = "Error";
		$r["message"] = "An error occurred. Please try again later.";
		return $response->withStatus(404);
	}
	
}
function getItemComments($request, $response, $args,$filter){
//$app->get('/items/:id/comments', 'authenticate', function($item_id)  use ($app) {
	global $current_user_id;
	$item_id = (int)$args['id'];
    global $r;
	$db = new DbItems();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count =$request->getParsedBodyParam('count',null);

	// fetching all user items
	$r = $db->item_get_comments($item_id, $newerthan_id, $olderthan_id, $count);

	if ($r != NULL) {
		return $response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 2003;
		$r["title"] = "Error";
		$r["message"] = "An error occurred. Please try again later.";
		return $response->withStatus(404);
	}
	
}






/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
function postItem($request, $response, $args,$filter){
//$app->post('/items', 'authenticate', function() use ($app) {
	
			// Create an item from whatever data we have
			
			$db_img = new DbImages();
			
            $img_key = '';
            $img_name = '';

			/*if($request->post('image') !== null){
				$image = str_replace('@testuploads/', '', $app->request->post('image'));
				$db_img->s3_setup();
				$uploads_api_3_tmp = $db_img->getApi3Base();
				$testbot_testuploads_tmp = $db_img->getTestbotBase();

				$filename = 'u' . uniqid() . '.jpg';

				$image = file_get_contents($testbot_testuploads_tmp.$image);
				$image = imagecreatefromstring($image);
				$cropped = move_crop_and_return_image($image, $uploads_api_3_tmp . $filename, 610, 610, $uploads_api_3_tmp);
				$file = $db_img->__fetchFile($filename, $uploads_api_3_tmp);

				// Setup the needed variables
			 	$db_img->_setPutObjectVariables($db->_getBucketItems() . $filename, $file);

				// Put the file to s3
				$s3_result = $db_img->_putObject(true);

				if($s3_result['status']) {
					$img_name = $s3_result['result'];
				}

				$img_key = 'image';

			}
			else{*/
				$img_key = 'images';
				$img_name = $request->getParsedBodyParam('images',"");
			//}

            // check for required params
            verifyRequiredParams($request,$response,array('title','price','currency','quantity',$img_key));

            $title = $request->getParsedBodyParam('title',"");
            $price = $request->getParsedBodyParam('price',"");
            $description = $request->getParsedBodyParam('description',"");
			if ($description == NULL){
				$description = "";
			}
            $negotiable = $request->getParsedBodyParam('negotiable',0);;
			if ($negotiable == NULL || $negotiable == ""){
				$negotiable = 1;
			}
            $quantity = $request->getParsedBodyParam('quantity',1);
            $images = $img_name/*$app->request->post('images')*/;
            $status = 1;//$request->getParsedBodyParam('status',1);
            $currency = $request->getParsedBodyParam('currency',"");
            $location_id = $request->getParsedBodyParam('location_id',"");
			$location = array("locality" => $request->getParsedBodyParam('location_locality',""), "latitude" => $request->getParsedBodyParam('location_latitude',""), "longitude" => $request->getParsedBodyParam('location_longitude',""), "admin" => $request->getParsedBodyParam('location_admin',""), "country" => $request->getParsedBodyParam('location_country',""));
			
            $test = $request->getParsedBodyParam('test',"");;
			
			if ($currency == NULL) $currency = "AUD";

            global $current_user_id;
			global $r;
            $db = new DbItems();
			/*$image1 = $db->image_get($current_user_id,$images[0]);
			if ($image1["i"] == -1){
				$response["error"] = 3;
                $response["message"] = "Image unavailable. Please try again.";
                return $response->withStatus(404);
				return;
			}
			if ($image1["item_id"] != 0){
				$response["error"] = 2;
                $response["message"] = "Image unavailable. Please try again.";
                return $response->withStatus(404);
				return;
			}*/
			if ($status == NULL || $status == ""){
				$status = 1;
			}
			if ($description == NULL){
				$description = 1;
			}
			$success = true;
			/*if ($images != NULL){*/
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
			/*}*/
			$r = array();
            if ($item_id != NULL) {
                $r["error"] = 0;
                $r["item_id"] = $item_id;
                return $response->withStatus(201);
            } else {
				$r["error"] = 4;
				$r["message"] = "Failed to create item. Please try again.";
				return $response->withStatus(200);
            }
        }





/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
		
function postItemStars($request, $response, $args,$filter){
//$app->post('/items/:id/stars', 'authenticate', function($item_id) use ($app) {
            // check for required params
			$item_id=(int)$args['id'];
            if (($v = verifyRequiredParams($request,$response,array('value'))) == null) return $v;
			global $current_user_id;
			global $r;
            $db = new DbItems();
			$r = ($db->item_like($current_user_id, $item_id,$request->getParsedBodyParam('value','')));
			return $response->withStatus(200);
        }
function postItemLikes($request, $response, $args,$filter){
//$app->post('/items/:id/likes', 'authenticate', function($item_id) use ($app) {
	        $item_id=$args['id'];
           if (($v = verifyRequiredParams($request,$response,array('value'))) == null) return $v;
			global $current_user_id;
			global $r;
            $db = new DbItems();
			$r = ($db->item_like($current_user_id, $item_id,$request->getParsedBodyParam('value','')));
			return $response->withStatus(200);
        }
		
function postItemComments($request, $response, $args,$filter){
//$app->post('/items/:id/comments', 'authenticate', function($item_id) use ($app) {
	       $item_id=$args['id'];
            if (($v = verifyRequiredParams($request,$response,array('value'))) == null) return $v;
			global $current_user_id;
			global $r;
            $db = new DbItems();
			$r = ($db->item_comment($current_user_id, $item_id,$request->getParsedBodyParam('value','')));
			return $response->withStatus(200);
        }
		
function postItemReports($request, $response, $args,$filter){
//$app->post('/items/:id/report', 'authenticate', function($item_id) use ($app) {
            if (($v = verifyRequiredParams($request,$response,array('comment'))) == null) return $v;
			global $current_user_id;
			global $r;
			$item_id=(int)$args['id'];
            $db = new DbItems();
			$r = ($db->item_report($current_user_id, $item_id,$request->getParsedBodyParam('comment','')));
			return $response->withStatus(200);
   }
function postItemsWanted($request, $response, $args,$filter){
//$app->post('/items/wanted', 'authenticate', function() use ($app) {
            if (($v = verifyRequiredParams($request,$response,array('q'))) != null) return $v;
			global $current_user_id;
			global $r;
            $db = new DbItems();
			$price = $request->getParsedBodyParam('price','');
			$currency = $request->getParsedBodyParam('currency','');
			$comment = $request->getParsedBodyParam('comment','');
			if ($price == NULL || $price == ""){
				$price = 0;
			}
			if ($currency == NULL){
				$currency = "";
			}
			if ($comment == NULL){
				$comment = "";
			}
			$r = ($db->item_wanted($current_user_id, $request->getParsedBodyParam('q',''),$price,$currency, $comment));
			return $response->withStatus(200);
        }
function postItemsFollowing($request, $response, $args,$filter){
//$app->post('/items/following', 'authenticate', function() use ($app) {
            if (($v = verifyRequiredParams($request,$response,array('q'))) == null) return $v;
			global $current_user_id;
            global $r;
            $db = new DbItems();
			$r = ($db->item_following($current_user_id, $request->getParsedBodyParam('q','')));			
			return $response->withStatus(200);
        }

function postItemOffers($request, $response, $args,$filter){
//$app->post('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
   global $r;
	$r = array();
	$db = new DbConversations();
	$message = $request->getParsedBodyParam('message','');
	$price = $request->getParsedBodyParam('price','');
	$currency = $request->getParsedBodyParam('currency','');
	$item_id=(int)$args['id'];
	if ($price == NULL || $price == "") $price = -1;
	$type = $request->getParsedBodyParam('type','');
	if (($user_id == NULL || $user_id == "") && ($item_id == NULL && $item_id == "")){
        verifyRequiredParams($request,$response,array('type'));
	}
	$r = $db->conversation_post($current_user_id,NULL,$item_id,NULL,$price, $currency, $message,$type);
	
	if ($r != NULL || $r["error"] == 0) {
		return $response->withStatus(201);
	} else {
		return $response->withStatus(200);
	}
}


function postItemSales($request, $response, $args,$filter){
//$app->post('/items/:id/sales', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
global $r;
	$item_id=(int)$args['id'];
	$r = array();
	$db = new DbItems();
	$quantity = $request->getParsedBodyParam('quantity','');
	$comment = $request->getParsedBodyParam('comment','');
	
	$r = $db->item_sales_post($current_user_id,$item_id,NULL, NULL, $quantity, $comment);
	
	if ($r != NULL || $r["error"] == 0) {
		return $response->withStatus(201);
	} else {
		return $response->withStatus(200);
	}
}



function postSaleReviews($request, $response, $args,$filter){
//$app->post('/sales/:id/reviews', 'authenticate', function($sale_id) use ($app) {
	global $current_user_id;
global $r;
	
	$r = array();
	$db = new DbItems();
	$rating = $app->request->post('rating');
	$comment = $app->request->post('comment');
	
	$r = $db->item_sales_reviews($current_user_id, $sale_id, $rating, $comment);
	
	if ($r != NULL || $r["error"] == 0) {
		return $response->withStatus(201);
	} else {
		return $response->withStatus(200);
	}
}



function postSaleVerifyPayment($request, $response, $args,$filter){
//$app->post('/sales/:id/verifypayment', 'authenticate', function($sale_id) use ($app) {
	global $current_user_id;
global $r;
	
	$r = array();
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
                    return $response->withStatus(200);
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
                $r = $db->item_sales_store_payment($sale_id, $payment->getId(), $userId, $payment->getCreateTime(), $payment->getUpdateTime(), $payment->getState(), $amount_server, $amount_server);
 
                // Verifying the amount
                if ($amount_server != $amount_client) {
                    $r["error"] = true;
                    $r["message"] = "Payment amount doesn't matched.";
                    return $response->withStatus(200);
                    return;
                }
 
                // Verifying the currency
                if ($currency_server != $currency_client) {
                    $r["error"] = true;
                    $r["message"] = "Payment currency doesn't matched.";
                    return $response->withStatus(200);
                    return;
                }
 
                // Verifying the sale state
                if ($sale_state != 'completed') {
                    $r["error"] = true;
                    $r["message"] = "Sale not completed";
                    return $response->withStatus(200);
                    return;
                }
 
                return $response->withStatus(200);
            } catch (\PayPal\Exception\PayPalConnectionException $exc) {
                if ($exc->getCode() == 404) {
                    $r["error"] = true;
                    $r["message"] = "Payment not found!";
                    return $response->withStatus(404);
                } else {
                    $r["error"] = true;
                    $r["message"] = "Unknown error occurred!" . $exc->getMessage();
                    return $response->withStatus(500);
                }
            } catch (Exception $exc) {
                $r["error"] = true;
                $r["message"] = "Unknown error occurred!" . $exc->getMessage();
                return $response->withStatus(500);
            }
}

function putOffer($request, $response, $args,$filter){
//$app->put('/offers/:offerid', 'authenticate', function($offer_id) use ($app) {
            verifyRequiredParams($request,$response,array('value'));
			global $current_user_id;
global $r;
            $db = new DbItems();
			$r = ($db->item_put_offer($current_user_id, $offer_id,$app->request->put('value'),$app->request->put('price'),$app->request->put('currency'),$app->request->put('message')));
			return $response->withStatus(200);
        }

function postOffer($request, $response, $args,$filter){
//$app->post('/offers/:offerid', 'authenticate', function($offer_id) use ($app) {
            verifyRequiredParams($request,$response,array('value'));
			global $current_user_id;
global $r;
            $db = new DbItems();
			$r = ($db->item_put_offer($current_user_id, $offer_id,$app->request->post('value'),$app->request->post('price'),$app->request->post('currency'),$app->request->post('message')));
			return $response->withStatus(200);
        }
		

function getItemOffers($request, $response, $args,$filter){
//$app->get('/items/:id/offers', 'authenticate', function($item_id) use ($app) {
	global $current_user_id;
    global $r;
   $item_id=(int)$args['id'];
	$r = array();
	$db = new DbItems();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);

	// fetching all user items
	$r = $db->item_get_offers($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);

	if ($r != NULL) {
		return $response->withStatus(200);
	} else {
		$r = array();
		$r["error"] = 2003;
		$r["title"] = "Error";
		$r["message"] = "An error occurred. Please try again later.";
		return $response->withStatus(404);
	}
	
}
// $app->post('/items/:id/report', 'authenticate', function($item_id) use ($app) {
// 			$response["error"] = 0;
// 			$response["id"] = $item_id;
// 			$response["message"] = "Reporting succeeded.";
// 			return $response->withStatus(200);
//         }

// For extension
function viewPostFromBookmarklet($request, $response, $args,$filter){
//$app->get('/items/create/bookmarklet', function() use ($app) {
    //$db = new DbItems();
	$vars = array();
	$vars["title"] = $app->request->get("title");
	$vars["description"] = $app->request->get("description");
	$vars["price"] = $app->request->get("price");
	$vars["url"] = $app->request->get("url");
	$vars["img"] = $app->request->get("img");
    // fetch item
    //$r = $db->item_get(0, $item_id);		
	//$r = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
	$app->render("create_extension.php",$vars);

    /*if ($r != NULL) {
        $r["error"] = 0;				
        $r["images"] = array();
    	//$images = $db->images_get($item_id);
		$images = explode(",",$r["image"]);
		if (count($images) == 0){
    		$r = array();
			$r["error"] = 1;
			$r["message"] = "Sorry, an unknown error occurred.";
			//echoResponse(404,$r);
			return;
		}
		$r["images"] = $images;
		//$r["image"] = $images[0];
		
    } else {
    	$r = array();
		$r["error"] = 1;
		$r["message"] = "Sorry, this item no longer exists.";
		//echoResponse(404,$r);
		return;
    }
	//echoResponse(200,$r);*/
}


function postItemsFromBookmarklet($request, $response, $args,$filter){
//$app->post('/items/create/bookmarklet', function() use ($app) {
    $db = new DbItems();
    // var_dump($current_user_id);die;
	$vars = array();
	$vars["title"] = $app->request->post("title");
	$vars["description"] = $app->request->post("description");
	$vars["price"] = $app->request->post("price");
	$vars["url"] = $app->request->post("url");
	$vars["img"] = $app->request->post("img");
	$vars["quantity"] = $app->request->post("quantity");

	$test = 0;

	if($app->request->post("test"))
		$test = $app->request->post("test");

    // fetch item
	$r = $db->items_post_from_url(1, $vars["title"],$vars["price"],$vars["description"],$vars["quantity"],$vars["img"],1,1, $test);	
	//$r = array("title" => "SOME TITLE", "description" => "SOME DESCRIPTION", "image" => "default.jpg");
	echo ($r);

    /*if ($r != NULL) {
        $r["error"] = 0;				
        $r["images"] = array();
    	//$images = $db->images_get($item_id);
		$images = explode(",",$r["image"]);
		if (count($images) == 0){
    		$r = array();
			$r["error"] = 1;
			$r["message"] = "Sorry, an unknown error occurred.";
			//echoResponse(404,$r);
			return;
		}
		$r["images"] = $images;
		//$r["image"] = $images[0];
		
    } else {
    	$r = array();
		$r["error"] = 1;
		$r["message"] = "Sorry, this item no longer exists.";
		//echoResponse(404,$r);
		return;
    }
	//echoResponse(200,$r);*/
}
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
function putItem($request, $response, $args,$filter){
//$app->put('/items/:id', 'authenticate', function($item_id) use($app) {
            // check for required params
            global $current_user_id;
            global $r;    
            $db = new DbItems();
            $db_img = new DbImages();
			
            $img_key = '';
            $img_name = '';		
            $item_id=(int)$args['id'];
			if($_FILES['image']['tmp_name'] !== null){
				$image = str_replace('@testuploads/', '', $_FILES['image']['tmp_name']);
				$db_img->s3_setup();
				$uploads_api_3_tmp = $db_img->getApi3Base();
				$testbot_testuploads_tmp = $db_img->getTestbotBase();

				$filename = 'u' . uniqid() . '.jpg';

				$image = file_get_contents($testbot_testuploads_tmp.$image);
				$image = imagecreatefromstring($image);
				$cropped = move_crop_and_return_image($image, $uploads_api_3_tmp . $filename, 610, 610, $uploads_api_3_tmp);
				$file = $db_img->__fetchFile($filename, $uploads_api_3_tmp);

				// Setup the needed variables
			 	$db_img->_setPutObjectVariables($db->_getBucketItems() . $filename, $file);

				// Put the file to s3
				$s3_result = $db_img->_putObject(true);

				if($s3_result['status']) {
					$img_name = $s3_result['result'];
				}

				$img_key = 'image';

			}
			else{
				$img_key = 'images';
				$img_name = $request->getParsedBodyParam('images','');
			}

			if (!$request->getParsedBodyParam('status')){
           		verifyRequiredParams($request,$response,array('title','price','description','quantity',$img_key,'location_id'));
			//  return;
			}

            // updating item
            $r = $db->updateItem($current_user_id, $item_id, $request->getParsedBodyParam('title',''), $request->getParsedBodyParam('price',''), $request->getParsedBodyParam('description',''), $request->getParsedBodyParam('quantity',''), $img_name /*$app->request->post('images')*/, $request->getParsedBodyParam('status',''), $request->getParsedBodyParam('location_id',''));
			// NOTE: If status is set, other fields will not be updated
            
			return $response->withStatus(200);
        }


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
function deleteItem($request, $response, $args,$filter){
//$app->delete('/items/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;
global $r;

            $db = new DbItems();
            $r = array();
            $r = $db->deactivateItem($current_user_id, $item_id);
			//echo $r;
            if ($r) {
                // item updated successfully
				$r["error"] = 0;
				$r["message"] = "Item deleted successfully";
            } else {
                // item failed to update
				$r["error"] = 1;
				$r["message"] = "Item failed to delete. Please try again.";
            }
			return $response->withStatus(200);
        }
		
?>