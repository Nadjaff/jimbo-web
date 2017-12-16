<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbItems extends DbBase {

	/* ------------- `items` table method ------------------ */
	
	/**
	 * Creating new item
	 * @param String $user_id user id to whom item belongs to
	 * @param String $item item text
	 */
	public function items_post($current_user_id, $title,$price,$description,$quantity,$images,$status,$location_id, $test) {
		if ($status == 1){
			$status = 0;
		}
		// Need to put a select statement in here to check that the user actually owns these images!
		
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$images = implode(",",array_filter(explode(",",$images)));
			ini_set('display_errors',1);
error_reporting(E_ALL);
		$stmt = $this->conn->prepare("INSERT INTO items(user_id,title,price,description,quantity,status,image,location_id, test) VALUES(:user_id, :title, :price, :description, :quantity, :status, :images, :location_id, :test)");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":title", $title);
		$stmt->bindParam(":price", $price);
		$stmt->bindParam(":description", $description);
		$stmt->bindParam(":quantity", $quantity);
		$stmt->bindParam(":status", $status);
		$stmt->bindParam(":images", $images);
		$stmt->bindParam(":location_id", $location_id);
		if ($test == NULL){
			$test = 0;
		}
		$stmt->bindParam(":test", intval($test));
		
		if ($stmt->execute()){
			$this->items_count($current_user_id,1);
			// item row created
			// now assign the item to user
			return $this->conn->lastInsertId();
		} else {
		print_r($this->conn->errorCode());
			// item failed to create
			return NULL;
		}
	}
	
	/**
	 * Fetching single item
	 * @param String $item_id id of the item
	 */
	public function item_get($current_user_id, $item_id) {
		$err = 0;
		$stmt = $this->conn->prepare("SELECT ut.username, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, COUNT(distinct c.id) AS num_comments, COUNT(distinct ul.id) AS liked, COUNT(distinct l.id) AS num_likes, COUNT(distinct s.id) AS num_shares from items t
		LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
		LEFT JOIN locations AS loc ON (loc.id = t.location_id)
		LEFT JOIN likes AS l ON (l.item_id = t.id)
		LEFT JOIN shares AS s ON (s.item_id = t.id)
		LEFT JOIN comments AS c ON (c.item_id = t.id)
		LEFT JOIN users AS ut ON ut.id = t.user_id		
		WHERE t.id = :item_id");
		
		// INNER JOIN locations AS lt ON lt.id = t.location_id
		$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		if (!$stmt->execute()) $err = 2;
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		$comm = $this->item_get_comments($item_id,0,0,0);
		$item["comments"] = $comm["comments"];
		$item["error"] = max($err,$comm["error"]);
		return $item;
	}
	
	
	
	public function item_get_likes($item_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name FROM users t, likes f WHERE f.id ### :limitid AND f.item_id = :item_id AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->yExecute($stmt,"likes");		
	}
	
	public function item_get_comments($item_id,$newerthan_id,$olderthan_id,$count){		
		$stmt = $this->limitQuery("SELECT * FROM (SELECT t.username, t.image, t.locality, t.id as user_id, t.name, f.comment, f.id, f.created_at FROM users t, comments f	WHERE f.id ### :limitid AND f.item_id = :item_id AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, :count) tmp ORDER BY tmp.id ASC",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->yExecute($stmt,"comments");
	}
	
	public function item_get_offers($item_id,$newerthan_id,$olderthan_id,$count){		
		$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name, f.message, f.id, f.result, f.created_at, f.offer, c.id conversation_id FROM users t, messages f, conversations c	WHERE f.id ### :limitid AND c.item_id = :item_id AND f.conversation_id = c.id AND t.id = f.user_id AND f.type = 2 ORDER BY f.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->yExecute($stmt,"offers");
	}
	
	
	
	
	public function item_like($current_user_id, $item_id,$val){
		$stmt = $this->conn->prepare("SELECT COUNT(*) FROM likes t WHERE t.user_id = :user_id AND t.item_id = :item_id");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		if (!$stmt->execute()) {
			return array("error" => 1);
		}
  		$num_rows = $stmt->fetchColumn();
			
		$stmt = NULL;
		if ($val == 1){
			if ($num_rows == 0){
				$stmt = $this->conn->prepare("INSERT INTO likes(user_id, item_id) values(:user_id,:item_id)");
			}
		}else{
			if ($num_rows == 1){
				$stmt = $this->conn->prepare("DELETE t from likes t WHERE t.user_id = :user_id AND t.item_id = :item_id");
			}else{
				// There is probably an error
			}
		}
		if ($stmt != NULL){
			$stmt->bindParam(":user_id",$current_user_id);
			$stmt->bindParam(":item_id",$item_id);
			if (!$stmt->execute()) {
				return array("error" => 2, "message" => "Unable to like at this time");
			}
		}
		
		if ($val == 1){
			$stmt = $this->conn->prepare("SELECT u.username FROM users u WHERE u.id = :current_user_id");
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->execute();
			if (!($poster = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "Error");
			}
			$stmt = $this->conn->prepare("SELECT i.title FROM items i WHERE i.id = :item_id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			if (!($item = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "Error");
			}	
			
			
			$stmt = $this->conn->prepare("SELECT u.username, u.id FROM users u, items i WHERE i.id = :item_id AND i.user_id = u.id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			include_once("db/db_notifications.php");
			$nots = new DbNotifications();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$nots->send_notification($owner["id"],1,$item_id,$current_user_id,"");
					//$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " liked your item: " . $item["title"], "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}
			/*include_once("libs/GCM.php");
			$gcm = new GCM();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " liked your item: " . $item["title"], "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}else{
				return array("error" => 1, "message" => "Error");
			}*/
		}
			
		return array("error" => 0, "result"=>$val, "message" => "Like Successful");
	}
	
	
	public function item_comment($current_user_id, $item_id,$comment){
		$stmt = $this->conn->prepare("INSERT INTO comments(user_id, item_id, comment) values(:user_id,:item_id,:comment)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$comment);
		if ($stmt->execute()){
			$comment_id = $this->conn->lastInsertId();
			
			$stmt = $this->conn->prepare("SELECT u.username FROM users u WHERE u.id = :current_user_id");
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->execute();
			if (!($poster = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "SError");
			}
				
			
			
			$stmt = $this->conn->prepare("SELECT u.username, u.id FROM users u, items i WHERE i.id = :item_id AND i.user_id = u.id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			//newandroid include_once("libs/GCM.php");
			//$gcm = new GCM();
			include_once("db/db_notifications.php");
			$nots = new DbNotifications();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$nots->send_notification($owner["id"],2,$item_id,$current_user_id,$comment);
					//$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " said: " . $comment, "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}
			
			
			$stmt = $this->conn->prepare("SELECT distinct(cp.user_id), u.username FROM comments cp, users u WHERE u.id = cp.user_id AND cp.user_id <> :current_user_id AND cp.user_id <> :owner_id AND cp.item_id = :item_id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->bindParam(":owner_id",$owner["id"]);
			$stmt->execute();
			//include_once("libs/GCM.php");
			//$gcm = new GCM();
			while (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
				$nots->send_notification($conv["user_id"],2,$item_id,$current_user_id,$comment);
				//$gcm->send_notification($conv["user_id"],"Jimbo",$poster["username"] . " said: " . $comment, "items/" . $item_id);
				//echo "sending" . $conv["user_id"] . "sending";
			}
				
			return array("error" => 0, "id" => $comment_id, "message" => "Comment Successful");
		}else{
			return array("error" => 1, "message" => "Unable to post comment");
		}
	}
	
	public function item_report($current_user_id, $item_id,$comment){
		$stmt = $this->conn->prepare("INSERT INTO reports(reporter_id, user_id, item_id, comment) values(:reporter_id, :user_id,:item_id,:comment)");
		$reported_user = 0;
		$stmt->bindParam(":reporter_id",$current_user_id);
		$stmt->bindParam(":user_id",$reported_user);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$comment);
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Item Reported");
		}else{
			return array("error" => 1, "message" => "Unable to report item - please try again later");
		}
	}
	public function item_offer($current_user_id, $item_id,$offer){
		$stmt = $this->conn->prepare("INSERT INTO messages(user_id, item_id, comment) values(:user_id,:item_id,:comment)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$offer);
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Offer Posted Successfully");
		}else{
			return array("error" => 1, "message" => "Unable to post offer");
		}
	}
	
	/**
	 * Fetching all user items
	 * @param String $user_id id of the user
	 */
	 
	public function getAllItems($current_user_id,$filter,$q,$newerthan_id,$olderthan_id,$count,$test) {
		$selectqry = "";
		if ($q == NULL) $q = "";
		switch($filter){
			case "":
				$selectqry .= "";
				//$searchqry = "AND f.user_id = ? AND f.following_id = t.user_id AND";
				$searchqry = "";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
				if ($count == NULL){
					$count = 10;
				}
			break;
			case "home":
				$selectqry .= ", following f";
				$searchqry = "AND f.user_id = :uid AND f.following_id = t.user_id";
				if ($count == NULL){
					$count = 10;
				}
				//$searchqry = "AND t.user_id <> ?";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
			break;
			case "purchased":
				$searchqry =  "AND t.purchased_by = :uid";
			break;
			case "sold":
				$searchqry =  "AND t.user_id = :uid AND t.purchased_by <> 0";
			break;
			case "favorites":
				$selectqry .= ", likes f";
				$searchqry =  "AND f.user_id = :uid AND f.item_id = t.id";
			break;
			case "explore":
				$searchqry =  "AND t.user_id <> :uid";
			break;
		}
		
		$stmt = $this->limitQuery("SELECT ut.username, ut.image as userimage, t.user_id, t.created_at, t.test, t.id, t.title, t.price, t.description, loc.locality, t.location_id, t.quantity, t.status, t.created_at, t.image, COUNT(distinct c.id) AS num_comments, COUNT(distinct ul.id) AS liked, COUNT(distinct l.id) AS num_likes, COUNT(distinct s.id) AS num_shares from items t
		LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
		LEFT JOIN likes AS l ON (l.item_id = t.id)
		LEFT JOIN locations AS loc ON (loc.id = t.location_id)
		LEFT JOIN shares AS s ON (s.item_id = t.id)
		LEFT JOIN comments AS c ON (c.item_id = t.id)
		LEFT JOIN users AS ut ON ut.id = t.user_id		
		$selectqry
		WHERE t.id ### :limitid AND t.status BETWEEN 0 AND 2 AND t.test = :testid $searchqry AND (t.title LIKE CONCAT('%', :q, '%') OR t.description LIKE CONCAT('%',:q, '%')) GROUP BY t.id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		
		// INNER JOIN locations AS lt ON lt.id = t.location_id
		if ($test == NULL){
			$test = 0;
		}
		$stmt->bindParam(":testid",$test);
		$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":q",$q);
		$response = $this->xExecute($stmt);
		$ids = array();
		$index = array();
		$result = $response["users"];
		$itemCount = count($result);
		for ($i=0; $i < $itemCount; $i++){
			$result[$i]["comments"] = array();
			$ids[] = $result["id"];
			$index[$result["id"]] = $i;
		}
		$inQuery = implode(',', array_fill(0, count($ids), '?'));
		echo $inQuery;
		$stmt = $this->conn->prepare("SELECT t.username, t.image, t.locality, t.id as user_id, t.name, f.comment, f.id, f.created_at, f.item_id FROM users t, comments f WHERE f.item_id in (" . $inQuery . ") AND t.id = f.user_id ORDER BY f.id DESC");
		
		if ($stmt->execute()){
			while (($comment = $stmt->fetch(PDO::FETCH_ASSOC))){
				$result[$index[$comment["item_id"]]]["comments"][] = $comment;
			}
		}
		return $result;
	}
	
	/**
	 * Updating item
	 * @param String $item_id id of the item
	 * @param String $item item text
	 * @param String $status item status
	 */
	public function updateItem($current_user_id, $item_id, $title, $price, $description, $quantity, $images, $status, $location_id) {
		if ($status != NULL & $status != 0){
			$stmt = $this->conn->prepare("UPDATE items t set t.status = :status WHERE t.id = :id AND t.user_id = :user_id");
			$stmt->bindParam(":status", $status);
			$stmt->bindParam(":id", $item_id);
			$stmt->bindParam(":user_id", $current_user_id);
			if ($stmt->execute()){
				return array("error" => 0, "message" => "Status Updated Successfully");
			}else{
				return array("error" => 1, "message" => "Status Update Failed");
			}
		}else{
			$stmt = $this->conn->prepare("UPDATE items t set t.title = :title, t.price = :price, t.description = :description, t.quantity = :quantity, t.image = :image, t.location_id = :location_id WHERE t.id = :id AND t.user_id = :user_id");
			$stmt->bindParam(":title", $title);
			$stmt->bindParam(":price", $price);
			$stmt->bindParam(":description", $description);
			$stmt->bindParam(":quantity", $quantity);
			$stmt->bindParam(":image", $image);
			$stmt->bindParam(":location_id", $location_id);
			$stmt->bindParam(":id", $id);
			$stmt->bindParam(":user_id", $user_id);
			
			if ($stmt->execute()){
				return array("error" => 0, "message" => "Updated Successfully");
			}else{
				return array("error" => 1, "message" => "Update Failed");
			}
		}
	}
	
	/**
	 * Publishing item
	 * @param String $item_id id of the item
	 * @param String $item item text
	 * @param String $status item status
	 */
	public function publishItem($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("UPDATE items t set t.published_at = :published_at, t.status = :status WHERE t.id = :id AND t.user_id = :user_id");
		
		$status = 2;
		$date = new DateTime();
		$published_at = $date->getTimestamp();
		
		$stmt->bindParam(":published_at", $published_at);
		$stmt->bindParam(":status", $status);
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		
		return intval($stmt->execute());
	}
	
	public function deactivateItem($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("UPDATE items t set t.status = :status WHERE t.id = :item_id AND t.user_id = :user_id");
		
		$status = 5;
		
		$stmt->bindParam(":status", $status);
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		$this->items_count($current_user_id,-1);
		
		return intval($stmt->execute());
	}
	public function items_count($current_user_id,$val){
		if ($val == 1){
			$stmt = $this->conn->prepare("UPDATE users t set t.no_products = t.no_products + 1 WHERE t.id = :user_id");
		}else {
			$stmt = $this->conn->prepare("UPDATE users t set t.no_products = t.no_products - 1 WHERE t.id = :user_id");
		}
		
		$stmt->bindParam(":user_id", $current_user_id);		
		return intval($stmt->execute());
	}
	/**
	 * Deleting a item
	 * @param String $item_id id of the item to delete
	 */
	public function deleteItem($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("DELETE t FROM items t WHERE t.id = :id AND t.user_id = :user_id");
		$stmt->bindParam(":id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		return $stmt->execute();
	}
	
	/* ------------- `user_items` table method ------------------ */
	
	/**
	 * public function to assign a item to user
	 * @param String $current_user_id id of the user
	 * @param String $item_id id of the item
	 */
	/*public function createUserItem($current_user_id, $item_id) {
		$stmt = $this->conni->prepare("INSERT INTO user_items(user_id, item_id) values(:user_id, :item_id)");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":item_id", $item_id);
		$result = $stmt->execute();
	
		if (false === $result) {
			die('execute() failed: ' . htmlspecialchars($stmt->error));
		}
		$stmt->close();
		return $result;
	}*/
	
}
?>
