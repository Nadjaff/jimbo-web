<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbConversations extends DbBase {

/*public function conversations_get_all($current_user_id,$item_id,$newerthan_id,$olderthan_id,$count) {
		
		
		/*$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name, c.id FROM users t, conversations c
		INNER JOIN conversation_participants AS cp ON (cp.conversation_id = c.id AND cp.user_id = :user_id)
		LEFT JOIN messages AS m ON m.conversation_id = c.id
		WHERE m.id ### :limitid AND m.id = (SELECT MAX(id) FROM messages where conversation_id = c.id) ORDER BY m.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);*//*
			$stmt = $this->limitQuery("SELECT m1.message as latest_message, m1.created_at as latest_message_timestamp, m1.id as latest_message_id, m1.type as latest_message_type, t.price, cp.conversation_id as conversation_id FROM conversation_participants cp, messages m1 LEFT JOIN offers t ON t.message_id = m1.id WHERE m1.id ### :limitid AND 
			m1.id = (SELECT MAX(m2.id)
					 FROM messages m2
					 WHERE m2.conversation_id = cp.conversation_id) AND m1.conversation_id = cp.conversation_id AND cp.user_id = :user_id ORDER BY m1.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":user_id",$current_user_id);
			
			
		$conversations = $this->yExecute($stmt,"conversations");
		return $conversations;
		
	}*/
	
	/**
	 * Fetching all user items
	 * @param String $current_user_id id of the user
	 */
	public function conversations_get_all($current_user_id,$item_id,$newerthan_id,$olderthan_id,$count) {
		
		
		/*$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name, c.id FROM users t, conversations c
		INNER JOIN conversation_participants AS cp ON (cp.conversation_id = c.id AND cp.user_id = :user_id)
		LEFT JOIN messages AS m ON m.conversation_id = c.id
		WHERE m.id ### :limitid AND m.id = (SELECT MAX(id) FROM messages where conversation_id = c.id) ORDER BY m.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);*/
			$stmt = $this->limitQuery("SELECT cp.unread as num_unread, i.title, i.id as item_id, i.image as item_image, i.price as item_price, t2.username, t2.image as userimage, t2.locality, t2.id as user_id, t2.name, c.id as conversation_id, m1.message as latest_message, m1.created_at as latest_message_timestamp, m1.id as latest_message_id, m1.type as latest_message_type, t3.username as latest_message_from_username, t3.id as latest_message_from_id, t3.image as latest_message_from_image, " . $this->getpricet	. ", t.result as latest_message_result FROM users t1, users t2, users t3, conversation_participants cp, conversation_participants cp2, messages m1, offers t 
		" . $this->getpricetablest	. ", conversations c LEFT JOIN items as i ON i.id = c.item_id 
			WHERE m1.id ### :limitid AND cp.conversation_id = c.id AND cp2.conversation_id = c.id AND cp2.user_id <> :user_id AND t2.id = cp2.user_id AND t.message_id = m1.id AND 
			m1.id = (SELECT MAX(m2.id)
					 FROM messages m2
					 WHERE m2.conversation_id = c.id) AND
			m1.conversation_id = c.id AND
			cp.user_id = :user_id AND t1.id = cp.user_id AND t3.id = m1.user_id ORDER BY m1.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":user_id",$current_user_id);
						$stmt->bindParam(":uid", $current_user_id);
			
			
		$conversations = $this->yExecute($stmt,"conversations");
		$stmt = $this->conn->prepare("SELECT SUM(cp.unread) as num_unread FROM conversation_participants cp WHERE cp.user_id = :user_id");
		$stmt->bindParam(":user_id", $current_user_id);
		if ($stmt->execute()) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$conversations["num_unread"] = $data["num_unread"];
		}
		return $conversations;
		
	}
	/**
	 * Fetching single item
	 * @param String $item_id id of the item
	 */
	public function conversation_get($current_user_id, $conversation_id,$newerthan_id,$olderthan_id,$count) {
		
		$stmt = $this->conn->prepare("SELECT i.title, i.image as item_image, i.user_id as item_owner, i.id as item_id, i.price as item_price, cur.name as item_currency, c.id as conversation_id FROM conversation_participants cp, conversations c LEFT JOIN items as i ON i.id = c.item_id LEFT JOIN currencies as cur ON cur.id = i.currency WHERE c.id = :conversation_id AND cp.conversation_id = c.id AND cp.user_id = :user_id");
		$stmt->bindParam(":conversation_id", $conversation_id);
		$stmt->bindParam(":user_id", $current_user_id);
		if ($stmt->execute()) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($data != NULL){
				
				// Check someone else is in the conversation and get their details in case it's not an item conversation
				$stmt = $this->conn->prepare("SELECT t.username, t.image as userimage, t.locality, t.id as user_id, t.name FROM conversation_participants cp, conversations c, users t WHERE c.id = :conversation_id AND cp.conversation_id = c.id AND cp.user_id <> :user_id AND t.id = cp.user_id");
				$stmt->bindParam(":conversation_id", $conversation_id);
				$stmt->bindParam(":user_id", $current_user_id);
			
				if ($stmt->execute()) {
					$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);print_r($participants);
					if ($data["item_owner"] == NULL){
						$data["username"] = isset($participants[0]["username"]) ? $participants[0]["username"] : null;
						$data["userimage"] = isset($participants[0]["image"]) ? $participants[0]["image"] : null;
					
					}
					
					if ($participants != NULL){
						$stmt = $this->limitQuery("SELECT u.username, u.image as userimage, u.locality, u.id as user_id, u.name, " . $this->getpricet	. ", m.message, m.type, m.id, t.result, m.created_at FROM users u, messages m LEFT JOIN offers as t on t.message_id = m.id " . $this->getpricetablest	. " WHERE m.id ### :limitid AND m.conversation_id = :conversation_id AND m.user_id = u.id ORDER BY m.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);	
						$stmt->bindParam(":conversation_id", $conversation_id);
						$stmt->bindParam(":uid", $current_user_id);
						$messages = $this->yExecute($stmt,"messages");
						$data["error"] = $messages["error"];
						
						$data["messages"] = $messages["messages"];
						$data["participants"] = $participants;
						
						$stmt2 = $this->conn->prepare("UPDATE conversation_participants cp set cp.unread = :unread WHERE cp.conversation_id = :conversation_id AND cp.user_id = :user_id");
						$unread = 0;
						$stmt2->bindParam(":unread", $unread);
						$stmt2->bindParam(":conversation_id", $conversation_id);
						$stmt2->bindParam(":user_id", $current_user_id);
						$stmt2->execute();
						return $data;
					}
				}
			}
		}
		return array ("error" => 1, "message" => "Error finding conversation");
	}
	
	public function conversations_message_user($current_user_id, $user_id, $item_id,$newerthan_id,$olderthan_id,$count) {
		if ($item_id == null) $item_id = -1;
		// user_id and current_user_id are in the conversation. In Future need to confirm they are the only 2 in the conversation.
		
		/*$stmt = $this->conn->prepare("SELECT c.id as conversation_id FROM conversation_participants cp, conversation_participants cp2, conversations c WHERE cp.conversation_id = c.id AND cp2.conversation_id = c.id AND cp.user_id = :current_user_id AND cp2.user_id = :user_id");
		$stmt->bindParam(":current_user_id", $current_user_id);
		$stmt->bindParam(":user_id", $user_id);*/
		
		
		$stmt = $this->conn->prepare("SELECT c.id as conversation_id FROM conversation_participants cp, conversation_participants cp2, conversations c LEFT JOIN items as i ON i.id = c.item_id WHERE c.item_id = :item_id AND cp.conversation_id = c.id AND cp2.conversation_id = c.id AND cp.user_id = :current_user_id AND cp2.user_id = :user_id");
		$stmt->bindParam(":current_user_id", $current_user_id);
		$stmt->bindParam(":user_id", $user_id);
		$stmt->bindParam(":item_id", $item_id);
		
		if ($stmt->execute()) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($data != NULL){
				$conversation_id = $data["conversation_id"];
				return $this->conversation_get($current_user_id, $conversation_id,$newerthan_id,$olderthan_id,$count);
				// Check someone else is in the conversation and get their details in case it's not an item conversation
				/*$stmt = $this->conn->prepare("SELECT t.username, t.image, t.locality, t.id, t.name FROM conversation_participants cp, conversations c, users t WHERE c.id = :conversation_id AND cp.conversation_id = c.id AND cp.user_id <> :user_id AND t.id = cp.user_id");
				$stmt->bindParam(":conversation_id", $conversation_id);
				$stmt->bindParam(":user_id", $current_user_id);
				if ($stmt->execute()) {
					$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if ($data["item_owner"] == NULL){
						$data["username"] = $participants[0]["username"];
						$data["userimage"] = $participants[0]["image"];
					}
					
					if ($participants != NULL){
						$conversation_id = $data["conversation_id"];
						$stmt = $this->limitQuery("SELECT t.username, t.image as userimage, t.locality, t.id, t.name, m.message, m.offer, m.type, m.id, m.result FROM messages m, users t WHERE m.id ### :limitid AND m.conversation_id = :conversation_id AND m.user_id = t.id ORDER BY m.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
						$stmt->bindParam(":conversation_id", $conversation_id);
						$messages = $this->yExecute($stmt,"messages");
						$data["error"] = $messages["error"];
						$data["messages"] = $messages["messages"];
						$data["participants"] = $participants;
						return $data;
					}
				}*/
			}
		}
		if ($item_id != -1){
			$stmt = $this->conn->prepare("SELECT i.title, i.image as item_image, i.useƒr_id as item_owner, i.id as item_id, i.price as item_price, t.username, t.image as userimage, t.locality, t.id as user_id, t.name FROM items i, users t WHERE t.id = :user_id AND i.user_id = :user_id AND i.id = :item_id");
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":user_id", $user_id);
		}else{
			$stmt = $this->conn->prepare("SELECT t.username, t.image as userimage, t.locality, t.id as user_id, t.name FROM users t WHERE t.id = :user_id");
			$stmt->bindParam(":user_id", $user_id);
		}
		if ($stmt->execute()) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($data != NULL){
				$data["username"] = $data["username"];
				$data["userimage"] = $data["userimage"];
				$data["messages"] = array();
				$data["participants"] = array();
				$data["participants"][0] = array("username" => $data["username"], "userimage" => $data["userimage"], "locality" => $data["locality"], "id" => $data["user_id"], "name" => $data["name"]);
				return $data;
			}
		}
		
		return array ("error" => 1, "message" => "Error finding conversation");
	}
	
	public function conversation_post($current_user_id,$conversation_id,$item_id,$user_id,$price, $currency, $message,$type) {
		$response = array();
		//$stmt = $this->conni->prepare("SELECT t.partya, t.partyb FROM conversations t, messages m WHERE m.id = ? AND m.conversation_id = t.id");
		//$stmt->bind_param("i",$conversation_id);
		
		if ($item_id == NULL) $item_id = -1;
		if ($conversation_id == NULL){
			
			// Check for exisiting conversation with ONLY user_id
			if ($user_id != NULL){
				$stmt = $this->conn->prepare("SELECT c.id from conversations c, conversation_participants cp1, conversation_participants cp2 WHERE cp1.user_id = :current_user_id AND cp2.user_id = :user_id AND cp1.conversation_id = c.id AND cp2.conversation_id = c.id AND c.item_id = :item_id");
				$stmt->bindParam(":user_id",$user_id);
			}else if($item_id != -1){
				$stmt = $this->conn->prepare("SELECT c.id from conversations c, conversation_participants cp1 WHERE cp1.user_id = :current_user_id AND cp1.conversation_id = c.id AND c.item_id = :item_id");
			}
			$stmt->bindParam(":item_id",$item_id);
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->execute();
			if (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
				$conversation_id = $conv["id"];
			}else{
				if ($user_id == NULL){
					$stmt = $this->conn->prepare("SELECT t.user_id from items t WHERE t.id = :item_id");
					$stmt->bindParam(":item_id",$item_id);
					$stmt->execute();
					if (($item = $stmt->fetch(PDO::FETCH_ASSOC))){
						$user_id = $item["user_id"];
					}
				}
				// Else create new conversation
				$succ = 0;
				// Create Conversation
				$stmt = $this->conn->prepare("INSERT INTO conversations(item_id,created_at,updated_at) values(:item_id,:created_at,:updated_at)");
				$stmt->bindParam(":item_id",$item_id);
				$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			    $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
				$succ += intval($stmt->execute());
				$conversation_id = $this->conn->lastInsertId();
				// Insert friend
				$stmt = $this->conn->prepare("INSERT INTO conversation_participants(conversation_id, user_id,created_at,updated_at) values(:conversation_id, :user_id,:created_at,:updated_at)");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":user_id",$user_id);
				$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			    $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
				$succ += intval($stmt->execute());
				// Insert User
				$stmt = $this->conn->prepare("INSERT INTO conversation_participants(conversation_id, user_id,created_at,updated_at) values(:conversation_id, :user_id,:created_at,:updated_at)");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":user_id",$current_user_id);
				$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			    $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
				$succ += intval($stmt->execute());
				if ($succ != 3){
					return array("error" => $succ, "message" => "3Error creating conversation");
				}
			}
		}
		
		$stmt = $this->conn->prepare("SELECT u.username, u.image as userimage from users u, conversation_participants cp1 WHERE cp1.user_id = :current_user_id AND u.id = :current_user_id AND cp1.conversation_id = :conversation_id");
		$stmt->bindParam(":current_user_id",$current_user_id);
		$stmt->bindParam(":conversation_id",$conversation_id);
		if ($stmt->execute() && (($curruser = $stmt->fetch(PDO::FETCH_ASSOC)))){
			if ($message == NULL) $message = "";
			$stmt = $this->conn->prepare("INSERT INTO messages(conversation_id, message, type, user_id,created_at,updated_at) values(:conversation_id, :message, :type, :user_id,:created_at,:updated_at)");
			$stmt->bindParam(":conversation_id", $conversation_id);
			$stmt->bindParam(":message", $message);
			$stmt->bindParam(":type", $type);
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));	  
			// Check for successful insertion
			if ($stmt->execute()) {
				$message_id = $this->conn->lastInsertId();
				$response["error"] = 0;
				if ($type == "2"){
					$dotpoint = strpos($price,".");
					if ($dotpoint !== false){
						$message = $dotpoint;
						if (strlen($price) > $dotpoint+6){
							$price = substr($price,0,$dotpoint) . substr($price,$dotpoint+1,6) . "." . substr($price,$dotpoint+7) ;
						}else{
							$numadded = strlen($price)-$dotpoint;
							$price = substr($price,0,$dotpoint) . substr($price,$dotpoint+1,$numadded);
							while ($numadded <= 6){
								$numadded++;
								$price = $price . "0";
							}
						}
					}else{
						$price = $price . "000000";
					}
					$stmt = $this->conn->prepare("INSERT INTO offers(message_id, price, currency,created_at,updated_at) values(:message_id, :price, :currency,:created_at,:updated_at)");
					$stmt->bindParam(":message_id", $message_id);
					$stmt->bindParam(":price", $price);
					$stmt->bindParam(":currency", $currency);
				   $stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			       $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
					if (!$stmt->execute()) {
						$response["error"] = 2;
					
					}
				}
				$response["message"] = "Message Sent";
				$response["added"] = array();
				$response["added"]["user_id"] = $current_user_id;
				$response["added"]["conversation_id"] = $conversation_id;
				$response["added"]["username"] = $curruser["username"];
				$response["added"]["userimage"] = $curruser["userimage"];
				$response["added"]["id"] = $message_id;
				$response["added"]["message"] = $message;
				$response["added"]["price"] = $price;
				$response["added"]["currency"] = $currency;
				$response["added"]["type"] = $type;
				$response["added"]["created_at"] = date("Y-m-d H:i:s");
				
				 
				$stmt = $this->conn->prepare("UPDATE conversation_participants cp set cp.unread = cp.unread + 1,updated_at=:updated_at WHERE cp.user_id <> :current_user_id AND cp.conversation_id = :conversation_id");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":current_user_id",$current_user_id);
				$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
				$stmt->execute();
				
				$stmt = $this->conn->prepare("SELECT u.image as userimage, cp.user_id FROM conversation_participants cp, users u WHERE cp.user_id <> :current_user_id AND cp.conversation_id = :conversation_id AND u.id = :current_user_id");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":current_user_id",$current_user_id);
				$stmt->execute();
				
				include_once("libs/GCM.php");
				$gcm = new GCM();
				while (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
					if ($type != 2){
						$gcm->send_notification($conv["user_id"],$curruser["username"], $message, "messages/" . $conversation_id,$response["added"]);
					}else{ 
						$gcm->send_notification($conv["user_id"],$curruser["username"], $curruser["username"] . " offered " . ((float)($price) / 1000000), "messages/" . $conversation_id,$response["added"]);
					}
				}
				return $response;
			}else{
				print_r( $stmt->errorInfo());
			}
		}
	}
	
	public function conversation_put_message($current_user_id,$conversation_id,$message_id,$action) {
		$response = array();
		$stmt = $this->conn->prepare("SELECT t.id, m.offer, m.message, m.type, m.result FROM conversations t, conversation_participants cp1, conversation_participants cp2, messages m WHERE t.id = :id AND cp1.conversation_id = :id AND cp2.conversation_id = :id AND (cp1.user_id = :uid OR cp2.user_id = :uid) AND m.id = :mid");
		$stmt->bindParam(":id",$conversation_id);
		$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":mid",$message_id);
		$conv = null;
		//$conversation_id = 0;
		if ($stmt->execute()){
			if (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
				$conversation_id = $conv["id"];
				if ($conv["type"] != "2"){
					$response["error"] = 3;
					$response["message"] = "Message is not a valid offer";
					return $response;
				}
				if ($conv["result"] != "0"){
					$response["error"] = 4;
					$response["message"] = "Offer has already been actioned";
					return $response;
				}
			}else{
				$conversation_id = 0;
			}
		}else{
			$response["error"] = 1;
			$response["message"] = "Could not access conversation";
			return $response;
		}
		if ($conversation_id == NULL || $conversation_id == 0){
			$response["error"] = 2;
			$response["message"] = "Could not access conversation.";
			return $response;
		}
		$stmt = $this->conni->prepare("UPDATE messages m set m.result = ?,m.updated_at=? WHERE m.id = ? AND m.conversation_id = ?");
		$stmt->bind_param("isii", date('Y-m-d H:i:s'),$action, $message_id, $conversation_id);
		
		$newtype = 3;
		
		$stmt = $this->conn->prepare("INSERT INTO messages(conversation_id, offer, message, type, user_id,created_at,updated_at) values(:conversation_id, :offer, :message, :type, :user_id,:created_at,:updated_at)");
		$stmt->bindParam(":conversation_id", $conversation_id);
		$stmt->bindParam(":offer", $conv["offer"]);
		$stmt->bindParam(":message", $conv["message"]);
		$stmt->bindParam(":type", $newtype);
		$stmt->bindParam(":user_id", $current_user_id);
	    $stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
	    $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
		// Check for successful insertion
		if ($stmt->execute()) {
			$response["error"] = 0;
			$response["message"] = "Status successfully changed";
		}else{
			$response["error"] = 3;
			$response["message"] = "Could not change offer status";
		}
		return $response;
	}
}
?>
