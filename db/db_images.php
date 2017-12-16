<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbImages extends DbBase {

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
