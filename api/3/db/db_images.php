<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbImages extends DbBase {

	public $upload_api_3_base = '';
	public $testbot_testuploads = '';

	public $s3_application; // s3 application

	public function s3_setup() {
		// A helper class to upload actual images to our s3 server
		include_once dirname(__DIR__) . '/helper/config_s3.php';

		ini_set('max_execution_time', 300);

	 	$this->s3_application = new Config_s3;

	 	$this->setApi3Base();
	 	$this->setTestbotBase();
	}

	private function check_s3_setup() {
		if(!($this->s3_application instanceof Config_s3))
			$this->s3_setup();
	}

	public function setApi3Base() {
		$this->check_s3_setup();
		$this->upload_api_3_base = $this->s3_application->upload_api_3_base;
	}

	public function getApi3Base() {
		$this->check_s3_setup();
		return $this->upload_api_3_base;
	}

	public function setTestbotBase() {
		$this->check_s3_setup();
		$this->testbot_testuploads = $this->s3_application->testbot_testuploads;
	}

	public function getTestbotBase() {
		$this->check_s3_setup();
		return $this->testbot_testuploads;
	}

	public function __fetchFile($filename, $uploads_api_3_tmp) {
		$this->check_s3_setup();
		return $this->s3_application->__fetchFile($filename, $uploads_api_3_tmp);
	}

	public function _setPutObjectVariables($filename, $file) {
		$this->check_s3_setup();
		$this->s3_application->_setPutObjectVariables($filename, $file);
	}

	public function _putObject($will_unlink = false) {
		$this->check_s3_setup();
		return $this->s3_application->_putObject($will_unlink);
	}

	public function _getBucketItems() {
		$this->check_s3_setup();
		return $this->s3_application->bucket_items;
	}

	public function _getBucketProfiles() {
		$this->check_s3_setup();
		return $this->s3_application->bucket_profiles;
	}

	/* ------------- `images` table method ------------------ */
	
	/**
	 * Creating new item
	 * @param String $current_user_id user id to whom item belongs to
	 * @param String $item item text
	 */
	public function createImage($current_user_id, $image) {
		$stmt = $this->conn->prepare("INSERT INTO images(user_id,image) VALUES(:id, :image)");
		$stmt->bindParam(":id",$current_user_id);
		$stmt->bindParam(":image",$image);
		
		$result = $stmt->execute();
	
		if ($result) {
			// item row created
			// now assign the item to user
			$new_item_id = $this->conn->lastInsertId();
			return $new_item_id;
		} else {
			// item failed to create
			return NULL;
		}
	}
	public function images_get($item_id) {
		$stmt = $this->conn->prepare("SELECT image, i FROM images WHERE item_id = :id");
		$stmt->bindParam(":id",$item_id);
		
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	public function image_get($current_user_id,$image_id) {
		$stmt = $this->conn->prepare("SELECT image, i, item_id FROM images WHERE id = :id AND user_id = :uid");
		$stmt->bindParam(":id", $image_id);
		$stmt->bindParam(":uid", $current_user_id);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		return $res;
	}
	public function associateImageWithItem($current_user_id,$item_id,$image_id,$i){
		
		/*$stmt = $this->conn->prepare("SELECT item_id, image FROM images WHERE id = :id");
		$stmt->bindParam(":id",$image_id);
		$result = $stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res ) {
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
		
		
		return $num_affected_rows > 0;*/
	}
	
	/**
	 * Deleting a item
	 * @param String $item_id id of the item to delete
	 */
	public function deleteImage($current_user_id, $item_id) {
		$stmt = $this->conn->prepare("DELETE t FROM images t WHERE t.id = :id AND t.user_id = :uid");
		$stmt->bindParam(":id", $item_id);
		$stmt->bindParam(":uid", $current_user_id);
		return $stmt->execute();
	}
}
?>
