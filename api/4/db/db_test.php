<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada	
 * @link URL Tutorial link
 */
class DbTest extends DbBase {
	
	public function bid_create($current_user_id, $item_id, $bid) {
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT id, item_id, user_id, bid, created_at FROM bids WHERE item_id = :item_id AND bid >= :bid ORDER BY id DESC LIMIT 0,1");
	
		$stmt->bindParam(":item_id", $item_id);	
		$stmt->bindParam(":bid", $bid);
	
		$stmt->execute();
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC)) == NULL) {
			$stmt = $this->conn->prepare("INSERT INTO bids(item_id, user_id, bid) values(:item_id, :user_id, :bid)");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":bid", $bid);
			if ($stmt->execute()) {
				return array("error"=>"0", "message"=>"Bid added");
			}
		} else {
			return array("error"=>"2", "message"=>"Bid must be higher than the current bid");
		}
		return array("error"=>"1", "message"=>"Error making bid");
	}
	
	public function max_bid_create($current_user_id, $item_id, $bid) {
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT id, item_id, user_id, bid, created_at FROM max_bids WHERE item_id = :item_id AND bid >= :bid ORDER BY id DESC LIMIT 0,1");
	
		$stmt->bindParam(":item_id", $item_id);	
		$stmt->bindParam(":bid", $bid);
	
		$stmt->execute();
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC)) == NULL) {
			$stmt = $this->conn->prepare("INSERT INTO max_bids(item_id, user_id, bid) values(:item_id, :user_id, :bid)");
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":bid", $bid);
			if ($stmt->execute()) {
				return array("error"=>"0", "message"=>"Max bid set");
			}
		} else {
			return array("error"=>"2", "message"=>"Bid must be higher than the current bid");
		}
		return array("error"=>"1", "message"=>"Error making bid");
	}
	
	
	public function item_get_bids($current_user_id, $item_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT b.user_id, b.bid, t.username, t.image as userimage, t.locality, t.name FROM bids b, users t WHERE b.id ### :limitid AND t.id = b.user_id AND b.item_id = :item_id ORDER BY b.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->xExecute($stmt);
	}
	
	
}
?>
