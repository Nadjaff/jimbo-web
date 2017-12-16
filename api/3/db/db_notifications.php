<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbNotifications extends DbBase {

	
	public function get_notifications($current_user_id, $newerthan_id, $olderthan_id, $count,$test){
		$stmt = $this->limitQuery("SELECT n.id, u.username, u.id as user_id, u.image as userimage, n.type, n.item_id, n.value, n.created_at, CONCAT(nt.prefix, '@',u.username, nt.suffix) as text, CONCAT(nt.deeplink, n.deeplink_id) as deeplink, n.read, i.image as item_image, i.title FROM users u, items i, notifications n, notification_text nt WHERE n.id ### :limitid AND u.id = n.initiator_id AND n.user_id = :user_id AND i.id = n.item_id AND nt.id = n.type ORDER BY n.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
				$stmt->bindParam(":user_id",$current_user_id);
		$nots = $this->yExecute($stmt,"notifications");	
		return $nots["notifications"];
	}
	
	
	public function send_notification($user_id, $type, $item_id, $initiator_id, $deeplink_id, $value){
		if ($type == 4){
			// have username not id, get id.
			$stmt = $this->conn->prepare("SELECT t.id FROM users t WHERE t.username = :username");
			$stmt->bindParam(":username", $user_id);
			if ($stmt->execute()) {
				$response = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($response != NULL && $response["id"] != NULL){
					$user_id = $response["id"];
				}
			}
		}
		$stmt = $this->conn->prepare("INSERT INTO notifications(user_id, type, item_id, initiator_id, deeplink_id, value) values(:user_id,:type, :item_id,:initiator_id, :deeplink_id, :value)");
		$stmt->bindParam(":user_id",$user_id);
		$stmt->bindParam(":type",$type);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":initiator_id",$initiator_id);
		$stmt->bindParam(":deeplink_id",$deeplink_id);
		$stmt->bindParam(":value",$value);
			include_once("libs/GCM.php");
		
		if ($stmt->execute()){
			$stmt = $this->conn->prepare("SELECT n.id, u.username, u.id as user_id, u.image as userimage, n.type, n.item_id, n.value, n.created_at, CONCAT(nt.prefix, '@',u.username, nt.suffix) as text, CONCAT(nt.deeplink, n.deeplink_id) as deeplink, n.read, i.image as item_image, i.title FROM users u, items i, notifications n, notification_text nt WHERE u.id = n.initiator_id AND n.user_id = :user_id AND i.id = n.item_id AND nt.id = n.type AND n.id=:nid");
			$stmt->bindParam(":user_id",$user_id);
			$stmt->bindParam(":nid",$this->conn->lastInsertId());
			if ($stmt->execute()){
				$r = $stmt->fetch(PDO::FETCH_ASSOC);
				$gcm = new GCM();
				$gcm->send_notification($user_id,"Jimbo",$r["text"], "items/" . $item_id,$r);
				return true;
			}
		}else{
			return false;
		}
	}
	
	public function read_notification($current_user_id, $nid){
			$stmt = $this->conn->prepare("UPDATE notifications n SET n.read = 1 WHERE n.user_id = :current_user_id AND n.id = :nid");
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->bindParam(":nid",$nid);
			$stmt->execute();
	}
	
	public function send_update(){
			include_once("libs/GCM.php");
			$gcm = new GCM();
			$gcm->send_notificationAll("Jimbo","An update for Jimbo is now available.", "update/" . "NEWVERSION","http://jimbo.co/apk/jimbo-debug.apk");
	}
	
}
?>
