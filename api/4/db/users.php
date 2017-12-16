<?php

/**
 * Fetching all user items
 * @param String $current_user_id id of the user
 */
function users_get_all($current_user_id,$q,$newerthan_id,$olderthan_id,$count) {
	if ($q == NULL) $q = "";
	$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name FROM users t WHERE t.id ### :limitid AND (t.username LIKE CONCAT(:q, '%') OR t.name LIKE CONCAT('', :q, '%') OR t.name LIKE CONCAT('% ', :q, '%')) ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
	$stmt->bindParam(':q', $q);
	return $this->xExecute($stmt);
}
/**
 * Fetching single item
 * @param String $item_id id of the item
 */
function user_get($current_user_id, $user_id) {
	$err = 0;
	$stmt = $this->conn->prepare("SELECT t.user_id FROM following t WHERE t.user_id = :uid AND t.following_id = :fid");
	$stmt->bindParam(":uid", $current_user_id);
	$stmt->bindParam(":fid", $user_id);
	
	if ($stmt->execute()) {
		$are_following = intval($stmt->rowCount() > 0);
	}else{
		$err = 2;
	}
	
	$qry = "SELECT t.id, t.username, t.name, t.stars, t.sales, t.bio, t.no_products, t.no_followers, t.no_following, t.locality, t.image";
	if ($user_id == $current_user_id){
		$qry .= ", t.phone, t.address, t.gender, t.email";
	}
	$qry .= " from users t WHERE t.id = :id";
	
	xQuery("SELECT t.price, t.image, t.id from items t WHERE t.id ### :limitid AND t.user_id = ($qry) ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id, $olderthan_id,$count);
	$res = limitExecute($stmt);
	$res["are_following"] = $are_following;
	
	
	if ($err == 2 && $res["error"] == 0){
		$res["error"] = $err;
	}
	return $res;
}
function user_get_settings($current_user_id, $user_id) {
	if ($user_id != $current_user_id) return;
	/*linkedaccounts
	notifications
	currency
	savephotos*/
	
	$stmt = $this->conn->prepare("SELECT t.notify_message, t.notify_offer, t.notify_comment, t.notify_review, t.notify_tag, t.notify_followed, t.notify_friendjoins, t.currency, t.save_originals from user_settings t WHERE t.id = :uid");
	$stmt->bindParam(":uid", $current_user_id);
	if ($stmt->execute()) {
		$res = array();
		$stmt->bind_result($notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins,$currency, $save_originals);
		// TODO
		// $item = $stmt->get_result()->fetch_assoc();
		$stmt->fetch();
		$res["notify_message"] = $notify_message;
		$res["notify_offer"] = $notify_offer;
		$res["notify_comment"] = $notify_comment;
		$res["notify_review"] = $notify_review;
		$res["notify_tag"] = $notify_tag;
		$res["notify_followed"] = $notify_followed;
		$res["notify_friendjoins"] = $notify_friendjoins;
		$res["currency"] = $currency;
		$res["save_originals"] = $currency;
		$stmt->close();
		
		return $res;
	} else {
		return NULL;
	}
}

function user_get_following($user_id,$newerthan_id,$olderthan_id,$count){
	if ($count == 0){
		$count = 20;
	}
	if ($count > 100){
		$count = 100;
	}
	if ($q == NULL){
		$q = "";
	}
	
	$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name FROM users t, following f";
	$searchqry = "f.user_id = ? AND t.id = f.following_id ORDER BY id DESC LIMIT 0, ?";
	
	if ($newerthan_id != 0){
		$stmt = $this->conn->prepare("$selectqry WHERE t.id > ? AND $searchqry");
		$stmt->bind_param("iii",$newerthan_id,$user_id,$count);
	}else if ($olderthan_id != 0){
		$stmt = $this->conn->prepare("$selectqry WHERE t.id < ? AND $searchqry");
		$stmt->bind_param("iii",$olderthan_id,$user_id,$count);
	}else{
		$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
		$stmt->bind_param("ii",$user_id,$count);
	}
	
	if ($stmt->execute()){
		$stmt->bind_result($username, $userimage, $locality, $id, $name);
		$response = array();
		$list = array();
		while ($stmt->fetch()){
			$res = array();
			$res["username"] = $username;
			$res["userimage"] = $userimage;
			$res["locality"] = $locality;
			$res["id"] = $id;
			$res["name"] = $name;
			array_push($list, $res);
		}
		$response["users"] = $list;
		$response["error"] = 0;
		$stmt->close();
		return $response;
	} else {
		return NULL;
	}
	
	
}

function user_get_followers($user_id,$newerthan_id,$olderthan_id,$count){
	if ($count == 0){
		$count = 20;
	}
	if ($count > 100){
		$count = 100;
	}
	if ($q == NULL){
		$q = "";
	}
	
	$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name FROM users t, following f";
	$searchqry = "f.following_id = ? AND t.id = f.user_id ORDER BY id DESC LIMIT 0, ?";
	
	if ($newerthan_id != 0){
		$stmt = $this->conn->prepare("$selectqry WHERE t.id > ? AND $searchqry");
		$stmt->bind_param("iii",$newerthan_id,$user_id,$count);
	}else if ($olderthan_id != 0){
		$stmt = $this->conn->prepare("$selectqry WHERE t.id < ? AND $searchqry");
		$stmt->bind_param("iii",$olderthan_id,$user_id,$count);
	}else{
		$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
		$stmt->bind_param("ii",$user_id,$count);
	}
	
	if ($stmt->execute()){
		$stmt->bind_result($username, $userimage, $locality, $id, $name);
		$list = array();
		$response = array();
		while ($stmt->fetch()){
			$res = array();
			$res["username"] = $username;
			$res["userimage"] = $userimage;
			$res["locality"] = $locality;
			$res["id"] = $id;
			$res["name"] = $name;
			array_push($list, $res);
		}
		$response["users"] = $list;
		$response["error"] = 0;
		$stmt->close();
		return $response;
	} else {
		return NULL;
	}
	
	
}
function users_post($name, $username, $email, $password, $lat, $long, $phone, $fbid, $imagedata) {
	require_once 'include/PassHash.php';
	$response = array();
	// First check if user already existed in db
	if (!$this->isEmailExists($email)) {
		if (!$this->isUserExists($username)) {
			// Generating password hash
			$password_hash = PassHash::hash($password);

			// Generating API key
			$api_key = $this->generateApiKey();
			$digits = 5;
			//$mobile_code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
			$mobile_code = rand(0, pow(10, $digits)-1);
			// insert query
			$stmt = $this->conn->prepare("INSERT INTO users(name, username, email, password_hash, phone, fbid, image, api_key, status, mobile_code) values(?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
			$stmt->bind_param("ssssssssi", $name, $username, $email, $password_hash, $phone, $fbid, $imagedata, $api_key, $mobile_code);

			// Check for successful insertion
			if ($stmt->execute()) {
				// User successfully inserted
				$response["error"] = 0;
				$response["api_key"] = $api_key;
				$response["id"] = $stmt->insert_id;
				$stmt->close();
				$stmt = $this->conn->prepare("INSERT INTO user_settings(id, currency) values(?, ?)");
				$curr = "AUD";
				$stmt->bind_param("is", $response["id"], $curr);
				if (!$stmt->execute()){
					users_delete($response["id"],$response["id"]);
					$response = array();
					$response["error"] = 2;
					$response["message"] = "Could not setup user profile";
				}
				return $response;
			} else {
				// Failed to create user
				$response["error"] = 1;
				return $response;
			}
			$stmt->close();
		}else{
			$response["error"] = 2;
			return $response;
		}
	} else {
		// User with same email already existed in the db
		$response["error"] = 3;
		return $response;
	}
}

function user_follow($current_user_id, $follow_id,$val){
	$stmt = $this->conn->prepare("SELECT t.user_id FROM following t WHERE t.user_id = ? AND t.following_id = ?");
	$stmt->bind_param("ii",$current_user_id,$follow_id);
	$stmt->execute();
	$stmt->store_result();
	$num_rows = $stmt->num_rows;
	$stmt->close();
	$stmt = NULL;
	if ($val == 1){
		if ($num_rows == 0){
			$stmt = $this->conn->prepare("UPDATE users t set t.no_following = t.no_following + 1 WHERE t.id = ?");
			$stmt->bind_param("i", $current_user_id);
			$stmt->execute();
			$stmt->close();
			$stmt = $this->conn->prepare("UPDATE users t set t.no_followers = t.no_followers + 1 WHERE t.id = ?");
			$stmt->bind_param("i", $follow_id);
			$stmt->execute();
			$stmt->close();
			$stmt = $this->conn->prepare("INSERT INTO following(user_id, following_id) values(?,?)");
		}
	}else{
		if ($num_rows == 1){
			$stmt = $this->conn->prepare("UPDATE users t set t.no_following = t.no_following - 1 WHERE t.id = ?");
			$stmt->bind_param("i", $current_user_id);
			$stmt->execute();
			$stmt->close();
			$stmt = $this->conn->prepare("UPDATE users t set t.no_followers = t.no_followers - 1 WHERE t.id = ?");
			$stmt->bind_param("i", $follow_id);
			$stmt->execute();
			$stmt->close();
			$stmt = $this->conn->prepare("DELETE t from following t WHERE t.user_id = ? AND t.following_id = ?");
		}else{
			// There is probably an error
		}
	}
	if ($stmt != NULL){
		$stmt->bind_param("ii",$current_user_id,$follow_id);
		if ($stmt->execute()){
			$stmt->store_result();
			$stmt->close();
			return $val;
		}
	}
	return -1;
}
function user_put($current_user_id, $user_id, $username, $name, $bio, $image, $phone, $email, $address, $gender, $password, $newpassword) {
	if ($current_user_id != $user_id) return 0;
	$result = array();
	$api_key = $_SERVER[HTTP_AUTHORIZATION];
	$password_hash = "";
	if ($password != "" && $newpassword != ""){
		$stmt = $this->conn->prepare("SELECT t.password_hash from users t WHERE t.api_key = ?");
		$stmt->bind_param("s",$api_key);
		$stmt->execute();
		$stmt->bind_result($password_hash);
		$stmt->fetch();
		if (PassHash::check_password($password_hash, $password)){
			$password_hash = PassHash::hash($newpassword);
		}else{
			$result["error"] = 1;
			$result["title"] = "Incorrect Password";
			$result["message"] = "Please enter your current password";
			return $result;
			//$password_hash = "";
		}
		$stmt->close();
	}
	if ($password_hash == ""){
		$stmt = $this->conn->prepare("UPDATE users t set t.username = ?, t.name = ?, t.bio = ?, t.image = ?, t.phone = ?, t.email = ?, t.address = ?, t.gender = ? WHERE t.id = ?");
		$stmt->bind_param("sssssssii", $username, $name, $bio, $image, $phone, $email, $address, $gender, $current_user_id);
	}else{
		$stmt = $this->conn->prepare("UPDATE users t set t.username = ?, t.name = ?, t.bio = ?, t.image = ?, t.phone = ?, t.email = ?, t.address = ?, t.gender = ?, t.password_hash = ? WHERE t.id = ?");
		$stmt->bind_param("sssssssisi", $username, $name, $bio, $image, $phone, $email, $address, $gender, $password_hash, $current_user_id);
	}
	if (!$stmt->execute()){
		$result["error"] = 1;
		$result["message"] = "Update failed. Please try again";
	}else{
		$result["error"] = 0;
		$result["message"] = "Update successful.";
	}
		
	$stmt->close();
	return $result;
}

function user_put_settings($current_user_id, $user_id, $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals) {
	if ($user_id != $current_user_id) return;
	
	
	/*linkedaccounts
	notifications
	currency
	savephotos*/
	
	$stmt = $this->conn->prepare("UPDATE user_settings t set t.notify_message = ?, t.notify_offer = ?, t.notify_comment = ?, t.notify_review = ?, t.notify_tag = ?, t.notify_followed = ?, t.notify_friendjoins = ?, t.currency = ?, t.save_originals = ? WHERE t.id = ?");
	$stmt->bind_param("ssssssssss", $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals,$user_id);
	return $stmt->execute();
}
function user_deactivate($current_user_id, $item_id) {
	if ($user_id != $current_user_id) return 0;
	$stmt = $this->conn->prepare("UPDATE users t set t.status=-1 WHERE t.id = ?");
	$stmt->bind_param("i", $current_user_id);
	$stmt->execute();
	$num_affected_rows = $stmt->affected_rows;
	$stmt->close();
	return $num_affected_rows > 0;
}







function user_delete($current_user_id, $user_id) {
	if ($user_id != $current_user_id) return 0;
	$stmt = $this->conn->prepare("DELETE t FROM users t WHERE t.id = ?");
	$stmt->bind_param("i", $current_user_id);
	$stmt->execute();
	$num_affected_rows = $stmt->affected_rows;
	$stmt->close();
	$stmt = $this->conn->prepare("DELETE t FROM user_settings t WHERE t.id = ?");
	$stmt->bind_param("i", $current_user_id);
	$stmt->execute();
	$num_affected_rows = $num_affected_rows + $stmt->affected_rows;
	$stmt->close();
	return $num_affected_rows > 0;
}
function getUserEmail($username) {
	$stmt = $this->conn->prepare("SELECT name, email from users WHERE username = ? OR email = ?");
	$stmt->bind_param("ss", $username, $username);
	$stmt->execute();
	$stmt->bind_result($name, $email);
	$stmt->fetch();
	$stmt->close();
	$response = array();
	$response["email"] = $email;
	$response["name"] = $name;
	return $response;
}

function getMobileInfo($current_user_id) {
	$stmt = $this->conn->prepare("SELECT name, email, phone, mobile_code from users WHERE id = ?");
	$stmt->bind_param("i", $current_user_id);
	$stmt->execute();
	$stmt->bind_result($name, $email, $mobile, $mobile_code);
	$stmt->fetch();
	$stmt->close();
	$response = array();
	$response["email"] = $email;
	$response["name"] = $name;
	$response["phone"] = $mobile;
	$response["mobile_code"] = $mobile_code;
	return $response;
}

function confirmMobile($current_user_id,$code) {
	$userid = $current_user_id;
	$neg = -1;
	$stmt = $this->conn->prepare("SELECT mobile_code from users WHERE id = ?");
	$stmt->bind_param("i", $current_user_id);
	$stmt->execute();
	$stmt->bind_result($mobile_code);
	$stmt->fetch();
	$stmt->close();
	if ($code == $mobile_code){
		$stmt2 = $this->conn->prepare("UPDATE users set mobile_code=? WHERE id = ?");
		$stmt2->bind_param("ii", $neg,$current_user_id);
		$stmt2->execute();
		$num_rows = $stmt2->affected_rows;
		$stmt2->close();
		return true;//$num_rows > 0;
	}
	return false;
}


/**
 * Checking for duplicate user by email address
 * @param String $email email to check in db
 * @return boolean
 */
function isUserExists($username) {
	$stmt = $this->conn->prepare("SELECT id from users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->store_result();
	$num_rows = $stmt->num_rows;
	$stmt->close();
	 return $num_rows > 0;
}
function isEmailExists($email) {
	$stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$stmt->store_result();
	$num_rows = $stmt->num_rows;
	$stmt->close();
	 return $num_rows > 0;
}

/**
 * Fetching user api key
 * @param String $current_user_id user id primary key in user table
 */
function getApiKeyById($current_user_id) {
	$stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
	$stmt->bind_param("i", $current_user_id);
	if ($stmt->execute()) {
		// $api_key = $stmt->get_result()->fetch_assoc();
		// TODO
		$stmt->bind_result($api_key);
		$stmt->close();
		return $api_key;
	} else {
		return NULL;
	}
}

/**
 * Fetching user id by api key
 * @param String $api_key user api key
 */
function getUserId($api_key) {
	$stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
	$stmt->bind_param("s", $api_key);
	if ($stmt->execute()) {
		$stmt->bind_result($user_id);
		$stmt->fetch();
		// TODO
		// $user_id = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		return $user_id;
	} else {
		return NULL;
	}
}
function deleteUserSession($api_key) {
	$stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
	$stmt->bind_param("s", $api_key);
	if ($stmt->execute()) {
		$stmt->bind_result($user_id);
		$stmt->fetch();
		// TODO
		// $user_id = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		return $user_id;
	} else {
		return NULL;
	}
}

/**
 * Validating user api key
 * If the api key is there in db, it is a valid key
 * @param String $api_key user api key
 * @return boolean
 */
function isValidApiKey($api_key) {
	$stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
	$stmt->bind_param("s", $api_key);
	$stmt->execute();
	$stmt->store_result();
	$num_rows = $stmt->num_rows;
	$stmt->close();
	return $num_rows > 0;
}

/**
 * Generating random Unique MD5 String for user Api key
 */
private function generateApiKey() {
	return md5(uniqid(rand(), true));
}

?>