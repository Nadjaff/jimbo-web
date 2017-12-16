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
        require_once dirname(__FILE__) . '/../include/DbConnect.php';
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
     * Fetching all user items
     * @param String $current_user_id id of the user
     */
    public function users_get_all($current_user_id,$q,$newerthan_id,$olderthan_id,$count) {
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($q == NULL){
			$q = "";
		}
		
		$selectqry = "SELECT t.username, t.image, t.locality, t.id, t.name FROM users t";
		$searchqry = "(t.username LIKE CONCAT(?, '%') OR t.name LIKE CONCAT('', ?, '%') OR t.name LIKE CONCAT('% ', ?, '%')) ORDER BY id DESC LIMIT 0, ?";
		
		if ($newerthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE t.id > ? AND $searchqry");
			$stmt->bind_param("isssi",$newerthan_id,$q,$q,$q,$count);
		}else if ($olderthan_id != 0){
			$stmt = $this->conn->prepare("$selectqry WHERE t.id < ? AND $searchqry");
			$stmt->bind_param("isssi",$olderthan_id,$q,$q,$q,$count);
		}else{
			$stmt = $this->conn->prepare("$selectqry WHERE $searchqry");
			$stmt->bind_param("sssi",$q,$q,$q,$count);
		}
		
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $id, $name);
			$response = array();
            while ($stmt->fetch()){
           		$res = array();
				$res["username"] = $username;
				$res["userimage"] = $userimage;
				$res["locality"] = $locality;
				$res["id"] = $id;
				$res["name"] = $name;
				array_push($response, $res);
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
    public function user_get($current_user_id, $user_id) {
		$are_following = 0;
		$stmt = $this->conn->prepare("SELECT t.user_id FROM following t WHERE t.user_id = ? AND t.following_id = ?");
		$stmt->bind_param("ii", $current_user_id, $user_id);
		if ($stmt->execute()) {
			$stmt->store_result();
			if ($stmt->num_rows > 0) {
				$are_following = 1;
			}
			$stmt->close();
			
			$stmt = $this->conn->prepare("SELECT t.id, t.username, t.name, t.stars, t.sales, t.bio, t.no_products, t.no_followers, t.no_following, t.locality, t.image, t.phone, t.address, t.gender, t.email from users t WHERE t.id = ?");
			$stmt->bind_param("i", $user_id);
			if ($stmt->execute()) {
				$res = array();
				$stmt->bind_result($id, $username, $name, $stars, $sales, $bio, $no_products, $no_followers, $no_following, $locality, $image, $phone, $address, $gender, $email);
				// TODO
				// $item = $stmt->get_result()->fetch_assoc();
				$stmt->fetch();
				$res["id"] = $id;
				$res["username"] = $username;
				$res["name"] = $name;
				$res["stars"] = $stars;
				$res["sales"] = $sales;
				$res["bio"] = $bio;
				$res["no_products"] = $no_products;
				$res["no_followers"] = $no_followers;
				$res["no_following"] = $no_following;
				$res["locality"] = $locality;
				$res["are_following"] = $are_following;
				$res["image"] = $image;
				$stmt->close();
				$res["items"] = array();
				
				if ($user_id == $current_user_id){
					$res["phone"] = $phone;
					$res["email"] = $email;
					$res["address"] = $address;
					$res["gender"] = $gender;
				}
				
				$stmt = $this->conn->prepare("SELECT t.price, t.image, t.id from items t WHERE t.user_id = ?");
				$stmt->bind_param("i", $user_id);
				if ($stmt->execute()) {
					$stmt->bind_result($price, $image, $item_id);
					$response = array();
					while ($stmt->fetch()){
						$itemlist = array();
						$itemlist["username"] = $username;
						$itemlist["userimage"] = $userimage;
						$itemlist["locality"] = $locality;
						$itemlist["id"] = $id;
						$itemlist["name"] = $name;
						array_push($res["items"], $itemlist);
					}
					$stmt->close();
					return $res;
				}
				return NULL;
			} else {
				return NULL;
			}
		}
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function users_post($name, $username, $email, $password, $lat, $long, $phone, $fbid, $imagedata) {
        require_once '../include/PassHash.php';
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
    public function createItem($current_user_id, $title,$price,$description,$quantity,$image1,$status,$location_id) {
		if ($status == 1){
			$status = 0;
		}
		// Need to put a select statement in here to check that the user actually owns this item!
		
		$stmt = $this->conn->prepare("INSERT INTO items(user_id,title,price,description,quantity,status,image,location_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isisiisi", $current_user_id, $title, $price, $description,$quantity,$status,$image1,$location_id);
		
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
    public function getItem($item_id, $current_user_id) {
        $stmt = $this->conn->prepare("SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at from items t, users ut, locations lt WHERE t.id = ? AND ut.id = t.user_id AND lt.id = t.location_id");
        $stmt->bind_param("i", $item_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($user_id, $total_images, $created_at, $id, $title, $price, $description, $quantity, $status, $published_at);
            // TODO
            // $item = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
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
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all user items
     * @param String $user_id id of the user
     */
    public function getAllItems($current_user_id,$q,$since_id,$max_id,$count) {
		if ($count == 0){
			$count = 20;
		}
		if ($count > 100){
			$count = 100;
		}
		if ($since_id != 0){
			$stmt = $this->conn->prepare("SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, t.comments, t.stars from items t, users ut, locations lt WHERE t.id > ? AND ut.id = t.user_id AND lt.id = t.location_id ORDER BY id DESC LIMIT 0, ?");
			$stmt->bind_param("ii",$since_id,$count);
		}else if ($max_id != 0){
			$stmt = $this->conn->prepare("SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, t.comments, t.stars from items t, users ut, locations lt  WHERE t.id <= ? AND ut.id = t.user_id AND lt.id = t.location_id ORDER BY id DESC LIMIT 0, ?");
			$stmt->bind_param("ii",$max_id,$count);
		}else{
			//$stmt = $this->conn->prepare("SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, t.comments, t.stars from items t, users ut, locations lt WHERE ut.id = t.user_id AND lt.id = t.location_id ORDER BY id DESC LIMIT 0, ?");
			$stmt = $this->conn->prepare("SELECT ut.username, ut.image, lt.locality, t.user_id, t.totalimages, t.created_at, t.id, t.title, t.price, t.description, t.quantity, t.status, t.created_at, t.image, t.comments, t.stars from items t, users ut, locations lt WHERE ut.id = t.user_id AND lt.id = t.location_id ORDER BY id DESC LIMIT 0, ?");
			$stmt->bind_param("i",$count);
		}
			
        if ($stmt->execute()){
            $stmt->bind_result($username, $userimage, $locality, $user_id, $total_images, $created_at, $id, $title, $price, $description, $quantity, $status, $published_at, $image,$comments,$stars);
			$response = array();
            while ($stmt->fetch()){
				if ($since_id != 0 && $since_id >= $id){
					break;
				}
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
				$res["image"] = $image;
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
    public function updateItem($current_user_id, $item_id, $item, $title, $price, $description, $quantity, $image1, $status) {
        $stmt = $this->conn->prepare("UPDATE items t set t.item = ?, t.status = ? WHERE t.id = ? AND t.id = t.item_id AND t.user_id = ?");
        $stmt->bind_param("siii", $item, $status, $item_id, $current_user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
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
	/**
     * Starring item
     * @param String $item_id id of the item
     * @param String $item item text
     * @param String $status item status
     */
    public function starItem($current_user_id, $item_id, $val) {
        $stmt = $this->conn->prepare("UPDATE items t set t.stars = t.stars + 1 WHERE t.id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows != 1;
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
	public function getImages($item_id) {
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
	public function getImage($current_user_id,$image_id) {
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

}


?>
