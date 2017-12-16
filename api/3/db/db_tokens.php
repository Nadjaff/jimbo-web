<?php

class DbTokens extends DbBase {
	/**
	 * Fetching all user items
	 * @param String $current_user_id id of the user
	 */
	 
	 public function sendEmail($templateName, $vars){
		 if ($templateName == "jimbo-welcome"){
				$tokenking = $this->createToken($vars["user_id"]);
				if (isset($res) && $res != NULL){	
					$response["error"] = 0;
					$response["title"] = "Check your email";
					$response["message"] = "A verification email has been sent to your email address. Please check your email inbox.";
					require_once 'libs/Mandrill.php';
					try {
						$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
						$message = array(
							'subject' => 'Welcome to Jimbo',
							'from_email' => 'noreply@jimbo.co',
							'from_name' => 'The Jimbo Team',
							'to' => array(
								array(
									'email' => $res["email"],
									'name' => $res["name"],
									'type' => 'to'
								)
							),
							'global_merge_vars' => array(array(
													"name"=>"token",
													"content"=>$tokenking["token"]
													),array(
													"name"=>"name",
													"content"=>$res["name"]
													)),
							'tags' => array('welcome'),
							'metadata' => array('Jimbo' => 'www.jimbo.co'),
							'recipient_metadata' => array(
								array(
									'rcpt' => $res["email"]
								)
							)
						);
						$async = true;
						$result = $mandrill->messages->sendTemplate($templateName, array(array()),$message);
						
					} catch(Mandrill_Error $e) {
						echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
						throw $e;
					}
					return $response;
				}else{
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "This user could not be found.";
					return $response;
				}
		 }
		 
		 if ($templateName == "jimbo-reset-password"){
				$res = $this->createTokenByUsername($vars["username"]);
				if ($res != NULL){	
					$response["error"] = 0;
					$response["title"] = "Check your email";
					$response["message"] = "A link to reset your password has been sent to the email address associated with this account.";
					require_once 'libs/Mandrill.php';
					try {
						$mandrill = new Mandrill('tHH_C7LodHnKpZv34d7PnQ');
						$message = array(
							'subject' => 'Your password has been reset',
							'from_email' => 'noreply@jimbo.co',
							'from_name' => 'The Jimbo Team',
							'to' => array(
								array(
									'email' => $res["email"],
									'name' => $res["name"],
									'type' => 'to'
								)
							),
							'global_merge_vars' => array(array(
													"name"=>"token",
													"content"=>$res["token"]
													),array(
													"name"=>"name",
													"content"=>$res["name"]
													)),
							'tags' => array('password-resets'),
							'metadata' => array('Jimbo' => 'www.jimbo.co'),
							'recipient_metadata' => array(
								array(
									'rcpt' => $res["email"]
								)
							)
						);
						$async = true;
						$result = $mandrill->messages->sendTemplate($templateName, array(array()),$message);
						
					} catch(Mandrill_Error $e) {
						echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
						throw $e;
					}
					return $response;
				}else{
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "This user could not be found.";
					return $response;
				}
		 }
	 }
	public function getToken($token) {
		$stmt = $this->conn->prepare("SELECT created_at, user_id, token from tokens WHERE token = :token AND created_at BETWEEN SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND NOW();");
		$stmt->bindParam(":token", $token);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res == NULL) $res = array("token" => NULL);
		return $res;
	}
	public function useTokenPassword($token, $pass) {
			$stmt = $this->conn->prepare("SELECT user_id from tokens WHERE token = :token AND created_at BETWEEN SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND NOW();");
			$stmt->bindParam(":token", $token);
			$stmt->execute();
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL && $res["user_id"] != NULL){
			$stmt = $this->conn->prepare("DELETE from tokens WHERE token = :token");
			$stmt->bindParam(":token", $token);
			$stmt->execute();
			//$res = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $this->user_put_password_from_token($res["user_id"],$pass);
		}
		return $res;
	}
	public function useTokenVerify($token, $pass) {
			$stmt = $this->conn->prepare("SELECT user_id from tokens WHERE token = :token AND created_at BETWEEN SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND NOW();");
			$stmt->bindParam(":token", $token);
			$stmt->execute();
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL && $res["user_id"] != NULL){
			$stmt = $this->conn->prepare("DELETE from tokens WHERE token = :token");
			$stmt->bindParam(":token", $token);
			$stmt->execute();
			//$res = $stmt->fetch(PDO::FETCH_ASSOC);
			return $this->user_put_email_verified_from_token($res["user_id"],TRUE);
		}
		return $res;
	}
	public function user_put_password_from_token($current_user_id, $newpassword) {
		$password_hash = PassHash::hash($newpassword);
		$stmt = $this->conn->prepare("UPDATE users t set t.password_hash = :password_hash WHERE t.id = :id");
		$stmt->bindParam(":password_hash", $password_hash);
		$stmt->bindParam(":id", $current_user_id);
		if (!$stmt->execute()) {
			return array("error" => 2);
		}else{
			return array("error" => 0, "message" => "Password updated successfully");
		}
	}
	public function user_put_email_verified_from_token($current_user_id, $email_verified) {
		$password_hash = PassHash::hash($newpassword);
		$stmt = $this->conn->prepare("UPDATE users t set t.email_verified = :email_verified WHERE t.id = :id");
		$stmt->bindParam(":email_verified", $email_verified);
		$stmt->bindParam(":id", $current_user_id);
		if (!$stmt->execute()) {
			return array("error" => 2);
		}else{
			return array("error" => 0, "message" => "Password updated successfully");
		}
	}
	public function user_put_mobile_verified_from_token($current_user_id, $mobile_verified) {
		$password_hash = PassHash::hash($newpassword);
		$stmt = $this->conn->prepare("UPDATE users t set t.mobile_verified = :mobile_verified WHERE t.id = :id");
		$stmt->bindParam(":mobile_verified", $mobile_verified);
		$stmt->bindParam(":id", $current_user_id);
		if (!$stmt->execute()) {
			return array("error" => 2);
		}else{
			return array("error" => 0, "message" => "Password updated successfully");
		}
	}
	
		
	public function createToken($res) {
		$length = 78;
		$token = bin2hex(openssl_random_pseudo_bytes(16));
		$stmt = $this->conn->prepare("INSERT INTO tokens(user_id, token) values(:user_id, :token)");
		$stmt->bindParam(":user_id", $res["id"]);
		$stmt->bindParam(":token", $token);
		$res["error"] = 0;
		$res["token"] = $token;
		if ($stmt->execute()) return $res;
		return array("error"=>"1", "message"=>"Error creating token");
	}
	
	
	public function createTokenByUsername($username) {
		$stmt = $this->conn->prepare("SELECT name, email, id, username, image as userimage from users WHERE username = :username OR email = :email");
		$stmt->bindParam(":username", $username);
		$stmt->bindParam(":email", $username);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			return $this->createToken($res);
		}
		return array("error"=>"1", "message"=>"Error resetting password");
	}
}


?>