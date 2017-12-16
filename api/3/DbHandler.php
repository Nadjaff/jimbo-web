<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/include/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    
    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function session_create($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT id, name, email, username, api_key, status, created_at, password_hash FROM users WHERE email = ? OR username = ?");

        $stmt->bind_param("ss", $email, $email);

        $stmt->execute();

		$stmt->bind_result($id, $name, $email, $username, $api_key, $status, $created_at, $password_hash);

        $stmt->store_result();
		
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
				$user = array();
				$user["name"] = $name;
				$user["email"] = $email;
				$user["user_id"] = $id;
				$user["username"] = $username;
				$user["api_key"] = $api_key;
				$user["status"] = $status;
				$user["created_at"] = $created_at;
				return $user;
            } else {
                // user password is incorrect
                return NULL;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return NULL;
        }
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function limitQuery($qry,$newerthan_id,$olderthan_id,$count){
		$r = "";
		if ($count == 0) $count = 20;
		if ($count > 100) $count = 100;
		
		if ($newerthan_id == 0 && $olderthan_id == 0){
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*\sAND/i","WHERE",$qry);
			$qry = preg_replace("/WHERE\s\S*\s###\s\S*/i","",$qry);
			// Remove limiting on newerthan_id or olderthan_id
		}else{
			if ($newerthan_id != 0){
				$r=">";
				$rid=intval($newerthan_id);
			}else if ($olderthan_id != 0){
				$r="<";
				$rid=intval($olderthan_id);
			}
			$qry = str_replace("###",$r,$qry);
		}
		//echo $qry;
		$stmt = $this->conn->prepare("$qry");
		$stmt->bindParam(':limitid', $rid,PDO::PARAM_INT);
		$stmt->bindParam(':count', $count,PDO::PARAM_INT);
		return $stmt;
	}
	
	public function xExecute($stmt){
		if ($stmt->execute()) {
			return array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>0);
		}else{
			print_r("error");
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			return array("users"=>$stmt->fetchAll(PDO::FETCH_ASSOC), "error"=>1);
		}
	}
	public function limitExecute($stmt){
		if ($stmt->execute()) {
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$res["error"] = 0;
		}else{
			print_r("error");
			print_r($stmt->errorInfo());
			echo $stmt->errorCode();
			$res = array("error"=>1);
		}
		return $res;
	}
	
	/**
     * Fetching all user items
     * @param String $current_user_id id of the user
     */
    public function users_get_all($current_user_id,$q,$newerthan_id,$olderthan_id,$count) {
		if ($q == NULL) $q = "";
		$stmt = $this->limitQuery("SELECT t.username, t.image, t.locality, t.id, t.name FROM users t WHERE t.id ### :limitid AND (t.username LIKE CONCAT(:q, '%') OR t.name LIKE CONCAT('', :q, '%') OR t.name LIKE CONCAT('% ', :q, '%')) ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(':q', $q);
		return $this->xExecute($stmt);
    }
	/**
     * Fetching single item
     * @param String $item_id id of the item
     */
    public function user_get($current_user_id, $user_id) {
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
	
	
	
	public function user_get_settings($current_user_id, $user_id) {
		if ($user_id != $current_user_id) return;
		/*linkedaccounts
		notifications
		currency
		savephotos*/
		
		$stmt = $this->conn->prepare("SELECT t.notify_message, t.notify_offer, t.notify_comment, t.notify_review, t.notify_tag, t.notify_followed, t.notify_friendjoins, t.currency, t.save_originals from user_settings t WHERE t.id = ?");
		$stmt->bind_param("i", $current_user_id);
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
	
	public function user_get_following($user_id,$newerthan_id,$olderthan_id,$count){
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
	
	public function user_get_followers($user_id,$newerthan_id,$olderthan_id,$count){
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
	
	
	
	
	
	
	
	
	
	
	public function users_post($name, $username, $email, $password, $lat, $long, $phone, $fbid, $imagedata) {
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
	
	public function user_follow($current_user_id, $follow_id,$val){
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
	
	
	
	
	
	
	
	public function user_put($current_user_id, $user_id, $username, $name, $bio, $image, $phone, $email, $address, $gender, $password, $newpassword) {
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
	
	public function user_put_settings($current_user_id, $user_id, $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals) {
		if ($user_id != $current_user_id) return;
		
		
		/*linkedaccounts
		notifications
		currency
		savephotos*/
		
		$stmt = $this->conn->prepare("UPDATE user_settings t set t.notify_message = ?, t.notify_offer = ?, t.notify_comment = ?, t.notify_review = ?, t.notify_tag = ?, t.notify_followed = ?, t.notify_friendjoins = ?, t.currency = ?, t.save_originals = ? WHERE t.id = ?");
		$stmt->bind_param("ssssssssss", $notify_message, $notify_offer, $notify_comment, $notify_review, $notify_tag, $notify_followed, $notify_friendjoins, $currency, $save_originals,$user_id);
		return $stmt->execute();
    }
	
	
	
	
	
	
	
	
	
	
    public function user_deactivate($current_user_id, $item_id) {
		if ($user_id != $current_user_id) return 0;
		$stmt = $this->conn->prepare("UPDATE users t set t.status=-1 WHERE t.id = ?");
		$stmt->bind_param("i", $current_user_id);
		$stmt->execute();
		$num_affected_rows = $stmt->affected_rows;
		$stmt->close();
        return $num_affected_rows > 0;
    }
	
	
	
	
	
	
	
	public function user_delete($current_user_id, $user_id) {
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function getUserEmail($username) {
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
	
	public function getMobileInfo($current_user_id) {
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
	
	public function confirmMobile($current_user_id,$code) {
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
    public function isUserExists($username) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
       	 return $num_rows > 0;
    }
    public function isEmailExists($email) {
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
    public function getApiKeyById($current_user_id) {
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
    public function getUserId($api_key) {
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
	public function deleteUserSession($api_key) {
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
    public function isValidApiKey($api_key) {
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

































    /* ------------- `items` table method ------------------ */

    /**
     * Creating new item
     * @param String $user_id user id to whom item belongs to
     * @param String $item item text
     */
    public function items_post($current_user_id, $title,$price,$description,$quantity,$images,$status,$location_id) {
		if ($status == 1){
			$status = 0;
		}
		// Need to put a select statement in here to check that the user actually owns this item!
		
			
			$imagesarr = explode(",",$images);
			$total_images = count($imagesarr);
			
		$stmt = $this->conn->prepare("INSERT INTO items(user_id,title,price,description,quantity,status,image,location_id, totalimages) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isisiisii", $current_user_id, $title, $price, $description,$quantity,$status,$images,$location_id, $total_images);
		
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // item row created
            // now assign the item to user
            $new_item_id = $this->conn->insert_id;
            return $new_item_id;
        } else {
            // item failed to create
            return NULL;
        }
    }

    /**
     * Fetching single item
     * @param String $item_id id of the item
     */
    public function item_get($current_user_id, $item_id) {
        $stmt = $this->conn->prepare("SELECT ut.id, ut.username, ut.image, lt.locality, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.image, t.status, t.published_at, t.stars, t.comments, t.shares, t.views from items t, users ut, locations lt WHERE t.id = ? AND ut.id = t.user_id AND lt.id = t.location_id");
        $stmt->bind_param("i", $item_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($user_id, $username, $image, $locality, $total_images, $created_at, $item_id, $title, $price, $description, $quantity, $img, $status, $published_at, $likes, $comments, $shares, $views );
            // TODO
            // $item = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["user_id"] = $user_id;
            $res["username"] = $username;
            $res["userimage"] = $image;
            $res["image"] = $img;
            $res["locality"] = $locality;
            $res["total_images"] = $total_images;
            $res["created_at"] = $created_at;
            $res["id"] = $item_id;
            $res["title"] = $title;
            $res["price"] = $price;
            $res["description"] = $description;
            $res["quantity"] = $quantity;
            $res["status"] = $status;
            $res["created_at"] = $created_at;
            $res["likes"] = $likes;
            $res["comments"] = $comments;
            $res["shares"] = $shares;
            $res["views"] = $views;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }
	
	
	
	public function item_get_likes($item_id,$newerthan_id,$olderthan_id,$count){
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($q == NULL){
			$q = "";
		}
		$response = array();
		
		$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name FROM users t, likes f";
		$searchqry = "f.item_id = ? AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, ?";
		
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE t.id > ? AND $searchqry");
			$stmt->bind_param("iii",$newerthan_id,$item_id,$count);
		}else if ($olderthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE t.id < ? AND $searchqry");
			$stmt->bind_param("iii",$olderthan_id,$item_id,$count);
		}else{
			$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
			$stmt->bind_param("ii",$item_id,$count);
		}
		
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $id, $name);
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
			$response["error"] = 0;
			$response["likes"] = $list;
			
            $stmt->close();
            return $response;
        } else {
            return NULL;
        }
		
		
	}
	
	public function item_get_comments($item_id,$newerthan_id,$olderthan_id,$count){
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($q == NULL){
			$q = "";
		}
		$response = array();
		$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name, f.comment, f.id, f.created_at FROM users t, comments f";
		$searchqry = "f.item_id = ? AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, ?";
		
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE f.id > ? AND $searchqry");
			$stmt->bind_param("iii",$newerthan_id,$item_id,$count);
		}else if ($olderthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE f.id < ? AND $searchqry");
			$stmt->bind_param("iii",$olderthan_id,$item_id,$count);
		}else{
			$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
			$stmt->bind_param("ii",$item_id,$count);
		}
		
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $id, $name, $comment, $comment_id, $commented_at);
			$list = array();
            while ($stmt->fetch()){
           		$res = array();
				$res["username"] = $username;
				$res["userimage"] = $userimage;
				$res["locality"] = $locality;
				$res["id"] = $id;
				$res["name"] = $name;
				$res["comment"] = $comment;
				$res["comment_id"] = $comment_id;
				$res["commented_at"] = $commented_at;
				array_push($list, $res);
			}
			$response["error"] = 0;
			$response["comments"] = $list;
			
            $stmt->close();
            return $response;
        } else {
            return NULL;
        }
		
		
	}
	
	
	
	public function item_get_offers($item_id,$newerthan_id,$olderthan_id,$count){
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($q == NULL){
			$q = "";
		}
		$response = array();
		
		$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name, f.message, f.id, f.result, f.created_at FROM users t, messages f, conversations c";
		$searchqry = "c.item_id = ? AND f.conversation_id = c.id AND t.id = f.user_id AND f.type = 2 ORDER BY f.id DESC LIMIT 0, ?";
		
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE f.id > ? AND $searchqry");
			$stmt->bind_param("iii",$newerthan_id,$item_id,$count);
		}else if ($olderthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE f.id < ? AND $searchqry");
			$stmt->bind_param("iii",$olderthan_id,$item_id,$count);
		}else{
			$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
			$stmt->bind_param("ii",$item_id,$count);
		}
		
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $id, $name, $offer, $offer_id, $result, $offered_at);
			$list = array();
            while ($stmt->fetch()){
           		$res = array();
				$res["username"] = $username;
				$res["userimage"] = $userimage;
				$res["locality"] = $locality;
				$res["id"] = $id;
				$res["name"] = $name;
				$res["offer"] = $offer;
				$res["offer_id"] = $offer_id;
				$res["result"] = $result;
				$res["offered_at"] = $offered_at;
				array_push($list, $res);
			}
			$response["error"] = 0;
			$response["offers"] = $list;
            $stmt->close();
            return $response;
        } else {
            return NULL;
        }
		
		
	}
	
	
	
	
	public function item_like($current_user_id, $item_id,$val){
		$stmt = $this->conn->prepare("SELECT t.id FROM likes t WHERE t.user_id = ? AND t.item_id = ?");
		$stmt->bind_param("ii",$current_user_id,$item_id);
		$stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
		$stmt->close();
		$stmt = NULL;
		if ($val == 1){
			if ($num_rows == 0){
				$stmt = $this->conn->prepare("UPDATE items t set t.stars = t.stars + 1 WHERE t.id = ?");
				$stmt->bind_param("i", $item_id);
				$stmt->execute();
				$stmt->close();
				$stmt = $this->conn->prepare("INSERT INTO likes(user_id, item_id) values(?,?)");
			}
		}else{
			if ($num_rows == 1){
				$stmt = $this->conn->prepare("UPDATE items t set t.stars = t.stars - 1 WHERE t.id = ?");
				$stmt->bind_param("i", $item_id);
				$stmt->execute();
				$stmt->close();
				$stmt = $this->conn->prepare("DELETE t from likes t WHERE t.user_id = ? AND t.item_id = ?");
			}else{
				// There is probably an error
			}
		}
		if ($stmt != NULL){
			$stmt->bind_param("ii",$current_user_id,$item_id);
			if ($stmt->execute()){
				$stmt->store_result();
				$stmt->close();
				return $val;
			}
		}
		return $num_rows;
	}
	
	
	public function item_comment($current_user_id, $item_id,$comment){
		$stmt = $this->conn->prepare("UPDATE items t set t.comments = t.comments + 1 WHERE t.id = ?");
		$stmt->bind_param("i", $item_id);
		$stmt->execute();
		$stmt->close();
		$stmt = $this->conn->prepare("INSERT INTO comments(user_id, item_id, comment) values(?,?,?)");
		$stmt->bind_param("iis",$current_user_id,$item_id,$comment);
		if ($stmt->execute()){
			$stmt->close();
			return $this->conn->insert_id;
		}else{
			return 0;
		}
	}
	public function item_offer($current_user_id, $item_id,$offer){
		$stmt = $this->conn->prepare("UPDATE offers t set t.comments = t.comments + 1 WHERE t.id = ?");
		$stmt->bind_param("i", $item_id);
		$stmt->execute();
		$stmt->close();
		$stmt = $this->conn->prepare("INSERT INTO offers(user_id, item_id, comment) values(?,?,?)");
		$stmt->bind_param("iii",$user_id,$item_id,$offer);
	
		if ($stmt->execute()){
			$stmt->close();
			return $this->conn->insert_id;
		}else{
			return 0;
		}
	}

    /**
     * Fetching all user items
     * @param String $user_id id of the user
     */
    public function getAllItems($current_user_id,$filter,$q,$newerthan_id,$olderthan_id,$count) {
		
		
		
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($q == NULL){
			$q = "";
		}
		
		$selectqry = "SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, t.comments, t.stars from items t, users ut, locations lt";
		$addselectqry = "";
		$searchqry = "";
		switch($filter){
			case "":
				$selectqry .= "";
				//$searchqry = "AND f.user_id = ? AND f.following_id = t.user_id AND";
				$searchqry = "";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
			break;
			case "home":
				$selectqry .= ", following f";
				$searchqry = "AND f.user_id = ? AND f.following_id = t.user_id";
				//$searchqry = "AND t.user_id <> ?";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
			break;
			case "purchased":
				$searchqry =  "AND t.purchased_by = ?";
			break;
			case "sold":
				$searchqry =  "AND t.user_id = ? AND t.purchased_by <> 0";
			break;
			case "favorites":
				$selectqry .= ", likes f";
				$searchqry =  "AND f.user_id = ? AND f.item_id = t.id";
			break;
			case "explore":
				$searchqry =  "AND t.user_id <> ?";
			break;
		}
		$searchqry .= " AND (t.title LIKE CONCAT('%', ?, '%') OR t.description LIKE CONCAT('%',?, '%')) ORDER BY t.id DESC LIMIT 0, ?";
		
		//$searchqry .= " ORDER BY id DESC LIMIT 0, ?";
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE t.id > ? AND ut.id = t.user_id AND lt.id = t.location_id $searchqry");
			if ($filter == ""){
				$stmt->bind_param("issi",$newerthan_id,$q,$q,$count);
			}else{
				$stmt->bind_param("iissi",$newerthan_id,$current_user_id,$q,$q,$count);
			}
		}else if ($olderthan_id != 0){
			
			$stmt = $this->conn->prepare("$selectqry WHERE t.id < ? AND ut.id = t.user_id AND lt.id = t.location_id $searchqry");
			if ($filter == ""){
				//echo "$selectqry WHERE t.id < ? AND ut.id = t.user_id AND lt.id = t.location_id $searchqry";
				$stmt->bind_param("issi",$olderthan_id,$q,$q,$count);
			}else{
				$stmt->bind_param("iissi",$olderthan_id,$current_user_id,$q,$q,$count);
			}
		}else if ($current_user_id == ""){
			//echo "$selectqry WHERE ut.id = t.user_id AND lt.id = t.location_id $searchqry";
			$stmt = $this->conn->prepare("$selectqry WHERE ut.id = t.user_id AND lt.id = t.location_id $searchqry");
			if ($filter == ""){
				$stmt->bind_param("ssi",$q,$q,$count);
			}else{
				return;
			}
		}else{
			//echo "$selectqry WHERE ut.id = t.user_id AND lt.id = t.location_id $searchqry";
			$stmt = $this->conn->prepare("$selectqry WHERE ut.id = t.user_id AND lt.id = t.location_id $searchqry");
			if ($filter == ""){
				$stmt->bind_param("issi",$current_user_id,$q,$q,$count);
			}else{
				$stmt->bind_param("issi",$current_user_id,$q,$q,$count);
				//return;
			}
		}
		
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $user_id, $total_images, $created_at, $id, $title, $price, $description, $quantity, $status, $published_at, $image,$comments,$stars);
			$response = array();
            while ($stmt->fetch()){
           		$res = array();
				$res["username"] = $username;
				$res["userimage"] = $userimage;
				$res["locality"] = $locality;
				$res["id"] = $id;
				$res["user_id"] = $user_id;
				$res["total_images"] = $total_images;
				$res["title"] = $title;
				$res["price"] = $price;
				$res["description"] = $description;
				$res["quantity"] = $quantity;
				$res["status"] = $status;
				$res["published_at"] = $published_at;
				$res["created_at"] = $created_at;
				$res["images"] = $image;
				$imgarr = explode(",",$image);
				$res["image"] = $imgarr[0];
				$res["comments"] = $comments;
				$res["stars"] = $stars;
				array_push($response, $res);
			}
            $stmt->close();
            return $response;
        } else {
            return NULL;
        }
    }

    /**
     * Updating item
     * @param String $item_id id of the item
     * @param String $item item text
     * @param String $status item status
     */
    public function updateItem($current_user_id, $item_id, $title, $price, $description, $quantity, $images, $status, $location_id) {
        $stmt = $this->conn->prepare("UPDATE items t set t.title = ?, t.price = ?, t.description = ?, t.quantity = ?, t.image = ?, t.status = ?, t.location_id = ? WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("sisisiiii", $title, $price, $description, $quantity, $images, $status, $location_id, $item_id, $current_user_id);
        if ($stmt->execute()){
        	$stmt->close();
        	return 1;
		}else{
        	return;
		}
    }

    /**
     * Publishing item
     * @param String $item_id id of the item
     * @param String $item item text
     * @param String $status item status
     */
    public function publishItem($current_user_id, $item_id) {
        $stmt = $this->conn->prepare("UPDATE items t set t.published_at = ?, t.status = ? WHERE t.id = ? AND t.user_id = ?");
		$status = 1;
		$date = new DateTime();
		$published_at = $date->getTimestamp();
        $stmt->bind_param("siii", $published_at, $status, $item_id, $current_user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }
    /**
     * Deleting a item
     * @param String $item_id id of the item to delete
     */
    public function deleteItem($current_user_id, $item_id) {
        $stmt = $this->conn->prepare("DELETE t FROM items t WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("ii", $item_id, $current_user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /* ------------- `user_items` table method ------------------ */

    /**
     * Function to assign a item to user
     * @param String $current_user_id id of the user
     * @param String $item_id id of the item
     */
    public function createUserItem($current_user_id, $item_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_items(user_id, item_id) values(?, ?)");
        $stmt->bind_param("ii", $current_user_id, $item_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }
































    /* ------------- `images` table method ------------------ */

    /**
     * Creating new item
     * @param String $current_user_id user id to whom item belongs to
     * @param String $item item text
     */
    public function createImage($current_user_id, $image) {
		$stmt = $this->conn->prepare("INSERT INTO images(user_id,image) VALUES(?, ?)");
		$stmt->bind_param("is", $current_user_id, $image);
		
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // item row created
            // now assign the item to user
            $new_item_id = $this->conn->insert_id;
            return $new_item_id;
        } else {
            // item failed to create
            return NULL;
        }
    }
	public function images_get($item_id) {
        $stmt = $this->conn->prepare("SELECT image, i FROM images WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($image, $i);
		$images = array();
		while ($stmt->fetch()){
			$tmp = array();
			$tmp["i"] = $i;
			$tmp["src"] = $image;
			array_push($images,$tmp);
		}		
        $stmt->close();
        return $images;
    }
	public function image_get($current_user_id,$image_id) {
        $stmt = $this->conn->prepare("SELECT image, i, item_id FROM images WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $current_user_id);
        $stmt->execute();
        $stmt->bind_result($image, $i, $item_id);
		$tmp = array();
		
		if ($stmt->fetch()){
			$tmp["i"] = $i;
			$tmp["src"] = $image;
			$tmp["item_id"] = $item_id;
		}else{
			$tmp["i"] = -1;
		}
        $stmt->close();
        return $tmp;
    }
	public function associateImageWithItem($current_user_id,$item_id,$image_id,$i){
		
		$stmt = $this->conn->prepare("SELECT item_id, image FROM images WHERE id = ?");
        $stmt->bind_param("i",$image_id);
        $result = $stmt->execute();
        $stmt->bind_result($current_item_id, $src);

        $stmt->store_result();
        if ($stmt->num_rows > 0) {
			if ($current_item_id != 0 && $current_item_id != $item_id){
				$stmt->close();
				return 0;
			}
		}
		$stmt->close();
			
		$stmt = $this->conn->prepare("UPDATE images t set t.item_id = ?, t.i = ? WHERE t.id = ? AND t.user_id = ?");
		$stmt->bind_param("iiii", $item_id, $i, $image_id, $current_user_id);
		$stmt->execute();
		$num_affected_rows = $stmt->affected_rows;
		$stmt->close();
		
		
		return $num_affected_rows > 0;
	}

    /**
     * Deleting a item
     * @param String $item_id id of the item to delete
     */
    public function deleteImage($current_user_id, $item_id) {
        $stmt = $this->conn->prepare("DELETE t FROM images t WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("ii", $item_id, $current_user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
     * Fetching all user items
     * @param String $current_user_id id of the user
     */
    public function conversations_get_all($current_user_id,$item_id,$newerthan_id,$olderthan_id,$count) {
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		
		$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name, c.id, ut.id, ut.title FROM users t, conversations c, items ut";
		if ($item_id == NULL){
			$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name, c.id FROM users t, conversations c";
		}
		$searchqry = "(c.partya = ? OR c.partyb = ?) AND ((c.partya = t.id AND c.partya <> ?) OR (c.partyb = t.id AND c.partyb <> ?)) ORDER BY c.id DESC LIMIT 0, ?";
		
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE c.id > ? AND $searchqry");
			$stmt->bind_param("iiiiii",$newerthan_id,$current_user_id,$current_user_id,$current_user_id,$current_user_id,$count);
		}else if ($olderthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE c.id < ? AND $searchqry");
			$stmt->bind_param("iiiiii",$olderthan_id,$current_user_id,$current_user_id,$current_user_id,$current_user_id,$count);
		}else{
			//echo "$selectqry WHERE $searchqry";
			$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
			$stmt->bind_param("iiiii",$current_user_id,$current_user_id,$current_user_id,$current_user_id,$count);
		}
		
        if ($stmt->execute()){
			if ($item_id == NULL){
           		$stmt->bind_result($username, $userimage, $locality, $user_id, $name, $conversation_id);
			}else{
           		$stmt->bind_result($username, $userimage, $locality, $user_id, $name, $conversation_id, $item_id, $title);
			}
			$response = array();
			$conv = array();
            while ($stmt->fetch()){
           		$res = array();
				$res["username"] = $username;
				$res["userimage"] = $userimage;
				$res["locality"] = $locality;
				$res["user_id"] = $user_id;
				$res["name"] = $name;
				$res["conversation_id"] = $conversation_id;
				if ($item_id != NULL){
					$res["item_id"] = $item_id;
					$res["title"] = $title;
				}
				array_push($conv, $res);
				$response["error"] = 0;
				$response["conversations"] = $conv;
			}
			if (count($response) == 0){
				$response["error"] = 0;
				$response["message"] = "No conversations found";
			}
            $stmt->close();
            return $response;
        } else {
            return NULL;
        }
    }
	/**
     * Fetching single item
     * @param String $item_id id of the item
     */
    public function conversation_get($current_user_id, $conversation_id,$newerthan_id,$olderthan_id,$count) {
		
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		
		$are_following = 0;
		$response = array();
		$stmt = $this->conn->prepare("SELECT t.username, t.image, t.locality, t.id, t.name, c.id FROM conversations c, users t WHERE c.id = ? AND ((c.partya = t.id AND c.partya <> ?) OR (c.partyb = t.id AND c.partyb <> ?))");
		$stmt->bind_param("iii", $conversation_id,$current_user_id, $current_user_id);
		if ($stmt->execute()) {
			$stmt->bind_result($username, $image, $locality, $user_id, $name, $cid);
			$stmt->fetch();
			$stmt->close();
			$response["username"] = $username;
			$response["image"] = $image;
			$response["locality"] = $locality;
			$response["user_id"] = $user_id;
			$response["name"] = $name;
			$response["cid"] = $cid;
			$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name, m.message, m.type, m.id, m.result FROM messages m, users t";
			$searchqry = "m.conversation_id = ? AND m.user_id = t.id ORDER BY m.id DESC LIMIT 0, ?";
			if ($newerthan_id != 0){
				$stmt = $this->conn->prepare("$selectqry WHERE m.id > ? AND $searchqry");
				$stmt->bind_param("iii",$newerthan_id,$conversation_id,$count);
			}else if ($olderthan_id != 0){
				$stmt = $this->conn->prepare("$selectqry WHERE m.id < ? AND $searchqry");
				$stmt->bind_param("iii",$olderthan_id,$conversation_id,$count);
			}else{
				//echo "$selectqry WHERE $searchqry";
				$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
				$stmt->bind_param("ii",$conversation_id,$count);
			}
			if ($stmt->execute()) {
				$stmt->bind_result($username, $image, $locality, $user_id, $name, $message, $type, $message_id, $result);
				$response = array();
				$msg = array();
				while ($stmt->fetch()){
					$itemlist = array();
					$itemlist["username"] = $username;
					$itemlist["userimage"] = $userimage;
					$itemlist["locality"] = $locality;
					$itemlist["name"] = $name;
					$itemlist["message"] = $message;
					$itemlist["result"] = $result;
					$itemlist["type"] = $type;
					$itemlist["id"] = $message_id;
					$itemlist["user_id"] = $user_id;
					array_push($msg, $itemlist);
				}
				$stmt->close();
				$response["messages"] = $msg;
				$response["error"] = 0;
			}else{
				$response["error"] = 1;
				$response["message"] = "Error finding messages";
			}
			return $response;
		}
		$response["error"] = 1;
		$response["message"] = "Error finding conversation";
		return $response;
    }
	
	public function conversation_post($current_user_id,$conversation_id,$item_id,$user_id,$message,$type) {
		$response = array();
		//$stmt = $this->conn->prepare("SELECT t.partya, t.partyb FROM conversations t, messages m WHERE m.id = ? AND m.conversation_id = t.id");
		//$stmt->bind_param("i",$conversation_id);
		if ($conversation_id == NULL){
			// Create a new conversation
			if ($item_id != NULL){
				// Check item_id
				$stmt = $this->conn->prepare("SELECT t.id, t.partya, t.partyb FROM conversations t WHERE t.item_id = ? AND (t.partya = ? OR t.partyb = ?)");
				$stmt->bind_param("iii", $item_id,$current_user_id,$current_user_id);
				if ($stmt->execute()){
					$stmt->bind_result($conversation_id, $partya, $partyb);
					$stmt->fetch();
					$stmt->close();
				}
				if ($conversation_id == 0){
					$stmt = $this->conn->prepare("SELECT t.user_id FROM items t WHERE t.id = ?");
					$stmt->bind_param("i", $item_id);
					if ($stmt->execute()){
						$stmt->bind_result($user_id);
						$stmt->fetch();
						$stmt->close();
					}
				}
			}else if ($user_id != NULL){
				// Check just user_id
				$stmt = $this->conn->prepare("SELECT t.id FROM conversations t WHERE item_id IS NULL AND ((t.partya = ? AND t.partyb = ?) OR (t.partya = ? AND t.partyb = ?))");
				$stmt->bind_param("iiii", $current_user_id,$user_id,$user_id,$current_user_id);
				if ($stmt->execute()){
					$stmt->bind_result($conversation_id);
					$stmt->fetch();
					$stmt->close();
				}
			}
			
			
			if ($conversation_id == 0){
				$stmt = $this->conn->prepare("INSERT INTO conversations(partya, partyb, item_id) values(?, ?, ?)");
				$stmt->bind_param("iii", $current_user_id, $user_id, $item_id);

				// Check for successful insertion
				if ($stmt->execute()) {
					$conversation_id = $stmt->insert_id;
					$stmt->close();
				}
			}
		}
		$stmt = $this->conn->prepare("SELECT t.id FROM conversations t WHERE t.id = ? AND (t.partya = ? OR t.partyb = ?)");
			$stmt->bind_param("iii", $conversation_id,$current_user_id,$current_user_id);
		
		if ($result = $stmt->execute()){
			$stmt->bind_result($conversation_id);
			$stmt->fetch();
			$stmt->close();
		}else{
			$response["error"] = 1;
			$response["message"] = "Could not access conversation";
			return $response;
		}
		if ($conversation_id == NULL || $conversation_id == 0){
			$response["error"] = 1;
			$response["message"] = "Could not access conversation.";
			return $response;
		}
		$stmt = $this->conn->prepare("INSERT INTO messages(conversation_id, message, type, user_id) values(?, ?, ?, ?)");
		$stmt->bind_param("isii", $conversation_id, $message, $type, $current_user_id);

		// Check for successful insertion
		if ($stmt->execute()) {
			$message_id = $stmt->insert_id;
			$stmt->close();
			$response["error"] = 0;
			$response["message"] = "Message Sent";
			$response["added"] = array();
			$response["added"]["user_id"] = $current_user_id;
			$response["added"]["id"] = $message_id;
			$response["added"]["message"] = $message;
			$response["added"]["type"] = $type;
			return $response;
		}
    }
	
	public function conversation_put_message($current_user_id,$conversation_id,$message_id,$action) {
		$response = array();
		$stmt = $this->conn->prepare("SELECT t.id FROM conversations t WHERE t.id = ? AND (t.partya = ? OR t.partyb = ?)");
		$stmt->bind_param("iii", $conversation_id,$current_user_id,$current_user_id);
		
		if ($result = $stmt->execute()){
			$stmt->bind_result($conversation_id);
			$stmt->fetch();
			$stmt->close();
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
		$stmt = $this->conn->prepare("UPDATE messages m set m.result = ? WHERE m.id = ? AND m.conversation_id = ?");
		$stmt->bind_param("iii", $action, $message_id, $conversation_id);

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
