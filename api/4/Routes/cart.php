<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
$app->get('/cart/{id}', function ($request, $response, $args) {
	getCartItems($request, $response, $args,"");
})->add($authentication);
$app->post('/cart', function($request, $response, $args)  {
         postcartItem($request, $response, $args,"");
})->add($authentication);

use PayPal\Api\Payment;
$app->get('/cart', function($request, $response, $args)  {
	getCartwhole($request, $response, $args);	
})->add($authentication);		

$app->post('/sales', function($request, $response, $args){
	postCartSale($request, $response, $args);
})->add($authentication);
$app->put('/cart/{id}', function($request, $response, $args){
	putCart($request, $response, $args);
})->add($authentication);
	$app->delete('/cart/{id}', function($request, $response, $args){
	cartdelete($request, $response, $args);
})->add($authentication);	


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
function cartdelete($request, $response, $args){
            global $current_user_id;
            global $r;
            $db = new DbCart();
			$cart_id=(int)$args['id'];
			//print_r($current_user_id); exit();
            $r = array();
            $result = $db->cart_update($current_user_id, $cart_id, -1,0);
			//echo $result; exit();
            if ($result) {
                // item updated successfully
				$r["error"] = 0;
				$$r["message"] = "Item deleted successfully";
				$httpcode = 200;
            } else {
                // item failed to update
				$r["error"] = 1;
				$r["message"] = "Item failed to delete. Please try again.";
				$httpcode = 200;
            }
			return $response->withStatus(200);
			//echoResponse($httpcode,$response);
}
		
function insertItemSales($paymentId, $transaction, $state) {
	global $current_user_id;

	$item_list = $transaction->getItemList();
	
	$db = new DbCart();
	
	foreach ($item_list->items as $item) {
		$sku = $item->sku;
		$sku = substr($sku,3);
		$paid = 0;
		if ($state == "completed"){
			$paid = 1;
		}
		$db->deleteCartItem($current_user_id, $sku,-1);
		$db->storeSale($paymentId, $current_user_id, $sku , $state, $item->price, $item->quantity, $item->currency, $state, $paid);
	}
}
function getCartItems($request, $response, $args,$filter){
	        global $current_user_id;
			global $r;
			$item_id=(int)$args['id'];
			$r=array();
            $db = new DbCart();
            $newerthan_id =$request->getParsedBodyParam('newerthan_id',null);
            $olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
            $count = $request->getParsedBodyParam('count',null);
            // fetch item
            $r = $db->cart_item_get($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);
		    return $response->withStatus(200);
}
function getCartwhole($request, $response, $args)
{
    global $current_user_id;
	$db = new DbCart();
	global $r;
	$r=array();
	$newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
	$olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
	$count = $request->getParsedBodyParam('count',null);
	// fetch item
	$r = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count);
	 return $response->withStatus(200);
}
function postCartSale($request, $response, $args){
    global $current_user_id;
	global $r;
	$db = new DbCart();
	$r=array();
	try {
		$paymentId = $request->getParsedBodyParam('paymentId','');		
		$payment_client = $request->getParsedBodyParam('paymentClientJson','');
		//$payment_client = '{"amount":"12.00","short_description":"steven2earth","details":{"tax":"0.0","subtotal":"12.00","shipping":"0.0"},"intent":"sale","currency_code":"AUD","item_list":{"items":[{"quantity":"1","price":"12.00","sku":"sku585","currency":"AUD","name":"The dark knight rises"}]}}';
		$payment_client = json_decode($payment_client, true);
		//$paymentId = "PAY-667535506U371083KKYYPUXA";
		$apiContext = new PayPal\Rest\ApiContext(
				new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID, // ClientID
				PAYPAL_SECRET      // ClientSecret
				)
		);

		// Gettin payment details by making call to paypal rest api
		$payment = Payment::get($paymentId, $apiContext);

		// Verifying the state approved
		if ($payment->getState() != 'approved') {
			$r["error"] = true;
			$r["message"] = "Payment has not been verified. Status is " . $payment->getState();
			 return $response->withStatus(200);
		}
		$r["error"] = 0;

		// Amount on client side
		$amount_client = $payment_client["amount"];

		// Currency on client side
		$currency_client = $payment_client["currency_code"];

		// Paypal transactions
		$transaction = $payment->getTransactions();
		$transaction = $transaction[0];
		// Amount on server side
		$amount_server = $transaction->getAmount()->getTotal();
		// Currency on server side
		$currency_server = $transaction->getAmount()->getCurrency();
		$sale_state = $transaction->getRelatedResources();
		$sale_state = $sale_state[0]->getSale()->getState();

		// Storing the payment in payments table
		$paymentId = $payment->getId();
		$db = new DbCart();
		if ($db->alreadyUsedReceipt($paymentId) == 0){
			$payment_id_in_db = $db->storePayment($paymentId, $current_user_id, $payment->getCreateTime(), $payment->getUpdateTime(), $payment->getState(), $amount_server, $currency_server);
		}
		// Verifying the amount
		if ($amount_server != $amount_client) {
			$r["error"] = true;
			$r["message"] = "Payment amount doesn't matched.";
			return $response->withStatus(200);
		}	

		// Verifying the currency
		if ($currency_server != $currency_client) {
			$r["error"] = true;
			$r["message"] = "Payment currency doesn't matched.";
			return $response->withStatus(200);
		
		}

		// Verifying the sale state
		if ($sale_state != 'completed') {
			$r["error"] = true;
			$r["message"] = "Sale not completed";
			return $response->withStatus(200);
		}

if ($payment_id_in_db != NULL){
		// storing the saled items
		insertItemSales($payment_id_in_db, $transaction, $sale_state);
}
               return $response->withStatus(200);
	} catch (\PayPal\Exception\PayPalConnectionException $exc) {
		if ($exc->getCode() == 404) {
			$r["error"] = true;
			$r["message"] = "Payment not found!";
			return $response->withStatus(200);
		} else {
			$r["error"] = true;
			$r["message"] = "Unknown error occurred!" . $exc->getMessage();
			return $response->withStatus(200);
		}
	} catch (Exception $exc) {
		$r["error"] = true;
		$r["message"] = "Unknown error occurred!" . $exc->getMessage();
		return $response->withStatus(200);
	}

	// fetch item
	//$result = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count);
	//echoResponse(200,$response);
}
function postcartItem($request, $response, $args,$filter){
            global $current_user_id;
			global $r;
            $db = new DbCart();			
			$r=array();
            $item_id = $request->getParsedBodyParam('item_id','');
            $newerthan_id = $request->getParsedBodyParam('newerthan_id',null);
            $olderthan_id = $request->getParsedBodyParam('olderthan_id',null);
            $count = $request->getParsedBodyParam('count',null);
            
            // if(!verifyRequiredParams(array('item_id'))) {
	            // fetch item
	            $r = $db->cart_post($current_user_id,$item_id,-1,1);
	            $r = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count,$item_id);
				  return $response->withStatus(200);
			// }
}
function putCart($request, $response, $args){
	  // check for required params
            global $current_user_id;    
            global $r; 
           $r = array();			
            $db = new DbCart();
            $cart_id=(int)$args['id'];
			 $quantity = $request->getParsedBodyParam('quantity',null);
			//print_r($quantity); exit();
            // updating item
            $result = $db->cart_update($current_user_id, $cart_id, -1,$quantity);
			//print_r( $result); exit();
			// NOTE: If status is set, other fields will not be updated
            if ($result) {
                // item updated successfully
				$r["error"] = 0;
				$r["message"] = "Item updated successfully";
				$httpcode = 200;
            } else {
                // item failed to update
				$r["error"] = 1;
				$r["message"] = "Item failed to update. Please try again.";
				$httpcode = 200;
            }
			 return $response->withStatus(200);
			//echoResponse(200,$result);
}

?>