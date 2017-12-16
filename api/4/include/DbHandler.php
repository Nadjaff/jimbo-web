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
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $username, $email, $password, $lat, $long, $phone, $fbid, $imagedata) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isEmailExists($email)) {
			if (!$this->isUserExists($username)) {
				// Generating password hash
				$password_hash = PassHash::hash($password);
	
				// Generating API key
				$api_key = $this->generateApiKey();
	
				// insert query
				//$stmt = $this->conn->prepare("INSERT INTO users(name, username, email, password_hash, phone, api_key, status) values(?, ?, ?, ?, ?, ?, 1)");
				//$stmt->bind_param("ssssss", $name, $username, $email, $password_hash, $phone, $api_key);
				$stmt = $this->conn->prepare("INSERT INTO users(name, username, email, password_hash, phone, fbid, image, api_key, status) values(?, ?, ?, ?, ?, ?, ?, ?, 1)");
				$stmt->bind_param("ssssssss", $name, $username, $email, $password_hash, $phone, $fbid, $imagedata, $api_key);
	
				$result = $stmt->execute();
	
				$stmt->close();
	
				// Check for successful insertion
				if ($result) {
					// User successfully inserted
					return 0;
				} else {
					// Failed to create user
					return USER_CREATE_FAILED;
				}
			}else{
				return USERNAME_IN_USE;
			}
        } else {
            // User with same email already existed in the db
            return EMAIL_IN_USE;
        }

        return $response;
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ? OR username = ?");

        $stmt->bind_param("ss", $email, $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();
		
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
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
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserDetails($email) {
        $stmt = $this->conn->prepare("SELECT name, email, username, api_key, status, created_at FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        if ($stmt->execute()) {
			// $user = $stmt->get_result()->fetch_assoc();
			$stmt->bind_result($name, $email, $username, $api_key, $status, $created_at);
			$stmt->fetch();
			$user = array();
			$user["name"] = $name;
			$user["email"] = $email;
			$user["username"] = $username;
			$user["api_key"] = $api_key;
			$user["status"] = $status;
			$user["created_at"] = $created_at;
			$stmt->close();
			return $user;
		}else{
			return NULL;
		}
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
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
    public function createItem($user_id, $title,$price,$description,$quantity,$image1,$status,$location_id) {
		if ($status == 1){
			$status = 0;
		}
		// Need to put a select statement in here to check that the user actually owns this item!
		
		$stmt = $this->conn->prepare("INSERT INTO items(user_id,title,price,description,quantity,status,image,location_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("isisiisi", $user_id, $title, $price, $description,$quantity,$status,$image1,$location_id);
		
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
    public function getItem($item_id, $user_id) {
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
    public function getAllItems($user_id,$since_id,$max_id,$count) {
		
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
    public function updateItem($user_id, $item_id, $item, $title, $price, $description, $quantity, $image1, $status) {
        $stmt = $this->conn->prepare("UPDATE items t set t.item = ?, t.status = ? WHERE t.id = ? AND t.id = t.item_id AND t.user_id = ?");
        $stmt->bind_param("siii", $item, $status, $item_id, $user_id);
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
    public function publishItem($user_id, $item_id) {
        $stmt = $this->conn->prepare("UPDATE items t set t.published_at = ?, t.status = ? WHERE t.id = ? AND t.user_id = ?");
		$status = 1;
		$date = new DateTime();
		$published_at = $date->getTimestamp();
        $stmt->bind_param("siii", $published_at, $status, $item_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }
    /**
     * Deleting a item
     * @param String $item_id id of the item to delete
     */
    public function deleteItem($user_id, $item_id) {
        $stmt = $this->conn->prepare("DELETE t FROM items t WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /* ------------- `user_items` table method ------------------ */

    /**
     * Function to assign a item to user
     * @param String $user_id id of the user
     * @param String $item_id id of the item
     */
    public function createUserItem($user_id, $item_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_items(user_id, item_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $item_id);
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
    public function starItem($user_id, $item_id, $val) {
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
     * @param String $user_id user id to whom item belongs to
     * @param String $item item text
     */
    public function createImage($user_id, $image) {
		$stmt = $this->conn->prepare("INSERT INTO images(user_id,image) VALUES(?, ?)");
		$stmt->bind_param("is", $user_id, $image);
		
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
	public function getImage($user_id,$image_id) {
        $stmt = $this->conn->prepare("SELECT image, i, item_id FROM images WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $user_id);
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
	public function associateImageWithItem($user_id,$item_id,$image_id,$i){
		
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
		$stmt->bind_param("iiii", $item_id, $i, $image_id, $user_id);
		$stmt->execute();
		$num_affected_rows = $stmt->affected_rows;
		$stmt->close();
		
		
		return $num_affected_rows > 0;
	}

    /**
     * Deleting a item
     * @param String $item_id id of the item to delete
     */
    public function deleteImage($user_id, $item_id) {
        $stmt = $this->conn->prepare("DELETE t FROM images t WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("ii", $item_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

}


?>
