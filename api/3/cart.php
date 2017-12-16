<?php
/*------------------------------------------------------------------------------------------------------------------*/
/*-----------------------------------------------------ITEMS--------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

$app->get('/cart/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;
            $db = new DbCart();
            $newerthan_id = $app->request->get('newerthan_id');
            $olderthan_id = $app->request->get('olderthan_id');
            $count = $app->request->get('count');

            // fetch item
            $result = $db->cart_item_get($current_user_id, $item_id, $newerthan_id, $olderthan_id, $count);
			echoResponse(200,$result);
        });
$app->post('/cart', 'authenticate', function() use($app) {
            global $current_user_id;
            $db = new DbCart();
            $item_id = $app->request->post('item_id');
            $newerthan_id = $app->request->post('newerthan_id');
            $olderthan_id = $app->request->post('olderthan_id');
            $count = $app->request->post('count');

            // if(!verifyRequiredParams(array('item_id'))) {
	            // fetch item
	            $result = $db->cart_post($current_user_id,$item_id,-1,1);
	            $result = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count,$item_id);
				echoResponse(200,$result);
			// }
        });
		
use PayPal\Api\Payment;
		
$app->get('/cart', 'authenticate', function() use($app) {
	global $current_user_id;
	$db = new DbCart();
	$newerthan_id = $app->request->get('newerthan_id');
	$olderthan_id = $app->request->get('olderthan_id');
	$count = $app->request->get('count');

	// fetch item
	$result = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count);
	echoResponse(200,$result);
});
$app->post('/sales', 'authenticate', function() use($app) {
	global $current_user_id;
	$db = new DbCart();
	
	try {
		$paymentId = $app->request()->post('paymentId');
		$payment_client = $app->request()->post('paymentClientJson');
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
			$response["error"] = true;
			$response["message"] = "Payment has not been verified. Status is " . $payment->getState();
			echoResponse(200, $response);
			return;
		}
		$response["error"] = 0;

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

if ($payment_id_in_db != NULL){
		// storing the saled items
		insertItemSales($payment_id_in_db, $transaction, $sale_state);
}

		echoResponse(200, $response);
	} catch (\PayPal\Exception\PayPalConnectionException $exc) {
		if ($exc->getCode() == 404) {
			$response["error"] = true;
			$response["message"] = "Payment not found!";
			echoResponse(200, $response);
		} else {
			$response["error"] = true;
			$response["message"] = "Unknown error occurred!" . $exc->getMessage();
			echoResponse(200, $response);
		}
	} catch (Exception $exc) {
		$response["error"] = true;
		$response["message"] = "Unknown error occurred!" . $exc->getMessage();
		echoResponse(200, $response);
	}

	// fetch item
	//$result = $db->cart_get($current_user_id, $newerthan_id, $olderthan_id, $count);
	//echoResponse(200,$response);
});
		
		
		
		
$app->put('/cart/:id', 'authenticate', function($cart_id) use($app) {
            // check for required params
            global $current_user_id;    
            $db = new DbCart();

            // updating item
            $result = $db->cart_update($current_user_id, $cart_id, -1,$quantity);
			// NOTE: If status is set, other fields will not be updated
            if ($result) {
                // item updated successfully
				$response["error"] = 0;
				$response["message"] = "Item updated successfully";
				$httpcode = 200;
            } else {
                // item failed to update
				$response["error"] = 1;
				$response["message"] = "Item failed to update. Please try again.";
				$httpcode = 200;
            }
			echoResponse(200,$result);
        });


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/cart/:id', 'authenticate', function($item_id) use($app) {
            global $current_user_id;

            $db = new DbCart();
            $response = array();
            $result = $db->cart_update($current_user_id, $cart_id, -1,0);
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
		
?>