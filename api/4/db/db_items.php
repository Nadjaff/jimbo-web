<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */

class DbItems extends DbBase {

	/* ------------- `items` table method ------------------ */
	
	/**
	 * Creating new item
	 * @param String $user_id user id to whom item belongs to
	 * @param String $item item text
	 */
	 
	 
	 public function items_post_from_url($current_user_id, $response,$title,$price,$description,$quantity,$images,$status,$location_id, $test=null) {
	 	// creating new item
		$db = new DbImages();
	 	// include '../../config.inc';
	 	/*include_once dirname(__DIR__) . '/helper/config_s3.php';
	 	ini_set('max_execution_time', 300);
	 	$s3 = new Config_s3;*/
	 	$message = '';
	 	error_reporting(E_ALL);
	 	/*$uploads_root = $uploadbase.'items/';*/

	 	$db->s3_setup();

	 	$uploads_api_3_tmp = $db->getApi3Base(); /*$s3::$upload_api_3_base;*/

	 	$filename = 'u' . uniqid() . '.jpg';

	 	$images = file_get_contents($images);

	 	$image = imagecreatefromstring($images);
		
	 	$cropped = move_crop_and_return_image($image, $uploads_api_3_tmp . $filename, 610, 610, $uploads_api_3_tmp);

	 	$file = $db->__fetchFile($filename, $uploads_api_3_tmp);

		// $file = $s3::__makeTmpFile($image, $filename, 'tmp/tmpfile/');

	 	// Setup the needed variables
	 	$db->_setPutObjectVariables($db->_getBucketItems() . $filename, $file);

		// Put the file to s3
		$s3_result = $db->_putObject(true);
		
	 	/*$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $images); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // good edit, thanks!
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); // also, this seems wise considering output is image.
		$data = curl_exec($ch);
		curl_close($ch);*/
	 	
		// $image = imagecreatefromstring($data);
		
		// move_and_crop_image($file/*$image*/, $uploads_root . $filename,610,610);
		//move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
        global $r;
		if($s3_result['status']) {
			$filename = $s3_result['result'];
			// // creating new item
			// $db = new DbImages();
			$image_id = $db->createImage($current_user_id,$filename);
			if ($image_id != NULL) {
				$r["error"] = 0;
				$r["image_id"] = $image_id;
				$r["url"] = $filename;
				$images = $filename;
				$this->items_post($current_user_id, $title,$price,"AUD",$description,$quantity,$images,$status,$location_id, NULL, 0, $test);
               return $response->withStatus(201);
			   //echoResponse(201, $response);
			} else {
				$r["error"] = 1;
				$r["title"] = "Error";
				$r["message"] = "Failed to create image. Please try again";
				//echoResponse(200, $response);
				return $response->withStatus(200);
			}
		} else {
			$r["error"] = 1;
			$r["title"] = "Error";
			$r["message"] = "Failed to upload image";
			return $response->withStatus(200);
		//	echoResponse(200, $response);
		}
	 }
	public function items_post($current_user_id, $title,$price,$currency,$description,$quantity,$images,$status,$location_id, $location, $negotiable, $test=null) {
		$price = floatval($price)*1000000;
		if ($status == 1){
			$status = 0;
		}
			if ($location_id == NULL){
				//$location["locality"] = "Bossley Park";
				//echo "crazainess" . $location["locality"]. $location["admin"]. $location["country"]. $location["latitude"]. $location["longitude"];
				if ($location["locality"] != NULL){
					$stmt = $this->conn->prepare("INSERT INTO locations(user_id,locality, admin, country, latitude, longitude,created_at,updated_at) VALUES(:user_id, :locality, :admin, :country, :latitude, :longitude,:created_at,:updated_at)");
					$stmt->bindParam(":user_id", $current_user_id);
					$stmt->bindParam(":locality", $location["locality"]);
					$stmt->bindParam(":admin", $location["admin"]);
					$stmt->bindParam(":country", $location["country"]);
					$stmt->bindParam(":latitude", $location["latitude"]);
					$stmt->bindParam(":longitude", $location["longitude"]);
					 $stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
					$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
					
					//echo "made";
					if ($stmt->execute()){
						$location_id = $this->conn->lastInsertId();
						//echo $location_id;
					}else{
    print_r($stmt->errorInfo());
						//echo "falied";
						$location_id = 0;
						//return NULL;
					}
				}else{
						$location_id = 0;
					//return NULL;
				}
			}
			
			
		// Need to put a select statement in here to check that the user actually owns these images!
		
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$images = implode(",",array_filter(explode(",",$images)));
			ini_set('display_errors',1);
error_reporting(E_ALL);
			$stmt = $this->conn->prepare("INSERT INTO items(user_id,title,price,currency,description,quantity,status,image,location_id, negotiable, test,created_at,updated_at) VALUES(:user_id, :title, :price, :currency, :description, :quantity, :status, :images, :location_id, :negotiable, :test,:created_at,:updated_at)");
	        $curr = date('Y-m-d H:i:s');	
		    $stmt->bindParam(":created_at",  $curr );
			$stmt->bindParam(":updated_at",  $curr );
			$stmt->bindParam(":user_id", $current_user_id);
			$stmt->bindParam(":title", $title);
			$stmt->bindParam(":price", $price);
			$stmt->bindParam(":currency", $currency);
			$stmt->bindParam(":description", $description);
			$stmt->bindParam(":quantity", $quantity);
			$stmt->bindParam(":status", $status);
			$stmt->bindParam(":images", $images);
			$stmt->bindParam(":negotiable", $negotiable);
			$stmt->bindParam(":location_id", $location_id);
			if ($test == NULL){
				$test = 0;
			}/* else {
				$test = 0;
			}*/
			$stmt->bindParam(":test", $test);
			
			
			if ($stmt->execute()){
				$item_id = $this->conn->lastInsertId();
				$this->items_count($current_user_id,1);
				$nots = new DbNotifications();
				// item row created
				// now assign the item to user
				if (preg_match_all('!@(.+)(?:\s|$)!U', $title, $matches)){
					$usernames = $matches[1];
				}else{
					$usernames = array(); // empty list, no users matched
			}
					for ($i=0;$i<count($usernames);$i++){
						$nots->send_notification($usernames[$i],4,$item_id,$current_user_id,$item_id,$title);
					}
					
					
			if (preg_match_all('!@(.+)(?:\s|$)!U', $description, $matches)){
					$usernames = $matches[1];
				}else{
					$usernames = array(); // empty list, no users matched
			}
					for ($i=0;$i<count($usernames);$i++){
						$nots->send_notification($usernames[$i],4,$item_id,$current_user_id,$item_id,$description);
					}
					
				return $item_id;
			} else {
			print_r($this->conn->errorCode());
				// item failed to create
				return NULL;
			}
	}
	
	/**
	 * Fetching single item
	 * @param String $item_id id of the item
	 */
	public function item_get($current_user_id, $item_id) {
		$err = 0;
		$stmt = $this->conn->prepare("SELECT NOT ISNULL(youfollow.user_id) as youfollow, ut.username, ut.id, loc.locality, ut.image as userimage, t.user_id, t.created_at, t.id, t.title, " . $this->getpricet	. ", t.description, t.quantity, t.status, t.type, t.negotiable, t.created_at, t.image, COUNT(distinct c.id) AS num_comments, COUNT(distinct ul.id) AS liked, COUNT(distinct l.id) AS num_likes, COUNT(distinct s.id) AS num_shares from items t
		LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
		LEFT JOIN locations AS loc ON (loc.id = t.location_id)	
		LEFT JOIN likes AS l ON (l.item_id = t.id)
		LEFT JOIN shares AS s ON (s.item_id = t.id)
		LEFT JOIN comments AS c ON (c.item_id = t.id)
		LEFT JOIN users AS ut ON ut.id = t.user_id	
		LEFT JOIN following as youfollow ON youfollow.user_id = :uid
		" . $this->getpricetablest	. "
		WHERE t.id = :item_id");		
		// INNER JOIN locations AS lt ON lt.id = t.location_id
		$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		if (!$stmt->execute()){ 
			print_r($stmt->errorInfo());
			 $err = 2;
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		$comm = $this->item_get_comments($item_id,0,0,0);
		$user_id = $item["user_id"];
		$test = 0;		
		/*$item["variants"] = array();
		$stmt = $this->conn->prepare("SELECT c.id, c.name from criteria c WHERE c.item_id = :item_id");
		$stmt->bindParam(":item_id",$item_id);
		if (!$stmt->execute()) $err = max($err,3);
		for ($i=0;$ii = $stmt->fetch(PDO::FETCH_ASSOC); $i++){
			$item["variants"][$i] = $ii;
			$stmt = $this->conn->prepare("SELECT v.id, v.name from criteria c, variants v WHERE c.id = :cid");
				$stmt->bindParam(":cid",$ii["id"]);
				if (!$stmt->execute()) $err = max($err,4);
				for ($j=0;$jj = $stmt->fetch(PDO::FETCH_ASSOC); $j++){
					$stmt = $this->conn->prepare("SELECT vi.quantity, vi.price, vi.images from criteria c, variants v WHERE vi.item_id=:item_id AND c.id=:cid AND v.id = :vid");
					$stmt->bindParam(":item_id",$item_id);
					$stmt->bindParam(":cid",$ii["id"]);
					$stmt->bindParam(":vid",$jj["id"]);
					
				}
		}		
		if ($item["criteria"] != "1"){
			$criteria = explode(",",$item["criteria"]);
			for ($i=0;$i<count($criteria);$i++){				
				$item["variants"][$i] = $stmt->fetch(PDO::FETCH_ASSOC);
				for ($j=0;$j<count($criteria);$j++){
				$stmt = $this->conn->prepare("SELECT v.id, v.name from criteria c, variants v WHERE c.id = :cid");
				$stmt->bindParam(":cid",$criteria[$i]);
				if (!$stmt->execute()) $err = max($err,3);
				$item["variants"][$i]["variants"] = $stmt->fetchAll(PDO::FETCH_ASSOC);				
				$stmt = $this->conn->prepare("SELECT c.id, vi.id, vi.quantity, vi.price, vi.images from criteria c, variants v WHERE vi.item_id=:item_id");
				$stmt->bindParam(":item_id",$item_id);
				if (!$stmt->execute()) $err = max($err,4);
			}
		}*/
		// global $newerthan_id, $olderthan_id, $count;
		$newerthan_id = $olderthan_id = 0;
		$count = 1;
		$stmt = $this->limitQuery("SELECT t.image, " . $this->getpricet	. ", t.id, t.title,t.description, t.created_at from items t 	 Left Join users ut ON ut.id=t.user_id 
		" . $this->getpricetablest	. "WHERE t.id ### :limitid AND t.id <> :item_id AND t.test = :test AND t.status < 5 AND t.user_id = :id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id, $olderthan_id,$count);
		$stmt->bindParam(":id",$user_id);
		//$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		if ($test == NULL) $test = 0;
		$stmt->bindParam(":test",$test);
		if (!$stmt->execute()) $err = max($err,2);			
		$item["selleritems"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//print_r($stmt);
		if (isset($response['selleritems']) && count($response["selleritems"]) == 0 && $newerthan_id != 0){
			$stmt = $this->limitQuery("SELECT t.image, " . $this->getpricet	. ", t.id, t.title, t.created_at from items t 	
		" . $this->getpricetablest	. "WHERE t.id ### :limitid AND t.id <> :item_id AND t.test = :test AND t.status < 5 AND t.user_id = :id ORDER BY t.id DESC LIMIT 0, :count",0, $olderthan_id,$count);
			$stmt->bindParam(":id",$user_id);
		$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
			if ($test == NULL) $test = 0;
			$stmt->bindParam(":test",$test);
			if (!$stmt->execute()) $err = max($err,2);
			$item["selleritems"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		}
		$item["comments"] = $comm["comments"];
		$item["error"] = max($err,$comm["error"]);
		return $item;
	}
	
	
	
	public function item_get_likes($current_user_id, $item_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT NOT ISNULL(youfollow.following_id) as youfollow, t.username, t.image as userimage, t.locality, t.id FROM users t, likes f LEFT JOIN following as youfollow ON youfollow.user_id = :current_user_id AND youfollow.following_id = f.user_id WHERE f.id ### :limitid AND f.item_id = :item_id AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":current_user_id",$current_user_id);
		return $this->yExecute($stmt,"users");		
	}
	
	public function item_get_comments($item_id,$newerthan_id,$olderthan_id,$count){		
		$stmt = $this->limitQuery("SELECT * FROM (SELECT t.username, t.image as userimage, t.image, t.locality, t.id as user_id, t.name, f.comment, f.id, f.created_at FROM users t, comments f	WHERE f.id ### :limitid AND f.item_id = :item_id AND t.id = f.user_id ORDER BY f.id DESC LIMIT 0, :count) tmp ORDER BY tmp.id ASC",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->yExecute($stmt,"comments");
	}
	
	public function item_get_offers($current_user_id,$item_id,$newerthan_id,$olderthan_id,$count){		
		$stmt = $this->limitQuery("SELECT i.title, c.item_id, u.username as latest_message_from_username, u.username, u.image as userimage, u.locality, u.id as user_id, u.name, f.message as latest_message, f.id as latest_message_id, f.type as latest_message_type, " . $this->getpricet	. ", t.result, f.created_at, c.id as conversation_id, i.image as item_image FROM users u, messages f, conversations c, items i, offers t LEFT JOIN users AS ut ON ut.id = t.user_id  " . $this->getpricetablest	. " WHERE f.id ### :limitid AND c.item_id = :item_id AND f.conversation_id = c.id AND u.id = f.user_id AND f.user_id = :uid AND i.id = c.item_id AND f.type = 2 AND t.message_id = f.id ORDER BY f.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":uid",$current_user_id);		
		return $this->yExecute($stmt,"conversations");
	}
	
	public function item_get_reviews($item_id,$newerthan_id,$olderthan_id,$count){
		$stmt = $this->limitQuery("SELECT t.rated_user_id, u1.username as rated_username, u1.image as rated_userimage, t.rater_user_id, u2.username as rater_username, u2.image as rater_userimage, t.sale_id, t.rating, t.comment, t.type, t.created_at FROM reviews t, sales s, users u1, users u2 WHERE t.id ### :limitid AND t.rated_user_id = u1.id AND t.rater_user_id = u2.id AND s.item_id = :item_id AND t.sale_id = s.id ORDER BY t.id DESC LIMIT 0, :count",$newerthan_id,$olderthan_id,$count);
		$stmt->bindParam(":item_id",$item_id);
		return $this->yExecute($stmt,"reviews");
	}
	
	
	
	public function item_like($current_user_id, $item_id,$val){
		$stmt = $this->conn->prepare("SELECT COUNT(*) FROM likes t WHERE t.user_id = :user_id AND t.item_id = :item_id");
		$stmt->bindParam(":user_id",$current_user_id);	
		$stmt->bindParam(":item_id",$item_id);
		if (!$stmt->execute()) {
			return array("error" => 1);
		}
  		$num_rows = $stmt->fetchColumn();
			
		$stmt = NULL;
		if ($val == 1){
			if ($num_rows == 0){
				$stmt = $this->conn->prepare("INSERT INTO likes(user_id, item_id,created_at,updated_at) values(:user_id,:item_id,:created_at,:updated_at)");
				$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
			    $stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
			}
		}else{
			if ($num_rows == 1){
				$stmt = $this->conn->prepare("DELETE t from likes t WHERE t.user_id = :user_id AND t.item_id = :item_id");
			}else{
				// There is probably an error
			}
		}
		if ($stmt != NULL){
			$stmt->bindParam(":user_id",$current_user_id);
			$stmt->bindParam(":item_id",$item_id);
			if (!$stmt->execute()) {
				return array("error" => 2, "message" => "Unable to like at this time");
			}
		}
		
		if ($val == 1){
			$stmt = $this->conn->prepare("SELECT u.username FROM users u WHERE u.id = :current_user_id");
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->execute();
			if (!($poster = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "Error");
			}
			$stmt = $this->conn->prepare("SELECT i.title FROM items i WHERE i.id = :item_id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			if (!($item = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "Error");
			}	
			
			
			$stmt = $this->conn->prepare("SELECT u.username, u.id FROM users u, items i WHERE i.id = :item_id AND i.user_id = u.id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			include_once("db/db_notifications.php");
			$nots = new DbNotifications();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$nots->send_notification($owner["id"],1,$item_id,$current_user_id,$item_id,"");
					//$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " liked your item: " . $item["title"], "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}
			/*include_once("libs/GCM.php");
			$gcm = new GCM();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " liked your item: " . $item["title"], "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}else{
				return array("error" => 1, "message" => "Error");
			}*/
		}
			
		return array("error" => 0, "result"=>$val, "message" => "Like Successful");
	}
	
	
	public function item_comment($current_user_id, $item_id,$comment){
		$stmt = $this->conn->prepare("INSERT INTO comments(user_id, item_id, comment,created_at,updated_at) values(:user_id,:item_id,:comment,:created_at,:updated_at)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$comment);
		$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
		$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
		if ($stmt->execute()){
			$comment_id = $this->conn->lastInsertId();
			
			$stmt = $this->conn->prepare("SELECT u.username FROM users u WHERE u.id = :current_user_id");
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->execute();
			if (!($poster = $stmt->fetch(PDO::FETCH_ASSOC))){
				return array("error" => 1, "message" => "Error");
			}
			$stmt = $this->conn->prepare("SELECT u.username, u.id FROM users u, items i WHERE i.id = :item_id AND i.user_id = u.id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->execute();
			//newandroid include_once("libs/GCM.php");
			//$gcm = new GCM();
			include_once("db/db_notifications.php");
			$nots = new DbNotifications();
			if (($owner = $stmt->fetch(PDO::FETCH_ASSOC))){
				if ($owner["id"] != $current_user_id){
					$nots->send_notification($owner["id"],2,$item_id,$current_user_id,$item_id,$comment);
					//$gcm->send_notification($owner["id"],"Jimbo",$poster["username"] . " said: " . $comment, "items/" . $item_id);
					//echo "owner" . $owner["id"] . "owner";
				}
			}
			
			
			/*$stmt = $this->conn->prepare("SELECT distinct(cp.user_id), u.username FROM comments cp, users u WHERE u.id = cp.user_id AND cp.user_id <> :current_user_id AND cp.user_id <> :owner_id AND cp.item_id = :item_id");
			$stmt->bindParam(":item_id",$item_id);
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->bindParam(":owner_id",$owner["id"]);
			$stmt->execute();
			//include_once("libs/GCM.php");
			//$gcm = new GCM();
			while (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
				$nots->send_notification($conv["user_id"],2,$item_id,$current_user_id,$comment);
				//$gcm->send_notification($conv["user_id"],"Jimbo",$poster["username"] . " said: " . $comment, "items/" . $item_id);
				//echo "sending" . $conv["user_id"] . "sending";
			}*/
			
			if (preg_match_all('!@(.+)(?:\s|$)!U', $comment, $matches)){
				$usernames = $matches[1];
			}else{
				$usernames = array(); // empty list, no users matched
		}
				for ($i=0;$i<count($usernames);$i++){
					$nots->send_notification($usernames[$i],4,$item_id,$current_user_id,$item_id,$comment);
				}
				
			return array("error" => 0, "id" => $comment_id, "message" => "Comment Successful");
		}else{
			return array("error" => 1, "message" => "Unable to post comment");
		}
	}
	
	public function item_report($current_user_id, $item_id,$comment){
		$stmt = $this->conn->prepare("INSERT INTO reports(reporter_id, user_id, item_id, comment,created_at,updated_at) values(:reporter_id, :user_id,:item_id,:comment,:created_at,:updated_at)");
		$reported_user = 0;
		$stmt->bindParam(":reporter_id",$current_user_id);
		$stmt->bindParam(":user_id",$reported_user);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$comment);
		$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
		$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));		
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Item Reported");
		}else{
			return array("error" => 1, "message" => "Unable to report item - please try again later");
		}
	}
	public function item_following($current_user_id, $q){
		$stmt = $this->conn->prepare("INSERT IGNORE INTO following_searches(user_id, q,created_at,updated_at) values(:user_id,:q,:created_at,:updated_at)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":q",$q);	
		$dt=  date('Y-m-d H:i:s');
		$stmt->bindParam(":created_at", $dt);
		$stmt->bindParam(":updated_at", $dt);
	   if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Search followed");
		}else{
			return array("error" => 1, "message" => "Unable to follow search - please try again later");
		}
	}
	public function item_wanted($current_user_id, $q, $price, $currency, $comment){
		$stmt = $this->conn->prepare("INSERT INTO wanted(user_id, q, price, currency, comment,created_at,updated_at) values(:user_id,:q,:price,:currency,:comment,:created_at,:updated_at)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":q",$q);
		$stmt->bindParam(":price",$price);
		$stmt->bindParam(":currency",$currency);
		$stmt->bindParam(":comment",$comment);
		$stmt->bindParam(":created_at", date('Y-m-d H:i:s'));
		$stmt->bindParam(":updated_at", date('Y-m-d H:i:s'));
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "You'll be notified");
		}else{
			print_r( $stmt->errorInfo());
			return array("error" => 1, "message" => "Unable to subscribe to notifications for search - please try again later");
		}
	}
	public function item_offer($current_user_id, $item_id,$offer){
		$stmt = $this->conn->prepare("INSERT INTO messages(user_id, item_id, comment) values(:user_id,:item_id,:comment)");
		$stmt->bindParam(":user_id",$current_user_id);
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":comment",$offer);
		if ($stmt->execute()){
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Offer Posted Successfully");
		}else{
			return array("error" => 1, "message" => "Unable to post offer");
		}
	}
	
	public function item_put_offer($current_user_id, $offer_id,$result, $price, $currency, $message){
		$startresult = 0;
					if ($message == NULL) $message = "";
		
		
		$stmt = $this->conn->prepare("SELECT c.item_id, m.conversation_id, i.user_id as owner_id, cp2.user_id as otheruser_id, m.message from messages m, conversation_participants cp, conversation_participants cp2, conversations c, items i WHERE m.id = :mid AND m.conversation_id = cp.conversation_id AND m.conversation_id = cp2.conversation_id AND cp.user_id = :current_user_id AND cp2.user_id <> :current_user_id AND c.id = m.conversation_id AND i.id = c.item_id AND i.negotiable = 1");
		$stmt->bindParam(":mid",$offer_id);
		$stmt->bindParam(":current_user_id",$current_user_id);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			if ($result != 4){
			$stmt = $this->conn->prepare("UPDATE offers t, messages m SET t.result = :result WHERE m.id = t.message_id AND t.message_id = :offer_id AND m.user_id <> :current_user_id AND t.result = 0");
			}else{
			$stmt = $this->conn->prepare("UPDATE offers t, messages m SET t.result = :result WHERE m.id = t.message_id AND t.message_id = :offer_id AND m.user_id = :current_user_id AND t.result = 0");
			}
				
			$stmt->bindParam(":current_user_id",$current_user_id);
			$stmt->bindParam(":offer_id",$offer_id);
			$stmt->bindParam(":result",$result);
			if ($stmt->execute()){	
				if ($result == 3){
					// time to make an offer
					$type = 2;
					$stmt = $this->conn->prepare("INSERT INTO messages(user_id, conversation_id, message, type) values(:user_id,:conversation_id,:message, :type)");
					$stmt->bindParam(":user_id",$current_user_id);
					$stmt->bindParam(":conversation_id",$res["conversation_id"]);
					$stmt->bindParam(":message",$message);
					$stmt->bindParam(":type",$type);
					if ($stmt->execute()){
						$message_id = $this->conn->lastInsertId();
						$stmt = $this->conn->prepare("INSERT INTO offers(message_id, price, currency) values(:message_id, :price, :currency)");
						$stmt->bindParam(":message_id",$message_id);
						$stmt->bindParam(":price",$price);
						$stmt->bindParam(":currency",$currency);
						if ($stmt->execute()){
							return array("error" => 0, "id" => $message_id, "message" => "Offer Posted Successfully");
						}else{
							return array("error" => 4, "message" => "Unable to counter offer");
						}
					}else{
						return array("error" => 1, "message" => "Unable to counter offer");
					}
				}
				$conversation_id = $res["conversation_id"];
				$stmt = $this->conn->prepare("UPDATE conversation_participants cp set cp.unread = cp.unread + 1 WHERE cp.user_id <> :current_user_id AND cp.conversation_id = :conversation_id");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":current_user_id",$current_user_id);
				$stmt->execute();
				
				$stmt = $this->conn->prepare("SELECT m.conversation_id, m.user_id, u.username, u.image as userimage, m.id, m.message, m.offer, m.type, m.result, m.created_at FROM messages m, users u WHERE m.id = :mid AND u.id = m.user_id");
				$stmt->bindParam(":mid",$offer_id);
				$stmt->execute();		
				$msg = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$stmt = $this->conn->prepare("SELECT u.image as userimage, cp.user_id, u.username FROM conversation_participants cp, users u WHERE cp.user_id <> :current_user_id AND cp.conversation_id = :conversation_id AND u.id = :current_user_id");
				$stmt->bindParam(":conversation_id",$conversation_id);
				$stmt->bindParam(":current_user_id",$current_user_id);
				$stmt->execute();		
				if ($result == 1 || $result == 2 || $result == 4){
					$typetext = "";
					if ($result == 1){
						$typetext = " accepted ";
					}else if ($result == 2){
						$typetext = " rejected ";
					}else if ($result == 4){
						$typetext = " cancelled ";
					}
					
					include_once("libs/GCM.php");
					$gcm = new GCM();
					while (($conv = $stmt->fetch(PDO::FETCH_ASSOC))){
						$gcm->send_notification($conv["user_id"],$conv["username"], $conv["username"] . $typetext . "your offer of " . ((float)($msg["offer"]) / 1000000), "messages/" . $conversation_id,$msg );
					}
				}
				
				if ($result == 1){
					// Mark item as sold
						if ($res["owner_id"] != $current_user_id){
							$buyer_id = $current_user_id;
						}else if($res["otheruser_id"] != $res["owner_id"]){
							$buyer_id = $res["otheruser_id"];
						}
					return $this->item_sales_post($buyer_id,$res["item_id"],$res["price"],$res["message"],1,"");
					// Send sale notification to buyer and seller
				}
				return array("error" => 0, "message" => "Offer Response Successful");
			}else{
				return array("error" => 1, "message" => "Unable to respond to offer");
			}
				return array("error" => 2, "message" => "Unable to respond to offer");
		}
				return array("error" => 3, "message" => "Unable to respond to offer");
	}
	
	public function item_sales_post($current_user_id, $item_id,$price, $currency, $quantity, $comment){
		$stmt = $this->conn->prepare("SELECT i.user_id, i.price, i.num_sales, i.quantity, i.currency from items i WHERE i.id = :item_id");
		$stmt->bindParam(":item_id", $item_id);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res == NULL) $res = array("token" => NULL);
		
		if ($price == NULL){
			$price = $res["price"];
		}
		if ($currency == NULL){
			$currency = $res["currency"];
		}
		if ($quantity == NULL) $quantity = 1;
		if ($comment == NULL) $comment = "";
		$remaining = $res["quantity"] - $res["num_sales"] - $quantity;
		if ($res["num_sales"] >= $res["quantity"]){
			return array("error" => 2, "message" => "This item is currently sold out");
		}
		if ($res["user_id"] == $current_user_id){
			// just marking as sold
			$stmt = $this->conn->prepare("UPDATE items t set t.num_sales = t.num_sales + 1 WHERE t.id = :item_id");
			$stmt->bindParam(":item_id", $item_id);
			$stmt->execute();
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Item Successfully Marked as Sold");
		}
			
		$stmt = $this->conn->prepare("INSERT INTO sales(item_id, seller_id, buyer_id, quantity, purchaseprice, 	purchasecurrency, comment) values(:item_id, :seller_id,:buyer_id,:quantity, :price, :currency, :comment)");
		$stmt->bindParam(":buyer_id",$current_user_id);
		$stmt->bindParam(":seller_id",$res["user_id"]); // from item
		$stmt->bindParam(":item_id",$item_id);
		$stmt->bindParam(":price",$price);
		$stmt->bindParam(":currency",$currency);
		$stmt->bindParam(":quantity",$quantity);
		$stmt->bindParam(":comment",$comment);	
        //print_r($stmt->execute());	print_r($stmt->errorInfo()); exit;		
		if ($stmt->execute()){
			$stmt = $this->conn->prepare("UPDATE items t set t.num_sales = t.num_sales + 1 WHERE t.id = :item_id");
			$stmt->bindParam(":item_id", $item_id);
			$stmt->execute();
			$nots = new DbNotifications();
			$sale_id = $this->conn->lastInsertId();
			$nots->send_notification($current_user_id,5,$item_id,$res["user_id"],$sale_id,"");
			$nots->send_notification($res["user_id"],6,$item_id,$current_user_id,$sale_id,"");
			return array("error" => 0, "id" => $sale_id, "message" => "Purchase Successful");
		}else{
			print_r($stmt->errorInfo());
			return array("error" => 1, "message" => "Unable to buy item - please try again later");
		}
	}
	public function item_sales_verifypayment($current_user_id, $sale_id){
		
	}
	public function item_sales_store_payment($sale_id, $paypalPaymentId, $user_id, $create_time, $update_time, $state, $amount, $currency) {
        $stmt = $this->conn->prepare("INSERT INTO payments(sale_id, paypalPaymentId, user_id, create_time, update_time, state, amount, currency) VALUES(:sale_id, :paypalPaymentId, :user_id, :create_time, :update_time, :state, :amount, :currency)");
		$stmt->bindParam(":paypalPaymentId",$paypalPaymentId);
		$stmt->bindParam(":user_id",$user_id);
		$stmt->bindParam(":create_time",$create_time);
		$stmt->bindParam(":update_time",$update_time);
		$stmt->bindParam(":state",$state);
		$stmt->bindParam(":amount",$amount);
		$stmt->bindParam(":currency",$currency);
        $result = $stmt->execute();
 
        if ($result) {
            // task row created
            // now assign the task to user
            $payment_id = $this->conn->lastInsertId();
		
			$stmt = $this->conn->prepare("UPDATE sales t set t.paid = 1 WHERE t.id = :sale_id");
			$stmt->bindParam(":sale_id", $sale_id);
			$stmt->execute();
			
            return $payment_id;
        } else {
            // task failed to create
            return NULL;
        }
    }
	
	
	public function item_sales_reviews($current_user_id, $sale_id,$rating, $comment){
		$stmt = $this->conn->prepare("SELECT seller_id from sales WHERE id = :sale_id AND buyer_id = :user_id");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":sale_id", $sale_id);
		$stmt->execute();
		$type = -1;
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			$rated_user_id = $res["seller_id"];
			$type = 1;
		}
		$stmt = $this->conn->prepare("SELECT user_id from sales WHERE id = :sale_id AND seller_id = :user_id");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":sale_id", $sale_id);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			$rated_user_id = $res["buyer_id"];
			$type = 2;
		}
		
		$stmt = $this->conn->prepare("SELECT rating from reviews WHERE rater_user_id = :rater_user_id AND rated_user_id = :rated_user_id");
		$stmt->bindParam(":rater_user_id", $current_user_id);
		$stmt->bindParam(":rated_user_id", $rated_user_id);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res != NULL){
			return array("error" => 3, "message" => "Sorry, you can't review the same transaction twice");
		}
		
		
		if ($type == -1){
			return array("error" => 2, "message" => "Unable to send review - no authorization");
		}
		$stmt = $this->conn->prepare("INSERT INTO reviews(rater_user_id, rated_user_id, sale_id, rating, comment, type) values(:rater_user_id, :rated_user_id,:sale_id,:rating, :comment, :type)");
		$stmt->bindParam(":rater_user_id",$current_user_id);
		$stmt->bindParam(":rated_user_id",$rated_user_id);
		$stmt->bindParam(":sale_id",$sale_id);
		$stmt->bindParam(":rating",$rating);
		$stmt->bindParam(":comment",$comment);
		$stmt->bindParam(":type",$type);
		if ($stmt->execute()){
			$stmt = $this->conn->prepare("UPDATE users t set t.no_reviews = t.no_reviews + 1 WHERE t.id = :user_id");
			$stmt->bindParam(":user_id", $rated_user_id);
			$stmt->execute();
			return array("error" => 0, "id" => $this->conn->lastInsertId(), "message" => "Review Successful");
		}else{
			return array("error" => 1, "message" => "Unable to send review - please try again later");
		}
	}
	
	/**
	 * Fetching all user items
	 * @param String $user_id id of the user
	 */
	 
	public function getAllItems($current_user_id,$filter,$q,$newerthan_id,$olderthan_id,$count,$test) {
		$selectqry = "";
		if ($q == NULL) $q = "";
		if ($newerthan_id == NULL) $newerthan_id = 0;
		if ($olderthan_id == NULL) $olderthan_id = 0;
		if ($test == NULL) $test = 0;
		
		switch($filter){
			case "":
				$selectqry .= "";
				//$searchqry = "AND f.user_id = ? AND f.following_id = t.user_id AND";
				$searchqry = "";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
				if ($count == NULL){
					$count = 10;
				}
			break;
			case "home":
				$selectqry .= ", following f";
				$searchqry = "AND f.user_id = :uid AND f.following_id = t.user_id";
				if ($count == NULL){
					$count = 10;
				}
				//$searchqry = "AND t.user_id <> ?";//"AND f.user_id = ? AND f.following_id = t.user_id AND";
			break;
			case "purchased":
				$selectqry .= ", sales sl";
				$searchqry =  "AND sl.buyer_id = :uid AND t.id = sl.item_id";
			break;
			case "sold":
				$selectqry .= ", sales sl";
				$searchqry =  "AND sl.seller_id = :uid AND t.id = sl.item_id";
				//$searchqry =  "AND t.user_id = :uid AND t.purchased_by <> 0";
			break;
			case "favorites":
				$selectqry .= ", likes f";
				$searchqry =  "AND f.user_id = :uid AND f.item_id = t.id";
			break;
			case "explore":
				if ($q == NULL || $q == ""){
					$searchqry =  "AND t.user_id <> :uid";
				}
			break;
		}
		
		$stmt = $this->limitQuery("SELECT NOT ISNULL(youfollow.user_id) as youfollow, ut.username, ut.image as userimage, t.user_id, t.created_at, t.test, t.negotiable, t.type, t.id, t.title, " . $this->getpricet	. ", t.description, loc.locality, t.location_id, t.quantity-t.num_sales as quantity, t.status, t.created_at, t.image, COUNT(distinct ul.id) as liked, COUNT(distinct s.id) AS num_shares from items t
		LEFT JOIN likes AS ul ON (ul.item_id = t.id AND ul.user_id = :uid)
		LEFT JOIN likes AS l ON (l.item_id = t.id)
		LEFT JOIN locations AS loc ON (loc.id = t.location_id)
		LEFT JOIN shares AS s ON (s.item_id = t.id)
		LEFT JOIN comments AS c ON (c.item_id = t.id)
		LEFT JOIN users AS ut ON ut.id = t.user_id	
		LEFT JOIN following as youfollow ON youfollow.user_id = :uid
		" . $this->getpricetablest	. "	
		$selectqry
		WHERE t.id ### :limitid AND t.status BETWEEN 0 AND 2 AND t.test = :testid $searchqry AND (t.title LIKE CONCAT('%', :q, '%') OR t.description LIKE CONCAT('%',:q, '%')) GROUP BY t.id ORDER BY t.id DESC LIMIT 0, 10",$newerthan_id,$olderthan_id,$count);
		
		// INNER JOIN locations AS lt ON lt.id = t.location_id
		if ($test == NULL){
			$test = 0;
		}
		$stmt->bindParam(":testid",$test);
					$stmt->bindParam(":uid",$current_user_id);
		$stmt->bindParam(":q",$q);
		//echo "<br />" .$stmt->debugDumpParams();
	//	echo $q;
		//print_r($stmt);exit;
		$response = $this->xExecute($stmt);
//echo $stmt->queryString;
		
		$ids = array();
		$index = array();
		$result = $response["users"];
		$itemCount = count($result);
		for ($i=0; $i < $itemCount; $i++){
			$result[$i]["num_comments"] = 0;
			$result[$i]["num_likes"] = 0;
			$result[$i]["num_interactions"] = 0;
			$result[$i]["comments"] = array();
			$result[$i]["likes"] = array();
			$result[$i]["interactions"] = array();
			$ids[] = $result[$i]["id"];
			$index[$result[$i]["id"]] = $i;
		}
		$idscount = count($ids);
		if ($idscount > 0){
			$inQuery = implode(',', array_fill(0, $idscount, '?'));
			$stmt = $this->conn->prepare("SELECT t.username, t.image as userimage, t.locality, t.id as user_id, t.name, f.comment, f.id, f.created_at, f.item_id FROM users t, comments f WHERE f.item_id in (" . $inQuery . ") AND t.id = f.user_id ORDER BY f.id DESC");
			foreach ($ids as $k => $id) $stmt->bindValue(($k+1),$id);
			
			if ($stmt->execute()){
				while (($comment = $stmt->fetch(PDO::FETCH_ASSOC))){
					if ($result[$index[$comment["item_id"]]]["num_comments"] < 20){
						$result[$index[$comment["item_id"]]]["comments"][] = $comment;
					}
						$result[$index[$comment["item_id"]]]["num_comments"]++;
				}
			}
			$stmt = $this->conn->prepare("SELECT t.username, t.image as userimage, t.locality, t.id as user_id, t.name, f.id, f.created_at, f.item_id FROM users t, likes f WHERE f.item_id in (" . $inQuery . ") AND t.id = f.user_id ORDER BY f.id DESC");
			foreach ($ids as $k => $id) $stmt->bindValue(($k+1),$id);
			
			if ($stmt->execute()){
				while (($comment = $stmt->fetch(PDO::FETCH_ASSOC))){
					if ($result[$index[$comment["item_id"]]]["num_likes"] < 20){
						$result[$index[$comment["item_id"]]]["likes"][] = $comment;
					}
						$result[$index[$comment["item_id"]]]["num_likes"]++;
				}
			}
			$stmt = $this->conn->prepare("(SELECT t.username, t.image as userimage, t.locality, t.id, t.name, f.item_id FROM users t, likes f WHERE f.item_id in (" . $inQuery . ") AND t.id = f.user_id AND t.id <> ? ORDER BY f.id DESC) UNION (SELECT t.username, t.image as userimage, t.locality, t.id, t.name, c.item_id FROM users t, comments c WHERE c.item_id in (" . $inQuery . ") AND t.id = c.user_id AND t.id <> ? ORDER BY c.id DESC)");
			foreach ($ids as $k => $id) {
				$stmt->bindValue(($k+1),$id);
				$stmt->bindValue(($k+$idscount+1+1),$id);
			}
			$stmt->bindValue($idscount+1,$current_user_id); 
			$stmt->bindValue(($idscount+1)*2,$current_user_id); 
			
			if ($stmt->execute()){
				while (($comment = $stmt->fetch(PDO::FETCH_ASSOC))){
					if ($result[$index[$comment["item_id"]]]["num_interactions"] < 20){
						$result[$index[$comment["item_id"]]]["interactions"][] = $comment;
					}
						$result[$index[$comment["item_id"]]]["num_interactions"]++;
				}
			}
		}
		//echo "hello";	
		return $result;
	}
	
	/**
	 * Updating item
	 * @param String $item_id id of the item
	 * @param String $item item text
	 * @param String $status item status
	 */
	public function updateItem($current_user_id, $item_id, $title, $price, $description, $quantity, $images, $status, $location_id) {

		// Check if the user is the owner of the item
		$stmt = $this->conn->prepare("SELECT user_id FROM items WHERE id = " . $item_id);
		if($stmt->execute()) {
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($result['user_id'] != $current_user_id)
				return array("error" => 1, "message" => "You don't have permission to update");
		} else {
			return array("error" => 1, "message" => "Something went wrong");
		}

		if ($status != NULL & $status != 0){
			$stmt = $this->conn->prepare("UPDATE items t set t.status = :status,t.updated_at=:updated_at WHERE t.id = :id AND t.user_id = :user_id");
			$stmt->bindParam(":status", $status);
			$stmt->bindParam(":id", $item_id);
			$stmt->bindParam(":user_id", $current_user_id);
			$curr_date=date('Y-m-d H:i:s');
			$stmt->bindParam(":updated_at", $curr_date);
			if ($stmt->execute()){
				return array("error" => 0, "message" => "Status Updated Successfully");
			}else{
				return array("error" => 1, "message" => "Status Update Failed");
			}
		}else{			
			$stmt = $this->conn->prepare("UPDATE items t set t.title = :title, t.price = :price, t.description = :description,t.updated_at=:updated_at t.quantity = :quantity, t.image = :image, t.location_id = :location_id WHERE t.id = :id AND t.user_id = :user_id");
			$stmt->bindParam(":title", $title);
			$stmt->bindParam(":price", $price);
			$stmt->bindParam(":description", $description);
			$stmt->bindParam(":quantity", $quantity);
			$stmt->bindParam(":image", $images);
			$stmt->bindParam(":location_id", $location_id);
			$stmt->bindParam(":id", $item_id);
			$stmt->bindParam(":user_id", $current_user_id);
			$curr_date=date('Y-m-d H:i:s');
			$stmt->bindParam(":updated_at", $curr_date);
			if ($stmt->execute()){
				return array("error" => 0, "message" => "Updated Successfully");
			}else{
				return array("error" => 1, "message" => "Update Failed");
			}
		}
	}
	
	/**
	 * Publishing item
	 * @param String $item_id id of the item
	 * @param String $item item text
	 * @param String $status item status
	 */
	public function publishItem($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("UPDATE items t set t.published_at = :published_at, t.status = :status WHERE t.id = :id AND t.user_id = :user_id");
		
		$status = 2;
		$date = new DateTime();
		$published_at = $date->getTimestamp();
		
		$stmt->bindParam(":published_at", $published_at);
		$stmt->bindParam(":status", $status);
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		
		return intval($stmt->execute());
	}
	
	public function deactivateItem($current_user_id, $item_id) {
		
		$status = 5;
		
		$stmt = $this->conn->prepare("SELECT t.status FROM items t WHERE t.id = :item_id AND t.user_id = :user_id");
		$stmt->bindParam(":item_id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->execute();
		$current_status = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if($status != $current_status['status']) {
			$stmt = $this->conn->prepare("UPDATE items t set t.status = :status WHERE t.id = :item_id AND t.user_id = :user_id");

			$stmt->bindParam(":status", $status);
			$stmt->bindParam(":item_id", $item_id);
			$stmt->bindParam(":user_id", $current_user_id);
			$this->items_count($current_user_id,-1);			
			return intval($stmt->execute());
		} else {
			return true;
		}
	}
	public function items_count($current_user_id,$val){
		if ($val == 1){
			$stmt = $this->conn->prepare("UPDATE users t set t.no_products = t.no_products + 1 WHERE t.id = :user_id");
		}else {
			$stmt = $this->conn->prepare("UPDATE users t set t.no_products = t.no_products - 1 WHERE t.id = :user_id");
		}
		
		$stmt->bindParam(":user_id", $current_user_id);		
		return intval($stmt->execute());
	}
	/**
	 * Deleting a item
	 * @param String $item_id id of the item to delete
	 */
	public function deleteItem($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("DELETE t FROM items t WHERE t.id = :id AND t.user_id = :user_id");
		$stmt->bindParam(":id", $item_id);
		$stmt->bindParam(":user_id", $current_user_id);
		return $stmt->execute();
	}
	
	/* ------------- `user_items` table method ------------------ */
	
	/**
	 * public function to assign a item to user
	 * @param String $current_user_id id of the user
	 * @param String $item_id id of the item
	 */
	/*public function createUserItem($current_user_id, $item_id) {
		$stmt = $this->conni->prepare("INSERT INTO user_items(user_id, item_id) values(:user_id, :item_id)");
		$stmt->bindParam(":user_id", $current_user_id);
		$stmt->bindParam(":item_id", $item_id);
		$result = $stmt->execute();
	
		if (false === $result) {
			die('execute() failed: ' . htmlspecialchars($stmt->error));
		}
		$stmt->close();
		return $result;
	}*/
	
}
?>
