<?php

/**
 * Listing all items of particual user
 * method GET
 * url /items          
 */
/*$app->get('/images/:id', 'authenticate', function($item_id) {
            global $current_user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user items
            //$result = $db->getAllItemImages($current_user_id);
			echoResponse(200,$db->image_get($current_user_id, $item_id));
        });*/
$app->get('/images/:id', 'authenticate', function($image_id) {
	global $current_user_id;
	$response = array();
	$db = new DbHandler();

	// fetching all user items
	//$result = $db->getAllItemImages($current_user_id);
	echoResponse(200,$db->image_get($current_user_id, $image_id));
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
			
			if ( is_uploaded_file($_FILES['image']['tmp_name']) ) {
				$filename = 'u' . uniqid() . '.jpg';
				move_and_crop_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename,610,610);
				//move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/images/uploads/items/' . $filename);
				// creating new item
				$image_id = $db->createImage($current_user_id,$filename);
				if ($image_id != NULL) {
					$response["error"] = 0;
					$response["image_id"] = $image_id;
					$response["url"] = $filename;
					echoResponse(201, $response);
				} else {
					$response["error"] = 1;
					$response["title"] = "Error";
					$response["message"] = "Failed to create item. Please try again";
					echoResponse(200, $response);
				}
				
			}else{
				$response["error"] = 1;
				$response["title"] = "Error";
				$response["message"] = "Failed to upload image. Please retry";
				echoResponse(200, $response);
			}
        });
		
function move_and_crop_uploaded_file($cropfile,$location,$new_w,$new_h){
        $image_info = getimagesize($cropfile);
	switch($image_info["mime"]){
			case "image/jpeg":
				$source_img = @imagecreatefromjpeg($cropfile); //jpeg file
			break;
			case "image/gif":
				$source_img = @imagecreatefromgif($cropfile); //gif file
		  break;
		  case "image/png":
			  $source_img = @imagecreatefrompng($cropfile); //png file
		  break;
		}
        if (!$source_img) {
            echo "could not create image handle";
            exit(0);
        }
        // set your width and height for the thumbnail

        $orig_w = imagesx($source_img);
        $orig_h = imagesy($source_img);

        $w_ratio = ($new_w / $orig_w);
        $h_ratio = ($new_h / $orig_h);

        if ($orig_w > $orig_h ) {//landscape from here new
            $crop_w = round($orig_w * $h_ratio);
            $crop_h = $new_h;
            $src_x = ceil( ( $orig_w - $orig_h ) / 2 );
            $src_y = 0;
        } elseif ($orig_w < $orig_h ) {//portrait
            $crop_h = round($orig_h * $w_ratio);
            $crop_w = $new_w;
            $src_x = 0;
            $src_y = ceil( ( $orig_h - $orig_w ) / 2 );
        } else {//square
            $crop_w = $new_w;
            $crop_h = $new_h;
            $src_x = 0;
            $src_y = 0;
        }
        $dest_img = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dest_img, $source_img, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h); //till here
        if(imagejpeg($dest_img, $location)) {
            imagedestroy($dest_img);
            imagedestroy($source_img);
        } else {
            echo "could not make thumbnail image";
            exit(0);
        }
}


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