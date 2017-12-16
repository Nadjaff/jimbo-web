<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada	
 * @link URL Tutorial link
 */
class DbSessions extends DbBase {
	
	
	public function facebook_login($fbtoken){
			require_once("include/facebook.php");
			$config = array(
				'appId' => '311245872363009',
				'secret' => 'd05f1b1ca6d70783511564b5595bd740',
				'fileUpload' => false,
				'allowSignedRequest' => true,
				);
			$facebook = new Facebook($config);		
			$facebook->setAccessToken($fbtoken);
			$facebook->setExtendedAccessToken();
			//$facebook->setExtendedAccessToken();
			
				$me = $facebook->api('/me');
				$me["friends"] = $facebook->api('/me/friends');
				$me["picture"] = $facebook->api('/me/picture');
				//print_r($me);
				$me["fbid"] = $me["id"];	
				$me["fbtoken"] = $fbtoken;
				return $me;
	}

	public function session_create_fb($fbid, $device, $regid) {
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT u.id, u.name, u.email, u.username, u.image as userimage, u.api_key, u.status, u.fbid, u.fbtoken, u.created_at, u.password_hash FROM users u WHERE u.fbid = :fbid");
	
		$stmt->bindParam(":fbid", $fbid);
	
		$stmt->execute();
		
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC))) {
				//if ($regid != ""){
					
			//echo $res;
					
					$stmt = $this->conn->prepare("DELETE from sessions WHERE regid = :regid");
	
					$stmt->bindParam(":regid", $regid);
				
					$stmt->execute();
					// Add it!
					$api_key = md5(uniqid(rand(), true));
					$stmt = $this->conn->prepare("INSERT INTO sessions(device, regid, user_id, api_key) values(:device, :regid, :user_id, :api_key)");
					$stmt->bindParam(":device", $device);
					$stmt->bindParam(":regid", $regid);
					$stmt->bindParam(":user_id", $res["id"]);
					$stmt->bindParam(":api_key", $api_key);
					$res["api_key"] = $api_key;
					$db = new DbUsers();
					$res["settings"] = $db->user_get_settings($res["id"],$res["id"]);
					$res["error"] = 0;
					if ($stmt->execute()) return $res;
		
					// Check for successful insertion
					return NULL;
				/*}else{
					$res["error"] = 0;
					return $res;
				}*/
		} else {	
			// user not existed with the fbid
			return NULL;
		}
	}

	public function session_create($email, $password, $device, $regid) {
		// fetching user by email
		$stmt = $this->conn->prepare("SELECT id, firstname, lastname, email, username, image as userimage, status, created_at, password_hash, fbid, fbtoken FROM users WHERE email = :email OR username = :username");
	
		$stmt->bindParam(":email", $email);	
		$stmt->bindParam(":username", $email);
	
		$stmt->execute();
		global $log;
		
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC))) {
			// Found user with the email
			// Now verify the password	
	
			if (PassHash::check_password($res["password_hash"], $password)) {
				// User password is correct
				unset($res["password_hash"]);
				//if ($regid != ""){
					if ($device == NULL || $device == ""){
						$device = 0;
					}
					if ($regid == NULL || $regid == ""){
						$regid = "";
					}
					if ($regid != ""){
						$stmt = $this->conn->prepare("DELETE from sessions WHERE regid = :regid");
	
					$stmt->bindParam(":regid", $regid);
				
					$stmt->execute();
					}
					
						// Add it!
						$api_key = md5(uniqid(rand(), true));
						$stmt = $this->conn->prepare("INSERT INTO sessions(device, regid, user_id, api_key) values(:device, :regid, :user_id, :api_key)");
						$stmt->bindParam(":device", $device);
						$stmt->bindParam(":regid", $regid);
						$stmt->bindParam(":user_id", $res["id"]);
						$stmt->bindParam(":api_key", $api_key);
						if ($stmt->execute()) {	
							$res["api_key"] = $api_key;
							$db = new DbUsers();
							$res["settings"] = $db->user_get_settings($res["id"],$res["id"]);
							return $res;
						}
		
					// Check for successful insertion
					return NULL;
				/*}else{
					return $res;
				}*/
			} else {
				// user password is incorrect
				return NULL;
			}
		} else {	
			// user not existed with the email
			return NULL;
		}
	}
	
	
	
	public function session_delete($api_key) {
		// fetching user by email
		/*$stmt = $this->conn->prepare("SELECT d.id FROM devices d WHERE d.user_id = :current_user_id AND d.device = :device AND d.regid = :regid");
		$stmt->bindParam(":device", $device);
		$stmt->bindParam(":regid", $regid);
		$stmt->bindParam(":current_user_id", $current_user_id);
	
		$stmt->execute();
		
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC))) {*/
			$stmt = $this->conn->prepare("DELETE d FROM sessions d WHERE d.api_key = :api_key");
	
			//$stmt->bindParam(":device", $device);
			$stmt->bindParam(":api_key", $api_key);
			//$stmt->bindParam(":current_user_id", $current_user_id);
		
			$stmt->execute();
			
		//}
		return array("error" => 0);
	}
	
	public function session_put($api_key, $device, $regid, $fbtoken) {
		// fetching user by email
		/*$stmt = $this->conn->prepare("SELECT d.id FROM devices d WHERE d.user_id = :current_user_id AND d.device = :device AND d.regid = :regid");
		$stmt->bindParam(":device", $device);
		$stmt->bindParam(":regid", $regid);
		$stmt->bindParam(":current_user_id", $current_user_id);
	
		$stmt->execute();
		
		if (($res = $stmt->fetch(PDO::FETCH_ASSOC))) {*/
		//UPDATE users t set t.no_following = t.no_following + 1 WHERE t.id = :id
			$stmt = $this->conn->prepare("UPDATE sessions d set d.regid = COALESCE(:regid, d.regid), d.updated_at = :updated_at, d.device = COALESCE(:device, d.device), d.fbtoken = COALESCE(:fbtoken, d.fbtoken) WHERE d.api_key = :api_key");
	        $cure_date=date('Y-m-d H:i:s');
			//$stmt->bindParam(":device", $device);
			$stmt->bindParam(":regid", $regid);
			$stmt->bindParam(":device", $device);
			$stmt->bindParam(":fbtoken", $fbtoken);
			$stmt->bindParam(":api_key", $api_key);
			$stmt->bindParam(":updated_at", $cure_date);			
			//$stmt->bindParam(":current_user_id", $current_user_id);		
			$stmt->execute();
			
		//}
		return array("error" => 0);
	}
	
	public function getUserId($api_key) {
		$stmt = $this->conn->prepare("SELECT user_id FROM sessions WHERE api_key = :api_key");
		$stmt->bindParam(":api_key", $api_key);
		if ($stmt->execute()) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row["user_id"];
		} else {
			return NULL;
		}
	}
}
?>
