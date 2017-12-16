<?php

/**
 * Listing all items of particual user
 * method GET
 * url /items          
 */
$app->get('/images/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user items
            //$result = $db->getAllItemImages($current_user_id);
			echoResponse(200,$db->getImage($current_user_id, $item_id));
        });

/**
 * Creating new item in db
 * method POST
 * params - name
 * url - /items/
 */
$app->post('/images', 'authenticate', function() use ($app) {
            // check for required params
            $response = array();

            global $current_user_id;
            $db = new DbHandler();
			
			error_log("tried to post image");
			error_log("User is " . $current_user_id);
			$filename = $_FILES['image']['tmp_name'];
			if ( is_uploaded_file($filename) ) {
				$image_info = getimagesize($filename);
				$image_width = $image_info[0];
				$image_height = $image_info[1];
				switch($image_info["mime"]){
					case "image/jpeg":
						$image = imagecreatefromjpeg($filename); //jpeg file
					break;
					case "image/gif":
						$image = imagecreatefromgif($filename); //gif file
				  break;
				  case "image/png":
					  $image = imagecreatefrompng($filename); //png file
				  break;
				default: 
					error_log("unknown image type - upload failed");
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "Failed to upload image. Please retry";
					echoResponse(200, $response);
				break;
				}
				if ($image_width != $image_height){
					$x = 0;
					$y = 0;
					if ($image_width > $image_height){
						$newsize = $image_height;
						$x = ($image_width-$newsize)/2;
					}else{
						$newsize = $image_width;
						$y = ($image_height-$newsize)/2;
					}
					$crop = imagecreatetruecolor($newsize,$newsize);
					imagecopy ( $crop, $image, 0, 0, $x, $y, $newsize, $newsize );
				}else{
					$crop = $image;
				}
				$filename = 'u' . uniqid() . '.jpg';
				imagejpeg($crop,$_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
				//move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
				// creating new item
				$image_id = $db->createImage($current_user_id,$filename);
				if ($image_id != NULL) {
					error_log("sjcceeded");
					$response["error"] = 0;
					$response["image_id"] = $image_id;
					echoResponse(201, $response);
				} else {
					error_log("failed");
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "Failed to create item. Please try again";
					echoResponse(200, $response);
				}
				
			}else{
				error_log("upload failed");
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "Failed to upload image. Please retry";
				echoResponse(200, $response);
			}
        });
		
		


/**
 * Deleting item. Users can delete only their items
 * method DELETE
 * url /items
 */
$app->delete('/images/:id', 'authenticate', function($image_id) use($app) {
            global $current_user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteImage($current_user_id, $image_id);
            if ($result) {
                // item deleted successfully
                $response["error"] = 0;
                $response["message"] = "Item deleted succesfully";
            } else {
                // item failed to delete
                $response["error"] = 1;
                $response["message"] = "Item failed to delete. Please try again!";
            }
            echoResponse(200, $response);
        });
?>