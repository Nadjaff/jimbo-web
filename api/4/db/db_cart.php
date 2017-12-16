<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbCart extends DbBase {

	/* ------------- `items` table method ------------------ */
	
	/**
	 * Creating new item
	 * @param String $user_id user id to whom item belongs to
	 * @param String $item item text
	 */
	 public function cart_post($current_user_id, $item_id, $variant_id, $quantity) {
		$stmt = $this->conn->prepare("SELECT c.id from cart c WHERE c.user_id = :user_id AND c.item_id = :item_id AND c.variant_id = :variant_id");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":variant_id", $variant_id);
		$stmt->execute();$r = $stmt->fetch(PDO::FETCH_ASSOC) ;
		if (($r = $stmt->fetch(PDO::FETCH_ASSOC)) == NULL){		
	
			$stmt = $this->conn->prepare("INSERT  INTO cart(user_id,	item_id, variant_id, quantity,created_at,updated_at) VALUES(:user_id, :item_id, :variant_id, :quantity,:created_at,:updated_at)");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":variant_id", $variant_id);
			$stmt->bindParam(":quantity", $quantity);
			$dt = date('Y-m-d H:i:s');
			$stmt->bindParam(":created_at",$dt );
			$stmt->bindParam(":updated_at", $dt );
		
			if ($stmt->execute()){
				$cart_id = $this->conn->lastInsertId();			
				//echo $location_id;
			}else{
				print_r($stmt->errorInfo());
				echo "falied";
				$location_id = 0;
				//return NULL;
			}
		}else{
			$stmt = $this->conn->prepare("UPDATE cart c set c.quantity = :quantity WHERE c.item_id = :item_id AND c.user_id = :user_id,updated_at=:updated_at AND c.variant_id = :variant_id");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":variant_id", $variant_id);
			$stmt->bindParam(":quantity", $quantity);
			$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
			return $stmt->execute();
		}
			
	}
	
	/**
	 * Fetching single item
	 * @param String $item_id id of the item
	 */
	public function cart_get($current_user_id, $newerthan_id,$olderthan_id,$count, $item_id=null) {	
		$err = 0;
		
		if($item_id !== NULL) {
			$stmt = $this->conn->prepare("SELECT * FROM items WHERE id = :item_id");
			$stmt->bindParam(":item_id", $item_id);
			if (!$stmt->execute()){
			print_r($stmt->errorInfo());
			}

			if(count($stmt->fetchAll(PDO::FETCH_ASSOC)) == 0)
				return ['error' => 1, 'message' => 'Item not found'];
		}

		$stmt = $this->limitQuery("SELECT DISTINCT ut.username, NOT ISNULL(youfollow.user_id) as youfollow, ut.id, ut.image as userimage, ut.location_description, t.user_id from items t, cart c, users ut LEFT JOIN following as youfollow ON youfollow.user_id = :uid WHERE ut.id ### :limitid AND c.user_id = :uid AND t.id = c.item_id AND ut.id = t.user_id ORDER BY ut.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":uid",$current_user_id);
		$users = $this->yExecute($stmt,"users");
		
		for ($i=0;$i<count($users["users"]);$i++){
		
			/*$stmt = $this->conn->prepare("SELECT ut.username, ut.id, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, t.price, t.currency, t.description, t.quantity, t.status, t.type, t.negotiable, t.created_at, t.image, COUNT(distinct c.id) AS num_comments, COUNT(distinct ul.id) AS liked, COUNT(distinct l.id) AS num_likes, COUNT(distinct s.id) AS num_shares from cart ca, users ut, items t
			LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
			LEFT JOIN locations AS loc ON (loc.id = t.location_id)	
			LEFT JOIN likes AS l ON (l.item_id = t.id)
			LEFT JOIN shares AS s ON (s.item_id = t.id)
			LEFT JOIN comments AS c ON (c.item_id = t.id)
			 WHERE ca.user_id = :uid AND t.id = ca.item_id AND ut.id = :user_id AND t.user_id = ut.id");*/
			
			
			
			$stmt = $this->conn->prepare("SELECT ca.id as cart_id, ca.quantity as num_buying, ut.username, ut.id, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, t.price, t.currency, t.shipping, t.allow_shipping, t.allow_pickup,		 t.description, t.quantity-t.num_sales as quantity, t.status, t.type, t.negotiable, t.created_at, t.image from cart ca, users ut, items t
			LEFT JOIN locations AS loc ON (loc.id = t.location_id)	
			 WHERE ca.user_id = :uid AND t.id = ca.item_id AND ut.id = :user_id AND t.user_id = ut.id");
			
			
			
			//$stmt = $this->conn->prepare("SELECT DISTINCT ut.username, ut.id, ut.image as userimage, t.user_id from items t, cart c, users ut WHERE c.user_id = :uid AND t.id = c.item_id AND ut.id = t.user_id");
			// INNER JOIN locations AS lt ON lt.id = t.location_id
			$stmt->bindParam(":uid",$current_user_id);
			$asdf = 1199;
			$stmt->bindParam(":user_id",$users["users"][$i]["id"]);
			if (!$stmt->execute()){
			print_r($stmt->errorInfo());
			}
			if (!$stmt->execute()) $err = 2;
			$users["users"][$i]["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		if(!isset($comm['error']))
			$comm['error'] = null;
		$users["error"] = max($err,$comm["error"]);
		
		return $users;
	}
	
	public function cart_item_get($current_user_id, $item_id=null, $newerthan_id,$olderthan_id,$count) {	
		$err = 0;		
		if($item_id !== NULL) {
			$stmt = $this->conn->prepare("SELECT * FROM items WHERE id = :item_id");
			$stmt->bindParam(":item_id", $item_id);
			if (!$stmt->execute()){
			print_r($stmt->errorInfo());
			}

			if(count($stmt->fetchAll(PDO::FETCH_ASSOC)) == 0)
				return ['error' => 1, 'message' => 'Item not found'];
		}
		
		$stmt = $this->limitQuery("SELECT DISTINCT ut.username, NOT ISNULL(youfollow.user_id) as youfollow, ut.id, ut.image as userimage, ut.location_description, t.user_id from items t, users ut
		LEFT JOIN following as youfollow ON youfollow.user_id = :uid WHERE ut.id ### :limitid AND t.id = :item_id AND ut.id = t.user_id ORDER BY ut.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":uid",$current_user_id);
		$users = $this->yExecute($stmt,"users");
		
		for ($i=0;$i<count($users["users"]);$i++){
		
			/*$stmt = $this->conn->prepare("SELECT ut.username, ut.id, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, t.price, t.currency, t.description, t.quantity, t.status, t.type, t.negotiable, t.created_at, t.image, COUNT(distinct c.id) AS num_comments, COUNT(distinct ul.id) AS liked, COUNT(distinct l.id) AS num_likes, COUNT(distinct s.id) AS num_shares from cart ca, users ut, items t
			LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
			LEFT JOIN locations AS loc ON (loc.id = t.location_id)	
			LEFT JOIN likes AS l ON (l.item_id = t.id)
			LEFT JOIN shares AS s ON (s.item_id = t.id)
			LEFT JOIN comments AS c ON (c.item_id = t.id)
			 WHERE ca.user_id = :uid AND t.id = ca.item_id AND ut.id = :user_id AND t.user_id = ut.id");*/
			
			
			
			$stmt = $this->conn->prepare("SELECT ut.username, ut.id, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, t.price, t.currency, t.shipping, t.allow_shipping, t.allow_pickup,		 t.description, t.quantity-t.num_sales as quantity, t.status, t.type, t.negotiable, t.created_at, t.image from users ut, items t
			LEFT JOIN locations AS loc ON (loc.id = t.location_id)	
			 WHERE t.id = :item_id AND ut.id = :user_id AND t.user_id = ut.id");
			
			
			
			//$stmt = $this->conn->prepare("SELECT DISTINCT ut.username, ut.id, ut.image as userimage, t.user_id from items t, cart c, users ut WHERE c.user_id = :uid AND t.id = c.item_id AND ut.id = t.user_id");
			// INNER JOIN locations AS lt ON lt.id = t.location_id
			$stmt->bindParam(":item_id",$item_id);
			$stmt->bindParam(":user_id",$users["users"][$i]["id"]);
			if (!$stmt->execute()){
			print_r($stmt->errorInfo());
			}
			if (!$stmt->execute()) $err = 2;
			$users["users"][$i]["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		if(!isset($comm['error']))
			$comm['error'] = null;
		     $users["error"] = max($err,$comm["error"]);
		
		return $users;
	}
	
	/**
	 * Updating item
	 * @param String $item_id id of the item
	 * @param String $item item text
	 * @param String $status item status
	 */
	public function cart_update($current_user_id, $cart_id, $variant_id, $quantity) {
		
		if ($quantity == 0){
			$stmt = $this->conn->prepare("DELETE c FROM cart c WHERE c.variant_id = :variant_id AND c.id = :id AND c.user_id = :user_id");
			$stmt->bindParam(":id", $cart_id);
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":variant_id", $variant_id);
			return $stmt->execute();
		}else{
			
			$stmt = $this->conn->prepare("UPDATE cart c set c.quantity = :quantity,c.updated_at = :updated_at WHERE c.id = :id AND c.user_id = :user_id AND c.variant_id = :variant_id  ");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":id", $cart_id);
			$stmt->bindParam(":variant_id", $variant_id);
			$stmt->bindParam(":quantity", $quantity);
			$updated_at=date('Y-m-d H:i:s');
			$stmt->bindParam(":updated_at", $updated_at);
			//print_r($stmt->errorInfo()); exit();
			return $stmt->execute();
		}
	}
	
	public function deleteCartItem($current_user_id, $item_id, $variant_id) {
		$stmt = $this->conn->prepare("DELETE c FROM cart c WHERE c.variant_id = :variant_id AND c.item_id = :item_id AND c.user_id = :user_id");
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":variant_id", $variant_id);
		
		return $stmt->execute();
	}
	
	
	public function alreadyUsedReceipt($paypalPaymentId) {
		$stmt = $this->conn->prepare("SELECT p.id FROM payments p WHERE p.paypalPaymentId = :paypalPaymentId");
        
		$stmt->bindParam(":paypalPaymentId", $paypalPaymentId);
		
        $result = $stmt->execute();
 
		if ($result){
			if ($stmt->fetch(PDO::FETCH_ASSOC) == NULL){
            	return 0;
			}
        }
		return 1;
    }
	public function storePayment($paypalPaymentId, $userId, $create_time, $update_time, $state, $amount, $currency) {
		$stmt = $this->conn->prepare("INSERT INTO payments(paypalPaymentId, user_id, create_time, update_time, state, amount, currency) VALUES(:paypalPaymentId,:userId,:create_time,:update_time,:state,:amount,:currency)");
        
		$stmt->bindParam(":paypalPaymentId", $paypalPaymentId);
		$stmt->bindParam(":userId", $userId);
		$stmt->bindParam(":create_time", $create_time);
		$stmt->bindParam(":create_time", $create_time);	
		$stmt->bindParam(":update_time", $update_time);
		$stmt->bindParam(":state", $state);
		$stmt->bindParam(":amount", $amount);
		$stmt->bindParam(":currency", $currency);
		
		
        $result = $stmt->execute();
 
        if ($result) {
            // task row created
            // now assign the task to user
				$payment_id = $this->conn->lastInsertId();		
            return $payment_id;
        } else {
				print_r($stmt->errorInfo());
            // task failed to create
            return NULL;
        }
    }
	public function storeSale($payment_id, $buyer_id, $item_id, $state, $price, $quantity, $currency, $comment, $paid) {
        $stmt = $this->conn->prepare("INSERT INTO sales(payment_id, buyer_id, item_id, price, quantity, currency, comment, paid) VALUES(:payment_id, :buyer_id, :item_id, :price, :quantity, :currency, :comment, :paid)");
        $stmt->bindParam(":payment_id", $payment_id);
		$stmt->bindParam(":buyer_id", $buyer_id);
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":price", $price);
		$stmt->bindParam(":quantity", $quantity);
		$stmt->bindParam(":currency", $currency);
		$stmt->bindParam(":comment", $state);
		$stmt->bindParam(":paid", $paid);
        $result = $stmt->execute();
 
        if ($result) {
				$sale_id = $this->conn->lastInsertId();
            return $sale_id;
        } else {
				print_r($stmt->errorInfo());
            // task failed to create
            return NULL;
        }
    }
	
}
?>
