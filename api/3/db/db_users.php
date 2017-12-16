<?php

class DbUsers extends DbBase {
	/**
	 * Fetching all user items
	 * @param String $current_user_id id of the user
	 */
	public function users_get_all($current_user_id,$q,$newerthan_id,$olderthan_id,$count,$test=0) {
		if ($q == NULL) $q = "";
		$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name FROM users t WHERE t.id ### :limitid AND (t.username LIKE CONCAT(:q, '%') OR t.name LIKE CONCAT('', :q, '%') OR t.name LIKE CONCAT('% ', :q, '%')) AND t.test = :test ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(':q', $q);
		$stmt->bindParam(':test', $test);
		return $this->xExecute($stmt);
	}
	/**
	 * Fetching single item
	 * @param String $item_id id of the item
	 */
	public function user_get($current_user_id, $user_id,$username, $newerthan_id,$olderthan_id,$count, $test) {
		$err = 0;
		$response = array();
		if ($user_id == "0"){
			$stmt = $this->conn->prepare("SELECT t.id FROM users t WHERE t.username = :username");
			$stmt->bindParam(":username", $username);
			if ($stmt->execute()) {
				$response = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($response != NULL && $response["id"] != NULL){
					$user_id = $response["id"];
				}
			}

		}
		$stmt = $this->conn->prepare("SELECT t.user_id FROM following t WHERE t.user_id = :uid AND t.following_id = :fid");
		$stmt->bindParam(":uid", $current_user_id);
		$stmt->bindParam(":fid", $user_id);
		if ($stmt->execute()) {
			$are_following = intval($stmt->rowCount() > 0);
		}else{
			$err = max($err,4);
		}
		
		$qry = "SELECT t.id, t.username, t.name, t.stars, t.sales, t.bio, t.website, t.location_description, t.no_products as num_products, t.no_followers as num_followers, t.no_following as num_following, t.locality, t.image";
		if ($user_id == $current_user_id){
			$qry .= ", t.phone, t.address, t.gender, t.email";
		}
		$qry .= " from users t WHERE t.id = :id";
		$stmt = $this->conn->prepare($qry);
		$stmt->bindParam(":id",$user_id);
		if (!$stmt->execute()) $err = max($err,3);
			
		$response = $stmt->fetch(PDO::FETCH_ASSOC);
		$response["are_following"] = $are_following;
		
		$stmt = $this->limitQuery("SELECT t.image, t.id, t.title,  " . $this->getpricet	. ",  t.created_at from items t " . $this->getpricetablest	. " WHERE t.id ### :limitid AND t.test = :test AND t.status < 5 AND t.user_id = :id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id, $olderthan_id,$count);
		$stmt->bindParam(":id",$user_id);
		$stmt->bindParam(":uid",$current_user_id);
		if ($test == NULL) $test = 0;
		$stmt->bindParam(":test",$test);
		if (!$stmt->execute()) $err = max($err,2);
		$response["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($response["items"]) == 0 && $newerthan_id != 0){
			$stmt = $this->limitQuery("SELECT t.image, " . $this->getpricet	. ", t.id, t.title, t.created_at from items t 
		" . $this->getpricetablest	. " WHERE t.id ### :limitid AND t.test = :test AND t.status < 5 AND t.user_id = :id ORDER BY t.id DESC LIMIT 0, :count",0, $olderthan_id,$count);
			$stmt->bindParam(":id",$user_id);
			$stmt->bindParam(":uid",$current_user_id);
			if ($test == NULL) $test = 0;
			$stmt->bindParam(":test",$test);
			if (!$stmt->execute()) $err = max($err,2);
			$response["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		
		if ($response["location_description"] == ""){
		$stmt = $this->conn->prepare("SELECT t.locality, t.admin, t.country FROM locations t WHERE t.user_id = :uid ORDER BY t.id DESC");
		$stmt->bindParam(":uid", $current_user_id);
		if ($stmt->execute()) {
			$me = $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			$err = max($err,5);
		}
		$stmt = $this->conn->prepare("SELECT t.locality, t.admin, t.country FROM locations t WHERE t.user_id = :uid ORDER BY t.id DESC");
		$stmt->bindParam(":uid", $user_id);
		if ($stmt->execute()) {
			$you = $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			$err = max($err,6);
		}
		if ($you != NULL && $you["locality"] != ""){
			$response["location_description_forged"] = $you["locality"] . ", " . $you["admin"] . (($you["country"] == $me["country"])?"":", " . $you["country"]);
		}
		}
		
		$response["error"] = $err;
		return $response;
	}
	public function user_get_initials($user_id) {
		$err = 0;
		$response = array();
		$stmt = $this->conn->prepare("SELECT t.name, t.col FROM users t WHERE t.id = :uid");
		$stmt->bindParam(":uid", $user_id);
		
		if ($stmt->execute()) {
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}else{
			return array("error" => 1);
		}
	}
	
	
	
	
	
	public function user_get_settings($current_user_id, $user_id) {
		if ($user_id != $current_user_id) return;
		
		$stmt = $this->conn->prepare("SELECT t.notify_message, t.notify_offer, t.notify_comment, t.notify_review, t.notify_tag, t.notify_followed, t.notify_friendjoins, t.currency, t.save_originals from user_settings t WHERE t.id = :uid");
		$stmt->bindParam(":uid", $current_user_id);
		return $this->singleExecute($stmt);
	}
	
	public function user_get_following($current_user_id, $user_id,$newerthan_id,$olderthan_id,$count){
		if ($current_user_id == $user_id){
			$stmt = $this->limitLargeQuery("SELECT NOT ISNULL(youfollow.user_id) as youfollow, t.username, t.image as userimage, t.locality, t.id, t.name FROM users t, following f LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = f.following_id WHERE t.id ### :limitid AND f.user_id = :id AND t.id = f.following_id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		}else{
			$stmt = $this->limitQuery("SELECT NOT ISNULL(youfollow.user_id) as youfollow, t.username, t.image as userimage, t.locality, t.id, t.name FROM users t, following f LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = f.following_id WHERE t.id ### :limitid AND f.user_id = :id AND t.id = f.following_id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		}
		$stmt->bindParam(":id",$user_id);
		$stmt->bindParam(":current_user_id",$current_user_id);
		return $this->xExecute($stmt);
	}
	
	public function user_get_followers($current_user_id, $user_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT NOT ISNULL(youfollow.following_id) as youfollow, t.username, t.image as userimage, t.locality, t.id, t.name FROM users t, following f LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = f.user_id WHERE t.id ### :limitid AND f.following_id = :id AND t.id = f.user_id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":current_user_id",$current_user_id);
		$stmt->bindParam(":id",$user_id);
		return $this->xExecute($stmt);
	}
	public function user_get_reviews($current_user_id, $user_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT t.rated_user_id, u1.username as rated_username, u1.image as rated_userimage, t.rater_user_id, u2.username as rater_username, u2.image as rater_userimage, t.sale_id, t.rating, t.comment, t.type, t.created_at FROM reviews t, users u1, users u2 WHERE t.id ### :limitid AND t.rated_user_id = u1.id AND t.rater_user_id = u2.id AND t.rated_user_id = :user_id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":user_id",$user_id);
		return $this->yExecute($stmt,"reviews");
	}
	
	
	
	
	public function user_get_facebook_friends($current_user_id){
		$stmt = $this->conn->prepare("SELECT t.fbtoken FROM users t WHERE t.id = :uid");
		$stmt->bindParam(":uid", $current_user_id);
		
		$stmt->execute();
		$response = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($response["fbtoken"] != ""){
			require_once("include/facebook.php");
			$config = array(
				'appId' => '311245872363009',
				'secret' => 'd05f1b1ca6d70783511564b5595bd740',
				'fileUpload' => false,
				'allowSignedRequest' => true,
				);
			$facebook = new Facebook($config);
			$facebook->setAccessToken($response["fbtoken"]);
			$facebook->setExtendedAccessToken();
			$me = $facebook->api('/me');
			$me["friends"] = $facebook->api('/me/friends');
			$users = array();
			foreach ($me["friends"]["data"] as $friend){
				$res = $this->getUserFBFollowing($current_user_id, $friend["id"]);
				if ($res != NULL) array_push($users,$res);
			}
			$me["users"] = $users;
			$me["picture"] = $facebook->api('/me/picture');
			$me["error"] = 0;
			return $me;
		}else{
			return array("error" => "1", "message" => "Please login to Facebook");
		}
	}
	
	
	
	
	public function user_update_all(){
		$stmt = $this->conn->prepare("SELECT * FROM users");
		$stmt->execute();
		$a = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($a as $l){
			$r = rand(0,255);
			$g = rand(0,255);
			$b = rand(0,255);
				$r = ($r+255)/2;
				$g = ($g+255)/2;
				$b = ($b+255)/2;
				$r = round($r);
				$g = round($g);
				$b = round($b);
				$col = dechex($r+$g*256+$b*256*256);
				echo $l["id"] . " => " . $col . " AND " . $r . "\n";
			if (substr($l["image"],0,7) == "default"){
				$namearr = explode(" ",$l["name"]);
				if (count($namearr) > 0){
					if (strlen($namearr[0]) > 0){
						$l1 = substr($namearr[0],0,1);
					}
				}
				if (count($namearr) > 1){
					if (strlen($namearr[1]) > 0){
						$l2 = substr($namearr[1],0,1);
					}
				}
				
				$image = "default_" . $col . "_" . $l1 . $l2 . ".jpg";
				$stmt2 = $this->conn->prepare("UPDATE users t SET t.image = :image WHERE t.image = :oldimage AND t.id = :id");
				$stmt2->bindParam(":id", $l["id"]);
				$stmt2->bindParam(":image", $image);
				$stmt2->bindParam(":oldimage", $l["image"]);
				echo $stmt2->execute();
			}
			$stmt2 = $this->conn->prepare("UPDATE users t SET t.col = :col WHERE t.id = :id");
			$stmt2->bindParam(":id", $l["id"]);
			$stmt2->bindParam(":col", $col);
			echo $stmt2->execute();
		}
		echo "Hello World";
		
	}
	
	
	public function mix_color(){
		$t = array('8e1c5f','466BA0','F17C99','F96737','3BAAA5','FEC200');
		$randomcol = $t[array_rand($t)];
		return $randomcol;
		$r = rand(0,255);
			$g = rand(0,255);
			$b = rand(0,255);
				$r = ($r+255)/2;
				$g = ($g+255)/2;
				$b = ($b+255)/2;
				$r = round($r);
				$g = round($g);
				$b = round($b);
				$col = dechex($r+$g*256+$b*256*256);
				return $col;
	}
	public function users_post($name, $username, $email, $password, $lat, $long, $phone, $fbid, $fbtoken, $image, $mobile_code, $test=0) {
		require_once 'include/PassHash.php';
		$response = array();
		$status = 1; 
		// First check if user already existed in db
		if (!$this->isEmailExists($email)) {
			if (!$this->isUserExists($username)) {
				// Generating password hash
				$password_hash = PassHash::hash($password);

				if ($image == NULL){
					$image = "default.jpg";
				}
				if ($fbid == NULL){
					$fbid = "";
				}
				if ($fbtoken == NULL){
					$fbtoken = "";
				}
				
				$col = $this->mix_color();
			
			if ($image == "default.jpg"){
				$namearr = explode(" ",$name);
				if (count($namearr) > 0){
					if (strlen($namearr[0]) > 0){
						$l1 = substr($namearr[0],0,1);
					}
				}
				if (count($namearr) > 1){
					if (strlen($namearr[1]) > 0){
						$l2 = substr($namearr[1],0,1);
					}
				}
				
				$image = "default_" . $col . "_" . $l1 . $l2 . ".jpg";
			}
				// Generating API key
				$api_key = $this->generateApiKey();
				// insert query
				$stmt = $this->conn->prepare("INSERT INTO users(name, username, email, password_hash, phone, fbid, fbtoken, image, api_key, status, mobile_code, col, test) values(:name, :username, :email, :password_hash, :phone, :fbid, :fbtoken, :image, :api_key, :status, :mobile_code, :col, :test)");
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":username", $username);
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":password_hash", $password_hash);
				$stmt->bindParam(":phone", $phone);
				$stmt->bindParam(":fbid", $fbid);
				$stmt->bindParam(":fbtoken", $fbtoken);
				$stmt->bindParam(":image", $image);
				$stmt->bindParam(":api_key", $api_key);
				$stmt->bindParam(":status", $status);
				$stmt->bindParam(":mobile_code", $mobile_code);
				$stmt->bindParam(":col", $col);
				$stmt->bindParam(":test", $test);
	
				$bool = $stmt->execute();
				// Check for successful insertion
				if ($bool) {
					// User successfully inserted
					$response["error"] = 0;
					$response["api_key"] = $api_key;
					$response["id"] = $this->conn->lastInsertId();
					
					$stmt = $this->conn->prepare("INSERT INTO user_settings(id, currency) values(:id, :currency)");
					$curr = "AUD";
					
					$stmt->bindParam(":id",$response["id"]);
					$stmt->bindParam(":currency",$curr);
					
					if (!$stmt->execute()){
						users_delete($response["id"],$response["id"]);
						$response = array();
						$response["error"] = 2;
					}
					return $response;
				} else {
					// Failed to create user
					$response["error"] = 1;
					return $response;
				}
			}else{
				$response["message"] = "This username already exists. Please try logging in instead";
				$response["error"] = 102;
				return $response;
			}
		} else {
			// User with same email already existed in the db
			$response["error"] = 101;
			$response["message"] = "This email address already exists. Please try logging in instead";
			return $response;
		}
	}
	public function users_update(){
		$stmt = $this->conn->prepare("UPDATE users as u inner join (SELECT u.id, COUNT(t.user_id) as items FROM items t LEFT JOIN users u ON u.id = t.user_id WHERE t.status < 5 GROUP BY t.user_id) as counter on u.id = counter.id SET u.no_products = counter.items");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	public function user_follow($current_user_id, $follow_id, $val){
		$stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM following t WHERE t.user_id = :id AND t.following_id = :fid");
		$stmt->bindParam(":id",$current_user_id);
		$stmt->bindParam(":fid",$follow_id);
		$stmt->execute();
		if (($foundrows = $stmt->fetch(PDO::FETCH_ASSOC))){
			$num_rows = $foundrows["total"];
		}
		//$num_rows = $stmt->fetchColumn();
		;
		$stmt = NULL;
		if ($val == 1){
			if ($num_rows == 0){
				$stmt = $this->conn->prepare("UPDATE users t set t.no_following = t.no_following + 1 WHERE t.id = :id");
				$stmt->bindParam(":id", $current_user_id);
				$stmt->execute();
				;
				$stmt = $this->conn->prepare("UPDATE users t set t.no_followers = t.no_followers + 1 WHERE t.id = :id");
				$stmt->bindParam(":id", $follow_id);
				$stmt->execute();
				;
				$stmt = $this->conn->prepare("INSERT INTO following(user_id, following_id) values(:id,:fid)");
			}else{
				// Already following, no need
			}
		}else{
			if ($num_rows == 1){
				$stmt = $this->conn->prepare("UPDATE users t set t.no_following = t.no_following - 1 WHERE t.id = :id");
				$stmt->bindParam(":id", $current_user_id);
				$stmt->execute();
				;
				$stmt = $this->conn->prepare("UPDATE users t set t.no_followers = t.no_followers - 1 WHERE t.id = :id");
				$stmt->bindParam(":id", $follow_id);
				$stmt->execute();
				;
				$stmt = $this->conn->prepare("DELETE t from following t WHERE t.user_id = :id AND t.following_id = :fid");
			}else{
				// Already unfollowed, no need
			}
		}
		if ($stmt != NULL){
			$stmt->bindParam(":id",$current_user_id);
			$stmt->bindParam(":fid",$follow_id);
			if ($stmt->execute()){
				
				if ($val == 1){
					$stmt = $this->conn->prepare("SELECT t.username FROM users t WHERE t.id = :current_user_id");
					$stmt->bindParam(":current_user_id",$current_user_id);
					$stmt->execute();
					include_once("libs/GCM.php");
					//$gcm = new GCM();
			$nots = new DbNotifications();
					if (($user = $stmt->fetch(PDO::FETCH_ASSOC))){
						// Should be only one occurance of user_id
						$nots->send_notification($follow_id, 3,-1,$current_user_id,$current_user_id,"");
						//$gcm->send_notification($follow_id, "Jimbo", $user["username"] . " followed you", "users/" . $current_user_id,"");
					}
				}
				return $val;
			}
		}
		return -1;
	}
	
	public function user_report($current_user_id, $user_id,$comment){
		$stmt = $this->conn->prepare("INSERT INTO reports(reporter_id, user_id, item_id, comment) values(:reporter_id, :user_id,:item_id,:comment)");
		$item_id = 0;
		$stmt->bindParam(":reporter_id",$reporter_id);
		$stmt->bindParam(":user_id",$user_id);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$comment);
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "User Reported");
		}else{
			return array("error" => 1, "message" => "Unable to report user - please try again later");
		}
	}
	
	public function user_clear_fb($current_user_id){
		$blank = "";
		$zero = 0;
		$stmt = $this->conn->prepare("UPDATE users t set t.fbid = :zero, t.fbtoken = :blank WHERE t.id = :id");
		$stmt->bindParam(":id", $current_user_id);
		$stmt->bindParam(":blank", $blank);
		$stmt->bindParam(":zero", $zero);
		$stmt->execute();
		return array("error" => "0", "message" => "Disconnected Facebook account");
	}
	
	public function user_put_fb($current_user_id, $fbid, $fbtoken) {
		$this->user_clear_fb($current_user_id);
			
		$stmt = $this->conn->prepare("UPDATE users t set t.fbid = :fbid, t.fbtoken = :fbtoken WHERE t.id = :id");
		$stmt->bindParam(":fbid", $fbid);
		$stmt->bindParam(":fbtoken", $fbtoken);
		$stmt->bindParam(":id", $current_user_id);
		
		if (!$stmt->execute()){
			$result["error"] = 1;
			$result["message"] = "Update failed. Please try again";
		}else{
			$result["error"] = 0;
			$result["image"] = $img;
			$result["message"] = "Update successful.";
		}
		return $result;
	}
	
	public function user_put($current_user_id, $user_id, $username, $name, $bio, $website, $phone, $email, $location_description, $gender, $img) {
		if ($current_user_id != $user_id) return 0;
		
		if (($username == NULL || $username == "") && $img != ""){
			
			if (substr($img,0,7) == "default"){
				$val = $this->user_get_initials($user_id);
				$name = $val["name"];
				$namearr = explode(" ",$name);
				if (count($namearr) > 0){
					if (strlen($namearr[0]) > 0){
						$l1 = substr($namearr[0],0,1);
					}
				}
				if (count($namearr) > 1){
					if (strlen($namearr[1]) > 0){
						$l2 = substr($namearr[1],0,1);
					}
				}
				
				$col = $this->mix_color();
				$image = "default_" . $col . "_" . $l1 . $l2 . ".jpg";
				$stmt = $this->conn->prepare("UPDATE users t SET t.image = :image WHERE t.id = :id");
				$stmt->bindParam(":id", $user_id);
				$stmt->bindParam(":image", $image);
				
				$img = $image;
			}else{
			
				$stmt = $this->conn->prepare("UPDATE users t set t.image = :image WHERE t.id = :id");
				$stmt->bindParam(":image", $img);
				$stmt->bindParam(":id", $user_id); // already checked this is current user
			}
			if (!$stmt->execute()){
				$result["error"] = 1;
				$result["message"] = "Update failed. Please try again";
			}else{
				$result["error"] = 0;
				$result["image"] = $img;
				$result["message"] = "Update successful.";
			}
			return $result;
		}else{
		$set_bio = '';
		$set_phone = '';
		$set_gender = '';
		$set_location_description = '';
		$set_website = '';

		if($bio != "")
			$set_bio = "t.bio = :bio,";
		if($phone != "")
			$set_phone = "t.phone = :phone,";
		if($gender != "")
			$set_gender = "t.gender = :gender,";
		if($location_description != "")
			$set_location_description = "t.location_description = :location_description,";
		if($website != "")
			$set_website = "t.website = :website,";

		$stmt = $this->conn->prepare("UPDATE users t set t.username = :username, t.name = :name, $set_bio $set_phone $set_location_description $set_gender $set_website t.email = :email WHERE t.id = :id");

		$stmt->bindParam(":username", $username);
		$stmt->bindParam(":name", $name);
		$stmt->bindParam(":email", $email);
		$stmt->bindParam(":id", $user_id); // already checked this is current user

		if($bio != "")
			$stmt->bindParam(":bio", $bio);
		if($phone != "")
			$stmt->bindParam(":phone", $phone);
		if($gender != "")
			$stmt->bindParam(":gender", $gender);
		if($location_description != "")
			$stmt->bindParam(":location_description", $location_description);
		if($website != "")
			$stmt->bindParam(":website", $website);
		
		if (!$stmt->execute()){
			$result["error"] = 1;
			$result["message"] = "Update failed. Please try again";
		}else{
			$result["error"] = 0;
			$result["message"] = "Update successful.";
		}
		return $result;
		}
	}
	
	public function user_put_settings($current_user_id, $user_id, $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals) {
		if ($user_id != $current_user_id) return;
		$stmt = $this->conn->prepare("UPDATE user_settings t set t.notify_message = :notify_message, t.notify_comment = :notify_comment, t.notify_tag = :notify_tag, t.notify_followed = :notify_followed, t.notify_friendjoins = :notify_friendjoins, t.currency = :currency, t.save_originals = :save_originals WHERE t.id = :id");
		$stmt->bindParam(":notify_message", $notify_message);
		$stmt->bindParam(":notify_comment", $notify_comment);
		$stmt->bindParam(":notify_tag", $notify_tag);
		$stmt->bindParam(":notify_followed", $notify_followed);
		$stmt->bindParam(":notify_friendjoins", $notify_friendjoins);
		$stmt->bindParam(":currency", $currency);
		$stmt->bindParam(":save_originals", $save_originals);
		$stmt->bindParam(":id", $user_id);
		return $stmt->execute();
	}
	
	public function user_reset_password($username) {
		require_once 'include/PassHash.php';
		$password_hash = PassHash::hash("earth2007");
		$stmt = $this->conn->prepare("UPDATE users t set t.password_hash = :password_hash WHERE t.username = :username OR t.email = :username");
		$stmt->bindParam(":password_hash", $password_hash);
		$stmt->bindParam(":username", $username);
		if (!$stmt->execute()) {
			return array("error" => 2);
		}else{
			return array("error" => 0, "message" => "Password updated successfully");
		}
	}
	
	public function user_put_password($current_user_id, $user_id, $password, $newpassword) {
		require_once 'include/PassHash.php';
		if ($user_id != $current_user_id) return;
		$password_hash = "";
		if ($password != "" && $newpassword != ""){
			$stmt = $this->conn->prepare("SELECT t.password_hash from users t WHERE t.id = :current_user_id");
			$stmt->bindParam(":current_user_id",$current_user_id);
			if (!$stmt->execute()) {
				return array("error" => 1);
			}
			$response = $stmt->fetch(PDO::FETCH_ASSOC);
			if (PassHash::check_password($response["password_hash"], $password)){
				$password_hash = PassHash::hash($newpassword);
				
				$stmt = $this->conn->prepare("UPDATE users t set t.password_hash = :password_hash WHERE t.id = :id");
			$stmt->bindParam(":password_hash", $password_hash);
			$stmt->bindParam(":id", $user_id);
			if (!$stmt->execute()) {
				return array("error" => 2);
			}else{
				return array("error" => 0, "message" => "Password updated successfully");
			}
			
			}else{
				$result["error"] = 1;
				$result["title"] = "Incorrect Password";
				$result["message"] = "Please enter your current password";
				return $result;
			}
		}		
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
	
	
	
	
	
	
	
	
	
	
	public function user_deactivate($current_user_id, $item_id) {
		if ($user_id != $current_user_id) return 0;
		$stmt = $this->conn->prepare("UPDATE users t set t.status=-1 WHERE t.id = :id");
		$stmt->bindParam(":id", $user_id);
		return $stmt->execute();
	}
	
	
	
	
	
	
	
	public function user_delete($current_user_id, $user_id) {
		if ($user_id != $current_user_id) return 0;
		$stmt = $this->conn->prepare("DELETE t FROM users t WHERE t.id = :id");
		$stmt->bindParam(":id", $current_user_id);
		$num_affected_rows = intval($stmt->execute());
		;
		$stmt = $this->conn->prepare("DELETE t FROM user_settings t WHERE t.id = :id");
		$stmt->bindParam(":id", $current_user_id);
		;
		$num_affected_rows = $num_affected_rows + intval($stmt->execute());
		;
		return $num_affected_rows >= 2;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function getToken($token) {
		$stmt = $this->conn->prepare("SELECT created_at, user_id, token from tokens WHERE token = :token AND created_at BETWEEN SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND NOW();");
		$stmt->bindParam(":token", $token);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res == NULL) $res = array("token" => NULL);
		return $res;
	}
	public function useToken($token, $pass) {
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
	
		
	
	
	
	public function createToken($username) {
		$stmt = $this->conn->prepare("SELECT name, email, id, username, image as userimage from users WHERE username = :username OR email = :email");
		$stmt->bindParam(":username", $username);
		$stmt->bindParam(":email", $username);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			$length = 78;
			$token = bin2hex(openssl_random_pseudo_bytes(16));
			$stmt = $this->conn->prepare("INSERT INTO tokens(user_id, token) values(:user_id, :token)");
					$stmt->bindParam(":user_id", $res["id"]);
					$stmt->bindParam(":token", $token);
					$res["error"] = 0;
					$res["token"] = $token;
					if ($stmt->execute()) return $res;
		}
		return array("error"=>"1", "message"=>"Error resetting password");
	}
	public function getUserEmail($username) {
		$stmt = $this->conn->prepare("SELECT name, email, id, username, image as userimage from users WHERE username = :username OR email = :email");
		$stmt->bindParam(":username", $username);
		$stmt->bindParam(":email", $username);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getUserPhone($phone) {
		$stmt = $this->conn->prepare("SELECT name, phone, id, username, image as userimage from users WHERE phone = :phone");
		$stmt->bindParam(":phone", $phone);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getUserEmailFollowing($current_user_id, $email) {
		$stmt = $this->conn->prepare("SELECT NOT ISNULL(youfollow.following_id) as youfollow, u.name, u.id, u.username, u.image as userimage from users u LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = u.id WHERE u.email = :email OR u.phone = :email"); 
		$stmt->bindParam(":current_user_id", $current_user_id);
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getUserFBFollowing($current_user_id, $fbid) {
		$stmt = $this->conn->prepare("SELECT NOT ISNULL(youfollow.following_id) as youfollow, u.name, u.id, u.username, u.image as userimage from users u LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = u.id WHERE u.fbid = :fbid");
		$stmt->bindParam(":current_user_id", $current_user_id);
		$stmt->bindParam(":fbid", $fbid);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getUserPhoneFollowing($current_user_id, $phone) {
		$stmt = $this->conn->prepare("SELECT NOT ISNULL(youfollow.following_id) as youfollow, u.name, u.id, u.username, u.image as userimage from users u LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = u.id WHERE u.phone = :phone");
		$stmt->bindParam(":current_user_id", $current_user_id);
		$stmt->bindParam(":phone", $phone);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getMobileInfo($current_user_id) {
		$stmt = $this->conn->prepare("SELECT name, email, phone, mobile_code from users WHERE id = :id");
		$stmt->bindParam(":id", $current_user_id);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function confirmMobile($current_user_id,$code) {
		$userid = $current_user_id;
		$neg = -1;
		$stmt = $this->conn->prepare("SELECT mobile_code from users WHERE id = :id");
		$stmt->bindParam(":id", $current_user_id);
		$stmt->execute();
		
		if ($code == $stmt->fetch(PDO::FETCH_ASSOC)){
			$stmt2 = $this->conn->prepare("UPDATE users set mobile_code=:code WHERE id = :id");
			$stmt2->bindParam(":code", $neg);
			$stmt2->bindParam(":id", $current_user_id);
			return $stmt2->execute();
		}
		return false;
	}
	
	
	/**
	 * Checking for duplicate user by email address
	 * @param String $email email to check in db
	 * @return boolean
	 */
	public function isUserExists($username) {
		$stmt = $this->conn->prepare("SELECT id from users WHERE username = :username");
		$stmt->bindParam(":username", $username);
		$stmt->execute();
		return ($stmt->rowCount() > 0);
	}
	public function isEmailExists($email) {
		$stmt = $this->conn->prepare("SELECT id from users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		return ($stmt->rowCount() > 0);
	}
	
	/**
	 * Fetching user api key
	 * @param String $current_user_id user id primary key in user table
	 */
	public function getApiKeyById($current_user_id) {
		$stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = :id");
		$stmt->bindParam(":id", $current_user_id);
		if ($stmt->execute()) {
			// $api_key = $stmt->get_result()->fetch_assoc();
			// TODO
			$stmt->bind_result($api_key);
			;
			return $api_key;
		} else {
			return NULL;
		}
	}
	
	/**
	 * Fetching user id by api key
	 * @param String $api_key user api key
	 */
	public function getUserId($api_key) {
		$stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = :api_key");
		$stmt->bindParam(":api_key", $api_key);
		if ($stmt->execute()) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row["id"];
		} else {
			return NULL;
		}
	}
	public function deleteUserSession($api_key) {
		$stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = :api_key");
		$stmt->bindParam(":api_key", $api_key);
		if ($stmt->execute()) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row["id"];
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
	public function isValidApiKey($api_key) {
		$stmt = $this->conn->prepare("SELECT id from users WHERE api_key = :api_key");
		$stmt->bindParam(":api_key", $api_key);
		$stmt->execute();
		$num_rows = $stmt->rowCount();
		;
		return $num_rows > 0;
	}
	
	/**
	 * Generating random Unique MD5 String for user Api key
	 */
	private function generateApiKey() {
		return md5(uniqid(rand(), true));
	}	
	public function store_contacts($current_user_id,$contacts){
		$stmt = $this->conn->prepare("DELETE t FROM contacts t WHERE t.friend_id = :id");
		$stmt->bindParam(":id", $current_user_id);
		$num_affected_rows = intval($stmt->execute());
		
		foreach ($contacts as $contact){
			$stmt = $this->conn->prepare("INSERT INTO contacts(friend_id, name, data) values(:friend_id, :name,:data)");
			$stmt->bindParam(":friend_id",$current_user_id);
			$stmt->bindParam(":name",$contact["name"]);
			$stmt->bindParam(":data",$contact["data"]);
			$stmt->execute();
		}
		
	}
}


?>