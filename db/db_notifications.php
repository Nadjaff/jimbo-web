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
		//echo $current_user_id;
		/*$stmt = $this->limitQuery("SELECT u1.username as user1, u2.username as user2, u3.username as user3, n.user_id, n.type, u.username as i_username, u2.username as o_username, i.user_id as owner_id FROM users u, items i, users u2, notifications n 
		LEFT JOIN notifications as n1 ON (n1.id = (SELECT MAX(id) FROM notifications ns1 WHERE ns1.item_id = n.item_id AND ns1.type = n.type))
		LEFT JOIN notifications as n2 ON (n2.id = (SELECT MAX(id) FROM notifications ns2 WHERE ns2.item_id = n.item_id AND ns2.type = n.type AND ns2.id <> n1.id AND ns2.initiator_id <> n1.initiator_id))
		LEFT JOIN notifications as n3 ON (n3.id = (SELECT MAX(id) FROM notifications ns3 WHERE ns3.item_id = n.item_id AND ns3.type = n.type AND ns3.id <> n1.id AND ns3.id <> n2.id AND ns3.initiator_id <> n1.initiator_id AND ns3.initiator_id <> n2.initiator_id))
		WHERE n.id ### :limitid AND u.id = n.initiator_id AND n.user_id = :current_user_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY n.item_id, n.type ORDER BY n.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);*/
		
		//$stmt = $this->limitQuery("SELECT n.user_id, n.item_id, n.type, u.username as i_username, u2.username as o_username, i.user_id as owner_id FROM users u, items i, users u2, notifications n WHERE n.id ### :limitid AND u.id = n.initiator_id AND n.user_id = :current_user_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY n.item_id, n.type ORDER BY n.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		
		/*$stmt = $this->conn->prepare("SELECT MAX(n.id) as id, n.user_id, n.item_id, n.type, u.username as i_username, u2.username as o_username, i.user_id as owner_id FROM users u, items i, users u2, notifications n WHERE u.id = n.initiator_id AND n.user_id = :current_user_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY n.item_id, n.type ORDER BY n.id DESC");
			$stmt->bindParam(":current_user_id",$current_user_id);
			
			if ($stmt->execute()){
		print_r( $stmt->fetchAll(PDO::FETCH_ASSOC));
			}*/
				
		//$stmt = $this->limitQuery("SELECT newn.id, newn.user_id, newn.item_id, newn.type, newn.i_username, newn.o_username, newn.owner_id FROM (SELECT MAX(n.id) as id, n.user_id, n.item_id, n.type, u.username as i_username, u2.username as o_username, i.user_id as owner_id FROM users u, items i, users u2, notifications n WHERE u.id = n.initiator_id AND n.user_id = :current_user_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY n.item_id, n.type ORDER BY n.id DESC) newn WHERE newn.id ### :limitid ORDER BY newn.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt = $this->limitQuery("SELECT newn.id, newn.user_id, newn.item_id, newn.type, newn.i_username, newn.i_userimage as i_userimage, newn.o_username, newn.owner_id, newn.initiator_id, newn.created_at as created_at FROM (SELECT n.created_at, n.id, n.user_id, n.item_id, n.type, u.username as i_username, u.image as i_userimage, u2.username as o_username, i.user_id as owner_id, n.initiator_id FROM users u, notifications n LEFT JOIN items as i INNER JOIN users as u2 ON u2.id = i.user_id ON i.id = n.item_id WHERE u.id = n.initiator_id AND n.user_id = :current_user_id AND u.id = n.initiator_id ORDER BY n.id DESC) newn WHERE newn.id ### :limitid ORDER BY newn.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		
		
		//"SELECT max(n.id), n.item_id, n.type, n.user_id, u.username as initiator_username, u2.username as owner_username, i.user_id as owner_id, FROM users u, items i, users u2, notifications n WHERE u2.id = i.user_id AND n.type = :type = n.item_id = :item_id AND u.id = n.initiator_id AND n.id ### :limitid AND n.user_id = :current_user_id GROUP BY n.item_id, n.type ORDER BY f.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":current_user_id",$current_user_id);
		$nots = $this->yExecute($stmt,"notifications");	
		//print_r($nots);
		$notes = $nots["notifications"];
		$cnots = count($notes);
		//print_r($nots["notifications"]);
		$output = array();
		for ($i=0;$i<$cnots;$i++){
			//echo $cnots . " is size ";
			$ii = $notes[$i]["id"];
			$type = $notes[$i]["type"];
			if ($type == 3){
				array_push($output,array("text" => $notes[$i]["i_username"] . " started following you", "id" => $notes[$i]["id"], "i_username" => $notes[$i]["i_username"], "userimage" => $notes[$i]["i_userimage"], "created_at"=>$notes[$i]["created_at"], "deeplink"=>"users/" . $notes[$i]["initiator_id"]));
				
			}else{
				$item_id = $notes[$i]["item_id"];
				$user_id = $current_user_id;
				//echo $type . "_" . $item_id . "_" . $user_id . "_" . $notes[$i]["id"];
				//$stmt = $this->conn->prepare("SELECT n.created_at, n.id, n.user_id, u.image as i_userimage, u.username as i_username, u2.username as o_username, i.user_id as owner_id, i.image as item_image FROM users u, items i, users u2, notifications n WHERE u.id = n.initiator_id AND n.user_id = :user_id AND n.type = :type AND n.item_id = :item_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY u.username");
				$stmt = $this->conn->prepare("SELECT n.created_at, n.id, n.user_id, u.image as i_userimage, u.username as i_username, u2.username as o_username, i.user_id as owner_id, i.image as item_image FROM users u, items i, users u2, notifications n WHERE u.id = n.initiator_id AND n.user_id = :user_id AND n.id = :ii AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY u.username");
				$stmt->bindParam(":user_id",$user_id);
				$stmt->bindParam(":ii",$ii);
				//$stmt->bindParam(":type",$type);
				//$stmt->bindParam(":item_id",$item_id);
				
				if (!$stmt->execute()){
					print_r("error");
					print_r($stmt->errorInfo());
					echo $stmt->errorCode();
				}
					
				$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$num_nots = count($notifications);
				if ($num_nots > 2){
					$note = $notifications[0]["i_username"] . ", " . $notifications[1]["i_username"] . ", and " . $notifications[2]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?" commented on ":"") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}else if ($num_nots > 1){
					$note = $notifications[0]["i_username"] . " and " . $notifications[1]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?" commented on ":"") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}else{
					$note = $notifications[0]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?(" commented on " . $notifications[0]["value"]) : "") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}
				array_push($output,array("text" => $note, "id" => $notifications[0]["id"], "userimage" => $notifications[0]["i_userimage"], "item_id"=>$item_id, "created_at"=>$notifications[0]["created_at"], "deeplink"=>"items/" . $item_id));
			}
			//echo "For " . $current_user_id . " is ? " . $notifications[0]["owner_id"];
		}
		return $output;
			//print_r( $output);
			/*if (count($notifications) > 2){
				$note = $notifications[0].initiator_username . ", " . $notifications[1].initiator_username . ", and " . $notifications[2].initiator_username . ($type == 1)?"liked ":"" . ($type == 2)?"commented on ":"" . ($notifications[0].owner_id == $current_user_id) ? "your item" : $notifications[0].owner_username . "\'s" . item;
			}else if (count($notifications) > 1){
				$note = $notifications[0].initiator_username . " and " . $notifications[1].initiator_username . ($type == 1)?"liked ":"" . ($type == 2)?"commented on ":"" . ($notifications[0].owner_id == $current_user_id) ? "your item" : $notifications[0].owner_username . "\'s" . item;
			}else{
				$note = $notifications[0].initiator_username . ($type == 1)?"liked ":"" . ($type == 2)?("said " . $notifications[0].value) : "";
			}*/
	}
	
	
	public function send_notification($user_id, $type, $item_id, $initiator_id, $value){
		$stmt = $this->conn->prepare("INSERT INTO notifications(user_id, type, item_id, initiator_id, value) values(:user_id,:type, :item_id,:initiator_id, :value)");
		$stmt->bindParam(":user_id",$user_id);
		$stmt->bindParam(":type",$type);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":initiator_id",$initiator_id);
		$stmt->bindParam(":value",$value);
			include_once("libs/GCM.php");
		
		if ($stmt->execute()){
			if ($type == 3){
				$stmt = $this->conn->prepare("SELECT u.username, u.image as userimage FROM users u WHERE u.id = :user_id");
				$stmt->bindParam(":user_id",$initiator_id);
				$stmt->execute();
				
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			$gcm = new GCM();
			$gcm->send_notification($user_id,$user["username"],$user["username"] . " started following you", "users/" . $initiator_id,array("username"=>$user["username"], "image" => $user["userimage"]));
			echo $initiator_id;
			}else{
				$stmt = $this->conn->prepare("SELECT n.user_id, u.username as i_username, u2.username as o_username, i.user_id as owner_id FROM users u, items i, users u2, notifications n WHERE u.id = :initiator_id AND n.user_id = :user_id AND n.type = :type AND n.item_id = :item_id AND i.id = n.item_id AND u2.id = i.user_id AND u.id = n.initiator_id GROUP BY u.username");
				$stmt->bindParam(":user_id",$user_id);
				$stmt->bindParam(":type",$type);
				$stmt->bindParam(":item_id",$item_id);
				$stmt->bindParam(":initiator_id",$initiator_id);
				
				if (!$stmt->execute()){
					print_r("error");
					print_r($stmt->errorInfo());
					echo $stmt->errorCode();
				}
					
				$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$num_nots = count($notifications);
				if ($num_nots > 2){
					$note = $notifications[0]["i_username"] . ", " . $notifications[1]["i_username"] . ", and " . $notifications[2]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?" commented on ":"") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}else if ($num_nots > 1){
					$note = $notifications[0]["i_username"] . " and " . $notifications[1]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?" commented on ":"") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}else{
					$note = $notifications[0]["i_username"] . (($type == 1)?" liked ":"") . (($type == 2)?(" commented on " . $notifications[0]["value"]) : "") . (($notifications[0]["owner_id"] == $user_id) ? "your item" : $notifications[0]["o_username"] . "\'s item");
				}
				//echo $note;
				//echo "For " . $user_id . " is ? " . $notifications[0]["owner_id"];
			$gcm = new GCM();
			$gcm->send_notification($user_id,"Jimbo",$note, "items/" . $item_id,"");
			}
				
			
			return true;
		}else{
			return false;
		}
	}
	
	public function send_update(){
			include_once("libs/GCM.php");
			$gcm = new GCM();
			$gcm->send_notificationAll("Jimbo","An update for Jimbo is now available.", "update/" . "NEWVERSION","http://jimbo.co/apk/jimbo-debug.apk");
	}
	
}
?>
